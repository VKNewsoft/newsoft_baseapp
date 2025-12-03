<?php 
namespace Config;

use CodeIgniter\Config\BaseConfig;
class Filepicker extends BaseConfig
{
	// Relative path
	public $uploadPath = 'public/files/uploads/';
	public $serverURL = 'filepicker/';
	public $iconPath = 'public/images/filepicker_images/';
	public $itemPerPage = 50;
	public $thumbnail = [
						'small' => ['w' => 250, 'h' => 250],
						'medium' => ['w' => 450, 'h' => 450]					
					];

	public function __construct() 
	{
		$config = new \Config\App();
		$this->uploadURL = $config->baseURL . $this->uploadPath;
		$this->uploadPath = realpath(__DIR__ . '/../..') . '/' . $this->uploadPath;
		$this->serverURL = $config->baseURL . $this->serverURL;
		$this->iconURL = $config->baseURL . $this->iconPath;
		$this->iconPath = realpath(__DIR__ . '/../..') . '/' . $this->iconPath;
	}
}