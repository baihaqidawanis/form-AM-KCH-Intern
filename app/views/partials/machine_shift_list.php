<?php
/** Shared overview for any machine whose Master Data Part enables Shift 2/3. */
$d = $this->view_data;
$records = $d->records;
$part_fields = $d->part_fields;
$can_delete_reports = !empty($d->can_delete_reports);
$bulk_delete_url = $d->bulk_delete_url;
$groups = array();
foreach ($records as $row) {
  $date = !empty($row['operational_date']) ? $row['operational_date'] : substr($row['created_at'], 0, 10);
  $key = $row['mesin'] . '|' . $date;
  if (!isset($groups[$key])) {
    $groups[$key] = array('mesin' => $row['mesin'], 'machine_name' => $row['nm_mesin'] ?: '-', 'date' => $date, 'rows' => array());
  }
  $groups[$key]['rows'][] = $row;
}
?>
<section class="page">
  <div class="bg-light p-3 mb-3"><div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center">
	  <div>
		<h4 class="mb-0"><?php echo htmlspecialchars($this->page_title); ?></h4>
		<?php if (!empty($d->machine_serial)) { ?><div class="text-muted small"><?php echo htmlspecialchars($d->machine_serial); ?></div><?php } ?>
	  </div>
      <div>
        <?php if ($this->show_header) { ?><a class="btn btn-primary" href="<?php print_link($d->machine_key . '/add'); ?>"><i class="fa fa-plus"></i> Add New <?php echo htmlspecialchars($d->display_name); ?></a><?php } ?>
        <a class="btn btn-danger ml-2" href="<?php print_link($d->machine_key . '/period_report'); ?>"><i class="fa fa-file-pdf-o"></i> Export PDF</a>
      </div>
    </div>
    <?php
    $model = new SharedController;
    $all_options = $model->sig_Line_option_list();
    $clean_name = trim(preg_replace('/\b(line|mesin)\b/i', '', (string)$d->display_name));
    $machine_options = array_filter($all_options, function($o) use ($clean_name, $d) {
      return stripos($o['label'], $clean_name) !== false || stripos($clean_name, $o['label']) !== false || (stripos($d->machine_key, 'fbd') !== false && stripos($o['label'], 'fbd') !== false);
    });
    ?>
    <form class="search filter-form mt-2" action="<?php print_link($d->machine_key . '/list2'); ?>" method="get">
      <div class="form-row align-items-end">
        <div class="col-md-3 form-group mb-2"><label class="small text-muted mb-1" for="filter-date_from">Tanggal Dari</label><input value="<?php echo get_value('date_from'); ?>" class="form-control form-control-sm" type="date" id="filter-date_from" name="date_from"></div>
        <div class="col-md-3 form-group mb-2"><label class="small text-muted mb-1" for="filter-date_to">Tanggal Sampai</label><input value="<?php echo get_value('date_to'); ?>" class="form-control form-control-sm" type="date" id="filter-date_to" name="date_to"></div>
        <?php if (!empty($machine_options)) { ?>
          <div class="col-md-3 form-group mb-2"><label class="small text-muted mb-1" for="filter-mesin">Nama Mesin</label><select class="custom-select custom-select-sm" id="filter-mesin" name="mesin"><option value="">Semua Mesin</option><?php foreach ($machine_options as $o) { ?><option <?php echo (get_value('mesin') == $o['value']) ? 'selected' : ''; ?> value="<?php echo $o['value']; ?>"><?php echo htmlspecialchars($o['label']); ?></option><?php } ?></select></div>
          <div class="col-md-3 form-group mb-2"><label class="small text-muted mb-1" for="filter-search">Pencarian</label><input value="<?php echo get_value('search'); ?>" class="form-control form-control-sm" type="text" id="filter-search" name="search" placeholder="Cari user, approval, dll..."></div>
        <?php } else { ?>
          <div class="col-md-6 form-group mb-2"><label class="small text-muted mb-1" for="filter-search">Pencarian</label><input value="<?php echo get_value('search'); ?>" class="form-control form-control-sm" type="text" id="filter-search" name="search" placeholder="Cari user, approval, dll..."></div>
        <?php } ?>
      </div>
      <div class="form-row">
        <div class="col-12 text-right">
          <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter"></i> Terapkan Filter</button> 
          <a href="<?php print_link($d->machine_key . '/list2'); ?>" class="btn btn-sm btn-outline-secondary ml-1"><i class="fa fa-times"></i> Reset</a>
        </div>
      </div>
    </form>
  </div></div>
  <div class="container-fluid page-content">
    <?php $this::display_page_errors(); ?>
    <div id="page-report-body" class="table-responsive"><table class="table table-hover table-bordered table-sm">
      <thead class="bg-light"><tr>
        <?php if ($can_delete_reports) { ?><th class="td-checkbox"><label class="custom-control custom-checkbox custom-control-inline"><input class="toggle-check-all custom-control-input" type="checkbox" aria-label="Pilih semua report harian di halaman"><span class="custom-control-label"></span></label></th><?php } ?>
        <th>#</th><th>Tanggal</th><th>Mesin</th><th>Pembuat</th><th class="text-center text-nowrap" style="min-width: 96px;">Shift Terisi</th><th>Status</th><th>Approval</th><th>Approval Oleh</th><th>Tanggal Approval</th><th>User Update</th><th>Tanggal Update</th><th>Aksi</th>
      </tr></thead>
      <tbody><?php if ($groups) { $i = 0; foreach ($groups as $report) { $i++; $record_ids=array(); $shifts=array(); $creators=array(); $nok=false; $all_approved=true; $updaters=array(); $updated_at=null; $approval_date=null; $report_created_at=null;
        foreach ($report['rows'] as $row) {
          $record_ids[] = $row[$d->id_column]; if ($report_created_at === null || $row['created_at'] < $report_created_at) { $report_created_at = $row['created_at']; }
          $shift_val = !empty($row['shift']) ? $row['shift'] : '1';
          $shift_clean = preg_replace('/^shift\s*/i', '', trim((string)$shift_val));
          $shifts[] = $shift_clean ?: '1';
          $creators[] = $row['user_create'];
          if (($row['approval'] ?? null) !== 'Approved') { $all_approved = false; }
          if (!empty($row['updated_at'])) { $updated_at = $row['updated_at']; }
          if (!empty($row['user_perubah'])) { $updaters[] = $row['user_perubah']; }
          if (!empty($row['tanggal_perubahan'])) { $approval_date = $row['tanggal_perubahan']; }
          foreach ($part_fields as $part) { if (($row[$part] ?? '') === 'NOK') { $nok = true; } }
        }
      ?><tr class="<?php echo $nok ? 'table-danger' : ''; ?>">
        <?php if ($can_delete_reports) { ?><td class="td-checkbox"><label class="custom-control custom-checkbox custom-control-inline"><input class="optioncheck custom-control-input" value="<?php echo htmlspecialchars(implode(',', $record_ids)); ?>" type="checkbox" aria-label="Pilih report harian"><span class="custom-control-label"></span></label></td><?php } ?>
        <?php $shifts = array_values(array_unique($shifts)); natsort($shifts); ?><td><?php echo $i; ?></td><td><?php echo format_am_date($report_created_at ?: $report['date']); ?></td><td><?php echo htmlspecialchars($report['machine_name']); ?></td><td><?php echo htmlspecialchars(implode(', ', array_unique($creators))); ?></td>
        <td class="text-center align-middle" style="min-width: 96px; white-space: nowrap;">
          <?php if (!empty($shifts)) { ?>
            <div class="d-inline-flex align-items-center justify-content-center" style="gap: 5px;">
              <?php foreach ($shifts as $s_num) { ?>
                <span class="badge-shift-card" title="Shift <?php echo htmlspecialchars($s_num); ?>"><?php echo htmlspecialchars($s_num); ?></span>
              <?php } ?>
            </div>
          <?php } else { echo '-'; } ?>
        </td>
        <td><?php echo $nok ? '<span class="badge badge-danger">Ada NOK</span>' : '<span class="badge badge-success"><i class="fa fa-check-circle"></i> OK</span>'; ?></td>
        <td><?php echo $all_approved ? 'Approved' : '-'; ?></td><td><?php echo $all_approved ? 'System' : '-'; ?></td><td><?php echo $all_approved && $approval_date ? format_am_date($approval_date) : '-'; ?></td><td><?php echo htmlspecialchars(implode(', ', array_unique($updaters)) ?: '-'); ?></td><td><?php echo $updated_at ? format_am_date($updated_at) : '-'; ?></td>
        <td><a class="btn btn-sm btn-success" href="<?php print_link($d->machine_key . '/daily_report?mesin=' . urlencode($report['mesin']) . '&date=' . urlencode($report['date'])); ?>">Buka Report Harian</a></td>
      </tr><?php } } else { ?><tr><td colspan="<?php echo $can_delete_reports ? 13 : 12; ?>" class="text-center text-muted">Belum ada report harian <?php echo htmlspecialchars($d->display_name); ?>.</td></tr><?php } ?></tbody>
    </table></div>
    <?php if ($can_delete_reports) { ?><div class="mt-2"><button data-prompt-msg="Report harian terpilih, termasuk seluruh shift dan detail kendalanya, akan dihapus permanen. Lanjutkan?" data-display-style="confirm" data-url="<?php echo htmlspecialchars($bulk_delete_url, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-sm btn-danger btn-delete-selected d-none"><i class="fa fa-trash"></i> Hapus Report Harian Terpilih</button></div><?php } ?>
    <?php if ($this->show_pagination) { $pager = new Pagination($d->total_records, $d->record_count); $pager->route = $this->route; $pager->show_page_count = true; $pager->show_record_count = true; $pager->show_page_limit = true; $pager->limit_count = $this->limit_count; $pager->show_page_number_list = true; $pager->pager_link_range = 5; $pager->render(); } ?>
  </div>
</section>
