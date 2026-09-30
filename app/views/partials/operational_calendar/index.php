<?php $records = $this->view_data['records'] ?? array(); $csrf_token = Csrf::$token; ?>
<section class="page">
  <div class="bg-light p-3 mb-3"><div class="container-fluid"><h4 class="record-title">Kalender Operasional</h4><p class="text-muted mb-0">Penanda hari libur/off tidak mengunci pengisian AM.</p></div></div>
  <div class="container-fluid"><div class="row"><div class="col-lg-9 mx-auto">
    <?php $this::display_page_errors(); ?>
    <div class="bg-light p-3 mb-3">
      <form method="post" action="<?php print_link("operational_calendar?csrf_token=$csrf_token") ?>" class="row align-items-end">
        <div class="col-md-3 form-group"><label>Tanggal</label><input required type="date" name="operational_date" class="form-control"></div>
        <div class="col-md-3 form-group"><label>Keterangan</label><input required type="text" name="label" class="form-control" value="Holiday/Off" maxlength="100"></div>
        <div class="col-md-4 form-group"><label>Catatan</label><input type="text" name="notes" class="form-control" placeholder="Opsional"></div>
        <div class="col-md-2 form-group"><button class="btn btn-primary btn-block" type="submit"><i class="fa fa-save"></i> Simpan</button></div>
      </form>
    </div>
    <div class="bg-light p-3"><div class="table-responsive"><table class="table table-bordered table-hover bg-white mb-0"><thead><tr><th>Tanggal</th><th>Keterangan</th><th>Catatan</th><th>Dibuat Oleh</th><th style="width:90px">Aksi</th></tr></thead><tbody>
      <?php if (empty($records)) { ?><tr><td colspan="5" class="text-center text-muted">Belum ada penanda hari.</td></tr><?php } ?>
      <?php foreach ($records as $row) { ?><tr><td><?php echo htmlspecialchars(date('d/m/Y', strtotime($row['operational_date']))); ?></td><td><?php echo htmlspecialchars($row['label']); ?></td><td><?php echo htmlspecialchars($row['notes'] ?? ''); ?></td><td><?php echo htmlspecialchars($row['created_by_username'] ?? '-'); ?></td><td><form method="post" action="<?php print_link('operational_calendar/delete/' . intval($row['id']) . '?csrf_token=' . $csrf_token) ?>"><button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Hapus penanda hari ini?')">Hapus</button></form></td></tr><?php } ?>
    </tbody></table></div></div>
  </div></div></div>
</section>
