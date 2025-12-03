<?php
namespace App\Models;

class DashboardModel extends \App\Models\BaseModel
{
	protected $idCompanyLogin;

	public function __construct() {
		parent::__construct();
		$this->idCompanyLogin = $this->session->get('user')['id_company'];
	}
	
	/**
	 * Mengambil daftar tahun dari data penjualan
	 * 
	 * @return array Daftar tahun penjualan
	 */
	public function getListTahun() {
		$sql = 'SELECT YEAR(tgl_penjualan) AS tahun
				FROM pos_penjualan
				WHERE status <> ?
				GROUP BY tahun';
		$result = $this->db->query($sql, ['void'])->getResultArray();
		return $result;
	}

	/**
	 * Mengambil daftar tenant yang aktif
	 * 
	 * @return array Daftar tenant aktif
	 */
	public function getListTenant() {
		$sql = 'SELECT * FROM core_company
				WHERE tenant_aktif = ?
				AND isDeleted = ?';
		$result = $this->db->query($sql, ['Y', 0])->getResultArray();
		return $result;
	}
	
	/**
	 * Mengambil total item terjual dengan perbandingan tahun sebelumnya
	 * 
	 * @param int $tahun Tahun yang akan dianalisa
	 * @return array Data total item terjual dengan growth
	 */
	public function getTotalItemTerjual($tahun) 
	{
		$tahun = (int) $tahun; // Sanitize input
		$tahunSebelumnya = $tahun - 1;
		
		$whereTenant = '';
		$params = [];
		
		if ($this->idCompanyLogin > 0) {
			$whereTenant = 'pos_penjualan.id_company = ? AND ';
			$params[] = $this->idCompanyLogin;
		}
		
		$sql = "SELECT jml, jml_prev, ROUND((jml - jml_prev) / jml_prev * 100, 2) AS growth
				FROM (
					SELECT 
						COUNT(IF(YEAR(tgl_penjualan) = ?, id_barang, NULL)) AS jml,
						COUNT(IF(YEAR(tgl_penjualan) = ?, id_barang, NULL)) AS jml_prev	
					FROM pos_penjualan_detail
					LEFT JOIN pos_penjualan USING(id_penjualan)
					WHERE {$whereTenant} (YEAR(tgl_penjualan) = ? OR YEAR(tgl_penjualan) = ?) 
						AND status <> ?
				) AS tabel";
		
		$params = array_merge($params, [$tahun, $tahunSebelumnya, $tahun, $tahunSebelumnya, 'void']);
		return $this->db->query($sql, $params)->getRowArray();
	}

	/**
	 * Mengambil total item terjual (alternatif menggunakan total_qty)
	 * 
	 * @param int $tahun Tahun yang akan dianalisa
	 * @return array Data total quantity terjual
	 */
	public function getTotalItemTerjual2($tahun) 
	{
		$tahun = (int) $tahun;
		$tahunSebelumnya = $tahun - 1;
		
		$whereTenant = '';
		$params = [];
		
		if ($this->idCompanyLogin > 0) {
			$whereTenant = 'pos_penjualan.id_company = ? AND ';
			$params[] = $this->idCompanyLogin;
		}
		
		$sql = "SELECT *, SUM(total_qty) as jml 
				FROM pos_penjualan 
				WHERE {$whereTenant} (YEAR(tgl_penjualan) = ? OR YEAR(tgl_penjualan) = ?) 
					AND status <> ?";
		
		$params = array_merge($params, [$tahun, $tahunSebelumnya, 'void']);
		return $this->db->query($sql, $params)->getRowArray();
	}
	
	/**
	 * Mengambil total jumlah transaksi dengan perbandingan tahun sebelumnya
	 * 
	 * @param int $tahun Tahun yang akan dianalisa
	 * @return array Data total transaksi dengan growth
	 */
	public function getTotalJumlahTransaksi($tahun) 
	{
		$tahun = (int) $tahun;
		$tahunSebelumnya = $tahun - 1;
		
		$whereTenant = '';
		$params = [];
		
		if ($this->idCompanyLogin > 0) {
			$whereTenant = 'pos_penjualan.id_company = ? AND ';
			$params[] = $this->idCompanyLogin;
		}
		
		$sql = "SELECT jml, jml_prev, ROUND((jml - jml_prev) / jml_prev * 100, 2) AS growth
				FROM (
					SELECT 
						COUNT(IF(YEAR(tgl_penjualan) = ?, id_penjualan, NULL)) AS jml,
						COUNT(IF(YEAR(tgl_penjualan) = ?, id_penjualan, NULL)) AS jml_prev
					FROM pos_penjualan
					WHERE {$whereTenant} (YEAR(tgl_penjualan) = ? OR YEAR(tgl_penjualan) = ?) 
						AND status <> ?
				) AS tabel";
		
		$params = array_merge($params, [$tahun, $tahunSebelumnya, $tahun, $tahunSebelumnya, 'void']);
		return $this->db->query($sql, $params)->getRowArray();
	}

