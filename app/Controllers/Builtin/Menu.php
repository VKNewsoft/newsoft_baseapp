<?php
/**
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Controllers\Builtin;
use App\Models\Builtin\MenuModel;

class Menu extends \App\Controllers\BaseController
{
	protected $model;
	
	public function __construct() {
		
		parent::__construct();
		// $this->mustLoggedIn();
		
		$this->model = new MenuModel;	
		$this->data['site_title'] = 'Halaman Menu';
		
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery-nestable/jquery.nestable.min.css?r='.time());
		$this->addStyle ( $this->config->baseURL . 'public/vendors/wdi/wdi-modal.css?r=' . time());
		$this->addStyle ( $this->config->baseURL . 'public/vendors/wdi/wdi-fapicker.css?r=' . time());
		$this->addStyle ( $this->config->baseURL . 'public/vendors/wdi/wdi-loader.css?r=' . time());
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/wdi/wdi-fapicker.js?r=' . time());
		$this->addJs ($this->config->baseURL . 'public/themes/modern/builtin/js/admin-menu.js');
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery-nestable/jquery.nestable.js?r=' . time());
		$this->addJs ( $this->config->baseURL . 'public/vendors/js-yaml/js-yaml.min.js?r=' . time());
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery-nestable/jquery.wdi-menueditor.js?r=' . time());
		
		$this->addJs ( $this->config->baseURL . 'public/vendors/jquery.select2/js/select2.full.min.js' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/css/select2.min.css' );
		$this->addStyle ( $this->config->baseURL . 'public/vendors/jquery.select2/bootstrap-5-theme/select2-bootstrap-5-theme.min.css' );
		
		$this->addJs($this->config->baseURL . 'public/vendors/dragula/dragula.min.js');
		$this->addStyle($this->config->baseURL . 'public/vendors/dragula/dragula.min.css');

		helper(['cookie', 'form']);
	}
	
	public function index()
	{
		$this->hasPermission('read_all');
		
		$data = $this->data;
		
		$menuUpdated = [];
		$msg = [];
		if ($this->request->getPost('submit')) 
		{
			$menuUpdated = $this->model->updateData();
			
			if ($menuUpdated) {
				$msg['status'] = 'ok';
				$msg['content'] = 'Menu berhasil diupdate';
			} else {
				$msg['status'] = 'warning';
				$msg['content'] = 'Tidak ada menu yang diupdate';
			}
		}
		
		$data['menu_kategori'] = $this->model->getKategori();
		$result = $this->model->getMenuByKategori($data['menu_kategori'][0]['id_menu_kategori']);
		$list_menu = menu_list($result);
	
		$data['list_menu'] = $result ? $this->buildMenuList($list_menu) : ''; 
		$data['role'] = 	$this->model->getAllRole();
		$data['msg'] = $msg;
		
		$this->view('builtin/menu.php', $data);
	}
	
	public function ajaxGetMenuByIdKategori() {
		$result = $this->model->getMenuByKategori($this->request->getGet('id_menu_kategori'));
		if ($result) {
			$listMenu = menu_list($result);
			echo $this->buildMenuList($listMenu); 
		} else {
			echo '';
		}
	}
	
	public function ajaxGetMenuForm() 
	{
		$this->data['menu_kategori'] = $this->model->getKategori();
		$this->data['list_module'] = $this->model->getListModules();
		$this->data['roles'] = $this->model->getAllRole();
		$this->data['menu'] =[];
		if ($this->request->getGet('id')) {
			$this->data['menu'] = $this->model->getMenuById($this->request->getGet('id'));
		}
		echo view('themes/modern/builtin/menu-form.php', $this->data);
	}

	public function ajaxGetKategoriForm() 
	{
		if ($this->request->getGet('id')) {
			if ($this->request->getGet('id')) {
				$this->data['kategori'] = $this->model->getKategoriById($this->request->getGet('id'));
				if (!$this->data['kategori']) {
					echo '<div class="alert alert-danger">Data tidak ditemukan</div>';
					exit;
				}
			}
		}
			
		echo view('themes/modern/builtin/menu-kategori-form.php', $this->data);
	}
	
	public function ajaxSaveKategori() {
		$result = $this->model->saveKategori($this->request->getPost());
		echo json_encode($result);
	}
	
	public function ajaxUpdateUrut() {
		
		$updated = $this->model->updateMenuUrut();
		if ($updated) {
			$message['status'] = 'ok';
			$message['message'] = 'Menu berhasil diupdate';
		} else {
			$message['status'] = 'warning';
			$message['message'] = 'Tidak ada menu yang diupdate';
		}
		
		echo json_encode($message);
	}
	
	public function ajaxUpdateKategoriUrut() {
		
		$updated = $this->model->updateKategoriUrut(json_decode($this->request->getPost('id'), true));
		if ($updated) {
			$message['status'] = 'ok';
			$message['message'] = 'Menu berhasil diupdate';
		} else {
			$message['status'] = 'warning';
			$message['message'] = 'Tidak ada menu yang diupdate';
		}
		
		echo json_encode($message);
	}
	
	public function ajaxDeleteKategori() {
		$delete = $this->model->deleteKategoriById($this->request->getPost('id'));
		if ($delete) {
			$message['status'] = 'ok';
			$message['message'] = 'Group berhasil dihapus';
		} else {
			$message['status'] = 'warning';
			$message['message'] = 'Group gagal dihapus';
		}
		
		echo json_encode($message);
	}
	
	public function editMenu()
	{
		$data['msg'] = [];
		if ($this->request->getPost('nama_menu')) 
		{
			$error = $this->checkForm();
			if ($error) {
				$data['msg']['status'] = 'error';
				$data['msg']['message'] = '<ul class="list-error"><li>' . join($error, '</li><li>') . '</li></ul>';
			} else {
				
				
				if (!$this->request->getPost('id')) {
					$query = $this->model->saveMenu();
					$message = 'Menu berhasil ditambahkan';
					$data['msg']['id_menu'] = $query;
				} else {
					$query = $this->model->saveMenu($this->request->getPost('id'));
					$message = 'Menu berhasil diupdate';
				}
				
				// $query = true;
				if ($query) {
					$data['msg']['status'] = 'ok';
					$data['msg']['message'] = $message;
					// $data['msg']['message'] = 'Menu berhasil diupdate';
				} else {
					$data['msg']['status'] = 'error';
					$data['msg']['message'] = 'Data gagal disimpan';
					$data['msg']['error_query'] = true;
				}	
			}
			echo json_encode($data['msg']);
			exit();
		}
		$this->view('builtin/module-form.php', $data);
	}
	
	public function ajaxDeleteMenu() {
		$result = $this->model->deleteMenu();
		
		if ($result) {
			$message = ['status' => 'ok', 'message' => 'Data menu berhasil dihapus'];
			echo json_encode($message);
		} else {
			echo json_encode(['status' => 'error', 'message' => 'Data menu gagal dihapus']);
		}
	}
	
	private function checkForm() 
	{
		$error = [];
		if (trim($this->request->getPost('nama_menu')) == '') {
			$error[] = 'Nama menu harus diisi';
		}
		
		if (trim($this->request->getPost('url')) == '') {
			$error[] = 'Url harus diisi';
		}
		
		return $error;
	}
	
	function buildMenuList($arr, $depth = 0)
	{
		// indent child levels by 20px per depth
		$margin = $depth ? ' style="margin-left: '.($depth * 20).'px;"' : '';
		$menu = "\n" . '<ol class="dd-list"' . $margin . ">\r\n";

		foreach ($arr as $key => $val) 
		{
			$new = !empty($val['new']) && $val['new'] == 1 ? '<span class="menu-baru">NEW</span>' : '';
			$icon = !empty($val['class']) ? '<i class="' . $val['class'] . ' me-2"></i>' : '';

			$bg_color  = 'style="background-color: #f5bcbc;"';
			$status_icon = '<i class="fas fa-times-circle text-danger me-2"></i>';
			if (!empty($val['aktif'])) {
				$bg_color  = 'style="background-color: #c0f5bc;"';
				$status_icon = '<i class="fas fa-check-circle text-success me-2"></i>';
			}

			$menu .= '<li class="dd-item" data-id="' . $val['id_menu'] . '"><div class="dd-handle d-flex align-items-center" ' . $bg_color . '>' . $status_icon . $icon . '<span class="menu-title flex-grow-1">' . htmlspecialchars($val['nama_menu']) . '</span>' . $new . '</div>';

			if (!empty($val['children']))
			{
				$menu .= $this->buildMenuList($val['children'], $depth + 1);
			}
			$menu .= "</li>\n";
		}
		$menu .= "</ol>\n";
		return $menu;
	}
}