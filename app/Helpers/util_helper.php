<?php
/**
 * util_helper.php
 * Utility Functions & Business Logic Helper
 * 
 * @author  VKNewsoft - Newsoft Developer, 2025
 */
/*
Jenis Izin :
- Sakit
- Lupa Absen
- Datang Siang
- Pulang Cepat
- Keperluan Keluarga
- Lainnya

Format NIK =  [KODE PT] + [YY] + [MM] + [XXX]
*/


// Fungsi Tambahan Baru Awal
function HelpGetDataMesinNoFilter($start, $end, $CI)
{
	$CI->db->transStart();

	return $CI->db->query('select * from hrm_data_mesin where isDeleted = 0 AND type <> "adjust" AND (date(tgl_data) between "' . $start . '" AND "' . $end . '")')->getResultArray();
}

function HelpUpsertingMesin($data, $CI)
{
	if ($data) {
		$CI->db->transStart();
		$CI->db->table('hrm_data_mesin')->insertBatch($data);
		$CI->db->transComplete();

		if ($CI->db->transStatus()) {
			return json_encode(['proses' => true, 'total' => count($data), 'message' => 'Berhasil Memperbarui ' . count($data) . ' Data Absensi']);
		} else {
			return json_encode(['proses' => false, 'total' => 0, 'message' => 'Terjadi Kesalahan Saat Proses Menarik Data']);
		}
	} else {
		return json_encode(['proses' => false, 'total' => 0, 'message' => 'Tidak Ditemukan Data Absensi Hari Ini']);
	}
}

function cronCutiBulk($CI, $data, $and = ' AND 1=1 ', $expired = 2)
{
	$CI->db->transStart();

	$query = $CI->db->query("select id_karyawan from hrm_employee_detail where isDeleted = 0 $and")->getResultArray();
	$tgl_expired = date("Y-m-d h:m:s", strtotime("+$expired YEAR", strtotime($data['tgl_transaksi'])));
	$i = 0;

	foreach ($query as $val) {
		$upsert[$i]['id_karyawan'] = $val['id_karyawan'];
		$upsert[$i]['id_transaksi'] = $data['id_transaksi'];
		$upsert[$i]['tgl_transaksi'] = $data['tgl_transaksi'];
		$upsert[$i]['tgl_expired'] = $tgl_expired;
		$upsert[$i]['tipe_transaksi'] = $data['tipe_transaksi'];
		$upsert[$i]['keterangan'] = $data['keterangan'];
		$upsert[$i]['total_cuti'] = $data['total_cuti'];
		$upsert[$i]['tgl_input'] = $data['tgl_input'];
		$upsert[$i]['id_user_input'] = $data['id_user_input'];
		$i++;
	}

	$CI->db->table('hrm_cron_cuti')->upsertBatch($upsert);
	$CI->db->transComplete();
	return $CI->db->transStatus();
}

function cronCutiDelete($CI, $data)
{
	$CI->db->transStart();

	$delete['tgl_edit'] = $data['tgl_edit'];
	$delete['id_user_edit'] = $data['id_user_edit'];
	$delete['isDeleted'] = 1;

	$CI->db->table('hrm_cron_cuti')->update($delete, ['id_transaksi' => $data['id_transaksi'], 'tipe_transaksi' => $data['tipe_transaksi']]);
	$CI->db->transComplete();
	return $CI->db->transStatus();
}

function compress_image($source_url, $destination_url, $quality)
{

	$info = getimagesize($source_url);

	if ($info['mime'] == 'image/jpeg') $image = imagecreatefromjpeg($source_url);
	elseif ($info['mime'] == 'image/gif') $image = imagecreatefromgif($source_url);
	elseif ($info['mime'] == 'image/png') $image = imagecreatefrompng($source_url);

	imagejpeg($image, $destination_url, $quality);
	return $destination_url;
}

function compress_images_in_dir($dir, $quality)
{
	$files = scandir($dir);
	foreach ($files as $file) {
		if (is_dir($file)) continue;
		$img_type = exif_imagetype($dir . $file);
		if (in_array($img_type, array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_GIF))) {
			$destination_url = $dir . $file;
			compress_image($dir . $file, $destination_url, $quality);
		}
	}
}

// // Set directory and quality
// $dir = 'path/to/your/image/folder/';
// $quality = 70; // Quality of compressed image

// // Call function
// compress_images_in_dir($dir, $quality);

function pngQRCode($data, $size = 25)
{
	include ROOTPATH . 'public/phpqrcode/qrlib.php';

	$tempDir = '/public/qrcode/' . $data . '.png';
	$create = ROOTPATH . '/public/qrcode/' . $data . '.png';
	$link = base_url($tempDir);
	$isi = $data;

	QRcode::png($isi, $create, QR_ECLEVEL_H, $size);
}

function searchForId($search_value, $array)
{

	// Iterating over main array
	foreach ($array as $key1 => $val1) {
		// Check if this value is an array
		// with atleast one element
		if (is_array($val1) and count($val1)) {
			// Iterating over the nested array
			foreach ($val1 as $key2 => $val2) {
				if ($val2 == $search_value) {
					return $key1;
				}
			}
		} else if ($val1 == $search_value) {
			return $key1;
		}
	}

	return null;
}

function cek_negative_number($int)
{
	return ($int < 0) ? "(" . number_format(abs($int)) . ")" : number_format($int);
}

function getMonthsAgo(int $n, $datenya): string
{
	$date = new DateTime($datenya);
	$day  = $date->format('j');
	$date->modify('first day of this month')->modify('-' . $n . ' months');
	if ($day > $date->format('t')) {
		$day = $date->format('t');
	}
	$date->setDate($date->format('Y'), $date->format('m'), $day);
	return $date->format('Y-m-d');
}

function generate_hour($date, $time_start, $time_end)
{
	$sin = new DateTime($time_start);
	$sout = new DateTime($time_end);
	$interval = $sin->diff($sout);
	$hours = (int) $interval->format('%H');
	$minutes = (int) $interval->format('%I');
	return $hours . ' Jam ' . $minutes . ' Menit';
}

function CheckLateHour($tglnya, $time_start, $time_end)
{
	$new_time_start = strtotime($tglnya . ' ' . $time_start);
	$new_time_end = strtotime($tglnya . ' ' . $time_end);

	$is_late = ceil(($new_time_end - $new_time_start) / 60);

	if ($is_late >= 0) {
		$result = false;
	} else {
		$result = true;
	}

	return $result;
}

function generateLate($tglnya, $time_start, $time_end)
{
	// HOUR FUNCTION START
	$old_date           = $tglnya;
	$old_date_timestamp = strtotime($old_date);
	$new_date           = date('m/d/Y', $old_date_timestamp);

	$sin  = new DateTime($new_date . $time_start);
	$sout = new DateTime($new_date . $time_end);
	$hour = $sin->diff($sout);

	$new_time_start = strtotime($tglnya . ' ' . $time_start);
	$new_time_end = strtotime($tglnya . ' ' . $time_end);
	$is_late = $new_time_start - $new_time_end;

	if ($is_late <= 0) {
		$result = "-";
	} else {
		if ($hour->format('%h') > 0) {
			$result = $hour->format('%H Jam %i Menit');
		} else {
			$result = $hour->format('%i Menit');
		}
	}

	// HOUR FUNCTION END
	return $result;
}

function generateLembur($tglnya_1, $tglnya_2, $time_start, $time_end)
{
	// HOUR FUNCTION START
	$old_date1           = $tglnya_1;
	$old_date_timestamp1 = strtotime($old_date1);
	$new_date1           = date('m/d/Y', $old_date_timestamp1);

	$old_date2           = $tglnya_2;
	$old_date_timestamp2 = strtotime($old_date2);
	$new_date2           = date('m/d/Y', $old_date_timestamp2);

	$sin  = new DateTime($new_date1 . $time_start);
	$sout = new DateTime($new_date2 . $time_end);
	$hour = $sin->diff($sout);

	$new_time_start = strtotime($tglnya_1 . ' ' . $time_start);
	$new_time_end = strtotime($tglnya_2 . ' ' . $time_end);
	$is_over = $new_time_end - $new_time_start;

	if ($is_over <= 0) {
		$result = null;
	} else {
		$result = $hour->format('%H:%I:%S');
	}

	// HOUR FUNCTION END
	return $result;
}

function generate_worktime($date, $time_start, $time_end)
{
	// asumsi $time_start dan $time_end = 'H:i:s'
	$sin  = new DateTime($date . ' ' . $time_start);
	$sout = new DateTime($date . ' ' . $time_end);
	$hour = $sin->diff($sout);
	$work = (int) $hour->format('%H');
	$minutes = (int) $hour->format('%I');
	$working = $work . ' Jam ' . $minutes . ' Menit';
	return $working;
}

function format_jamtobahasa($time = null)
{
	if ($time) {
		$timeParts = explode(':', $time);

		$hour = (int) $timeParts[0];
		$minute = (int) $timeParts[1];
		return "$hour Jam $minute Menit";
	} else {
		return "-";
	}
}

function format_jamtomenit($time = null)
{
	if ($time) {
		$hoursMinutesSeconds = explode(":", $time);

		$minutes = $hoursMinutesSeconds[0] * 60; // convert hours to minutes
		$minutes += $hoursMinutesSeconds[1]; // add minutes
		return $minutes;
	} else {
		return 0;
	}
}

function format_menittobahasa($time = null)
{
	$hours = floor($time / 60);
	$minutes = $time % 60;

	return "{$hours} jam {$minutes} menit";
}

function format_mnttoxls($time = null)
{
	$hours = str_pad(floor($time / 60), 2, "0"); //str_pad($no_squence, $jml_digit, "0", STR_PAD_LEFT);
	$minutes = str_pad(($time % 60), 2, "0");

	return "$hours:$minutes";
}

