/**
 * Dropdown anggota di-refresh berdasarkan project terpilih agar assignment task selalu sesuai relasi project_member.
 */
(function() {
	function buildOptions(selectElement, options, selectedValue) {
		if (!selectElement) {
			return;
		}

		selectElement.innerHTML = '';
		Object.keys(options || {}).forEach(function(key) {
			var option = document.createElement('option');
			option.value = key;
			option.textContent = options[key];
			if (String(key) === String(selectedValue || '')) {
				option.selected = true;
			}
			selectElement.appendChild(option);
		});
	}

	document.addEventListener('DOMContentLoaded', function() {
		var projectSelect = document.getElementById('task-project-id');
		var memberSelect = document.getElementById('task-member-id');
		if (!projectSelect || !memberSelect) {
			return;
		}

		projectSelect.addEventListener('change', function() {
			var projectId = projectSelect.value || '';
			var url = module_url + '/memberOptions?project_id=' + encodeURIComponent(projectId);

			fetch(url, {
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				}
			})
				.then(function(response) {
					return response.json();
				})
				.then(function(result) {
					// Saat project berubah, selection anggota dikosongkan agar user memilih member yang benar.
					buildOptions(memberSelect, result.data || {}, '');
				})
				.catch(function() {
					buildOptions(memberSelect, {'': 'Pilih anggota project'}, '');
				});
		});
	});
})();
