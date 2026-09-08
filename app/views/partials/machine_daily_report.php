<?php
$d = $this->view_data;
foreach ($d['rows'] as &$report_row) { if (empty($report_row['shift'])) { $report_row['shift'] = '1'; } }
unset($report_row);
$part_shift_schedules = $d['part_shift_schedules'] ?? array();
?>
<section class="page"><div class="container py-3">
  <h4>Report Harian &mdash; <?php echo htmlspecialchars($d['display_name']); ?></h4>
  <div class="alert alert-info">Mesin: <strong><?php echo htmlspecialchars($d['machine_name']); ?></strong> &middot; Tanggal operasional: <strong><?php echo htmlspecialchars($d['operational_date']); ?></strong> (06.45 sampai 05.45 esok hari)</div>
  <div id="page-report-body" class="table-responsive"><table class="table table-bordered table-sm"><thead><tr><th>Part</th>
    <?php foreach ($d['rows'] as $row) { ?><th>Shift <?php echo htmlspecialchars($row['shift']); ?><br><small><?php echo htmlspecialchars($row['user_create']); ?></small><br><a class="btn btn-sm btn-outline-primary mt-1" href="<?php print_link($d['machine_key'] . '/view/' . urlencode($row[$d['id_column']])); ?>">View</a></th><?php } ?>
  </tr></thead><tbody>
    <?php foreach ($d['parts'] as $field => $label) { ?><tr><th><?php echo htmlspecialchars($label); ?></th>
      <?php foreach ($d['rows'] as $row) { $shift = (string)$row['shift']; $v = $row[$field] ?? ''; $scheduled_shifts = $part_shift_schedules[$row[$d['id_column']]][$field] ?? array('1'); ?><td><?php if (!in_array($shift, $scheduled_shifts, true)) { ?><span class="text-muted font-weight-bold">-</span><?php } elseif ($v === 'NOK') { ?><span class="badge badge-danger">NOK</span><?php } elseif ($v === 'OK') { ?><span class="badge badge-success">OK</span><?php } else { ?><span class="text-muted font-weight-bold">-</span><?php } ?></td><?php } ?>
    </tr><?php } ?>
  </tbody></table></div>
  <a class="btn btn-secondary" href="<?php print_link($d['machine_key']); ?>">Kembali</a>
</div></section>