	/**
	 * Mengambil total jumlah transaksi void dengan perbandingan tahun sebelumnya
	 * 
	 * @param int $tahun Tahun yang akan dianalisa
	 * @return array Data total transaksi void dengan growth
	 */
	public function getTotalJumlahTransaksiVoid($tahun) 
	{
		$tahun = (int) $tahun;
		$tahunSebelumnya = $tahun - 1;
		
		$whereTenant = '';
		$params = [];
		
		if ($this->idCompanyLogin > 0) {
			$whereTenant = 'pos_penjualan.id_company = ? AND ';
			$params[] = $this->idCompanyLogin;
		}
		
		$sql = "SELECT jml, jml_prev, ROUND((jml - jml_prev) / jml_prev * 100, 2) AS growth
				FROM (
					SELECT 
						COUNT(IF(YEAR(tgl_penjualan) = ?, id_penjualan, NULL)) AS jml,
						COUNT(IF(YEAR(tgl_penjualan) = ?, id_penjualan, NULL)) AS jml_prev
					FROM pos_penjualan
					WHERE {$whereTenant} (YEAR(tgl_penjualan) = ? OR YEAR(tgl_penjualan) = ?) 
						AND status = ?
				) AS tabel";
		
		$params = array_merge($params, [$tahun, $tahunSebelumnya, $tahun, $tahunSebelumnya, 'void']);
		return $this->db->query($sql, $params)->getRowArray();
	}
	
	/**
	 * Mengambil total nilai penjualan dengan perbandingan tahun sebelumnya
	 * 
	 * @param int $tahun Tahun yang akan dianalisa
	 * @return array Data total nilai penjualan dengan growth
	 */
	public function getTotalNilaiPenjualan($tahun) {
		$tahun = (int) $tahun;
		$tahunSebelumnya = $tahun - 1;
		
		$whereTenant = '';
		$params = [];
		
		if ($this->idCompanyLogin > 0) {
			$whereTenant = 'pos_penjualan.id_company = ? AND ';
			$params[] = $this->idCompanyLogin;
		}
		
		$sql = "SELECT jml, jml_prev, ROUND((jml - jml_prev) / jml_prev * 100, 2) AS growth
				FROM (
					SELECT 
						SUM(IF(YEAR(tgl_penjualan) = ?, neto, NULL)) AS jml,
						SUM(IF(YEAR(tgl_penjualan) = ?, neto, NULL)) AS jml_prev
					FROM pos_penjualan
					WHERE {$whereTenant} (YEAR(tgl_penjualan) = ? OR YEAR(tgl_penjualan) = ?) 
						AND status <> ?
				) AS tabel";
		
		$params = array_merge($params, [$tahun, $tahunSebelumnya, $tahun, $tahunSebelumnya, 'void']);
		return $this->db->query($sql, $params)->getRowArray();
	}
	
