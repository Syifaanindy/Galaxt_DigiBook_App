<?php
$page        = $page ?? 1;
$total_pages = $total_pages ?? 1;
$total_data  = $total_data ?? 0;
$target_url  = $target_url ?? basename($_SERVER['PHP_SELF']);
?>

<?php if ($total_pages > 1): ?>
<div class="d-flex justify-content-between align-items-center mt-4">
    <div class="text-muted" style="font-size: 14px;">
        Halaman <strong><?= $page; ?></strong> dari <strong><?= $total_pages; ?></strong> (Total <?= $total_data; ?> data)
    </div>
    
    <nav aria-label="Page navigation">
      <ul class="pagination mb-0">
        
        <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
          <a class="page-link" href="<?= ($page <= 1) ? '#' : $target_url . '?page=' . ($page - 1); ?>">
            Previous
          </a>
        </li>
        
        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
          <li class="page-item <?= ($page == $i) ? 'active' : ''; ?>">
            <a class="page-link" href="<?= $target_url . '?page=' . $i; ?>">
                <?= $i; ?>
            </a>
          </li>
        <?php endfor; ?>
        
        <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
          <a class="page-link" href="<?= ($page >= $total_pages) ? '#' : $target_url . '?page=' . ($page + 1); ?>">
            Next
          </a>
        </li>

      </ul>
    </nav>
</div>
<?php endif; ?>