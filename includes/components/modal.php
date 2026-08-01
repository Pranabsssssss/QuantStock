<?php
/**
 * QuantStock — Modal Component
 * 
 * Usage:
 * renderModal(['id' => 'addProductModal', 'title' => 'Add Product', 'size' => 'lg']);
 * ... modal body content ...
 * renderModalEnd();
 */

function renderModal(array $config): void {
    $id    = $config['id'] ?? 'modal';
    $title = e($config['title'] ?? '');
    $size  = $config['size'] ?? 'md'; // sm, md, lg, xl
    ?>
    <div class="modal-overlay" id="<?= $id ?>" style="display:none">
        <div class="modal-container modal-<?= $size ?>">
            <div class="modal-header">
                <h3 class="modal-title"><?= $title ?></h3>
                <button class="modal-close" onclick="closeModal('<?= $id ?>')" aria-label="Close">
                    <i data-lucide="x"></i>
                </button>
            </div>
            <div class="modal-body">
    <?php
}

function renderModalEnd(): void {
    ?>
            </div>
        </div>
    </div>
    <?php
}
