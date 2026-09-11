<?php
$page_id = null;
$comp_model = new SharedController;
?>

<div class="am-login-page">
    <div class="am-login-container">
        <div class="am-login-card">

            <!-- Left Panel: Authentication Form -->
            <div class="am-login-form-side">
                <div class="am-form-inner">
                    <!-- Brand Header -->
                    <div class="am-brand-header">
                        <div class="am-brand-pill">
                            <span class="am-brand-dot"></span>
                            <span>Kalbe Consumer Health &bull; Plant Pulogadung</span>
                        </div>
                        <h1 class="am-login-heading">AM Online</h1>
                        <p class="am-login-desc">Autonomous Maintenance Management System. Silakan masuk dengan NIK
                            Anda.</p>
                    </div>

                    <!-- Alerts & Page Errors -->
                    <?php $this::display_page_errors(); ?>

                    <!-- Login Form -->
                    <form name="loginForm" action="<?php print_link('index/login/?csrf_token=' . Csrf::$token); ?>"
                        class="needs-validation form page-form" method="post" autocomplete="on">

                        <!-- NIK Input -->
                        <div class="form-group am-field-group">
                            <label class="am-field-label" for="input-username">NIK Karyawan</label>
                            <div class="input-group am-input-wrapper">
                                <div class="input-group-prepend">
                                    <span class="input-group-text am-input-icon"><i
                                            class="fa fa-user-circle-o"></i></span>
                                </div>
                                <input id="input-username" placeholder="Masukkan NIK Anda" name="username"
                                    required="required" class="form-control am-input-control" type="text"
                                    autocomplete="username" />
                            </div>
                        </div>

                        <!-- Password Input -->
                        <div class="form-group am-field-group">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="am-field-label mb-0" for="input-password">Password</label>
                            </div>
                            <div class="input-group am-input-wrapper">
                                <div class="input-group-prepend">
                                    <span class="input-group-text am-input-icon"><i class="fa fa-lock"></i></span>
                                </div>
                                <input id="input-password" placeholder="Masukkan Password" required="required"
                                    v-model="user.password" name="password" class="form-control am-input-control"
                                    type="password" autocomplete="current-password" />
                                <div class="input-group-append cursor-pointer btn-toggle-password"
                                    title="Lihat / Sembunyikan Password">
                                    <span class="input-group-text am-input-icon am-toggle-icon"><i
                                            class="fa fa-eye"></i></span>
                                </div>
                            </div>
                        </div>

                        <!-- Options Row: Remember Me & Forgot Password -->
                        <div class="am-form-meta">
                            <label class="am-checkbox-label">
                                <input value="true" type="checkbox" name="rememberme" class="am-checkbox" />
                                <span>Ingat Saya</span>
                            </label>
                            <a href="<?php print_link('passwordmanager') ?>" class="am-forgot-link">Lupa Password?</a>
                        </div>

                        <!-- Action Button -->
                        <div class="form-group mb-4">
                            <button class="btn btn-primary btn-block am-submit-button" type="submit">
                                <i class="load-indicator">
                                    <clip-loader :loading="loading" color="#fff" size="18px"></clip-loader>
                                </i>
                                <span>Masuk ke Akun</span>
                                <i class="fa fa-sign-in am-btn-icon"></i>
                            </button>
                        </div>

                        <!-- Footer: Register -->
                        <div class="am-form-footer">
                            <span class="text-muted">Belum memiliki akun?</span>
                            <a href="<?php print_link("index/register") ?>" class="am-register-link">
                                Daftar Sekarang <i class="fa fa-angle-right ml-1"></i>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Panel: Visual Showcase & Motivation (SF Pro / Glassmorphic) -->
            <div class="am-login-visual-side">
                <div class="am-visual-slideshow">
                    <div class="am-slide-item active"
                        style="background-image: url('<?php print_link('assets/images/bg1.jpeg'); ?>');"></div>
                    <div class="am-slide-item"
                        style="background-image: url('<?php print_link('assets/images/bg2.jpeg'); ?>');"></div>
                    <div class="am-slide-item"
                        style="background-image: url('<?php print_link('assets/images/bg3.jpeg'); ?>');"></div>
                    <div class="am-visual-overlay"></div>
                </div>

                <!-- Bottom Motivational Card (Replaces Obsolete Running Marquee) -->
                <div class="am-motivation-card">
                    <div class="am-motivation-header">
                        <span class="am-motivation-badge">
                            <i class="fa fa-lightbulb-o"></i> Pengingat Harian AM
                        </span>
                        <div class="am-slide-dots">
                            <span class="am-dot active" data-slide="0"></span>
                            <span class="am-dot" data-slide="1"></span>
                            <span class="am-dot" data-slide="2"></span>
                        </div>
                    </div>
                    <p class="am-motivation-quote">
                        &ldquo;Jangan lupa mengisi Autonomous Maintenance di area masing-masing. Merawat mesin merupakan
                        kunci keberhasilan produktivitas di tempat kerja &#128170;&rdquo;
                    </p>
                </div>
            </div>

        </div>
    </div>
