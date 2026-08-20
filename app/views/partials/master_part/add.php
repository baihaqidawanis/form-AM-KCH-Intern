<?php
$csrf_token = Csrf::$token;
$machine_keys = Master_partController::$machine_keys;
$highlight_options = Master_partController::$highlight_options;
//Mesin yang lagi dibuka pas klik "Tambah Part" -- dipreselect biar admin gak
//perlu milih ulang, dan tombol Batal balik ke list mesin yang sama.
$preselect_machine = !empty($this->preselect_machine) ? $this->preselect_machine : '';
$back_url = 'master_part/index/' . $preselect_machine;
?>
<section class="page">
  <div class="bg-light p-3 mb-3">
    <div class="container-fluid">
      <h4 class="record-title">Tambah Part Mesin</h4>
    </div>
  </div>
  <div class="container-fluid">
    <div class="row">
      <div class="col-md-8">
        <?php $this::display_page_errors(); ?>
        <div class="alert alert-info">
          <i class="fa fa-info-circle"></i> Nambah part di sini <strong>otomatis bikin kolom baru</strong> di tabel database mesin terkait (kalau Field Name-nya belum ada) -- part langsung muncul di form Add AM begitu disimpan. Field Name gak bisa diubah lagi setelah dibuat (identitas part), jadi pastikan sudah benar sebelum Simpan.
        </div>
        <div class="bg-light p-3">
          <form class="form needs-validation" novalidate action="<?php print_link("master_part/add/$preselect_machine?csrf_token=$csrf_token") ?>" method="post">
            <div class="form-group">
              <label>Mesin <span class="text-danger">*</span></label>
              <select required name="machine_key" class="custom-select">
                <option value="" disabled <?php echo empty($preselect_machine) ? 'selected' : ''; ?>>Pilih mesin ...</option>
                <?php foreach ($machine_keys as $key => $label) { ?>
                  <option value="<?php echo $key; ?>" <?php echo ($key === $preselect_machine ? 'selected' : ''); ?>><?php echo $label; ?></option>
                <?php } ?>
              </select>
              <small class="form-text text-muted">Cuma Cosmec yang form Add AM-nya sudah baca dari sini -- mesin lain belum berefek sampai view-nya dimigrasikan.</small>
            </div>
            <div class="form-group">
              <label>Field Name <span class="text-danger">*</span></label>
              <input required type="text" name="field_name" class="form-control" placeholder="misal: body_panel_hmi" />
              <small class="form-text text-muted">Huruf kecil/angka/underscore saja, HARUS persis sama dengan nama kolom di tabel mesinnya.</small>
            </div>
            <div class="form-group">
              <label>Label <span class="text-danger">*</span></label>
              <input required type="text" name="label" class="form-control" placeholder="misal: Body Panel HMI" />
            </div>
            <div class="form-group">
              <label>Section</label>
              <input type="text" name="section" class="form-control" placeholder="misal: STANDAR PEMBERSIHAN (CLEANING)" />
            </div>
            <div class="row">
              <div class="col-md-6 form-group">
                <label>Metode</label>
                <input type="text" name="metode" class="form-control" />
              </div>
              <div class="col-md-6 form-group">
                <label>Alat</label>
                <input type="text" name="alat" class="form-control" />
              </div>
            </div>
            <div class="form-group">
              <label>Standard</label>
              <textarea name="standard" class="form-control"></textarea>
            </div>
            <div class="row">
              <div class="col-md-6 form-group">
                <label>Durasi</label>
                <input type="text" name="durasi" class="form-control" placeholder="misal: 2'" />
              </div>
              <div class="col-md-6 form-group">
                <label>Urutan</label>
                <input type="number" name="urutan" class="form-control" value="0" />
              </div>
            </div>
            <div class="form-group">
              <label>Pelaksanaan</label>
              <input type="text" name="pelaksanaan" class="form-control" placeholder="misal: Harian (Setiap Awal Shift 1)" />
            </div>
            <div class="form-group">
              <label>Highlight Baris</label>
              <select name="highlight" class="custom-select">
                <?php foreach ($highlight_options as $value => $label) { ?>
                  <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="form-group">
              <label>Foto</label>
              <div class="dropzone" input="#ctrl-image_path" fieldname="part_image" data-multiple="false" dropmsg="Choose file or drag and drop" btntext="Browse" filesize="3" maximum="1">
                <input name="image_path" id="ctrl-image_path" class="dropzone-input form-control" value="" type="text" />
              </div>
            </div>
            <div class="form-group text-center">
              <a class="btn btn-secondary mr-2" href="<?php print_link($back_url) ?>">Batal</a>
              <button class="btn btn-primary" type="submit">Simpan <i class="fa fa-send"></i></button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
