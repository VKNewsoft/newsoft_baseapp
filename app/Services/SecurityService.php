<?php

namespace App\Services;

use CodeIgniter\Database\ConnectionInterface;
use Config\Database;

class SecurityService
{
    protected $db;
    protected $ip;
    protected $blockDuration = 3600; // 1 jam
    protected $maxRequests = 100;     // 60 request per menit
    protected $window = 60;          // 60 detik

    public function __construct()
    {
        $this->db = db_connect();
        $this->ip = service('request')->getIPAddress();
    }

    /*public function isBlocked(): bool
    {
        $query = $this->db->table('security_logs')
            ->where('ip_address', $this->ip)
            ->where('blocked_until >', date('Y-m-d H:i:s'))
            ->countAllResults();

        return $query > 0;
    }*/

   /* public function logAttack($attackType)
    {
        $data = [
            'ip_address'    => $this->ip,
            'request_uri'   => current_url(),
            'user_agent'    => service('request')->getUserAgent(),
            'attack_type'   => $attackType,
            'created_at'    => date('Y-m-d H:i:s'),
            'blocked_until' => date('Y-m-d H:i:s', time() + $this->blockDuration),
        ];

        $this->db->table('security_logs')->insert($data);
    }*/
    
            // Di SecurityService.php
        public function logAttack($attackType)
        {
            if ($attackType === 'sqlmap') {
        $this->blockIp($this->ip);
    }
            // Hanya log, jangan blokir dulu
            $this->db->table('security_logs')->insert([
                'ip_address'    => $this->ip,
                'request_uri'   => current_url(),
                'user_agent'    => service('request')->getUserAgent(),
                'attack_type'   => $attackType,
                'created_at'    => date('Y-m-d H:i:s'),
                 'blocked_until' => $attackType === 'sqlmap' 
                            ? date('Y-m-d H:i:s', time() + $this->blockDuration) 
                            : null,
            ]);
        
            // 🔥 Debug: Tampilkan di log
            log_message('error', "Suspicious request from {$this->ip}: $attackType");
        }

               public function incrementRequest()
            {
                $now = date('Y-m-d H:i:s');
                $windowStart = date('Y-m-d H:i:s', strtotime("-{$this->window} seconds"));
            
                $query = $this->db->table('security_logs')
                    ->select('SUM(request_count) as total')
                    ->where('ip_address', $this->ip)
                    ->where('request_uri', current_url())
                    ->where('created_at >', $windowStart)
                    ->get()
                    ->getRow();
            
                $count = $query->total ?? 0;
            
                if ($count >= $this->maxRequests) {
                    // 🔥 Blokir IP karena rate limit
                    $this->blockIp($this->ip);
                    $this->logRateLimitBlock();
                    return false;
                }
            
                // Tambahkan request normal
                $this->db->table('security_logs')->insert([
                    'ip_address'    => $this->ip,
                    'request_uri'   => current_url(),
                    'user_agent'    => service('request')->getUserAgent(),
                    'attack_type'   => 'normal',
                    'created_at'    => $now,
                    'request_count' => 1,
                    'blocked_until' => null,
                ]);
            
                return true;
            }

    public function logRateLimitBlock()
    {
        $this->db->table('security_logs')->insert([
            'ip_address'    => $this->ip,
            'request_uri'   => current_url(),
            'user_agent'    => service('request')->getUserAgent(),
            'attack_type'   => 'rate_limit',
            'created_at'    => date('Y-m-d H:i:s'),
            'blocked_until' => date('Y-m-d H:i:s', time() + $this->blockDuration),
            'request_count' => 1,
        ]);
    }
    
            public function blockIp($ip)
            {
                // CEK IP Whitelist untuk menghindari blocklist
                $db = db_connect();
                $whitelist = $db->table('whitelist_ips')
                    ->where('ip_address', $ip)
                    ->countAllResults();

                if($whitelist <> 1){
                    // Opsi 1: Simpan ke database
                    $db = db_connect();
                    $db->table('blocked_ips')->replace([
                        'ip_address' => $ip,
                        'blocked_at' => date('Y-m-d H:i:s')
                    ]);
                }
            
                // Opsi 2: Simpan ke cache (lebih cepat)
                // cache()->save("blocked_ip_{$ip}", true, 3600); // blokir 1 jam
            }
            
            public function isBlocked()
            {
                $ip = service('request')->getIPAddress();
            
                // Cek dari database
                $db = db_connect();
                $result = $db->table('blocked_ips')
                             ->where('ip_address', $ip)
                             ->countAllResults();
            
                return $result > 0;
            
                // Atau cek dari cache
                // return cache()->has("blocked_ip_{$ip}");
            }
}