	/**
	 * Mengambil total pelanggan aktif dengan perbandingan tahun sebelumnya
	 * 
	 * @param int $tahun Tahun yang akan dianalisa
	 * @return array Data total pelanggan aktif dengan growth
	 */
	public function getTotalPelangganAktif($tahun) 
	{
		$tahun = (int) $tahun;
		$tahunSebelumnya = $tahun - 1;
		
		$whereTenant = '';
		$params = [];
		
		if ($this->idCompanyLogin > 0) {
			$whereTenant = 'pos_penjualan.id_company = ? AND ';
			$params[] = $this->idCompanyLogin;
		}
		
		$sql = "SELECT jml, jml_prev, ROUND((jml - jml_prev) / jml_prev * 100) AS growth, total 
				FROM (
					SELECT COUNT(jml) AS jml, COUNT(jml_prev) AS jml_prev, 
						(SELECT COUNT(*) FROM pos_customer) AS total
					FROM (
						SELECT 
							MAX(IF(YEAR(tgl_penjualan) = ?, 1, NULL)) AS jml,
							MAX(IF(YEAR(tgl_penjualan) = ?, 1, NULL)) AS jml_prev
						FROM pos_penjualan
						WHERE {$whereTenant} (YEAR(tgl_penjualan) = ? OR YEAR(tgl_penjualan) = ?) 
							AND status <> ?
						GROUP BY id_customer
					) AS tabel
				) tabel_utama";
		
		$params = array_merge($params, [$tahun, $tahunSebelumnya, $tahun, $tahunSebelumnya, 'void']);
		return $this->db->query($sql, $params)->getRowArray();
	}
			
	/**
	 * Mengambil data series penjualan per hari untuk beberapa tahun
	 * 
	 * @param array $listTahun Daftar tahun yang akan dianalisa
	 * @return array Data series penjualan per tahun
	 */
	public function getSeriesPenjualan($listTahun) {
		$result = [];		
		$whereTenant = '';
		$params = [];
		
		if ($this->idCompanyLogin > 0) {
			$whereTenant = 'pos_penjualan.id_company = ? AND ';
		}

		foreach ($listTahun as $tahun) {
			$tahun = (int) $tahun; // Sanitize
			$params = [];
			
			if ($this->idCompanyLogin > 0) {
				$params[] = $this->idCompanyLogin;
			}
			
			$sql = "SELECT MONTH(tgl_penjualan) AS bulan, DAY(tgl_penjualan) AS hari, 
						COUNT(id_penjualan) as JML, SUM(neto) total
					FROM pos_penjualan
					WHERE {$whereTenant} tgl_penjualan >= ? AND tgl_penjualan <= ? 
						AND status <> ?
					GROUP BY DAY(tgl_penjualan), MONTH(tgl_penjualan)";
			
			$params = array_merge($params, ["{$tahun}-01-01", "{$tahun}-12-31", 'void']);
			$result[$tahun] = $this->db->query($sql, $params)->getResultArray();
		}
		return $result;
	}
	
	/**
	 * Mengambil data series total penjualan per bulan untuk beberapa tahun
	 * 
	 * @param array $listTahun Daftar tahun yang akan dianalisa
	 * @return array Data series total penjualan per tahun
	 */
	public function getSeriesTotalPenjualan($listTahun) {		
		$result = [];
		$whereTenant = '';
		
		if ($this->idCompanyLogin > 0) {
			$whereTenant = 'pos_penjualan.id_company = ? AND ';
		}

		foreach ($listTahun as $tahun) {
			$tahun = (int) $tahun; // Sanitize
			$params = [];
			
			if ($this->idCompanyLogin > 0) {
				$params[] = $this->idCompanyLogin;
			}
			
			$sql = "SELECT MONTH(tgl_penjualan) as bulan, SUM(neto) AS total
					FROM pos_penjualan
					WHERE {$whereTenant} tgl_penjualan >= ? AND tgl_penjualan <= ? 
						AND status <> ?
					GROUP BY MONTH(tgl_penjualan)";
			
			$params = array_merge($params, ["{$tahun}-01-01", "{$tahun}-12-31", 'void']);
			$result[$tahun] = $this->db->query($sql, $params)->getResultArray();
		}
		return $result;
	}

	/**
	 * Mengambil ranking penjualan per company berdasarkan nominal
	 * 
	 * @param array $listTahun Daftar tahun yang akan dianalisa
	 * @return array Data ranking nominal penjualan per company
	 */
	public function getSeriesRankNominal($listTahun) {		
		$result = [];
		$whereTenant = '';
		
		if ($this->idCompanyLogin > 0) {
			$whereTenant = 'pos_penjualan.id_company = ? AND ';
		}

		foreach ($listTahun as $tahun) {
			$tahun = (int) $tahun; // Sanitize
			$params = [];
			
			if ($this->idCompanyLogin > 0) {
				$params[] = $this->idCompanyLogin;
			}
			
			$sql = "SELECT nama_company, MONTH(tgl_penjualan) as bulan, SUM(neto) AS total
					FROM pos_penjualan 
					LEFT JOIN core_company USING(id_company)
					WHERE {$whereTenant} tgl_penjualan >= ? AND tgl_penjualan <= ? 
						AND status <> ? AND tenant_aktif = ?
					GROUP BY nama_company, MONTH(tgl_penjualan)";
			
			$params = array_merge($params, ["{$tahun}-01-01", "{$tahun}-12-31", 'void', 'Y']);
			$result[$tahun] = $this->db->query($sql, $params)->getResultArray();
		}
		return $result;
	}

