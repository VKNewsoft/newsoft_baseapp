<?php
/**
 * Command prediksi forex dipakai cron saat market open New York maupun
 * evaluasi hourly untuk membangun payload next-day dari histori tersimpan.
 */

namespace App\Commands;

use App\Modules\ForexPrediction\Libraries\ForexPredictionService;
use App\Services\WhatsappService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class ForexPredictCommand extends BaseCommand
{
	protected $group = 'Custom';
	protected $name = 'forex:predict';
	protected $description = 'Bangun prediksi next-day GBP/JPY dari data OHLC terakhir dengan beberapa metode ringan';
	protected $usage = 'forex:predict [--date YYYY-MM-DD] [--force] [--mode manual|market-open|hourly] [--skip-notify]';
	protected $options = [
		'--date' => 'Batasi prediksi ke candle basis maksimum dengan format YYYY-MM-DD.',
		'--force' => 'Bangun ulang prediksi walau cache untuk base date tersebut masih ada.',
		'--mode' => 'Label mode kalkulasi: manual, market-open, atau hourly.',
		'--skip-notify' => 'Lewati pengiriman notifikasi WhatsApp setelah prediksi selesai.',
	];

	/**
	 * Output CLI diringkas per metode agar log scheduler tetap padat tetapi
	 * operator masih bisa membaca bias dan proyeksi gabungan dengan cepat.
	 */
	public function run(array $params)
	{
		$targetDate = CLI::getOption('date');
		$forceRefresh = CLI::getOption('force') !== null;
		$mode = trim((string) (CLI::getOption('mode') ?: 'manual'));
		$skipNotify = CLI::getOption('skip-notify') !== null;
		$prediction = (new ForexPredictionService())->getLatestPrediction($targetDate, $forceRefresh, $mode);

		if (!$prediction) {
			CLI::write('Status: WARNING', 'yellow');
			CLI::write('Pesan : Belum ada histori harga GBP/JPY yang bisa dipakai untuk prediksi');
			return;
		}

		$aggregate = $prediction['aggregate'] ?? [];
		CLI::write('Status: OK', 'green');
		CLI::write('Mode  : ' . ($prediction['scheduler']['mode'] ?? $mode));
		CLI::write('Basis : ' . ($prediction['base_date'] ?? '-'));
		CLI::write('Target: ' . ($prediction['target_date'] ?? '-'));
		CLI::write('Arah  : ' . strtoupper((string) ($aggregate['direction'] ?? '-')));
		CLI::write('High  : ' . number_format((float) ($aggregate['predicted_high'] ?? 0), 4, '.', ''));
		CLI::write('Low   : ' . number_format((float) ($aggregate['predicted_low'] ?? 0), 4, '.', ''));

		foreach (($prediction['methods'] ?? []) as $method) {
			CLI::write(
				str_pad((string) ($method['key'] ?? 'method'), 12, ' ', STR_PAD_RIGHT)
				. ': '
				. strtoupper((string) ($method['direction'] ?? '-'))
				. ' | H=' . number_format((float) ($method['predicted_high'] ?? 0), 4, '.', '')
				. ' | L=' . number_format((float) ($method['predicted_low'] ?? 0), 4, '.', '')
			);
		}

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

			$waPayload = [
				'status' => 'ok',
				'message' => 'Prediksi next-day GBP/JPY berhasil diperbarui untuk sesi ' . ($prediction['target_date'] ?? '-'),
				'price' => $prediction['base_price'] ?? [],
				'analysis' => [
					'summary' => $aggregate['summary'] ?? '',
					'high_low_range' => $prediction['base_price']['range'] ?? 0,
					'trend' => $prediction['base_price']['trend'] ?? 'sideways',
				],
				'prediction' => $prediction,
				'requested_date' => $prediction['base_date'] ?? '-',
				'fetched_date' => $prediction['target_date'] ?? '-',
				'data_source' => 'prediction-cache',
			];

			$waResult = $whatsappService->sendMessage($recipient, $whatsappService->buildForexDailyMessage($waPayload));
			$waColor = $waResult['status'] === 'ok' ? 'green' : 'yellow';
			CLI::write('WA    : ' . $waResult['message'], $waColor);
		}
	}
}
