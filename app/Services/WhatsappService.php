<?php
/**
 * Service WhatsApp dipakai untuk mengirim notifikasi forex harian melalui
 * provider HTTP eksternal tanpa membuat command gagal saat API WA bermasalah.
 */

namespace App\Services;

class WhatsappService
{
	/**
	 * Ambil konfigurasi environment secara terpusat agar command dan module
	 * lain tidak perlu mengetahui detail nama variable provider WhatsApp.
	 */
	public function getConfig(): array
	{
		return [
			'api_url' => trim((string) (env('whatsapp.apiUrl') ?: 'https://whatsapp-endpoint.watzap.id/api/send-message')),
			'api_key' => trim((string) (env('whatsapp.apiKey') ?: '')),
			'number_key' => trim((string) (env('whatsapp.numberKey') ?: '')),
			'default_recipient' => $this->normalizePhoneNumber((string) (env('whatsapp.defaultRecipient') ?: '')),
			'wait_until_send' => trim((string) (env('whatsapp.waitUntilSend') ?: '1')),
		];
	}

	/**
	 * Validasi konfigurasi dipisah agar caller bisa memutuskan kapan notifikasi
	 * dilewati tanpa melempar exception yang mengganggu alur utama sistem.
	 */
	public function isConfigured(): bool
	{
		$config = $this->getConfig();
		return $config['api_url'] !== '' && $config['api_key'] !== '' && $config['number_key'] !== '';
	}

	/**
	 * Nomor penerima dinormalisasi ke format digit internasional agar payload
	 * tetap konsisten walau input environment memakai awalan plus atau spasi.
	 */
	public function normalizePhoneNumber(string $phoneNumber): string
	{
		$phoneNumber = preg_replace('/[^0-9]/', '', $phoneNumber);
		return (string) $phoneNumber;
	}

	/**
	 * Pengiriman pesan dibuat generic agar bisa dipakai ulang oleh cron forex
	 * maupun module lain, tetapi tetap aman jika provider sedang gagal.
	 */
	public function sendMessage(string $recipient, string $message): array
	{
		$config = $this->getConfig();
		if (!$this->isConfigured()) {
			return [
				'status' => 'warning',
				'message' => 'Konfigurasi WhatsApp belum lengkap',
			];
		}

		$recipient = $this->normalizePhoneNumber($recipient);
		if ($recipient === '') {
			return [
				'status' => 'warning',
				'message' => 'Nomor WhatsApp tujuan tidak valid',
			];
		}

		$message = trim($message);
		if ($message === '') {
			return [
				'status' => 'warning',
				'message' => 'Isi pesan WhatsApp kosong',
			];
		}

		try {
			$response = \Config\Services::curlrequest([
				'timeout' => 20,
				'headers' => [
					'Accept' => 'application/json',
					'Content-Type' => 'application/json',
					'User-Agent' => 'Newsoft Baseapp WhatsApp Notification',
				],
			])->post($config['api_url'], [
				'json' => [
					'api_key' => $config['api_key'],
					'number_key' => $config['number_key'],
					'phone_no' => $recipient,
					'message' => $message,
					'wait_until_send' => $config['wait_until_send'],
				],
			]);

			$body = trim((string) $response->getBody());
			return [
				'status' => 'ok',
				'message' => 'Notifikasi WhatsApp berhasil dipanggil',
				'response_body' => $body,
			];
		} catch (\Throwable $e) {
			return [
				'status' => 'warning',
				'message' => 'Pengiriman WhatsApp gagal: ' . $e->getMessage(),
			];
		}
	}

	/**
	 * Pesan forex diringkas dalam format teks biasa agar mudah dibaca di WA
	 * dan aman untuk provider yang tidak memproses HTML kompleks.
	 */
	public function buildForexDailyMessage(array $result): string
	{
		$price = $result['price'] ?? [];
		$analysis = $result['analysis'] ?? [];
		$status = strtoupper((string) ($result['status'] ?? 'warning'));
		$targetDate = (string) ($result['requested_date'] ?? '-');
		$fetchedDate = (string) ($result['fetched_date'] ?? '-');

		$message = [];
		$message[] = 'FOREX MONITOR GBP/JPY';
		$message[] = 'Status: ' . $status;
		$message[] = 'Target: ' . $targetDate;
		$message[] = 'Candle: ' . $fetchedDate;
		$message[] = 'Sumber: ' . (string) ($result['data_source'] ?? '-');

		if ($price) {
			$message[] = 'Open: ' . number_format((float) ($price['open_price'] ?? 0), 4, '.', '');
			$message[] = 'High: ' . number_format((float) ($price['high_price'] ?? 0), 4, '.', '');
			$message[] = 'Low: ' . number_format((float) ($price['low_price'] ?? 0), 4, '.', '');
			$message[] = 'Close: ' . number_format((float) ($price['close_price'] ?? 0), 4, '.', '');
		}

		if (!empty($analysis['high_low_range'])) {
			$message[] = 'Range: ' . number_format((float) $analysis['high_low_range'], 4, '.', '');
		}

		if (!empty($analysis['trend'])) {
			$message[] = 'Trend: ' . strtoupper((string) $analysis['trend']);
		}

		$message[] = 'Catatan: ' . trim((string) ($result['message'] ?? '-'));

		if (!empty($analysis['summary'])) {
			$message[] = 'Summary: ' . trim((string) $analysis['summary']);
		}

		/**
		 * Prediksi next-day disisipkan bila payload command sudah membawanya
		 * agar notifikasi harian langsung memuat bias sesi berikutnya.
		 */
		$prediction = $result['prediction']['aggregate'] ?? [];
		if (!empty($prediction)) {
			$message[] = 'Prediksi Next Day: ' . strtoupper((string) ($prediction['direction'] ?? '-'));
			$message[] = 'Prediksi High: ' . number_format((float) ($prediction['predicted_high'] ?? 0), 4, '.', '');
			$message[] = 'Prediksi Low: ' . number_format((float) ($prediction['predicted_low'] ?? 0), 4, '.', '');
			$message[] = 'Ringkasan Prediksi: ' . trim((string) ($prediction['summary'] ?? '-'));
		}

		return implode("\n", $message);
	}
}
