<?php
/**
 * Base Model
 * Parent class untuk semua Model dengan fitur common
 * 
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Modules\Common\Models;
use App\Libraries\Auth;

class BaseModel extends \CodeIgniter\Model 
{
	protected $request;
	protected $session;
	protected $cache;
	protected $special_akses;
	protected $special_approver;
	private $auth;
	protected $user;
	
	public function __construct() {
		parent::__construct();
		
		$this->request = \Config\Services::request();
		$this->session = \Config\Services::session();
		$this->cache = \Config\Services::cache();
		$user = $this->session->get('user');
		$this->user = $user; // Assign user ke property
		
		// DECLARE SPECIAL ACCESS BY USER LOGIN START
		$this->special_akses = false;
		$this->explicit_access = isset($user['access_company'])?$user['access_company']:(isset($user['id_company'])?$user['id_company']:0);

		$akses = ($this->session->get('user'))?$this->session->get('user')['role']:null;
		if($akses){
			foreach($akses as $key => $v){
				if($key == 13){
					$this->special_akses = true;
				}
				if($key == 12){
					$this->special_approver = true;
				}
			}
		}
		// DECLARE SPECIAL ACCESS BY USER LOGIN END
		
		$this->auth = new \App\Libraries\Auth;
	}

	protected function buildCacheKey(string $scope, array $parts = []): string
	{
		return 'baseapp_' . $scope . '_' . md5(json_encode($parts));
	}

	protected function rememberCacheValue(string $key, callable $resolver, int $ttl = 180)
	{
		static $requestCache = [];

		if (array_key_exists($key, $requestCache)) {
			return $requestCache[$key];
		}

		$cached = $this->cache ? $this->cache->get($key) : null;
		if (is_array($cached) && array_key_exists('data', $cached)) {
			$requestCache[$key] = $cached['data'];
			return $requestCache[$key];
		}

		$value = $resolver();
		if ($this->cache) {
			$this->cache->save($key, ['data' => $value], $ttl);
		}

		$requestCache[$key] = $value;
		return $value;
	}

	protected function normalizeIntegerList(array $values): array
	{
		$result = [];
		foreach ($values as $value) {
			if (is_numeric($value)) {
				$result[] = (int) $value;
			}
		}

		return array_values(array_unique($result));
	}

	protected function buildWhereInClause(string $column, array $values): string
	{
		$normalizedValues = $this->normalizeIntegerList($values);
		if (!$normalizedValues) {
			return '1 = 0';
		}

		return $column . ' IN (' . implode(',', $normalizedValues) . ')';
	}

	protected function assertIdentifier(string $identifier): string
	{
		if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $identifier)) {
			throw new \InvalidArgumentException('Identifier tidak valid');
		}

		return $identifier;
	}
	
	public function checkRememberme() 
	{
		if ($this->session->get('logged_in')) 
		{
			return true; 
		}
		
		helper('cookie');
		$cookie_login = get_cookie('remember');
	
		if ($cookie_login) 
		{
			list($selector, $cookie_token) = explode(':', $cookie_login);

			$sql = 'SELECT * FROM core_user_token WHERE selector = ?';		
			$data = $this->db->query($sql, $selector)->getRowArray();
			
			if ($this->auth->validateToken($cookie_token, @$data['token'])) {
				
				if ($data['expires'] > date('Y-m-d H:i:s')) 
				{
					$user_detail = $this->getUserById($data['id_user']);
					$this->session->set('user', $user_detail);
					$this->session->set('logged_in', true);
				}
			}
		}
		
		return false;
	}
	
	public function getUserById($id_user = null, $array = false) {
		
		if (!$id_user) {
			if (!$this->user) {
				return false;
			}
			$id_user = $this->user['id_user'];
		}
		
		$query = $this->db->query('SELECT 
									a.*
								FROM core_user a 
								WHERE a.isDeleted = 0 AND id_user = ?', [$id_user]);
		$user = $query->getRowArray();
		
		$query = $this->db->query('SELECT 
									* 
								FROM core_user_role a
								LEFT JOIN core_role b USING(id_role) 
								LEFT JOIN core_module c USING(id_module) 
								WHERE id_user = ? 
								ORDER BY  nama_role', [$id_user]);
		$result = $query->getResultArray();
		
		foreach ($result as $val) {
			$user['role'][$val['id_role']] = $val;
		}
		if ($user) {
			if (!isset($user['role'])) {
				$user['role'] = [];
			}

			if (!$user['id_module']) {
				foreach ($user['role'] as $val) {
					$user['id_module'] = $val['id_module'];
				}
			}	
			
			$query = $this->db->query('SELECT * FROM core_module WHERE id_module = ?', [$user['id_module']]);
			$user['default_module'] = $query->getRowArray();
		}
		
		return $user;
	}

	public function getdataJHK($id_payroll, $id_karyawan)
	{
		helper('html');
		$jenis_cuti = jenis_cuti_config();

		$builder = $this->db->table('hrm_gpayroll a')
			->select([
				'a.id_payroll',
				'a.id_karyawan',
				'c.status_posting',
				'b.nik',
				'b.absen_id',
				'b.nama_ktp',
				'b.nama_jabatan',
				'a.template_skema_gaji',
				'a.template_gaji_pokok',
				'a.template_skema_uang_makan',
				'a.template_uang_makan',
				'a.template_skema_tunj_jabatan',
				'a.template_tunj_jabatan',
				'a.template_skema_tunj_operational',
				'a.template_tunj_operational',
				'a.template_skema_tunj_komunikasi',
				'a.template_tunj_komunikasi',
				'a.template_skema_tunj_kerajinan',
				'a.template_tunj_kerajinan',
				'a.template_absen_hari',
				'a.template_absen_jam',
				'COUNT(sub_1.tgl_generate) AS total_hari',
				"COUNT(CASE WHEN sub_1.status_masuk IN ('Normal','Telat','Dinas','Absensi','Keluar') THEN sub_1.id_karyawan END) AS absen_masuk",
				"COUNT(CASE WHEN sub_1.status_masuk = 'Kosong' THEN sub_1.id_karyawan END) AS absen_kosong",
				"COUNT(CASE WHEN sub_1.status_masuk = 'Libur' THEN sub_1.id_karyawan END) AS absen_libur",
				"COUNT(CASE WHEN sub_1.status_masuk = 'Cutber' THEN sub_1.id_karyawan END) AS absen_cutber",
				"COUNT(CASE WHEN sub_1.status_masuk = 'Telat' THEN sub_1.id_karyawan END) AS absen_telat",
				'COUNT(CASE WHEN sub_2.jenis_leave IN ("' . implode('","', $jenis_cuti) . '") THEN sub_1.id_karyawan END) AS absen_leave',
				"COUNT(CASE WHEN sub_2.jenis_leave = 'sakit' THEN sub_1.id_karyawan END) AS absen_sakit",
				"COUNT(CASE WHEN sub_2.jenis_leave IN ('dayoff','lainnya','keperluan') THEN sub_1.id_karyawan END) AS absen_izin",
				"COUNT(CASE WHEN sub_1.status_keluar = 'Overtime' THEN sub_1.id_karyawan END) AS absen_overtime",
				"SUM(CASE WHEN sub_1.status_keluar = 'Overtime' THEN sub_1.total_lembur END) AS absen_total_ot"
			])
			->join('hrm_employee_detail b', 'b.id_karyawan = a.id_karyawan', 'left')
			->join('hrm_payroll c', 'c.id_payroll = a.id_payroll', 'left')
			->join('hrm_dpayroll sub_1', 'sub_1.id_karyawan = a.id_karyawan AND sub_1.id_payroll = a.id_payroll', 'left')
			->join('hrm_leave sub_2', 'sub_2.id_leave = sub_1.id_leave AND sub_2.isDeleted = 0', 'left')
			->where('a.id_payroll', $id_payroll)
			->where('a.id_karyawan', $id_karyawan)
			->groupBy('a.id_karyawan');

		$result = $builder->get()->getResultArray();
		return $result;
	}

	public function getListJHKxls($id_payroll) {
		$sql = 'SELECT * 
				FROM hrm_post_payroll a
				WHERE a.isDeleted = 0 AND a.id_payroll = ?
				GROUP BY a.id_payroll, a.id_karyawan';
		$query = $this->db->query($sql, [(int) $id_payroll])->getResultArray();
		return $query;
	}

	public function getEmployeeById($id_user = null, $array = false) {
		
		if (!$id_user) {
			if (!$this->user) {
				return false;
			}
			$id_user = $this->user['id_user'];
		}
		
		$query = $this->db->query('
			SELECT 
				d.*, 
				bu.id_company,
				cu.nama as created_by, 
				cu2.nama as updated_by,
				(
					SELECT COALESCE(SUM(
					CASE
						WHEN tipe_transaksi = "cron"
						THEN total_cuti
						ELSE - total_cuti
					END
					), 0)
					FROM hrm_cron_cuti
					WHERE id_karyawan = d.id_karyawan
					AND isDeleted = 0
				) AS saldo_cuti
			FROM 
				hrm_employee_detail d 
			LEFT JOIN core_user bu on bu.id_user = d.id_user
			LEFT JOIN core_user cu on cu.id_user = d.id_user_input
			LEFT JOIN core_user cu2 on cu2.id_user = d.id_user_input
			WHERE d.isDeleted = 0 AND d.id_user = ?', [$id_user]);
		$user = $query->getRowArray();
		
		return $user;
	}
	
	public function getUserSetting() {
		$userId = (int) ($this->session->get('user')['id_user'] ?? 0);
		if ($userId <= 0) {
			return null;
		}

		// Setting layout user di-cache singkat karena dipakai di hampir semua
		// halaman login, layout, dan controller dasar.
		return $this->rememberCacheValue(
			$this->buildCacheKey('user_layout', [$userId]),
			function() use ($userId) {
				$result = $this->db->query('SELECT * FROM core_setting_user WHERE id_user = ? AND type = "layout"', [$userId])
							->getRow();
				
				if (!$result) {
					$query = $this->getAppLayoutSetting();
					$data = [];
					foreach ($query as $val) {
						$data[$val['param']] = $val['value'];
					}
					
					$result = new \StdClass;
					$result->param = json_encode($data);
				}

				return $result;
			},
			180
		);
	}
	
	public function getAppLayoutSetting() {
		// Layout default dipakai lintas halaman sehingga aman di-cache global.
		return $this->rememberCacheValue(
			$this->buildCacheKey('app_layout_setting'),
			function() {
				return $this->db->query('SELECT * FROM core_setting WHERE type="layout"')->getResultArray();
			},
			300
		);
	}

	public function getKaryawanID() {
		return $this->db->table('hrm_employee_detail a')
			->select('a.*, COALESCE(access_company, id_company) as id_company')
			->join('core_user b', 'b.id_user = a.id_user', 'left')
			->where('a.isDeleted', 0)
			->where('a.id_user', $this->user['id_user'])
			->get()
			->getRow();
	}
	
	public function getDefaultUserModule() {
		$roleIds = $this->normalizeIntegerList(array_keys($this->session->get('user')['role'] ?? []));
		if (!$roleIds) {
			return null;
		}

		return $this->rememberCacheValue(
			$this->buildCacheKey('default_user_module', $roleIds),
			function() use ($roleIds) {
				$sql = 'SELECT * 
						FROM core_role 
						LEFT JOIN core_module USING(id_module)
						WHERE ' . $this->buildWhereInClause('id_role', $roleIds);

				return $this->db->query($sql)->getRow();
			},
			300
		);
	}
	
	public function getModule($nama_module) {
		return $this->rememberCacheValue(
			$this->buildCacheKey('module_by_name', [$nama_module]),
			function() use ($nama_module) {
				$result = $this->db->query('SELECT * FROM core_module LEFT JOIN core_module_status USING(id_module_status) WHERE nama_module = ?', [$nama_module])
							->getRowArray();
				if (!$result) {
					$result = $this->getFallbackModuleDefinition($nama_module);
				}

				return $result;
			},
			300
		);
	}

	/**
	 * Registry fallback module statis untuk first setup.
	 *
	 * Data ini hanya dipakai jika module belum terdaftar di database agar
	 * controller tetap bisa berjalan dan menu tetap dapat muncul.
	 */
	public function getFallbackModules(): array
	{
		return [
			'db-synchronisation' => [
				'id_module' => 124,
				'nama_module' => 'db-synchronisation',
				'judul_module' => 'CORE - DB Synchronisation',
				'id_module_status' => 1,
				'nama_status' => 'Aktif',
				'login' => 'Y',
				'deskripsi' => 'Module untuk membandingkan schema database aktif dengan dump installer',
				'is_fallback_module' => true
			],
			'email-expiration' => [
				'id_module' => 125,
				'nama_module' => 'email-expiration',
				'judul_module' => 'Subscription Manager',
				'id_module_status' => 1,
				'nama_status' => 'Aktif',
				'login' => 'Y',
				'deskripsi' => 'Module untuk memantau subscription masa aktif akun email dan proses renew periodenya',
				'is_fallback_module' => true
			],
			'project' => [
				'id_module' => 126,
				'nama_module' => 'project',
				'judul_module' => 'Project List',
				'id_module_status' => 1,
				'nama_status' => 'Aktif',
				'login' => 'Y',
				'deskripsi' => 'Module untuk mengelola master project dan reporting token usage per project',
				'is_fallback_module' => true
			],
			'project-category' => [
				'id_module' => 127,
				'nama_module' => 'project-category',
				'judul_module' => 'Category List',
				'id_module_status' => 1,
				'nama_status' => 'Aktif',
				'login' => 'Y',
				'deskripsi' => 'Module untuk mengelola kategori project',
				'is_fallback_module' => true
			],
			'project-member' => [
				'id_module' => 128,
				'nama_module' => 'project-member',
				'judul_module' => 'Project Member',
				'id_module_status' => 1,
				'nama_status' => 'Aktif',
				'login' => 'Y',
				'deskripsi' => 'Module untuk mengelola anggota project',
				'is_fallback_module' => true
			],
			'task-management' => [
				'id_module' => 129,
				'nama_module' => 'task-management',
				'judul_module' => 'Task List',
				'id_module_status' => 1,
				'nama_status' => 'Aktif',
				'login' => 'Y',
				'deskripsi' => 'Module untuk mengelola task project dan log AI token usage',
				'is_fallback_module' => true
			],
			'forex-monitor' => [
				'id_module' => 130,
				'nama_module' => 'forex-monitor',
				'judul_module' => 'Forex Monitor',
				'id_module_status' => 1,
				'nama_status' => 'Aktif',
				'login' => 'Y',
				'deskripsi' => 'Module untuk monitoring realtime, histori OHLC, chart, dan alert GBP/JPY',
				'is_fallback_module' => true
			],
			'forex-prediction' => [
				'id_module' => 131,
				'nama_module' => 'forex-prediction',
				'judul_module' => 'Forex Prediction',
				'id_module_status' => 1,
				'nama_status' => 'Aktif',
				'login' => 'Y',
				'deskripsi' => 'Module untuk signal, market context, dan prediksi multi-metode GBP/JPY',
				'is_fallback_module' => true
			]
		];
	}

	/**
	 * Ambil definisi module fallback berdasarkan nama module.
	 */
	public function getFallbackModuleDefinition(string $namaModule)
	{
		$fallbackModules = $this->getFallbackModules();
		return $fallbackModules[$namaModule] ?? [];
	}

	/**
	 * Cek apakah module sudah benar-benar terdaftar di database.
	 */
	public function isModuleRegisteredInDatabase(string $namaModule): bool
	{
		return (bool) $this->db->table('core_module')
			->select('id_module')
			->where('nama_module', $namaModule)
			->get()
			->getRowArray();
	}

	/**
	 * Cek apakah menu module sudah terdaftar di database.
	 */
	public function isMenuRegisteredInDatabase(string $menuUrl): bool
	{
		return (bool) $this->db->table('core_menu')
			->select('id_menu')
			->where('url', $menuUrl)
			->get()
			->getRowArray();
	}

	/**
	 * Ambil fallback menu statis untuk module yang harus tetap usable saat
	 * record core_menu/core_module belum tersedia.
	 */
	public function getFallbackMenuRows(): array
	{
		return [
			'db-synchronisation' => [
				'menu' => [
					'id_menu' => 172,
					'nama_menu' => 'DB Synchronisation',
					'id_menu_kategori' => 1,
					'class' => '',
					'url' => 'db-synchronisation',
					'id_module' => 124,
					'nama_module' => 'db-synchronisation',
					'judul_module' => 'CORE - DB Synchronisation',
					'id_parent' => null,
					'aktif' => 1,
					'new' => 0,
					'urut' => 4,
					'highlight' => 0,
					'depth' => 0,
					'is_fallback_menu' => true
				],
				'parent' => [
					'id_menu' => 13,
					'nama_menu' => 'Administrator',
					'id_menu_kategori' => 1,
					'class' => 'far fa-sun',
					'url' => '#',
					'id_module' => null,
					'nama_module' => null,
					'judul_module' => null,
					'id_parent' => null,
					'aktif' => 1,
					'new' => 0,
					'urut' => 2,
					'highlight' => 0,
					'depth' => 0,
					'is_fallback_menu' => true
				],
				'category' => [
					'id_menu_kategori' => 1,
					'nama_kategori' => 'CORE - SYSTEM CONFIG',
					'deskripsi' => '',
					'aktif' => 'Y',
					'tampil' => 'Y',
					'urut' => 1,
					'icon' => 'far fa-sun'
				]
			],
			'email-expiration' => [
				'menu' => [
					'id_menu' => 173,
					'nama_menu' => 'Subscription Manager',
					'id_menu_kategori' => 1,
					'class' => '',
					'url' => 'email-expiration',
					'id_module' => 125,
					'nama_module' => 'email-expiration',
					'judul_module' => 'Subscription Manager',
					'id_parent' => null,
					'aktif' => 1,
					'new' => 0,
					'urut' => 3,
					'highlight' => 0,
					'depth' => 0,
					'is_fallback_menu' => true
				],
				'parent' => [
					'id_menu' => 18,
					'nama_menu' => 'Security Monitor',
					'id_menu_kategori' => 1,
					'class' => 'fas fa-shield-halved',
					'url' => 'securitymonitor',
					'id_module' => 121,
					'nama_module' => 'securitymonitor',
					'judul_module' => 'Security Monitor',
					'id_parent' => null,
					'aktif' => 1,
					'new' => 0,
					'urut' => 2,
					'highlight' => 0,
					'depth' => 0,
					'is_fallback_menu' => true
				],
				'category' => [
					'id_menu_kategori' => 1,
					'nama_kategori' => 'CORE - SYSTEM CONFIG',
					'deskripsi' => '',
					'aktif' => 'Y',
					'tampil' => 'Y',
					'urut' => 1,
					'icon' => 'far fa-sun'
				]
			],
			'project' => [
				'menu' => [
					'id_menu' => 175,
					'nama_menu' => 'Project List',
					'id_menu_kategori' => 1,
					'class' => '',
					'url' => 'project',
					'id_module' => 126,
					'nama_module' => 'project',
					'judul_module' => 'Project List',
					'id_parent' => 174,
					'aktif' => 1,
					'new' => 0,
					'urut' => 1,
					'highlight' => 0,
					'depth' => 0,
					'is_fallback_menu' => true
				],
				'parent' => [
					'id_menu' => 174,
					'nama_menu' => 'Project',
					'id_menu_kategori' => 1,
					'class' => 'fas fa-diagram-project',
					'url' => '#',
					'id_module' => null,
					'nama_module' => null,
					'judul_module' => null,
					'id_parent' => null,
					'aktif' => 1,
					'new' => 0,
					'urut' => 3,
					'highlight' => 0,
					'depth' => 0,
					'is_fallback_menu' => true
				],
				'category' => [
					'id_menu_kategori' => 1,
					'nama_kategori' => 'CORE - SYSTEM CONFIG',
					'deskripsi' => '',
					'aktif' => 'Y',
					'tampil' => 'Y',
					'urut' => 1,
					'icon' => 'far fa-sun'
				]
			],
			'project-category' => [
				'menu' => [
					'id_menu' => 176,
					'nama_menu' => 'Category List',
					'id_menu_kategori' => 1,
					'class' => '',
					'url' => 'project-category',
					'id_module' => 127,
					'nama_module' => 'project-category',
					'judul_module' => 'Category List',
					'id_parent' => 174,
					'aktif' => 1,
					'new' => 0,
					'urut' => 2,
					'highlight' => 0,
					'depth' => 0,
					'is_fallback_menu' => true
				],
				'parent' => [
					'id_menu' => 174,
					'nama_menu' => 'Project',
					'id_menu_kategori' => 1,
					'class' => 'fas fa-diagram-project',
					'url' => '#',
					'id_module' => null,
					'nama_module' => null,
					'judul_module' => null,
					'id_parent' => null,
					'aktif' => 1,
					'new' => 0,
					'urut' => 3,
					'highlight' => 0,
					'depth' => 0,
					'is_fallback_menu' => true
				],
				'category' => [
					'id_menu_kategori' => 1,
					'nama_kategori' => 'CORE - SYSTEM CONFIG',
					'deskripsi' => '',
					'aktif' => 'Y',
					'tampil' => 'Y',
					'urut' => 1,
					'icon' => 'far fa-sun'
				]
			],
			'task-management' => [
				'menu' => [
					'id_menu' => 177,
					'nama_menu' => 'Task List',
					'id_menu_kategori' => 1,
					'class' => '',
					'url' => 'task-management',
					'id_module' => 129,
					'nama_module' => 'task-management',
					'judul_module' => 'Task List',
					'id_parent' => 174,
					'aktif' => 1,
					'new' => 0,
					'urut' => 3,
					'highlight' => 0,
					'depth' => 0,
					'is_fallback_menu' => true
				],
				'parent' => [
					'id_menu' => 174,
					'nama_menu' => 'Project',
					'id_menu_kategori' => 1,
					'class' => 'fas fa-diagram-project',
					'url' => '#',
					'id_module' => null,
					'nama_module' => null,
					'judul_module' => null,
					'id_parent' => null,
					'aktif' => 1,
					'new' => 0,
					'urut' => 3,
					'highlight' => 0,
					'depth' => 0,
					'is_fallback_menu' => true
				],
				'category' => [
					'id_menu_kategori' => 1,
					'nama_kategori' => 'CORE - SYSTEM CONFIG',
					'deskripsi' => '',
					'aktif' => 'Y',
					'tampil' => 'Y',
					'urut' => 1,
					'icon' => 'far fa-sun'
				]
			],
			'forex-monitor' => [
				'menu' => [
					'id_menu' => 179,
					'nama_menu' => 'Forex Monitor',
					'id_menu_kategori' => 1,
					'class' => '',
					'url' => 'forex-monitor',
					'id_module' => 130,
					'nama_module' => 'forex-monitor',
					'judul_module' => 'Forex Monitor',
					'id_parent' => 178,
					'aktif' => 1,
					'new' => 0,
					'urut' => 1,
					'highlight' => 0,
					'depth' => 0,
					'is_fallback_menu' => true
				],
				'parent' => [
					'id_menu' => 178,
					'nama_menu' => 'Forex',
					'id_menu_kategori' => 1,
					'class' => 'fas fa-chart-line',
					'url' => '#',
					'id_module' => null,
					'nama_module' => null,
					'judul_module' => null,
					'id_parent' => null,
					'aktif' => 1,
					'new' => 0,
					'urut' => 6,
					'highlight' => 0,
					'depth' => 0,
					'is_fallback_menu' => true
				],
				'category' => [
					'id_menu_kategori' => 1,
					'nama_kategori' => 'CORE - SYSTEM CONFIG',
					'deskripsi' => '',
					'aktif' => 'Y',
					'tampil' => 'Y',
					'urut' => 1,
					'icon' => 'far fa-sun'
				]
			],
			'forex-prediction' => [
				'menu' => [
					'id_menu' => 180,
					'nama_menu' => 'Forex Prediction',
					'id_menu_kategori' => 1,
					'class' => '',
					'url' => 'forex-prediction',
					'id_module' => 131,
					'nama_module' => 'forex-prediction',
					'judul_module' => 'Forex Prediction',
					'id_parent' => 178,
					'aktif' => 1,
					'new' => 0,
					'urut' => 2,
					'highlight' => 0,
					'depth' => 0,
					'is_fallback_menu' => true
				],
				'parent' => [
					'id_menu' => 178,
					'nama_menu' => 'Forex',
					'id_menu_kategori' => 1,
					'class' => 'fas fa-chart-line',
					'url' => '#',
					'id_module' => null,
					'nama_module' => null,
					'judul_module' => null,
					'id_parent' => null,
					'aktif' => 1,
					'new' => 0,
					'urut' => 6,
					'highlight' => 0,
					'depth' => 0,
					'is_fallback_menu' => true
				],
				'category' => [
					'id_menu_kategori' => 1,
					'nama_kategori' => 'CORE - SYSTEM CONFIG',
					'deskripsi' => '',
					'aktif' => 'Y',
					'tampil' => 'Y',
					'urut' => 1,
					'icon' => 'far fa-sun'
				]
			]
		];
	}

	/**
	 * Tambah menu fallback ke hasil query menu database tanpa mengubah menu yang
	 * sudah ada. Fallback hanya muncul untuk role Administrator.
	 */
	protected function mergeFallbackMenu(array $result, string $current_module = ''): array
	{
		$roleIds = $this->normalizeIntegerList(array_keys($this->session->get('user')['role'] ?? []));
		if (!in_array(1, $roleIds, true)) {
			return $result;
		}

		$fallbackRows = $this->getFallbackMenuRows();
		foreach ($fallbackRows as $namaModule => $fallback) {
			$moduleRegistered = $this->isModuleRegisteredInDatabase($namaModule);
			$menuRegistered = $this->isMenuRegisteredInDatabase($fallback['menu']['url']);
			$menuExists = false;
			foreach ($result as $kategoriData) {
				foreach ($kategoriData['menu'] as $menuRow) {
					if (($menuRow['nama_module'] ?? '') === $namaModule || ($menuRow['url'] ?? '') === $fallback['menu']['url']) {
						$menuExists = true;
						break 2;
					}
				}
			}

			if (($moduleRegistered && $menuRegistered) || $menuExists) {
				continue;
			}

			$categoryId = $fallback['category']['id_menu_kategori'];
			if (!isset($result[$categoryId])) {
				$result[$categoryId] = [
					'kategori' => $fallback['category'],
					'menu' => []
				];
			}

			$menuRows = &$result[$categoryId]['menu'];
			if (!isset($menuRows[$fallback['parent']['id_menu']])) {
				$menuRows[$fallback['parent']['id_menu']] = $fallback['parent'];
			}

			if ($current_module === $namaModule) {
				$menuRows[$fallback['parent']['id_menu']]['highlight'] = 1;
				$menuRows[$fallback['menu']['id_menu']]['highlight'] = 1;
			}

			$menuRows[$fallback['menu']['id_menu']] = array_merge(
				$fallback['menu'],
				['highlight' => $current_module === $namaModule ? 1 : 0]
			);
		}

		return $result;
	}
	
	public function getMenu($current_module = '') {		
		$roleIds = $this->normalizeIntegerList(array_keys($this->session->get('user')['role'] ?? []));
		if (!$roleIds) {
			return [];
		}

		// Struktur menu user di-cache berdasarkan role + module aktif karena
		// tree menu dan highlight-nya dipakai di seluruh halaman admin.
		return $this->rememberCacheValue(
			$this->buildCacheKey('menu_tree', [$roleIds, $current_module]),
			function() use ($roleIds, $current_module) {
				$sql = 'SELECT * FROM core_menu 
							LEFT JOIN core_menu_role USING (id_menu) 
							LEFT JOIN core_module USING (id_module)
							LEFT JOIN core_menu_kategori USING(id_menu_kategori)
						WHERE core_menu_kategori.aktif = "Y" AND core_menu.aktif = 1 AND ( ' . $this->buildWhereInClause('id_role', $roleIds) . ' )
						ORDER BY core_menu_kategori.urut, core_menu.urut';				
				$query_result = $this->db->query($sql)->getResultArray();
				
				$current_id = '';
				$menu = [];
				foreach ($query_result as $val) 
				{
					$menu[$val['id_menu']] = $val;
					$menu[$val['id_menu']]['highlight'] = 0;
					$menu[$val['id_menu']]['depth'] = 0;

					if ($current_module == $val['nama_module']) {				
						$current_id = $val['id_menu'];
						$menu[$val['id_menu']]['highlight'] = 1;
					}
				}
			
				if ($current_id) {
					$this->menuCurrent($menu, $current_id);
				}
				
				$menu_kategori = [];
				foreach ($menu as $id_menu => $val) {
					if (!$id_menu) {
						continue;
					}
					
					$menu_kategori[$val['id_menu_kategori']][$val['id_menu']] = $val;
				}

				$sql = 'SELECT * FROM core_menu_kategori WHERE aktif = "Y" ORDER BY urut';
				$query_result = $this->db->query($sql)->getResultArray();
				$result = [];
				foreach ($query_result as $val) {
					if (key_exists($val['id_menu_kategori'], $menu_kategori)) {
						$result[$val['id_menu_kategori']] = [ 'kategori' => $val, 'menu' => $menu_kategori[$val['id_menu_kategori']] ];
					}
				}

				return $this->mergeFallbackMenu($result, $current_module);
			},
			300
		);
	}
	
	// Highlight child and parent
	private function menuCurrent( &$result, $current_id) 
	{
		// $parent = $result[$current_id]['id_parent'];
		
		// $result[$parent]['highlight'] = 1; // Highlight menu parent
		// if (@$result[$parent]['id_parent']) {
		// 	$this->menuCurrent($result, $parent);
		// }

		// Validate input and ensure the current item exists
		if (empty($current_id) || !is_array($result) || !isset($result[$current_id])) {
			return;
		}

		$parent = isset($result[$current_id]['id_parent']) ? $result[$current_id]['id_parent'] : null;

		// If parent is not set or parent item does not exist in menu list, stop
		if (empty($parent) || !isset($result[$parent])) {
			return;
		}
		
		$result[$parent]['highlight'] = 1; // Highlight menu parent

		// Continue up the chain if the parent has its own parent
		if (!empty($result[$parent]['id_parent'])) {
			$this->menuCurrent($result, $parent);
		}
	}
	
	public function getModulePermission($id_module) {
		return $this->rememberCacheValue(
			$this->buildCacheKey('module_permission_rows', [(int) $id_module]),
			function() use ($id_module) {
				$sql = 'SELECT * FROM core_module_permission LEFT JOIN core_role_module_permission USING (id_module_permission) WHERE id_module = ?';
				return $this->db->query($sql, [$id_module])->getResultArray();
			},
			180
		);
	}
	
	public function getAllModulePermission($id_user) {
		return $this->rememberCacheValue(
			$this->buildCacheKey('all_permission_rows', [(int) $id_user]),
			function() use ($id_user) {
				$sql = 'SELECT * FROM core_role_module_permission
						LEFT JOIN core_module_permission USING(id_module_permission)
						LEFT JOIN core_module USING(id_module)
						LEFT JOIN core_user_role USING(id_role)
						WHERE id_user = ?';
				return $this->db->query($sql, $id_user)->getResultArray();
			},
			180
		);
	}

	public function getModulePermissionMap(int $idModule): array
	{
		return $this->rememberCacheValue(
			$this->buildCacheKey('module_permission_map', [$idModule]),
			function() use ($idModule) {
				$map = [];
				foreach ($this->getModulePermission($idModule) as $val) {
					$namaPermission = $val['nama_permission'] ?: 'null';
					$map[$val['id_role']][$namaPermission] = $namaPermission;
				}
				return $map;
			},
			180
		);
	}

	public function getAllModulePermissionMap(int $idUser): array
	{
		return $this->rememberCacheValue(
			$this->buildCacheKey('all_permission_map', [$idUser]),
			function() use ($idUser) {
				$map = [];
				foreach ($this->getAllModulePermission($idUser) as $val) {
					$map[$val['id_module']][$val['nama_permission']] = $val;
				}
				return $map;
			},
			180
		);
	}
	
	/* public function getModuleRole($id_module) {
		 $result = $this->db->query('SELECT * FROM module_role WHERE id_module = ? ', $id_module)->getResultArray();
		 return $result;
	} */

	public function validateFormToken($session_name = null, $post_name = 'form_token') {				

		$formTokenValue = (string) $this->request->getPost($post_name);
		if ($formTokenValue === '' || strpos($formTokenValue, ':') === false) {
			return false;
		}

		$form_token = explode(':', $formTokenValue, 2);
		
		$form_selector = $form_token[0];
		$sess_token = $this->session->get('token');
		if ($session_name)
			$sess_token = $sess_token[$session_name];

		if (!is_array($sess_token)) {
			return false;
		}
	
		if (!key_exists($form_selector, $sess_token))
				return false;
		
		try {
			$equal = $this->auth->validateToken($sess_token[$form_selector], $form_token[1]);

			return $equal;
		} catch (\Exception $e) {
			return false;
		}
		
		return false;
	}
	
	// For role check BaseController->cekHakAkses
	public function getDataById($table, $column, $id) {
		$table = $this->assertIdentifier($table);
		$column = $this->assertIdentifier($column);
		$sql = 'SELECT * FROM ' . $table . ' WHERE ' . $column . ' = ?';
		return $this->db->query($sql, $id)->getResultArray();
	}
	
	public function checkUser($username) 
	{
		$user = $this->db->table('core_user')
                    ->where('isDeleted', 0)
                    ->where('username', $username)
                    ->get()
                    ->getRowArray();

		if (!$user) {
			return null;
		}

		return $this->getUserById($user['id_user']);
	}

	public function checkUsername($username, $id_user = null) {
		$sql = "SELECT COUNT(*) as jml FROM core_user 
            WHERE isDeleted = 0 
            AND username = ?";
    
		$params = [$username];
		
		if ($id_user !== null) {
			$sql .= " AND id_user != ?";
			$params[] = $id_user;
		}
		
		return $this->db->query($sql, $params)->getRow()->jml;
	}
	
	public function saveDownUpLog($post) 
	{
		$data = [
			'tgl_down' => $post['downtime'] ?? null,
			'tgl_up' => $post['uptime'] ?? null
		];
		$this->db->table('offline_log')->insert($data);
	}
	
	public function getSettingAplikasi() {
		return $this->rememberCacheValue(
			$this->buildCacheKey('setting_aplikasi'),
			function() {
				$sql = 'SELECT * FROM core_setting WHERE type="app" OR type="config" OR type="pajak"';
				$query = $this->db->query($sql)->getResultArray();
				$settingAplikasi = [];
				
				foreach($query as $val) {
					$settingAplikasi[$val['param']] = $val['value'];
				}
				return $settingAplikasi;
			},
			300
		);
	}
	
	public function getSettingRegistrasi() {
		return $this->rememberCacheValue(
			$this->buildCacheKey('setting_registrasi'),
			function() {
				$sql = 'SELECT * FROM core_setting WHERE type="register"';
				$query = $this->db->query($sql)->getResultArray();
				$setting_register = [];
				foreach($query as $val) {
					$setting_register[$val['param']] = $val['value'];
				}
				return $setting_register;
			},
			300
		);
	}
	
	public function getIdentitas() {
		$companyId = (int) ($this->session->get('user')['id_company'] ?? 0);
		return $this->rememberCacheValue(
			$this->buildCacheKey('identitas', [$companyId]),
			function() use ($companyId) {
				$sql = 'SELECT * FROM core_identitas WHERE id_company = ?';
				return $this->db->query($sql, [$companyId])->getRowArray();
			},
			300
		);
	}
	
	public function getSetting($type) {
		return $this->rememberCacheValue(
			$this->buildCacheKey('setting_type', [(string) $type]),
			function() use ($type) {
				$sql = 'SELECT * FROM core_setting WHERE type = ?'; 
				return $this->db->query($sql, $type)->getResultArray();
			},
			300
		);
	}

	public function deleteAuthCookiePeriode($id_user) 
	{
		$this->db->table('core_user_token')->delete(['action' => 'remember', 'id_user' => $id_user]);
		setcookie('remember', '', time() - 360000, '/');	
	}

	//getdataKaryawan
	public function getKaryawan() {
		$sql = '
			SELECT d.*, e.is_shifting
			FROM core_user a
			LEFT JOIN core_company b USING(id_company)
			LEFT JOIN core_user_role c USING(id_user)
			LEFT JOIN hrm_employee_detail d USING(id_user)
			LEFT JOIN hrm_jadwal e USING(id_jadwal)
			LEFT JOIN hrm_department f USING(id_department)
			LEFT JOIN core_role g ON c.id_role = g.id_role
			LEFT JOIN hrm_resign h ON d.id_karyawan = h.id_karyawan AND h.isDeleted = 0
			WHERE 
				d.isDeleted = 0 
				AND (h.tgl_resign IS NULL OR h.tgl_resign >= ?) 
				AND b.sistem = "hrms"
			GROUP BY a.id_user
		';

		$result = $this->db->query($sql, [date("Y-m-d")])->getResultArray();
		return $result;
	}

	public function getDepartemen() {
		$sql = '
			SELECT * FROM hrm_department where isDeleted = 0
		';

		$result = $this->db->query($sql)->getResultArray();
		return $result;
	}

}
