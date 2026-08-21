<?php
$csrf_token = Csrf::$token;
$machine_keys = Master_partController::$machine_keys;
$highlight_options = Master_partController::$highlight_options;
//Mesin yang lagi dibuka pas klik "Tambah Part" -- dipreselect biar admin gak
//perlu milih ulang, dan tombol Batal balik ke list mesin yang sama.
$preselect_machine = !empty($this->preselect_machine) ? $this->preselect_machine : '';
$back_url = 'master_part/index/' . $preselect_machine;
//Section yang udah ada per mesin -- dropdown "Section" difilter JS sesuai
//Mesin yang lagi dipilih, ditambah opsi ketik section baru.
$sections_by_machine = !empty($this->sections_by_machine) ? $this->sections_by_machine : array();
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
              <select required name="machine_key" id="ctrl-machine-key" class="custom-select">
                <option value="" disabled <?php echo empty($preselect_machine) ? 'selected' : ''; ?>>Pilih mesin ...</option>
                <?php foreach ($machine_keys as $key => $label) { ?>
                  <option value="<?php echo $key; ?>" <?php echo ($key === $preselect_machine ? 'selected' : ''); ?>><?php echo $label; ?></option>
                <?php } ?>
              </select>
              <small class="form-text text-muted">Semua mesin form Add AM-nya sudah baca dari sini -- part yang ditambahkan langsung muncul begitu disimpan.</small>
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
              <select id="ctrl-section-picker" class="custom-select mb-2">
                <option value="">-- Pilih section yang sudah ada, atau ketik baru di bawah --</option>
              </select>
              <input type="text" id="ctrl-section" name="section" class="form-control" placeholder="Ketik section baru, atau kosongkan buat masuk grup &quot;LAINNYA&quot;" />
              <small class="form-text text-muted">Judul grup part di form Add AM. Pilih dari dropdown buat gabung ke section yang sudah ada, atau ketik section baru di kotak teks. Kosongkan buat masuk grup "LAINNYA".</small>
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
            <div class="form-group">
              <label>Durasi</label>
              <input type="text" name="durasi" class="form-control" placeholder="misal: 2'" />
              <small class="form-text text-muted">Urutan tampil part diatur lewat drag-and-drop di halaman list, bukan di sini -- part baru otomatis ditaruh paling akhir.</small>
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
<script>
$(function () {
  var sectionsByMachine = <?php echo json_encode($sections_by_machine); ?>;
  var $machineSelect = $('#ctrl-machine-key');
  var $picker = $('#ctrl-section-picker');
  var $sectionInput = $('#ctrl-section');

  function reloadSectionPicker() {
    var sections = sectionsByMachine[$machineSelect.val()] || [];
    $picker.find('option:not(:first)').remove();
    sections.forEach(function (s) {
      $picker.append($('<option></option>').val(s).text(s));
    });
  }

  $machineSelect.on('change', reloadSectionPicker);
  $picker.on('change', function () {
    if ($picker.val()) { $sectionInput.val($picker.val()); }
  });

  reloadSectionPicker();
});
</script>
