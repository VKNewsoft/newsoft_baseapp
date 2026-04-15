<div class="card setting-layout-card">
	<div class="card-header">
		<h5 class="card-title"><?=$title?></h5>
	</div>
	
	<div class="card-body">
		<?php 
		helper('html');
		helper('setting_layout');
		if (!empty($msg)) {
			show_message($msg['content'], $msg['status']);
		}

		$fontMap = setting_layout_font_map();
		$currentFontFamily = setting_layout_normalize_font_family(@$font_family);
		
		$list = ['logo_background_color', 'color_scheme', 'sidebar_color', 'font_family'];
		foreach ($list as $val) {
			if (empty($$val)) {
				$$val = '';
			}
		}
		?>
		<form method="post" action="" id="form-setting" class="form-shell">
			<div class="tab-content setting-layout-shell" id="myTabContent">
				<div class="form-shell-section">
					<div class="form-shell-section-title">
						<h5>Theme & Branding</h5>
					</div>
					<div class="row g-4 align-items-start">
						<div class="col-xl-8">
							<div class="row g-4">
								<div class="col-12">
									<label class="form-label">Color Scheme</label>
									<ul id="color-scheme" class="color-scheme-options">
							<?php
							$list = ['blue-dark', 'blue', 'green', 'grey', 'purple', 'red', 'yellow', 'jnn', 'payday'];
							
							foreach ($list as $val) {
								$check = $color_scheme ==  $val ? '<i class="fa fa-check theme-check"></i>' : '';
								echo '<li><a href="javascript:void(0)" class="'.$val.'-theme" data-color-scheme="'.$val.'"><span class="theme-option-label">'.ucwords(str_replace('-', ' ', $val)).'</span>' . $check . '</a></li>';
							}
							?>	
									</ul>
									<input type="hidden" name="color_scheme" id="input-color-scheme" value="<?=@set_value('color_scheme', $color_scheme)?>">
									<div class="form-text">Pilih warna utama tampilan admin agar header, panel, dan aksen UI tetap konsisten.</div>
								</div>
								<div class="col-md-6">
									<label class="form-label">Theme</label>
						<?php
						$options = ['default' => 'Default'];
						$choosen = ['cosmo', 'flatty', 'journal', 'litera', 'lumen', 'minty', 'pulse', 'sandstone', 'simplex', 'spacelab', 'united', 'yeti', 'zephyr', 'cerulean'];
						foreach ($list_bootswatch_theme as $dir) {
		
							if (!in_array($dir, $choosen))
								continue;
							
							$options[$dir] = ucwords($dir);
						}
						if (!@$bootswatch_theme) {
							$bootswatch_theme = 'default';
						}
						
						?>
									<?=options(['name' => 'bootswatch_theme', 'id' => 'bootswatch-theme', 'class' => 'form-select'], $options, set_value('bootswatch_theme', @$bootswatch_theme))?>
								</div>
								<div class="col-md-6">
									<label class="form-label">Background Logo</label>
									<?=options(['name' => 'logo_background_color', 'id' => 'logo-background-color', 'class' => 'form-select'], ['default' => 'Sesuai Color Scheme', 'dark' => 'Dark', 'light' => 'Light'], set_value('logo_background_color', @$logo_background_color))?>
								</div>
								<div class="col-md-6">
									<label class="form-label">Sidebar Color</label>
									<?=options(['name' => 'sidebar_color', 'id' => 'sidebar-color', 'class' => 'form-select'], ['light' => 'Light', 'dark' => 'Dark'],  set_value('sidebar_color', @$sidebar_color))?>
								</div>
								<div class="col-md-6">
									<label class="form-label">Font Family</label>
									<select name="font_family" id="font" class="form-select">
										<?php
										$selectedFontFamily = set_value('font_family', $currentFontFamily);
										foreach ($fontMap as $fontKey => $font) {
											$selected = $selectedFontFamily === $font['family'] ? ' selected' : '';
											echo '<option value="'.esc($font['family'], 'attr').'" data-font-key="'.esc($fontKey, 'attr').'" data-css-path="'.esc($font['css_path'], 'attr').'"'.$selected.'>'.$font['label'].'</option>';
										}
										?>
									</select>
									<div class="font-preview-note" id="font-preview-note">Preview cepat: The quick brown fox jumps over the lazy dog.</div>
								</div>
								<div class="col-md-6">
									<label class="form-label">Font Size</label>
									<div class="range-slider-test" id="font-size-control">
										<?php
										$value = @$font_size ? $font_size : $request->getPost('font_size');
										?>
									  <div class="range-slider-track"><span class="range-slider-progress" id="font-size-progress"></span></div>
									  <input class="range-slider" id="font-size" type="range" step="0.5" name="font_size" value="<?=$value?>" min="10" max="18">
									  <?php
									  $pos_left = (($value - 10 ) * 33);
									  ?>
									  <output for="font-size" id="font-size-output" style="left:<?=$pos_left?>px"><?=$value?></output><span class="range-value-suffix">px</span>
									  <div class="range-slider-scale">
									  	<span>10</span>
									  	<span>12</span>
									  	<span>14</span>
									  	<span>16</span>
									  	<span>18</span>
									  </div>
									</div>
								</div>
							</div>
						</div>
						<div class="col-xl-4">
							<div class="theme-preview-surface setting-layout-preview">
								<div class="setting-layout-preview-header">
									<span class="setting-layout-preview-badge">Preview</span>
									<h6 id="preview-theme-name"><?=ucwords(str_replace('-', ' ', @$color_scheme))?></h6>
									<p>Pratinjau cepat warna, sidebar, dan tipografi.</p>
								</div>
								<div class="setting-layout-preview-body">
									<div class="preview-browser-bar">
										<span></span><span></span><span></span>
									</div>
									<div class="preview-frame">
										<div class="preview-sidebar" id="preview-sidebar">
											<div class="preview-sidebar-logo">NSD</div>
											<div class="preview-sidebar-group">
												<div class="preview-sidebar-group-header active">Core Module</div>
												<div class="preview-sidebar-menu">
													<div class="preview-sidebar-item active">Dashboard</div>
													<div class="preview-sidebar-item tree-open">Master Data</div>
												</div>
											</div>
											<div class="preview-sidebar-group">
												<div class="preview-sidebar-group-header">Configuration</div>
												<div class="preview-sidebar-menu">
													<div class="preview-sidebar-item highlight">Pengaturan Layout</div>
													<div class="preview-sidebar-item">Role Permission</div>
												</div>
											</div>
										</div>
										<div class="preview-main">
											<div class="preview-topbar" id="preview-topbar"></div>
											<div class="preview-card">
												<div class="preview-chip">Form UX</div>
												<div class="preview-line preview-line-lg"></div>
												<div class="preview-line"></div>
												<div class="preview-input"></div>
												<div class="preview-input preview-input-sm"></div>
												<div class="preview-button">Simpan Perubahan</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="sticky-form-actions">
					<div class="d-flex justify-content-end">
						<button type="submit" name="submit" id="btn-submit" value="submit" class="btn btn-primary px-4">Submit</button>
					</div>
				</div>
			</div>
		</form>
	</div>
</div>
<script>
window.FONT_MAP = <?=json_encode($fontMap, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)?>;
</script>
