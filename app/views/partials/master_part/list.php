<?php
$data = $this->view_data;
$records = $data['records'];
$csrf_token = Csrf::$token;
$selected_machine = $this->selected_machine;
$machine_keys = Master_partController::$machine_keys;
$selected_label = isset($machine_keys[$selected_machine]) ? $machine_keys[$selected_machine] : $selected_machine;
?>
<section class="page">
  <div class="card border-0 shadow-sm mb-3" style="border-radius: 14px; background: #FFFFFF;">
    <div class="card-body p-3">
      <div class="row align-items-center">
        <div class="col">
          <h4 class="record-title m-0 font-weight-bold" style="color: #1D1D1F;">Master Data Part Mesin</h4>
          <small class="text-muted">Detail per part (foto, Metode, Alat, Standard, Durasi, Pelaksanaan) yang tampil di form Add AM.</small>
        </div>
        <div class="col-sm-3 text-right">
          <a class="btn btn-success my-1 px-3" style="background: #009639; border-color: #009639; border-radius: 8px; font-weight: 600;" href="<?php print_link('master_part/add/' . $selected_machine) ?>">
            <i class="fa fa-plus mr-1"></i> Tambah Part
          </a>
        </div>
      </div>
    </div>
  </div>

  <div class="container-fluid p-0">
    <?php $this::display_page_errors(); ?>
    <div class="card border-0 shadow-sm" style="border-radius: 14px; background: #FFFFFF;">
      <div class="card-body p-3">
        <div class="mb-3 d-flex flex-wrap align-items-center" style="gap: 8px 6px;">
          <label class="mb-1 mr-2 font-weight-bold" style="color: #48484A; font-size: 0.88rem;">Filter mesin:</label>
          <div class="d-flex flex-wrap align-items-center" style="gap: 8px 6px;">
            <?php foreach ($machine_keys as $key => $label) { ?>
              <a class="btn btn-sm mb-1 am-filter-pill <?php echo ($selected_machine === $key) ? 'active' : ''; ?>" href="<?php print_link('master_part/index/' . $key) ?>"><?php echo $label; ?></a>
            <?php } ?>
          </div>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
          <div class="d-flex align-items-center">
            <h5 class="m-0 font-weight-bold" style="color: #1D1D1F;"><?php echo $selected_label; ?></h5>
            <small class="text-muted ml-3"><i class="fa fa-arrows text-success mr-1"></i> Tarik baris (drag) buat ubah urutan tampil di form Add AM &mdash; otomatis tersimpan.</small>
          </div>
          <span id="reorder-status" class="ml-3 small font-weight-bold"></span>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered table-sm table-hover mb-0" id="master-part-table">
            <thead class="thead-light">
              <tr>
                <th style="width:36px;" class="text-center"></th>
                <th>Foto</th>
                <th>Mesin</th>
                <th>Field Name</th>
                <th>Label</th>
                <th>Section</th>
                <th style="width:70px;" class="text-center">Urutan</th>
                <th style="width:110px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody id="master-part-rows">
              <?php if (empty($records)) { ?>
                <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada data part untuk mesin ini.</td></tr>
              <?php } foreach ($records as $r) { ?>
                <tr draggable="true" class="master-part-row" data-id="<?php echo $r['id']; ?>">
                  <td class="text-center text-muted" style="cursor:grab;" title="Tarik untuk mengubah urutan"><i class="fa fa-bars"></i></td>
                  <td style="width:70px;" class="align-middle text-center">
                    <?php if (!empty($r['image_path'])) { ?>
                      <img src="<?php print_link($r['image_path']); ?>" style="max-width:52px;max-height:52px;object-fit:cover;border-radius:6px;border:1px solid #E5E5EA;" onerror="this.style.display='none';">
                    <?php } ?>
                  </td>
                  <td class="align-middle font-weight-bold"><?php echo isset($machine_keys[$r['machine_key']]) ? $machine_keys[$r['machine_key']] : $r['machine_key']; ?></td>
                  <td class="align-middle"><code><?php echo htmlspecialchars($r['field_name']); ?></code></td>
                  <td class="align-middle"><?php echo htmlspecialchars($r['label']); ?></td>
                  <td class="align-middle"><span class="badge badge-light border text-secondary"><?php echo htmlspecialchars($r['section']); ?></span></td>
                  <td class="urutan-cell align-middle text-center font-weight-bold"><?php echo $r['urutan']; ?></td>
                  <td class="align-middle text-center">
                    <a class="btn btn-sm btn-outline-primary py-1 px-2" href="<?php print_link('master_part/edit/' . $r['id']) ?>" title="Edit Part"><i class="fa fa-edit"></i></a>
  <?php if (empty($r['taken_out_at'])) { ?><a class="btn btn-sm btn-outline-warning py-1 px-2" href="<?php print_link('master_part/takeout/' . $r['id']); ?>" title="Takeout part tanpa menghapus riwayat"><i class="fa fa-sign-out"></i></a><?php } else { ?>
    <span class="badge badge-secondary" title="Di-takeout <?php echo htmlspecialchars($r['taken_out_at']); ?><?php echo !empty($r['takeout_reason']) ? ': ' . htmlspecialchars($r['takeout_reason']) : ''; ?>">Taken out</span>
    <a class="btn btn-sm btn-outline-success py-1 px-2"
      href="<?php print_link('master_part/reactivate/' . $r['id'] . '?csrf_token=' . Csrf::$token); ?>"
      onclick="return confirm('Aktifkan kembali part ini agar muncul di form AM baru?');"
      title="Aktifkan kembali part ke form AM">
      <i class="fa fa-undo"></i>
    </a>
  <?php } ?>
                  </td>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
<style>
  .am-filter-pill {
    background: #FFFFFF !important;
    border: 1px solid #D2D2D7 !important;
    color: #48484A !important;
    font-weight: 500;
    border-radius: 999px;
    padding: 5px 14px;
    font-size: 0.8rem;
    transition: all 0.16s ease;
  }
  .am-filter-pill:hover {
    background: #F0F8EC !important;
    border-color: #86BD40 !important;
    color: #009639 !important;
  }
  .am-filter-pill.active {
    background: #009639 !important;
    border-color: #009639 !important;
    color: #FFFFFF !important;
    font-weight: 600;
    box-shadow: 0 2px 6px rgba(0, 150, 57, 0.28);
  }
  .master-part-row.dragging { opacity: .4; background: #F0F8EC; }
  .master-part-row.drop-target-above { box-shadow: inset 0 3px 0 0 #009639; }
  .master-part-row.drop-target-below { box-shadow: inset 0 -3px 0 0 #009639; }
</style>
<script>
(function () {
  // Drag-and-drop urutan part -- pakai HTML5 Drag & Drop API bawaan browser,
  // sengaja TANPA jQuery UI/library tambahan (project ini gak punya bundler &
  // dipakai di jaringan internal yang belum tentu bisa ambil CDN).
  var tbody = document.getElementById('master-part-rows');
  if (!tbody) { return; }
  var statusEl = document.getElementById('reorder-status');
  var dragged = null;

  function clearMarkers() {
    tbody.querySelectorAll('.master-part-row').forEach(function (row) {
      row.classList.remove('drop-target-above', 'drop-target-below');
    });
  }

  function setStatus(text, cssClass) {
    if (!statusEl) { return; }
    statusEl.className = 'ml-3 small ' + (cssClass || '');
    statusEl.textContent = text;
  }

  // Baris pertama yang titik tengahnya ADA DI BAWAH kursor -- itu jadi patokan
  // "sisipkan sebelum baris ini". Null artinya taruh paling bawah.
  function rowAfterCursor(y) {
    var rows = Array.prototype.slice.call(tbody.querySelectorAll('.master-part-row:not(.dragging)'));
    for (var i = 0; i < rows.length; i++) {
      var box = rows[i].getBoundingClientRect();
      if (y < box.top + box.height / 2) { return rows[i]; }
    }
    return null;
  }

  tbody.addEventListener('dragstart', function (e) {
    var row = e.target.closest ? e.target.closest('.master-part-row') : null;
    if (!row) { return; }
    dragged = row;
    row.classList.add('dragging');
    e.dataTransfer.effectAllowed = 'move';
    // Firefox butuh setData supaya drag-nya kebaca sama sekali.
    try { e.dataTransfer.setData('text/plain', row.getAttribute('data-id')); } catch (err) {}
  });

  tbody.addEventListener('dragover', function (e) {
    if (!dragged) { return; }
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    clearMarkers();
    var target = rowAfterCursor(e.clientY);
    if (target) { target.classList.add('drop-target-above'); }
    else {
      var rows = tbody.querySelectorAll('.master-part-row:not(.dragging)');
      if (rows.length) { rows[rows.length - 1].classList.add('drop-target-below'); }
    }
  });

  tbody.addEventListener('drop', function (e) {
    if (!dragged) { return; }
    e.preventDefault();
    clearMarkers();
    var target = rowAfterCursor(e.clientY);
    if (target) { tbody.insertBefore(dragged, target); }
    else { tbody.appendChild(dragged); }
  });

  tbody.addEventListener('dragend', function () {
    if (!dragged) { return; }
    dragged.classList.remove('dragging');
    dragged = null;
    clearMarkers();
    saveOrder();
  });

  function saveOrder() {
    var rows = Array.prototype.slice.call(tbody.querySelectorAll('.master-part-row'));
    var ids = rows.map(function (row) { return row.getAttribute('data-id'); });
    if (!ids.length) { return; }
    // Nomor "Urutan" di layar langsung disesuaikan biar nyambung sama posisi
    // baru; angka final tetap yang dari server abis reload.
    rows.forEach(function (row, i) {
      var cell = row.querySelector('.urutan-cell');
      if (cell) { cell.textContent = i + 1; }
    });
    setStatus('Menyimpan urutan ...', 'text-muted');
    $.post('<?php print_link("master_part/reorder"); ?>', {
      ids: ids.join(','),
      csrf_token: '<?php echo $csrf_token; ?>'
    }).done(function (res) {
      if (res && res.success) { setStatus('Urutan tersimpan', 'text-success'); }
      else { setStatus((res && res.message) ? res.message : 'Gagal menyimpan urutan', 'text-danger'); }
    }).fail(function () {
      setStatus('Gagal menyimpan urutan (koneksi bermasalah)', 'text-danger');
    });
  }
})();
</script>
