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
