<?php
namespace App\Libraries;
require_once('app/ThirdParty/Tcpdf/tcpdf.php');
require_once('app/Helpers/util_helper.php');

class NSDPDF extends \TCPDF {
	
	private $model;
	private $footerText = '';
	
	public function __construct() {
		parent::__construct();
		$this->model = new \App\Models\BaseModel;
	}
	
	//Page header
	public function Header() 
	{
		// Logo
		// Image($file, $x='', $y='', $w=0, $h=0, $type='', $link='', $align='', $resize=false, $dpi=300, $palign='', $ismask=false, $imgmask=false, $border=0, $fitbox=false, $hidden=false, $fitonpage=false)
	
		$identitas = $this->model->getIdentitas();
		$setting_result = $this->model->getSetting('invoice');
		foreach ($setting_result as $val) {
			$setting[$val['param']] = $val['value'];
		}
		// $this->Image(ROOTPATH . 'public/images/' . $setting['logo'], 10, 7, 10, 0, 'JPG', 'https://newsoftdev.com');
		$this->SetTextColor(30, 30, 30);
		$margin_left = 10;
		$margin_top = 7;
		$font_size = 10;
		$image_dim = getimagesize(ROOTPATH . 'public/images/' . $setting['logo']);
		$x = $margin_left + ($image_dim[0] * 0.2645833333) + 3;
		
		$this->SetFont('dejavusans', '', $font_size + 3, '', true);
		$this->SetY($margin_top);
		$this->Cell(0, 0, $identitas['nama'], 0, 1, 'L', 0, '', 0, false, 'T', 'M' );
		//Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=0, $link='', $stretch=0, $ignore_min_height=false, $calign='T', $valign='M')
		// $this->SetX($x);
		// $this->SetFont ('helvetica', '', $font_size, '', 'default', true );
		// $this->Cell(0, 0, $identitas['alamat'] . ', ' . $identitas['nama_kelurahan'] . ', ' . $identitas['nama_kecamatan'], 0, 1, 'L', 0, '', 0, false, 'T', 'M' ); //
		// $this->SetX($x);
		// $this->Cell(0, 0, $identitas['nama_kabupaten'] . ', ' . $identitas['nama_propinsi'] , 0, 1, 'L', 0, '', 0, false, 'T', 'M' );
		
		$this->ln(3);
		$this->SetFont ('helvetica', '', 0, '', 'default', true );
		$border_color= '#AAAAAA';
		$tbl = <<<EOD
				<table>
					<tbody>
						<tr>
							<td style="width:100%;height:1px;border-bottom-color:$border_color;" align="center"></td>
						</tr>
					</tbody>
				</table> 
				EOD;
		
		$this->writeHTML($tbl, false, false, false, false, '');
	}
	
	public function setFooterText( $footer_text ) {
		$this->footerText = $footer_text;
		
	}

	// Page footer
	public function Footer() 
	{
		// Position at 15 mm from bottom
		$this->SetY(-15);
		$this->SetFont('helvetica', 'I', 10, '', 'default', true );
		$border_color= '#AAAAAA';
	
		$this->SetTextColor(30, 30, 30);
		// $this->SetLineWidth(0.1);
		// $this->SetDrawColor(126,126,126);
		// $this->Cell(0,7,$text, 'T', 0, 'R', 0, '', 0, false, 'T', 'B');
		
		$hal_awal = $this->getAliasNumPage();
		$hal_total = $this->getAliasNbPages();
		$text = 'Halaman ' . trim($hal_awal) . '/' . trim($hal_total);
			$tbl = '
				<table border="0" cellspacing="0" cellpadding="6">
				<tbody>
					<tr>
						<td style="width:75%;border-top-color:' . $border_color .'" align="left">' . $this->footerText . '</td>
						<td style="width:25%;border-top-color:' . $border_color . '" align="right">' . $text . '</td>
					</tr>
				</tbody>
				</table>';
				
		$this->writeHTML($tbl, false, false, false, false, '');
	}
}