<?php
$model = new SharedController;
$tag_options = $model->sig_kategori_tag_option_list();
$korelasi_options = $model->sig_korelasi_tag_option_list();
$klasifikasi_options = $model->sig_klasifikasi_tag_option_list();
$data = $this->view_data;
$parts = $data['parts'];
$image_names = array(
  'body_mesin' => 'body mesin',
  'panel_fbd' => 'panel fbd',
  'hmi_panel_fbd' => 'hmi panel fbd',
  'seal_bagtight' => 'seal bagtight',
  'container_up_down' => 'container up down',
  'shaking' => 'shaking',
  'pressure_gauge_damper' => 'pressure gauge damper',
  'seal_container' => 'seal container',
  'guarding_pengunci_kontainer' => 'guarding pengunci kontainer',
  'container_mesh_dan_roda' => 'container mesh dan roda',
  'filter_dan_bag_tight' => 'filter dan bag tight'
);

$sections = array(
  'STANDAR PEMBERSIHAN (CLEANING)' => array(
    'body_mesin', 'panel_fbd'
  ),
  'STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)' => array(
    'hmi_panel_fbd', 'seal_bagtight', 'container_up_down', 'shaking', 'pressure_gauge_damper', 'seal_container', 'guarding_pengunci_kontainer', 'container_mesh_dan_roda', 'filter_dan_bag_tight'
  )
);

