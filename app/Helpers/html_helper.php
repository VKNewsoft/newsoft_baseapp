<?php
/**
 * html_helper.php
 * HTML Form Elements & UI Components Helper
 * 
 * @author  VKNewsoft - Newsoft Developer, 2025
 */

// options(['name' => 'gender'], ['M' => 'Male', 'F' => 'Female'], ['input_field', 'default'])
function options($attr, $data, $selected = '', $print = false) 
{
	if (empty($attr['class'])) {
		$attr['class'] = 'form-select';
	} else {
		$attr['class'] = $attr['class'] . ' form-select';
	}
	
	foreach ($attr as $key => $val) {
		$attribute[] = $key . '="' . $val . '"'; 
	}
	$attribute = join(' ', $attribute);
	
	if ($selected != '') {
		if (!is_array($selected)) {
			$selected = [$selected];
		}
	}
	
	$result = '
	<select '. $attribute .'>';
		foreach($data as $key => $value) 
		{
			$attr_option = '';
			if (is_array($value)) {
				$text = $value['text'];
				if (key_exists('attr', $value)) {
					$attr_option = ' ';
					foreach ($value['attr'] as $attr_key => $attr_val) {
						$attr_option .= $attr_key . '="' . $attr_val . '"'; 
					}
				}
				
			} else {
				$text = $value;
			}
			
			$option_selected = '';
			if ($selected != '') {
				if ( @empty($_REQUEST[$selected[0]]) ) {
					if (in_array( $key, $selected)) {
						$option_selected = true;
					}
				} else {
					if ($key == $_REQUEST[$selected[0]]) {
						$option_selected = true;
					}
				}
			}
			
			if ($option_selected) {
				$option_selected = ' selected';
			}
			$result .= '<option ' . $attr_option . ' value="'.$key.'"'.$option_selected.'>'.$text.'</option>';
		}
		
	$result .= '</select>';
	
	if ($print) {
		echo $result;
	} else {
		return $result;
	}
	
}
/*
checkbox(
	[
		'attr' => ['name' => $module[$id_module]['nama_module']
				, 'class=' => 'module-name'
				, 'id' => $module[$id_module]['nama_module']
			]
		, 'label' => '<strong>' . $module[$id_module]['judul_module'] . '</strong>'
	]
)

checkbox(
	[
		'attr_parent' => ['class' => 'ms-4']
		, 'attr' => ['name' => 'permission[]'
					,'class' => 'permission'
					, 'id' => $val['nama_permission']
				]
		, 'label' => $val['nama_permission']
	]
)

*/
function checkbox($data, $checked = []) 
{
	
	if (!is_array($data)) {
		$data[] = ['attr' => ['name' => $data, 'id' => $data]];
	} else {
		if (!key_exists(0, $data)) {
			$clone = $data;
			$data = [];
			$data[] = $clone;
		}
	}
	
	$checkbox = '';
	foreach ($data as $key => $val) 
	{
		// Container
		$container_class = 'checkbox form-check mb-1';
		$attr_container = '';
		
		if (key_exists('attr_container', $val)) 
		{
			if (key_exists('class', $val['attr_container'])) {
				$container_class .= ' ' . $val['attr_container']['class'];
				unset ( $val['attr_container']['class'] );
			}
			
			foreach ($val['attr_container'] as $attr_name => $attr_value) {
				$attr_container[] = $attr_name . '=' . $attr_value;
			}
			
			if ($attr_container) {
				$attr_container = ' ' . join(' ', $attr_container);
			}
		}
		
		// Checkbox
		$attr_checked = '';
		if ($checked === true) {
			$attr_checked = 'checked';
		} else {
			if (is_array($checked)) {
				if (in_array($val['attr']['name'], $checked)) {
					$attr_checked = 'checked';
				}
			} else {
				if ($val['attr']['name'] == $checked) {
					$attr_checked = 'checked';
				}
			}
		}
		
		if (key_exists('class', $val['attr'])) {
			$val['attr']['class'] = $val['attr']['class'] . ' form-check-input';
		} else {
			$val['attr']['class'] = 'form-check-input';
		}
		
		$attr_checkbox = [];
		foreach ($val['attr'] as $attr_name => $attr_value) {
			$attr_checkbox[] = $attr_name . '="' . $attr_value . '"';
		}
		// echo '<pre>'; print_r($attr_checkbox); die;
		$attr_checkbox = ' ' . join(' ', $attr_checkbox) . ' ';
		
		$checkbox .= '<div class="'. $container_class .'"' . $attr_container . '>
			<input type="checkbox"'. $attr_checkbox . $attr_checked.' >
			<label class="form-check-label" for="'. $val['attr']['id'].'">' . $val['label'] . '</label>
		</div>';
	}
	
	return $checkbox;
}

