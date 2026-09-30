<?php
$data = $this->view_data;
$csrf_token = Csrf::$token;
$machine_keys = Master_partController::$machine_keys;
$highlight_options = Master_partController::$highlight_options;
$rec_id = isset($data['id']) ? $data['id'] : null;
//Balik ke list mesin part ini, bukan ke mesin default.
$back_url = !empty($this->back_url) ? $this->back_url : 'master_part';
//Section yang udah ada buat mesin part ini -- dropdown "Section" milih dari sini.
$sections_by_machine = !empty($this->sections_by_machine) ? $this->sections_by_machine : array();
$existing_sections = isset($sections_by_machine[$data['machine_key']]) ? $sections_by_machine[$data['machine_key']] : array();
$override_units = !empty($this->override_units) ? $this->override_units : array();
$part_overrides = !empty($this->part_overrides) ? $this->part_overrides : array();
?>
<section class="page">
  <div class="bg-light p-3 mb-3">
    <div class="container-fluid">
      <h4 class="record-title">Edit Part Mesin</h4>
    </div>
  </div>
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-8">
        <?php $this::display_page_errors(); ?>
        <?php if (!$rec_id) { ?>
          <div class="text-muted p-3"><i class="fa fa-ban"></i> No Record Found</div>
        <?php } else { ?>
        <div class="bg-light p-3">
          <form class="form needs-validation" novalidate action="<?php print_link("master_part/edit/$rec_id?csrf_token=$csrf_token") ?>" method="post">
            <div class="form-group">
              <label>Mesin</label>
              <input type="text" class="form-control" value="<?php echo isset($machine_keys[$data['machine_key']]) ? $machine_keys[$data['machine_key']] : $data['machine_key']; ?>" disabled readonly />
            </div>
            <div class="form-group">
              <label>Field Name</label>
              <input type="text" class="form-control" value="<?php echo $data['field_name']; ?>" disabled readonly />
              <small class="form-text text-muted">Mesin & Field Name gak bisa diubah setelah dibuat (identitas part) -- kalau salah, hapus lalu buat baru.</small>
            </div>
            <div class="form-group">
              <label>Label <span class="text-danger">*</span></label>
              <input required type="text" name="label" class="form-control" value="<?php echo $data['label']; ?>" />
            </div>
            <div class="form-group">
              <label>Section</label>
              <select id="ctrl-section-picker" class="custom-select mb-2">
                <option value="">-- Pilih section yang sudah ada, atau ketik baru di bawah --</option>
                <?php foreach ($existing_sections as $s) { ?>
                  <option value="<?php echo htmlspecialchars($s); ?>"><?php echo htmlspecialchars($s); ?></option>
                <?php } ?>
              </select>
              <input type="text" id="ctrl-section" name="section" class="form-control" value="<?php echo $data['section']; ?>" placeholder="Ketik section baru, atau kosongkan buat masuk grup &quot;LAINNYA&quot;" />
              <small class="form-text text-muted">Judul grup part di form Add AM. Pilih dari dropdown buat gabung ke section yang sudah ada, atau ketik section baru di kotak teks. Kosongkan buat masuk grup "LAINNYA".</small>
            </div>
            <div class="row">
              <div class="col-md-6 form-group">
                <label>Metode</label>
                <input type="text" name="metode" class="form-control" value="<?php echo $data['metode']; ?>" />
              </div>
              <div class="col-md-6 form-group">
                <label>Alat</label>
                <input type="text" name="alat" class="form-control" value="<?php echo $data['alat']; ?>" />
              </div>
            </div>
            <div class="form-group">
              <label>Standard</label>
              <textarea name="standard" class="form-control"><?php echo $data['standard']; ?></textarea>
            </div>
            <div class="form-group">
              <label>Durasi</label>
              <input type="text" name="durasi" class="form-control" value="<?php echo $data['durasi']; ?>" />
              <small class="form-text text-muted">Urutan tampil part diatur lewat drag-and-drop di halaman list, bukan di sini.</small>
            </div>
            <div class="form-group">
              <label>Pelaksanaan</label>
              <input type="text" name="pelaksanaan" class="form-control" value="<?php echo $data['pelaksanaan']; ?>" />
            </div>
            <div class="form-group">
              <label>Berlaku pada Shift <span class="text-danger">*</span></label>
              <?php $selected_shifts = explode(',', $data['shift_schedule'] ?? '1'); ?>
              <div class="d-flex flex-wrap" id="shift-schedule-options">
                <?php foreach (array('1', '2', '3') as $shift) { ?>
                  <div class="custom-control custom-checkbox custom-control-inline mr-3 mb-2">
                    <input type="checkbox" class="custom-control-input shift-schedule-option" id="ctrl-shift-<?php echo $shift; ?>" data-shift="<?php echo $shift; ?>" <?php echo in_array($shift, $selected_shifts, true) ? 'checked' : ''; ?> />
                    <label class="custom-control-label" for="ctrl-shift-<?php echo $shift; ?>">Shift <?php echo $shift; ?></label>
                  </div>
                <?php } ?>
              </div>
              <input type="hidden" required name="shift_schedule" id="ctrl-shift-schedule" value="<?php echo htmlspecialchars(implode(',', $selected_shifts)); ?>" />
              <small id="shift-schedule-error" class="form-text text-danger" style="display:none">Pilih minimal satu shift.</small>
              <small class="form-text text-muted">Dipakai oleh filter checklist. Teks Pelaksanaan hanya untuk informasi operator.</small>
            </div>
            <div class="form-group">
              <label>Highlight Baris</label>
              <select name="highlight" class="custom-select">
                <?php foreach ($highlight_options as $value => $label) { $sel = ($value == $data['highlight']) ? 'selected' : ''; ?>
                  <option <?php echo $sel; ?> value="<?php echo $value; ?>"><?php echo $label; ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="form-group">
              <label>Foto</label>
              <?php if (!empty($data['image_path'])) { ?>
                <div class="mb-2"><img src="<?php print_link($data['image_path']); ?>" style="max-width:120px;max-height:120px;object-fit:cover;" onerror="this.style.display='none';"></div>
              <?php } ?>
              <div class="dropzone" input="#ctrl-image_path" fieldname="part_image" data-multiple="false" dropmsg="Choose file or drag and drop (kosongkan kalau gak mau ganti foto)" btntext="Browse" filesize="3" maximum="1">
                <input name="image_path" id="ctrl-image_path" class="dropzone-input form-control" value="" type="text" />
              </div>
            </div>
            <div class="form-group text-center">
              <a class="btn btn-secondary mr-2" href="<?php print_link($back_url) ?>">Batal</a>
              <button class="btn btn-primary" type="submit">Simpan <i class="fa fa-send"></i></button>
            </div>
          </form>
        </div>
        <?php if (!empty($override_units)) { ?>
        <div class="bg-light p-3 mt-3">
          <h5 class="mb-2">Override per Unit Mesin</h5>
          <p class="small text-muted">Atur part yang tidak berlaku atau durasi khusus. Nilai kosong memakai durasi default part.</p>
          <form method="post" action="<?php print_link("master_part/save_override/$rec_id?csrf_token=$csrf_token") ?>" class="row align-items-end">
            <div class="col-md-4 form-group"><label>Unit Mesin</label><select required name="mesin_id" class="custom-select"><option value="">Pilih unit</option><?php foreach ($override_units as $unit) { ?><option value="<?php echo intval($unit['id']); ?>"><?php echo htmlspecialchars($unit['nama_mesin']); ?></option><?php } ?></select></div>
            <div class="col-md-4 form-group"><label>Status Part</label><select name="is_applicable" class="custom-select"><option value="1">Berlaku</option><option value="0">Tidak berlaku (N/A)</option></select></div>
            <div class="col-md-2 form-group"><label>Durasi Khusus</label><input type="text" name="durasi" class="form-control" maxlength="50" placeholder="Default"></div>
            <div class="col-md-2 form-group"><button type="submit" class="btn btn-primary btn-block">Simpan</button></div>
          </form>
          <?php if (!empty($part_overrides)) { ?>
          <div class="table-responsive"><table class="table table-sm table-bordered bg-white mb-0"><thead><tr><th>Unit</th><th>Status</th><th>Durasi</th><th style="width:90px">Aksi</th></tr></thead><tbody>
          <?php foreach ($part_overrides as $override) { ?>
            <tr><td><?php echo htmlspecialchars($override['nama_mesin']); ?></td><td><?php echo !empty($override['is_applicable']) && $override['is_applicable'] !== 'f' ? 'Berlaku' : 'N/A'; ?></td><td><?php echo $override['durasi'] !== null && $override['durasi'] !== '' ? htmlspecialchars($override['durasi']) : '<span class="text-muted">Default</span>'; ?></td><td><form method="post" action="<?php print_link("master_part/delete_override/$rec_id?csrf_token=$csrf_token") ?>"><input type="hidden" name="override_id" value="<?php echo intval($override['id']); ?>"><button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button></form></td></tr>
          <?php } ?>
          </tbody></table></div>
          <?php } ?>
        </div>
        <?php } ?>
        <?php } ?>
      </div>
    </div>
  </div>
</section>
<script>
$(function () {
  $('#ctrl-section-picker').on('change', function () {
    if (this.value) { $('#ctrl-section').val(this.value); }
  });

  function syncShiftSchedule() {
    var shifts = $('.shift-schedule-option:checked').map(function () { return $(this).data('shift').toString(); }).get();
    $('#ctrl-shift-schedule').val(shifts.join(','));
    $('#shift-schedule-error').toggle(shifts.length === 0);
    return shifts.length > 0;
  }
  $('.shift-schedule-option').on('change', syncShiftSchedule);
  $('form').on('submit', function (event) {
    if (!syncShiftSchedule()) { event.preventDefault(); }
  });
});
</script>
