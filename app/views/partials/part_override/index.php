<?php
$data = $this->view_data;
$modules = $data['modules'] ?? array();
$units = $data['units'] ?? array();
$units_by_module = $data['units_by_module'] ?? array();
$parts = $data['parts'] ?? array();
$machine_key = $data['machine_key'] ?? '';
$mesin_id = intval($data['mesin_id'] ?? 0);
$selected_unit = $data['selected_unit'] ?? null;
$search = $data['search'] ?? '';
$override_records = $data['override_records'] ?? array();
$csrf_token = Csrf::$token;
?>
<section class="page">
  <div class="bg-light p-3 mb-3">
    <div class="container-fluid">
      <h4 class="record-title mb-1">Override Part per Unit</h4>
      <p class="text-muted mb-0">Atur perbedaan part antarunit ketika satu template Form AM dipakai oleh beberapa unit fisik. Modul satu unit diatur langsung melalui Master Data Part. Form lama tetap memakai snapshot saat disubmit.</p>
    </div>
  </div>
  <div class="container-fluid">
    <?php $this::display_page_errors(); ?>
    <div class="bg-light p-3 mb-3">
      <form method="get" action="<?php print_link('part_override'); ?>">
        <div class="form-row align-items-end">
          <div class="col-md-4 form-group">
            <label>Modul Mesin</label>
            <select required id="override-machine-key" name="machine_key" class="custom-select">
              <option value="">Pilih modul ...</option>
              <?php foreach ($modules as $key => $module) { ?><option value="<?php echo htmlspecialchars($key); ?>" <?php echo $machine_key === $key ? 'selected' : ''; ?>><?php echo htmlspecialchars($module['label']); ?></option><?php } ?>
            </select>
          </div>
          <div class="col-md-4 form-group">
            <label>Unit Fisik</label>
            <select id="override-unit-id" name="mesin_id" class="custom-select" <?php echo $machine_key === '' ? 'disabled' : ''; ?>>
              <option value="">Pilih unit ...</option>
              <?php foreach ($units as $unit) { ?><option value="<?php echo intval($unit['id']); ?>" <?php echo $mesin_id === intval($unit['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($unit['nama_mesin']); ?><?php echo !empty($unit['nomor_seri']) ? ' - ' . htmlspecialchars($unit['nomor_seri']) : ''; ?></option><?php } ?>
            </select>
          </div>
          <div class="col-md-4 form-group">
            <label>Cari Part</label>
            <input type="text" name="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Nama atau field part">
          </div>
        </div>
        <div class="text-right"><button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Tampilkan</button></div>
      </form>
      <?php if ($machine_key !== '' && empty($units)) { ?><div class="alert alert-warning mt-3 mb-0">Belum ada unit fisik yang dipetakan ke modul ini.</div><?php } ?>
      <?php if ($machine_key !== '' && !$selected_unit && !empty($units)) { ?><div class="small text-muted mt-3">Pilih unit fisik lalu tekan Tampilkan untuk mengatur override.</div><?php } ?>
    </div>

    <div class="bg-light p-3 mb-3">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h5 class="mb-1"><?php echo $selected_unit ? 'Override untuk ' . htmlspecialchars($selected_unit['nama_mesin']) : 'Daftar Override Part per Unit'; ?></h5>
          <div class="text-muted small"><?php echo $selected_unit ? 'Override yang sudah disetel untuk unit fisik yang dipilih.' : 'Seluruh override part yang sudah disetel pada modul dengan lebih dari satu unit fisik.'; ?></div>
        </div>
        <span class="badge badge-info"><?php echo count($override_records); ?> override</span>
      </div>
      <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white mb-0">
          <thead><tr><th>Modul</th><th>Unit Fisik</th><th>Part</th><th>Status</th><th>Durasi Khusus</th><th>Terakhir Diubah</th><th>Aksi</th></tr></thead>
          <tbody>
          <?php if (empty($override_records)) { ?><tr><td colspan="7" class="text-center text-muted">Belum ada override yang sesuai dengan filter. Part tetap memakai konfigurasi default.</td></tr><?php } ?>
          <?php foreach ($override_records as $override) {
            $is_applicable = in_array($override['is_applicable'] ?? null, array(true, 1, '1', 't', 'true'), true);
            $override_url = 'part_override?' . http_build_query(array('machine_key' => $override['machine_key'], 'mesin_id' => intval($override['mesin_id'])));
          ?>
            <tr>
              <td><?php echo htmlspecialchars(Master_partController::$machine_keys[$override['machine_key']] ?? $override['machine_key']); ?></td>
              <td><strong><?php echo htmlspecialchars($override['nama_mesin']); ?></strong><?php if (!empty($override['nomor_seri'])) { ?><div class="small text-muted"><?php echo htmlspecialchars($override['nomor_seri']); ?></div><?php } ?></td>
              <td><strong><?php echo htmlspecialchars($override['label']); ?></strong><div class="small text-muted"><?php echo htmlspecialchars($override['field_name']); ?><?php echo !empty($override['section']) ? ' &middot; ' . htmlspecialchars($override['section']) : ''; ?></div></td>
              <td><?php echo $is_applicable ? '<span class="badge badge-success">Berlaku</span>' : '<span class="badge badge-secondary">Tidak Berlaku</span>'; ?></td>
              <td><?php echo htmlspecialchars($override['override_durasi'] ?? '-') ?: '-'; ?></td>
              <td><?php echo !empty($override['updated_at']) ? format_am_date($override['updated_at']) : '-'; ?></td>
              <td><a class="btn btn-sm btn-outline-primary" href="<?php print_link($override_url); ?>">Atur</a></td>
            </tr>
          <?php } ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php if ($selected_unit) { ?>
    <div class="bg-light p-3 mb-3">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div><h5 class="mb-1"><?php echo htmlspecialchars(Master_partController::$machine_keys[$machine_key] ?? $machine_key); ?></h5><div class="text-muted"><?php echo htmlspecialchars($selected_unit['nama_mesin']); ?></div></div>
        <span class="badge badge-info"><?php echo count($parts); ?> part</span>
      </div>
      <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white mb-0">
          <thead><tr><th>Part</th><th>Default</th><th style="min-width:170px">Status Unit</th><th style="min-width:160px">Durasi Khusus</th><th style="min-width:175px">Aksi</th></tr></thead>
          <tbody>
          <?php if (empty($parts)) { ?><tr><td colspan="5" class="text-center text-muted">Part tidak ditemukan.</td></tr><?php } ?>
          <?php foreach ($parts as $part) {
            $has_override = !empty($part['override_id']);
            $applicable = !$has_override || in_array($part['is_applicable'] ?? null, array(true, 1, '1', 't', 'true'), true);
          ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($part['label']); ?></strong><div class="small text-muted"><?php echo htmlspecialchars($part['field_name']); ?><?php echo !empty($part['section']) ? ' &middot; ' . htmlspecialchars($part['section']) : ''; ?></div></td>
              <td><div>Status: Berlaku</div><div>Durasi: <?php echo htmlspecialchars($part['default_durasi'] ?? '-'); ?></div></td>
              <td colspan="3" class="p-2">
                <form method="post" action="<?php print_link('part_override/save?csrf_token=' . $csrf_token); ?>" class="form-row align-items-center m-0">
                  <input type="hidden" name="master_part_id" value="<?php echo intval($part['id']); ?>">
                  <input type="hidden" name="machine_key" value="<?php echo htmlspecialchars($machine_key); ?>">
                  <input type="hidden" name="mesin_id" value="<?php echo $mesin_id; ?>">
                  <div class="col-md-4 px-1"><select name="is_applicable" class="custom-select custom-select-sm"><option value="1" <?php echo $applicable ? 'selected' : ''; ?>>Berlaku</option><option value="0" <?php echo !$applicable ? 'selected' : ''; ?>>Tidak berlaku (N/A)</option></select></div>
                  <div class="col-md-4 px-1"><input type="text" name="durasi" maxlength="50" class="form-control form-control-sm" value="<?php echo htmlspecialchars($part['override_durasi'] ?? ''); ?>" placeholder="Default: <?php echo htmlspecialchars($part['default_durasi'] ?? '-'); ?>"></div>
                  <div class="col-md-4 px-1 text-nowrap"><button type="submit" class="btn btn-sm btn-primary">Simpan</button></div>
                </form>
                <?php if ($has_override) { ?>
                  <form method="post" action="<?php print_link('part_override/delete?csrf_token=' . $csrf_token); ?>" class="d-inline-block mt-1">
                    <input type="hidden" name="override_id" value="<?php echo intval($part['override_id']); ?>">
                    <input type="hidden" name="machine_key" value="<?php echo htmlspecialchars($machine_key); ?>">
                    <input type="hidden" name="mesin_id" value="<?php echo $mesin_id; ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus override dan kembali ke default?')">Reset</button>
                  </form>
                <?php } ?>
              </td>
            </tr>
          <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php } ?>
  </div>
</section>
<script>
$(function () {
  var unitsByModule = <?php echo json_encode($units_by_module, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
  var $module = $('#override-machine-key');
  var $unit = $('#override-unit-id');
  var initialUnit = '<?php echo $mesin_id; ?>';

  function populateUnits(keepSelection) {
    var key = $module.val() || '';
    var units = unitsByModule[key] || [];
    var wanted = keepSelection ? initialUnit : '';
    $('#override-unit-hidden').remove();
    $unit.empty().append($('<option>', {value: '', text: 'Pilih unit ...'}));
    units.forEach(function (unit) {
      var label = unit.nama_mesin + (unit.nomor_seri ? ' - ' + unit.nomor_seri : '');
      $unit.append($('<option>', {value: unit.id, text: label, selected: String(unit.id) === String(wanted)}));
    });
    if (units.length === 1) {
      $unit.val(String(units[0].id)).prop('disabled', true);
      $('<input>', {type: 'hidden', id: 'override-unit-hidden', name: 'mesin_id', value: units[0].id}).insertAfter($unit);
    } else {
      $unit.prop('disabled', !key || units.length === 0);
    }
  }

  $module.on('change', function () { populateUnits(false); });
  populateUnits(true);
});
</script>