function btn_submit($data = []) {
	$html = $attr = '';
	// echo '<pre>'; print_r($data);
	foreach ($data as $key => $val) {
		if (key_exists('attr', $val)) {
			foreach($val['attr'] as $key_attr => $val_attr) {
				$attr .= $key_attr . '="' . $val_attr . '"';
			}
		}
			
		$html .= '<button type="submit" class="btn '.$val['btn_class'].' btn-xs"'.$attr.'>
							<span class="btn-label-icon"><i class="'.$val['icon'].'"></i></span> '.$val['text'].'
			</button>';
	}
	
	return $html;
}

function btn_action($data = []) {

	$html = '<div class="form-inline btn-action-group">';
	$attr = '';
	foreach ($data as $key => $val) 
	{
		if ($key == 'edit') 
		{
			$btn_class = 'btn btn-success btn-xs me-1';
			if (!key_exists('attr', $val)) {
				
				 $val['attr'] = ['class' => $btn_class];
			}
			
			foreach ($val['attr'] as $attr_name => $attr_value) {
				if ($attr_name == 'class') {
					$attr_value = $btn_class . ' ' . $attr_value;
				}
				
				$attr .= $attr_name . '="' . $attr_value . '"';
			}
			
			$html .= '<a href="'.$data[$key]['url'].'" ' . $attr . '>
						<span class="btn-label-icon"><i class="fa fa-edit pe-1"></i></span> Edit
					</a>';
		}
		
		else if ($key == 'delete') {
			$html .= '<form method="post" action="'. $data[$key]['url'] .'">
					<button type="submit" data-action="delete-data" data-delete-title="'.$data[$key]['delete-title'].'" class="btn btn-danger btn-xs">
						<span class="btn-label-icon"><i class="fa fa-times pe-1"></i></span> Delete
					</button>
					<input type="hidden" name="delete" value="delete"/>
					<input type="hidden" name="id" value="'.$data[$key]['id'].'"/>
				</form>';
		}
		else {
			
			if (key_exists('attr', $data[$key])) {
				foreach($data[$key]['attr'] as $key_attr => $val_attr) {
					$attr .= $key_attr . '="' . $val_attr . '"';
				}
			}
			// print_r($attr); die;
			$html .= '<a href="'.$data[$key]['url'].'" class="btn '.$data[$key]['btn_class'].' btn-xs me-1" ' . $attr . '>
						<span class="btn-label-icon"><i class="'.$data[$key]['icon'].'"></i></span>&nbsp;'.$data[$key]['text'].'
					</a>';
			
		}
	}
	
	$html .= '</div>';
	return $html;
}