function cekJadwal($harinya)
{
	$hari = strtolower($harinya);
	switch ($hari) {
		case 'minggu':
			$hari_ini = ['field_status' => "minggu_status", 'field_start' => 'minggu_start', 'field_end' => 'minggu_end'];
			break;

		case 'senin':
			$hari_ini = ['field_status' => "senin_status", 'field_start' => 'senin_start', 'field_end' => 'senin_end'];
			break;

		case 'selasa':
			$hari_ini = ['field_status' => "selasa_status", 'field_start' => 'selasa_start', 'field_end' => 'selasa_end'];
			break;

		case 'rabu':
			$hari_ini = ['field_status' => "rabu_status", 'field_start' => 'rabu_start', 'field_end' => 'rabu_end'];
			break;

		case 'kamis':
			$hari_ini = ['field_status' => "kamis_status", 'field_start' => 'kamis_start', 'field_end' => 'kamis_end'];
			break;

		case 'jumat':
			$hari_ini = ['field_status' => "jumat_status", 'field_start' => 'jumat_start', 'field_end' => 'jumat_end'];
			break;

		case 'sabtu':
			$hari_ini = ['field_status' => "sabtu_status", 'field_start' => 'sabtu_start', 'field_end' => 'sabtu_end'];
			break;
	}

	return $hari_ini;
}

function getNamaHari($tanggalnya)
{
	$hari = date("D", $tanggalnya);

	switch ($hari) {
		case 'Sun':
			$hari_ini = "Minggu";
			break;

		case 'Mon':
			$hari_ini = "Senin";
			break;

		case 'Tue':
			$hari_ini = "Selasa";
			break;

		case 'Wed':
			$hari_ini = "Rabu";
			break;

		case 'Thu':
			$hari_ini = "Kamis";
			break;

		case 'Fri':
			$hari_ini = "Jumat";
			break;

		case 'Sat':
			$hari_ini = "Sabtu";
			break;

		default:
			$hari_ini = "Tidak di ketahui";
			break;
	}

	return $hari_ini;
}

function getIdBank($source, $keywords)
{
	// BUAT ARRAY UNTUK PENCARIAN BIAR GK LEMOT START
	$search_base = [];
	foreach ($source as $key => $arrsearch) {
		$search_base[$key]['nama_bank'] = $arrsearch['nama_bank'];
	}
	$id_bank = array_keys($search_base, ['nama_bank' => $keywords]);

	return ($id_bank) ? $source[$id_bank[0]]['id_bank'] : 0;
}

function getHariByNumber($nomornya)
{
	switch ($nomornya) {
		case 0:
			$hari_ini = "Minggu";
			break;

		case 1:
			$hari_ini = "Senin";
			break;

		case 2:
			$hari_ini = "Selasa";
			break;

		case 3:
			$hari_ini = "Rabu";
			break;

		case 4:
			$hari_ini = "Kamis";
			break;

		case 5:
			$hari_ini = "Jumat";
			break;

		case 6:
			$hari_ini = "Sabtu";
			break;

		case 7:
			$hari_ini = "Minggu";
			break;

		default:
			$hari_ini = "Tidak di ketahui";
			break;
	}

	return $hari_ini;
}
// Fungsi Tambahan Baru Akhir

/* Create breadcrumb
$data: title as key, and url as value */

function list_files($dir, $subdir = false, $data = [])
{

	$files = scandir($dir . '/' . $subdir);

	$result = $files;

	if ($subdir) {
		foreach ($result as &$val) {
			$val = $subdir . '/' . $val;
		}
	}

	$result = array_merge($data, $result);



	foreach ($files as $file) {
		if ($file == '.' || $file == '..')
			continue;

		if (is_dir($dir . '/' . $subdir . '/' . $file)) {
			$nextdir = $subdir ?  $subdir . '/' . $file : $file;
			$result = list_files($dir, $nextdir, $result, true);
		}
	}


	return $result;
}

function delete_file($path)
{
	if (file_exists($path)) {
		$unlink = unlink($path);
		if ($unlink) {
			return true;
		}
		return false;
	}

	return true;
}

if (!function_exists('breadcrumb')) {
	function breadcrumb($data)
	{
		$separator = '&raquo;';
		echo '<nav aria-label="breadcrumb">
	<ol class="breadcrumb shadow-sm">';
		foreach ($data as $title => $url) {
			if ($url) {
				echo '<li class="breadcrumb-item"><a href="' . $url . '">' . $title . '</a></li>';
			} else {
				echo '<li class="breadcrumb-item active" aria-current="page">' . $title . '</li>';
			}
		}
		echo '
	</ol>
	</nav>';
	}
}

if (!function_exists('set_value')) {
	function set_value($field_name, $default = '')
	{
		$requestService = \Config\Services::request();
		$request = array_merge($requestService->getGet() ?? [], $requestService->getPost() ?? []);
		$search = $field_name;

		// If Array
		$is_array = false;
		if (strpos($search, '[')) {
			$is_array = true;
			$exp = explode('[', $field_name);
			$field_name = $exp[0];
		}

		if (isset($request[$field_name])) {
			if ($is_array) {
				$exp_close = explode(']', $exp[1]);
				$index = $exp_close[0];
				return $request[$field_name][$index];
			}
			return $request[$field_name];
		}
		return $default;
	}
}

function format_tanggal($date, $format = 'dd mmmm yyyy')
{
	if ($date == '0000-00-00' || $date == '0000-00-00 00:00:00' || $date == '')
		return $date;

	$time = '';
	// Date time
	if (strlen($date) == 19) {
		$exp = explode(' ', $date);
		$date = $exp[0];
		$time = ' ' . $exp[1];
	}

	$format = strtolower($format);
	$new_format = $date;

	list($year, $month, $date) = explode('-', $date);
	if (strpos($format, 'dd') !== false) {
		$new_format = str_replace('dd', $date, $format);
	}

	if (strpos($format, 'mmmm') !== false) {
		$bulan = nama_bulan();
		$new_format = str_replace('mmmm', $bulan[($month * 1)], $new_format);
	} else if (strpos($format, 'mm') !== false) {
		$new_format = str_replace('mm', $month, $new_format);
	}

	if (strpos($format, 'yyyy') !== false) {
		$new_format = str_replace('yyyy', $year, $new_format);
	}
	return $new_format . $time;
}

function prepare_datadb($data)
{
	$request = \Config\Services::request();
	foreach ($data as $field) {
		$result[$field] = $request->getPost($field);
	}
	return $result;
}

function theme_url()
{
	return $config['base_url'] . 'themes/modern';
}

function module_url($action = false)
{

	$config = new \Config\App();
	$url = $config->baseURL;

	$session = session();
	$web = $session->get('web');
	$nama_module = $web['nama_module'];

	$url .= $nama_module;

	$request = \Config\Services::request();
	$getAction = $request->getGet('action');
	if (!empty($getAction) && $getAction != 'index' && $action) {
		$url .= $getAction;
	}

	return $url;
}

function cek_hakakses($action, $param = false)
{
	global $list_action;
	global $app_module;

	$allowed = $list_action[$action];
	if ($allowed == 'no') {
		// echo 'Anda tidak berhak mengakses halaman ini ' . $app_module['judul_module']; die;
		$app_module['nama_module'] = 'error';
		load_view('views/error.php', ['status' => 'error', 'message' => 'Anda tidak berhak mengakses halaman ini']);
	}
}
/*
	$message = ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
	show_message($message);
	
	$msg = ['status' => 'ok', 'content' => 'Data berhasil disimpan'];
	show_message($msg['content'], $msg['status']);
	
	$error = ['role_name' => ['Data sudah ada di database', 'Data harus disi']];
	show_message($error, 'error');
	
	$error = ['Data sudah ada di database', 'Data harus disi'];
	show_message($error, 'error');
*/
function show_message($message, $type = null, $dismiss = true)
{
	//<ul class="list-error">
	if (is_array($message)) {

		// $message = ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
		if (key_exists('status', $message)) {
			$type = $message['status'];
			if (key_exists('message', $message)) {
				$message_source = $message['message'];
			} else if (key_exists('content', $message)) {
				$message_source = $message['content'];
			}


			if (is_array($message_source)) {
				$message_content = $message_source;
			} else {
				$message_content[] = $message_source;
			}
		} else {
			if (is_array($message)) {
				foreach ($message as $key => $val) {
					if (is_array($val)) {
						foreach ($val as $key2 => $val2) {
							$message_content[] = $val2;
						}
					} else {
						$message_content[] = $val;
					}
				}
			}
		}
		// print_r($message_content);
		if (count($message_content) > 1) {

			$message_content = recursive_loop($message_content);
			$message = '<ul><li>' . join('</li><li>', $message_content) . '</li></ul>';
		} else {
			// echo '<pre>'; print_r($message_content);
			$message_content = recursive_loop($message_content);
			// echo '<pre>'; print_r($message_content);
			$message = $message_content[0];
		}
	}

	switch ($type) {
		case 'error':
			$alert_type = 'danger';
			break;
		case 'warning':
			$alert_type = 'warning';
			break;
		default:
			$alert_type = 'success';
			break;
	}

	$close_btn = '';
	if ($dismiss) {
		$close_btn = '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
	}

	echo '<div class="alert alert-dismissible fade show alert-' . $alert_type . '" role="alert">' . $message . $close_btn . '</div>';
}

function recursive_loop($array, $result = [])
{
	foreach ($array as $val) {
		if (is_array($val)) {
			$result = recursive_loop($val, $result);
		} else {
			$result[] = $val;
		}
	}
	return $result;
}

function show_alert($message, $title = null, $dismiss = true)
{

	if (is_array($message)) {
		// $message = ['status' => 'ok', 'message' => 'Data berhasil disimpan'];
		if (key_exists('status', $message)) {
			$type = $message['status'];
		}

		if (key_exists('message', $message)) {
			$message = $message['message'];
		}

		if (is_array($message)) {
			foreach ($message as $key => $val) {
				if (is_array($val)) {
					foreach ($val as $key2 => $val2) {
						$message_content[] = $val2;
					}
				} else {
					$message_content[] = $val;
				}
			}

			if (count($message_content) > 1) {
				$message = '<ul><li>' . join($message_content, '</li><li>') . '</li></ul>';
			} else {
				$message = $message_content[0];
			}
		}
	}

	if (!$title) {
		switch ($type) {
			case 'error':
				$title = 'ERROR !!!';
				$icon_type = 'error';
				break;
			case 'warning':
				$title = 'WARNIG !!!';
				$icon_type = 'error';
				break;
			default:
				$title = 'SUKSES !!!';
				$icon_type = 'success';
				break;
		}
	}

	echo '<script type="text/javascript">
			Swal.fire({
				title: "' . $title . '",
				html: "' . $message . '",
				icon: "' . $icon_type . '",
				showCloseButton: ' . $dismiss . ',
				confirmButtonText: "OK"
			})
		</script>';
}

