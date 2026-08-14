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
					<a class="prev">‹</a>
					<a class="next">›</a>
					<a class="close">×</a>
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
				<!-- URS 1.3: Session Timeout Warning (30 menit idle) [Start] -->
				<?php if (user_login_status() == true) { ?>
				<div id="session-timeout-warning">
					<div class="session-timeout-box">
						<h4><i class="fa fa-clock-o"></i> Sesi Akan Berakhir</h4>
						<p>Anda tidak aktif. Sesi akan otomatis berakhir dalam <strong><span id="session-timeout-countdown">5:00</span></strong> menit.</p>
						<p class="text-muted small mb-3">Kalau lagi ngisi form, data yang udah diisi bakal disimpan otomatis dan bisa dipulihkan setelah login ulang.</p>
						<button type="button" id="session-timeout-stay-btn" class="btn btn-primary">Saya Masih Di Sini</button>
					</div>
				</div>
				<style>
					#session-timeout-warning {
						display: none;
						position: fixed;
						top: 0; left: 0; right: 0; bottom: 0;
						width: 100%; height: 100%;
						background: rgba(0, 0, 0, 0.6);
						z-index: 10500;
						align-items: center;
						justify-content: center;
					}
					#session-timeout-warning.show {
						display: flex;
					}
					.session-timeout-box {
						background: #fff;
						border-radius: 6px;
						padding: 24px 28px;
						max-width: 420px;
						width: 90%;
						box-shadow: 0 5px 30px rgba(0, 0, 0, 0.3);
						text-align: center;
					}
					.session-timeout-box h4 {
						color: #dc3545;
						margin-bottom: 12px;
					}
					.draft-restore-notice {
						display: flex;
						align-items: center;
						justify-content: space-between;
						flex-wrap: wrap;
						gap: 8px;
					}
				</style>
				<!-- URS 1.3: Session Timeout Warning [End] -->
				<?php } ?>
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
			<?php if (user_login_status() == true) { ?>
			// URS 1.3: session timeout 30 menit idle (mouse/keyboard/touch) + peringatan + auto-save draft.
			(function(){
				var IDLE_LIMIT_MS = <?php echo SESSION_TIMEOUT_SECONDS * 1000; ?>;
				var WARNING_MS = 5 * 60 * 1000; // tampilkan peringatan 5 menit sebelum berakhir
				// index/logout WAJIB csrf_token (Csrf::cross_check()) -- tanpa ini
				// request-nya ditolak 403 SEBELUM session_destroy() sempet jalan,
				// jadi timeout "kelihatan" jalan (redirect) tapi session-nya
				// sebenernya TETEP AKTIF. Ketauan pas nulis test Playwright buat ini.
				var LOGOUT_URL = '<?php echo print_link("index/logout?csrf_token=" . Csrf::$token); ?>';
				var idleTimer, warningTimer, countdownInterval;

				function draftKey(){
					return 'am_draft_' + window.location.pathname;
				}

				// Simpan isi form (kalau lagi ada) ke localStorage sebelum logout paksa,
				// biar operator gak kehilangan data yang udah diisi pas sesi berakhir.
				function saveFormDraft(){
					var $form = $('form[novalidate]').first();
					if (!$form.length) { return; }
					var data = {};
					$form.find('input, select, textarea').each(function(){
						var $el = $(this);
						var name = $el.attr('name');
						var type = $el.attr('type');
						if (!name || type === 'password' || name === 'csrf_token') { return; }
						if (type === 'checkbox' || type === 'radio') {
							if ($el.is(':checked')) { data[name] = $el.val(); }
						} else {
							data[name] = $el.val();
						}
					});
					if (Object.keys(data).length) {
						try {
							localStorage.setItem(draftKey(), JSON.stringify({ data: data, savedAt: Date.now() }));
						} catch (e) { /* localStorage penuh/nonaktif, biarin aja */ }
					}
				}

				function updateCountdownText(seconds){
					var m = Math.floor(seconds / 60);
					var s = seconds % 60;
					$('#session-timeout-countdown').text(m + ':' + (s < 10 ? '0' : '') + s);
				}

				function showWarning(){
					var remaining = Math.floor(WARNING_MS / 1000);
					updateCountdownText(remaining);
					$('#session-timeout-warning').addClass('show');
					clearInterval(countdownInterval);
					countdownInterval = setInterval(function(){
						remaining--;
						updateCountdownText(Math.max(remaining, 0));
						if (remaining <= 0) { clearInterval(countdownInterval); }
					}, 1000);
				}

				function hideWarning(){
					$('#session-timeout-warning').removeClass('show');
					clearInterval(countdownInterval);
				}

				function doTimeout(){
					saveFormDraft();
					window.location.href = LOGOUT_URL;
				}

				function resetTimers(){
					clearTimeout(idleTimer);
					clearTimeout(warningTimer);
					hideWarning();
					warningTimer = setTimeout(showWarning, IDLE_LIMIT_MS - WARNING_MS);
					idleTimer = setTimeout(doTimeout, IDLE_LIMIT_MS);
				}

				$(document).on('click', '#session-timeout-stay-btn', function(){
					resetTimers();
					$.get(siteAddr + 'home/ping'); // bump last_activity server-side juga
				});

				// mouse + keyboard + touch (device produksi pakai tablet layar sentuh)
				['mousemove', 'mousedown', 'keypress', 'scroll', 'touchstart', 'touchmove'].forEach(function(evt){
					document.addEventListener(evt, function(){
						// jangan reset timer kalau lagi nampilin peringatan -- biar user
						// harus klik tombol "Saya Masih Di Sini" secara sadar
						if (!$('#session-timeout-warning').hasClass('show')) { resetTimers(); }
					}, { passive: true });
				});

				resetTimers();
			})();

			// Pulihkan draft form yang sempat ke-save otomatis pas sesi berakhir sebelumnya.
			(function(){
				var key = 'am_draft_' + window.location.pathname;
				var raw = null;
				try { raw = localStorage.getItem(key); } catch (e) { return; }
				if (!raw) { return; }
				var draft;
				try { draft = JSON.parse(raw); } catch (e) { localStorage.removeItem(key); return; }
				if (!draft || !draft.savedAt || (Date.now() - draft.savedAt) > 24 * 60 * 60 * 1000) {
					localStorage.removeItem(key);
					return;
				}
				$(function(){
					var $form = $('form[novalidate]').first();
					if (!$form.length) { return; }
					var $notice = $(
						'<div class="alert alert-info draft-restore-notice">' +
						'<span>Ditemukan draft form yang belum sempat disimpan (sesi berakhir sebelumnya).</span>' +
						'<span>' +
						'<button type="button" class="btn btn-sm btn-primary" id="draft-restore-btn">Pulihkan Draft</button> ' +
						'<button type="button" class="btn btn-sm btn-outline-secondary" id="draft-discard-btn">Buang</button>' +
						'</span></div>'
					);
					$form.prepend($notice);
					$(document).on('click', '#draft-restore-btn', function(){
						Object.keys(draft.data).forEach(function(name){
							var $el = $form.find('[name="' + name + '"]');
							if (!$el.length) { return; }
							if ($el.is(':checkbox, :radio')) {
								$el.filter('[value="' + draft.data[name] + '"]').prop('checked', true).trigger('change');
							} else {
								$el.val(draft.data[name]).trigger('change');
							}
						});
						localStorage.removeItem(key);
						$notice.remove();
					});
					$(document).on('click', '#draft-discard-btn', function(){
						localStorage.removeItem(key);
						$notice.remove();
					});
				});
			})();
			<?php } ?>
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