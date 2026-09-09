<?php
$d = $this->view_data; $model = new SharedController;
$machine_options = $d['machine_options'] ?? $model->sig_Line_option_list();
$month_names = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');

// Tentukan Kategori Area berdasarkan machine_key
$machine_key = $d['machine_key'] ?? '';
$report_machine_labels = array(
    'illapak_1_2' => array('ilapak 1', 'ilapak 2'),
    'illapak_3_12' => array('ilapak 3', 'ilapak 4', 'ilapak 5', 'ilapak 6', 'ilapak 7', 'ilapak 8', 'ilapak 9', 'ilapak 10', 'ilapak 11', 'ilapak 12'),
    'sig' => array('sig 5', 'sig 6'),
    'cosmec' => array('cosmec'),
    'best_pack' => array('injekt kemas & best pack'),
    'chimei' => array('chimei 12a (js 1)', 'chimei 4b (js 2)', 'chimei 10a (js 3)', 'chimei 11a (js 4)', 'chimei 6a (ilapak 1)', 'chimei 9a (ilapak 11)', 'chimei 5b (unifill b)', 'chimei 1a (sig 6)'),
    'conveyor_sig' => array('conveyor sig 5', 'conveyor sig 6'),
    'fbd_glatt' => array('fbd glatt'),
    'fbd_jaw_chuan' => array('fbd jaw chuan'),
    'granulator' => array('granulator'),
    'jihcheng' => array('jihcheng'),
    'jinsung_1_4' => array('jinsung 1', 'jinsung 2', 'jinsung 3', 'jinsung 4'),
    'jinsung_5' => array('jinsung 5'),
    'joeya' => array('joeya'),
    'mixing_tank' => array('mt silverson', 'mt tetrapak 1', 'mt tetrapak 2', 'mt tetrapak 3'),
    'supermixer' => array('supermixer'),
    'temach' => array('temach'),
    'unifill_b' => array('unifill b'),
    'check_weigher' => array('check weigher jinsung 1', 'check weigher jinsung 2', 'check weigher jinsung 3', 'check weigher jinsung 4', 'check weigher jinsung 5'),
    'storage_tank' => array('st liq no 1', 'st liq no 2', 'st liq no 3', 'st liq no 4', 'st liq no 5', 'st liq no 6', 'st liq no 7', 'st liq no 8', 'st liq no 9', 'st liq no 10', 'st liq no 11', 'st liq no 12', 'st liq no 13', 'st liq no 14', 'st liq no 15'),
    'storage_tank_tetrapak' => array('st liq 2 no 3', 'st liq 2 no 4', 'st liq 2 no 5', 'st liq 2 no 6', 'st liq 2 no 7', 'st liq 2 no 8', 'st liq 2 no 9', 'st liq 2 no 10', 'st liq 2 no 11', 'st liq 2 no 12', 'st liq 2 no 13', 'st liq 2 no 14', 'st liq 2 no 15', 'st liq 2 no 16', 'st liq 2 no 17'),
);
if (isset($report_machine_labels[$machine_key])) {
    $machine_options = array_values(array_filter($machine_options, function ($option) use ($report_machine_labels, $machine_key) {
        $canonical_label = str_replace('illapak', 'ilapak', strtolower(trim($option['label'])));
        return in_array($canonical_label, $report_machine_labels[$machine_key], true);
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
<?php } else { 
  $part_count = count($d['parts'] ?? array());
  if ($part_count > 12) {
      // 13-16 parts (e.g. Illapak) - ultra compact to ensure strict 1-page fit
      $css_sheet_fs = '6.2px';
      $css_pad = '0.8px 1.5px';
      $css_head_fs = '8.5px';
      $css_subhead_fs = '7px';
      $css_img_h = '14px';
      $css_img_w = '32px';
      $css_sig_h = '14px';
      $css_meta_h = '13px';
      $css_section_fs = '6.5px';
      $css_section_pad = '1px';
      $css_page_margin = '2mm 3mm';
  } elseif ($part_count > 9) {
      // 10-12 parts - medium compact
      $css_sheet_fs = '6.6px';
      $css_pad = '1.8px 2px';
      $css_head_fs = '9px';
      $css_subhead_fs = '7.5px';
      $css_img_h = '20px';
      $css_img_w = '38px';
      $css_sig_h = '18px';
      $css_meta_h = '14px';
      $css_section_fs = '6.8px';
      $css_section_pad = '1.5px';
      $css_page_margin = '3mm 3.5mm';
  } else {
      // 1-9 parts (e.g. Chimei, Cosmec, Granulator) - generous height to fill paper
      $css_sheet_fs = '7px';
      $css_pad = '3.5px 2.5px';
      $css_head_fs = '10px';
      $css_subhead_fs = '8px';
      $css_img_h = '28px';
      $css_img_w = '44px';
      $css_sig_h = '24px';
      $css_meta_h = '16px';
      $css_section_fs = '7px';
      $css_section_pad = '2px';
      $css_page_margin = '3.5mm 4mm';
  }
?>
  <div id="page-report-body" class="check-sheet">
    <style>
      @page { size: A4 landscape; margin: <?php echo $css_page_margin; ?>; }
      html, body { margin: 0; padding: 0; font-family: "DejaVu Sans", Arial, sans-serif; }
      .check-sheet { font-family: "DejaVu Sans", Arial, sans-serif; color: #000; font-size: <?php echo $css_sheet_fs; ?>; }
      table, .check-sheet table { width: 100%; border-collapse: collapse; margin-bottom: 0px; }
      th, td, .check-sheet th, .check-sheet td { border: 1px solid #000; padding: <?php echo $css_pad; ?>; vertical-align: middle; }
      .head { font-size: <?php echo $css_head_fs; ?>; font-weight: bold; text-align: center; line-height: 1.15; }
      .subhead { font-size: <?php echo $css_subhead_fs; ?>; font-weight: bold; text-align: center; }
      .section { font-size: <?php echo $css_section_fs; ?>; font-weight: bold; text-align: center; background: #fff; padding: <?php echo $css_section_pad; ?>; }
      .meta td { height: <?php echo $css_meta_h; ?>; font-size: 7.5px; padding: 1px 3px; }
      .photo { width: 5%; text-align: center; padding: 1px; }
      .photo img { max-width: <?php echo $css_img_w; ?>; max-height: <?php echo $css_img_h; ?>; display: block; margin: 0 auto; }
      .day { width: 1.5%; text-align: center; padding: 1px 0; font-size: 7px; }
      .mark-ok { color: #000; font-weight: bold; font-size: 8.5px; }
      .mark-nok { color: #000; font-weight: bold; font-size: 8.5px; }
      .mark-deactive { color: #856404; font-weight: bold; font-size: 9px; }
      .cell-deactive { background: #fff3cd !important; }
      .signature { height: <?php echo $css_sig_h; ?>; }
    </style>
    <table>
      <tr>
        <td style="width:14%; text-align:center; vertical-align:middle; padding:2px;">
          <img src="<?php echo get_period_image_src('assets/images/logo.png'); ?>" style="max-height:30px; max-width:85px;" alt="Logo Bintang Toedjoe">
        </td>
        <td class="head" style="width:26%; text-align:center;">
          PT. BINTANG TOEDJOE<br>
          <span class="subhead">Total Productive Maintenance<br>Site Pulo Gadung</span>
        </td>
        <td class="head" style="width:42%; text-align:center;">
          AUTONOMOUS MAINTENANCE STANDARD<br>
          <span class="subhead">Check Sheet Kerja</span><br>
          <em style="font-size:7.5px; font-weight:normal;">Saya Pakai, Saya Rawat</em>
        </td>
        <td style="width:18%; padding:0; vertical-align:top; border:none;">
          <table style="width:100%; border-collapse:collapse; margin:0; border:1px solid #000;">
            <tr>
              <td colspan="2" style="border:none; border-bottom:1px solid #000; text-align:center; font-weight:bold; font-size:7.5px; padding:1px;">
                Diperiksa Oleh
              </td>
            </tr>
            <tr>
              <td colspan="2" style="border:none; border-bottom:1px solid #000; height:20px; vertical-align:bottom; text-align:center; font-size:7px; padding-bottom:1px;">
                (Operator Produksi)
              </td>
            </tr>
            <tr>
              <td style="border:none; border-right:1px solid #000; border-bottom:1px solid #000; width:50%; font-size:7px; font-weight:bold; padding:2px 3px; text-align:left;">
                SPV/Fasilitator
              </td>
              <td style="border:none; border-bottom:1px solid #000; width:50%; font-size:7px; padding:2px 3px; text-align:center;">
              </td>
            </tr>
            <tr>
              <td style="border:none; border-right:1px solid #000; width:50%; font-size:7px; font-weight:bold; padding:2px 3px; text-align:left;">
                Bulan/Tahun
              </td>
              <td style="border:none; width:50%; font-size:7px; padding:2px 3px; text-align:center;">
                <?php echo $month_names[$d['month']] . ' ' . $d['year']; ?>
              </td>
            </tr>
          </table>
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
          <th style="width: 5.0%;">Gambar</th>
          <th style="width: 1.8%;">No</th>
          <th style="width: 20.0%;">Nama Part</th>
          <th style="width: 6.5%;">Alat</th>
          <th style="width: 5.5%;">Metode</th>
          <th style="width: 26.0%;">Standar</th>
          <th style="width: 2.2%;">Durasi</th>
          <th style="width: 9.0%;">Pelaksanaan</th>
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
        // Utamakan master schedule; bila legacy/default, pulihkan informasi multi-shift
        // dari teks pelaksanaan atau dari data shift yang benar-benar ada pada periode ini.
        $shift_schedule = trim((string)($part['shift_schedule'] ?? ''));
        $shifts = array_values(array_unique(array_filter(array_map('trim', explode(',', $shift_schedule)), function ($shift) {
          return in_array($shift, array('1', '2', '3'), true);
        })));
        $schedule_is_default = empty($shifts) || $shifts === array('1');
        if ($schedule_is_default) {
          $pelaksanaan = (string)($part['pelaksanaan'] ?? '');
          if (preg_match('/(?<!\d)1\s*,\s*2(?:\s*,\s*3)?(?!\d)/', $pelaksanaan, $matches)) {
            $shifts = array_values(array_unique(array_filter(array_map('trim', explode(',', $matches[0])))));
          }
        }
        $period_shifts = array();
        foreach (($d['checks'][$field] ?? array()) as $day_entries) {
          foreach ((array)$day_entries as $shift_key => $value) {
            if (in_array((string)$shift_key, array('2', '3'), true)) { $period_shifts[] = (string)$shift_key; }
          }
        }
        if (!empty($period_shifts)) { $shifts = array_values(array_unique(array_merge($shifts, $period_shifts))); }
        if (empty($shifts)) { $shifts = array('1'); }
        sort($shifts, SORT_NUMERIC);
        $is_multi_shift = count($shifts) > 1;
        $rowspan = count($shifts);

        // Sub-baris pertama
        $first_shift = $shifts[0];
        $pelaksanaan_label = $is_multi_shift ? 'Awal Shift ' . $first_shift : $part['pelaksanaan'];
        ?>
        <tr>
          <td class="photo" rowspan="<?php echo $rowspan; ?>" style="text-align:center; height:<?php echo $css_img_h; ?>;">
            <?php if (!empty($part['image_path'])) { ?>
              <img style="max-width:<?php echo $css_img_w; ?>;max-height:<?php echo $css_img_h; ?>;" src="<?php echo get_period_image_src($part['image_path']); ?>" alt="<?php echo htmlspecialchars($part['label']); ?>">
            <?php } ?>
          </td>
          <td rowspan="<?php echo $rowspan; ?>" style="text-align:center; font-weight:bold;"><?php echo $number; ?></td>
          <td rowspan="<?php echo $rowspan; ?>" style="font-weight:bold;"><?php echo htmlspecialchars($part['label']); ?></td>
          <td rowspan="<?php echo $rowspan; ?>"><?php echo htmlspecialchars($part['alat']); ?></td>
          <td rowspan="<?php echo $rowspan; ?>"><?php echo htmlspecialchars($part['metode']); ?></td>
          <td rowspan="<?php echo $rowspan; ?>"><?php echo htmlspecialchars($part['standard']); ?></td>
          <td rowspan="<?php echo $rowspan; ?>" style="text-align:center;"><?php echo htmlspecialchars($part['durasi']); ?></td>
          <td style="font-weight:bold;"><?php echo htmlspecialchars($pelaksanaan_label); ?></td>
          <?php for ($day = $d['start_day']; $day <= $d['end_day']; $day++) {
            $is_deactive = isset($d['deactivated_days'][$day]);
            $deact = $is_deactive ? $d['deactivated_days'][$day] : null;
            $tooltip = $is_deactive ? 'DEAKTIVASI: ' . htmlspecialchars($deact['reason'] ?? '') . ' | Oleh: ' . htmlspecialchars($deact['action_by_username'] ?? '-') . ' (' . htmlspecialchars($deact['started_at'] ?? '') . ')' : '';
            $entries = $d['checks'][$field][$day] ?? array();
            $cell_val = '';
            $c = '';
            $cell_style = '';
            if ($is_deactive) {
              $cell_style = 'background:#fff3cd !important; text-align:center;';
              $cell_val = '&mdash;';
              $c = 'mark-deactive';
            } elseif ($is_multi_shift) {
              $value = $entries[(string)$first_shift] ?? ($entries['__default__'] ?? '');
              if ($value !== '') { $c = $value === 'NOK' ? 'mark-nok' : 'mark-ok'; $cell_val = $value === 'NOK' ? '&times;' : '&radic;'; }
            } elseif (!empty($entries)) {
              // Untuk part single-shift, satu NOK pada hari tersebut selalu lebih penting daripada OK.
              $value = in_array('NOK', $entries, true) ? 'NOK' : reset($entries);
              $c = $value === 'NOK' ? 'mark-nok' : 'mark-ok';
              $cell_val = $value === 'NOK' ? '&times;' : '&radic;';
            }
          ?>
            <td class="day" style="<?php echo $cell_style; ?>" <?php if ($is_deactive) { ?>title="<?php echo $tooltip; ?>"<?php } ?>><span class="<?php echo $c; ?>"><?php echo $cell_val; ?></span></td>
          <?php } ?>
        </tr>
        <?php
        // Sub-baris untuk shift berikutnya (Shift 2, Shift 3)
        for ($s_idx = 1; $s_idx < count($shifts); $s_idx++) {
          $curr_shift = $shifts[$s_idx];
          $sub_pelaksanaan = 'Awal Shift ' . $curr_shift;
          ?>
          <tr>
            <td style="font-weight:bold;"><?php echo htmlspecialchars($sub_pelaksanaan); ?></td>
            <?php for ($day = $d['start_day']; $day <= $d['end_day']; $day++) {
              $is_deactive = isset($d['deactivated_days'][$day]);
              $deact = $is_deactive ? $d['deactivated_days'][$day] : null;
              $tooltip = $is_deactive ? 'DEAKTIVASI: ' . htmlspecialchars($deact['reason'] ?? '') . ' | Oleh: ' . htmlspecialchars($deact['action_by_username'] ?? '-') . ' (' . htmlspecialchars($deact['started_at'] ?? '') . ')' : '';
              $entries = $d['checks'][$field][$day] ?? array();
              $cell_val = '';
              $c = '';
              $cell_style = '';
              if ($is_deactive) {
                $cell_style = 'background:#fff3cd !important; text-align:center;';
                $cell_val = '&mdash;';
                $c = 'mark-deactive';
              } else {
                $value = $entries[(string)$curr_shift] ?? '';
                if ($value !== '') {
                  $c = $value === 'NOK' ? 'mark-nok' : 'mark-ok';
                  $cell_val = $value === 'NOK' ? '&times;' : '&radic;';
                }
              }
            ?>
              <td class="day" style="<?php echo $cell_style; ?>" <?php if ($is_deactive) { ?>title="<?php echo $tooltip; ?>"<?php } ?>><span class="<?php echo $c; ?>"><?php echo $cell_val; ?></span></td>
            <?php } ?>
          </tr>
          <?php
        }
      }
      ?>
      <tr>
        <td class="signature" colspan="8" style="font-weight:bold; text-align:center;">Paraf Pelaksana</td>
        <?php for ($day = $d['start_day']; $day <= $d['end_day']; $day++) {
          $is_deactive = isset($d['deactivated_days'][$day]);
        ?>
          <td style="<?php echo $is_deactive ? 'background:#fff3cd !important; text-align:center; font-size:6px; color:#856404; font-weight:bold;' : ''; ?>">
            <?php if ($is_deactive) { echo 'DEAKTIF'; } ?>
          </td>
        <?php } ?>
      </tr>
      </tbody>
    </table>
    <table style="width:100%; margin-top:2px; border:none;">
      <tr>
        <td style="border:none; text-align:left; font-size:6.8px; padding:0;"><strong>Keterangan:</strong> (&radic;) OK &nbsp;|&nbsp; (&times;) NOK &nbsp;|&nbsp; <span style="background:#fff3cd; color:#856404; padding:0 3px; font-weight:bold;">(&mdash;) Deaktivasi Mesin</span></td>
        <td style="border:none; text-align:right; font-size:6.8px; padding:0;">CR-PR-PR-1203.00 (26 Jan 2026)<br>Halaman : 1/1</td>
      </tr>
    </table>
    <?php if (!empty($d['deactivation_records'])) { ?>
    <div style="margin-top: 2px; padding: 2px 4px; background: #fff3cd; border: 1px solid #ffeeba; border-radius: 3px; font-size: 6.8px;">
      <strong style="color: #856404;"><i class="fa fa-info-circle"></i> Catatan Deaktivasi Mesin pada Periode Ini:</strong>
      <ul style="margin: 1px 0 0 12px; padding: 0;">
      <?php foreach ($d['deactivation_records'] as $dr) { ?>
        <li>
          Periode: <strong><?php echo date('d/m/Y H:i', strtotime($dr['started_at'])); ?></strong> s/d <strong><?php echo !empty($dr['ended_at']) ? date('d/m/Y H:i', strtotime($dr['ended_at'])) : 'Sekarang (Masih Deaktivasi)'; ?></strong>
          &mdash; Alasan: <strong><?php echo htmlspecialchars($dr['reason']); ?></strong>
          <?php if (!empty($dr['notes'])) { echo ' (<em>' . htmlspecialchars($dr['notes']) . '</em>)'; } ?>
          &mdash; Oleh: <strong><?php echo htmlspecialchars($dr['action_by_username']); ?></strong>
        </li>
      <?php } ?>
      </ul>
    </div>
    <?php } ?>
    <div style="text-align:center; margin-top:2px;">
      <?php if (!empty($d['all_approved'])) { ?><span style="border:1.5px solid #198754; color:#198754; font-weight:bold; font-size:8.5px; padding:0px 10px; display:inline-block; border-radius:3px; letter-spacing:1px;">APPROVED</span><?php } else { ?><span style="border:1.5px solid #d9534f; color:#d9534f; font-weight:bold; font-size:8.5px; padding:0px 10px; display:inline-block; border-radius:3px; letter-spacing:1px;">MENUNGGU APPROVAL</span><?php } ?>
    </div>
  </div>
  <div class="mt-3">
    <a class="btn btn-secondary" href="<?php print_link($d['machine_key'] . '/period_report'); ?>"><i class="fa fa-arrow-left"></i> Ganti Periode</a>
    <a class="btn btn-danger" target="_blank" href="<?php print_link($this->set_current_page_link(array('format' => 'pdf'))); ?>" title="PDF di-rotate 90 derajat mengikuti kontur vertikal A4 saat dicetak"><i class="fa fa-file-pdf-o"></i> Export PDF (Rotated A4)</a>
    <a class="btn btn-outline-danger" target="_blank" href="<?php print_link($this->set_current_page_link(array('format' => 'pdf', 'rotate' => '0'))); ?>" title="PDF format landscape standar tanpa rotasi"><i class="fa fa-file-pdf-o"></i> PDF Landscape</a>
    <a class="btn btn-success" target="_blank" href="<?php print_link($this->set_current_page_link(array('format' => 'excel'))); ?>"><i class="fa fa-file-excel-o"></i> Export Excel</a>
  </div>
<?php } ?></div></section>
