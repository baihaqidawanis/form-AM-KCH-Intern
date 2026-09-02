<div class="container py-5">
	<div class="row justify-content-center">
		<div class="col-md-6">
			<div class="card shadow-sm border-0">
				<div class="card-header bg-primary text-white text-center py-3">
					<h4 class="mb-0"><i class="fa fa-key"></i> Lupa Password</h4>
				</div>
				<div class="card-body p-4">
					<p class="text-muted text-center mb-4">
						Masukkan Email atau Username / NIK Anda yang terdaftar untuk mengatur ulang password secara mandiri.
					</p>
					<?php $this::display_page_errors(); ?>
					<form method="post" action="<?php print_link("passwordmanager/postresetlink?csrf_token=" . Csrf::$token); ?>">
						<input type="hidden" name="csrf_token" value="<?php echo Csrf::$token; ?>" />
						<div class="form-group mb-3">
							<label class="font-weight-bold" for="ctrl-email">Email atau Username / NIK <span class="text-danger">*</span></label>
							<div class="input-group">
								<div class="input-group-prepend">
									<span class="input-group-text"><i class="fa fa-user"></i></span>
								</div>
								<input value="<?php echo htmlspecialchars(get_form_field_value('email')); ?>" placeholder="Contoh: user@bintang7.com atau NIK1234" required id="ctrl-email" class="form-control" name="email" type="text" />
							</div>
						</div>
						<div class="mt-4 text-center">
							<button class="btn btn-primary btn-block py-2" type="submit">
								Lanjut Atur Ulang Password <i class="fa fa-arrow-right ml-1"></i>
							</button>
						</div>
					</form>
					<div class="alert alert-info mt-4 mb-0 text-left p-2" style="font-size: 13px;">
						<i class="fa fa-info-circle"></i> <strong>Catatan:</strong> Setelah password baru disimpan, akun akan masuk antrean aktivasi oleh Administrator untuk diverifikasi.
					</div>
					<div class="text-center mt-3">
						<a href="<?php print_link('index'); ?>" class="small text-muted"><i class="fa fa-arrow-left"></i> Kembali ke Login</a>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