	/**
	 * Mengambil ranking penjualan per company berdasarkan quantity
	 * 
	 * @param array $listTahun Daftar tahun yang akan dianalisa
	 * @return array Data ranking quantity penjualan per company
	 */
	public function getSeriesRankQty($listTahun) {		
		$result = [];
		$whereTenant = '';
		
		if ($this->idCompanyLogin > 0) {
			$whereTenant = 'pos_penjualan.id_company = ? AND ';
		}

		foreach ($listTahun as $tahun) {
			$tahun = (int) $tahun; // Sanitize
			$params = [];
			
			if ($this->idCompanyLogin > 0) {
				$params[] = $this->idCompanyLogin;
			}
			
			$sql = "SELECT nama_company, MONTH(tgl_penjualan) as bulan, SUM(total_qty) AS total
					FROM pos_penjualan 
					LEFT JOIN core_company USING(id_company)
					WHERE {$whereTenant} tgl_penjualan >= ? AND tgl_penjualan <= ? 
						AND status <> ? AND tenant_aktif = ?
					GROUP BY nama_company, MONTH(tgl_penjualan)";
			
			$params = array_merge($params, ["{$tahun}-01-01", "{$tahun}-12-31", 'void', 'Y']);
			$result[$tahun] = $this->db->query($sql, $params)->getResultArray();
		}
		return $result;
	}
	
	/**
	 * Mengambil daftar pelanggan dengan piutang terbesar
	 * 
	 * @return array Top 5 pelanggan dengan piutang terbesar
	 */
	public function getPiutangTerbesar() {
		$whereTenant = '';
		$params = [];
		
		if ($this->idCompanyLogin > 0) {
			$whereTenant = 'pos_penjualan.id_company = ? AND ';
			$params[] = $this->idCompanyLogin;
		}

		$sql = "SELECT id_customer, foto, nama_customer, SUM(kurang_bayar) AS total_kurang_bayar 
				FROM pos_penjualan
				LEFT JOIN pos_customer USING(id_customer)
				WHERE {$whereTenant} status = ?
				GROUP BY id_customer
				ORDER BY total_kurang_bayar DESC
				LIMIT 5";
		
		$params[] = 'kurang_bayar';
		return $this->db->query($sql, $params)->getResultArray();
	}
	
	/**
	 * Mengambil daftar pelanggan dengan pembelian terbesar dalam tahun tertentu
	 * 
	 * @param int $tahun Tahun yang akan dianalisa
	 * @return array Top 5 pelanggan dengan pembelian terbesar
	 */
	public function getPembelianPelangganTerbesar($tahun) {
		$tahun = (int) $tahun; // Sanitize
		$whereTenant = '';
		$params = [];
		
		if ($this->idCompanyLogin > 0) {
			$whereTenant = 'pos_penjualan.id_company = ? AND ';
			$params[] = $this->idCompanyLogin;
		}

		$sql = "SELECT id_customer, foto, nama_customer, SUM(neto) AS total_harga 
				FROM pos_penjualan
				LEFT JOIN pos_customer USING(id_customer)
				WHERE {$whereTenant} pos_penjualan.status <> ? 
					AND YEAR(tgl_penjualan) = ? 
				GROUP BY id_customer
				ORDER BY total_harga DESC
				LIMIT 5";
		
		$params = array_merge($params, ['void', $tahun]);
		return $this->db->query($sql, $params)->getResultArray();
	}
	
