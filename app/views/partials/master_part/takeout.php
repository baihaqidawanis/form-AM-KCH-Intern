<?php $part = $this->view_data; ?>
<section class="page"><div class="container py-4"><h4>Takeout Part</h4><?php $this::display_page_errors(); ?>
<div class="alert alert-warning">Part <strong><?php echo htmlspecialchars($part['label']); ?></strong> tidak akan muncul pada form baru. Report sebelum waktu takeout tetap menampilkan part dan nilainya.</div>
<form method="post" action="<?php print_link('master_part/takeout/' . $part['id'] . '?csrf_token=' . Csrf::$token); ?>">
<?php Html::csrf_token(); ?><div class="form-group"><label for="takeout_reason">Alasan takeout <span class="text-danger">*</span></label><textarea required class="form-control" id="takeout_reason" name="takeout_reason"></textarea></div>
<a class="btn btn-secondary" href="<?php print_link('master_part/index/' . $part['machine_key']); ?>">Batal</a><button class="btn btn-warning" type="submit">Konfirmasi Takeout</button></form></div></section>
