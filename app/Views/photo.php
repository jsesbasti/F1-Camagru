<?php require __DIR__ . '/partials/header.php';?>
<div class="flex min-h-screen items-center justify-center bg-[radial-gradient(circle_at_top,rgba(225,6,0,0.22),transparent_35%)] p-6 pt-20 pb-24">
<main class="w-[min(92vw,880px)] overflow-hidden rounded-2xl border border-line border-t-4 border-t-f1-red bg-carbon shadow-[0_24px_80px_rgba(0,0,0,0.45)]">
    <section class="border-b border-line bg-[linear-gradient(135deg,rgba(31,31,39,0.96),rgba(15,15,20,0.96))] p-6">
        <h1 class="mb-2 font-racing text-[clamp(2rem,5vw,4rem)] uppercase tracking-wide">Photo <span class="text-f1-red">Editor</span></h1>
        <p class="text-muted">Capture or upload photos and customize them with effects before publishing.</p>
    </section>
    <section class="grid grid-cols-[1.2fr_0.8fr] max-[780px]:grid-cols-1">
        <div class="flex min-h-[420px] flex-col gap-4 overflow-y-auto bg-[linear-gradient(135deg,rgba(61,61,78,0.4),rgba(15,15,20,0.9))] p-6 text-muted">
            <!-- Modo cámara -->
            <div id="camera-mode" class="photo-mode active">
                <div class="relative aspect-square w-full max-w-full flex-shrink-0 overflow-hidden rounded-2xl border border-line bg-[linear-gradient(135deg,rgba(31,31,39,0.85),rgba(15,15,20,0.95))] shadow-[inset_0_0_0_1px_rgba(255,255,255,0.03)]">
                    <video id="camera-preview" autoplay playsinline muted class="block h-full w-full [transform:scaleX(-1)] bg-[linear-gradient(135deg,rgba(61,61,78,0.35),rgba(15,15,20,0.9))] object-cover"></video>
                    <div class="pointer-events-none absolute inset-0 flex items-end justify-start bg-[linear-gradient(to_top,rgba(15,15,20,0.6),transparent_40%,transparent)] p-[18px]">
                        <div id="camera-status" class="relative inline-flex items-center gap-2 rounded-full border border-line bg-asphalt/72 px-3 py-2 font-racing text-xs uppercase tracking-wide text-white before:h-2 before:w-2 before:rounded-full before:bg-start-green before:shadow-[0_0_10px_rgba(0,230,118,0.3)] before:content-['']">Starting camera...</div>
                    </div>
                </div>
                <div class="flex flex-col gap-3">
                    <h3 class="font-racing text-sm uppercase tracking-wide text-white">Available Effects</h3>
                    <div class="grid grid-cols-[repeat(auto-fill,minmax(60px,1fr))] gap-2" id="effects-container">
                        <!-- Effects will be loaded here dynamically -->
                    </div>
                </div>
                <button class="photo-action primary" id="capture-photo-btn" type="button">Capture Photo</button>
                <label class="photo-action secondary" for="upload-image" id="upload-label">Upload Image</label>
                <input type="file" id="upload-image" name="image" accept="image/*" class="hidden">
            </div>

            <!-- Modo edición -->
            <div id="edit-mode" class="photo-mode">
                <div class="relative aspect-square w-full max-w-full flex-shrink-0 overflow-hidden rounded-2xl border border-line bg-[linear-gradient(135deg,rgba(31,31,39,0.85),rgba(15,15,20,0.95))] shadow-[inset_0_0_0_1px_rgba(255,255,255,0.03)]">
                    <img id="edit-preview" src="" alt="Imagen a editar" class="block h-full w-full bg-[linear-gradient(135deg,rgba(61,61,78,0.35),rgba(15,15,20,0.9))] object-cover">
                </div>
                <div class="flex flex-col gap-3">
                    <div class="flex flex-col gap-3">
                        <h3 class="font-racing text-sm uppercase tracking-wide text-white">Available Effects</h3>
                        <div class="grid grid-cols-[repeat(auto-fill,minmax(60px,1fr))] gap-2" id="edit-effects-container">
                            <!-- Effects will be loaded here dynamically -->
                        </div>
                    </div>
                </div>
                <button class="photo-action primary" id="publish-photo-btn" type="button">Publish Photo</button>
                <button class="photo-action secondary" id="cancel-edit-btn" type="button">Cancel</button>
            </div>
        </div>
        <aside class="flex flex-col gap-4 overflow-y-auto border-l border-line p-6 max-[780px]:border-l-0 max-[780px]:border-t">
            <h3 class="font-racing text-sm uppercase tracking-wide text-white">Previous Photos</h3>
            <div class="grid flex-grow grid-cols-[repeat(auto-fill,minmax(80px,1fr))] gap-2 overflow-y-auto" id="thumbnails-container">
                <!-- Thumbnails will be loaded here dynamically -->
            </div>
            <button class="photo-action primary" id="feed-btn" type="button">Back to Feed</button>
        </aside>
    </section>
</main>
</div>
<?php require __DIR__ . '/partials/footer-nav.php';?>
