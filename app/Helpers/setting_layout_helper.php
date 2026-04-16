<?php

if (!function_exists('setting_layout_font_map')) {
	function setting_layout_font_map(): array
	{
		return [
			'open-sans' => [
				'label' => 'Open Sans (Default)',
				'family' => '"Open Sans", "Segoe UI", Arial, sans-serif',
				'css_path' => 'css/fonts/open-sans.css'
			],
			'roboto' => [
				'label' => 'Roboto',
				'family' => '"Roboto", "Segoe UI", Arial, sans-serif',
				'css_path' => 'css/fonts/roboto.css'
			],
			'montserrat' => [
				'label' => 'Montserrat',
				'family' => '"Montserrat", "Segoe UI", Arial, sans-serif',
				'css_path' => 'css/fonts/montserrat.css'
			],
			'poppins' => [
				'label' => 'Poppins',
				'family' => '"Poppins", "Segoe UI", Arial, sans-serif',
				'css_path' => 'css/fonts/poppins.css'
			],
			'arial' => [
				'label' => 'Arial',
				'family' => 'Arial, "Helvetica Neue", sans-serif',
				'css_path' => 'css/fonts/arial.css'
			],
			'verdana' => [
				'label' => 'Verdana',
				'family' => 'Verdana, Geneva, sans-serif',
				'css_path' => 'css/fonts/verdana.css'
			],
			'tahoma' => [
				'label' => 'Tahoma',
				'family' => 'Tahoma, "Segoe UI", sans-serif',
				'css_path' => 'css/fonts/tahoma.css'
			],
			'trebuchet-ms' => [
				'label' => 'Trebuchet MS',
				'family' => '"Trebuchet MS", "Lucida Sans Unicode", sans-serif',
				'css_path' => 'css/fonts/trebuchet-ms.css'
			],
			'georgia' => [
				'label' => 'Georgia',
				'family' => 'Georgia, "Times New Roman", serif',
				'css_path' => 'css/fonts/georgia.css'
			]
		];
	}
}

if (!function_exists('setting_layout_font_entry')) {
	function setting_layout_font_entry(?string $value): array
	{
		$fontMap = setting_layout_font_map();
		$key = setting_layout_font_key($value);
		return $fontMap[$key];
	}
}

if (!function_exists('setting_layout_font_key')) {
	function setting_layout_font_key(?string $value): string
	{
		$fontMap = setting_layout_font_map();
		$value = trim((string) $value);
		if ($value === '') {
			return 'open-sans';
		}

		if (isset($fontMap[$value])) {
			return $value;
		}

		foreach ($fontMap as $key => $font) {
			if ($font['family'] === $value) {
				return $key;
			}
		}

		return 'open-sans';
	}
}

if (!function_exists('setting_layout_normalize_font_family')) {
	function setting_layout_normalize_font_family(?string $value): string
	{
		$fontEntry = setting_layout_font_entry($value);
		return $fontEntry['family'];
	}
}

if (!function_exists('setting_layout_font_options')) {
	function setting_layout_font_options(): array
	{
		$options = [];
		foreach (setting_layout_font_map() as $font) {
			$options[$font['family']] = $font['label'];
		}
		return $options;
	}
}

if (!function_exists('setting_layout_font_asset_map')) {
	function setting_layout_font_asset_map(): array
	{
		return [
			'open-sans' => [
				'family_name' => 'Open Sans',
				'faces' => [
					['weight' => 400, 'file' => 'opensans_400.woff2'],
					['weight' => 600, 'file' => 'opensans_600.woff2'],
					['weight' => 700, 'file' => 'opensans_700.woff2']
				]
			],
			'roboto' => [
				'family_name' => 'Roboto',
				'faces' => [
					['weight' => 400, 'file' => 'Roboto-400-normal-latin.woff2'],
					['weight' => 500, 'file' => 'Roboto-500-normal-latin.woff2'],
					['weight' => 700, 'file' => 'Roboto-700-normal-latin.woff2']
				]
			],
			'montserrat' => [
				'family_name' => 'Montserrat',
				'faces' => [
					['weight' => 400, 'file' => 'Montserrat-400-normal-latin.woff2'],
					['weight' => 500, 'file' => 'Montserrat-500-normal-latin.woff2'],
					['weight' => 600, 'file' => 'Montserrat-600-normal-latin.woff2'],
					['weight' => 700, 'file' => 'Montserrat-700-normal-latin.woff2']
				]
			],
			'poppins' => [
				'family_name' => 'Poppins',
				'faces' => [
					['weight' => 400, 'file' => 'poppins_400.woff2'],
					['weight' => 500, 'file' => 'poppins_500.woff2'],
					['weight' => 700, 'file' => 'poppins_700.woff2']
				]
			]
		];
	}
}

if (!function_exists('setting_layout_font_preload_files')) {
	function setting_layout_font_preload_files(?string $value): array
	{
		$key = setting_layout_font_key($value);
		$assetMap = setting_layout_font_asset_map();
		return $assetMap[$key]['faces'] ?? [];
	}
}

if (!function_exists('setting_layout_font_critical_css')) {
	function setting_layout_font_critical_css(?string $value): string
	{
		$key = setting_layout_font_key($value);
		$assetMap = setting_layout_font_asset_map();
		if (empty($assetMap[$key]['faces']) || empty($assetMap[$key]['family_name'])) {
			return '';
		}

		/**
		 * Menyisipkan font-face kritikal langsung di HTML agar browser tidak
		 * menunggu file CSS font terpisah sebelum merender teks utama.
		 */
		$css = [];
		$familyName = $assetMap[$key]['family_name'];
		foreach ($assetMap[$key]['faces'] as $face) {
			$filePath = APPPATH . 'Modules/Common/Assets/builtin/fonts/' . $face['file'];
			if (!is_file($filePath)) {
				continue;
			}

			$fileUrl = base_url('module-assets/Common/builtin/fonts/' . $face['file']) . '?v=' . @filemtime($filePath);
			$css[] = "@font-face{font-family:'" . addslashes($familyName) . "';font-style:normal;font-weight:" . (int) $face['weight'] . ";font-display:optional;src:url('" . $fileUrl . "') format('woff2');}";
		}

		return implode('', $css);
	}
}