	/**
	 * Mengambil daftar item yang paling banyak terjual
	 * 
	 * @param int $tahun Tahun yang akan dianalisa
	 * @param int $tenant ID tenant (0 = semua tenant)
	 * @return array Top 5 item terlaris
	 */
	public function getItemTerjual($tahun, $tenant = 0) {
		$tahun = (int) $tahun; // Sanitize
		$tenant = (int) $tenant; // Sanitize
		$whereTenant = '';
		$params = [];

		if ($this->idCompanyLogin > 0) {
			$whereTenant = 'pos_penjualan.id_company = ? AND ';
			$params[] = $this->idCompanyLogin;
		} else {
			if ($tenant != 0) {
				$whereTenant = 'pos_penjualan.id_company = ? AND ';
				$params[] = $tenant;
			}
		}

		$sql = "SELECT id_barang, nama_barang, SUM(qty) AS jml
				FROM pos_penjualan_detail
				LEFT JOIN pos_penjualan USING(id_penjualan)
				LEFT JOIN pos_barang USING(id_barang)
				WHERE {$whereTenant} tgl_penjualan >= ? AND tgl_penjualan <= ? 
					AND status <> ? 
				GROUP BY id_barang
				ORDER BY jml DESC 
				LIMIT 5";
		
		$params = array_merge($params, ["{$tahun}-01-01", "{$tahun}-12-31", 'void']);
        $result = $this->db->query($sql, $params)->getResultArray();
		return $result;
	}
	
	/**
	 * Mengambil daftar kategori barang yang paling banyak terjual
	 * 
	 * @param int $tahun Tahun yang akan dianalisa
	 * @return array Top 5 kategori terlaris
	 */
	public function getKategoriTerjual($tahun) {
		$tahun = (int) $tahun; // Sanitize
		$whereTenant = '';
		$params = [];
		
		if ($this->idCompanyLogin > 0) {
			$whereTenant = 'pos_penjualan.id_company = ? AND ';
			$params[] = $this->idCompanyLogin;
		}

		$sql = "SELECT id_barang_kategori, nama_kategori, COUNT(id_barang) AS jml, SUM(harga_neto) AS nilai
				FROM pos_penjualan_detail
				LEFT JOIN pos_penjualan USING(id_penjualan)
				LEFT JOIN pos_barang USING(id_barang)
				LEFT JOIN pos_barang_kategori USING(id_barang_kategori)
				WHERE {$whereTenant} tgl_penjualan >= ? AND tgl_penjualan <= ? 
					AND status <> ? 
				GROUP BY id_barang_kategori
				ORDER BY nilai DESC 
				LIMIT 5";
		
		$params = array_merge($params, ["{$tahun}-01-01", "{$tahun}-12-31", 'void']);
        $result = $this->db->query($sql, $params)->getResultArray();
		return $result;
	}
		
	/**
	 * Mengambil daftar item/barang yang baru ditambahkan
	 * 
	 * @return array 5 item terbaru
	 */
	public function getItemTerbaru() {
		$whereTenant = '';
		$params = [];
		
		if ($this->idCompanyLogin > 0) {
			$whereTenant = 'pos_barang.id_company = ? AND ';
			$params[] = $this->idCompanyLogin;
		}

		$sql = "SELECT *, harga AS harga_jual 
				FROM pos_barang 
				LEFT JOIN pos_barang_harga USING(id_barang)
				LEFT JOIN pos_barang_image USING(id_barang)
				LEFT JOIN core_file_picker USING(id_file_picker)
				WHERE {$whereTenant} id_jenis_harga = ? AND urut = ?
				ORDER BY pos_barang.tgl_input DESC 
				LIMIT 5";
		
		$params = array_merge($params, [1, 1]);
		return $this->db->query($sql, $params)->getResultArray();
	}
	
	/**
	 * Mengambil daftar penjualan terbaru dalam tahun tertentu
	 * 
	 * @param int $tahun Tahun yang akan dianalisa
	 * @return array 50 penjualan terbaru
	 */
	public function penjualanTerbaru($tahun) {
		$tahun = (int) $tahun; // Sanitize
		$whereTenant = '';
		$params = [];
		
		if ($this->idCompanyLogin > 0) {
			$whereTenant = 'pos_penjualan.id_company = ? AND ';
			$params[] = $this->idCompanyLogin;
		}

		$sql = "SELECT no_invoice, nama_customer, SUM(qty) AS jml_barang, 
					MAX(neto) AS total_harga, tgl_invoice, MAX(tgl_penjualan) AS tgl_penjualan, 
					kurang_bayar, status
				FROM pos_penjualan 
				LEFT JOIN pos_penjualan_detail USING(id_penjualan)
				LEFT JOIN pos_customer USING(id_customer)
				WHERE {$whereTenant} YEAR(tgl_penjualan) = ? AND status <> ?
				GROUP BY id_penjualan
				ORDER BY tgl_penjualan DESC 
				LIMIT 50";
		
		$params = array_merge($params, [$tahun, 'void']);
		return $this->db->query($sql, $params)->getResultArray();
	}
	
