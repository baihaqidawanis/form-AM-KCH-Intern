<?php
$d = $this->view_data; $model = new SharedController;
$machine_options = $d['machine_options'] ?? $model->sig_Line_option_list();
$month_names = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');

// Tentukan Kategori Area berdasarkan machine_key
$machine_key = $d['machine_key'] ?? '';
$report_machine_labels = array(
    'illapak_1_2' => array('illapak 1', 'illapak 2'),
    'illapak_3_12' => array('illapak 3', 'illapak 4', 'illapak 5', 'illapak 6', 'illapak 7', 'illapak 8', 'illapak 9', 'illapak 10', 'illapak 11', 'illapak 12'),
    'sig' => array('sig 5', 'sig 6'),
    'cosmec' => array('cosmec'),
    'best_pack' => array('injekt kemas & best pack'),
    'chimei' => array('chimei'),
    'conveyor_sig' => array('conveyor sig 5', 'conveyor sig 6'),
    'fbd_glatt' => array('fbd glatt'),
    'fbd_jaw_chuan' => array('fbd jaw chuan'),
    'granulator' => array('granulator'),
    'jihcheng' => array('jihcheng'),
    'jinsung_1_4' => array('jinsung 1', 'jinsung 2', 'jinsung 3', 'jinsung 4'),
    'jinsung_5' => array('jinsung 5'),
    'joeya' => array('joeya'),
    'mixing_tank' => array('mixing tank'),
    'supermixer' => array('supermixer'),
    'temach' => array('temach'),
    'unifill_b' => array('unifill b'),
    'check_weigher' => array('check weigher jinsung 1', 'check weigher jinsung 2', 'check weigher jinsung 3', 'check weigher jinsung 4', 'check weigher jinsung 5'),
    'storage_tank' => array('st liq no 1', 'st liq no 2', 'st liq no 3', 'st liq no 4', 'st liq no 5', 'st liq no 6', 'st liq no 7', 'st liq no 8', 'st liq no 9', 'st liq no 10', 'st liq no 11', 'st liq no 12', 'st liq no 13', 'st liq no 14', 'st liq no 15'),
    'storage_tank_tetrapak' => array('st liq 2 no 3', 'st liq 2 no 4', 'st liq 2 no 5', 'st liq 2 no 6', 'st liq 2 no 7', 'st liq 2 no 8', 'st liq 2 no 9', 'st liq 2 no 10', 'st liq 2 no 11', 'st liq 2 no 12', 'st liq 2 no 13', 'st liq 2 no 14', 'st liq 2 no 15', 'st liq 2 no 16', 'st liq 2 no 17'),
);
if (isset($report_machine_labels[$machine_key])) {
    $machine_options = array_values(array_filter($machine_options, function ($option) use ($report_machine_labels, $machine_key) {
        return in_array(strtolower(trim($option['label'])), $report_machine_labels[$machine_key], true);
    }));
}
$area_name = 'FILLING';
if (in_array($machine_key, array('chimei', 'temach', 'jihcheng', 'jinsung_1_4', 'jinsung_5', 'best_pack', 'check_weigher', 'conveyor_sig'), true)) {
    $area_name = 'PACKAGING 1';
} elseif (in_array($machine_key, array('cosmec', 'fbd_jaw_chuan', 'fbd_glatt', 'supermixer', 'storage_tank', 'storage_tank_tetrapak', 'mixing_tank', 'granulator'), true)) {
    $area_name = 'COMPOUNDING';
}