function nama_bulan()
{
	return [1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
}

function calc_discount($data)
{
	if ($data->unit == 'rp') {
		return $data->voucher_value;
	} elseif ($data->unit == '%') {
		return $data->voucher_value / 100 * $data->amount;
	}

	return 0;
}

function is_ajax_request()
{
	if (key_exists('HTTP_X_REQUESTED_WITH', $_SERVER)) {
		return $_SERVER['HTTP_X_REQUESTED_WITH'] && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
	}
	return false;
}

function format_date($tgl, $nama_bulan = true)
{
	if ($tgl == '0000-00-00 00:00:00' || !$tgl) {
		return false;
	}
	$exp = explode(' ', $tgl);
	$exp_tgl = explode('-', $exp[0]);
	$bulan = nama_bulan();
	return $exp_tgl[2] . ' ' . $bulan[(int) $exp_tgl[1]] . ' ' . $exp_tgl[0];
}

function clean_number($value)
{
	$value = str_replace('.', '', $value);
	$value = str_replace(',', '.', $value);
	return $value;
}

function format_number($value, $is_float = false)
{
	if ($value == 0)
		return 0;

	if ($value == '')
		return '';

	if (!is_numeric($value))
		return '';

	if (empty($value))
		return;

	$minus = substr($value, 0, 1);
	if ($minus != '-') {
		$minus = '';
	}

	if ($is_float) {
		$exp = explode('.', $value);
		// print_r($exp);
		if (count($exp) == 2) {
			$ribuan = preg_replace('/\D/', '', $exp[0]);
			$koma = trim(str_replace('0', '', $exp[1]));
			if ($koma) {
				return $minus . number_format($ribuan, 0, ',', '.') . ',' . $koma;
			} else {
				return $minus . number_format($ribuan, 0, ',', '.');
			}
		}
	}

	$value = preg_replace('/\D/', '', $value);
	// echo $value . '#';

	return $minus . number_format($value, 0, ',', '.');
}

function format_datedb($tgl)
{
	if ($tgl == '0000-00-00 00:00:00' || !$tgl) {
		return false;
	}
	$exp = explode(' ', $tgl);
	$exp_tgl = explode('-', $exp[0]);
	return $exp_tgl[2] . '-' . $exp_tgl[1] . '-' . $exp_tgl[0];
}

function format_size($size)
{
	if ($size > 1024 * 1024) {
		return round($size / (1024 * 1024), 2) . 'Mb';
	} else {
		return round($size / 1024, 2) . 'Kb';
	}
}

function set_depth(&$result, $depth = 0)
{
	foreach ($result as $key => &$val) {
		$val['depth'] = $depth;
		if (key_exists('children', $val)) {
			set_depth($val['children'], $val['depth'] + 1);
		}
	}
}

function kategori_list($result)
{
	// print_r($result); 
	$refs = array();
	$list = array();

	foreach ($result as $key => $data) {
		if (!$key || empty($data['id_barang_kategori'])) // Highlight OR No parent
			continue;

		$thisref = &$refs[$data['id_barang_kategori']];
		foreach ($data as $field => $value) {
			$thisref[$field] = $value;
		}

		// no parent
		if ($data['id_parent'] == 0) {

			$list[$data['id_barang_kategori']] = &$thisref;
		} else {

			$thisref['depth'] = ++$refs[$data['id_barang_kategori']]['depth'];
			$refs[$data['id_parent']]['children'][$data['id_barang_kategori']] = &$thisref;
		}
	}
	set_depth($list);
	return $list;
}

function menu_list($result)
{
	$refs = array();
	$list = array();
	foreach ($result as $key => $data) {
		if (!$key || empty($data['id_menu'])) // Highlight OR No parent
			continue;

		$thisref = &$refs[$data['id_menu']];
		foreach ($data as $field => $value) {
			$thisref[$field] = $value;
		}

		// no parent
		if ($data['id_parent'] == 0) {

			$list[$data['id_menu']] = &$thisref;
		} else {

			$thisref['depth'] = ++$refs[$data['id_menu']]['depth'];
			$refs[$data['id_parent']]['children'][$data['id_menu']] = &$thisref;
		}
	}
	set_depth($list);
	return $list;
}

function build_menu($current_module, $arr_menu, $submenu = false)
{
	$menu = "\n" . '<ul' . $submenu . '>' . "\r\n";

	foreach ($arr_menu as $key => $val) {
		// echo '<pre>ff'; print_r($arr); die;
		if (!$key)
			continue;

		// Check new
		$new = '';
		if (key_exists('new', $val)) {
			$new = $val['new'] == 1 ? '<span class="menu-baru">NEW</span>' : '';
		}
		$arrow = key_exists('children', $val) ? '<span class="pull-right-container">
								<i class="fa fa-angle-left arrow"></i>
							</span>' : '';
		$has_child = key_exists('children', $val) ? 'has-children' : '';

		if ($has_child) {
			$url = '#';
			$onClick = ' onclick="javascript:void(0)"';
		} else {
			$onClick = '';
			$url = $val['url'];
		}

		// class attribute for <li>
		$class_li = [];
		if ($current_module['nama_module'] == $val['nama_module']) {
			$class_li[] = 'tree-open';
		}

		if ($val['highlight']) {
			$class_li[] = 'highlight tree-open';
		}

		if ($class_li) {
			$class_li = ' class="' . join(' ', $class_li) . '"';
		} else {
			$class_li = '';
		}

		// Class attribute for <a>, children of <li>
		$class_a = ['depth-' . $val['depth']];
		if ($has_child) {
			$class_a[] = 'has-children';
		}

		$class_a = ' class="' . join(' ', $class_a) . '"';

		// Menu icon
		$menu_icon = '';
		if ($val['class']) {
			$menu_icon = '<i class="sidebar-menu-icon ' . $val['class'] . '"></i>';
		}

		// Menu
		$config = new \Config\App();

		if (substr($url, 0, 4) != 'http') {
			$url = $config->baseURL . $url;
		}
		$menu .= '<li' . $class_li . '>
					<a ' . $class_a . ' href="' . $url . '"' . $onClick . '>' .
			'<span class="menu-item">' .
			$menu_icon .
			'<span class="text">' . $val['nama_menu'] . '</span>' .
			'</span>' .
			$arrow .
			'</a>' . $new;

		if (key_exists('children', $val)) {
			$menu .= build_menu($current_module, $val['children'], ' class="submenu"');
		}
		$menu .= "</li>\n";
	}
	$menu .= "</ul>\n";
	return $menu;
}

function email_content($content)
{
	return '<html>
	<head>
	<style>
	body{
		font-family: "segoe ui", "open sans", arial;
		font-size: 16px;
	}
	h1, h2, h3, h4, h5, h6, ul, ol, p {
		margin: 0;
		padding: 0;
	}
	ul.list-circle {
		list-style: circle;
	}
	ul.list-circle li{
		margin-left: 25px;
	}
	h1 {
		font-weight: normal;
		font-size: 200%;
	}
	h2 {
		font-weight: normal;
		font-size: 150%;
	}
	.box-title {
		text-align: center;
	}
	ul li{
		font-size: 16px;
	}
	.button {
		text-decoration:none;
		display:inline-block;
		margin-bottom:0;
		font-weight:normal;
		text-align:center;
		vertical-align:middle;
		background-image:none;
		border:1px solid transparent;
		white-space:nowrap;
		padding:7px 15px;
		line-height:1.5384616;
		background-color:#0277bd;
		border-color:#0277bd;
		color:#FFFFFF;
	}
	.button span {
		font-family:arial,helvetica,sans-serif;
		font-size: 16px;
		color:#FFFFFF;
	}
	p {
		font-size: 16px;
		line-height: 1.5;
		margin: 15px 0;
	}
	mb-15{
		margin-bottom:15px;
	}
	hr {
		border: 0;
		border-bottom: 1px solid #CCCCCC;
	}
	.mt-10 { margin-top: 10px}
	.mb-5 { margin-bottom: 5px }
	.mb-10 { margin-bottom: 10px }
	.mb-20 { margin-bottom: 20px }
	.mb-40 { margin-bottom: 40px }
	p {
		margin-top: 7px;
		margin-bottom: 7px;
	}
	.thankyou h1 {
		font-weight: normal;
		font-size: 200%;
	}
	.thankyou h2 {
		font-weight: normal;
		font-size: 150%;
	}
	.thankyou h3 {
		font-weight: normal;
		font-size: 120%;
	}
	.aligncenter  {
		text-align: center;
	}
	.alert {
		display: inline-block;
		margin-bottom: 0;
		font-weight: normal;
		text-align: left;
		vertical-align: middle;
		background-image: none;
		border: 1px solid transparent;
		padding: 7px 15px;
		line-height: 1.5384616;
		background-color: #ffb4b4;
		border-color: #ff9c9c;
		color: #c34949;
		font-size: 16px;
	}
	</style>
	</head>
	<body>' . $content . '</body>
	</html>';
}

function send_email($to, $subject, $message, $from = 'no-reply@indopasifik.id')
{
	$email = \Config\Services::email();
	$email->setTo($to);
	$email->setFrom($from, 'PAYDAY MAIL NOTIFICATION'); // Change to your email
	$email->setSubject($subject);
	$email->setMessage($message);
	$email->setNewline("\r\n");
    $email->setCRLF("\r\n");
	if ($email->send()) {
		return true;
	} else {
		return $email->printDebugger(['headers']);
	}
}

function send_email_queue($id_cli, $ref_table, $to, $subject, $message, $from = 'no-reply@indopasifik.id')
{
	$db = \Config\Database::connect();

	$builder = $db->table('hrm_email_queue'); // ubah jika nama tabel berbeda
    $data = [
        'id_cli' 			=> $id_cli,
        'refference' 		=> $ref_table,
        'recipient_email' 	=> $to,
        'subject'         	=> $subject,
        'message'         	=> $message,
        'created_at'      	=> date('Y-m-d H:i:s'),
        'status'          	=> 'pending'
    ];

    // Simpan ke database
    $insert = $builder->insert($data);

    if ($insert) {
        return true;
    } else {
        return $db->error();
    }
}

function mail_cli($jenis = null, $nama_atasan = null, $nama = null, $tanggal = null, $alasan = null, $link_approve = '', $link_reject = '', $image_link = '', $title = "Notifikasi Pengajuan CLI")
{
	$img_div = "";
	if (empty($link)) {
		$link = base_url();
	}

	if (!empty($image_link)) {
		$img_div = "
			<div class='image-preview' style='text-align:center; margin:20px 0;'>
				<a href='$image_link' target='_blank'>
					<img src='$image_link' alt='Preview Image' style='max-width:200px; height:auto; border-radius:5px; box-shadow:0px 2px 6px rgba(0,0,0,0.1);'>
				</a>
				<p style='font-size:12px; color:#777;'>Click to view full image</p>
			</div>
		";
	}

	return '
		<!DOCTYPE html>
		<html lang="id">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>' . $title . '</title>
			<style>
				body {
					font-family: Arial, sans-serif;
					background-color: #f4f4f4;
					color: #333;
					padding: 20px;
				}
				.email-container {
					max-width: 600px;
					margin: 0 auto;
					background: #ffffff;
					padding: 20px;
					border-radius: 8px;
					box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
				}
				h2 {
					color: #0277bd;
					text-align: center;
				}
				p {
					font-size: 16px;
					line-height: 1.5;
				}
				.details {
					background: #f1f1f1;
					padding: 15px;
					border-radius: 5px;
					margin: 10px 0;
				}
				.button-container {
					text-align: center;
					margin-top: 20px;
				}
				.button {
					display: inline-block;
					padding: 10px 20px;
					font-size: 16px;
					color: #ffffff;
					text-decoration: none;
					border-radius: 5px;
					margin: 5px;
					text-align: center;
				}
				.approve {
					background-color: #28a745; /* Hijau */
				}
				.approve:hover {
					background-color: #218838;
				}
				.reject {
					background-color: #dc3545; /* Merah */
				}
				.reject:hover {
					background-color: #c82333;
				}
				.footer {
					text-align: center;
					font-size: 14px;
					color: #777;
					margin-top: 20px;
				}
			</style>
		</head>
		<body>
			<div class="email-container">
				<h2>Notifikasi Pengajuan ' . $jenis . '</h2>
				<p>Halo ' . $nama_atasan . ',</p>
				<p>Terdapat permohonan <strong>[' . $jenis . ']</strong>.</p>
				
				<div class="details">
					<p><strong>Nama Karyawan:</strong> ' . $nama . '</p>
					<p><strong>Tanggal Pengajuan:</strong> ' . $tanggal . '</p>
					<p><strong>Alasan:</strong> ' . $alasan . '</p>
				</div>

				' . $img_div . '
				
				<p>Silakan berikan respons segera.</p>
				
				<div class="button-container">
					<a href="' . $link_approve . '" class="button approve">Setujui</a>
					<a href="' . $link_reject . '" class="button reject">Tolak</a>
				</div>
				
				<p>Terima kasih.</p>
				
				<div class="footer">
					<p>PAYDAY HRM MAIL NOTIFICATION</p>
					<p>Email ini dikirim secara otomatis, mohon tidak membalas.</p>
				</div>
			</div>
		</body>
		</html>
	';
}

function mail_cli_rejection($jenis = null, $nama_atasan = null, $nama = null, $tanggal = null, $alasan = null, $alasan_penolakan = null, $title = "Notifikasi Penolakan CLI")
{
	$link = base_url();
	return '
		<!DOCTYPE html>
		<html lang="id">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>' . $title . '</title>
			<style>
				body {
					font-family: Arial, sans-serif;
					background-color: #f4f4f4;
					color: #333;
					padding: 20px;
				}
				.email-container {
					max-width: 600px;
					margin: 0 auto;
					background: #ffffff;
					padding: 20px;
					border-radius: 8px;
					box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
				}
				h2 {
					color: #d32f2f;
					text-align: center;
				}
				p {
					font-size: 16px;
					line-height: 1.5;
				}
				.details {
					background: #f1f1f1;
					padding: 15px;
					border-radius: 5px;
					margin: 10px 0;
				}
				.alert {
					background-color: #ffb4b4;
					color: #c34949;
					padding: 10px;
					border-radius: 5px;
					margin: 10px 0;
				}
				.button {
					display: inline-block;
					padding: 10px 20px;
					font-size: 16px;
					color: #ffffff;
					background-color: #d32f2f;
					text-decoration: none;
					border-radius: 5px;
					margin-top: 10px;
					text-align: center;
				}
				.button:hover {
					background-color: #b71c1c;
				}
				.footer {
					text-align: center;
					font-size: 14px;
					color: #777;
					margin-top: 20px;
				}
			</style>
		</head>
		<body>
			<div class="email-container">
				<h2>Notifikasi Penolakan ' . $jenis . '</h2>
				<p>Halo ' . $nama . ',</p>
				<p>Pengajuan <strong>[' . $jenis . ']</strong> yang Anda ajukan telah <strong>DITOLAK</strong> oleh ' . $nama_atasan . '.</p>
				
				<div class="details">
					<p><strong>Nama Karyawan:</strong> ' . $nama . '</p>
					<p><strong>Tanggal Pengajuan:</strong> ' . $tanggal . '</p>
					<p><strong>Alasan Pengajuan:</strong> ' . $alasan . '</p>
				</div>

				<div class="alert">
					<p><strong>Alasan Penolakan:</strong> ' . $alasan_penolakan . '</p>
				</div>
				
				<p>Jika Anda ingin mengajukan kembali, silakan buat kembali pengajuan Anda.</p>
				
				<p style="text-align: center;">
					<a style="color: white" href="' . $link . '" class="button">Ajukan Ulang</a>
				</p>
				
				<p>Terima kasih.</p>
				
				<div class="footer">
					<p>PAYDAY HRM MAIL NOTIFICATION</p>
					<p>Email ini dikirim secara otomatis, mohon tidak membalas.</p>
				</div>
			</div>
		</body>
		</html>
	';
}

function mail_cli_approval($jenis = null, $nama_atasan = null, $nama = null, $tanggal = null, $alasan = null, $title = "Notifikasi Persetujuan Pengajuan CLI")
{
	$link = base_url();
	return '
		<!DOCTYPE html>
		<html lang="id">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>' . $title . '</title>
			<style>
				body {
					font-family: Arial, sans-serif;
					background-color: #f4f4f4;
					color: #333;
					padding: 20px;
				}
				.email-container {
					max-width: 600px;
					margin: 0 auto;
					background: #ffffff;
					padding: 20px;
					border-radius: 8px;
					box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
				}
				h2 {
					color: #388e3c;
					text-align: center;
				}
				p {
					font-size: 16px;
					line-height: 1.5;
				}
				.details {
					background: #f1f1f1;
					padding: 15px;
					border-radius: 5px;
					margin: 10px 0;
				}
				.success {
					background-color: #c8e6c9;
					color: #256029;
					padding: 10px;
					border-radius: 5px;
					margin: 10px 0;
				}
				.button {
					display: inline-block;
					padding: 10px 20px;
					font-size: 16px;
					color: #ffffff;
					background-color: #388e3c;
					text-decoration: none;
					border-radius: 5px;
					margin-top: 10px;
					text-align: center;
				}
				.button:hover {
					background-color: #2e7d32;
				}
				.footer {
					text-align: center;
					font-size: 14px;
					color: #777;
					margin-top: 20px;
				}
			</style>
		</head>
		<body>
			<div class="email-container">
				<h2>Notifikasi Persetujuan ' . $jenis . '</h2>
				<p>Halo ' . $nama . ',</p>
				
				<div class="details">
					<p><strong>Nama Karyawan:</strong> ' . $nama . '</p>
					<p><strong>Tanggal Pengajuan:</strong> ' . $tanggal . '</p>
					<p><strong>Alasan Pengajuan:</strong> ' . $alasan . '</p>
				</div>

				<div class="success">
					<p><strong>Status:</strong>- Disetujui oleh ' . $nama_atasan . ' <br />- Menunggu Konfirmasi Akhir dari HRD</p>
				</div>
				
				<p>Silakan menunggu notifikasi lebih lanjut terkait keputusan akhir dari HRD.</p>
				
				<p style="text-align: center;">
					<a style="color: white" href="' . $link . '" class="button">Lihat Status</a>
				</p>
				
				<p>Terima kasih.</p>
				
				<div class="footer">
					<p>PAYDAY HRM MAIL NOTIFICATION</p>
					<p>Email ini dikirim secara otomatis, mohon tidak membalas.</p>
				</div>
			</div>
		</body>
		</html>
	';
}

function generateResponsePage($status, $message, $icon)
{
	return '
    <!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Pengajuan ' . $status . '</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                text-align: center;
                padding: 50px;
            }
            .container {
                background: #ffffff;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
                max-width: 500px;
                margin: auto;
            }
            .status-icon {
                font-size: 60px;
                color: ' . ($status === "Disetujui" ? "#28a745" : "#dc3545") . ';
            }
            h2 {
                color: #333;
            }
            p {
                font-size: 16px;
                color: #555;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="status-icon">' . $icon . '</div>
            <h2>Pengajuan ' . $status . '</h2>
            <p>' . $message . '</p>
        </div>
    </body>
    </html>';
}

function generateConfirmPage($encrypt)
{
	return '
    <!DOCTYPE html>
	<html lang="id">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Konfirmasi Penolakan</title>
		<style>
			body {
				font-family: Arial, sans-serif;
				background-color: #f4f4f4;
				text-align: center;
				padding: 50px;
			}
			.container {
				background: #ffffff;
				padding: 30px;
				border-radius: 10px;
				box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
				max-width: 500px;
				margin: auto;
			}
			h2 {
				color: #333;
			}
			p {
				font-size: 16px;
				color: #555;
			}
			textarea {
				width: 100%;
				height: 100px;
				margin-top: 10px;
				padding: 10px;
				border-radius: 5px;
				border: 1px solid #ccc;
				font-size: 16px;
				resize: none;
			}
			.button {
				display: inline-block;
				padding: 12px 20px;
				font-size: 16px;
				color: #ffffff;
				text-decoration: none;
				border-radius: 5px;
				cursor: pointer;
				border: none;
				margin-top: 10px;
			}
			.save {
				background-color: #dc3545;
			}
			.save:hover {
				background-color: #c82333;
			}

			/* MODAL STYLE */
			.modal {
				display: none;
				position: fixed;
				z-index: 1;
				left: 0;
				top: 0;
				width: 100%;
				height: 100%;
				background-color: rgba(0, 0, 0, 0.4);
			}
			.modal-content {
				background-color: #fff;
				margin: 15% auto;
				padding: 20px;
				border-radius: 10px;
				width: 400px;
				text-align: center;
				box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
			}
			.modal-content p {
				color: #333;
			}
			.close-btn {
				background-color: #dc3545;
				color: #fff;
				padding: 10px 20px;
				border: none;
				border-radius: 5px;
				cursor: pointer;
				margin-top: 10px;
			}
			.close-btn:hover {
				background-color: #b02a37;
			}
		</style>
	</head>
	<body>
		<div class="container">
			<h2>Konfirmasi Penolakan</h2>
			<p>Masukkan alasan penolakan Anda di bawah ini:</p>
			
			<form id="rejectForm" action="" method="POST">
				<input type="hidden" name="request_id" value="' . $encrypt . '">
				<textarea id="reject_reason" name="reject_reason" placeholder="Tulis alasan penolakan pengajuan..."></textarea>
				<button type="submit" class="button save">Proses</button>
			</form>
		</div>

		<!-- MODAL -->
		<div id="myModal" class="modal">
			<div class="modal-content">
				<p>⚠️ Alasan penolakan wajib diisi!</p>
				<button class="close-btn" onclick="closeModal()">Tutup</button>
			</div>
		</div>

		<script>
			document.getElementById("rejectForm").addEventListener("submit", function(event) {
				var reason = document.getElementById("reject_reason").value.trim();
				if (reason === "") {
					event.preventDefault(); // Mencegah form dikirim
					document.getElementById("myModal").style.display = "block"; // Tampilkan modal
				}
			});

			function closeModal() {
				document.getElementById("myModal").style.display = "none"; // Sembunyikan modal
			}
		</script>
	</body>
	</html>';
}

function formatTimeToWords($time)
{
	list($hours, $minutes, $seconds) = explode(':', $time);

	$result = [];
	if ($hours > 0) {
		$result[] = ltrim($hours, '0') . ' Jam';
	}
	if ($minutes > 0) {
		$result[] = ltrim($minutes, '0') . ' Menit';
	}

	return implode(' ', $result);
}

function formatJenisLeave($jenis_leave)
{
	// Define the list of "Cuti" categories
	$cuti_categories = ['tahunan', 'melahirkan', 'menikah', 'keguguran', 'keluarga meninggal', 'khusus'];

	// Convert input to lowercase for case-insensitive comparison
	$jenis_lower = strtolower($jenis_leave);

	// Check if the leave type exists in the cuti category
	if (in_array($jenis_lower, $cuti_categories)) {
		return 'Cuti ' . ucwords($jenis_leave);
	}

	// If not in "Cuti" category, return as-is
	return ucwords($jenis_leave);
}

function sendWa($to, $msg, $image)
{
	// dd($image);
	$curl = curl_init();

	curl_setopt_array($curl, array(
		CURLOPT_URL => 'joni.watzap.id/api/send_message',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => array(
			'target' => $to,
			'message' => $msg,
			'url' => $image,
			//   'filename' => 'my-file.pdf'
		),
		CURLOPT_HTTPHEADER => array(
			'Authorization: xxxx-xxxx-xxxx'
		),
	));
	$response = curl_exec($curl);
	curl_close($curl);
	// 		dd($image);
	// 		echo($response);
}

function formConvertHtmlWA($htmData)
{

	$html = str_replace(["\r", "\n"], "", $htmData);
	$html = str_ireplace(["<br>", "<br/>", "<br />"], '\n', $html);


	$html = str_ireplace('<li>', '. ', $html);
	$html = str_ireplace('</li>', '\n', $html);


	$html = str_ireplace(['<p>', '</p>'], '\n', $html);


	$html = str_ireplace(['</ul>', '</ol>'], '\n', $html);


	$html = preg_replace('/<strong[^>]>(.?)<\/strong>/i', '$1', $html);


	$html = preg_replace('/<span[^>]>(.?)<\/span>/i', '$1', $html);
	$text = strip_tags($html);
	$text = preg_replace("/[ \t]+/", " ", $text);
	$text = preg_replace("/(\n){2,}/", "\n", $text);


	return $text;
}

function sendWaResetPassword($to, $url)
{
	$curl = curl_init();
	$msg_html = '
	<p>Berikut adalah link untuk mereset password Anda:</p>
	<p>' . $url . '</p>
	<p>Jika Anda tidak merasa melakukan permintaan reset password, abaikan pesan ini.</p>
	<p><strong>Jangan bagikan link ini kepada siapapun untuk menjaga keamanan akun Anda.</strong></p>
	';

	$msg = formConvertHtmlWA($msg_html);

	curl_setopt_array($curl, array(
		CURLOPT_URL => 'whatsapp-endpoint.watzap.id/api/send-message',
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_ENCODING => '',
		CURLOPT_MAXREDIRS => 10,
		CURLOPT_TIMEOUT => 0,
		CURLOPT_FOLLOWLOCATION => true,
		CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST => 'POST',
		CURLOPT_POSTFIELDS => '{
        "api_key": "xxxx-xxxx-xxxx",
        "number_key": "xxxx-xxxx-xxxx",
        "phone_no": "' . $to . '",
        "message": "' . $msg . '",
        "wait_until_send": "1" 
    }',
		CURLOPT_HTTPHEADER => array(
			'Content-Type: application/json',
			'Cookie: PHPSESSID=fpjn0p2vbhrl1ldgvh6qm18771; X_URL_PATH=aHR0cHM6Ly9jb3JlLndhdHphcC5pZC98fHx8fHN1c3VrYWNhbmc%3D'
		),
	));

	$response = curl_exec($curl);
	curl_close($curl);
}