	/**
	 * Menghitung total data penjualan terbesar
	 * 
	 * @param int $tahun Tahun yang akan dianalisa
	 * @param int $tenant ID tenant (0 = semua tenant)
	 * @return int Total jumlah data
	 */
	public function countAllDataPejualanTerbesar($tahun, $tenant = 0) {
		$tahun = (int) $tahun; // Sanitize
		$tenant = (int) $tenant; // Sanitize
		$whereTenant = '';
		$params = [];
		
		if ($this->idCompanyLogin > 0) {
			$whereTenant = 'pos_penjualan.id_company = ? AND ';
			$params[] = $this->idCompanyLogin;
			
			if ($tenant != 0) {
				$whereTenant = 'pos_penjualan.id_company = ? AND id_company = ? AND ';
				$params[] = $this->idCompanyLogin;
				$params[] = $tenant;
			}
		} else {
			if ($tenant != 0) {
				$whereTenant = 'id_company = ? AND ';
				$params[] = $tenant;
			}
		}

		$sql = "SELECT COUNT(*) as jml
				FROM (
					SELECT id_barang 
					FROM pos_penjualan_detail
					LEFT JOIN pos_penjualan USING(id_penjualan)
					WHERE {$whereTenant} tgl_penjualan >= ? AND tgl_penjualan <= ?
					GROUP BY id_barang
				) AS tabel";
		
		$params = array_merge($params, ["{$tahun}-01-01", "{$tahun}-12-31"]);
		$result = $this->db->query($sql, $params)->getRow();
		return $result->jml;
	}
	
