<?php
/**
 * Schema Parser & Migration Generator
 * 
 * Script ini akan membaca file HTML schema dan generate migration files
 * untuk CodeIgniter 4
 */

$schemaFile = __DIR__ . '/newsoft_base schema.htm';
$outputDir = __DIR__ . '/app/Database/Migrations/';

if (!file_exists($schemaFile)) {
    die("Schema file not found: $schemaFile\n");
}

$html = file_get_contents($schemaFile);

// Parse tables
preg_match_all('/<a name=\'([^\']+)\'>&nbsp<\/a>/', $html, $tableMatches);
$tables = $tableMatches[1];

echo "Found " . count($tables) . " tables\n\n";

// Function to get table structure
function getTableStructure($html, $tableName) {
    // Find table section
    $pattern = '/<a name=\'' . preg_quote($tableName) . '\'>&nbsp<\/a>.*?<a href="#header"/s';
    if (!preg_match($pattern, $html, $section)) {
        return null;
    }
    
    $sectionHtml = $section[0];
    
    // Extract only the Fields section (not Indexes section)
    if (preg_match('/<td class="fieldheader"[^>]*>Fields<\/td>.*?<\/table>\s*<table[^>]*>(.*?)<\/table>/s', $sectionHtml, $fieldsSection)) {
        $fieldsHtml = $fieldsSection[1];
    } else {
        return null;
    }
    
    // Parse fields from Fields section only
    preg_match_all('/<tr>\s*<td align="left" valign="top"><p class="normal">([^<]+)<\/td>\s*<td align="left" valign="top"><p class="normal">([^<]+)<\/td>\s*<td[^>]*><p class="normal">([^<]*)<\/td>\s*<td[^>]*><p class="normal">([^<]*)<\/td>\s*<td[^>]*><p class="normal">([^<]*)<\/td>\s*<td[^>]*><p class="normal">([^<]*)<\/td>\s*<td[^>]*><p class="normal">([^<]*)<\/td>/s', $fieldsHtml, $fields);
    
    $columns = [];
    for ($i = 0; $i < count($fields[0]); $i++) {
        $fieldName = trim($fields[1][$i]);
        $fieldType = trim($fields[2][$i]);
        $collation = trim($fields[3][$i]);
        $null = trim($fields[4][$i]);
        $key = trim($fields[5][$i]);
        $default = trim($fields[6][$i]);
        $extra = trim($fields[7][$i]);
        
        $columns[] = [
            'name' => $fieldName,
            'type' => $fieldType,
            'null' => $null === 'YES',
            'key' => $key,
            'default' => $default !== '(NULL)' && $default !== '&nbsp;' ? $default : null,
            'extra' => $extra
        ];
    }
    
    return $columns;
}

// Convert MySQL type to CodeIgniter type
function convertType($mysqlType) {
    $mysqlType = strtolower($mysqlType);
    $unsigned = false;
    
    // Check for unsigned
    if (strpos($mysqlType, 'unsigned') !== false) {
        $unsigned = true;
        $mysqlType = str_replace(' unsigned', '', $mysqlType);
    }
    
    if (preg_match('/int\((\d+)\)/', $mysqlType, $m)) {
        $length = (int)$m[1];
        if ($length <= 3) return $unsigned ? ['TINYINT', 'unsigned' => true] : 'TINYINT';
        if ($length <= 5) return $unsigned ? ['SMALLINT', 'unsigned' => true] : 'SMALLINT';
        if ($length <= 9) return $unsigned ? ['INT', 'unsigned' => true] : 'INT';
        return $unsigned ? ['BIGINT', 'unsigned' => true] : 'BIGINT';
    }
    
    if (preg_match('/varchar\((\d+)\)/', $mysqlType, $m)) {
        return ['VARCHAR', (int)$m[1]];
    }
    
    if (preg_match('/char\((\d+)\)/', $mysqlType, $m)) {
        return ['CHAR', (int)$m[1]];
    }
    
    if (preg_match('/decimal\((\d+),(\d+)\)/', $mysqlType, $m)) {
        return ['DECIMAL', $m[1], $m[2]];
    }
    
    // Handle ENUM with values
    if (preg_match('/enum\((.*?)\)/i', $mysqlType, $m)) {
        // Extract enum values: 'Y','N' -> ['Y','N']
        $values = str_replace("'", "", $m[1]);
        $values = explode(',', $values);
        return ['ENUM', $values];
    }
    
    if (strpos($mysqlType, 'text') !== false) return 'TEXT';
    if (strpos($mysqlType, 'longtext') !== false) return 'LONGTEXT';
    if (strpos($mysqlType, 'mediumtext') !== false) return 'MEDIUMTEXT';
    if (strpos($mysqlType, 'datetime') !== false) return 'DATETIME';
    if (strpos($mysqlType, 'timestamp') !== false) return 'DATETIME'; // Use DATETIME instead of TIMESTAMP for compatibility
    if (strpos($mysqlType, 'date') !== false) return 'DATE';
    if (strpos($mysqlType, 'time') !== false) return 'TIME';
    if (strpos($mysqlType, 'float') !== false) return 'FLOAT';
    if (strpos($mysqlType, 'double') !== false) return 'DOUBLE';
    
    return 'VARCHAR'; // default
}

