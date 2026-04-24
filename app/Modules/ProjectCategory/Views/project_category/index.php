<?php
/**
 * Halaman kategori menampilkan total project per kategori agar admin tahu master data yang aktif digunakan.
 */
helper('html');
$flashMessage = session()->getFlashdata('message');
?>
<style>
	/* Wrapper halaman dibuat setinggi viewport agar area daftar kategori
	   tetap memanjang sampai bawah layar dan tidak berhenti di tengah konten. */
	.project-category-page-shell {
		min-height: calc(100vh - 140px);
		display: flex;
		flex-direction: column;
	}

	/* Card utama dibuat fleksibel supaya area tabel kategori mengisi sisa tinggi halaman. */
	.project-category-page-card {
		flex: 1 1 auto;
		display: flex;
		flex-direction: column;
	}

	/* Body card mengikuti tinggi parent agar region tabel dapat turun sampai mendekati footer. */
	.project-category-page-card > .card-body {
		flex: 1 1 auto;
		display: flex;
		flex-direction: column;
	}

	/* Overflow horizontal tetap dikunci di container agar tabel tidak melewati layar. */
	.project-category-page-card .card-table-wrap,
	.project-category-page-card .table-responsive {
		flex: 1 1 auto;
		width: 100%;
		max-width: 100%;
		overflow-x: auto;
	}

	/* Lebar minimum tabel dijaga agar susunan kolom tetap stabil pada layar kecil. */
	.project-category-page-card table {
		width: 100%;
		min-width: 720px;
	}
</style>
<div class="page-shell project-category-page-shell">
	<div class="page-hero">
		<div>
			<div class="page-kicker">Project Category</div>
			<h3 class="page-heading"><?=$current_module['judul_module']?></h3>
			<p class="page-copy mb-0">Master kategori project untuk kebutuhan klasifikasi project dan task lintas tim.</p>
		</div>
		<div class="page-actions">
			<a href="<?=$module_url?>/add" class="btn btn-success btn-sm"><i class="fa fa-plus pe-1"></i> Tambah Category</a>
		</div>
	</div>

	<?php if (!empty($flashMessage)) { show_alert($flashMessage); } ?>

	<div class="card page-card project-category-page-card">
		<div class="card-body p-0">
			<div class="table-responsive card-table-wrap">
				<table class="table table-striped table-bordered table-hover align-middle mb-0">
					<thead>
						<tr>
							<th style="width: 60px;">No</th>
							<th>Nama Category</th>
							<th class="text-center">Total Project</th>
							<th style="width: 120px;">Action</th>
						</tr>
					</thead>
					<tbody>
						<?php if (!$categories): ?>
							<tr>
								<td colspan="4" class="text-center text-muted py-4">Belum ada data kategori</td>
							</tr>
						<?php endif; ?>

						<?php foreach ($categories as $index => $category): ?>
							<?php
							$actions = [
								['type' => 'link', 'href' => $module_url . '/edit?id=' . $category['id_project_category'], 'icon' => 'fas fa-edit text-success', 'label' => 'Edit'],
								['separator' => true],
								['type' => 'form', 'action' => $module_url . '/delete', 'icon' => 'fas fa-trash text-danger', 'label' => 'Hapus', 'hidden' => ['id' => $category['id_project_category']], 'attrs' => ['onclick' => "return confirm('Hapus kategori ini?')"]],
							];
							?>
							<tr>
								<td class="text-center"><?=($index + 1)?></td>
								<td><?=esc($category['name'])?></td>
								<td class="text-center"><span class="badge bg-secondary"><?=$category['total_project']?></span></td>
								<td><?=btn_dropdown_actions($actions)?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