	/**
	 * Mengambil list data penjualan terbesar dengan pagination dan search
	 * 
	 * @param int $tahun Tahun yang akan dianalisa
	 * @param int $tenant ID tenant (0 = semua tenant)
	 * @return array Data penjualan terbesar dengan total filtered
	 */
	public function getListDataPenjualanTerbesar($tahun, $tenant = 0) {
		$tahun = (int) $tahun; // Sanitize
		$tenant = (int) $tenant; // Sanitize
		$columns = $this->request->getPost('columns');

		$whereTenant = '';
		$params = [];
		$subParams = [];
		
		if ($this->idCompanyLogin > 0) {
			$whereTenant = 'pos_penjualan.id_company = ? AND ';
			$subParams[] = $this->idCompanyLogin;
		} else {
			if ($tenant != 0) {
				$whereTenant = 'pos_penjualan.id_company = ? AND ';
				$subParams[] = $tenant;
			}
		}

		// Search
		$where = ' WHERE 1=1 ';
		$subwhere = " WHERE {$whereTenant} pos_penjualan.status <> ? ";
		$subParams[] = 'void';
		
		$searchAll = @$this->request->getPost('search')['value'];
		if ($searchAll) {
			$whereCol = [];
			foreach ($columns as $val) {
				if (strpos($val['data'], 'ignore') !== false)
					continue;
				
				$whereCol[] = $val['data'] . ' LIKE ?';
				$params[] = "%{$searchAll}%";
			}
			if (!empty($whereCol)) {
				$where .= ' AND (' . join(' OR ', $whereCol) . ') ';
			}
		}
		
		// Order		
		$orderData = $this->request->getPost('order');
		$order = '';
		if (isset($_POST['columns'][$orderData[0]['column']]['data']) && 
			strpos($_POST['columns'][$orderData[0]['column']]['data'], 'ignore_search') === false) {
			$orderBy = $columns[$orderData[0]['column']]['data'] . ' ' . strtoupper($orderData[0]['dir']);
			$order = ' ORDER BY ' . $orderBy;
		}

		// Query Total Filtered
		$sqlTotalParams = array_merge($subParams, ["{$tahun}-01-01", "{$tahun}-12-31"]);
		$sql = "
				SELECT tabel_utama.*, COUNT(*) AS jml_data 
				FROM (
					SELECT tabel.*, ROUND(total_harga / total_penjualan * 100, 0) AS kontribusi 
					FROM (
						SELECT id_barang, pos_penjualan.id_company, 
							(SELECT nama_company FROM core_company WHERE core_company.id_company = pos_barang.id_company) as nama_company, 
							nama_barang, harga_satuan, COUNT(id_barang) AS jml_terjual, SUM(harga_neto) AS total_harga,
							(SELECT SUM(harga_neto) FROM pos_penjualan_detail LEFT JOIN pos_penjualan USING(id_penjualan) 
								WHERE pos_penjualan.status <> ? AND tgl_penjualan >= ? AND tgl_penjualan <= ?) AS total_penjualan
						FROM pos_penjualan_detail
						LEFT JOIN pos_penjualan USING(id_penjualan)
						LEFT JOIN pos_barang USING(id_barang)
						{$subwhere}
						GROUP BY id_barang
					) AS tabel
				) AS tabel_utama
				" . $where;
		
		$totalFilteredParams = array_merge($sqlTotalParams, ['void', "{$tahun}-01-01", "{$tahun}-12-31"], $params);
		$totalFiltered = $this->db->query($sql, $totalFilteredParams)->getRowArray()['jml_data'];
		
		// Query Data
		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		
		$sql = "
				SELECT * FROM (
					SELECT tabel.*, ROUND(total_harga / total_penjualan * 100, 0) AS kontribusi 
					FROM (
						SELECT id_barang, pos_penjualan.id_company, 
							(SELECT nama_company FROM core_company WHERE core_company.id_company = pos_barang.id_company) as nama_company, 
							nama_barang, harga_satuan, SUM(qty) AS jml_terjual, SUM(harga_neto) AS total_harga,
							(SELECT SUM(harga_neto) FROM pos_penjualan_detail LEFT JOIN pos_penjualan USING(id_penjualan) 
								WHERE pos_penjualan.status <> ? AND tgl_penjualan >= ? AND tgl_penjualan <= ?) AS total_penjualan
						FROM pos_penjualan_detail
						LEFT JOIN pos_penjualan USING(id_penjualan)
						LEFT JOIN pos_barang USING(id_barang)
						WHERE {$whereTenant} pos_penjualan.status <> ? AND tgl_penjualan >= ? AND tgl_penjualan <= ?
						GROUP BY id_barang
					) AS tabel
				) AS tabel_utama
				" . $where . $order . ' LIMIT ' . (int)$start . ', ' . (int)$length;
		
		$dataParams = array_merge(['void', "{$tahun}-01-01", "{$tahun}-12-31"], $subParams, ['void', "{$tahun}-01-01", "{$tahun}-12-31"], $params);
		$data = $this->db->query($sql, $dataParams)->getResultArray();
				
		return ['data' => $data, 'total_filtered' => $totalFiltered];
	}
	
	/**
	 * Menghitung total penjualan tempo
	 * 
	 * @param array $settingPiutang Setting konfigurasi piutang
	 * @return int Total jumlah penjualan tempo
	 */
	public function countAllDataPenjualanTempo($settingPiutang) {
		$whereTenant = '';
		$params = [];
		
		if ($this->idCompanyLogin > 0) {
			$whereTenant = 'pos_penjualan.id_company = ? AND ';
			$params[] = $this->idCompanyLogin;
		}
			
		$sql = "SELECT COUNT(*) AS jml 
				FROM pos_penjualan AS tabel 
				WHERE {$whereTenant} jenis_bayar = ? 
					AND status = ? 
					AND tgl_invoice >= ? 
					AND tgl_invoice <= ?
					" . $this->setWhereJatuhTempo($settingPiutang);
		
		$params = array_merge($params, ['tempo', 'kurang_bayar', $_GET['start_date'], $_GET['end_date']]);
		$result = $this->db->query($sql, $params)->getRow();
		return $result->jml;
	}
	