function btn_action_custom($data = []) {
	$html = '<div class="form-inline btn-action-group justify-content-start">';

	// detect if approval is required (presence of 'approve' key)
	$hasApprove = false;
	foreach ($data as $k => $v) {
		if ($k === 'approve') {
			$hasApprove = true;
			break;
		}
	}

	foreach ($data as $key => $val) {
		$attr = '';

		if ($key == 'edit') {
			$btn_class = 'btn btn-success btngreen btn-xs me-1';
			if (!key_exists('attr', $val)) {
				$val['attr'] = ['class' => $btn_class, 'title' => 'Edit'];
			}

			foreach ($val['attr'] as $attr_name => $attr_value) {
				if ($attr_name == 'class') {
					$attr_value = $btn_class . ' ' . $attr_value;
				}
				$attr .= $attr_name . '="' . $attr_value . '"';
			}

			$html .= '<a href="'.$data[$key]['url'].'" ' . $attr . '>
						<span class="btn-label-icon"><i class="fa fa-edit p-1"></i></span>
					</a>';
		} else if ($key == 'delete') {
			$html .= '<form method="post" action="'. $data[$key]['url'] .'">
					<button type="submit" data-action="delete-data" data-delete-title="'.$data[$key]['delete-title'].'" class="btn btn-danger btn-xs me-1" title="Hapus">
						<span class="btn-label-icon"><i class="fas fa-trash p-1"></i></span>
					</button>
					<input type="hidden" name="delete" value="delete"/>
					<input type="hidden" name="id" value="'.$data[$key]['id'].'"/>
				</form>';
		} else if ($key == 'approve') {
			$btn_class = 'btn btn-success btn-xs me-1';
			if (!key_exists('attr', $val)) {
				$val['attr'] = ['class' => $btn_class, 'title' => 'Approve', 'data-id' => isset($val['attr']['data-id']) ? $val['attr']['data-id'] : ''];
			}
			$attr = '';
			foreach ($val['attr'] as $attr_name => $attr_value) {
				if ($attr_name == 'class') {
					$attr_value = $btn_class . ' ' . $attr_value;
				}
				$attr .= $attr_name . '="' . $attr_value . '" ';
			}
			$html .= '<button type="button" ' . trim($attr) . ' class="btn btn-success btn-xs me-1 approve-button" style="background-color: green !important; color: white; border:none;">
						<span class="btn-label-icon"><i class="fa fa-check p-1"></i></span> Approve Perubahan
					  </button>';
		} else if ($key == 'detail') {
			// If approval is required, don't display the detail button
			if ($hasApprove) {
				continue;
			}
			$btn_class = 'btn btn-success btn-xs me-1';
			if (!key_exists('attr', $val)) {
				$val['attr'] = ['class' => $btn_class, 'title' => 'Detail', 'data-id' => isset($val['attr']['data-id']) ? $val['attr']['data-id'] : ''];
			}
			$attr = '';
			foreach ($val['attr'] as $attr_name => $attr_value) {
				if ($attr_name == 'class') {
					$attr_value = $btn_class . ' ' . $attr_value;
				}
				$attr .= $attr_name . '="' . $attr_value . '" ';
			}
			$html .= '<button type="button" ' . trim($attr) . ' class="btn btn-success btn-xs me-1 detail-button" style="background-color: #004080 !important; color: white; border:none;">
						<span class="btn-label-icon"><i class="fa fa-eye p-1"></i></span>
					  </button>';
		}
		else {
			if (key_exists('attr', $data[$key])) {
				foreach ($data[$key]['attr'] as $key_attr => $val_attr) {
					$attr .= $key_attr . '="' . $val_attr . '"';
				}
			}
			$html .= '<a href="'.$data[$key]['url'].'" class="btn '.$data[$key]['btn_class'].' btn-xs me-1" ' . $attr . ' title="'.$data[$key]['text'].'">
						<span class="btn-label-icon"><i class="'.$data[$key]['icon'].'"></i></span>&nbsp;'.$data[$key]['text'].'
					</a>';
		}
	}

	$html .= '</div>';
	return $html;
}

