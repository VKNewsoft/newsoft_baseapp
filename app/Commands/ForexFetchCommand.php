<?php
/**
 * Command forex dipakai scheduler/cron untuk mengambil data harian GBP/JPY
 * tanpa harus membuka halaman admin, sekaligus tetap memakai cache dan fallback.
 */

namespace App\Commands;

use App\Modules\ForexMonitor\Libraries\ForexDataService;
use App\Services\WhatsappService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ForexFetchCommand extends BaseCommand
{
	protected $group = 'Custom';
	protected $name = 'forex:fetch';
	protected $description = 'Ambil dan simpan data harian GBP/JPY beserta analisis sederhananya';
	protected $usage = 'forex:fetch [--date YYYY-MM-DD] [--force] [--skip-notify]';
	protected $options = [
		'--date' => 'Tanggal target dengan format YYYY-MM-DD. Default memakai tanggal hari ini.',
		'--force' => 'Abaikan cache API agar request dilakukan ulang ke provider.',
		'--skip-notify' => 'Lewati pengiriman notifikasi WhatsApp setelah fetch selesai.',
	];

	/**
	 * Output CLI dibuat ringkas agar log cron mudah dipantau, tetapi tetap
	 * menjelaskan apakah data berasal dari API, cache, atau fallback database.
	 */
	public function run(array $params)
	{
		$targetDate = CLI::getOption('date');
		$forceRefresh = CLI::getOption('force') !== null;
		$skipNotify = CLI::getOption('skip-notify') !== null;
		$result = (new ForexDataService())->syncDailyData($targetDate, $forceRefresh);

		$statusColor = $result['status'] === 'ok' ? 'green' : 'yellow';
		CLI::write('Status: ' . strtoupper($result['status']), $statusColor);
		CLI::write('Pesan : ' . $result['message']);
		CLI::write('Source: ' . ($result['data_source'] ?? '-'));
		CLI::write('Target: ' . ($result['requested_date'] ?? '-'));
		CLI::write('Candle: ' . ($result['fetched_date'] ?? '-'));

		if (!empty($result['price'])) {
			CLI::write(
				'OHLC  : O=' . number_format((float) ($result['price']['open_price'] ?? 0), 4, '.', '')
				. ' H=' . number_format((float) ($result['price']['high_price'] ?? 0), 4, '.', '')
				. ' L=' . number_format((float) ($result['price']['low_price'] ?? 0), 4, '.', '')
				. ' C=' . number_format((float) ($result['price']['close_price'] ?? 0), 4, '.', '')
			);
		}

		// Ringkasan prediksi ikut ditampilkan agar fetch harian langsung memberi
		// gambaran bias sesi berikutnya tanpa harus menjalankan command terpisah.
		if (!empty($result['prediction']['aggregate'])) {
			CLI::write('Pred  : ' . strtoupper((string) ($result['prediction']['aggregate']['direction'] ?? '-')));
			CLI::write('PredH : ' . number_format((float) ($result['prediction']['aggregate']['predicted_high'] ?? 0), 4, '.', ''));
			CLI::write('PredL : ' . number_format((float) ($result['prediction']['aggregate']['predicted_low'] ?? 0), 4, '.', ''));
		}

		/**
		 * Notifikasi WA dijalankan setelah fetch selesai agar kegagalan provider
		 * chat tidak menghentikan proses penyimpanan data forex harian.
		 */
		if (!$skipNotify) {
			$whatsappService = new WhatsappService();
			$whatsappConfig = $whatsappService->getConfig();
			$recipient = $whatsappConfig['default_recipient'];

			if ($recipient === '') {
				CLI::write('WA    : Nomor tujuan WhatsApp belum diatur', 'yellow');
				return;
			}

			if (!$whatsappService->isConfigured()) {
				CLI::write('WA    : Kredensial WhatsApp belum lengkap, notifikasi dilewati', 'yellow');
				return;
			}

			$waResult = $whatsappService->sendMessage($recipient, $whatsappService->buildForexDailyMessage($result));
			$waColor = $waResult['status'] === 'ok' ? 'green' : 'yellow';
			CLI::write('WA    : ' . $waResult['message'], $waColor);
		}
	}
}
