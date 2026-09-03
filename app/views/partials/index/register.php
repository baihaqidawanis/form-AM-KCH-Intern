<?php
$comp_model = new SharedController;
$page_element_id = "add-page-" . random_str();
$current_page = $this->set_current_page_link();
$csrf_token = Csrf::$token;
$show_header = $this->show_header;
$view_title = $this->view_title;
$redirect_to = $this->redirect_to;
?>
<section class="page" id="<?php echo $page_element_id; ?>" data-page-type="add"  data-display-type="" data-page-url="<?php print_link($current_page); ?>">
    <?php
    if( $show_header == true ){
    ?>
    <div  class="bg-light p-3 mb-3">
        <div class="container">
            <div class="row ">
                <div class="col ">
                    <h4 class="record-title">User registration</h4>
                </div>
                <div class="col-sm-6 comp-grid">
                    <div class="">
                        <div class="text-center">
                            Already have an account?  <a class="btn btn-primary" href="<?php print_link('') ?>"> Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    }
    ?>
    <div  class="">
        <div class="container">
            <div class="row ">
                <div class="col-md-7 comp-grid">
                    <?php $this :: display_page_errors(); ?>
                    <div  class="bg-light p-3 animated fadeIn page-content">
                        <form id="users-userregister-form" role="form" novalidate enctype="multipart/form-data" class="form page-form form-horizontal needs-validation" action="<?php print_link("index/register?csrf_token=$csrf_token") ?>" method="post">
                            <!--[main-form-start]-->
                            <div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="nama">Nama <span class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <input id="ctrl-nama"  value="<?php  echo $this->set_field_value('nama',""); ?>" type="text" placeholder="Enter Nama"  required="" name="nama"  class="form-control " />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group ">
                                        <div class="row">
                                            <div class="col-sm-4">
                                                <label class="control-label" for="email">Email <span class="text-danger">*</span></label>
                                            </div>
                                            <div class="col-sm-8">
                                                <div class="">
                                                    <input id="ctrl-email"  value="<?php  echo $this->set_field_value('email',""); ?>" type="email" placeholder="Enter Email"  required="" name="email"  data-url="api/json/users_email_value_exist/" data-loading-msg="Checking availability ..." data-available-msg="Available" data-unavailable-msg="Not available" class="form-control  ctrl-check-duplicate" />
                                                        <div class="check-status"></div> 
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group ">
                                            <div class="row">
                                                <div class="col-sm-4">
                                                    <label class="control-label" for="username">NIK <span class="text-danger">*</span></label>
                                                </div>
                                                <div class="col-sm-8">
                                                    <div class="">
                                                        <input id="ctrl-username"  value="<?php  echo $this->set_field_value('username',""); ?>" type="text" placeholder="NIK (huruf/angka, maks 11 karakter)" pattern="[A-Za-z0-9]{1,11}" maxlength="11" required="" name="username"  data-url="api/json/users_username_value_exist/" data-loading-msg="Checking availability ..." data-available-msg="Available" data-unavailable-msg="Not available" class="form-control  ctrl-check-duplicate" />
                                                            <small class="form-text text-muted">NIK (Nomor Induk Karyawan): huruf dan/atau angka, maksimal 11 karakter.</small>
                                                            <div class="check-status"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group ">
                                                <div class="row">
                                                    <div class="col-sm-4">
                                                        <label class="control-label" for="area">Area <span class="text-danger">*</span></label>
                                                    </div>
                                                    <div class="col-sm-8">
                                                        <div class="">
                                                            <select required id="ctrl-area" name="area" class="custom-select">
                                                                <option value="" disabled selected>Pilih area ...</option>
                                                                <?php foreach (array('Compounding', 'Filling', 'Kemas', 'Wrapping dan Pack Cartoning', 'Semua Area') as $area_option) {
                                                                  $selected = $this->set_field_selected('area', $area_option, "");
                                                                ?>
                                                                <option <?php echo $selected; ?> value="<?php echo $area_option; ?>"><?php echo $area_option; ?></option>
                                                                <?php } ?>
                                                            </select>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group ">
                                                    <div class="row">
                                                        <div class="col-sm-4">
                                                            <label class="control-label" for="mesin">Mesin <span class="text-danger">*</span></label>
                                                        </div>
                                                        <div class="col-sm-8">
                                                            <div class="">
                                                                <?php
                                                                $mesin_options = $comp_model->sig_Line_option_list();
                                                                // Prefix nama mesin per kategori, sesuai pengelompokan submenu di helpers/Menu.php.
                                                                $area_machine_prefixes = array(
                                                                  'Compounding' => array('cosmec', 'fbd jaw chuan', 'fbd glatt', 'supermixer', 'granulator', 'storage tank', 'mixing tank'),
                                                                  'Filling' => array('joeya', 'sig', 'illapak', 'unifill'),
                                                                  'Kemas' => array('jihcheng', 'jinsung'),
                                                                  'Wrapping dan Pack Cartoning' => array('chimei', 'temach', 'check weigher', 'conveyor sig', 'injekt kemas', 'inkjet kemas', 'best pack', 'cartoning', 'pack', 'wrapping'),
                                                                );
                                                                $mesin_area_of = function ($label) use ($area_machine_prefixes) {
                                                                  foreach ($area_machine_prefixes as $area => $prefixes) {
                                                                    foreach ($prefixes as $prefix) {
                                                                      if (stripos($label, $prefix) !== false) { return $area; }
                                                                    }
                                                                  }
                                                                  return '';
                                                                };
                                                                ?>
                                                                <select required id="ctrl-mesin" name="mesin" class="custom-select">
                                                                    <option value="" disabled selected>Pilih nama mesin ...</option>
                                                                    <?php foreach ($mesin_options as $option) {
                                                                      $selected = $this->set_field_selected('mesin', $option['label'], "");
                                                                    ?>
                                                                    <option data-area="<?php echo $mesin_area_of($option['label']); ?>" <?php echo $selected; ?> value="<?php echo $option['label']; ?>"><?php echo $option['label']; ?></option>
                                                                    <?php }
                                                                    $selected_all = $this->set_field_selected('mesin', 'Semua Mesin', "");
                                                                    ?>
                                                                    <option data-area="" <?php echo $selected_all; ?> value="Semua Mesin">Semua Mesin</option>
                                                                </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="form-group ">
                                                        <div class="row">
                                                            <div class="col-sm-4">
                                                                <label class="control-label" for="password">Password <span class="text-danger">*</span></label>
                                                            </div>
                                                            <div class="col-sm-8">
                                                                <div class="input-group">
                                                                    <input id="ctrl-password"  value="<?php  echo $this->set_field_value('password',""); ?>" type="password" placeholder="Enter Password"  required="" minlength="8" name="password"  class="form-control  password password-strength" />
                                                                        <div class="input-group-append cursor-pointer btn-toggle-password">
                                                                            <span class="input-group-text"><i class="fa fa-eye"></i></span>
                                                                        </div>
                                                                    </div>
                                                                    <div class="password-strength-msg">
                                                                        <small class="font-weight-bold">Should contain</small>
                                                                        <small class="length chip">8 Characters minimum</small>
                                                                        <small class="lower chip">Lowercase Letter</small>
                                                                        <small class="caps chip">Capital Letter</small>
                                                                        <small class="number chip">Number</small>
                                                                        <small class="special chip">Symbol</small>
                                                                    </div>
                                                                    <small class="form-text text-muted mt-1">
                                                                        <i class="fa fa-info-circle"></i>
                                                                        Password minimal 8 karakter dan harus mengandung huruf besar, huruf kecil, angka, dan karakter spesial.
                                                                    </small>
                                                                    <?php
                                                                    // Tampilkan error password secara inline di sini juga
                                                                    $page_errors = $this->view->page_error ?? [];
                                                                    foreach ((array)$page_errors as $err) {
                                                                        if (stripos($err, 'password') !== false || stripos($err, 'karakter') !== false || stripos($err, 'huruf') !== false) { ?>
                                                                        <div class="alert alert-danger py-1 px-2 mt-1 mb-0" style="font-size:0.875rem;">
                                                                            <i class="fa fa-exclamation-circle"></i> <?php echo htmlspecialchars($err); ?>
                                                                        </div>
                                                                    <?php }} ?>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="form-group ">
                                                            <div class="row">
                                                                <div class="col-sm-4">
                                                                    <label class="control-label" for="confirm_password">Confirm Password <span class="text-danger">*</span></label>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="input-group">
                                                                        <input id="ctrl-password-confirm" data-match="#ctrl-password"  class="form-control password-confirm " type="password" name="confirm_password" required placeholder="Confirm Password" />
                                                                        <div class="input-group-append cursor-pointer btn-toggle-password">
                                                                            <span class="input-group-text"><i class="fa fa-eye"></i></span>
                                                                        </div>
                                                                        <div class="invalid-feedback">
                                                                            Password does not match
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!-- Role gak dipilih user sendiri -- akun baru selalu didaftarkan sebagai
                                                             Staff/Operator (role_id 4). Kalau butuh role lebih tinggi (Manager/
                                                             Supervisor/Administrator), superadmin yang naikkan manual lewat
                                                             menu Users -> Edit setelah akun diaktivasi. -->
                                                        <input type="hidden" name="user_role_id" value="4">
                                                        <div class="form-group ">
                                                            <div class="row">
                                                                <div class="col-sm-4">
                                                                    <label class="control-label" for="pict">Pict <span class="text-danger">*</span></label>
                                                                </div>
                                                                <div class="col-sm-8">
                                                                    <div class="">
                                                                        <div class="dropzone required" input="#ctrl-pict" fieldname="pict"    data-multiple="false" dropmsg="Choose files or drag and drop files to upload"    btntext="Browse" filesize="3" maximum="1">
                                                                            <input name="pict" id="ctrl-pict" required="" class="dropzone-input form-control" value="<?php  echo $this->set_field_value('pict',""); ?>" type="text"  />
                                                                                <!--<div class="invalid-feedback animated bounceIn text-center">Please a choose file</div>-->
                                                                                <div class="dz-file-limit animated bounceIn text-center text-danger"></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <!--[main-form-end]-->
                                                        <div class="form-group form-submit-btn-holder text-center mt-3">
                                                            <button class="btn btn-primary" type="submit">
                                                                Submit
                                                                <i class="fa fa-send"></i>
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                            