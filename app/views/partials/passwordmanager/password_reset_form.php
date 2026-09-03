<div class="container">
	<h3>Password Reset </h3>
	<hr />
	<div class="row">
		<div class="col-sm-6">
			<?php $page_link = $this->set_current_page_link(); ?>
			<form method="post" action="<?php print_link($page_link); ?>">
				<?php Html::csrf_token(); ?>
				<?php 
					$this :: display_page_errors();			
				?>
				<div class="form-group">
					<label>New Password</label>
					<div class="input-group">
						<input placeholder="Your New Password" required minlength="8" value="" class="form-control default" name="password" id="txtpass" type="password" />
						<div class="input-group-append cursor-pointer btn-toggle-password" style="cursor: pointer;">
							<span class="input-group-text"><i class="fa fa-eye"></i></span>
						</div>
					</div>
					<small class="form-text text-muted mt-1">Minimal 8 karakter, harus mengandung huruf besar, huruf kecil, angka, dan karakter spesial.</small>
				</div>
				<div class="form-group">
					<label>Confirm new password</label>
					<div class="input-group">
						<input placeholder="Confirm Password" required class="form-control default" name="cpassword" id="txtcpass" type="password" />
						<div class="input-group-append cursor-pointer btn-toggle-password" style="cursor: pointer;">
							<span class="input-group-text"><i class="fa fa-eye"></i></span>
						</div>
					</div>
				</div>
				<div class="mt-2 "><button  class="btn btn-success" type="submit">Change Password</button></div>
			</form>
		</div>
	</div>
</div>
