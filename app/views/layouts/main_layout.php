<?php
	// Set url Variable From Router Class
	$page_name = Router::$page_name;
	$page_action = Router::$page_action;
	$page_id = Router::$page_id;
	$body_class = "$page_name-" . str_ireplace('list','index', $page_action);
	$page_title = $this->get_page_title();
?>
<!DOCTYPE html>
<html>
	<head>
		<title><?php echo $page_title; ?></title>
		<meta http-equiv="content-type" content="text/html;charset=utf-8" />
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
		<link rel="shortcut icon" href="<?php print_link(SITE_FAVICON . '?v=' . time()); ?>" />
		<?php 
			Html ::  page_meta('theme-color',META_THEME_COLOR);
			Html ::  page_meta('author',META_AUTHOR); 
			Html ::  page_meta('keyword',META_KEYWORDS); 
			Html ::  page_meta('description',META_DESCRIPTION); 
			Html ::  page_meta('viewport',META_VIEWPORT);
			Html ::  page_css('font-awesome.min.css');
			Html ::  page_css('animate.css');
			Html ::  page_css('blueimp-gallery.css');
		?>
				<?php 
			Html ::  page_css('bootstrap-theme-pulse-darkblue.css');
			Html ::  page_css('custom-style.css');
			Html ::  page_css('apple-kalbe-theme.css');
		?>
		<?php
			Html ::  page_css('flatpickr.min.css');
			Html ::  page_css('bootstrap-editable.css');
			Html ::  page_css('dropzone.min.css');
			Html ::  page_js('jquery-3.3.1.min.js');
		?>
	</head>
	<?php 
		$page_id = "index";
		if(user_login_status() == true){
			$page_id = "main";
		}
	?>
	<body id="<?php echo $page_id ?>" class="with-login <?php echo $body_class ?>">
		<div id="page-wrapper">
			<!-- Show progress bar when ajax upload-->
			<div class="progress ajax-progress-bar">
				<div class="progress-bar"></div>
			</div>
			<?php 
				$this->render_view('appheader.php'); 
			?>
			<div id="main-content">
				<!-- Page Main Content Start -->
					<div id="page-content">
						<?php $this->render_body();?>
					</div>	
				<!-- Page Main Content [End] -->
				<!-- Page Footer Start -->
					<?php 
						$this->render_view('appfooter.php'); 
					?>
				<!-- Page Footer Ends -->
				<div class="flash-msg-container"><?php show_flash_msg(); ?></div>
				<!-- Modal page for displaying ajax page -->
				<div id="main-page-modal" class="modal fade" role="dialog">
					<div class="modal-dialog modal-lg">
						<div class="modal-content">
							<div class="modal-body p-0 reset-grids inline-page">
							</div>
							<div style="top: 5px; right:5px; z-index: 999;" class="position-absolute">
								<button type="button" class="btn btn-sm btn-danger" data-dismiss="modal">&times;</button>
							</div>
						</div>
					</div>
				</div>
				<!-- Modal page for displaying record delete prompt -->
				<div class="modal fade" id="delete-record-modal-confirm" tabindex="-1" role="dialog" aria-labelledby="delete-record-modal-confirm" aria-hidden="true">
					<div class="modal-dialog" role="document">
						<div class="modal-content">
							<div class="modal-header">
								<h5 class="modal-title">Delete record</h5>
								<button type="button" class="close" data-dismiss="modal" aria-label="Close"> 
									<span aria-hidden="true">&times;</span> 
								</button>
							</div>
							<div id="delete-record-modal-msg" class="modal-body"></div>
							<div class="modal-footer">
								<button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
								<a href="" id="delete-record-modal-btn" class="btn btn-primary">Delete</a> 
							</div>
						</div>
					</div>
				</div>
				<!-- Image Preview Component [Start] -->
				<div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls">
					<div class="slides"></div>
					<h3 class="title"></h3>
					<a class="prev">&#8249;</a>
					<a class="next">&#8250;</a>
					<a class="close">&times;</a>
					<a class="play-pause"></a>
					<ol class="indicator"></ol>
				</div>
				<!-- Image Preview Component [End] -->
				<!-- Part Image Lightbox (same-tab zoom for part photos) [Start] -->
				<div id="part-image-lightbox">
					<span id="part-image-lightbox-close">&times;</span>
					<img id="part-image-lightbox-img" src="" alt="">
				</div>
				<style>
					#part-image-lightbox {
						display: none;
						position: fixed;
						top: 0; left: 0; right: 0; bottom: 0;
						width: 100%; height: 100%;
						background: rgba(0, 0, 0, 0.9);
						z-index: 10000;
						text-align: center;
						align-items: center;
						justify-content: center;
					}
					#part-image-lightbox.show {
						display: flex;
					}
					#part-image-lightbox img {
						max-width: 90%;
						max-height: 90%;
						box-shadow: 0 0 25px rgba(0, 0, 0, 0.6);
					}
					#part-image-lightbox-close {
						position: fixed;
						top: 15px;
						right: 25px;
						color: #fff;
						font-size: 40px;
						font-weight: bold;
						line-height: 1;
						cursor: pointer;
						z-index: 10001;
					}
				</style>
				<!-- Part Image Lightbox [End] -->
				<template id="page-loading-indicator">
					<div class="p-2 text-center m-2 text-muted m-auto">
						<div class="ajax-loader"></div>
						<h4 class="p-3 mt-2 font-weight-light">Loading...</h4>
					</div>
				</template>
				<template id="page-saving-indicator">
					<div class="p-2 text-center m-2 text-muted">
						<div class="lds-dual-ring"></div>
						<h4 class="p-3 mt-2 font-weight-light">Saving...</h4>
					</div>
				</template>
				<template id="inline-loading-indicator">
					<div class="p-2 text-center d-flex justify-content-center">
						<span class="loader mr-3"></span>
						<span class="font-weight-bold">Loading...</span>
					</div>
				</template>
			</div>
		</div>
		<script>
			var siteAddr = '<?php echo SITE_ADDR; ?>';
			var defaultPageLimit = <?php echo MAX_RECORD_COUNT; ?>;
			var csrfToken = '<?php echo Csrf :: $token; ?>';
			$(document).on('click', '.part-image-link', function(e){
				e.preventDefault();
				$('#part-image-lightbox-img').attr('src', $(this).attr('href'));
				$('#part-image-lightbox').addClass('show');
			});
			$(document).on('click', '#part-image-lightbox, #part-image-lightbox-close', function(){
				$('#part-image-lightbox').removeClass('show');
			});
			// Opsi lanjutan Red Tag hanya tersedia bila status actionable terakhir
			// untuk mesin+part adalah NOK/On Process. N/A tidak memutus rantai.
			(function(){
				$(function(){
					var $form = $('form.page-form').first();
					if (!$form.length) { return; }
					var operationalDayFlag = <?php echo !empty($this->operational_day_flag) ? json_encode($this->operational_day_flag, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) : 'null'; ?>;
					if (operationalDayFlag) {
						var flagLabel = $('<div>').text(operationalDayFlag.label || 'Holiday/Off').html();
						var flagNotes = operationalDayFlag.notes ? '<div class="small mt-1">' + $('<div>').text(operationalDayFlag.notes).html() + '</div>' : '';
						$form.prepend('<div class="alert alert-secondary border-secondary shadow-sm mb-3"><strong><i class="fa fa-calendar"></i> ' + flagLabel + '</strong><div>Hari ini ditandai sebagai hari libur/off. Pengisian AM tetap diperbolehkan dan akan diberi shading pada report.</div>' + flagNotes + '</div>');
					}
					var $machine = $form.find('[name="mesin"]').first();
					var action = $form.attr('action') || '';
					var recIdMatch = action.match(/\/edit_data\/(\d+)/i);
					var recId = recIdMatch ? recIdMatch[1] : '';
					var isAddForm = /\/add(?:\?|$)/i.test(action);
					var preselectedMachineId = <?php echo !empty($this->preselected_machine_id) ? intval($this->preselected_machine_id) : 0; ?>;
					var preselectedMachineName = <?php echo !empty($this->preselected_machine_name) ? json_encode($this->preselected_machine_name, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) : 'null'; ?>;
					if (isAddForm && preselectedMachineId) {
						if ($machine.is('select')) {
							$machine.val(String(preselectedMachineId)).prop('disabled', true);
							$('<input>', {type: 'hidden', name: 'mesin', value: preselectedMachineId}).insertAfter($machine);
							$machine.closest('.form-group').hide();
						}
						// Ringkasan shift bawaan tiap modul digabung dengan unit agar tidak
						// muncul dua tombol penggantian konteks yang melakukan hal sama.
						$form.prev('.alert.alert-info').remove();
						var selectedShift = $form.find('[name="shift"]').first().val();
						var $context = $('<div class="alert alert-info mb-3"></div>');
						$context.append($('<strong></strong>').text('Shift ' + selectedShift + ' - Unit: ' + (preselectedMachineName || preselectedMachineId)));
						$context.append(' - kombinasi telah diperiksa. ');
						$context.append($('<a class="btn btn-sm btn-outline-primary ml-2"></a>').attr('href', window.location.pathname).text('Ganti Shift / Mesin'));
						$form.prepend($context);
					}
					var endpoint = action.replace(/\/(add|edit_data)(?:\/[^?]*)?(?:\?.*)?$/i, '/on_process_options');
					var statusRequest = null;
					function updateDuplicateGuard(data){
						if (!isAddForm) { return; }
						var duplicate = data && data.duplicate;
						var $banner = $form.find('#am-duplicate-warning');
						var $submitBtns = $form.find('button[type="submit"], input[type="submit"]');
						$form.data('duplicateLocked', !!duplicate);
						if (duplicate) {
							if (!$banner.length) {
								$banner = $('<div id="am-duplicate-warning" class="alert alert-warning border-warning shadow-sm mb-3"></div>');
								$form.prepend($banner);
							}
							var shift = $form.find('[name="shift"]').val();
							var shiftText = shift ? ' Shift ' + $('<div>').text(shift).html() + ',' : '';
							var creator = duplicate.user_create ? ' oleh <strong>' + $('<div>').text(duplicate.user_create).html() + '</strong>' : '';
							var createdAt = duplicate.created_at ? ' pada ' + $('<div>').text(duplicate.created_at).html() : '';
							$banner.html('<strong><i class="fa fa-exclamation-triangle"></i> AM sudah diisi.</strong><div class="mt-1">Mesin ini untuk' + shiftText + ' tanggal operasional <strong>' + $('<div>').text(data.operational_date || '').html() + '</strong> sudah tersimpan' + creator + createdAt + '. Pilih mesin atau shift lain.</div>').show();
							$submitBtns.prop('disabled', true).addClass('disabled').attr('title', 'AM untuk mesin dan shift ini sudah diisi');
						} else {
							if ($banner.length) { $banner.hide(); }
							if (!$('#machine-deactivation-banner:visible, #period-signature-lock-banner:visible').length && !$form.data('partConfigLoading') && !$form.data('partConfigError')) {
								$submitBtns.prop('disabled', false).removeClass('disabled').removeAttr('title');
							}
						}
					}
					function applyPartOverrides(overrides){
						overrides = overrides || {};
						$form.find('.part-card[data-part]').each(function(){
							var $card = $(this), field = String($card.data('part') || ''), override = overrides[field];
							var $durationCell = $card.find('th').filter(function(){ return $.trim($(this).text()).toLowerCase() === 'durasi'; }).first().next('td');
							if ($durationCell.length && $durationCell.data('defaultDuration') === undefined) { $durationCell.data('defaultDuration', $durationCell.text()); }
							if ($durationCell.length) { $durationCell.text(override && override.durasi !== null ? override.durasi : $durationCell.data('defaultDuration')); }
							$card.find('.part-na-note, input.part-na-value').remove();
							var applicable = !override || override.is_applicable;
							$card.toggleClass('part-not-applicable', !applicable).toggle(applicable);
							if (!applicable) {
								$card.find('.part-kondisi').prop('checked', false).prop('disabled', true).removeAttr('required').trigger('change');
							} else {
								$card.find('.part-kondisi').prop('disabled', false).attr('required', 'required');
							}
						});
					}
					function updateOnProcess(){
						var machineId = $machine.val();
						var $options = $form.find('input.part-kondisi[value="ON_PROCESS_RED_TAG"]');
						$form.find('.rtwt-process-value, .rtwt-process-note').remove();
						$form.find('.part-card.part-rtwt-active').removeClass('part-rtwt-active').find('.part-kondisi').prop('disabled', false);
						$options.prop('disabled', true).closest('.custom-control').addClass('d-none').hide();
						if (statusRequest) { statusRequest.abort(); statusRequest = null; }
						$form.data('partConfigLoading', false).data('partConfigError', false);
						$form.find('#part-config-load-error').remove();
						// Jangan mempertahankan peringatan duplikasi milik unit sebelumnya
						// ketika operator mengganti pilihan mesin.
						$form.data('duplicateLocked', false);
						$form.find('#am-duplicate-warning').hide();
						if ((!machineId && !recId) || endpoint === action) { applyPartOverrides({}); updateDuplicateGuard(null); return; }
						$form.data('partConfigLoading', true);
						$form.find('button[type="submit"], input[type="submit"]').prop('disabled', true).addClass('disabled').attr('title', 'Konfigurasi part sedang dimuat');
						var params = {};
						if (machineId) { params.mesin = machineId; }
						if (recId) { params.rec_id = recId; }
						var selectedShift = $form.find('[name="shift"]').val();
						if (selectedShift) { params.shift = selectedShift; }
						var request = $.getJSON(endpoint, params);
						statusRequest = request;
						request.done(function(data){
							if (String($machine.val() || '') !== String(machineId || '')) { return; }
							if (!data || !data.success) {
								$form.data('partConfigLoading', false).data('partConfigError', true);
								$form.prepend('<div id="part-config-load-error" class="alert alert-danger"><strong>Konfigurasi part gagal dimuat.</strong> Pilih ulang mesin atau muat ulang halaman sebelum mengisi.</div>');
								return;
							}
							$form.data('partConfigLoading', false).data('partConfigError', false);
							applyPartOverrides(data && data.success ? data.part_overrides : {});
							var allowed = data && data.success ? data.fields : [];
							var rtwtFields = data && data.success && $.isArray(data.rtwt_fields) ? data.rtwt_fields : [];
							var rtwtTickets = data && data.success && data.rtwt_tickets ? data.rtwt_tickets : {};
							$options.each(function(){
								var enabled = !$(this).closest('.part-card').hasClass('part-not-applicable') && allowed.indexOf(this.name) !== -1;
								var $ctrl = $(this).prop('disabled', !enabled).closest('.custom-control');
								$ctrl.toggleClass('d-none', !enabled);
								if (enabled) {
									$ctrl.show();
								} else {
									$ctrl.hide();
									if (this.checked) {
										this.checked = false;
										$(this).closest('.part-card').find('.part-kondisi').first().trigger('change');
									}
								}
							});
							if (isAddForm) {
								$.each(rtwtFields, function(_, field){
									var $card = $form.find('.part-card[data-part="' + field + '"]').first();
									if (!$card.length || $card.hasClass('part-not-applicable')) { return; }
									var $onProcess = $card.find('.part-kondisi[value="ON_PROCESS_RED_TAG"]').first();
									$onProcess.closest('.custom-control').removeClass('d-none').show();
									$onProcess.prop('disabled', false).prop('checked', true).trigger('change');
									$card.find('.part-kondisi').prop('disabled', true).removeAttr('required');
									$card.append($('<input>', { type: 'hidden', name: field, value: 'ON_PROCESS_RED_TAG', class: 'rtwt-process-value' }));
									$card.addClass('part-rtwt-active');
									var ticketNumber = String(rtwtTickets[field] || '').trim();
									var $note = $('<div>', { 'class': 'rtwt-process-note' }).text('On Process RTWT Mesin');
									if (ticketNumber) { $note.append(document.createTextNode(' - ' + ticketNumber)); }
									$card.find('.part-kondisi').first().closest('.col-md-4').prepend($note);
								});
							}
							updateDuplicateGuard(data);
							// updateDuplicateGuard hanya mengatur form tambah. Pada form edit,
							// tombol sebelumnya dinonaktifkan saat konfigurasi dimuat dan harus
							// dibuka lagi setelah respons berhasil diterima.
							if (!isAddForm && !$('#machine-deactivation-banner:visible, #period-signature-lock-banner:visible').length) {
								$form.find('button[type="submit"], input[type="submit"]').prop('disabled', false).removeClass('disabled').removeAttr('title');
							}
						}).fail(function(xhr, status){
							if (status === 'abort') { return; }
							$form.data('partConfigLoading', false).data('partConfigError', true);
							$options.prop('disabled', true).closest('.custom-control').addClass('d-none').hide();
							$form.find('#part-config-load-error').remove();
							$form.prepend('<div id="part-config-load-error" class="alert alert-danger"><strong>Konfigurasi part gagal dimuat.</strong> Pilih ulang mesin atau muat ulang halaman sebelum mengisi.</div>');
						}).always(function(){
							if (statusRequest === request) { statusRequest = null; }
						});
					}
					$machine.on('change', updateOnProcess); updateOnProcess();
					$form.on('click', '.btn-check-section-ok', function(){
						setTimeout(function(){ $form.find('.part-not-applicable .part-kondisi').prop('checked', false); }, 0);
					});

					var nokPhotoTargetBytes = 2 * 1024 * 1024;
					function nokPhotoSize(bytes) { return (bytes / (1024 * 1024)).toFixed(2) + ' MB'; }
					function showNokPhotoPreview($preview, file) {
						var reader = new FileReader();
						reader.onload = function(e) { $preview.find('.nok-photo-preview').attr('src', e.target.result); $preview.removeClass('d-none'); };
						reader.readAsDataURL(file);
					}
					function compressNokPhoto(file, done) {
						if (!window.URL || !window.URL.createObjectURL || !window.HTMLCanvasElement || !HTMLCanvasElement.prototype.toBlob) {
							done(null, 'Browser ini belum mendukung kompresi foto. Gunakan foto asli atau Upload dari perangkat.'); return;
						}
						var sourceUrl = URL.createObjectURL(file), image = new Image();
						image.onload = function() {
							var maxDimension = 1920, baseScale = Math.min(1, maxDimension / Math.max(image.naturalWidth || image.width, image.naturalHeight || image.height));
							var attempts = 0;
							function render(scale, quality) {
								var canvas = document.createElement('canvas');
								canvas.width = Math.max(1, Math.round((image.naturalWidth || image.width) * scale));
								canvas.height = Math.max(1, Math.round((image.naturalHeight || image.height) * scale));
								canvas.getContext('2d').drawImage(image, 0, 0, canvas.width, canvas.height);
								canvas.toBlob(function(blob) {
									attempts++;
									if (!blob) { URL.revokeObjectURL(sourceUrl); done(null, 'Foto tidak dapat dikompres.'); return; }
									if (blob.size <= nokPhotoTargetBytes) { URL.revokeObjectURL(sourceUrl); done(blob); return; }
									if (attempts >= 10) { URL.revokeObjectURL(sourceUrl); done(null, 'Foto belum dapat diperkecil hingga 2 MB. Pilih foto lain atau gunakan ukuran kamera lebih kecil.'); return; }
									if (quality > 0.55) { render(scale, quality - 0.10); } else { render(scale * 0.80, 0.82); }
								}, 'image/jpeg', quality);
							}
							render(baseScale, 0.88);
						};
						image.onerror = function() { URL.revokeObjectURL(sourceUrl); done(null, 'Foto tidak dapat dibaca untuk dikompres.'); };
						image.src = sourceUrl;
					}
					$form.on('change', '.nok-photo-input', function(){
						var $input = $(this), $box = $input.closest('.nok-photo-box'), $preview = $input.siblings('.nok-photo-preview-wrap'), $state = $input.siblings('.nok-photo-state'), $compress = $input.siblings('.nok-photo-compress');
						var file = this.files && this.files[0];
						if (file && file.size > 5 * 1024 * 1024) {
							alert('Foto Before maksimal 5 MB.'); $input.val(''); file = null;
						}
						if (file && file.type && ['image/jpeg', 'image/png', 'image/webp'].indexOf(file.type) === -1) {
							alert('Foto Before harus JPEG, PNG, atau WebP.'); $input.val(''); file = null;
						}
						$input.siblings('.nok-photo-cancel').toggleClass('d-none', !file);
						if (file) {
							$input.data('nokOriginalBytes', file.size);
							$state.text('Foto dipilih: ' + file.name + ' (' + nokPhotoSize(file.size) + ')').removeClass('d-none');
							$compress.removeClass('d-none').prop('disabled', false).html('<i class="fa fa-compress"></i> Kompres Foto &lt; 2 MB');
							$box.removeClass('border border-danger p-2 rounded'); showNokPhotoPreview($preview, file);
						} else {
							$state.text('').addClass('d-none'); $compress.addClass('d-none'); $preview.find('.nok-photo-preview').removeAttr('src'); $preview.addClass('d-none');
						}
					});
					$form.on('click', '.nok-photo-compress', function(){
						var $button = $(this), $box = $button.closest('.nok-photo-box'), $input = $box.find('.nok-photo-input').first(), input = $input[0], file = input && input.files && input.files[0];
						if (!file) { return; }

						$button.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Menyiapkan foto...');
						compressNokPhoto(file, function(blob, error) {
							if (error) { $button.prop('disabled', false).html('<i class="fa fa-compress"></i> Kompres Foto &lt; 2 MB'); alert(error); return; }
							try {
								var compressed = new File([blob], file.name.replace(/\.[^.]+$/, '') + '-compressed.jpg', { type: 'image/jpeg' });
								var transfer = new DataTransfer(); transfer.items.add(compressed); input.files = transfer.files;
								var originalBytes = $input.data('nokOriginalBytes') || file.size;
								$box.find('.nok-photo-state').text('Foto dikompres: ' + nokPhotoSize(originalBytes) + ' menjadi ' + nokPhotoSize(compressed.size)).removeClass('d-none');
								$button.addClass('d-none'); showNokPhotoPreview($box.find('.nok-photo-preview-wrap'), compressed);
							} catch (err) {
								$button.prop('disabled', false).html('<i class="fa fa-compress"></i> Kompres Foto &lt; 2 MB');
								alert('Browser ini tidak dapat memakai hasil kompresi. Foto asli tetap dipakai.');
							}
						});
					});
					$form.on('change', '.nok-camera-input', function(){
						var cameraInput = this, file = cameraInput.files && cameraInput.files[0];
						var uploadInput = $(cameraInput).closest('.nok-photo-box').find('.nok-photo-input').first()[0];
						if (!file || !uploadInput) { return; }
						try {
							var transfer = new DataTransfer(); transfer.items.add(file); uploadInput.files = transfer.files;
							cameraInput.value = ''; $(uploadInput).trigger('change');
						} catch (err) {
							cameraInput.value = '';
							alert('Browser ini tidak dapat meneruskan hasil kamera ke form. Gunakan Upload dari perangkat.');
						}
					});
					$form.on('click', '.nok-photo-cancel', function(){
						$(this).siblings('.nok-photo-input').val(''); $(this).siblings('.nok-photo-state').text('').addClass('d-none'); $(this).siblings('.nok-photo-compress').addClass('d-none'); $(this).siblings('.nok-photo-preview-wrap').find('.nok-photo-preview').removeAttr('src'); $(this).siblings('.nok-photo-preview-wrap').addClass('d-none');
						$(this).addClass('d-none');
					});
					function syncNokPhotoBox($card) {
						var $box = $card.find('.nok-photo-box');
						var isNok = $card.find('.part-kondisi[value="NOK"]').is(':checked');
						if (!isNok) { $box.find('.nok-photo-input').val(''); $box.find('.nok-photo-cancel').addClass('d-none'); $box.find('.nok-photo-state').text('').addClass('d-none'); $box.find('.nok-photo-compress').addClass('d-none'); $box.find('.nok-photo-preview').removeAttr('src'); $box.find('.nok-photo-preview-wrap').addClass('d-none'); }
						$box.toggle(isNok);
						var required = isNok && $box.attr('data-existing-photo') !== '1';
						$box.find('.nok-photo-input').removeAttr('required').first().attr('data-photo-required', required ? '1' : '0');
					}
					$form.on('change', '.part-kondisi', function(){
						syncNokPhotoBox($(this).closest('.part-card'));
					});
					$form.find('.part-card').each(function(){ syncNokPhotoBox($(this)); });
					function lockProductivityCategory($select) {
						var field = ($select.attr('id') || '').replace(/^ctrl-ketidaksesuaian-/, '');
						if (!field) { return; }
						var $correlation = $('#korelasi-' + field), isProductivity = $.trim($correlation.find('option:selected').text()).toLowerCase() === 'productivity';
						var $none = $select.find('option').filter(function(){ return $.trim($(this).text()).toLowerCase() === 'none'; }).first();
						if (isProductivity && $none.length) {
							$select.val($none.val()).css({'pointer-events':'none','background-color':'#e9ecef','background-image':'none','-webkit-appearance':'none','-moz-appearance':'none','appearance':'none'}).attr('tabindex', '-1');
						} else {
							$select.css({'pointer-events':'auto','background-color':'#ffffff','background-image':'','-webkit-appearance':'','-moz-appearance':'','appearance':''}).removeAttr('tabindex');
						}
					}
					$(document).ajaxComplete(function(event, xhr, settings) {
						if (settings.url && settings.url.indexOf('sig_kategori_ketidaksesuaian_option_list') !== -1) {
							$form.find('[id^="ctrl-ketidaksesuaian-"]').each(function(){ lockProductivityCategory($(this)); });
						}
					});
					$form.on('submit', function(e){
						if (typeof this.checkValidity === 'function' && !this.checkValidity()) {
							e.preventDefault();
							e.stopImmediatePropagation();
							if (typeof this.reportValidity === 'function') { this.reportValidity(); }
							var invalidField = this.querySelector(':invalid');
							if (invalidField) { invalidField.focus(); }
							return false;
						}
						var missing = false, hasNok = false;
						$form.find('.part-card').each(function(){
							var $card = $(this);
							if ($card.find('.part-kondisi[value="NOK"]').is(':checked')) {
								hasNok = true;
								if ($card.find('.nok-photo-box').attr('data-existing-photo') !== '1') {
									var hasFile = false; $card.find('.nok-photo-input').each(function(){ if (this.files && this.files.length) { hasFile = true; } });
									if (!hasFile) { missing = true; $card.find('.nok-photo-box').addClass('border border-danger p-2 rounded'); }
								}
							}
						});
						if (missing) { e.preventDefault(); alert('Foto Before wajib untuk setiap part NOK.'); return; }
						var $submit = $form.find('button[type="submit"], input[type="submit"]').filter(':visible').first();
						if (!$submit.length || $submit.data('amSubmitting')) { if ($submit.data('amSubmitting')) { e.preventDefault(); } return; }
						$submit.data('amSubmitting', true).prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ' + (hasNok ? 'Mengunggah Foto Before...' : 'Menyimpan AM...'));
					});
				});
			})();
			// Banner peringatan dan penguncian form AM jika mesin sedang DIDEAKTIVASI atau PERIODE SUDAH DITANDATANGANI
			(function(){
				var deactivatedMap = <?php echo !empty($this->deactivated_units) ? json_encode($this->deactivated_units) : '{}'; ?>;
				var signedMap = <?php echo !empty($this->signed_units) ? json_encode($this->signed_units) : '{}'; ?>;
				$(function(){
					var $mesinCtrl = $('select[name="mesin"], input[name="mesin"]').first();
					if (!$mesinCtrl.length) { return; }
					var $form = $mesinCtrl.closest('form');
					if (!$form.length) { return; }

					function updateUnitGuards() {
						var val = parseInt($mesinCtrl.val(), 10);
						var deactInfo = deactivatedMap[val];
						var sigInfo = signedMap[val];
						var $deactBanner = $('#machine-deactivation-banner');
						var $sigBanner = $('#period-signature-lock-banner');
						var $submitBtns = $form.find('button[type="submit"], input[type="submit"]');

						// 1. Pengecekan Deaktivasi Mesin
						if (deactInfo) {
							if (!$deactBanner.length) {
								$deactBanner = $('<div id="machine-deactivation-banner" class="alert alert-warning border-warning shadow-sm mb-3"></div>');
								$form.prepend($deactBanner);
							}
							var notesHtml = deactInfo.notes ? '<div class="small mt-1 text-secondary"><strong>Catatan:</strong> ' + $('<div>').text(deactInfo.notes).html() + '</div>' : '';
							$deactBanner.html(
								'<div class="d-flex align-items-start">' +
								'  <div class="mr-3 text-warning"><i class="fa fa-exclamation-triangle fa-2x"></i></div>' +
								'  <div class="flex-grow-1">' +
								'    <h5 class="alert-heading font-weight-bold mb-1" style="font-size:15px; color:#856404;">PERHATIAN: Mesin Ini Sedang DIDEAKTIVASI!</h5>' +
								'    <p class="mb-1" style="font-size:13px;">Unit <strong>' + $('<div>').text(deactInfo.nama_mesin).html() + '</strong> dinonaktifkan sementara untuk: <strong class="badge badge-warning text-dark font-weight-bold" style="font-size:12px;">' + $('<div>').text(deactInfo.reason).html() + '</strong> oleh <strong>' + $('<div>').text(deactInfo.action_by_username).html() + '</strong> sejak ' + $('<div>').text(deactInfo.started_at).html() + '.</p>' +
								notesHtml +
								'    <div class="small text-danger font-weight-bold mt-2"><i class="fa fa-lock"></i> Pengisian Form AM pada unit ini DIKUNCI hingga mesin diaktifkan kembali oleh Supervisor/Admin.</div>' +
								'  </div>' +
								'</div>'
							).show();
						} else {
							if ($deactBanner.length) { $deactBanner.hide(); }
						}

						// 2. Pengecekan Tanda Tangan Digital Periode AM
						if (sigInfo) {
							if (!$sigBanner.length) {
								$sigBanner = $('<div id="period-signature-lock-banner" class="alert alert-warning border-warning shadow-sm mb-3"></div>');
								$form.prepend($sigBanner);
							}
							var whoSigned = [];
							if (sigInfo.is_operator_signed) whoSigned.push('Operator Produksi');
							if (sigInfo.is_spv_signed) whoSigned.push('Supervisor');
							var whoText = whoSigned.join(' & ');

							$sigBanner.html(
								'<div class="d-flex align-items-start">' +
								'  <div class="mr-3 text-warning"><i class="fa fa-lock fa-2x"></i></div>' +
								'  <div class="flex-grow-1">' +
								'    <h5 class="alert-heading font-weight-bold mb-1" style="font-size:15px; color:#856404;"><i class="fa fa-shield"></i> Periode Ini Telah Ditandatangani Digital (' + $('<div>').text(whoText).html() + ')</h5>' +
								'    <p class="mb-1" style="font-size:13px;">Unit <strong>' + $('<div>').text(sigInfo.nama_mesin).html() + '</strong> pada Periode ' + sigInfo.periode + ' (Bulan ' + sigInfo.bulan + '/' + sigInfo.tahun + ') telah terkunci dengan tanda tangan digital resmi. Penambahan checklist baru pada periode ini tidak diizinkan demi menjamin integritas keabsahan dokumen.</p>' +
								'    <div class="small text-danger font-weight-bold mt-2"><i class="fa fa-info-circle"></i> Jika perlu mengisi atau merevisi data periode ini, batalkan tanda tangan terlebih dahulu pada menu <em>Cetak Check Sheet AM</em>.</div>' +
								'  </div>' +
								'</div>'
							).show();
						} else {
							if ($sigBanner.length) { $sigBanner.hide(); }
						}

						// Atur disable submit button
						if (deactInfo) {
							$submitBtns.prop('disabled', true).addClass('disabled').attr('title', 'Unit mesin sedang deaktif');
						} else if (sigInfo) {
							$submitBtns.prop('disabled', true).addClass('disabled').attr('title', 'Periode AM telah ditandatangani digital');
						} else if ($form.data('duplicateLocked')) {
							$submitBtns.prop('disabled', true).addClass('disabled').attr('title', 'AM untuk mesin dan shift ini sudah diisi');
						} else if ($form.data('partConfigLoading') || $form.data('partConfigError')) {
							$submitBtns.prop('disabled', true).addClass('disabled').attr('title', $form.data('partConfigError') ? 'Konfigurasi part gagal dimuat' : 'Konfigurasi part sedang dimuat');
						} else {
							$submitBtns.prop('disabled', false).removeClass('disabled').removeAttr('title');
						}
					}

					$mesinCtrl.on('change input', updateUnitGuards);
					updateUnitGuards();
				});
			})();
			// Cegah double-submit pada koneksi lambat tanpa mengabaikan validasi HTML5.
			(function(){
				$(function(){
					$(document).on('submit', 'form', function(e){
						var form = this;
						var $form = $(form);
						if ($form.data('antiDoubleSubmitLocked')) {
							e.preventDefault();
							return false;
						}
						if (typeof form.checkValidity === 'function' && !form.checkValidity()) {
							e.preventDefault();
							e.stopImmediatePropagation();
							if (typeof form.reportValidity === 'function') {
								form.reportValidity();
							}
							var invalidField = form.querySelector(':invalid');
							if (invalidField) { invalidField.focus(); }
							return false;
						}
						var $buttons = $form.find('button[type="submit"], input[type="submit"]');
						if (!$buttons.length) {
							return;
						}
						$form.data('antiDoubleSubmitLocked', true);
						$buttons.each(function(){
							var $button = $(this);
							$button.data('antiDoubleSubmitOriginalDisabled', $button.prop('disabled'));
							if ($button.is('button')) {
								$button.data('antiDoubleSubmitLabel', $button.html());
								$button.html('<i class="fa fa-spinner fa-spin mr-1"></i> Memproses...');
							} else {
								$button.data('antiDoubleSubmitLabel', $button.val());
								$button.val('Memproses...');
							}
							$button.prop('disabled', true).addClass('disabled').attr('aria-busy', 'true');
						});
						setTimeout(function(){
							if (!$form.data('antiDoubleSubmitLocked')) {
								return;
							}
							$buttons.each(function(){
								var $button = $(this);
								var wasDisabled = $button.data('antiDoubleSubmitOriginalDisabled');
								if (!wasDisabled) {
									$button.prop('disabled', false).removeClass('disabled').removeAttr('aria-busy');
								}
								if ($button.is('button')) {
									$button.html($button.data('antiDoubleSubmitLabel'));
								} else {
									$button.val($button.data('antiDoubleSubmitLabel'));
								}
								$button.removeData('antiDoubleSubmitLabel').removeData('antiDoubleSubmitOriginalDisabled');
							});
							$form.removeData('antiDoubleSubmitLocked');
						}, 15000);
					});
				});
			})();

		</script>
		<?php 
			Html ::  page_js('popper.js');
			Html ::  page_js('bootstrap-4.3.1.min.js');
		?>
		<?php
			Html ::  page_js('flatpickr.min.js');
			Html ::  page_js('bootstrap-editable.js');
			Html ::  page_js('dropzone.min.js');
			Html ::  page_js('plugins.js'); //boostrapswitch, passwordStrength, twbs-pagination, blueimp-gallery,
			Html ::  page_js('plugins-init.js');
			Html ::  page_js('page-scripts.js');
		?>
	</body>
</html>
