<?php
/**
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Controllers\Builtin;

class Qrscan extends \App\Controllers\BaseController
{
	public function __construct() {
		
		parent::__construct();
		$this->data['site_title'] = 'QRCode Scanner';
		
		$this->addJs ( $this->config->baseURL . 'public/themes/modern/js/qr-code.js');
	}
	
	public function index()
	{
		$data = $this->data;
		$this->view('builtin/qrscan.php',$data);
	}

}