</div>

<style>
    /* Modern Apple-Kalbe Split Login Layout */
    .am-login-page {
        min-height: calc(100vh - 100px);
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px 16px 48px;
        background: var(--ak-canvas, #F5F5F7);
        font-family: -apple-system, BlinkMacSystemFont, "SF Pro Text", "SF Pro Display", "SF Pro", "Segoe UI", Roboto, sans-serif;
    }

    .am-login-container {
        width: 100%;
        max-width: 980px;
        margin: 0 auto;
    }

    .am-login-card {
        display: flex;
        background: #FFFFFF;
        border-radius: 20px;
        box-shadow: 0 20px 48px -12px rgba(0, 0, 0, 0.08), 0 2px 8px rgba(0, 0, 0, 0.03);
        border: 1px solid rgba(0, 0, 0, 0.07);
        overflow: hidden;
        min-height: 580px;
        transition: box-shadow 0.3s ease;
    }

    /* Left Form Pane */
    .am-login-form-side {
        flex: 1 1 50%;
        min-width: 320px;
        padding: 44px 40px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: #FFFFFF;
    }

    .am-form-inner {
        max-width: 380px;
        width: 100%;
        margin: 0 auto;
    }

    .am-brand-header {
        margin-bottom: 24px;
    }

    .am-brand-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #F0F8EC;
        border: 1px solid #D1EBB8;
        color: #009639;
        font-size: 0.76rem;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 999px;
        letter-spacing: 0.01em;
        margin-bottom: 12px;
    }

    .am-brand-dot {
        width: 6px;
        height: 6px;
        background: #009639;
        border-radius: 50%;
        display: inline-block;
        box-shadow: 0 0 0 2px rgba(0, 150, 57, 0.2);
    }

    .am-login-heading {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1D1D1F;
        letter-spacing: -0.025em;
        margin: 0 0 6px;
        line-height: 1.2;
    }

    .am-login-desc {
        font-size: 0.88rem;
        color: #6E6E73;
        line-height: 1.45;
        margin: 0;
    }

    /* Fields & Inputs */
    .am-field-group {
        margin-bottom: 18px;
    }

    .am-field-label {
        font-size: 0.82rem;
        font-weight: 600;
        color: #3A3A3C;
        margin-bottom: 6px;
        display: block;
    }

    .am-input-wrapper {
        position: relative;
        border-radius: 10px;
        overflow: hidden;
        transition: all 0.2s ease;
    }

    .am-input-icon {
        background: #F5F5F7;
        border: 1px solid #D2D2D7;
        border-right: none;
        color: #8E8E93;
        font-size: 0.95rem;
        padding: 0 14px;
        display: flex;
        align-items: center;
        border-top-left-radius: 10px;
        border-bottom-left-radius: 10px;
    }

    .am-toggle-icon {
        border-left: none;
        border-right: 1px solid #D2D2D7;
        border-top-right-radius: 10px;
        border-bottom-right-radius: 10px;
        border-top-left-radius: 0;
        border-bottom-left-radius: 0;
        cursor: pointer;
    }

    .am-input-control {
        border: 1px solid #D2D2D7;
        height: 44px;
        font-size: 0.92rem;
        color: #1D1D1F;
        padding: 10px 14px;
        background: #FFFFFF;
        transition: all 0.2s ease;
    }

    .am-input-wrapper:focus-within .am-input-icon,
    .am-input-wrapper:focus-within .am-toggle-icon {
        border-color: #009639;
        color: #009639;
    }

    .am-input-control:focus {
        border-color: #009639;
        box-shadow: 0 0 0 3px rgba(0, 150, 57, 0.15);
        outline: none;
    }

    /* Form Meta (Remember me / Forgot pass) */
    .am-form-meta {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 14px 0 20px;
        font-size: 0.84rem;
    }

    .am-checkbox-label {
        display: flex;
        align-items: center;
        gap: 7px;
        color: #48484A;
        margin: 0;
        cursor: pointer;
        font-weight: 500;
    }

    .am-checkbox {
        width: 16px;
        height: 16px;
        accent-color: #009639;
        cursor: pointer;
    }

    .am-forgot-link {
        color: #009639;
        text-decoration: none;
        font-weight: 500;
        transition: color 0.15s ease;
    }

    .am-forgot-link:hover {
        color: #007A2E;
        text-decoration: underline;
    }

    /* Submit Button */
    .am-submit-button {
        background: linear-gradient(180deg, #00A63F 0%, #009639 100%) !important;
        border: none !important;
        color: #FFFFFF !important;
        height: 46px;
        border-radius: 10px !important;
        font-size: 0.96rem !important;
        font-weight: 600 !important;
        letter-spacing: -0.01em;
        display: flex !important;
        align-items: center;
        justify-content: center;
        gap: 8px;
        box-shadow: 0 4px 14px rgba(0, 150, 57, 0.28);
        transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
        cursor: pointer;
    }

    .am-submit-button:hover {
        background: linear-gradient(180deg, #009639 0%, #008231 100%) !important;
        box-shadow: 0 6px 18px rgba(0, 150, 57, 0.38);
        transform: translateY(-1px);
    }

    .am-submit-button:active {
        transform: translateY(0);
        box-shadow: 0 2px 8px rgba(0, 150, 57, 0.25);
    }

    .am-btn-icon {
        font-size: 1rem;
        transition: transform 0.2s ease;
    }

    .am-submit-button:hover .am-btn-icon {
        transform: translateX(3px);
    }

    /* Footer Register */
    .am-form-footer {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        font-size: 0.86rem;
        padding-top: 14px;
        border-top: 1px solid #F0F0F2;
    }

    .am-register-link {
        color: #009639;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }

    .am-register-link:hover {
        color: #007A2E;
        text-decoration: underline;
    }

    .am-login-visual-side {
        flex: 1 1 50%;
        position: relative;
        background: #0B1910;
        min-height: 520px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        padding: 32px;
    }

    .am-visual-slideshow {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
    }

    .am-slide-item {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-size: cover;
        background-position: center;
        opacity: 0;
        transform: scale(1.04);
        transition: opacity 1.5s cubic-bezier(0.4, 0, 0.2, 1), transform 8s linear;
    }

    .am-slide-item.active {
        opacity: 1;
        transform: scale(1.0);
    }

    .am-visual-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(180deg, rgba(6, 20, 11, 0.35) 0%, rgba(6, 20, 11, 0.72) 100%);
        z-index: 1;
    }

    /* Motivational Card (Glassmorphism & SF Pro) */
    .am-motivation-card {
        position: relative;
        z-index: 2;
        background: rgba(255, 255, 255, 0.92);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid rgba(255, 255, 255, 0.8);
        border-radius: 16px;
        padding: 20px 22px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .am-motivation-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 10px;
    }

    .am-motivation-badge {
        color: #009639;
        font-weight: 700;
        font-size: 0.78rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .am-motivation-quote {
        font-size: 0.88rem;
        line-height: 1.5;
        color: #1D1D1F;
        font-weight: 500;
        margin: 0;
    }

    .am-slide-dots {
        display: flex;
        gap: 6px;
    }

    .am-dot {
        width: 7px;
        height: 7px;
        border-radius: 50%;
        background: rgba(0, 0, 0, 0.2);
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .am-dot.active {
        background: #009639;
        width: 18px;
        border-radius: 4px;
    }

    /* Responsive adjustments */
    @media (max-width: 880px) {
        .am-login-card {
            flex-direction: column;
            max-width: 480px;
        }

        .am-login-form-side {
            padding: 32px 24px;
        }

        .am-login-visual-side {
            min-height: 280px;
            padding: 24px;
        }

        .am-motivation-quote {
            font-size: 0.82rem;
        }
    }
</style>

<script>
    // Lightweight Pure JS Slideshow with Smooth Crossfade
    (function () {
        function initAmSlideshow() {
            var slides = document.querySelectorAll('.am-slide-item');
            var dots = document.querySelectorAll('.am-dot');
            if (!slides || slides.length === 0) return;

            var currentIndex = 0;
            var slideTimer = null;
            var intervalMs = 4250; // Durasi per foto: 4.25 detik (4250 milidetik)

            function goToSlide(index) {
                if (index < 0) index = slides.length - 1;
                if (index >= slides.length) index = 0;
                currentIndex = index;

                for (var i = 0; i < slides.length; i++) {
                    slides[i].classList.remove('active');
                }
                for (var j = 0; j < dots.length; j++) {
                    dots[j].classList.remove('active');
                }

                slides[currentIndex].classList.add('active');
                if (dots[currentIndex]) {
                    dots[currentIndex].classList.add('active');
                }
            }

            function startTimer() {
                stopTimer();
                slideTimer = setInterval(function () {
                    goToSlide(currentIndex + 1);
                }, intervalMs);
            }

            function stopTimer() {
                if (slideTimer) {
                    clearInterval(slideTimer);
                    slideTimer = null;
                }
            }

            // Dot click handlers
            dots.forEach(function (dot) {
                dot.addEventListener('click', function () {
                    var targetIdx = parseInt(this.getAttribute('data-slide'), 10);
                    if (!isNaN(targetIdx)) {
                        goToSlide(targetIdx);
                        startTimer();
                    }
                });
            });

            // Pause on quote hover
            var quoteCard = document.querySelector('.am-motivation-card');
            if (quoteCard) {
                quoteCard.addEventListener('mouseenter', stopTimer);
                quoteCard.addEventListener('mouseleave', startTimer);
            }

            startTimer();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAmSlideshow);
        } else {
            initAmSlideshow();
        }
    })();
</script>