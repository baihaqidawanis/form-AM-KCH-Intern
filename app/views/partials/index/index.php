        <?php 
        $page_id = null;
        $comp_model = new SharedController;
        ?>
        <div  class=" py-5">
            <div class="container">
                <div class="row ">
                    <div class="col-md-4 comp-grid">
                        <div class="">
                            <div class="fadeIn animated mb-4">
                                <div class="text-capitalize">
                                    <h2 class="text-capitalize">AM Online Pulogadung</h2>
                                </div>
                            </div>
                        </div>
                        <?php $this :: display_page_errors(); ?>
                        
                        <div  class="bg-light p-3 animated fadeIn page-content">
                            <div>
                                <h4><i class="fa fa-key"></i> User Login</h4>
                                <hr />
                                <?php 
                                $this :: display_page_errors(); 
                                ?>
                                <form name="loginForm" action="<?php print_link('index/login/?csrf_token=' . Csrf::$token); ?>" class="needs-validation form page-form" method="post">
                                    <div class="input-group form-group">
                                        <input placeholder="NIK" name="username"  required="required" class="form-control" type="text"  />
                                        <div class="input-group-append">
                                            <span class="input-group-text"><i class="form-control-feedback fa fa-user"></i></span>
                                        </div>
                                    </div>
                                    <div class="input-group form-group">
                                        <input  placeholder="Password" required="required" v-model="user.password" name="password" class="form-control " type="password" />
                                        <div class="input-group-append cursor-pointer btn-toggle-password">
                                            <span class="input-group-text"><i class="fa fa-eye"></i></span>
                                        </div>
                                    </div>
                                    <div class="row clearfix mt-3 mb-3">
                                        <div class="col-6">
                                            <label class="">
                                                <input value="true" type="checkbox" name="rememberme" />
                                                Remember Me
                                            </label>
                                        </div>
                                        <div class="col-6">
                                            <a href="<?php print_link('passwordmanager') ?>" class="text-danger"> Reset Password?</a>
                                        </div>
                                    </div>
                                    <div class="form-group text-center">
                                        <button class="btn btn-primary btn-block btn-md" type="submit"> 
                                            <i class="load-indicator">
                                                <clip-loader :loading="loading" color="#fff" size="20px"></clip-loader> 
                                            </i>
                                            Login <i class="fa fa-key"></i>
                                        </button>
                                    </div>
                                    <hr />
                                    <div class="text-center">
                                        Don't Have an Account? <a href="<?php print_link("index/register") ?>" class="btn btn-success">Register
                                        <i class="fa fa-user"></i></a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 comp-grid">
                        <div class=""><div></div>
                        </div>
                    </div>
                    <div class="col-md-4 comp-grid">
                        <div class="">
                            <div class="fadeIn animated mb-4" style="color: white;">
                                <div class="text-capitalize">
                                    <h2 class="text-capitalize">AM Online Pulogadung</h2>
                                </div>
                            </div>
                            </div><div class=""><!DOCTYPE html>
                            <html lang="en">
                                <head>
                                    <meta charset="UTF-8">
                                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                                            <title>Produksi Cikarang</title>
                                            <style>
                                                body {
                                                margin: 0;
                                                font-family: Arial, sans-serif;
                                                }
                                                .running-text-container {
                                                width: 500px;
                                                height: 50px;
                                                overflow: hidden;
                                                border: 1px solid #ccc;
                                                background-color: white;
                                                display: flex;
                                                align-items: center;
                                                }
                                                .running-text {
                                                white-space: nowrap;
                                                font-size: 30px;
                                                animation: marquee 15s linear infinite;
                                                }
                                                @keyframes marquee {
                                                0% { transform: translateX(20%); }
                                                100% { transform: translateX(-100%); }
                                                }
                                                .slideshow {
                                                width: 500px;
                                                height: 300px;
                                                position: relative;
                                                overflow: hidden;
                                                margin: 0 auto;
                                                }
                                                .slideshow img {
                                                width: 100%;
                                                height: 100%;
                                                position: absolute;
                                                top: 0;
                                                left: 0;
                                                opacity: 0;
                                                transition: opacity 5s ease; /* Efek transisi untuk perubahan opasitas */
                                                }
                                                .slideshow img.active {
                                                opacity: 1;
                                                }
                                            </style>
                                        </head>
                                        <body>
                                            <div class="slideshow">
                                                <img src="/form-am/assets/images/bg1.jpeg" class="active" width="500px" height="300px" style="object-fit: cover; object-position: 0% 75%">
                                                    <img src="/form-am/assets/images/bg2.jpeg" width="500px" height="300px" style="object-fit: cover;">
                                                        <img src="/form-am/assets/images/bg3.jpeg" width="500px" height="300px" style="object-fit: cover;">
                                                            <!-- Tambahkan gambar-gambar lainnya di sini -->
                                                        </div>
                                                        <div class="running-text-container">
                                                            <div class="running-text">Jangan lupa mengisi Autonomous Maintenance di area masing-masing. Merawat mesin merupakan kunci keberhasilan produktivitas di tempat kerja 💪 </div>
                                                        </div>
                                                        <script>
                                                            const slides = document.querySelectorAll('.slideshow img');
                                                            const intervalTime = 3000; // Ganti gambar setiap 5 detik
                                                            let slideInterval = setInterval(nextSlide, intervalTime);
                                                            function nextSlide() {
                                                            const current = document.querySelector('.active');
                                                            current.style.opacity = 0;
                                                            setTimeout(() => {
                                                            current.classList.remove('active');
                                                            if (current.nextElementSibling) {
                                                            current.nextElementSibling.style.opacity = 1;
                                                            current.nextElementSibling.classList.add('active');
                                                            } else {
                                                            slides[0].style.opacity = 1;
                                                            slides[0].classList.add('active');
                                                            }
                                                            }, 500); // Waktu tambahan untuk memastikan efek transisi selesai sebelum mengubah gambar berikutnya
                                                            }
                                                        </script>
                                                    </body>
                                                </html>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            