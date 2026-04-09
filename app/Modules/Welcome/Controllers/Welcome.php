<?php
/**
 * Welcome.php
 * 
 * @author  VKNewsoft - Newsoft Developer, 2025
 */		
namespace App\Controllers\Admin;

class Welcome extends \App\Controllers\BaseAdmin
{
	protected $model = '';
	private $nonce;
	
	public function __construct() {	
	
		parent::__construct();
	}
	
	public function index() 
	{
		$this->view('welcome.php', $this->data);
	}
}
