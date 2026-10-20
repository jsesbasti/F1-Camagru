<?php require __DIR__ . '/partials/header.php';?>
<div class="flex min-h-screen items-center justify-center bg-[radial-gradient(circle_at_top,rgba(225,6,0,0.22),transparent_35%)] p-6 pt-20 pb-24 max-[480px]:px-2.5">
<main class="w-[min(96vw,1180px)] overflow-hidden rounded-2xl border border-line border-t-4 border-t-f1-red bg-carbon shadow-[0_24px_80px_rgba(0,0,0,0.45)]">
    <section class="border-b border-line bg-[linear-gradient(135deg,rgba(31,31,39,0.96),rgba(15,15,20,0.96))] p-6">
        <h1 class="mb-2 font-racing text-[clamp(2rem,5vw,4rem)] uppercase tracking-wide">Photo <span class="text-f1-red">Editor</span></h1>
        <p class="text-muted">Pick your overlays, capture or upload a photo, then save it as a draft or publish it.</p>
    </section>
    <section class="grid grid-cols-[200px_minmax(0,1fr)_240px] max-[1000px]:grid-cols-[170px_minmax(0,1fr)] max-[700px]:grid-cols-1">

        <aside class="flex flex-col gap-3 border-r border-line p-5 max-[700px]:order-2 max-[700px]:border-r-0 max-[700px]:border-t">
            <h3 class="font-racing text-sm uppercase tracking-wide text-white">Overlays</h3>
            <p class="text-xs text-muted">Pick at least one to capture or publish.</p>
            <div class="grid grid-cols-2 gap-2 max-[700px]:grid-cols-[repeat(auto-fill,minmax(70px,1fr))]">
                <?php foreach ($overlays as $overlay): ?>
                    <button type="button" class="overlay-option" data-overlay="<?= htmlspecialchars($overlay['id']) ?>" aria-pressed="false" title="<?= htmlspecialchars($overlay['id']) ?>">
                        <img src="<?= htmlspecialchars($overlay['url']) ?>" alt="<?= htmlspecialchars($overlay['id']) ?>">
                    </button>
                <?php endforeach; ?>
            </div>
            <?php if (!$overlays): ?>
                <p class="text-sm text-error">No overlays available.</p>
            <?php endif; ?>
            <div id="overlay-controls" class="flex flex-col gap-2 border-t border-line pt-3" hidden>
                <label for="overlay-size" class="font-racing text-xs uppercase tracking-wide text-white">Size</label>
                <input type="range" id="overlay-size" min="10" max="300" step="1" class="w-full accent-f1-red">
                <button type="button" id="overlay-reset" class="photo-action secondary !py-2">Reset position</button>
                <p class="text-xs text-muted">Drag the overlay to move it; drag its corner or scroll to resize.</p>
            </div>
        </aside>

        <div class="flex flex-col gap-4 bg-[linear-gradient(135deg,rgba(61,61,78,0.4),rgba(15,15,20,0.9))] p-6 text-muted max-[700px]:order-1 max-[480px]:p-4">
            <div class="photo-stage">
                <video id="camera-preview" autoplay playsinline muted class="h-full w-full [transform:scaleX(-1)] object-cover"></video>
                <img id="edit-preview" src="" alt="Captured photo" class="hidden h-full w-full object-cover">
                <div class="overlay-layer"></div>
                <div class="pointer-events-none absolute inset-0 flex items-end justify-start bg-[linear-gradient(to_top,rgba(15,15,20,0.6),transparent_40%,transparent)] p-[18px]">
                    <div id="camera-status" class="relative inline-flex items-center gap-2 rounded-full border border-line bg-asphalt/72 px-3 py-2 font-racing text-xs uppercase tracking-wide text-white before:h-2 before:w-2 before:rounded-full before:bg-start-green before:shadow-[0_0_10px_rgba(0,230,118,0.3)] before:content-['']">Starting camera...</div>
                </div>
            </div>

            <div id="camera-actions" class="flex flex-col gap-3">
                <button class="photo-action primary hidden" id="enable-camera-btn" type="button">Enable Camera</button>
                <button class="photo-action primary" id="capture-photo-btn" type="button" disabled>Capture Photo</button>
                <label class="photo-action secondary" for="upload-image">Upload Image</label>
                <input type="file" id="upload-image" accept="image/*" class="hidden">
            </div>

            <div id="edit-actions" class="hidden flex-col gap-3">
                <button class="photo-action primary" id="publish-photo-btn" type="button">Publish Photo</button>
                <button class="photo-action secondary" id="save-draft-btn" type="button">Save Draft</button>
                <button class="photo-action secondary" id="discard-btn" type="button">Discard</button>
            </div>
            <p id="editor-error" class="error-msg"></p>
        </div>

        <aside class="flex flex-col gap-4 border-l border-line p-5 max-[1000px]:col-span-2 max-[1000px]:border-l-0 max-[1000px]:border-t max-[700px]:order-3 max-[700px]:col-span-1">
            <h3 class="font-racing text-sm uppercase tracking-wide text-white">Previous Photos</h3>
            <div class="grid auto-rows-min grid-cols-[repeat(auto-fill,minmax(90px,1fr))] gap-2 overflow-y-auto lg:max-h-[560px]" id="thumbnails-container">
                <?php foreach ($photos as $photo): ?>
                    <?php require __DIR__ . '/partials/studio-thumb.php'; ?>
                <?php endforeach; ?>
            </div>
            <p id="thumbnails-empty" class="text-sm text-muted" <?= $photos ? 'hidden' : '' ?>>Your drafts and published photos will show up here.</p>
            <a href="/" class="photo-action secondary mt-auto">Back to Feed</a>
        </aside>
    </section>
