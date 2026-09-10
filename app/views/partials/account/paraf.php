<?php
$data = $this->view_data ?: array();
$paraf_image = $data['paraf_image'] ?? null;
$has_valid_paraf = is_valid_base64_png_data_uri($paraf_image);
?>
<div class="p-3 bg-white rounded">
    <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
        <div>
            <h5 class="mb-1 text-primary"><i class="fa fa-pencil-square-o mr-1"></i> Paraf Digital Akun</h5>
            <small class="text-muted">Digunakan otomatis untuk mengisi baris paraf pelaksana pada Check Sheet Harian AM.</small>
        </div>
    </div>

    <div id="paraf-alert-msg"></div>

    <div class="row">
        <!-- Kolom Preview Status Saat Ini -->
        <div class="col-md-5 mb-4">
            <div class="card bg-light border-0 shadow-none h-100">
                <div class="card-body text-center d-flex flex-column justify-content-center">
                    <h6 class="text-muted text-uppercase mb-3" style="font-size: 11px; letter-spacing: 0.5px;">Paraf Aktif Saat Ini</h6>
                    <div class="p-3 bg-white border rounded mb-3 mx-auto d-flex align-items-center justify-content-center" style="width: 240px; height: 110px; border-style: dashed !important;">
                        <?php if ($has_valid_paraf) { ?>
                            <img id="current-paraf-preview" src="<?php echo htmlspecialchars($paraf_image, ENT_QUOTES, 'UTF-8'); ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;" alt="Paraf Digital">
                        <?php } else { ?>
                            <div id="current-paraf-preview" class="alert alert-light border text-muted small">
                                <i class="fa fa-info-circle mr-1"></i> Belum ada gambar paraf.<br>Check sheet harian Anda otomatis menggunakan identitas sistem: <strong class="badge badge-primary">ID: <?php echo intval($data['id_user'] ?? USER_ID); ?></strong>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Canvas Gambar Baru -->
        <div class="col-md-7 mb-4">
            <form id="form-save-paraf" onsubmit="return false;">
                <div class="form-group mb-3">
                    <label class="font-weight-bold" style="font-size: 13px;">
                        Gambar Paraf Baru (Gunakan Touchscreen atau Mouse):
                    </label>
                    <div style="border: 2px dashed #007bff; border-radius: 6px; background: #fafcff; touch-action: none; position: relative;">
                        <canvas id="paraf-canvas" width="380" height="150" style="width: 100%; height: 150px; cursor: crosshair; display: block;"></canvas>
                        <div id="canvas-placeholder" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); color: #aaa; pointer-events: none; font-size: 12px;">
                            <i class="fa fa-hand-pointer-o mr-1"></i> Gambar paraf Anda di kotak ini
                        </div>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <button type="button" id="btn-clear-canvas" class="btn btn-sm btn-outline-secondary">
                            <i class="fa fa-eraser mr-1"></i> Hapus / Reset Gambar
                        </button>
                        <small class="text-muted">Goresan tinta biru resmi</small>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="button" id="btn-save-paraf" class="btn btn-primary px-4"><i class="fa fa-check mr-1"></i> Simpan Paraf Digital</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var canvas = document.getElementById('paraf-canvas');
    if (!canvas) return;

    var ctx = canvas.getContext('2d');
    var isDrawing = false;
    var hasDrawn = false;
    var placeholder = document.getElementById('canvas-placeholder');

    // Pen style
    ctx.lineWidth = 2.5;
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.strokeStyle = '#003366'; // Corporate blue signature ink

    function getPos(e) {
        var rect = canvas.getBoundingClientRect();
        var scaleX = canvas.width / rect.width;
        var scaleY = canvas.height / rect.height;
        var clientX = e.clientX;
        var clientY = e.clientY;
        if (e.touches && e.touches.length > 0) {
            clientX = e.touches[0].clientX;
            clientY = e.touches[0].clientY;
        }
        return {
            x: (clientX - rect.left) * scaleX,
            y: (clientY - rect.top) * scaleY
        };
    }

    function startDraw(e) {
        e.preventDefault();
        isDrawing = true;
        hasDrawn = true;
        if (placeholder) placeholder.style.display = 'none';
        var pos = getPos(e);
        ctx.beginPath();
        ctx.moveTo(pos.x, pos.y);
    }

    function draw(e) {
        if (!isDrawing) return;
        e.preventDefault();
        var pos = getPos(e);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
    }

    function stopDraw(e) {
        if (!isDrawing) return;
        e.preventDefault();
        isDrawing = false;
    }

    // Mouse events
    canvas.addEventListener('mousedown', startDraw);
    canvas.addEventListener('mousemove', draw);
    window.addEventListener('mouseup', stopDraw);

    // Touch events for mobile/tablet
    canvas.addEventListener('touchstart', startDraw, { passive: false });
    canvas.addEventListener('touchmove', draw, { passive: false });
    window.addEventListener('touchend', stopDraw, { passive: false });

    // Clear canvas
    document.getElementById('btn-clear-canvas').addEventListener('click', function() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        hasDrawn = false;
        if (placeholder) placeholder.style.display = 'block';
    });

    // Save Paraf via AJAX
    document.getElementById('btn-save-paraf').addEventListener('click', function() {
        var btn = this;
        var parafData = '';

		if (!hasDrawn) {
			alert('Silakan gambar paraf terlebih dahulu.');
			return;
        }
		parafData = canvas.toDataURL('image/png');

        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-1"></i> Menyimpan...';

        var formData = new FormData();
		formData.append('csrf_token', <?php echo json_encode(Csrf::$token); ?>);
		formData.append('paraf_image', parafData);

        fetch('<?php print_link("account/save_paraf?csrf_token=" . urlencode(Csrf::$token)); ?>', {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-check mr-1"></i> Simpan Paraf Digital';

            var alertBox = document.getElementById('paraf-alert-msg');
            if (data.success) {
                alertBox.innerHTML = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                    '<i class="fa fa-check-circle mr-1"></i> ' + data.message +
                    '<button type="button" class="close" data-dismiss="alert">&times;</button></div>';

                // Update preview
                if (hasDrawn) {
                    var previewContainer = document.getElementById('current-paraf-preview');
                    if (previewContainer.tagName === 'IMG') {
                        previewContainer.src = parafData;
                    } else {
                        var newImg = document.createElement('img');
                        newImg.id = 'current-paraf-preview';
                        newImg.src = parafData;
                        newImg.style.maxWidth = '100%';
                        newImg.style.maxHeight = '100%';
                        newImg.style.objectFit = 'contain';
                        previewContainer.parentNode.replaceChild(newImg, previewContainer);
                    }
                }
            } else {
                alertBox.innerHTML = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                    '<i class="fa fa-exclamation-triangle mr-1"></i> ' + (data.message || 'Gagal menyimpan.') +
                    '<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-check mr-1"></i> Simpan Paraf Digital';
            var alertBox = document.getElementById('paraf-alert-msg');
            alertBox.innerHTML = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                '<i class="fa fa-exclamation-triangle mr-1"></i> Terjadi kesalahan koneksi.' +
                '<button type="button" class="close" data-dismiss="alert">&times;</button></div>';
        });
    });
});
</script>
