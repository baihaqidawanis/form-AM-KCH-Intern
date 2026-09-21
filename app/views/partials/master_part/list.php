<?php
$data = $this->view_data;
$records = $data['records'] ?? array();
$machine_results = $data['machine_results'] ?? array();
$search = $data['search'] ?? '';
$area = $data['area'] ?? '';
$csrf_token = Csrf::$token;
$selected_machine = $this->selected_machine;
$machine_keys = Master_partController::$machine_keys;
$selected_label = isset($machine_keys[$selected_machine]) ? $machine_keys[$selected_machine] : '';
?>
<section class="page">
  <div class="card mb-4" style="margin-left:15px; margin-right:15px; border-radius: 14px; background: #FFFFFF;">
    <div class="card-body p-3">
      <div class="row align-items-center">
        <div class="col">
          <h4 class="record-title m-0 font-weight-bold" style="color: #1D1D1F;">Master Data Part Mesin</h4>
          <small class="text-muted">Detail per part (foto, Metode, Alat, Standard, Durasi, Pelaksanaan) yang tampil di form Add AM.</small>
        </div>
        <?php if ($selected_machine) { ?><div class="col-sm-5 text-right">
          <a class="btn btn-outline-secondary my-1 px-3 mr-1" style="border-radius: 8px; font-weight: 600;" href="<?php print_link('master_part'); ?>">
            <i class="fa fa-arrow-left mr-1"></i> Daftar Mesin
          </a>
          <a class="btn btn-success my-1 px-3" style="background: #009639; border-color: #009639; border-radius: 8px; font-weight: 600;" href="<?php print_link('master_part/add/' . $selected_machine) ?>">
            <i class="fa fa-plus mr-1"></i> Tambah Part
          </a>
        </div><?php } ?>
      </div>
    </div>
  </div>

  <div class="container-fluid">
    <?php $this::display_page_errors(); ?>
    <?php if (!$selected_machine) { ?><div class="card mb-4 master-part-filter-card">
      <div class="card-body p-4">
        <form method="get" action="<?php print_link('master_part'); ?>" class="row align-items-end">
          <input type="hidden" name="search_submit" value="1">
          <div class="col-md-4 mb-2"><label class="font-weight-bold mb-1">Area</label><select name="area" class="custom-select"><option value="">Semua Area</option><?php foreach (array('compounding' => 'Compounding', 'filling' => 'Filling', 'kemas' => 'Kemas', 'wrapping dan pack cartoning' => 'Wrapping dan Pack Cartoning') as $value => $label) { ?><option value="<?php echo $value; ?>" <?php echo $area === $value ? 'selected' : ''; ?>><?php echo $label; ?></option><?php } ?></select></div>
          <div class="col-md-5 mb-2"><label class="font-weight-bold mb-1">Cari template mesin</label><input type="search" name="search" value="<?php echo htmlspecialchars($search); ?>" class="form-control" placeholder="Contoh: SIG, Ilapak, FBD"></div>
          <div class="col-md-3 mb-2"><button class="btn btn-primary btn-block" type="submit"><i class="fa fa-search mr-1"></i> Search</button></div>
        </form>
        <div class="d-flex align-items-center justify-content-between mt-4 mb-3"><div><h5 class="mb-1 font-weight-bold" style="color:#1D1D1F;">Template Mesin</h5><small class="text-muted">Pilih mesin untuk mengelola detail part checklist.</small></div><span class="badge badge-light border px-3 py-2"><?php echo count($machine_results); ?> mesin</span></div>
        <div class="row">
          <?php if (empty($machine_results)) { ?><div class="col-12"><div class="text-center py-4 text-muted border rounded">Tidak ada template mesin yang sesuai.</div></div><?php } ?>
          <?php foreach ($machine_results as $machine) { ?>
            <div class="col-md-6 col-xl-4 mb-3">
              <a class="master-part-machine-card d-block h-100" href="<?php print_link('master_part/index/' . $machine['key']); ?>">
                <div class="d-flex align-items-center justify-content-between mb-3"><span class="master-part-machine-icon"><i class="fa fa-cogs"></i></span><span class="badge badge-light border"><?php echo htmlspecialchars(ucwords($machine['area'])); ?></span></div>
                <strong class="d-block mb-1"><?php echo htmlspecialchars($machine['label']); ?></strong>
                <small class="text-muted">Kelola part, urutan, foto, dan jadwal shift</small>
                <span class="master-part-machine-action mt-3">Kelola Part <i class="fa fa-arrow-right ml-1"></i></span>
              </a>
            </div>
          <?php } ?>
        </div>
      </div>
    </div>
    <?php } ?>

    <?php if ($selected_machine) { ?><div class="card mb-4" style="border-radius: 14px; background: #FFFFFF;">
      <div class="card-body p-3">

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
                <th style="width:65px;" class="text-center">Foto</th>
                <th style="width:90px;" class="text-nowrap">Mesin</th>
                <th style="min-width:180px;" class="text-nowrap">Field Name</th>
                <th style="min-width:160px;">Label</th>
                <th style="min-width:160px;">Section</th>
                <th style="width:65px;" class="text-center">Urutan</th>
                <th style="width:100px;" class="text-center">Aksi</th>
              </tr>
            </thead>
            <tbody id="master-part-rows">
              <?php if (empty($records)) { ?>
                <tr><td colspan="8" class="text-center py-4 text-muted">Belum ada data part untuk mesin ini.</td></tr>
              <?php } foreach ($records as $r) { ?>
                <tr draggable="true" class="master-part-row" data-id="<?php echo $r['id']; ?>">
                  <td class="text-center text-muted" style="cursor:grab;" title="Tarik untuk mengubah urutan"><i class="fa fa-bars"></i></td>
                  <td style="width:65px;" class="align-middle text-center">
                    <?php if (!empty($r['image_path'])) { ?>
                      <img src="<?php print_link($r['image_path']); ?>" style="max-width:50px;max-height:50px;object-fit:cover;border-radius:6px;border:1px solid #E5E5EA;" onerror="this.style.display='none';">
                    <?php } ?>
                  </td>
                  <td class="align-middle font-weight-bold text-nowrap"><?php echo isset($machine_keys[$r['machine_key']]) ? $machine_keys[$r['machine_key']] : $r['machine_key']; ?></td>
                  <td class="align-middle field-name-cell"><code><?php echo htmlspecialchars($r['field_name']); ?></code></td>
                  <td class="align-middle font-weight-500"><?php echo htmlspecialchars($r['label']); ?></td>
                  <td class="align-middle"><span class="badge badge-light border text-secondary text-nowrap"><?php echo htmlspecialchars($r['section']); ?></span></td>
                  <td class="urutan-cell"><?php echo $r['urutan']; ?></td>
                  <td class="align-middle text-center text-nowrap">
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
    <?php } ?>
  </div>
</section>
<style>
  .master-part-filter-card { border-radius: 16px; background: linear-gradient(135deg, #FFFFFF 0%, #F7FBF7 100%); }
  .master-part-machine-card { padding: 20px; border: 1px solid #E2E8E3; border-radius: 14px; background: #FFFFFF; color: #1D1D1F; text-decoration: none !important; box-shadow: 0 2px 7px rgba(0,0,0,.035); transition: transform .16s ease, box-shadow .16s ease, border-color .16s ease; }
  .master-part-machine-card:hover { transform: translateY(-2px); border-color: #86BD40; box-shadow: 0 10px 24px rgba(0, 150, 57, .12); color: #1D1D1F; }
  .master-part-machine-icon { width: 38px; height: 38px; display: inline-flex; align-items: center; justify-content: center; border-radius: 11px; background: #EAF6E8; color: #009639; }
  .master-part-machine-action { display: block; color: #008435; font-size: .85rem; font-weight: 700; }
  .master-part-row.dragging { opacity: .4; background: #F0F8EC; }
  .master-part-row.drop-target-above { box-shadow: inset 0 3px 0 0 #009639; }
  .master-part-row.drop-target-below { box-shadow: inset 0 -3px 0 0 #009639; }
  .urutan-cell { text-align: center; font-weight: bold; vertical-align: middle !important; }

  #master-part-table {
    min-width: 860px !important;
  }
  #master-part-table th, #master-part-table td {
    vertical-align: middle !important;
  }
  #master-part-table td.field-name-cell code {
    background: #F1F5F9;
    color: #0F172A;
    border: 1px solid #E2E8F0;
    padding: 3px 8px;
    border-radius: 6px;
    font-size: 0.83rem;
    font-weight: 600;
    font-family: SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
    white-space: nowrap !important;
    display: inline-block;
  }
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
