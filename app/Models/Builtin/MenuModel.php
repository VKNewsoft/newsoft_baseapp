<?php
/**
 * Model Menu
 * 
 * Mengelola menu navigasi aplikasi termasuk kategori menu, role-based access,
 * dan struktur hierarki menu (parent-child).
 * 
 * @package    App\Models\Builtin
 * @author     Newsoft Developer
 * @copyright  2020-2023
 */

namespace App\Models\Builtin;

class MenuModel extends \App\Models\BaseModel
{
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}
	
	/**
	 * Mendapatkan daftar menu dari database berdasarkan status aktif dan role
	 * 
	 * @param string $aktif Status aktif menu ('all' atau 1/0)
	 * @param bool $showAll Tampilkan semua menu tanpa filter role
	 * @return array Daftar menu dengan informasi highlight dan depth
	 */
	public function getMenuDb($aktif = 'all', $showAll = false) {
		global $app_module;
		
		$result = [];
		$namaModule = $app_module['nama_module'] ?? '';
		
		// Build query dengan Query Builder untuk keamanan
		$builder = $this->db->table('core_menu')
			->select('core_menu.*, core_menu_role.*, core_module.*')
			->join('core_menu_role', 'core_menu_role.id_menu = core_menu.id_menu', 'left')
			->join('core_module', 'core_module.id_module = core_menu.id_module', 'left');
		
		// Filter berdasarkan status aktif
		if ($aktif != 'all') {
			$builder->where('core_menu.aktif', (int) $aktif);
		}
		
		// Filter berdasarkan role user
		if (!$showAll) {
			$userSession = session()->get('user');
			$idRole = $userSession['id_role'] ?? 0;
			$builder->where('core_menu_role.id_role', $idRole);
		}
		
		$query = $builder->orderBy('core_menu.urut', 'ASC')->get()->getResultArray();
		
		$currentId = '';
		foreach ($query as $row) {
			$result[$row['id_menu']] = $row;
			$result[$row['id_menu']]['highlight'] = 0;
			$result[$row['id_menu']]['depth'] = 0;

			// Tandai menu yang sedang aktif
			if ($namaModule == $row['nama_module']) {
				$currentId = $row['id_menu'];
				$result[$row['id_menu']]['highlight'] = 1;
			}
		}
		
		// Set highlight untuk menu parent jika ada
		if ($currentId && function_exists('menu_current')) {
			menu_current($result, $currentId);
		}
		
		return $result;
	}
	
	/**
	 * Mendapatkan menu berdasarkan kategori
	 * 
	 * @param int|null $idMenuKategori ID kategori menu (null untuk menu tanpa kategori)
	 * @return array Daftar menu dalam kategori tersebut
	 */
	public function getMenuByKategori($idMenuKategori) {
		$builder = $this->db->table('core_menu')
			->select('core_menu.*, core_menu_role.*, core_module.*')
			->join('core_menu_role', 'core_menu_role.id_menu = core_menu.id_menu', 'left')
			->join('core_module', 'core_module.id_module = core_menu.id_module', 'left');
		
		// Filter berdasarkan kategori
		if ($idMenuKategori) {
			$builder->where('core_menu.id_menu_kategori', $idMenuKategori);
		} else {
			// Menu tanpa kategori (NULL atau 0)
			$builder->groupStart()
				->where('core_menu.id_menu_kategori', 0)
				->orWhere('core_menu.id_menu_kategori', '')
				->orWhere('core_menu.id_menu_kategori', null)
				->groupEnd();
		}
		
		$query = $builder->orderBy('core_menu.urut', 'ASC')->get()->getResultArray();

		$result = [];
		foreach ($query as $row) {
			$result[$row['id_menu']] = $row;
			$result[$row['id_menu']]['highlight'] = 0;
			$result[$row['id_menu']]['depth'] = 0;
		}
				
		return $result;
	}
	
	/**
	 * Update urutan kategori menu
	 * 
	 * @param array $listKategori Array ID kategori yang sudah diurutkan
	 * @return bool Status transaksi
	 */
	public function updateKategoriUrut($listKategori) {
		$this->db->transStart();
		$urut = 1;
		foreach ($listKategori as $idKategori) {
			$this->db->table('core_menu_kategori')
				->where('id_menu_kategori', $idKategori)
				->update(['urut' => $urut]);
			$urut++;
		}
		$this->db->transComplete();
		return $this->db->transStatus();
	}
	
	/**
	 * Update urutan menu berdasarkan drag-and-drop (dari JSON)
	 * 
	 * @return bool Status transaksi
	 */
	public function updateMenuUrut() {
		$data = $this->request->getPost('data');
		$idMenuKategori = $this->request->getPost('id_menu_kategori');
		
		$json = json_decode(trim($data), true);
		$array = $this->buildChild($json);
		
		$listMenu = [];
		foreach ($array as $idParent => $arr) {
			foreach ($arr as $key => $idMenu) {
				$listMenu[$idMenu] = ['id_parent' => $idParent, 'urut' => ($key + 1)];
			}
		}
	
		// Build query untuk mendapatkan menu dalam kategori
		$builder = $this->db->table('core_menu');
		if (empty($idMenuKategori)) {
			$builder->groupStart()
				->where('id_menu_kategori', '')
				->orWhere('id_menu_kategori', null)
				->groupEnd();
		} else {
			$builder->where('id_menu_kategori', $idMenuKategori);
		}
		
		$result = $builder->get()->getResultArray();
		
		$this->db->transStart();
		$menuUpdated = [];
		
		foreach ($result as $key => $row) {
			$dataDb = [];
			
			// Cek perubahan parent
			if (isset($listMenu[$row['id_menu']]['id_parent']) && 
				$listMenu[$row['id_menu']]['id_parent'] != $row['id_parent']) {
				$idParent = $listMenu[$row['id_menu']]['id_parent'] == 0 ? null : $listMenu[$row['id_menu']]['id_parent'];
				$dataDb['id_parent'] = $idParent;
			}
			
			// Cek perubahan urutan
			if (isset($listMenu[$row['id_menu']]['urut']) && 
				$listMenu[$row['id_menu']]['urut'] != $row['urut']) {
				$dataDb['urut'] = $listMenu[$row['id_menu']]['urut'];
			}
			
			// Update jika ada perubahan
			if (!empty($dataDb)) {
				$updated = $this->db->table('core_menu')
					->where('id_menu', $row['id_menu'])
					->update($dataDb);
						
				if ($updated) {
					$menuUpdated[$row['id_menu']] = $row['id_menu'];
				}
			}
		}
		
		$this->db->transComplete();
		return $this->db->transStatus();
	}
	
	/**
	 * Mendapatkan daftar semua module beserta statusnya
	 * 
	 * @return array Daftar module
	 */
	public function getListModules() {
		return $this->db->table('core_module')
			->select('core_module.*, core_module_status.*')
			->join('core_module_status', 'core_module_status.id_module_status = core_module.id_module_status', 'left')
			->orderBy('nama_module', 'ASC')
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan semua kategori menu
	 * 
	 * @return array Daftar kategori menu
	 */
	public function getKategori() {
		return $this->db->table('core_menu_kategori')
			->orderBy('urut', 'ASC')
			->get()
			->getResultArray();
	}
	
	/**
	 * Mendapatkan kategori menu berdasarkan ID
	 * 
	 * @param int $id ID kategori menu
	 * @return array Data kategori menu
	 */
	public function getKategoriById($id) {
		return $this->db->table('core_menu_kategori')
			->where('id_menu_kategori', $id)
			->get()
			->getRowArray();
	}
	
	/**
	 * Mendapatkan semua role
	 * 
	 * @return array Daftar role
	 */
	public function getAllRole() {
		return $this->db->table('core_role')->get()->getResultArray();
	}
	
	/**
	 * Mendapatkan menu berdasarkan ID beserta role-nya
	 * 
	 * @param int $id ID menu
	 * @return array Data menu dengan daftar id_role (comma separated)
	 */
	public function getMenuById($id) {
		return $this->db->table('core_menu')
			->select('core_menu.*, GROUP_CONCAT(id_role) AS id_role')
			->join('core_menu_role', 'core_menu_role.id_menu = core_menu.id_menu', 'left')
			->where('core_menu.id_menu', $id)
			->groupBy('core_menu.id_menu')
			->get()
			->getRowArray();
	}
	
	/**
	 * Simpan atau update data menu
	 * 
	 * @param int|null $id ID menu untuk update (null untuk insert baru)
	 * @return int|bool Insert ID untuk data baru, atau status transaksi untuk update
	 */
	public function saveMenu($id = null) 
	{
		$dataDb = [];
		$dataDb['nama_menu'] = $this->request->getPost('nama_menu');
		$dataDb['id_module'] = $this->request->getPost('id_module') ?: null;
		$dataDb['url'] = $this->request->getPost('url');
		
		$idMenuKategori = trim($this->request->getPost('id_menu_kategori'));
		$dataDb['id_menu_kategori'] = empty($idMenuKategori) ? null : $idMenuKategori;
		
		$dataDb['aktif'] = empty($this->request->getPost('aktif')) ? 0 : 1;
		
		if ($this->request->getPost('use_icon')) {
			$dataDb['class'] = $this->request->getPost('icon_class');
		} else {
			$dataDb['class'] = null;
		}
		
		if ($id) {
			$this->db->transStart();
			
			// Cek perubahan kategori
			$idMenu = $this->request->getPost('id');
			$query = $this->db->table('core_menu')
				->select('id_menu_kategori')
				->where('id_menu', $idMenu)
				->get()
				->getRowArray();
				
			if ($query['id_menu_kategori'] != $dataDb['id_menu_kategori']) {
				$dataDb['id_parent'] = null;
			}
			
			$this->db->table('core_menu')
				->where('id_menu', $idMenu)
				->update($dataDb);
			
			// Update kategori untuk semua child menu
			$menuTree = $this->request->getPost('menu_tree');
			$json = json_decode(trim($menuTree), true);
			$array = $this->buildChild($json);
			$allChild = $this->allChild($idMenu, $array);
			
			foreach ($allChild as $val) {
				$this->db->table('core_menu')
					->where('id_menu', $val)
					->update(['id_menu_kategori' => $dataDb['id_menu_kategori']]);
			}
			
			// Update role menu
			$idRoles = $this->request->getPost('id_role') ?? [];
			$roleData = [];
			foreach ($idRoles as $val) {
				$roleData[] = ['id_menu' => $idMenu, 'id_role' => $val];
			}
			
			$this->db->table('core_menu_role')->where('id_menu', $idMenu)->delete();
			if (!empty($roleData)) {
				$this->db->table('core_menu_role')->insertBatch($roleData);
			}
			
			$this->db->transComplete();
			return $this->db->transStatus();
		} else {
			// Insert menu baru
			$this->db->table('core_menu')->insert($dataDb);
			$insertId = $this->db->insertID();
			
			$idRoles = $this->request->getPost('id_role') ?? [];
			if (!empty($idRoles)) {
				$roleData = [];
				foreach ($idRoles as $val) {
					$roleData[] = ['id_menu' => $insertId, 'id_role' => $val];
				}
				$this->db->table('core_menu_role')->insertBatch($roleData);
			}
			return $insertId;
		}
	}
	
	/**
	 * Hapus menu beserta semua child menu-nya
	 * 
	 * @return bool Status transaksi
	 */
	public function deleteMenu() {
		$this->db->transStart();
		
		$idMenu = $this->request->getPost('id');
		$menuTree = $this->request->getPost('menu_tree');
		
		// Hapus parent dan semua child
		$json = json_decode(trim($menuTree), true);
		$array = $this->buildChild($json);
		$allChild = $this->allChild($idMenu, $array);
		
		if ($allChild) {
			foreach ($allChild as $menuId) {
				$this->db->table('core_menu')->where('id_menu', $menuId)->delete();
			}
		} else {
			$this->db->table('core_menu')->where('id_menu', $idMenu)->delete();
		}
		
		$this->db->transComplete();
		return $this->db->transStatus();
	}
	
	/**
	 * Mendapatkan semua menu
	 * 
	 * @return array Semua menu
	 */
	public function getAllMenu() {
		return $this->db->table('core_menu')->get()->getResultArray();
	}
	
	/**
	 * Simpan atau update kategori menu
	 * 
	 * @param array $data Data kategori menu
	 * @return array Status dan pesan hasil operasi
	 */
	public function saveKategori($data) {
		$dataDb = [];
		$dataDb['nama_kategori'] = $data['nama_kategori'];
		$dataDb['deskripsi'] = $data['deskripsi'];
		$dataDb['aktif'] = $data['aktif'];
		$dataDb['show_title'] = $data['show_title'];

		if (!empty($data['id'])) {
			$save = $this->db->table('core_menu_kategori')
				->where('id_menu_kategori', $data['id'])
				->update($dataDb);
		} else {
			// Get urutan terakhir
			$lastUrut = $this->db->table('core_menu_kategori')
				->selectMax('urut')
				->get()
				->getRowArray();
					
			$dataDb['urut'] = ($lastUrut['urut'] ?? 0) + 1;
			$save = $this->db->table('core_menu_kategori')->insert($dataDb);
		}
		
		if ($save) {
			$message = [
				'status' => 'ok',
				'message' => 'Menu berhasil diupdate',
				'aktif' => $data['aktif'],
				'id_kategori' => $this->db->insertID()
			];
		} else {
			$message = [
				'status' => 'warning',
				'message' => 'Tidak ada data yang diupdate'
			];
		}
		return $message;
	}
	
	/**
	 * Hapus kategori menu berdasarkan ID
	 * 
	 * @param int $id ID kategori menu
	 * @return bool Status transaksi
	 */
	public function deleteKategoriById($id) 
	{
		$this->db->transStart();
		$this->db->table('core_menu_kategori')->where('id_menu_kategori', $id)->delete();
		$this->db->table('core_menu')
			->where('id_menu_kategori', $id)
			->update(['id_menu_kategori' => null]);
		$this->db->transComplete();
		
		return $this->db->transStatus();
	}
	
	/**
	 * Membangun array hierarki menu child dari data JSON
	 * 
	 * @param array $arr Data menu dalam bentuk array
	 * @param int $parent ID parent menu
	 * @param array $list Array hasil (by reference)
	 * @return array Hierarki menu
	 */
	private function buildChild($arr, $parent = 0, &$list = []) 
	{
		foreach ($arr as $key => $val) 
		{
			$list[$parent][] = $val['id'];

			if (key_exists('children', $val)) { 
				$this->buildChild($val['children'], $val['id'], $list);
			}
		}
		
		return $list;
	}
	
	/**
	 * Mendapatkan semua ID child menu secara rekursif
	 * 
	 * @param int $id ID menu parent
	 * @param array $list Array hierarki menu
	 * @param array $result Array hasil (by reference)
	 * @return array Daftar ID child menu
	 */
	private function allChild($id, $list, &$result = []) 
	{
		if (!key_exists($id, $list)) {
			return $result;
		}
		
		$result[$id] = $id;
		foreach ($list[$id] as $val) 
		{
			$result[$val] = $val;
			if (key_exists($val, $list)) {
				$this->allChild($val, $list, $result);
			}
		}
		return $result;
	}
}
?>
