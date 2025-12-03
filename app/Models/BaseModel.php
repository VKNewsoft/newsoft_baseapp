<?php
/**
 * Base Model
 * Parent class untuk semua Model dengan fitur common
 * 
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Models;
use App\Libraries\Auth;

class BaseModel extends \CodeIgniter\Model 
{
	protected $request;
	protected $session;
	protected $special_akses;
	protected $special_approver;
	private $auth;
	protected $user;
	
	public function __construct() {
		parent::__construct();
		
		$this->request = \Config\Services::request();
		$this->session = \Config\Services::session();
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
			if ( !$user['id_module'] ) {
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
		$where = " WHERE a.isDeleted = 0 AND a.id_payroll = $id_payroll GROUP BY a.id_payroll,a.id_karyawan";
		$query = $this->db->query('SELECT * FROM hrm_post_payroll a ' . $where)->getResultArray();
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
		
		$result = $this->db->query('SELECT * FROM core_setting_user WHERE id_user = ? AND type = "layout"', [$this->session->get('user')['id_user']])
						->getRow();
		
		if (!$result) {
			$query = $this->db->query('SELECT * FROM core_setting WHERE type="layout"')
						->getResultArray();
			
			foreach ($query as $val) {
				$data[$val['param']] = $val['value'];
			}
			
			$result = new \StdClass;
			$result->param = json_encode($data);
		}
		return $result;
	}
	
	public function getAppLayoutSetting() {
		$result = $this->db->query('SELECT * FROM core_setting WHERE type="layout"')->getResultArray();
		return $result;
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
		
		$query = $this->db->query('SELECT * 
							FROM core_role 
							LEFT JOIN core_module USING(id_module)
							WHERE id_role IN (' . join(',', array_keys($this->session->get('user')['role'])) . ')'
							)
						->getRow();
		return $query;
	}
	
	public function getModule($nama_module) {
		$result = $this->db->query('SELECT * FROM core_module LEFT JOIN core_module_status USING(id_module_status) WHERE nama_module = ?', [$nama_module])
						->getRowArray();
		// print_r($this->db->getLastQuery());die;
		return $result;
	}
	
	public function getMenu($current_module = '') {		
		// Menu
		$sql = 'SELECT * FROM core_menu 
					LEFT JOIN core_menu_role USING (id_menu) 
					LEFT JOIN core_module USING (id_module)
					LEFT JOIN core_menu_kategori USING(id_menu_kategori)
				WHERE core_menu_kategori.aktif = "Y" AND core_menu.aktif = 1 AND ( id_role IN ( ' . join(',', array_keys($_SESSION['user']['role'])) . ') )
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
	
		// dd($query_result, $menu, $current_module, $current_id);
		if ($current_id) {
			$this->menuCurrent($menu, $current_id);
		}
		
		$menu_kategori = [];
		foreach ($menu as $id_menu => $val) {
			if (!$id_menu)
				continue;
			
			$menu_kategori[$val['id_menu_kategori']][$val['id_menu']] = $val;
		}

		// Kategori
		$sql = 'SELECT * FROM core_menu_kategori WHERE aktif = "Y" ORDER BY urut';
		$query_result = $this->db->query($sql)->getResultArray();
		$result = [];
		foreach ($query_result as $val) {
			if (key_exists($val['id_menu_kategori'], $menu_kategori)) {
				$result[$val['id_menu_kategori']] = [ 'kategori' => $val, 'menu' => $menu_kategori[$val['id_menu_kategori']] ];
			}
		}		
		// echo '<pre>'; print_r($result); die;
		return $result;
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
		$sql = 'SELECT * FROM core_module_permission LEFT JOIN core_role_module_permission USING (id_module_permission) WHERE id_module = ?';
		
		$result = $this->db->query($sql, [$id_module])->getResultArray();
		return $result;
	}
	
	public function getAllModulePermission($id_user) {
		$sql = 'SELECT * FROM core_role_module_permission
				LEFT JOIN core_module_permission USING(id_module_permission)
				LEFT JOIN core_module USING(id_module)
				LEFT JOIN core_user_role USING(id_role)
				WHERE id_user = ?';
						
		$result = $this->db->query($sql, $id_user)->getResultArray();
		return $result;
	}
	
	/* public function getModuleRole($id_module) {
		 $result = $this->db->query('SELECT * FROM module_role WHERE id_module = ? ', $id_module)->getResultArray();
		 return $result;
	} */

	public function validateFormToken($session_name = null, $post_name = 'form_token') {				

		$form_token = explode (':', $this->request->getPost($post_name));
		
		$form_selector = $form_token[0];
		$sess_token = $this->session->get('token');
		if ($session_name)
			$sess_token = $sess_token[$session_name];
	
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
			'tgl_down' => $post['downtime'],
			'tgl_up' => $post['uptime']
		];
		$this->db->table('offline_log')->insert($data);
	}
	
	public function getSettingAplikasi() {
		$sql = 'SELECT * FROM core_setting WHERE type="app" OR type="config" OR type="pajak"';
		$query = $this->db->query($sql)->getResultArray();
		
		foreach($query as $val) {
			$settingAplikasi[$val['param']] = $val['value'];
		}
		return $settingAplikasi;
	}
	
	public function getSettingRegistrasi() {
		$sql = 'SELECT * FROM core_setting WHERE type="register"';
		$query = $this->db->query($sql)->getResultArray();
		foreach($query as $val) {
			$setting_register[$val['param']] = $val['value'];
		}
		return $setting_register;
	}
	
	public function getIdentitas() {
		// $sql = 'SELECT * FROM core_identitas 
		// 		LEFT JOIN core_wilayah_kelurahan USING(id_wilayah_kelurahan)
		// 		LEFT JOIN core_wilayah_kecamatan USING(id_wilayah_kecamatan)
		// 		LEFT JOIN core_wilayah_kabupaten USING(id_wilayah_kabupaten)
		// 		LEFT JOIN core_wilayah_propinsi USING(id_wilayah_propinsi)';
		// return $this->db->query($sql)->getRowArray();
		$sql = 'SELECT * FROM core_identitas where id_company = "'.$this->session->user['id_company'].'"';
		$result = $this->db->query($sql)->getRowArray();
		return $result;
	}
	
	public function getSetting($type) {
		$sql = 'SELECT * FROM core_setting WHERE type = ?'; 
		return $this->db->query($sql, $type)->getResultArray();
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
				AND (h.tgl_resign IS NULL OR h.tgl_resign >= "' . date("Y-m-d") . '") 
				AND b.sistem = "hrms"
			GROUP BY a.id_user
		';

		$result = $this->db->query($sql)->getResultArray();
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