$csrf_token = Csrf::$token;
$page_element_id = 'fbd_glatt-edit-data-' . random_str();
$rec_id = !empty($data['id_fbd_glatt']) ? $data['id_fbd_glatt'] : null;
?>
<section class="page" id="<?php echo $page_element_id; ?>">
  <div class="bg-light p-3 mb-3">
    <div class="container-fluid">
      <h4 class="record-title">Edit Data AM FBD Glatt</h4>
      <div>No: CR-PR-PR-1203.00 (25 Okt 2021)</div>
    </div>
  </div>
  <div class="container-fluid">
    <?php $this::display_page_errors(); ?>
    <?php if (!$rec_id) { ?>
      <div class="text-muted p-3"><i class="fa fa-ban"></i> No Record Found</div>
    <?php } else { ?>
    <div class="bg-light p-3 animated fadeIn page-content">
      <table class="table table-bordered table-sm mb-3">
        <tr><th width="20%">Nama Mesin</th><td><?php echo $data['nm_mesin'] ?: '-'; ?></td></tr>
        <tr><th>Dibuat</th><td><?php echo format_am_date($data["created_at"]); ?> oleh <?php echo $data['user_create']; ?></td></tr>
      </table>
      <form id="fbd_glatt-edit-data-form" class="form page-form needs-validation" novalidate
        action="<?php print_link("fbd_glatt/edit_data/$rec_id?csrf_token=$csrf_token") ?>" method="post">
        <?php foreach ($sections as $section_title => $section_fields) { ?>
          <div class="section-block mb-4">
            <div class="d-flex justify-content-between align-items-center mt-4 mb-3 border-bottom pb-2">
              <h4 class="text-primary m-0"><?php echo $section_title; ?></h4>
              <button type="button" class="btn btn-outline-success btn-sm btn-check-section-ok">
                <i class="fa fa-check"></i> Semua Kondisi Baik
              </button>
            </div>
            <?php foreach ($section_fields as $field) {
              if (!isset($parts[$field])) continue;
              $label = $parts[$field];
              $current_value = isset($data[$field]) ? $data[$field] : '';
              $abn = isset($data['abnormalitas'][$field]) ? $data['abnormalitas'][$field] : null;
              $is_nok = ($current_value === 'NOK');
              $img_key = isset($image_names[$field]) ? $image_names[$field] : str_replace('_', ' ', $field);
              $image_path = 'assets/images/fbd_glatt/fbd_glatt ' . $img_key . '.png';
            ?>
              <div class="card mb-3 part-card" data-part="<?php echo $field; ?>">
                <div class="card-body">
                  <div class="row">
                    <div class="col-md-3">
                      <div class="border text-center p-2 text-muted"><a href="<?php print_link($image_path); ?>" class="part-image-link"><img class="img-fluid" src="<?php print_link($image_path); ?>" alt="<?php echo $label; ?>" onerror="this.style.display='none';this.parentNode.nextElementSibling.style.display='block';"></a><span style="display:none">Gambar belum diunggah</span></div>
                    </div>
                    <div class="col-md-4">
                      <label class="d-block"><?php echo $label; ?> <span class="text-danger">*</span></label>
                      <?php foreach (Menu::$Kondisi_Harian as $option) { ?>
                        <div class="custom-control custom-radio">
                          <input required class="custom-control-input part-kondisi" type="radio"
                            id="<?php echo $field . '-' . $option['value']; ?>" name="<?php echo $field; ?>"
                            value="<?php echo $option['value']; ?>"
                            <?php echo ($option['value'] === $current_value ? 'checked' : ''); ?>>
                          <label class="custom-control-label" for="<?php echo $field . '-' . $option['value']; ?>"><?php echo $option['label']; ?></label>
                        </div>
                      <?php } ?>
                    </div>
                  </div>
                  <div class="kendala-box border-top mt-3 pt-3" style="<?php echo $is_nok ? '' : 'display:none'; ?>">
                    <h6>Kendala selama AM</h6>
                    <textarea name="kendala_<?php echo $field; ?>" class="form-control mb-2"
                      placeholder="Jelaskan kendala" <?php echo $is_nok ? 'required' : ''; ?>><?php echo htmlspecialchars($abn ? $abn['kendala'] : ''); ?></textarea>
                    <div class="row">
                      <div class="col-md-3"><label>Kategori Tag</label>
                        <select name="kategori_tag_<?php echo $field; ?>" class="custom-select">
                          <option value="">Pilih ...</option>
                          <?php foreach ($tag_options as $o) { $sel = ($abn && $abn['kategori_tag'] == $o['value']) ? 'selected' : ''; ?>
                            <option <?php echo $sel; ?> value="<?php echo $o['value']; ?>"><?php echo $o['label']; ?></option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="col-md-3"><label>Korelasi Tag</label>
                        <select name="korelasi_tag_<?php echo $field; ?>" id="korelasi-<?php echo $field; ?>"
                          data-load-select-options="ketidaksesuaian-<?php echo $field; ?>" class="custom-select">
                          <option value="">Pilih ...</option>
                          <?php foreach ($korelasi_options as $o) { $sel = ($abn && $abn['korelasi_tag'] == $o['value']) ? 'selected' : ''; ?>
                            <option <?php echo $sel; ?> value="<?php echo $o['value']; ?>"><?php echo $o['label']; ?></option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="col-md-3"><label>Klasifikasi Tag</label>
                        <select name="klasifikasi_tag_<?php echo $field; ?>" class="custom-select">
                          <option value="">Pilih ...</option>
                          <?php foreach ($klasifikasi_options as $o) { $sel = ($abn && $abn['klasifikasi_tag'] == $o['value']) ? 'selected' : ''; ?>
                            <option <?php echo $sel; ?> value="<?php echo $o['value']; ?>"><?php echo $o['label']; ?></option>
                          <?php } ?>
                        </select>
                      </div>
                      <div class="col-md-3"><label>Kategori Ketidaksesuaian</label>
                        <select name="kategori_ketidaksesuaian_<?php echo $field; ?>" id="ctrl-ketidaksesuaian-<?php echo $field; ?>"
                          data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>" class="custom-select">
                          <option value="">Pilih ...</option>
                          <?php if ($abn && !empty($abn['kategori_ketidaksesuaian'])) { ?>
                            <option selected value="<?php echo $abn['kategori_ketidaksesuaian']; ?>"><?php echo $abn['kategori_ketidaksesuaian']; ?></option>
                          <?php } ?>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            <?php } ?>
          </div>
        <?php } ?>

        <div class="form-group mt-4">
          <label class="control-label font-weight-bold" for="ctrl-perubahan">Perubahan yang dilakukan <span class="text-danger">*</span></label>
          <textarea placeholder="Masukkan detail perubahan yang dilakukan pada data ini..." id="ctrl-perubahan" rows="4"
            name="perubahan" class="form-control" required></textarea>
        </div>

        <div class="text-center">
          <a class="btn btn-secondary mx-1" href="<?php print_link('fbd_glatt/view/' . $rec_id) ?>"><i class="fa fa-arrow-left"></i> Kembali</a>
          <button class="btn btn-primary mx-1" type="submit">Simpan Perubahan <i class="fa fa-save"></i></button>
        </div>
      </form>
    </div>
    <?php } ?>
  </div>
</section>
<script>
$(function () {
  function toggleKendala(card) {
    var box = card.find('.kendala-box'),
        isNokChecked = card.find('.part-kondisi[value="NOK"]').is(':checked');
    box.toggle(isNokChecked);
    box.find('textarea,select').prop('required', isNokChecked);
  }

  $('.part-kondisi').on('change', function () {
    toggleKendala($(this).closest('.part-card'));
  });

  $('.btn-check-section-ok').on('click', function () {
    var sectionBlock = $(this).closest('.section-block');
    var okRadios = sectionBlock.find('.part-kondisi[value="OK"]');
    var allChecked = okRadios.filter(':checked').length === okRadios.length;
    if (allChecked) {
      sectionBlock.find('.part-kondisi').prop('checked', false).trigger('change');
    } else {
      okRadios.prop('checked', true).trigger('change');
    }
  });
});
</script>
