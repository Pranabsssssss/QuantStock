<?php
/**
 * QuantStock — Table Component
 * 
 * Usage:
 * renderTableStart(['id' => 'productsTable', 'columns' => ['Name', 'SKU', 'Price', 'Stock', 'Actions']]);
 * ... table rows ...
 * renderTableEnd($pagination);
 */

function renderTableStart(array $config): void {
    $id = $config['id'] ?? 'dataTable';
    $columns = $config['columns'] ?? [];
    ?>
    <div class="table-wrapper">
        <div class="table-responsive">
            <table class="data-table" id="<?= e($id) ?>">
                <thead>
                    <tr>
                        <?php foreach ($columns as $col): ?>
                            <?php if (is_array($col)): ?>
                                <th class="<?= e($col['class'] ?? '') ?>" <?= !empty($col['sort']) ? 'data-sort="'.$col['sort'].'"' : '' ?>><?= e($col['label']) ?></th>
                            <?php else: ?>
                                <th><?= e($col) ?></th>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
    <?php
}

function renderTableEnd(?array $pagination = null): void {
    ?>
                </tbody>
            </table>
        </div>
        <?php if ($pagination && $pagination['total'] > 0): ?>
            <div class="table-footer">
                <span class="table-info">
                    Showing <?= (($pagination['current'] - 1) * $pagination['per_page']) + 1 ?> 
                    to <?= min($pagination['current'] * $pagination['per_page'], $pagination['total']) ?> 
                    of <?= formatNumber($pagination['total']) ?> entries
                </span>
                <div class="table-pagination">
                    <?php if ($pagination['has_prev']): ?>
                        <button class="page-btn" onclick="goToPage(<?= $pagination['current'] - 1 ?>)">
                            <i data-lucide="chevron-left"></i>
                        </button>
                    <?php endif; ?>
                    
                    <?php
                    $start = max(1, $pagination['current'] - 2);
                    $end = min($pagination['total_pages'], $pagination['current'] + 2);
                    for ($i = $start; $i <= $end; $i++):
                    ?>
                        <button class="page-btn <?= $i === $pagination['current'] ? 'active' : '' ?>" onclick="goToPage(<?= $i ?>)">
                            <?= $i ?>
                        </button>
                    <?php endfor; ?>
                    
                    <?php if ($pagination['has_next']): ?>
                        <button class="page-btn" onclick="goToPage(<?= $pagination['current'] + 1 ?>)">
                            <i data-lucide="chevron-right"></i>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
