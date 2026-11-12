<?php $records = $this->view_data['records'] ?? array(); $csrf_token = Csrf::$token; ?>
<section class="page">
  <div class="bg-light p-3 mb-3"><div class="container-fluid"><h4 class="record-title">Kalender Operasional</h4><p class="text-muted mb-0">Sabtu–Minggu otomatis Holiday/Off mulai 12 November 2026. Admin dapat menetapkan hari operasional atau libur lainnya. Pengisian AM tetap diperbolehkan.</p></div></div>
  <div class="container-fluid"><div class="row"><div class="col-lg-9 mx-auto">
    <?php $this::display_page_errors(); ?>
    <div class="bg-light p-3 mb-3">
      <form method="post" action="<?php print_link("operational_calendar?csrf_token=$csrf_token") ?>" class="row align-items-end"><?php Html::csrf_token(); ?>
        <div class="col-md-3 form-group"><label>Tanggal</label><input required type="date" name="operational_date" class="form-control"></div>
        <div class="col-md-3 form-group"><label>Jenis Hari</label><select name="day_type" class="form-control"><option value="holiday">Holiday/Off</option><option value="working">Hari Operasional</option></select></div>
        <div class="col-md-3 form-group"><label>Keterangan Libur</label><input required type="text" name="label" class="form-control" value="Holiday/Off" maxlength="100"></div>
        <div class="col-md-3 form-group"><label>Catatan</label><input type="text" name="notes" class="form-control" placeholder="Opsional"></div>
        <div class="col-md-3 form-group"><button class="btn btn-primary btn-block" type="submit"><i class="fa fa-save"></i> Simpan</button></div>
      </form>
    </div>
    <p class="small text-muted">Daftar menampilkan pengaturan manual serta akhir pekan otomatis bulan ini dan bulan berikutnya. Aturan otomatis tetap berlaku untuk bulan-bulan selanjutnya. Hapus pengaturan manual untuk kembali ke aturan default.</p>
    <div class="bg-light p-3"><div class="table-responsive"><table class="table table-bordered table-hover bg-white mb-0"><thead><tr><th>Tanggal</th><th>Keterangan</th><th>Catatan</th><th>Dibuat Oleh</th><th style="min-width:170px">Aksi</th></tr></thead><tbody>
      <?php if (empty($records)) { ?><tr><td colspan="5" class="text-center text-muted">Belum ada penanda hari.</td></tr><?php } ?>
      <?php foreach ($records as $row) { $working = OperationalCalendar::isWorkingDay($row); ?>
      <tr>
        <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($row['operational_date']))); ?></td>
        <td><?php echo htmlspecialchars($row['label']); ?><?php if (!empty($row['automatic'])) { ?><div class="small text-muted">Otomatis</div><?php } ?></td>
        <td><?php echo htmlspecialchars($row['notes'] ?? ''); ?></td>
        <td><?php echo htmlspecialchars($row['created_by_username'] ?? '-'); ?></td>
        <td>
          <form method="post" action="<?php print_link('operational_calendar') ?>" class="mb-1"><?php Html::csrf_token(); ?>
            <input type="hidden" name="operational_date" value="<?php echo htmlspecialchars($row['operational_date']); ?>">
            <input type="hidden" name="day_type" value="<?php echo $working ? 'holiday' : 'working'; ?>">
            <input type="hidden" name="label" value="Holiday/Off">
            <input type="hidden" name="notes" value="<?php echo htmlspecialchars(empty($row['automatic']) ? ($row['notes'] ?? '') : ''); ?>">
            <button class="btn btn-sm <?php echo $working ? 'btn-outline-secondary' : 'btn-outline-success'; ?>" type="submit"><?php echo $working ? 'Jadikan Holiday/Off' : 'Jadikan Operasional'; ?></button>
          </form>
          <?php if (!empty($row['id'])) { ?><form method="post" action="<?php print_link('operational_calendar/delete/' . intval($row['id'])) ?>"><?php Html::csrf_token(); ?><button class="btn btn-sm btn-outline-danger" type="submit" onclick="return confirm('Hapus pengaturan manual? Tanggal ini akan kembali ke aturan default.');">Hapus Pengaturan</button></form><?php } ?>
        </td>
      </tr><?php } ?>
    </tbody></table></div></div>
  </div></div></div>
</section>
