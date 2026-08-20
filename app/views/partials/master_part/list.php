<?php
$data = $this->view_data;
$records = $data['records'];
$csrf_token = Csrf::$token;
$selected_machine = $this->selected_machine;
$machine_keys = Master_partController::$machine_keys;
$selected_label = isset($machine_keys[$selected_machine]) ? $machine_keys[$selected_machine] : $selected_machine;
?>
<section class="page">
  <div class="bg-light p-3 mb-3">
    <div class="container-fluid">
      <div class="row">
        <div class="col">
          <h4 class="record-title">Master Data Part Mesin</h4>
          <small class="text-muted">Detail per part (foto, Metode, Alat, Standard, Durasi, Pelaksanaan) yang tampil di form Add AM. Baru Cosmec yang formnya sudah baca dari sini.</small>
        </div>
        <div class="col-sm-3">
          <a class="btn btn-primary my-1" href="<?php print_link('master_part/add/' . $selected_machine) ?>"><i class="fa fa-plus"></i> Tambah Part</a>
        </div>
      </div>
    </div>
  </div>
  <div class="container-fluid">
    <?php $this::display_page_errors(); ?>
    <div class="bg-light p-3">
      <div class="mb-3">
        <label class="mr-2">Filter mesin:</label>
        <?php foreach ($machine_keys as $key => $label) { ?>
          <a class="btn btn-sm <?php echo ($selected_machine === $key) ? 'btn-primary' : 'btn-outline-secondary'; ?>" href="<?php print_link('master_part/index/' . $key) ?>"><?php echo $label; ?></a>
        <?php } ?>
      </div>
      <div class="d-flex align-items-center mb-2">
        <h5 class="m-0"><?php echo $selected_label; ?></h5>
        <small class="text-muted ml-3"><i class="fa fa-arrows"></i> Tarik baris (drag) buat ubah urutan tampil di form Add AM &mdash; otomatis tersimpan.</small>
        <span id="reorder-status" class="ml-3 small"></span>
      </div>
      <div class="table-responsive">
        <table class="table table-bordered table-sm table-hover" id="master-part-table">
          <thead class="thead-light">
            <tr>
              <th style="width:36px;"></th>
              <th>Foto</th>
              <th>Mesin</th>
              <th>Field Name</th>
              <th>Label</th>
              <th>Section</th>
              <th style="width:70px;">Urutan</th>
              <th style="width:100px;">Aksi</th>
            </tr>
          </thead>
          <tbody id="master-part-rows">
            <?php if (empty($records)) { ?>
              <tr><td colspan="8" class="text-center text-muted">Belum ada data</td></tr>
            <?php } foreach ($records as $r) { ?>
              <tr draggable="true" class="master-part-row" data-id="<?php echo $r['id']; ?>">
                <td class="text-center text-muted" style="cursor:grab;" title="Tarik untuk mengubah urutan"><i class="fa fa-bars"></i></td>
                <td style="width:70px;">
                  <?php if (!empty($r['image_path'])) { ?>
                    <img src="<?php print_link($r['image_path']); ?>" style="max-width:60px;max-height:60px;object-fit:cover;" onerror="this.style.display='none';">
                  <?php } ?>
                </td>
                <td><?php echo isset($machine_keys[$r['machine_key']]) ? $machine_keys[$r['machine_key']] : $r['machine_key']; ?></td>
                <td><code><?php echo $r['field_name']; ?></code></td>
                <td><?php echo $r['label']; ?></td>
                <td><small><?php echo $r['section']; ?></small></td>
                <td class="urutan-cell"><?php echo $r['urutan']; ?></td>
                <td>
                  <a class="btn btn-sm btn-outline-primary" href="<?php print_link('master_part/edit/' . $r['id']) ?>"><i class="fa fa-edit"></i></a>
                  <a class="btn btn-sm btn-outline-danger" href="<?php print_link('master_part/delete/' . $r['id'] . '?csrf_token=' . $csrf_token) ?>" onclick="return confirm('Hapus part &quot;<?php echo htmlspecialchars($r['label']); ?>&quot;? Bagian ini bakal hilang dari form Add AM.');"><i class="fa fa-trash"></i></a>
                </td>
              </tr>
            <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</section>
<style>
  .master-part-row.dragging { opacity: .4; }
  .master-part-row.drop-target-above { box-shadow: inset 0 3px 0 0 #007bff; }
  .master-part-row.drop-target-below { box-shadow: inset 0 -3px 0 0 #007bff; }
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
