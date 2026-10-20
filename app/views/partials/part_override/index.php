<?php
$data = $this->view_data;
$modules = $data['modules'] ?? array();
$units = $data['units'] ?? array();
$units_by_module = $data['units_by_module'] ?? array();
$parts = $data['parts'] ?? array();
$part_catalog = $data['part_catalog'] ?? array();
$selected_part = $data['selected_part'] ?? null;
$part_unit_rows = $data['part_unit_rows'] ?? array();
$machine_key = $data['machine_key'] ?? '';
$mesin_id = intval($data['mesin_id'] ?? 0);
$selected_unit = $data['selected_unit'] ?? null;
$view_mode = $data['view_mode'] ?? 'part';
$search = $data['search'] ?? '';
$override_records = $data['override_records'] ?? array();
$fixed_excluded_unit_ids = $data['fixed_excluded_unit_ids'] ?? array();
$overrides_by_part = array();
foreach ($override_records as $override_record) {
  $overrides_by_part[intval($override_record['master_part_id'])][] = $override_record;
}
$csrf_token = Csrf::$token;
?>
<section class="page">
  <div class="bg-light p-3 mb-3">
    <div class="container-fluid">
      <h4 class="record-title mb-1">Override Part per Unit</h4>
      <p class="text-muted mb-0">Atur perbedaan part antarunit fisik. Master Part menjadi baseline; form yang sudah disimpan tetap memakai snapshot historis.</p>
    </div>
  </div>
  <div class="container-fluid">
    <?php $this::display_page_errors(); ?>
    <div class="mb-3">
      <a class="btn btn-sm <?php echo $view_mode === 'part' ? 'btn-primary' : 'btn-outline-primary'; ?>" href="<?php print_link('part_override?' . http_build_query(array('machine_key' => $machine_key))); ?>">Lihat per Part</a>
      <a class="btn btn-sm <?php echo $view_mode === 'unit' ? 'btn-primary' : 'btn-outline-primary'; ?>" href="<?php print_link('part_override?' . http_build_query(array('machine_key' => $machine_key, 'view' => 'unit'))); ?>">Lihat per Unit</a>
    </div>
    <div class="bg-light p-3 mb-3">
      <form method="get" action="<?php print_link('part_override'); ?>">
        <input type="hidden" name="view" value="<?php echo htmlspecialchars($view_mode); ?>">
        <div class="form-row align-items-end">
          <div class="col-md-<?php echo $view_mode === 'unit' ? '4' : '6'; ?> form-group">
            <label>Modul Mesin</label>
            <select required id="override-machine-key" name="machine_key" class="custom-select">
              <option value="">Pilih modul ...</option>
              <?php foreach ($modules as $key => $module) { ?><option value="<?php echo htmlspecialchars($key); ?>" <?php echo $machine_key === $key ? 'selected' : ''; ?>><?php echo htmlspecialchars($module['label']); ?></option><?php } ?>
            </select>
          </div>
          <?php if ($view_mode === 'unit') { ?>
          <div class="col-md-4 form-group">
            <label>Unit Fisik</label>
            <select id="override-unit-id" name="mesin_id" class="custom-select" <?php echo $machine_key === '' ? 'disabled' : ''; ?>>
              <option value="">Pilih unit ...</option>
              <?php foreach ($units as $unit) { ?><option value="<?php echo intval($unit['id']); ?>" <?php echo $mesin_id === intval($unit['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($unit['nama_mesin']); ?><?php echo !empty($unit['nomor_seri']) ? ' - ' . htmlspecialchars($unit['nomor_seri']) : ''; ?></option><?php } ?>
            </select>
          </div>
          <?php } ?>
          <div class="col-md-<?php echo $view_mode === 'unit' ? '4' : '6'; ?> form-group">
            <label>Cari Part</label>
            <input type="text" name="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Nama atau field part">
          </div>
        </div>
        <div class="text-right"><button type="submit" class="btn btn-primary"><i class="fa fa-search"></i> Tampilkan</button></div>
      </form>
      <?php if ($machine_key !== '' && empty($units)) { ?><div class="alert alert-warning mt-3 mb-0">Belum ada unit fisik yang dipetakan ke modul ini.</div><?php } ?>
      <?php if ($machine_key !== '' && $view_mode === 'unit' && !$selected_unit) { ?><div class="small text-muted mt-3">Pilih unit fisik untuk melihat seluruh part pada unit tersebut.</div><?php } ?>
    </div>

    <?php if ($machine_key !== '' && $view_mode === 'part' && !$selected_part) { ?>
    <div class="bg-light p-3 mb-3">
      <h5 class="mb-1">Pilih Part</h5>
      <p class="small text-muted mb-3">Part aktif pada modul <?php echo htmlspecialchars(Master_partController::$machine_keys[$machine_key] ?? $machine_key); ?>. Gunakan Cari Part di atas untuk mempersempit daftar.</p>
      <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white mb-0">
          <thead><tr><th>Part</th><th>Section</th><th>Baseline</th><th>Pengecualian / Override</th><th>Aksi</th></tr></thead>
          <tbody>
          <?php if (empty($part_catalog)) { ?><tr><td colspan="5" class="text-center text-muted">Part tidak ditemukan.</td></tr><?php } ?>
          <?php foreach ($part_catalog as $catalog_part) {
            $part_url = 'part_override?' . http_build_query(array('machine_key' => $machine_key, 'part_id' => intval($catalog_part['id']), 'search' => $search));
            $part_overrides = $overrides_by_part[intval($catalog_part['id'])] ?? array();
            $override_labels = array();
            $fixed_inkjet_part = $machine_key === 'best_pack' && $catalog_part['field_name'] === 'print_head_inkjet';
            foreach ($part_overrides as $part_override) {
              if ($fixed_inkjet_part && isset($fixed_excluded_unit_ids[intval($part_override['mesin_id'])])) { continue; }
              $is_applicable = in_array($part_override['is_applicable'] ?? null, array(true, 1, '1', 't', 'true'), true);
              $override_labels[] = $part_override['nama_mesin'] . ($is_applicable ? ' (durasi ' . ($part_override['override_durasi'] ?: 'default') . ')' : ' (N/A)');
            }
            if ($fixed_inkjet_part) {
              foreach ($units as $unit) {
                if (isset($fixed_excluded_unit_ids[intval($unit['id'])])) { $override_labels[] = $unit['nama_mesin'] . ' (N/A - aturan unit)'; }
              }
            }
          ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($catalog_part['label']); ?></strong><div class="small text-muted"><?php echo htmlspecialchars($catalog_part['field_name']); ?></div></td>
              <td><?php echo htmlspecialchars($catalog_part['section'] ?? '-'); ?></td>
              <td>Berlaku &middot; Durasi <?php echo htmlspecialchars($catalog_part['durasi'] ?? '-'); ?></td>
              <td><?php echo $override_labels ? '<strong>' . count($override_labels) . ' unit:</strong> ' . htmlspecialchars(implode(', ', $override_labels)) : '<span class="text-muted">Semua unit mengikuti baseline</span>'; ?></td>
              <td><a class="btn btn-sm btn-outline-primary" href="<?php print_link($part_url); ?>">Lihat semua unit</a></td>
            </tr>
          <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php } ?>

    <?php if ($view_mode === 'part' && $selected_part) { ?>
    <div class="bg-light p-3 mb-3" id="part-unit-status">
      <a class="btn btn-sm btn-outline-primary mb-3" href="<?php print_link('part_override?' . http_build_query(array('machine_key' => $machine_key, 'search' => $search))); ?>">&larr; Kembali ke daftar part</a>
      <h5 class="mb-1"><?php echo htmlspecialchars($selected_part['label']); ?> &mdash; Status per Unit</h5>
      <p class="small text-muted mb-3">Baseline master: berlaku, durasi <?php echo htmlspecialchars($selected_part['durasi'] ?? '-'); ?>. <?php if ($machine_key === 'best_pack' && $selected_part['field_name'] === 'print_head_inkjet') { ?>Unit non-Inkjet memiliki pengecualian tetap untuk part ini.<?php } ?> Ubah beberapa unit sekaligus, lalu klik Simpan Perubahan sekali. Tombol Kembali ke baseline juga baru berlaku setelah disimpan.</p>
      <form id="bulk-part-override-form" method="post" action="<?php print_link('part_override/save_bulk?csrf_token=' . $csrf_token); ?>">
        <input type="hidden" name="master_part_id" value="<?php echo intval($selected_part['id']); ?>">
        <input type="hidden" name="machine_key" value="<?php echo htmlspecialchars($machine_key); ?>">
      </form>
      <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white mb-0">
          <thead><tr><th>Unit Fisik</th><th>Status</th><th>Durasi Khusus</th><th>Aksi</th></tr></thead>
          <tbody>
          <?php foreach ($part_unit_rows as $unit) {
            $override = $unit['override'];
            $fixed_excluded = $machine_key === 'best_pack' && $selected_part['field_name'] === 'print_head_inkjet' && isset($fixed_excluded_unit_ids[intval($unit['id'])]);
            $applicable = !$override || in_array($override['is_applicable'] ?? null, array(true, 1, '1', 't', 'true'), true);
            $unit_id = intval($unit['id']);
            $form_id = 'part-unit-' . $unit_id;
          ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($unit['nama_mesin']); ?></strong><?php if (!empty($unit['nomor_seri'])) { ?><div class="small text-muted"><?php echo htmlspecialchars($unit['nomor_seri']); ?></div><?php } ?></td>
              <?php if ($fixed_excluded) { ?>
              <td><span class="badge badge-secondary">Tidak berlaku</span><div class="small text-muted">Aturan unit non-Inkjet</div></td>
              <td class="text-muted">&mdash;</td>
              <td class="text-muted">Ditetapkan sistem</td>
              <?php } else { ?>
              <td>
                <input type="hidden" form="bulk-part-override-form" name="units[<?php echo $unit_id; ?>][original_has_override]" value="<?php echo $override ? '1' : '0'; ?>">
                <input type="hidden" form="bulk-part-override-form" name="units[<?php echo $unit_id; ?>][original_is_applicable]" value="<?php echo $applicable ? '1' : '0'; ?>">
                <input type="hidden" form="bulk-part-override-form" name="units[<?php echo $unit_id; ?>][original_durasi]" value="<?php echo htmlspecialchars($override['durasi'] ?? ''); ?>">
                <input id="<?php echo $form_id; ?>-reset" type="hidden" form="bulk-part-override-form" name="units[<?php echo $unit_id; ?>][reset_to_baseline]" value="0">
                <input type="hidden" form="bulk-part-override-form" name="units[<?php echo $unit_id; ?>][is_applicable]" value="0">
                <label class="mb-0" for="<?php echo $form_id; ?>"><input id="<?php echo $form_id; ?>" type="checkbox" form="bulk-part-override-form" name="units[<?php echo $unit_id; ?>][is_applicable]" value="1" <?php echo $applicable ? 'checked' : ''; ?>> Berlaku</label>
                <?php if (!$override) { ?><div class="small text-muted">Mengikuti baseline</div><?php } ?>
              </td>
              <td><input id="<?php echo $form_id; ?>-durasi" form="bulk-part-override-form" type="text" name="units[<?php echo $unit_id; ?>][durasi]" maxlength="50" class="form-control form-control-sm" value="<?php echo htmlspecialchars($override['durasi'] ?? ''); ?>" placeholder="Default: <?php echo htmlspecialchars($selected_part['durasi'] ?? '-'); ?>"></td>
              <td class="text-nowrap">
                <button type="button" class="btn btn-sm btn-outline-secondary override-reset-row" data-unit-id="<?php echo $unit_id; ?>">Kembali ke baseline</button>
              </td>
              <?php } ?>
            </tr>
          <?php } ?>
          </tbody>
        </table>
      </div>
      <div id="bulk-override-feedback" class="alert d-none mt-3 mb-0" role="alert"></div>
      <div class="text-right mt-3"><button id="bulk-override-submit" type="submit" form="bulk-part-override-form" class="btn btn-primary">Simpan Perubahan</button></div>
    </div>
    <?php } ?>

    <?php if ($view_mode === 'unit' && $selected_unit) { ?>
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
            $fixed_excluded = $machine_key === 'best_pack' && $part['field_name'] === 'print_head_inkjet' && isset($fixed_excluded_unit_ids[$mesin_id]);
            $applicable = !$has_override || in_array($part['is_applicable'] ?? null, array(true, 1, '1', 't', 'true'), true);
          ?>
            <tr>
              <td><strong><?php echo htmlspecialchars($part['label']); ?></strong><div class="small text-muted"><?php echo htmlspecialchars($part['field_name']); ?><?php echo !empty($part['section']) ? ' &middot; ' . htmlspecialchars($part['section']) : ''; ?></div></td>
              <td><div>Master: Berlaku</div><div>Durasi: <?php echo htmlspecialchars($part['default_durasi'] ?? '-'); ?></div></td>
              <?php if ($fixed_excluded) { ?>
              <td colspan="3"><span class="badge badge-secondary">Tidak berlaku</span> <span class="small text-muted">Aturan unit non-Inkjet; tidak dapat diubah melalui override.</span></td>
              <?php } else { ?>
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
              <?php } ?>
            </tr>
          <?php } ?>
          </tbody>
        </table>
      </div>
    </div>
    <?php } ?>
  </div>
</section>
<?php if ($view_mode === 'part' && $selected_part) { ?>
<script>
$(function () {
  var $feedback = $('#bulk-override-feedback');
  function showFeedback(message, level) {
    $feedback.removeClass('d-none alert-danger alert-warning').addClass('alert-' + level).text(message);
  }
  $('#bulk-part-override-form').on('submit', async function (event) {
    event.preventDefault();
    var form = this;
    var $submit = $('#bulk-override-submit');
    $submit.prop('disabled', true);
    $feedback.addClass('d-none');
    try {
      var response = await fetch(form.action, {
        method: 'POST',
        headers: {'X-Requested-With': 'XMLHttpRequest'},
        body: new FormData(form),
        credentials: 'same-origin'
      });
      if (!(response.headers.get('content-type') || '').includes('application/json')) {
        throw new Error('Respons server tidak valid. Periksa sesi login, lalu coba lagi.');
      }
      var result = await response.json();
      if (!response.ok || !result.success) { throw new Error(result.message || 'Perubahan gagal disimpan.'); }
      if (result.changed > 0) { window.location.reload(); return; }
      showFeedback(result.message, 'warning');
    } catch (error) {
      showFeedback(error.message || 'Perubahan gagal disimpan. Coba lagi.', 'danger');
    }
    $submit.prop('disabled', false);
  });
  $('.override-reset-row').on('click', function () {
    var prefix = '#part-unit-' + $(this).attr('data-unit-id');
    $(prefix).prop('checked', true);
    $(prefix + '-durasi').val('');
    $(prefix + '-reset').val('1');
  });
  $('#part-unit-status input[type="checkbox"], #part-unit-status input[name$="[durasi]"]').on('change input', function () {
    var unitId = $(this).closest('tr').find('.override-reset-row').attr('data-unit-id');
    $('#part-unit-' + unitId + '-reset').val('0');
  });
});
</script>
<?php } ?>
<?php if ($view_mode === 'unit') { ?>
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
<?php } ?>
