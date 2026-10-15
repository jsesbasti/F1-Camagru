<div class="studio-thumb" data-id="<?= (int) $photo['id'] ?>" data-draft="<?= $photo['is_draft'] ? '1' : '0' ?>" data-overlays="<?= htmlspecialchars(json_encode($photo['overlays'])) ?>" data-src="<?= htmlspecialchars($photo['source_url']) ?>">
    <button type="button" class="studio-thumb-open" aria-label="<?= $photo['is_draft'] ? 'Edit draft' : 'View post' ?>">
        <img src="<?= htmlspecialchars($photo['image_url']) ?>" alt="" loading="lazy">
    </button>
    <span class="studio-thumb-badge" <?= $photo['is_draft'] ? '' : 'hidden' ?>>Draft</span>
    <button type="button" class="studio-thumb-delete" aria-label="Delete draft" <?= $photo['is_draft'] ? '' : 'hidden' ?>>&times;</button>
</div>
