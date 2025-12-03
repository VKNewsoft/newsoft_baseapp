<?php
/**
 * Bootstrap 5 Pagination Template
 * @var \CodeIgniter\Pager\PagerRenderer $pager
 */

$pager->setSurroundCount(2);
?>

<nav aria-label="Page navigation">
	<ul class="pagination pagination-sm justify-content-end mb-0">
		<?php if ($pager->hasPrevious()) : ?>
			<li class="page-item">
				<a class="page-link" href="<?= $pager->getFirst() ?>" title="First page">
					<i class="bi bi-chevron-double-left"></i>
				</a>
			</li>
			<li class="page-item">
				<a class="page-link" href="<?= $pager->getPrevious() ?>" title="Previous page">
					<i class="bi bi-chevron-left"></i>
				</a>
			</li>
		<?php else : ?>
			<li class="page-item disabled">
				<span class="page-link"><i class="bi bi-chevron-double-left"></i></span>
			</li>
			<li class="page-item disabled">
				<span class="page-link"><i class="bi bi-chevron-left"></i></span>
			</li>
		<?php endif ?>

		<?php foreach ($pager->links() as $link) : ?>
			<li class="page-item <?= $link['active'] ? 'active' : '' ?>">
				<a class="page-link" href="<?= $link['uri'] ?>" <?= $link['active'] ? 'aria-current="page"' : '' ?>>
					<?= $link['title'] ?>
				</a>
			</li>
		<?php endforeach ?>

		<?php if ($pager->hasNext()) : ?>
			<li class="page-item">
				<a class="page-link" href="<?= $pager->getNext() ?>" title="Next page">
					<i class="bi bi-chevron-right"></i>
				</a>
			</li>
			<li class="page-item">
				<a class="page-link" href="<?= $pager->getLast() ?>" title="Last page">
					<i class="bi bi-chevron-double-right"></i>
				</a>
			</li>
		<?php else : ?>
			<li class="page-item disabled">
				<span class="page-link"><i class="bi bi-chevron-right"></i></span>
			</li>
			<li class="page-item disabled">
				<span class="page-link"><i class="bi bi-chevron-double-right"></i></span>
			</li>
		<?php endif ?>
	</ul>
</nav>
