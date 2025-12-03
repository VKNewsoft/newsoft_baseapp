<?php
/**
 * Model File Picker
 * Mengelola upload, storage, dan manajemen file media
 * Mendukung image thumbnail generation, file type filtering, dan search
 * 
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Models;
require_once('app/ThirdParty/Imageworkshop/autoload.php');
use PHPImageWorkshop\ImageWorkshop;

class FilepickerModel extends \App\Models\BaseModel
{
	protected $idCompanyLogin;
	
	public function __construct() 
	{
		parent::__construct();
		$this->idCompanyLogin = session()->get('user')['id_company'] ?? 0;
	}
	
	/**
	 * Ambil data file dengan pagination, filter, dan search
	 * Mendukung filter tipe file, tanggal upload, dan pencarian
	 * 
	 * @param int $itemPerPage Jumlah item per halaman
	 * @param int $idCompany ID company untuk multi-tenant
	 * @return array Data file, total item, dan loaded item
	 */
	public function getData($itemPerPage, $idCompany = 0) 
	{
		helper('filepicker');
		$listFileType = file_type();
		$result = ['filter_tgl' => []];
		
		// Build Query dengan Query Builder
		$builder = $this->db->table('core_file_picker');
		$builder->where('id_company', $idCompany);
		
		// Filter by ID file picker (detail file)
		$idFilePicker = $this->request->getGet('id_file_picker');
		if ($idFilePicker) {
			$builder->where('id_file_picker', $idFilePicker);
		} else {
			
			// Filter File Type
			$filterFile = $this->request->getGet('filter_file');
			if ($filterFile) {
				$split = explode(' ', $filterFile);
				$listFilter = [];
				
				foreach ($split as $filter) 
				{
					$filter = trim($filter);
					if (!$filter)
						continue;
					
					$listMime = [];
					foreach ($listFileType as $mime => $val) {
						if ($val['file_type'] == $filter) {
							$listMime[] = $mime;
						}
					}
					
					if ($listMime) {
						$listFilter[] = $listMime;
					}
				}
				
				// Apply filter dengan whereIn (OR condition)
				if ($listFilter) {
					$builder->groupStart();
					foreach ($listFilter as $mimes) {
						$builder->orWhereIn('mime_type', $mimes);
					}
					$builder->groupEnd();
				}
			}
			
			// Siapkan filter tanggal (untuk dropdown)
			$builderDate = $this->db->table('core_file_picker');
			$builderDate->select('DATE_FORMAT(tgl_upload,"%Y-%m") AS bulan');
			$builderDate->groupBy('bulan');
			$builderDate->orderBy('bulan', 'DESC');
			$tanggal = $builderDate->get()->getResultArray();
			
			$namaBulan = nama_bulan();
			foreach ($tanggal as $val) {
				$exp = explode('-', $val['bulan']);
				$result['filter_tgl'][$val['bulan']] = $namaBulan[(int)$exp[1]] . ' ' . $exp[0];
			}
			
			// Filter Tanggal
			$filterTgl = $this->request->getGet('filter_tgl');
			if ($filterTgl) {
				$builder->like('tgl_upload', $filterTgl, 'after');
			}
			
			// Filter Search 
			$search = $this->request->getGet('q');
			if ($search && trim($search) != '') {
				$builder->groupStart();
				$builder->like('title', $search);
				$builder->orLike('nama_file', $search);
				$builder->groupEnd();
			}
		}
		
		// Hitung total item
		$totalItem = $builder->countAllResults(false);
		
		// Pagination
		$page = (int) ($this->request->getGet('page') ?? 1);
		$start = $itemPerPage * ($page - 1);
		$builder->orderBy('tgl_upload', 'DESC');
		$builder->limit($itemPerPage, $start);
		
		$result['data'] = $builder->get()->getResultArray();
		$result['total_item'] = $totalItem;
		
		$jmlData = count($result['data']);
		$loadedItem = $jmlData < $itemPerPage ? $jmlData : $itemPerPage;
		$result['loaded_item'] = ($itemPerPage * ($page - 1)) + count($result['data']);
					
		foreach ($result['data'] as $key => $val) 
		{
			$metaFile = json_decode($val['meta_file'], true);
			$properties = $this->getFileProperties($val['mime_type'], $val['nama_file'], $metaFile);
			$result['data'][$key] = array_merge($result['data'][$key], $properties);
		}
		
		return ['result' => $result, 'total_item' => $totalItem, 'loaded_item' => $result['loaded_item']];
	}
	
	/**
	 * Ambil properties file (thumbnail, URL, type, dll)
	 * Generate thumbnail URL untuk image atau icon untuk non-image
	 * 
	 * @param string $mime MIME type file
	 * @param string $fileName Nama file
	 * @param array $metaFile Metadata file (width, height, thumbnail, dll)
	 * @return array Properties file
	 */
	private function getFileProperties($mime, $fileName, $metaFile) 
	{			
		$config = new \Config\Filepicker();
		helper('filepicker');
		$listFileType = file_type();
		
		$extensionColor = $extension = '';
		$mimeImage = ['image/png', 'image/jpeg', 'image/bmp', 'image/gif'];
		
		$fileExists = true;
		
		// Cek file original ada atau tidak
		if (file_exists($config->uploadPath . $fileName)) 
		{
			$result['file_exists']['original'] = 'found';
		} else {
			$fileExists = false;
			$result['file_exists']['original'] = 'not_found';
		}
		
		// Handle image dengan thumbnail
		if (in_array($mime, $mimeImage)) {
			
			$thumbnailFile = $fileName;
			if (key_exists('thumbnail', $metaFile)) 
			{
				$thumbnail = $metaFile['thumbnail'];
				foreach ($thumbnail as $size => $val) 
				{
					if (file_exists($config->uploadPath . $val['filename'])) {
						$result['file_exists']['thumbnail'][$size] = 'found';
					} else {
						$fileExists = false;
						$result['file_exists']['thumbnail'][$size] = 'not_found';
					}
				}
				
				if (key_exists('small', $thumbnail)) {
					$thumbnailFile = $thumbnail['small']['filename'];
				}
			}
			
			$thumbnailUrl = $config->uploadURL . $thumbnailFile; 
			$fileType = 'image';

		} else {
			// Handle non-image files (documents, video, audio, dll)
			$pathinfo = pathinfo($fileName);
			$extension = $pathinfo['extension'] ?? '';
			
			$fileIcon = 'file';
			$fileType = 'non_image';
			
			if (key_exists($mime, $listFileType)) {
				$fileIcon = $listFileType[$mime]['extension'];
				$fileType = $listFileType[$mime]['file_type'];
			} else {
				foreach ($listFileType as $val) {
					if (($val['extension'] ?? '') == $extension) {
						$fileIcon = strtolower($extension);
						$fileType = $val['file_type'];
					}
				}
			}
			
			$thumbnailUrl = $config->iconURL . $fileIcon . '.png';
		}
		
		// Jika file tidak ditemukan, gunakan icon not found
		if (!$fileExists) {
			$thumbnailUrl = $config->iconURL . 'file_not_found.png';
		}
		
		if (!key_exists('thumbnail', $result['file_exists'])) {
			$result['file_exists']['thumbnail'] = [];
		}
		
		$result['file_not_found'] = $fileExists ? 'false' : 'true';
		$result['file_type'] = $fileType;
		$result['url'] = $config->uploadURL . $fileName; 
		$result['thumbnail']['url'] = $thumbnailUrl;
		$result['thumbnail']['extension_name'] = $extension;
		
		return $result;
	}
	
	/**
	 * Update metadata file (title, alt, description, dll)
	 * 
	 * @return bool Status update
	 */
	public function updateMetaFile() 
	{
		$idFilePicker = $this->request->getPost('id');
		$fieldName = $this->request->getPost('name');
		$fieldValue = $this->request->getPost('value');
		
		$update = $this->db->table('core_file_picker')
						   ->update([$fieldName => $fieldValue], ['id_file_picker' => $idFilePicker]);
		return $update;
	}
	
	/**
	 * Hapus file dari database dan storage
	 * Mendukung delete multiple files
	 * 
	 * @return array Status dan pesan hasil penghapusan
	 */
	public function deleteFile() 
	{
		$config = new \Config\Filepicker();
		$this->db->transBegin();
		
		// Parse ID files (bisa array atau single ID)
		$idPost = $this->request->getPost('id');
		$idFiles = json_decode($idPost, true);
		
		if (!is_array($idFiles)) {
			$idFiles = $idPost ? [$idPost] : [];
		}
		
		$error = [];
		foreach ($idFiles as $idFile) 
		{
			// Ambil data file
			$builder = $this->db->table('core_file_picker');
			$builder->where('id_file_picker', $idFile);
			$file = $builder->get()->getRowArray();
			
			if (!$file) {
				$error[] = 'File tidak ditemukan (ID: ' . $idFile . ')';
			} else {
				// Hapus dari database
				$delete = $this->db->table('core_file_picker')->delete(['id_file_picker' => $idFile]);
				
				if ($delete) {
					$meta = json_decode($file['meta_file'], true);
					
					$dir = rtrim($config->uploadPath, '/\\') . '/';
					
					// Hapus file utama
					if (file_exists($dir . $file['nama_file'])) { 
						$unlink = delete_file($dir . $file['nama_file']);
						if (!$unlink) {
							$error[] = 'Gagal menghapus file: ' . $file['nama_file'];
						}
					}
					
					// Hapus thumbnail jika ada
					if (key_exists('thumbnail', $meta)) 
					{
						foreach ($meta['thumbnail'] as $val) {
							if (file_exists($dir . $val['filename'])) { 
								$unlink = delete_file($dir . $val['filename']);
								if (!$unlink) {
									$error[] = 'Gagal menghapus file: ' . $val['filename'];
								}
							}
						}
					}
					
				} else {
					$error[] = 'Gagal menghapus data database file ID: ' . $idFile;
				}
			}
		}
		
		// Commit atau rollback transaksi
		if ($error) {
			$this->db->transRollback();
			return [
				'status' => 'error',
				'message' => '<ul><li>' . join('</li><li>', $error) . '</li></ul>'
			];
		} else {
			$this->db->transCommit();
			return [
				'status' => 'ok',
				'message' => 'Data berhasil dihapus'
			];
		}
	}
	
	/**
	 * Hapus semua file dari database dan storage
	 * HATI-HATI: Operasi ini akan menghapus SEMUA file!
	 * 
	 * @return array Status dan pesan hasil penghapusan
	 */
	public function deleteAllFiles() 
	{
		$config = new \Config\Filepicker();
		$path = $config->uploadPath;
		
		$listFile = @scandir($path);
		if ($listFile) 
		{
			// Truncate table
			$truncate = $this->db->table('core_file_picker')->truncate();
			if ($truncate) {
				foreach ($listFile as $val) 
				{
					if ($val == '.' || $val == '..') {
						continue;
					}
					
					delete_file($path . $val);
				}
			}
			
			return [
				'status' => 'ok',
				'message' => 'Semua file berhasil dihapus'
			];
		} else {
			return [
				'status' => 'error',
				'message' => 'Folder ' . $path . ' kosong atau tidak dapat diakses'
			];
		}
	}
	
	/**
	 * Upload file baru
	 * Mendukung auto-generate thumbnail untuk image
	 * 
	 * @param callable $allowedFiletypes Fungsi yang return array MIME types yang diizinkan
	 * @param int $idCompany ID company untuk multi-tenant
	 * @return array Status upload dan info file
	 */
	public function uploadFile($allowedFiletypes, $idCompany = 0) 
	{		
		$config = new \Config\Filepicker();
		$namaBulan = nama_bulan();
		$path = $config->uploadPath;
		
		helper('filepicker');
		helper('upload_file');
		$listFileType = file_type();
		
		// Ambil file dari request
		$file = $this->request->getFile('file');
		
		if ($file && $file->getName()) {
			
			// Cek folder upload
			if (file_exists($path) && is_dir($path)) {
				
				if (!is_writable($path)) {
					return [
						'status' => 'error',
						'message' => 'Tidak dapat menulis file ke folder'
					];
				}
				
				// Validasi tipe file
				$currentMimeType = $file->getMimeType();
				if (in_array($currentMimeType, $allowedFiletypes())) {
					
					// Upload file
					$newName = upload_file($path, $file);
					$metaFile = [];
					
					$mimeImage = ['image/png', 'image/jpeg', 'image/bmp', 'image/gif'];
					
					// Jika image, generate thumbnail
					if (in_array($currentMimeType, $mimeImage)) 
					{
						$imgSize = @getimagesize($path . $newName);
					
						$metaFile['default'] = [
							'width' => $imgSize[0],
							'height' => $imgSize[1],
							'size' => $file->getSize()
						];

						// Generate thumbnail dengan berbagai ukuran
						foreach ($config->thumbnail as $size => $dim) 
						{
							if ($imgSize[0] > $dim['w'] || $imgSize[1] > $dim['h']) 
							{
								$imgDim = image_dimension($path. $newName, $dim['w'], $dim['h']);
								$imgWidth = ceil($imgDim[0]);
								$imgHeight = ceil($imgDim[1]);
								
								$width = $height = null;
								if ($imgWidth >= $dim['w']) {
									$width = $dim['w'];
								} else if ($imgHeight >= $dim['h']) {
									$height = $dim['h'];
								}

								$layer = ImageWorkshop::initFromPath($path . $newName);
								$layer->resizeInPixel($width, $height, true);
								$namePath = pathinfo($newName);
								$thumbName = $namePath['filename'] . '_' . $size . '.' . $namePath['extension'];
								$layer->save($path, $thumbName, false, false, 97);
								
								$thumbDim = @getimagesize($path . $thumbName);
								$metaFile['thumbnail'][$size] = [
									'filename' => $thumbName,
									'width' => $thumbDim[0],
									'height' => $thumbDim[1],
									'size' => @filesize($path . $thumbName)
								];
							}
						}
					}
					
					// Simpan ke database
					$idUser = session()->get('user')['id_user'] ?? 0;
					$dataDb = [
						'nama_file' => $newName,
						'id_company' => $idCompany,
						'mime_type' => $currentMimeType,
						'size' => $file->getSize(),
						'tgl_upload' => date('Y-m-d H:i:s'),
						'id_user_upload' => $idUser,
						'meta_file' => json_encode($metaFile)
					];
					
					$insert = $this->db->table('core_file_picker')->insert($dataDb);
					
					$fileInfo = $dataDb;
					$fileInfo['bulan_upload'][date('Y-m')] = $namaBulan[date('n')] . ' ' . date('Y');
					$fileInfo['id_file_picker'] = $this->db->insertID();
					$fileProperties = $this->getFileProperties($currentMimeType, $newName, $metaFile);
					$fileInfo = array_merge($fileInfo, $fileProperties);
					
					return [
						'status' => 'success',
						'message' => 'File berhasil diupload',
						'file_info' => $fileInfo
					];
				} else {
					return [
						'status' => 'error',
						'message' => 'Tipe file tidak diizinkan'
					];
				}

			} else {
				return [
					'status' => 'error',
					'message' => 'Folder ' . $path . ' tidak ditemukan'
				];
			}
			
		} else {
			return [
				'status' => 'error',
				'message' => 'File tidak ditemukan dalam request'
			];
		}
	}
}