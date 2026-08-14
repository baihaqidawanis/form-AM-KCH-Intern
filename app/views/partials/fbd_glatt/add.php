<?php
$model = new SharedController;
$tag_options = $model->sig_kategori_tag_option_list();
$korelasi_options = $model->sig_korelasi_tag_option_list();
$klasifikasi_options = $model->sig_klasifikasi_tag_option_list();
$parts = $this->view_data['parts'];

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

$part_details = array(
  'body_mesin' => array(
    'metode' => 'Disemprot',
    'alat' => 'Selang, Wiper Lantai, Wypall dan air',
    'standard' => 'Bagian luar bersih dari kotoran',
    'durasi' => "10'",
    'pelaksanaan' => 'Harian (Setiap akhir Shift 1) *note: dilakukaan jika ada proses'
  ),
  'panel_fbd' => array(
    'metode' => 'Dilap',
    'alat' => 'Wypall lembab dengan Alkohol 70%',
    'standard' => 'Bagian luar bersih dari kotoran',
    'durasi' => "2'",
    'pelaksanaan' => 'Harian (Setiap akhir Shift 1) *note: dilakukaan jika ada proses'
  ),
  'hmi_panel_fbd' => array(
    'metode' => 'Test Fungsi',
    'alat' => 'Visual Control',
    'standard' => 'Layar/display merespons ketika disentuh',
    'durasi' => "1'",
    'pelaksanaan' => 'Harian (Setiap Awal Shift 1)'
  ),
  'seal_bagtight' => array(
    'metode' => 'Test Fungsi',
    'alat' => 'Visual Control',
    'standard' => 'Mengembang',
    'durasi' => "3'",
    'pelaksanaan' => 'Harian (Setiap Awal Shift 1)'
  ),
  'container_up_down' => array(
    'metode' => 'Test Fungsi',
    'alat' => 'Visual Control',
    'standard' => 'Kontainer dapat naik dan turun',
    'durasi' => "2'",
    'pelaksanaan' => 'Harian (Setiap Awal Shift 1)'
  ),
  'shaking' => array(
    'metode' => 'Test Fungsi',
    'alat' => 'Visual Control',
    'standard' => 'Filter bag bergerak kanan dan kiri',
    'durasi' => "2'",
    'pelaksanaan' => 'Harian (Setiap Awal Shift 1)'
  ),
  'pressure_gauge_damper' => array(
    'metode' => 'Test Fungsi',
    'alat' => 'Visual Control',
    'standard' => '4-6 bar',
    'durasi' => "3'",
    'pelaksanaan' => 'Harian (Setiap Awal Shift 1)'
  ),
  'seal_container' => array(
    'metode' => 'Dicek',
    'alat' => 'Visual Control',
    'standard' => 'Tidak ada kerusakan/robek/gompal/bocor',
    'durasi' => "2'",
    'pelaksanaan' => 'Harian (Setiap Awal Shift 1)'
  ),
  'guarding_pengunci_kontainer' => array(
    'metode' => 'Test Fungsi',
    'alat' => 'Visual Control',
    'standard' => 'Pengunci tidak kendor',
    'durasi' => "1'",
    'pelaksanaan' => 'Harian (Setiap Awal Shift 1)'
  ),
  'container_mesh_dan_roda' => array(
    'metode' => 'Dicek',
    'alat' => 'Visual Control',
    'standard' => "Mesh normal tidak sobek\nRoda normal tidak macet/rusak",
    'durasi' => "2'",
    'pelaksanaan' => 'Harian (Setiap Awal Shift 1)'
  ),
  'filter_dan_bag_tight' => array(
    'metode' => 'Dicek',
    'alat' => 'Visual Control',
    'standard' => "Filter tidak sobek\nSeal tidak bocor",
    'durasi' => "10'",
    'pelaksanaan' => 'Harian (Setiap Awal Shift 1)'
  )
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
$page_element_id = 'fbd_glatt-add-' . random_str();
?>
<section class="page" id="<?php echo $page_element_id; ?>">
  <div class="bg-light p-3 mb-3">
    <div class="container-fluid">
      <h4 class="record-title">Add Autonomous Maintenance FBD Glatt</h4>
      <div>No: CR-PR-PR-1203.00 (25 Okt 2021)</div>
    </div>
  </div>
  <div class="container-fluid">
    <div class="row">
      <!-- Sticky Keterangan Pelaksanaan (Legend) Box -->
      <div class="col-md-3 pt-3 d-none d-md-block">
        <div style="position: sticky; top: 80px; z-index: 100;" class="p-3 bg-white border rounded shadow-sm">
          <h6 class="font-weight-bold text-center border-bottom pb-2 mb-3">Keterangan Pelaksanaan</h6>
          <table class="table table-borderless table-sm mb-0">
            <tbody>
              <tr>
                <td style="background-color: rgba(255, 255, 0, 0.4); border: 1px solid #ccc; width: 35px; border-radius: 4px;"></td>
                <td class="align-middle" style="font-size: 14px; padding-left: 10px;">Mingguan</td>
              </tr>
              <tr><td colspan="2" style="height: 5px; padding: 0;"></td></tr>
              <tr>
                <td style="background-color: rgba(0, 204, 255, 0.4); border: 1px solid #ccc; width: 35px; border-radius: 4px;"></td>
                <td class="align-middle" style="font-size: 14px; padding-left: 10px;">Bulanan / 2 Mingguan</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Main Form Area -->
      <div class="col-md-9">
        <div class="bg-light p-3 animated fadeIn page-content">
          <?php $this::display_page_errors(); ?>
          <form id="fbd_glatt-add-form" class="form page-form needs-validation" novalidate
            action="<?php print_link("fbd_glatt/add?csrf_token=$csrf_token") ?>" method="post">
            <input type="hidden" name="mesin" value="43">

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
                  $info = isset($part_details[$field]) ? $part_details[$field] : array('metode'=>'', 'alat'=>'', 'standard'=>'', 'durasi'=>'', 'pelaksanaan'=>'');
                  $img_key = isset($image_names[$field]) ? $image_names[$field] : str_replace('_', ' ', $field);
                  $image_path = 'assets/images/fbd_glatt/fbd_glatt ' . $img_key . '.png';

                  // Determine row highlight background color based on Pelaksanaan value
                  $pelaksanaan_lower = strtolower($info['pelaksanaan']);
                  $pelaksanaan_bg = '';
                  if (strpos($pelaksanaan_lower, 'mingguan') !== false && strpos($pelaksanaan_lower, '2 mingguan') === false) {
                    $pelaksanaan_bg = 'background-color: rgba(255, 255, 0, 0.4);';
                  } elseif (strpos($pelaksanaan_lower, 'bulanan') !== false || strpos($pelaksanaan_lower, '2 mingguan') !== false) {
                    $pelaksanaan_bg = 'background-color: rgba(0, 204, 255, 0.4);';
                  }
                ?>
                  <div class="card mb-3 part-card" data-part="<?php echo $field; ?>">
                    <div class="card-body">
                      <h5><?php echo $label; ?></h5>
                      <div class="row">
                        <div class="col-md-3">
                          <div class="border text-center p-2 text-muted"><a href="<?php print_link($image_path); ?>" class="part-image-link"><img class="img-fluid" src="<?php print_link($image_path); ?>"
                                alt="<?php echo $label; ?>"
                                onerror="this.style.display='none';this.parentNode.nextElementSibling.style.display='block';"></a><span
                              style="display:none">Gambar belum diunggah</span></div>
                        </div>
                        <div class="col-md-5">
                          <table class="table table-bordered table-sm mb-0">
                            <tr>
                              <th>Metode</th>
                              <td><?php echo nl2br(htmlspecialchars($info['metode'])); ?></td>
                            </tr>
                            <tr>
                              <th>Alat</th>
                              <td><?php echo nl2br(htmlspecialchars($info['alat'])); ?></td>
                            </tr>
                            <tr>
                              <th>Standard</th>
                              <td><?php echo nl2br(htmlspecialchars($info['standard'])); ?></td>
                            </tr>
                            <tr>
                              <th>Durasi</th>
                              <td><?php echo htmlspecialchars($info['durasi']); ?></td>
                            </tr>
                            <tr style="<?php echo $pelaksanaan_bg; ?>">
                              <th>Pelaksanaan</th>
                              <td><?php echo htmlspecialchars($info['pelaksanaan']); ?></td>
                            </tr>
                          </table>
                        </div>
                        <div class="col-md-4"><label class="d-block">Kondisi <span
                              class="text-danger">*</span></label><?php foreach (Menu::$Kondisi_Harian as $option) { ?>
                            <div class="custom-control custom-radio"><input required class="custom-control-input part-kondisi"
                                type="radio" id="<?php echo $field . '-' . $option['value']; ?>" name="<?php echo $field; ?>"
                                value="<?php echo $option['value']; ?>"><label class="custom-control-label"
                                for="<?php echo $field . '-' . $option['value']; ?>"><?php echo $option['label']; ?></label></div>
                          <?php } ?>
                        </div>
                      </div>
                      <div class="kendala-box border-top mt-3 pt-3" style="display:none">
                        <h6>Kendala selama AM</h6><textarea name="kendala_<?php echo $field; ?>" class="form-control mb-2"
                          placeholder="Jelaskan kendala"></textarea>
                        <div class="row">
                          <div class="col-md-3"><label>Kategori Tag</label><select name="kategori_tag_<?php echo $field; ?>"
                              class="custom-select">
                              <option value="">Pilih ...</option><?php foreach ($tag_options as $o) { ?>
                                <option value="<?php echo $o['value']; ?>"><?php echo $o['label']; ?></option><?php } ?>
                            </select></div>
                          <div class="col-md-3"><label>Korelasi Tag</label><select name="korelasi_tag_<?php echo $field; ?>"
                              id="korelasi-<?php echo $field; ?>" data-load-select-options="ketidaksesuaian-<?php echo $field; ?>"
                              class="custom-select">
                              <option value="">Pilih ...</option><?php foreach ($korelasi_options as $o) { ?>
                                <option value="<?php echo $o['value']; ?>"><?php echo $o['label']; ?></option><?php } ?>
                            </select></div>
                          <div class="col-md-3"><label>Klasifikasi Tag</label><select name="klasifikasi_tag_<?php echo $field; ?>"
                              class="custom-select">
                              <option value="">Pilih ...</option><?php foreach ($klasifikasi_options as $o) { ?>
                                <option value="<?php echo $o['value']; ?>"><?php echo $o['label']; ?></option><?php } ?>
                            </select></div>
                          <div class="col-md-3"><label>Kategori Ketidaksesuaian</label><select
                              name="kategori_ketidaksesuaian_<?php echo $field; ?>" id="ctrl-ketidaksesuaian-<?php echo $field; ?>"
                              data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>"
                              class="custom-select">
                              <option value="">Pilih ...</option>
                            </select></div>
                        </div>
                      </div>
                    </div>
                  </div>
                <?php } ?>
              </div>
            <?php } ?>

            <div class="text-center"><a class="btn btn-secondary mr-2" href="<?php print_link('fbd_glatt') ?>">Batal</a><button
                class="btn btn-primary" type="submit">Simpan AM <i class="fa fa-send"></i></button></div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