</main>
</div>

<div class="modal-overlay" id="publish-modal">
    <div class="modal-card !max-w-[520px]">
        <?php require __DIR__ . '/modals/logout-link.php'; ?>
        <h2 class="modal-title">Publish photo</h2>
        <div class="photo-stage mb-4">
            <img id="publish-preview" src="" alt="Photo to publish" class="h-full w-full object-cover">
            <div class="overlay-layer"></div>
        </div>
        <p class="mb-2 font-racing text-xs uppercase tracking-wide text-white">Overlays</p>
        <div class="mb-1 grid grid-cols-[repeat(auto-fill,minmax(60px,1fr))] gap-2">
            <?php foreach ($overlays as $overlay): ?>
                <button type="button" class="overlay-option" data-overlay="<?= htmlspecialchars($overlay['id']) ?>" aria-pressed="false" title="<?= htmlspecialchars($overlay['id']) ?>">
                    <img src="<?= htmlspecialchars($overlay['url']) ?>" alt="<?= htmlspecialchars($overlay['id']) ?>">
                </button>
            <?php endforeach; ?>
        </div>
        <p class="error-msg" data-error="overlays"></p>
        <textarea id="publish-description" class="f1-input min-h-[90px] resize-y" maxlength="2200" placeholder="Write a caption..."></textarea>
        <p class="error-msg" data-error="description"></p>
        <p class="error-msg" data-error="error"></p>
        <div class="modal-auth-actions mt-4">
            <button type="button" class="modal-secondary-btn" id="publish-cancel-btn">Cancel</button>
            <button type="button" class="modal-primary-btn !w-auto flex-[1_1_120px]" id="publish-confirm-btn">Publish</button>
        </div>
    </div>
</div>

<template id="studio-thumb-template">
    <?php $photo = ['id' => 0, 'image_url' => '', 'source_url' => '', 'is_draft' => true, 'overlays' => []]; ?>
    <?php require __DIR__ . '/partials/studio-thumb.php'; ?>
</template>
<?php require __DIR__ . '/partials/footer-nav.php';?>