function access_dashboard_probation()
{
	return $id_role = [1, 10, 11];
}

function jenis_cuti_config()
{
	$db = \Config\Database::connect();
	$jenis_cuti = $db->query("SELECT strval_config FROM core_config WHERE param_config = 'jenis_cuti' and isDeleted = 0")->getResult();
	$result = array_map(function ($row) {
		return $row->strval_config;
	}, $jenis_cuti);

	return $result;
}

function batas_sinkronisasi()
{
	$db = \Config\Database::connect();
	$result = $db->query("SELECT intval_config FROM core_config WHERE nama_config = 'batas_sinkronisasi' and isDeleted = 0")->getRow()->intval_config;
	return $result;
}

function fitur_singkronisasi()
{
	$db = \Config\Database::connect();
	$result = $db->query("SELECT intval_config FROM core_config WHERE nama_config = 'fitur_sinkronisasi' and isDeleted = 0")->getRow()->intval_config;
	return $result;
}

function jumlah_jhk_config($hari_libur)
{
	$periode_berjalan = config_periode_berjalan();
	$tgl_merah = array_map(fn($tgl) => $tgl->tgl_holiday, tgl_merah($periode_berjalan)); // Ambil hanya tanggal
	$start_date = $periode_berjalan['start_period'];
	$end_date = $periode_berjalan['end_period'];

	$start = new DateTime($start_date);
	$end = new DateTime($end_date);
	$end->modify('+1 day');

	$jumlah_hari = 0;
	while ($start < $end) {
		$current_date = $start->format('Y-m-d');
		// Cek jika hari saat ini tidak ada di array $hari_libur dan $tgl_merah
		if (!in_array($start->format('l'), $hari_libur) && !in_array($current_date, $tgl_merah)) {
			$jumlah_hari++; // Hari kerja
		}
		$start->modify('+1 day'); // Pindah ke hari berikutnya
	}

	return $jumlah_hari;
}