	/**
	 * Mengambil list penjualan tempo dengan pagination dan search
	 * 
	 * @param array $settingPiutang Setting konfigurasi piutang
	 * @return array Data penjualan tempo dengan total filtered
	 */
	public function getListPenjualanTempo($settingPiutang) 
	{
		$columns = $this->request->getPost('columns');

		$whereTenant = '';
		$params = [];
		
		if ($this->idCompanyLogin > 0) {
			$whereTenant = 'pos_penjualan.id_company = ? AND ';
			$params[] = $this->idCompanyLogin;
		}

		// Search
		$searchAll = @$this->request->getPost('search')['value'];
		$where = " WHERE {$whereTenant} jenis_bayar = ? AND status = ? AND tgl_invoice >= ? AND tgl_invoice <= ? ";
		$baseParams = array_merge($params, ['tempo', 'kurang_bayar', $_GET['start_date'], $_GET['end_date']]);
		$searchParams = [];
		
		if ($searchAll) {
			$whereCol = [];
			foreach ($columns as $val) {
				if (strpos($val['data'], 'ignore_search') !== false) 
					continue;
				
				if (strpos($val['data'], 'ignore') !== false)
					continue;
				
				$whereCol[] = $val['data'] . ' LIKE ?';
				$searchParams[] = "%{$searchAll}%";
			}
			if (!empty($whereCol)) {
				$where .= ' AND (' . join(' OR ', $whereCol) . ') ';
			}
		}
		
		// Query Total Filtered
		$sql = "SELECT COUNT(*) AS jml FROM pos_penjualan 
				LEFT JOIN pos_customer USING(id_customer)
				" . $where . ($this->setWhereJatuhTempo($settingPiutang));
		
		$totalParams = array_merge($baseParams, $searchParams);
		$data = $this->db->query($sql, $totalParams)->getRowArray();
		$totalFiltered = $data['jml'];
		
		// Order
		$orderData = $this->request->getPost('order');
		$order = '';
		if (isset($_POST['columns'][$orderData[0]['column']]['data']) && 
			strpos($_POST['columns'][$orderData[0]['column']]['data'], 'ignore_search') === false) {
			$orderBy = $columns[$orderData[0]['column']]['data'] . ' ' . strtoupper($orderData[0]['dir']);
			$order = ' ORDER BY ' . $orderBy;
		}

		$start = $this->request->getPost('start') ?: 0;
		$length = $this->request->getPost('length') ?: 10;
		
		// Query Data
		$sql = "SELECT * FROM pos_penjualan 
				LEFT JOIN pos_customer USING(id_customer)
				" . $where . ($this->setWhereJatuhTempo($settingPiutang)) . $order . ' LIMIT ' . (int)$start . ', ' . (int)$length;
		$data = $this->db->query($sql, $totalParams)->getResultArray();
		
		// Query Total
		$sql = "SELECT SUM(total_qty) AS total_qty, SUM(neto) AS total_neto 
				FROM pos_penjualan 
				LEFT JOIN pos_customer USING(id_customer)
				" . $where . ($this->setWhereJatuhTempo($settingPiutang));

		$total = $this->db->query($sql, $totalParams)->getRowArray();
		if (!$total) {
			$total = ['total_qty' => 0, 'total_neto' => 0];
		}
		
		foreach ($data as &$val) {
			$val['total'] = $total;
		}
	
		return ['data' => $data, 'total_filtered' => $totalFiltered];
	}
	
	/**
	 * Membuat kondisi WHERE untuk filter jatuh tempo piutang
	 * 
	 * @param array $settingPiutang Setting konfigurasi piutang
	 * @return string Kondisi WHERE untuk jatuh tempo
	 */
	private function setWhereJatuhTempo($settingPiutang) {
		$whereJatuhTempo = '';
		
		if (!empty($_GET['jatuh_tempo'])) {
			$jatuhTempo = $_GET['jatuh_tempo'];
			$piutangPeriode = (int) $settingPiutang['piutang_periode'];
			$notifikasiPeriode = (int) $settingPiutang['notifikasi_periode'];
			
			if ($jatuhTempo == 'akan_jatuh_tempo') {
				$minPeriode = $piutangPeriode - $notifikasiPeriode;
				$whereJatuhTempo = " AND DATEDIFF(NOW(), tgl_penjualan) > {$minPeriode} AND DATEDIFF(NOW(), tgl_penjualan) <= {$piutangPeriode}";
			} else if ($jatuhTempo == 'lewat_jatuh_tempo') {
				$whereJatuhTempo = " AND tgl_penjualan < DATE_SUB(NOW(), INTERVAL {$piutangPeriode} DAY)";
			}
		}
		return $whereJatuhTempo;
	}

	
}