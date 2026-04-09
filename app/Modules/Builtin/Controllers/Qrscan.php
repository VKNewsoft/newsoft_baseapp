<?php
/**
 * @author VKNewsoft - Newsoft Developer
 * @year 2025
 */

namespace App\Modules\Builtin\Controllers;

class Qrscan extends \App\Modules\Common\Controllers\BaseController
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