function kouta_cuti_setahun_config()
{
	$db = \Config\Database::connect();
	$kuota_cuti = $db->query("SELECT intval_config FROM core_config WHERE param_config = 'kuota_cuti' and isDeleted = 0")->getRow()->intval_config;
	return $kuota_cuti;
}

function config_periode_berjalan()
{
	$db = \Config\Database::connect();
	$start_date = $db->query("SELECT intval_config FROM core_config WHERE nama_config = 'start_period' and isDeleted = 0")->getRow()->intval_config;
	$end_date = $db->query("SELECT intval_config FROM core_config WHERE nama_config = 'end_period' and isDeleted = 0")->getRow()->intval_config;

	$current_date = new DateTime();

	// Mendapatkan hari, bulan, dan tahun saat ini
	$current_day = (int)$current_date->format('d');
	$current_month = (int)$current_date->format('m');
	$current_year = (int)$current_date->format('Y');

	// Kondisi 1: Jika tanggal saat ini di bawah 21
	if ($current_day < $start_date) {
		// start_period: tanggal 21 bulan sebelumnya
		$start_period = new DateTime("$current_year-$current_month-$start_date");
		$start_period->modify('-1 month');

		// end_period: tanggal 20 bulan ini
		$end_period = new DateTime("$current_year-$current_month-$end_date");
	}

	// Kondisi 2: Jika tanggal saat ini >= 21
	else {
		// start_period: tanggal 21 bulan ini
		$start_period = new DateTime("$current_year-$current_month-$start_date");

		// end_period: tanggal 20 bulan depan
		$end_period = new DateTime("$current_year-$current_month-$end_date");
		$end_period->modify('+1 month');
	}

	return [
		'start_period' => $start_period->format('Y-m-d'),
		'end_period'   => $end_period->format('Y-m-d')
	];
}