$(function () {
  $('.part-kondisi').on('change', function () {
    var card = $(this).closest('.part-card'),
        box = card.find('.kendala-box'),
        isNokChecked = card.find('.part-kondisi[value="NOK"]').is(':checked');
    box.toggle(isNokChecked);
    box.find('textarea,select').prop('required', isNokChecked);
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

  $(document).ajaxComplete(function (event, xhr, settings) {
    if (settings.url && settings.url.indexOf('sig_kategori_ketidaksesuaian_option_list') !== -1) {
      $('[id^="ctrl-ketidaksesuaian-"]').each(function () {
        var $select = $(this);
        var $validOptions = $select.find('option').filter(function () {
          return $(this).val() !== '';
        });

        if ($validOptions.length === 1 && $validOptions.text().trim().toLowerCase() === 'none') {
          $select.val($validOptions.val());
          $select.css({
            'pointer-events': 'none',
            'background-color': '#e9ecef',
            'background-image': 'none',
            '-webkit-appearance': 'none',
            '-moz-appearance': 'none',
            'appearance': 'none'
          }).attr('tabindex', '-1');
        } else if ($validOptions.length > 0) {
          $select.css({
            'pointer-events': 'auto',
            'background-color': '#ffffff',
            'background-image': '',
            '-webkit-appearance': '',
            '-moz-appearance': '',
            'appearance': ''
          }).removeAttr('tabindex');
        }
      });
    }
  });
});
</script>