/**
 * Generate a Bootstrap dropdown action button group dynamically.
 *
 * $actions = [
 *   ['type'=>'link','label'=>'Edit','icon'=>'fas fa-edit me-2 text-success','href'=>'#','attrs'=>['data-id'=>$id,'class'=>'btn-edit']],
 *   ['separator'=>true],
 *   ['type'=>'link','label'=>'Delete','icon'=>'fas fa-times me-2','href'=>'#','attrs'=>['data-id'=>$id,'data-delete-title'=>"Hapus nama company : <strong>$name</strong>",'class'=>'btn-delete text-danger']]
 * ];
 *
 * echo btn_dropdown_actions($actions, ['button_label'=>'Actions','button_class'=>'btn btn-sm dropdown-toggle d-flex align-items-center','menu_class'=>'dropdown-menu-end','min_width'=>'220px']);
 *
 */
function btn_dropdown_actions(array $actions = [], array $options = []): string
{
	$opt = array_merge([
		'button_label' => 'Actions',
		'button_class' => 'btn btn-sm dropdown-toggle d-flex align-items-center',
		'button_attrs' => [], // extra attributes for button
		'menu_class' => 'dropdown-menu-end shadow-lg rounded-3 border-0',
		'menu_style' => 'min-width:220px; overflow:hidden;',
		'wrapper_class' => 'btn-group',
		'icon_before' => ' <i class="fas fa-ellipsis-v me-2"></i>',
		'escape' => false, // if true, escape labels (default false to allow html in label)
	], $options);

	$build_attrs = function(array $attrs) {
		$parts = [];
		foreach ($attrs as $k => $v) {
			if ($v === true) {
				$parts[] = $k;
			} elseif ($v === false || $v === null) {
				continue;
			} else {
				$parts[] = $k . '="' . htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8') . '"';
			}
		}
		return $parts ? ' ' . implode(' ', $parts) : '';
	};

	$button_attrs = array_merge([
		'type' => 'button',
		'data-bs-toggle' => 'dropdown',
		'aria-expanded' => 'false'
	], $opt['button_attrs']);

	$html = '<div class="'. $opt['wrapper_class'] .'">';
	$html .= '<button class="'. $opt['button_class'] .'"' . $build_attrs($button_attrs) . ' title="'.htmlspecialchars($opt['button_label'], ENT_QUOTES, 'UTF-8').'" style="background: linear-gradient(90deg,#4f46e5,#06b6d4); color:#fff; box-shadow:0 6px 18px rgba(79,70,229,0.18); border:none; transition:transform .12s ease;">'
		   . ($opt['icon_before'] ? $opt['icon_before'] : '')
		   . htmlspecialchars($opt['button_label'], ENT_QUOTES, 'UTF-8')
		   . '</button>';

	$html .= '<ul class="dropdown-menu '. $opt['menu_class'] .'" style="'. $opt['menu_style'] .'">';

	foreach ($actions as $act) {
		// separator
		if (!empty($act['separator'])) {
			$html .= '<li><hr class="dropdown-divider"></li>';
			continue;
		}

		$type = $act['type'] ?? 'link';
		$label = $act['label'] ?? '';
		$icon = !empty($act['icon']) ? '<i class="'. $act['icon'] .'"></i>' : '';
		$attrs = $act['attrs'] ?? [];
		// default classes for dropdown item
		if (empty($attrs['class'])) {
			$attrs['class'] = 'dropdown-item d-flex align-items-center';
		} else {
			$attrs['class'] = $attrs['class'] . ' dropdown-item d-flex align-items-center';
		}

		// ensure a small gap between icon and label; if icon already has Bootstrap spacing classes (me-, ms-, pe-, ps-) don't add extra
		$icon_html = '';
		if ($icon) {
			if (strpos($icon, 'me-') === false && strpos($icon, 'ms-') === false && strpos($icon, 'pe-') === false && strpos($icon, 'ps-') === false) {
				$icon_html = '<span class="me-2">' . $icon . '</span>';
			} else {
				$icon_html = $icon;
			}
		}

		if ($type === 'link') {
			$href = $act['href'] ?? '#';
			$attrs['href'] = $href;
			$attr_str = $build_attrs($attrs);
			$content = ($icon_html ? $icon_html . ' ' : '') . ($opt['escape'] ? htmlspecialchars($label, ENT_QUOTES, 'UTF-8') : $label);
			$html .= '<li><a' . $attr_str . ' style="transition:background .12s;">' . $content . '</a></li>';
		} elseif ($type === 'button') {
			// render a button inside li
			$attrs['type'] = 'button';
			$attr_str = $build_attrs($attrs);
			$content = ($icon_html ? $icon_html . ' ' : '') . ($opt['escape'] ? htmlspecialchars($label, ENT_QUOTES, 'UTF-8') : $label);
			$html .= '<li><button' . $attr_str . ' style="transition:background .12s;">' . $content . '</button></li>';
		} elseif ($type === 'form') {
			// form-based action (method, action, hidden inputs)
			$form_action = $act['action'] ?? '#';
			$form_method = strtoupper($act['method'] ?? 'POST');
			$hidden = $act['hidden'] ?? [];
			$btn_attrs = $act['attrs'] ?? [];
			if (empty($btn_attrs['class'])) {
				$btn_attrs['class'] = 'dropdown-item d-flex align-items-center';
			} else {
				$btn_attrs['class'] = $btn_attrs['class'] . ' dropdown-item d-flex align-items-center';
			}
			$attr_str = $build_attrs($btn_attrs);
			$content = ($icon_html ? $icon_html . ' ' : '') . ($opt['escape'] ? htmlspecialchars($label, ENT_QUOTES, 'UTF-8') : $label);
			$html .= '<li><form method="'.htmlspecialchars($form_method, ENT_QUOTES, 'UTF-8').'" action="'.htmlspecialchars($form_action, ENT_QUOTES, 'UTF-8').'">';
			foreach ($hidden as $name => $value) {
				$html .= '<input type="hidden" name="'.htmlspecialchars($name, ENT_QUOTES, 'UTF-8').'" value="'.htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8').'">';
			}
			$html .= '<button' . $attr_str . ' style="transition:background .12s;">' . $content . '</button>';
			$html .= '</form></li>';
		} else {
			// fallback to link
			$href = $act['href'] ?? '#';
			$attrs['href'] = $href;
			$attr_str = $build_attrs($attrs);
			$content = ($icon_html ? $icon_html . ' ' : '') . ($opt['escape'] ? htmlspecialchars($label, ENT_QUOTES, 'UTF-8') : $label);
			$html .= '<li><a' . $attr_str . ' style="transition:background .12s;">' . $content . '</a></li>';
		}
	}

	$html .= '</ul></div>';

	return $html;
}