if (!function_exists('format_tanggal_indo')) {
	function format_tanggal_indo($tanggal)
	{
		$hari = array(
			'Sunday' => 'Minggu',
			'Monday' => 'Senin',
			'Tuesday' => 'Selasa',
			'Wednesday' => 'Rabu',
			'Thursday' => 'Kamis',
			'Friday' => 'Jumat',
			'Saturday' => 'Sabtu'
		);

		$bulan = array(
			'January' => 'Januari',
			'February' => 'Februari',
			'March' => 'Maret',
			'April' => 'April',
			'May' => 'Mei',
			'June' => 'Juni',
			'July' => 'Juli',
			'August' => 'Agustus',
			'September' => 'September',
			'October' => 'Oktober',
			'November' => 'November',
			'December' => 'Desember'
		);

		$namaHari = $hari[date('l', strtotime($tanggal))];
		$namaBulan = $bulan[date('F', strtotime($tanggal))];

		// Format: Jumat, 11 Oktober - 2024 - 14:40
		return $namaHari . ', ' . date('d', strtotime($tanggal)) . ' ' . $namaBulan . ' ' . date('Y', strtotime($tanggal));
	}
}

function tglteks($p)
{
	$bln = date('m', strtotime($p));
	$tgl = date('d', strtotime($p));
	$tahun = date('Y', strtotime($p));
	$bulan = '';
	switch ($bln) {
		case 1:
			$bulan = "Januari";
			break;
		case 2:
			$bulan = "Februari";
			break;
		case 3:
			$bulan = "Maret";
			break;
		case 4:
			$bulan = "April";
			break;
		case 5:
			$bulan = "Mei";
			break;
		case 6:
			$bulan = "Juni";
			break;
		case 7:
			$bulan = "Juli";
			break;
		case 8:
			$bulan = "Agustus";
			break;
		case 9:
			$bulan = "September";
			break;
		case 10:
			$bulan = "Oktober";
			break;
		case 11:
			$bulan = "November";
			break;
		case 12:
			$bulan = "Desember";
			break;
	}
	return $tgl . ' ' . $bulan . ' ' . $tahun;
}

function tgl_merah($periode_berjalan = false, $single_date = false)
{
	$db = \Config\Database::connect();
	$where_periode = "";
	if ($periode_berjalan) {
		$start_period = $periode_berjalan['start_period'];
		$end_period = $periode_berjalan['end_period'];
		$where_periode = " AND DATE(tgl_holiday) >= '$start_period' AND DATE(tgl_holiday) <= '$end_period' ";
	}

	if ($single_date) {
		$where_periode = " AND  DATE(tgl_holiday) = '$single_date' ";
	}

	$tgl_cuti = $db->query("SELECT tgl_holiday, jenis_holiday FROM hrm_setholiday WHERE isDeleted = 0 $where_periode")->getResult();
	// print_r($tgl_cuti);die;

	return $tgl_cuti;
}

function cek_anomali_kehadiran($data_jadwal, $data_kehadiran, $hari_libur, $periode_berjalan)
{
	$start_period = $periode_berjalan['start_period'];
	$end_period = date("Y-m-d");

	// Simpan tanggal kehadiran dalam associative array agar lebih cepat dicari
	$tanggal_kehadiran_tercatat = [];
	foreach ($data_kehadiran as $dk) {
		$tanggal_kehadiran_tercatat[date('Y-m-d', strtotime($dk['tgl_data']))] = true;
	}

	// Konversi hari libur dan tanggal merah ke associative array agar pencarian lebih cepat
	$tgl_merah = array_flip(array_map(fn($tgl) => $tgl->tgl_holiday, tgl_merah($periode_berjalan)));
	$hari_libur_flip = array_flip($hari_libur);

	// Looping dari start sampai end period untuk mencari tanggal yang tidak ada dalam kehadiran
	$current_date = new DateTime($start_period);
	$end_date = new DateTime($end_period);
	$end_date->modify('+1 day'); // Termasuk hari terakhir

	while ($current_date < $end_date) {
		$tanggal_str = $current_date->format('Y-m-d');
		$hari_str = $current_date->format('l');

		// Cek apakah tanggal sudah tercatat, atau termasuk hari libur atau tanggal merah
		if (
			!isset($tanggal_kehadiran_tercatat[$tanggal_str]) &&
			!isset($hari_libur_flip[$hari_str]) &&
			!isset($tgl_merah[$tanggal_str])
		) {
			$data_kehadiran[] = [
				'tgl_data' => $tanggal_str,
				'waktu_in' => null,
				'waktu_out' => null,
				'type_anomali' => 'Alpa',
				'jenis' => 'absen'
			];
		}

		$current_date->modify('+1 day');
	}

	// Pengecekan anomali untuk setiap data kehadiran
	foreach ($data_kehadiran as &$dk) {
		$tanggal_kehadiran = date('Y-m-d', strtotime($dk['tgl_data']));
		$hari = date('l', strtotime($dk['tgl_data']));

		// Deteksi "Lupa Absen" jika hanya salah satu dari waktu_in atau waktu_out yang ada
		if ((empty($dk['waktu_in']) && !empty($dk['waktu_out'])) || (!empty($dk['waktu_in']) && empty($dk['waktu_out']))) {
			$dk['type_anomali'] = 'Lupa Absen';
		}

		// Jika hari libur, langsung lewati tanpa cek anomali
		if (isset($hari_libur_flip[$hari]) || isset($tgl_merah[$tanggal_kehadiran])) {
			$dk['type_anomali'] = 'Hari Libur';
			continue;
		}

		// Jika tidak ada waktu masuk atau keluar
		if (empty($dk['waktu_in']) && empty($dk['waktu_out'])) {
			$dk['type_anomali'] = 'Alpa';
			continue;
		}

		// Jadwal kerja berdasarkan hari
		$jadwal_kerja = [
			'Monday'    => ['start' => $data_jadwal->senin_start, 'end' => $data_jadwal->senin_end],
			'Tuesday'   => ['start' => $data_jadwal->selasa_start, 'end' => $data_jadwal->selasa_end],
			'Wednesday' => ['start' => $data_jadwal->rabu_start, 'end' => $data_jadwal->rabu_end],
			'Thursday'  => ['start' => $data_jadwal->kamis_start, 'end' => $data_jadwal->kamis_end],
			'Friday'    => ['start' => $data_jadwal->jumat_start, 'end' => $data_jadwal->jumat_end],
			'Saturday'  => ['start' => $data_jadwal->sabtu_start, 'end' => $data_jadwal->sabtu_end],
			'Sunday'    => ['start' => $data_jadwal->minggu_start, 'end' => $data_jadwal->minggu_end],
		];

		// Default normal
		$dk['type_anomali'] = 'Normal';

		//Jika masih hari berjalan anomali lupa absen pulang jangan dlu terbaca jika belum ada jam keluar
		$today = date('Y-m-d');
		$tgl_in = !empty($dk['tgl_in']) ? date('Y-m-d', strtotime($dk['tgl_in'])) : null;

		// Cek keterlambatan
		if (isset($jadwal_kerja[$hari])) {
			if (toMinute($dk['waktu_in']) > toMinute($jadwal_kerja[$hari]['start'])) {
				$dk['type_anomali'] = 'Telat';
				// }elseif (toMinute($dk['waktu_out']) < toMinute($jadwal_kerja[$hari]['end'])) {
				//     $dk['type_anomali'] = 'Pulang Cepat';
			} elseif ((!isset($dk['waktu_out']) || empty($dk['waktu_out'])) && $today != $tgl_in) {
				$dk['type_anomali'] = 'Lupa Absen';
			}
		} else {
			$dk['type_anomali'] = 'Tidak Diketahui';
		}
	}

	// Filter hanya data yang type_anomali-nya 'Alfa', 'Telat', 'Lupa Absen' atau Pulang Cepat
	$data_anomali = array_filter($data_kehadiran, function ($dk) {
		return $dk['jenis'] == 'absen' && in_array($dk['type_anomali'], ['Alpa', 'Telat', 'Lupa Absen', 'Pulang Cepat']);
	});

	$data_hadir = array_filter($data_kehadiran, function ($dk) {
		return $dk['jenis'] == 'absen' && in_array($dk['type_anomali'], ['Normal', 'Telat', 'Lupa Absen', 'Pulang Cepat']);
	});

	// Hitung total Alfa, Telat, dan Lupa Absen
	$data['total_alfa'] = count(array_filter($data_anomali, fn($dk) => $dk['type_anomali'] === 'Alpa'));
	$data['total_hadir'] = count($data_hadir);
	$data['total_telat'] = count(array_filter($data_anomali, fn($dk) => $dk['type_anomali'] === 'Telat'));
	$data['total_lupa_absen'] = count(array_filter($data_anomali, fn($dk) => $dk['type_anomali'] === 'Lupa Absen'));
	$data['total_anomali'] = (int) $data['total_alfa'] + (int) $data['total_telat'] + (int) $data['total_lupa_absen'];

	// Mengurutkan data anomali berdasarkan tanggal
	usort($data_anomali, function ($a, $b) {
		return strtotime($b['tgl_data']) - strtotime($a['tgl_data']);
	});

	$data['data_anomali'] = $data_anomali;

	return $data;
}

function toMinute($time)
{
	$data = date('Y-m-d H:i', strtotime($time));
	return $data;
}

