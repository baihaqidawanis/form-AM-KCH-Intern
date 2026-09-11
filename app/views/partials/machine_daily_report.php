<?php
$d = $this->view_data;
foreach ($d['rows'] as &$report_row) { if (empty($report_row['shift'])) { $report_row['shift'] = '1'; } }
unset($report_row);
$part_shift_schedules = $d['part_shift_schedules'] ?? array();
?>
<section class="page">
  <div class="container-fluid py-4 px-md-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <div>
        <h4 class="mb-1 font-weight-bold" style="color: var(--ak-text, #1D1D1F);">Report Harian &mdash; <?php echo htmlspecialchars($d['display_name']); ?></h4>
        <nav aria-label="breadcrumb">
          <ol class="breadcrumb bg-transparent p-0 m-0 small">
            <li class="breadcrumb-item"><a href="<?php print_link($d['machine_key']); ?>"><?php echo htmlspecialchars($d['display_name']); ?></a></li>
            <li class="breadcrumb-item active" aria-current="page">Report Harian</li>
          </ol>
        </nav>
      </div>
      <a class="btn btn-sm btn-outline-secondary" href="<?php print_link($d['machine_key']); ?>">
        <i class="fa fa-arrow-left mr-1"></i> Kembali ke Menu Mesin
      </a>
    </div>

    <!-- Banner Informasi Report Harian -->
    <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #F0F8EC 0%, #FFFFFF 100%); border-left: 4px solid var(--ak-green, #009639) !important;">
      <div class="card-body p-3 p-md-4">
        <div class="row align-items-center">
          <div class="col-md-4 mb-2 mb-md-0">
            <span class="text-muted small text-uppercase font-weight-bold d-block">Unit Mesin</span>
            <span class="h5 mb-0 font-weight-bold" style="color: var(--ak-green, #009639);"><?php echo htmlspecialchars($d['machine_name']); ?></span>
          </div>
          <div class="col-md-4 mb-2 mb-md-0">
            <span class="text-muted small text-uppercase font-weight-bold d-block">Tanggal Operasional</span>
            <span class="h6 mb-0 font-weight-bold text-dark"><?php echo format_am_date($d['operational_date']); ?></span>
          </div>
          <div class="col-md-4 text-md-right">
            <span class="badge badge-light border px-2 py-1 text-muted" style="font-size: 0.8rem; font-weight: 600;">
              <i class="fa fa-clock-o mr-1"></i> 06.45 &ndash; 05.45 (esok hari)
            </span>
          </div>
        </div>
      </div>
    </div>

    <!-- Tabel Report Harian Gabungan Shift -->
    <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
      <div class="card-body p-0">
        <div id="page-report-body" class="table-responsive">
          <table class="table table-hover table-bordered mb-0">
            <thead class="bg-light text-center">
              <tr>
                <th class="text-left align-middle px-3 py-3" style="width: 32%; min-width: 200px;">Part Pemeriksaan</th>
                <?php foreach ($d['rows'] as $row) { ?>
                  <th class="align-middle py-3" style="min-width: 140px;">
                    <div class="font-weight-bold text-dark" style="font-size: 0.95rem;">Shift <?php echo htmlspecialchars($row['shift']); ?></div>
                    <div class="small text-muted mb-1"><i class="fa fa-user-circle-o mr-1"></i><?php echo htmlspecialchars($row['user_create']); ?></div>
                    <a class="btn btn-xs btn-sm btn-outline-success px-2 py-0" style="font-size: 0.75rem; border-radius: 6px;" href="<?php print_link($d['machine_key'] . '/view/' . urlencode($row[$d['id_column']])); ?>">
                      <i class="fa fa-eye mr-1"></i> View
                    </a>
                  </th>
                <?php } ?>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($d['parts'] as $field => $label) { ?>
                <tr>
                  <th class="text-left align-middle px-3 py-2 text-dark" style="font-weight: 600; font-size: 0.88rem;"><?php echo htmlspecialchars($label); ?></th>
                  <?php foreach ($d['rows'] as $row) {
                    $shift = (string)$row['shift'];
                    $v = $row[$field] ?? '';
                    $scheduled_shifts = $part_shift_schedules[$row[$d['id_column']]][$field] ?? array('1');
                  ?>
                    <td class="text-center align-middle py-2">
                      <?php if ($v === 'NOK') { ?>
                        <span class="badge badge-danger px-2 py-1" style="font-size: 0.75rem;">NOK</span>
                      <?php } elseif ($v === 'OK') { ?>
                        <span class="badge badge-success px-2 py-1" style="font-size: 0.75rem;"><i class="fa fa-check mr-1"></i>OK</span>
                      <?php } elseif ($v === 'N/A') { ?>
                        <span class="badge badge-secondary px-2 py-1" style="font-size: 0.75rem;">N/A</span>
                      <?php } elseif (!in_array($shift, $scheduled_shifts, true)) { ?>
                        <span class="text-muted font-weight-bold">-</span>
                      <?php } else { ?>
                        <span class="text-muted font-weight-bold">-</span>
                      <?php } ?>
                    </td>
                  <?php } ?>
                </tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <div class="mt-4">
      <a class="btn btn-secondary px-3" href="<?php print_link($d['machine_key']); ?>">
        <i class="fa fa-arrow-left mr-1"></i> Kembali
      </a>
    </div>
  </div>
</section>