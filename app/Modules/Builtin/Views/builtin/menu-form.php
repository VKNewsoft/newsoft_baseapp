<?php
helper('html');
?>
<form method="post" class="modal-form" id="add-form" action="<?=current_url()?>" >
	<div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Nama Menu</label>
			<div class="col-sm-8">
				<input class="form-control" type="text" name="nama_menu" value="<?=@$menu['nama_menu']?>" placeholder="Nama Menu" required="required"/>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">URL</label>
			<div class="col-sm-8">
				<input class="form-control" type="text" name="url" value="<?=@$menu['url']?>" placeholder="URL" required="required"/>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Aktif</label>
			<div class="col-sm-8">
				<?php
					$checked = @$menu['aktif'] ? 'checked="checked"' : '';
				?>
				<div class="form-check-input-sm form-switch"><input name="aktif" type="checkbox" class="form-check-input" value="1" <?=$checked?>></div>
				<small class="form-text text-muted"><em>Jika tidak aktif, semua children tidak akan dimunculkan</em></small>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Module</label>
			<div class="col-sm-8">
				<?php
				$options[0] = 'Tidak ada module';
				foreach ($list_module as $key => $val) {
					$options[$val['id_module']] = $val['nama_module'] . ' | ' . $val['judul_module'] . ' (' . $val['nama_status']  . ')';
				}
				echo options(['name' => 'id_module', 'id' => 'id-module', 'class' => 'select2'], $options, @$menu['id_module']);
				
				echo '<small class="form-text text-muted"><em>Untuk highlight menu dan parent</em></small>';
				?>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Use icon</label>
			<div class="col-sm-8 form-inline">
				<?php 
					$selected = @$menu['class'] ? 1 : 0;
					$selected2 = @$menu['class'] ? '' : 'selected';
					$options = array(1 => 'Ya', 0 => 'Tidak');
					$display = $selected ? '' : 'style="display:none"';
					echo options(['name' => 'use_icon'], $options, $selected2);
					$icon = @$menu['class'] ? $menu['class'] : 'far fa-circle';
				?>
				<a href="javascript:void(0)" class="icon-preview" data-action="faPicker" <?=$display?>><i class="<?=$icon?>"></i></a>
				<input type="hidden" name="icon_class" value="<?=$icon?>"/>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Kategori</label>
			<div class="col-sm-8 form-inline">
				<?php 
					$options = [];
					foreach ($menu_kategori as $val) {
						$options[$val['id_menu_kategori']] = $val['nama_kategori'];
					}
					$options[''] = 'Uncategorized';
					echo options(['name' => 'id_menu_kategori'], $options, @$menu['id_menu_kategori']); 
				?>
			</div>
		</div>
		<div class="row mb-3">
			<label class="col-sm-3 col-form-label">Role</label>
			<div class="col-sm-8">
				<?php
				$options = [];
				foreach ($roles as $val) {
					$options[$val['id_role']] = $val['judul_role'];
				}
				$selected = [];
				if (!empty($menu['id_role'])) {
					$selected = explode(',', $menu['id_role']);
				}
				echo options(['name' => 'id_role[]', 'class' => 'select2', 'multiple' => 'multiple'], $options, $selected); 
				?>
			</div>
		</div>
		<input type="hidden" name="id" value="<?= esc($request->getGet('id') ?? '') ?>"/>
		
	</div>
</form>