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
});
</script>