function data_anomali_karyawan_department($data_karyawan, $id_karyawan_dept, $date)
{
	$db = \Config\Database::connect();

	$id_karyawan = [];
	$status_libur = [
		'Monday'    => 'senin_status',
		'Tuesday'   => 'selasa_status',
		'Wednesday' => 'rabu_status',
		'Thursday'  => 'kamis_status',
		'Friday'    => 'jumat_status',
		'Saturday'  => 'sabtu_status',
		'Sunday'    => 'minggu_status',
	];

	// Ambil daftar tanggal merah
	$tgl_merah = tgl_merah(false, $date);
	$tgl_merah_array = [];
	foreach ($tgl_merah as $holiday) {
		$tgl_merah_array[$holiday->tgl_holiday] = $holiday->jenis_holiday;
	}

	$temp_data = [];
	foreach ($data_karyawan as &$dk) {
		$id_karyawan[] = $dk['id_karyawan'];
		$dk['tgl_data'] = date('Y-m-d', strtotime($dk['tgl_data']));
		$hari = date('l', strtotime($dk['tgl_data']));

		if ($dk['jenis'] === 'absen') {
			if ($dk['waktu_in'] == null && $dk['waktu_out'] == null) {
				$dk['jenis'] = 'alfa';
				continue;
			}
		}

		// Jika absen dan waktu masuk/keluar kosong, ubah jenis menjadi 'alfa'
		if ($dk['jenis'] === 'absen' && (empty($dk['waktu_in']) && empty($dk['waktu_out']))) {
			$dk['jenis'] = 'alfa';
			continue;
		}

		// Cek status libur berdasarkan hari kerja karyawan
		if (isset($status_libur[$hari]) && ($dk[$status_libur[$hari]] ?? '') === 'libur') {
			$dk['jenis'] = 'libur';
		}

		// Cek apakah tanggal ini ada di daftar tanggal merah
		$tgl_data = $dk['tgl_data'];
		if (isset($tgl_merah_array[$tgl_data])) {
			$dk['jenis'] = ($tgl_merah_array[$tgl_data] === 'cutber') ? 'cutber' : 'libur';
			$dk['status'] = 'libur';
		}

		$unique_key = $tgl_data . '_' . $dk['id_karyawan']; //key
		// handle jika pada tanggal yang sama ada absen masuk dan izin, ambil data absen masuk dan keluar namun status izin
		if (!isset($temp_data[$unique_key])) {
			$temp_data[$unique_key] = $dk;
		} elseif (!empty($dk['absen_id']) && empty($temp_data[$unique_key]['absen_id'])) {
			// Jika ada absen_id baru tetapi yang tersimpan sebelumnya kosong, timpa dengan data absen
			$temp_data[$unique_key] = $dk;
		} elseif (empty($dk['absen_id']) && !empty($temp_data[$unique_key]['absen_id'])) {
			$temp_data[$unique_key]['alasan_leave'] = $dk['alasan_leave'];
			$temp_data[$unique_key]['status'] = $dk['status'];
			$temp_data[$unique_key]['jenis'] = $dk['jenis'];
		}
	}

	$data_karyawan = array_values($temp_data);

	// Cari karyawan yang tidak hadir
	$karyawan_tidak_hadir = array_diff($id_karyawan_dept, $id_karyawan);

	if (!empty($karyawan_tidak_hadir)) {
		$karyawan_list = implode(',', $karyawan_tidak_hadir);
		$query = $db->query("SELECT a.id_karyawan, a.nama_ktp, c.nama_company, 
            d.senin_status, d.selasa_status, d.rabu_status, d.kamis_status, 
            d.jumat_status, d.sabtu_status, d.minggu_status 
            FROM hrm_employee_detail a  
            LEFT JOIN core_user b ON b.id_user = a.id_user 
            LEFT JOIN core_company c ON c.id_company = b.id_company 
            LEFT JOIN hrm_jadwal d ON d.id_jadwal = a.id_jadwal 
            WHERE a.id_karyawan IN ($karyawan_list)")->getResult();

		foreach ($query as $dt_karyawan) {
			$hari = date('l', strtotime($date));
			$jenis = (isset($status_libur[$hari]) && ($dt_karyawan->{$status_libur[$hari]} ?? '') === 'libur') ? 'libur' : 'alfa';

			// Jika tanggal tidak ada dalam `tgl_data` dan merupakan tanggal merah, sesuaikan jenisnya
			if (isset($tgl_merah_array[$date])) {
				$jenis = ($tgl_merah_array[$date] === 'cutber') ? 'cutber' : 'libur';
			}

			// Tambahkan data karyawan yang tidak hadir
			$data_karyawan[] = [
				'nama_karyawan'  => $dt_karyawan->nama_ktp,
				'nama_perusahaan' => $dt_karyawan->nama_company,
				'tgl_data'       => $date,
				'waktu_in'       => null,
				'waktu_out'      => null,
				'jenis'          => $jenis,
			];
		}
	}

	// Mengurutkan array berdasarkan 'nama_perusahaan' dan 'nama_karyawan'
	usort($data_karyawan, fn($a, $b) => $a['nama_perusahaan'] <=> $b['nama_perusahaan'] ?: $a['nama_karyawan'] <=> $b['nama_karyawan']);
	return $data_karyawan;
}

function data_alfa_karyawan($data_kehadiran, $periode_berjalan, $hari_libur, $end_period)
{
	$tgl_merah = tgl_merah($periode_berjalan);
	$temp_data = [];

	foreach ($data_kehadiran as $data) {
		$tgl_data = $data['tgl_data'];
		// Konversi tanggal menjadi nama hari untuk pengecekan libur
		$day_of_week = date('l', strtotime($tgl_data));

		// Jika hari libur, langsung atur status & jenis ke "libur"
		if (in_array($day_of_week, $hari_libur)) {
			$data['status'] = 'libur';
			$data['jenis'] = 'libur';
		}

		// Cek apakah tanggal ini ada di daftar tanggal merah
		foreach ($tgl_merah as $tgl) {
			if ($tgl->tgl_holiday === $tgl_data) {
				// Sesuaikan jenis berdasarkan kategori tanggal merah
				$data['jenis'] = ($tgl->jenis_holiday === 'cutber') ? 'cutber' : 'libur';
				$data['status'] = 'libur';
				break;
			}
		}

		// jika data hadir namun dihari yang sama ada izin ambil data hadir namun status izin 
		if (!isset($temp_data[$tgl_data]) || (empty($temp_data[$tgl_data]['absen_id']) && !empty($data['absen_id']))) {
			$temp_data[$tgl_data] = $data;
		} elseif (empty($data['absen_id'])) {
			// Jika data izin ditemukan, update alasan dan status
			$temp_data[$tgl_data]['alasan_leave'] = $data['alasan_leave'];
			$temp_data[$tgl_data]['status'] = $data['status'];
			$temp_data[$tgl_data]['jenis'] = $data['jenis'];
		}
	}
	$data_kehadiran = array_values($temp_data);

	$start_period = $periode_berjalan['start_period'];
	$periode_berjalan_current = config_periode_berjalan();
	$end_period_current = $periode_berjalan_current['end_period'];

	if ($end_period_current === $end_period) {
		$end_period = date("Y-m-d");
	} else {
		$end_period = $end_period;
	}

	// array untuk menyimpan tanggal kehadiran
	$tanggal_kehadiran_tercatat = array_map(function ($dk) {
		return date('Y-m-d', strtotime($dk['tgl_data']));
	}, $data_kehadiran);

	$period = new DatePeriod(
		new DateTime($start_period),
		new DateInterval('P1D'),
		(new DateTime($end_period))->modify('+1 day')
	);

	$tgl_merah_array = [];
	foreach ($tgl_merah as $holiday) {
		$tgl_merah_array[$holiday->tgl_holiday] = $holiday->jenis_holiday;
	}

	foreach ($period as $date) {
		$current_date = $date->format('Y-m-d');
		$current_day = $date->format('l');

		// Jika tanggal tidak ada dalam data kehadiran dan masuk dalam daftar hari libur
		if (!in_array($current_date, $tanggal_kehadiran_tercatat) && in_array($current_day, $hari_libur)) {
			$data_kehadiran[] = [
				'tgl_data' => $current_date,
				'waktu_in' => null,
				'waktu_out' => null,
				'jenis' => 'libur'
			];
			continue;
		}

		// Jika tanggal tidak ada dalam data kehadiran dan masuk dalam tanggal merah
		if (!in_array($current_date, $tanggal_kehadiran_tercatat) && isset($tgl_merah_array[$current_date])) {
			$jenis_holiday = ($tgl_merah_array[$current_date] === 'cutber') ? 'cutber' : 'libur';
			$data_kehadiran[] = [
				'tgl_data' => $current_date,
				'waktu_in' => null,
				'waktu_out' => null,
				'jenis' => $jenis_holiday
			];
			continue;
		}

		// Jika tanggal tidak ada dalam data kehadiran dan bukan tanggal merah atau hari libur
		if (!in_array($current_date, $tanggal_kehadiran_tercatat)) {
			$data_kehadiran[] = [
				'tgl_data' => $current_date,
				'waktu_in' => null,
				'waktu_out' => null,
				'jenis' => 'alfa'
			];
		}
	}

	usort($data_kehadiran, function ($a, $b) {
		return strtotime($b['tgl_data']) - strtotime($a['tgl_data']);
	});
	return $data_kehadiran;
}

function id_approval_akses()
{
	$db = \Config\Database::connect();
	//untuk get id role HR approval dicore role.
	$id = $db->query("SELECT id_role FROM core_role WHERE nama_role = 'HR Approval'")->getRow()->id_role;
	$id = array($id);
	return $id;
}

function periode_berjalan_custom($period)
{
	$db = \Config\Database::connect();

	// Ambil nilai start_date dan end_date dari database
	$start_date = (int)$db->query("SELECT intval_config FROM core_config WHERE nama_config = 'start_period' AND isDeleted = 0")->getRow()->intval_config;
	$end_date = (int)$db->query("SELECT intval_config FROM core_config WHERE nama_config = 'end_period' AND isDeleted = 0")->getRow()->intval_config;

	// Ambil bulan dan tahun dari parameter
	list($year, $month) = explode('-', $period);
	$year = (int)$year;
	$month = (int)$month;

	// Hitung start_period (bulan sebelumnya)
	if ($month == 1) { // Jika Januari, mundur ke Desember tahun sebelumnya
		$start_period = new DateTime(($year - 1) . "-12-$start_date");
	} else {
		$start_period = new DateTime("$year-" . ($month - 1) . "-$start_date");
	}

	// Hitung end_period (bulan yang dikirim)
	$end_period = new DateTime("$year-$month-$end_date");

	return [
		'start_period' => $start_period->format('Y-m-d'),
		'end_period'   => $end_period->format('Y-m-d')
	];
}

function cek_fitur_slip($id_company)
{
	$db = \Config\Database::connect();

	$builder = $db->table('core_company');
	$builder->select('slip_dashboard');
	$builder->where('id_company', $id_company);

	$row = $builder->get()->getRow();
	$data = isset($row->slip_dashboard) ? $row->slip_dashboard : 0;
	return intval($data);
}


function id_role_manager()
{
	$db = \Config\Database::connect();
	$builder = $db->table('core_role');
	$builder->select('id_role');
	$builder->whereIn('id_role', [6, 7]);
	return $builder->get()->getResultArray();
}

function id_role_spv()
{
	$db = \Config\Database::connect();
	$builder = $db->table('core_role');
	$builder->select('id_role');
	$builder->where('id_role', 7);
	$result = $builder->get()->getRow();
	return [$result->id_role];
}


function id_role_manager_only()
{
	$db = \Config\Database::connect();
	$builder = $db->table('core_role');
	$builder->select('id_role');
	$builder->where('id_role', 6);
	$result = $builder->get()->getRow();
	return [$result->id_role];
}

function id_role_administrator()
{
	$db = \Config\Database::connect();
	$builder = $db->table('core_role');
	$builder->select('id_role');
	$builder->where('id_role', 1);
	$result = $builder->get()->getRow();
	return [$result->id_role];
}

function id_role_karyawan()
{
	$db = \Config\Database::connect();
	$builder = $db->table('core_role');
	$builder->select('id_role');
	$builder->where('id_role', 8);
	$result = $builder->get()->getRow();
	return [$result->id_role];
}

function id_role_manager_hr()
{
	$db = \Config\Database::connect();
	$builder = $db->table('core_role');
	$builder->select('id_role');
	$builder->where('id_role', 11);
	$result = $builder->get()->getRow();
	return [$result->id_role];
}

// Digunakan untuk melakukan check data
function checkDataSPV($id_department, $id_company = 0, $include_all = true)
{
	$data_manager = '0';

	if ($include_all) {
		$db = \Config\Database::connect();
		$builder = $db->table('hrm_employee_detail a');
		$id_role_spv = id_role_spv();

		$builder->selectCount('a.id_karyawan', 'jml_spv');
		$builder->join('core_user b', 'a.id_user = b.id_user', 'inner');
		$builder->join('core_user_role c', 'a.id_user = c.id_user', 'left');
		$builder->join('hrm_resign d', 'a.id_karyawan = d.id_karyawan AND d.isDeleted = 0 AND d.tgl_resign < CURDATE()', 'left');

		$builder->where('a.isDeleted', 0);
		$builder->where('a.id_department', $id_department);
		$builder->where('c.id_role', $id_role_spv[0]);
		$builder->where('d.id_resign IS NULL', null, false);

		if ($id_company) {
			$builder->groupStart()
				->where('b.id_company', $id_company)
				->orLike('b.access_company', $id_company)
				->groupEnd();
		}

		$query = $builder->get();
		$data_manager = $query->getRow()->jml_spv;
	}

	return $data_manager;
}

function checkDataManager($id_department, $id_company = 0, $include_all = true)
{
	$data_manager = '0';

	if ($include_all) {
		$db = \Config\Database::connect();
		$id_role_manager = id_role_manager_only();

		$builder = $db->table('hrm_employee_detail a');
		$builder->selectCount('a.id_karyawan', 'jml_manager');
		$builder->join('core_user b', 'a.id_user = b.id_user', 'inner');
		$builder->join('core_user_role c', 'a.id_user = c.id_user', 'left');
		$builder->join('hrm_resign d', 'a.id_karyawan = d.id_karyawan AND d.isDeleted = 0 AND d.tgl_resign < CURDATE()', 'left');

		$builder->where('a.isDeleted', 0);
		$builder->where('a.id_department', $id_department);
		$builder->where('c.id_role', $id_role_manager[0]);
		$builder->where('d.id_resign IS NULL', null, false);

		if ($id_company) {
			$builder->groupStart()
				->where('b.id_company', $id_company)
				->orLike('b.access_company', $id_company)
				->groupEnd();
		}

		$query = $builder->get();
		$data_manager = $query->getRow()->jml_manager;
	}

	return $data_manager;
}

// Digunakan untuk menampilkan daftar spv
function listDataSPV($id_department, $id_company = 0)
{
	$db = \Config\Database::connect();
	$id_role_spv = id_role_spv();

	$builder = $db->table('hrm_employee_detail a');
	$builder->select('a.*');
	$builder->join('core_user b', 'a.id_user = b.id_user', 'inner');
	$builder->join('core_user_role c', 'a.id_user = c.id_user', 'left');
	$builder->where('a.isDeleted', 0);
	$builder->where('a.id_department', $id_department);
	$builder->where('c.id_role', $id_role_spv[0]);

	if ($id_company) {
		$builder->groupStart()
			->where('b.id_company', $id_company)
			->orLike('b.access_company', $id_company)
			->groupEnd();
	}

	return $builder->get()->getResultArray();
}

// Digunakan untuk menampilkan daftar manager
function listDataManager($id_department, $id_company = 0)
{
	$db = \Config\Database::connect();
	$id_role_manager = id_role_manager_only();

	$builder = $db->table('hrm_employee_detail a');
	$builder->select('a.*');
	$builder->join('core_user b', 'a.id_user = b.id_user', 'inner');
	$builder->join('core_user_role c', 'a.id_user = c.id_user', 'left');
	$builder->where('a.isDeleted', 0);
	$builder->where('a.id_department', $id_department);
	$builder->where('c.id_role', $id_role_manager[0]);

	if ($id_company) {
		$builder->groupStart()
			->where('b.id_company', $id_company)
			->orLike('b.access_company', $id_company)
			->groupEnd();
	}

	return $builder->get()->getResultArray();
}

function convertDate($mysqlDate)
{
	if (!empty($mysqlDate) && strtotime($mysqlDate)) {
		return date("d/m/Y", strtotime($mysqlDate));
	}
	return null; // Return null jika input tidak valid
}

function formatDateToMysql($datetime)
{
	$datetime = trim($datetime);
	$possible_formats = [
		// Tambah format Excel US style
		'n/j/Y',
		'n/j/Y H:i',
		'n/j/Y H:i:s',
		'm/d/Y',
		'm/d/Y H:i',
		'm/d/Y H:i:s',

		// Prioritaskan format Indonesia (STRIP)
		'Y-m-d',
		'Y-m-d H:i',
		'Y-m-d H:i:s',
		'd-m-Y',
		'd-m-Y H:i',
		'd-m-Y H:i:s',

		// Prioritaskan format Indonesia (GARIS MIRING)
		'Y/m/d',
		'Y/m/d H:i',
		'Y/m/d H:i:s',
		'd/m/Y',
		'd/m/Y H:i',
		'd/m/Y H:i:s',
	];

	foreach ($possible_formats as $format) {
		$dateTime = \DateTime::createFromFormat($format, $datetime);
		$errors = \DateTime::getLastErrors();

		if ($dateTime !== false && $errors['warning_count'] == 0 && $errors['error_count'] == 0) {
			return $dateTime->format('Y-m-d H:i:s');
		}
	}

	throw new \Exception("Format tanggal tidak dikenali atau tidak valid: $datetime");
}


function google_api_key()
{
	return  'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx';
}


function send_fcm_cli($id_user, $type, $id_ref)
{

	$db = \Config\Database::connect();
	$builder = $db->table('core_config a');
	$builder->select('a.tipe_config');
	$builder->where('a.isDeleted', 0);
	$builder->where('a.param_config', 'end_point_mobile');
	$builder->where('a.strval_config', 'general');
	$query = $builder->get();
	$row = $query->getRow();

	// 🔹 Default jika tidak ditemukan di database
	$baseUrl = $row ? rtrim($row->tipe_config, '/') : 'https://dev.indopasifik.id/apineptime/public';
	
	// 🔹 Endpoint final
	$endpoint = $baseUrl . '/api/helper/send-fcm-cli';

	$data = [
		'id_user' => $id_user,
		'type'    => $type,
		'id_ref'  => $id_ref,
	];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $endpoint);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Content-Type: application/x-www-form-urlencoded'
	]);
	$response = curl_exec($ch);
	$error = curl_error($ch);
	curl_close($ch);

	if ($error) {
		log_message('error', '❌ Gagal kirim FCM ke user ' . $id_user . ' : ' . $error);
		return false;
	}

	log_message('info', '✅ FCM terkirim ke user ' . $id_user . ' | Response: ' . $response);
	return $response;
}