// Generate migration file content
function generateMigration($tableName, $columns) {
    $className = 'Create' . str_replace('_', '', ucwords($tableName, '_'));
    
    $content = "<?php\n\nnamespace App\Database\Migrations;\n\n";
    $content .= "use CodeIgniter\Database\Migration;\n\n";
    $content .= "class {$className} extends Migration\n{\n";
    $content .= "    public function up()\n    {\n";
    $content .= "        \$this->forge->addField([\n";
    
    $processedFields = []; // Track processed field names to avoid duplicates
    
    foreach ($columns as $col) {
        // Skip duplicate field names
        if (in_array($col['name'], $processedFields)) {
            continue;
        }
        $processedFields[] = $col['name'];
        
        $content .= "            '{$col['name']}' => [\n";
        
        $type = convertType($col['type']);
        if (is_array($type)) {
            $content .= "                'type' => '{$type[0]}',\n";
            
            // Handle unsigned
            if (isset($type['unsigned']) && $type['unsigned']) {
                $content .= "                'unsigned' => true,\n";
            }
            
            if (isset($type[1])) {
                if ($type[0] === 'ENUM' && is_array($type[1])) {
                    // ENUM values
                    $enumValues = "'" . implode("','", $type[1]) . "'";
                    $content .= "                'constraint' => [{$enumValues}],\n";
                } elseif (isset($type[2])) {
                    // DECIMAL (precision, scale)
                    $content .= "                'constraint' => '{$type[1]},{$type[2]}',\n";
                } elseif ($type[1] !== 'unsigned') {
                    // VARCHAR, CHAR (length) - skip if it's just unsigned flag
                    $content .= "                'constraint' => {$type[1]},\n";
                }
            }
        } else {
            $content .= "                'type' => '{$type}',\n";
        }
        
        if (strpos($col['extra'], 'auto_increment') !== false) {
            $content .= "                'auto_increment' => true,\n";
        }
        
        // Handle nullable
        if (!$col['null']) {
            $content .= "                'null' => false,\n";
        } else {
            $content .= "                'null' => true,\n";
        }
        
        if ($col['default'] !== null && $col['default'] !== '') {
            $defaultUpper = strtoupper($col['default']);
            // Skip CURRENT_TIMESTAMP for DATETIME fields - let application handle it
            if ($defaultUpper === 'CURRENT_TIMESTAMP' || $defaultUpper === 'CURRENT_TIMESTAMP()') {
                // Don't set default, will be handled by application
            } else {
                $content .= "                'default' => '{$col['default']}',\n";
            }
        }
        
        $content .= "            ],\n";
    }
    
    $content .= "        ]);\n\n";
    
    // Add primary key
    $primaryKeys = array_filter($columns, function($col) {
        return $col['key'] === 'PRI';
    });
    
    if (!empty($primaryKeys)) {
        $pkNames = array_map(function($col) {
            return $col['name'];
        }, $primaryKeys);
        $content .= "        \$this->forge->addKey(['" . implode("', '", $pkNames) . "'], true);\n";
    }
    
    // Add unique keys
    $uniqueKeys = array_filter($columns, function($col) {
        return $col['key'] === 'UNI';
    });
    
    foreach ($uniqueKeys as $uk) {
        $content .= "        \$this->forge->addUniqueKey('{$uk['name']}');\n";
    }
    
    $content .= "        \$this->forge->createTable('{$tableName}');\n";
    $content .= "    }\n\n";
    $content .= "    public function down()\n    {\n";
    $content .= "        \$this->forge->dropTable('{$tableName}');\n";
    $content .= "    }\n";
    $content .= "}\n";
    
    return $content;
}

// Create output directory if not exists
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$timestamp = date('YmdHis');
$counter = 0;

foreach ($tables as $table) {
    $counter++;
    $fileTimestamp = date('Y-m-d-His', strtotime($timestamp) + $counter);
    $fileTimestamp = str_replace('-', '', $fileTimestamp);
    
    echo "Processing table: $table ... ";
    
    $columns = getTableStructure($html, $table);
    
    if ($columns === null || empty($columns)) {
        echo "SKIPPED (no columns found)\n";
        continue;
    }
    
    $migration = generateMigration($table, $columns);
    
    $filename = $fileTimestamp . '_create_' . $table . '.php';
    $filepath = $outputDir . $filename;
    
    file_put_contents($filepath, $migration);
    
    echo "OK ($filename)\n";
}

echo "\n✅ Migration files generated successfully!\n";
echo "Location: $outputDir\n";
echo "\nTo run migrations:\n";
echo "php spark migrate\n\n";
echo "To rollback:\n";
echo "php spark migrate:rollback\n";