if (!function_exists('get_period_image_src')) {
    function get_period_image_src($rel_path) {
        $full_path = ROOT . ltrim($rel_path, '/\\');
        if (!empty($rel_path) && file_exists($full_path)) {
            $ext = pathinfo($full_path, PATHINFO_EXTENSION);
            $mime = ($ext === 'jpg' || $ext === 'jpeg') ? 'image/jpeg' : 'image/png';
            return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($full_path));
        }
        return print_link($rel_path);
    }
}
?>
<section class="page"><div class="container-fluid py-3">
<?php if (!empty($d['selection_only'])) { ?>
  <h4>Cetak Check Sheet Periode — <?php echo htmlspecialchars($d['display_name']); ?></h4>
  <p class="text-muted">Periode 1: tanggal 1–16. Periode 2: tanggal 17 sampai akhir bulan.</p>
  <form class="form-row" method="get" action="<?php print_link($d['machine_key'] . '/period_report'); ?>">
    <div class="col-md-3 form-group"><label>Mesin</label><select required class="custom-select" name="mesin"><option value="">Pilih mesin</option><?php foreach ($machine_options as $o) { ?><option value="<?php echo $o['value']; ?>"><?php echo htmlspecialchars($o['label']); ?></option><?php } ?></select></div>
    <div class="col-md-2 form-group"><label>Bulan</label><select class="custom-select" name="month"><?php foreach ($month_names as $n => $name) { ?><option value="<?php echo $n; ?>" <?php echo $n === intval(date('n')) ? 'selected' : ''; ?>><?php echo $name; ?></option><?php } ?></select></div>
    <div class="col-md-2 form-group"><label>Tahun</label><input class="form-control" type="number" name="year" value="<?php echo date('Y'); ?>" min="2020" max="2100"></div>
    <div class="col-md-2 form-group"><label>Periode</label><select class="custom-select" name="period"><option value="1">1 (1–16)</option><option value="2">2 (17–akhir bulan)</option></select></div>
    <div class="col-md-2 form-group align-self-end"><button class="btn btn-primary">Tampilkan Check Sheet</button></div>
  </form>
<?php } else { ?>
  <div id="page-report-body" class="check-sheet">
    <style>
      @page { size: A4 landscape; margin: 6mm; }
      .check-sheet { font-family: Arial, sans-serif; color: #000; font-size: 7px; }
      .check-sheet table { width: 100%; border-collapse: collapse; margin-bottom: 0px; }
      .check-sheet th, .check-sheet td { border: 1px solid #000; padding: 2px 3px; vertical-align: middle; }
      .check-sheet .head { font-size: 11px; font-weight: bold; text-align: center; line-height: 1.2; }
      .check-sheet .subhead { font-size: 8.5px; font-weight: bold; text-align: center; }
      .check-sheet .section { font-size: 7.5px; font-weight: bold; text-align: center; background: #fff; padding: 3px; }
      .check-sheet .meta td { height: 20px; font-size: 8px; }
      .check-sheet .photo { width: 62px; text-align: center; }
      .check-sheet .day { width: 16px; text-align: center; padding: 1px; }
      .check-sheet .mark-ok { color: #000; font-weight: bold; font-size: 9px; }
      .check-sheet .mark-nok { color: #000; font-weight: bold; font-size: 9px; }
      .check-sheet .signature { height: 24px; }
    </style>
    <table>
      <tr>
        <td style="width:14%; text-align:center; vertical-align:middle; padding:3px;">
          <img src="<?php echo get_period_image_src('assets/images/logo.png'); ?>" style="max-height:36px; max-width:90px;" alt="Logo Bintang Toedjoe">
        </td>
        <td class="head" style="width:26%; text-align:center;">
          PT. BINTANG TOEDJOE<br>
          <span class="subhead">Total Productive Maintenance<br>Site Pulo Gadung</span>
        </td>
        <td class="head" style="width:42%; text-align:center;">
          AUTONOMOUS MAINTENANCE STANDARD<br>
          <span class="subhead">Check Sheet Kerja</span><br>
          <em style="font-size:8px; font-weight:normal;">Saya Pakai, Saya Rawat</em>
        </td>
        <td class="subhead" style="width:18%; text-align:center; padding:2px;">
          Diperiksa Oleh<br><br>
          (Operator Produksi)<br>
          <div style="border-top:1px solid #000; margin-top:2px; padding-top:2px;">SPV/Fasilitator<br>Bulan/Tahun</div>
        </td>
      </tr>
    </table>
    <table class="meta">
      <tr>
        <td style="width:22%"><b>Area:</b> <?php echo htmlspecialchars($area_name); ?></td>
        <td style="width:43%"><b>Mesin / Line:</b> <?php echo htmlspecialchars($d['machine_name']); ?></td>
        <td style="width:35%"><b>Bulan / Tahun:</b> <?php echo $month_names[$d['month']] . ' ' . $d['year']; ?></td>
      </tr>
    </table>
    <table>
      <thead>
        <tr>
          <th>Gambar</th>
          <th style="width:18px;">No</th>
          <th>Nama Part</th>
          <th>Alat</th>
          <th>Metode</th>
          <th>Standar</th>
          <th style="width:28px;">Durasi</th>
          <th style="width:90px;">Pelaksanaan</th>
          <?php for ($day = $d['start_day']; $day <= $d['end_day']; $day++) { ?>
            <th class="day"><?php echo $day; ?></th>
          <?php } ?>
        </tr>
      </thead>
      <tbody>
      <?php
      $number = 0;
      $section = '';
      $total_cols = 8 + ($d['end_day'] - $d['start_day'] + 1);

      foreach (($d['part_details'] ?: array()) as $part) {
        if ($section !== $part['section']) {
          $section = $part['section'];
          ?>
          <tr>
            <td class="section" colspan="<?php echo $total_cols; ?>"><?php echo htmlspecialchars($section); ?>, diisi dengan memberikan tanda (√)</td>
          </tr>
          <?php
        }
        $number++;
        $field = $part['field_name'];
        $shift_schedule = trim((string)($part['shift_schedule'] ?? '1'));
        $shifts = array_filter(array_map('trim', explode(',', $shift_schedule)));
        if (empty($shifts)) { $shifts = array('1'); }
        $is_multi_shift = count($shifts) > 1;
        $rowspan = count($shifts);

        // Sub-baris pertama
        $first_shift = $shifts[0];
        $pelaksanaan_label = $is_multi_shift ? 'Awal Shift ' . $first_shift : $part['pelaksanaan'];
        ?>
        <tr>
          <td class="photo" rowspan="<?php echo $rowspan; ?>" style="background:#fff; text-align:center;">
            <?php if (!empty($part['image_path'])) { ?>
              <img style="max-width:58px;max-height:38px" src="<?php echo get_period_image_src($part['image_path']); ?>" alt="<?php echo htmlspecialchars($part['label']); ?>">
            <?php } ?>
          </td>
          <td rowspan="<?php echo $rowspan; ?>" style="text-align:center; background:#fff; font-weight:bold;"><?php echo $number; ?></td>
          <td rowspan="<?php echo $rowspan; ?>" style="background:#fff; font-weight:bold;"><?php echo htmlspecialchars($part['label']); ?></td>
          <td rowspan="<?php echo $rowspan; ?>" style="background:#fff;"><?php echo htmlspecialchars($part['alat']); ?></td>
          <td rowspan="<?php echo $rowspan; ?>" style="background:#fff;"><?php echo htmlspecialchars($part['metode']); ?></td>
          <td rowspan="<?php echo $rowspan; ?>" style="background:#fff;"><?php echo htmlspecialchars($part['standard']); ?></td>
          <td rowspan="<?php echo $rowspan; ?>" style="background:#fff; text-align:center;"><?php echo htmlspecialchars($part['durasi']); ?></td>
          <td style="background:#fff; font-weight:bold;"><?php echo htmlspecialchars($pelaksanaan_label); ?></td>
          <?php for ($day = $d['start_day']; $day <= $d['end_day']; $day++) {
            $entries = $d['checks'][$field][$day] ?? array();
            $cell_val = '';
            $c = '';
            foreach ($entries as $e) {
              if (!$is_multi_shift || (string)($e['shift'] ?? '') === (string)$first_shift || empty($e['shift'])) {
                $c = ($e['value'] ?? '') === 'NOK' ? 'mark-nok' : 'mark-ok';
                $cell_val = ($e['value'] ?? '') === 'NOK' ? '×' : '√';
                break;
              }
            }
          ?>
            <td class="day" style="background:#fff;"><span class="<?php echo $c; ?>"><?php echo $cell_val; ?></span></td>
          <?php } ?>
        </tr>
        <?php
        // Sub-baris untuk shift berikutnya (Shift 2, Shift 3)
        for ($s_idx = 1; $s_idx < count($shifts); $s_idx++) {
          $curr_shift = $shifts[$s_idx];
          $sub_pelaksanaan = 'Awal Shift ' . $curr_shift;
          ?>
          <tr>
            <td style="background:#fff; font-weight:bold;"><?php echo htmlspecialchars($sub_pelaksanaan); ?></td>
            <?php for ($day = $d['start_day']; $day <= $d['end_day']; $day++) {
              $entries = $d['checks'][$field][$day] ?? array();
              $cell_val = '';
              $c = '';
              foreach ($entries as $e) {
                if ((string)($e['shift'] ?? '') === (string)$curr_shift) {
                  $c = ($e['value'] ?? '') === 'NOK' ? 'mark-nok' : 'mark-ok';
                  $cell_val = ($e['value'] ?? '') === 'NOK' ? '×' : '√';
                  break;
                }
              }
            ?>
              <td class="day" style="background:#fff;"><span class="<?php echo $c; ?>"><?php echo $cell_val; ?></span></td>
            <?php } ?>
          </tr>
          <?php
        }
      }
      ?>
      <tr>
        <td class="signature" colspan="8" style="font-weight:bold; text-align:center;">Paraf Pelaksana</td>
        <?php for ($day = $d['start_day']; $day <= $d['end_day']; $day++) { ?>
          <td style="background:#fff;"></td>
        <?php } ?>
      </tr>
      </tbody>
    </table>
    <table style="width:100%; margin-top:4px; border:none;">
      <tr>
        <td style="border:none; text-align:left; font-size:7px; padding:0;">*dokumen yang sudah terisi penuh diarsip di Produksi selama 3 tahun</td>
        <td style="border:none; text-align:right; font-size:7px; padding:0;">CR-PR-PR-1203.00 (26 Jan 2026)<br>Halaman : 1/1</td>
      </tr>
    </table>
    <div style="text-align:center; margin-top:3px;">
      <?php if (!empty($d['all_approved'])) { ?><span style="border:1.5px solid #198754; color:#198754; font-weight:bold; font-size:10px; padding:1px 12px; display:inline-block; border-radius:3px; letter-spacing:1px;">APPROVED</span><?php } else { ?><span style="border:1.5px solid #d9534f; color:#d9534f; font-weight:bold; font-size:10px; padding:1px 12px; display:inline-block; border-radius:3px; letter-spacing:1px;">MENUNGGU APPROVAL</span><?php } ?>
    </div>
  </div>
  <div class="mt-3">
    <a class="btn btn-secondary" href="<?php print_link($d['machine_key'] . '/period_report'); ?>">Ganti Periode</a>
    <a class="btn btn-danger" target="_blank" href="<?php print_link($this->set_current_page_link(array('format' => 'pdf'))); ?>">Export PDF</a>
    <a class="btn btn-success" target="_blank" href="<?php print_link($this->set_current_page_link(array('format' => 'excel'))); ?>">Export Excel</a>
  </div>
<?php } ?></div></section>