function send_notification_approval_cli($id_ref, $ref_table, $prefix, $tgl_overtime, $jam_start, $jam_end, $id_user_pengaju, $status, $id_user_approval)
{
	// $endpoint = 'https://dev.indopasifik.id/apineptime/public/api/helper/send-notification-approval-manual';
	$db = \Config\Database::connect();
	$builder = $db->table('core_config a');
	$builder->select('a.tipe_config');
	$builder->where('a.isDeleted', 0);
	$builder->where('a.param_config', 'end_point_mobile');
	$builder->where('a.strval_config', 'general');
	$query = $builder->get();
	$row = $query->getRow();

	// 🔹 Default jika tidak ditemukan di database
	$baseUrl = $row ? rtrim($row->tipe_config, '/') : 'https://dev.indopasifik.id/apineptime/public';
	
	// 🔹 Endpoint final
	$endpoint = $baseUrl . '/api/helper/send-notification-approval-manual';

	$data = [
		'id_ref'               => $id_ref,
		'ref_table'            => $ref_table,
		'prefix'               => $prefix,
		'tgl_overtime'         => $tgl_overtime,
		'jam_start'            => $jam_start,
		'jam_end'              => $jam_end,
		'id_user_pengaju' 	   => $id_user_pengaju,
		'status'               => $status,
		'id_user_approval' 	   => $id_user_approval,
	];

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $endpoint);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
	curl_setopt($ch, CURLOPT_HTTPHEADER, [
		'Content-Type: application/json'
	]);

	$response = curl_exec($ch);
	$error = curl_error($ch);
	curl_close($ch);

	if ($error) {
		log_message('error', '❌ Gagal kirim notifikasi approval CLI (Ref: ' . $id_ref . ') : ' . $error);
		return false;
	}

	log_message('info', '✅ Notifikasi approval CLI terkirim (Ref: ' . $id_ref . ') | Response: ' . $response);
	return $response;
}

function prefix($param)
{
    $db = \Config\Database::connect();
    $builder = $db->table('core_config');
    $builder->select('tipe_config');
    $builder->where('isDeleted', 0);
    $builder->groupStart()
        ->where('param_config', 'jenis_cuti')
        ->orWhere('param_config', 'jenis_izin')
    ->groupEnd();
    $builder->where('strval_config', $param);

    $result = $builder->get()->getRow();

    return $result ? $result->tipe_config : null;
}