function btn_label($data) 
{
	$attr = [];
	if (key_exists('attr', $data)) {
		foreach($data['attr'] as $name => $value) {
			if ($name == 'class') {
				// $value = 'btn-inline ' . $value;
			}
			$attr[] = $name . '="' . $value . '"';
		}
	}
	
	$label = '';
	if (key_exists('label', $data)) {
		$label = $data['label'];
	}
	
	$icon = '';
	if (key_exists('icon', $data)) {
		$padding = $label ? ' pe-1' : '';
		$icon = '<span class="btn-label-icon"><i class="' . $data['icon'] . $padding . '"></i></span> ';
	}
	
	$html = '
		<button  type="button" ' . join(' ', $attr) . '>'.$icon. $label . '</button>';
	return $html;
}

function btn_link($data) 
{
	$attr = [];
	if (key_exists('attr', $data)) {
		foreach($data['attr'] as $name => $value) {
			if ($name == 'class') {
				// $value = 'btn-inline ' . $value;
			}
			$attr[] = $name . '="' . $value . '"';
		}
	}
	
	$label = '';
	if (key_exists('label', $data)) {
		$label = $data['label'];
	}
	
	$icon = '';
	if (key_exists('icon', $data)) {
		$padding = $label ? ' pe-1' : '';
		$icon = '<span class="btn-label-icon"><i class="' . $data['icon'] . $padding . '"></i></span> ';
	}
	
	$html = '
		<a href="'.$data['url'].'" ' . join(' ', $attr) . '>'.$icon. $label . '</a>';
	return $html;
}