<?php
$comp_model = new SharedController;
$kategori_tag_options = $comp_model->sig_kategori_tag_option_list();
$korelasi_tag_options = $comp_model->sig_korelasi_tag_option_list();
$korelasi_tag_options = $comp_model->sig_korelasi_tag_option_list();
$klasifikasi_tag_options = $comp_model->sig_klasifikasi_tag_option_list();
$page_element_id = "add-page-" . random_str();
$current_page = $this->set_current_page_link();
$csrf_token = Csrf::$token;
$show_header = $this->show_header;
$view_title = $this->view_title;
$redirect_to = $this->redirect_to;
?>
<section class="page" id="<?php echo $page_element_id; ?>" data-page-type="add" data-display-type=""
    data-page-url="<?php print_link($current_page); ?>">
    <?php
    if ($show_header == true) {
        ?>
        <div class="bg-light p-3 mb-3">
            <div class="container">
                <div class="row ">
                    <div class="col ">
                        <h4 class="record-title">Add Autonomous Maintenance SIG</h4>
                        <div class="">
                            <div class="subheader-container" style="padding-bottom:20px;">
                                <div class="record-title">No:CR-PR-PR-1203.00 (25 Okt 2021) </div>
                            </div>
                            <script>
                                $(document).ready(function () {
                                });
                            </script>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
    <div class="">
        <div class="container-fluid">
            <div class="row d-flex justify-content-center">
                <div class="col-md-3 col-sm-4 pt-3 d-none d-sm-block">
                    <div style="position: sticky; top: 80px; z-index: 100;" class="p-3 bg-white border rounded shadow-sm">
                        <h6 class="font-weight-bold text-center border-bottom pb-2 mb-3">Keterangan Pelaksanaan</h6>
                        <table class="table table-borderless table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td style="background-color: rgba(255, 255, 0, 0.4); border: 1px solid #ccc; width: 35px; border-radius: 4px;"></td>
                                    <td class="align-middle" style="font-size: 14px; padding-left: 10px;">Mingguan</td>
                                </tr>
                                <tr><td colspan="2" style="height: 5px; padding: 0;"></td></tr>
                                <tr>
                                    <td style="background-color: rgba(0, 204, 255, 0.4); border: 1px solid #ccc; width: 35px; border-radius: 4px;"></td>
                                    <td class="align-middle" style="font-size: 14px; padding-left: 10px;">Bulanan</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="col-md-8 col-sm-8 comp-grid">
                    <?php $this::display_page_errors(); ?>
                    <div class="bg-light p-3 animated fadeIn page-content">
                        <style>
                            #paddingimg {
                                padding: 15px;
                            }

                            #img {
                                width: 150px;
                                height: 150px;
                            }

                            #tabel {
                                padding-left: 15px;
                                padding-top: 15px;
                            }

                            #field {
                                display: grid;
                                place-items: center;
                            }
                        </style>
                        <?php
                        // $serverIp = "10.127.17.10";
                        $serverIp = $_SERVER['HTTP_HOST'];
                        ?>
                        <form id="sig-add-form" role="form" novalidate enctype="multipart/form-data"
                            class="form page-form form-vertical needs-validation"
                            action="<?php print_link("sig/add?csrf_token=$csrf_token") ?>" method="post">
                            <div>
                                <div class="form-group ">
                                    <label class="control-label" for="Mesin">Mesin <span
                                            class="text-danger">*</span></label>
                                    <div id="ctrl-Line-holder" class="">
                                        <select required="" id="ctrl-Line" name="Mesin" placeholder="Pilih nama mesin"
                                            class="custom-select">
                                            <option value="" disabled selected hidden>Pilih nama mesin ...</option>
                                            <?php
                                            $Line_options = $comp_model->sig_Line_option_list();
                                            if (!empty($Line_options)) {
                                                foreach ($Line_options as $option) {
                                                    $value = (!empty($option['value']) ? $option['value'] : null);
                                                    $label = (!empty($option['label']) ? $option['label'] : $value);
													if (strcasecmp($label, 'SIG') !== 0) { continue; }
                                                    $selected = $this->set_field_selected('Mesin', $value, "");
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo $value; ?>">
                                                        <?php echo $label; ?>
                                                    </option>
                                                    <?php
                                                }
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="subheader-container d-flex justify-content-between"
                                    style="padding-bottom:20px; gap: 15px;">
                                    <h4 class="text-primary mb-0">STANDAR PEMBERSIHAN (CLEANING)</h4>
                                    <button type="button" class="btn btn-sm btn-outline-success btn-auto-ok"
                                        data-type="cleaning">
                                        <i class="fa fa-check"></i> Semua Kondisi Baik
                                    </button>
                                </div>
                                <div class="form-group ">
                                    <!-- <label class="control-label" for="Sealing_Cross_dan_Vertikal">Sealing Cross dan Vertikal <span -->
                                    <label class="control-label" for="Sealing_Cross_dan_Vertikal">Sealing Cross dan
                                        Vertikal <span class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="column" id="paddingimg">
                                            <!-- <img src="http://<?php // echo $serverIp; ?>/produksicikarang/assets/images/rvs/jalur konveyor.png" id="img"> -->
                                            <a href="http://<?php echo $serverIp; ?>/form-am/assets/images/sig/sig sealing cross.png"
                                                target="_blank">
                                                <img src="http://<?php echo $serverIp; ?>/form-am/assets/images/sig/sig sealing cross.png"
                                                    id="img">
                                            </a>
                                        </div>
                                        <div class="column" id="tabel">
                                            <table border="1" cellpadding=3 width=350px>
                                                <tr>
                                                    <th>Metode</th>
                                                    <td>Disikat</td>
                                                </tr>
                                                <tr>
                                                    <th>Alat</th>
                                                    <td>Sikat Kawat</td>
                                                </tr>
                                                <tr>
                                                    <th>Standard</th>
                                                    <td>Bersih dari kotoran</td>
                                                </tr>
                                                <tr>
                                                    <th>Durasi</th>
                                                    <td>5'</td>
                                                </tr>
                                                <tr>
                                                    <th>Pelaksanaan</td>
                                                    <td>Harian(Setiap Awal Shift 1)</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col" id="field">
                                            <div id="ctrl-Sealing_Cross_dan_Vertikal-holder" class="">
                                                <?php
                                                $Sealing_cross_dan_vertikal_options = Menu::$Kondisi_Harian;
                                                if (!empty($Sealing_cross_dan_vertikal_options)) {
                                                    foreach ($Sealing_cross_dan_vertikal_options as $option) {
                                                        $value = $option['value'];
                                                        $label = $option['label'];
                                                        // Asumsi framework menggunakan fungsi ini untuk mengecek status checked di radio
                                                        $checked = ($value == $this->set_field_value('Sealing_Cross_dan_Vertikal', "")) ? "checked" : "";
                                                        ?>
                                                        <div class="custom-control custom-radio mt-2"> <input <?php echo $checked ?> type="radio" required
                                                                id="ctrl-Sealing_Cross_dan_Vertikal-<?php echo $value ?>"
                                                                name="Sealing_Cross_dan_Vertikal" value="<?php echo $value ?>"
                                                                class="custom-control-input part-kondisi"
                                                                data-part="Sealing_Cross_dan_Vertikal">
                                                            <label class="custom-control-label"
                                                                for="ctrl-Sealing_Cross_dan_Vertikal-<?php echo $value ?>">
                                                                <?php echo $label ?>
                                                            </label>
                                                        </div>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="report-ctrl-Sealing_Cross_dan_Vertikal"
                                        style="display: none; border-bottom: 1px dashed #ccc; padding-top:15px; margin-bottom:15px;">
                                        <div class="form-group ">
                                            <label class="control-label">Kendala selama AM <span
                                                    class="text-danger">*</span></label>
                                            <div>
                                                <textarea placeholder="Enter Kendala"
                                                    name="Kendala_Sealing_Cross_dan_Vertikal"
                                                    class="form-control"></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">Kategori Tag <span
                                                    class="text-danger">*</span></label>
                                            <div>
                                                <select name="kategori_tag_Sealing_Cross_dan_Vertikal"
                                                    class="custom-select form-control">
                                                    <option value="" disabled hidden selected>Pilih Kategori Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($kategori_tag_options)) {
                                                        foreach ($kategori_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group ">
                                            <label class="control-label">Korelasi Tag <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-korelasi_tag_Sealing_Cross_dan_Vertikal-holder" class="">
                                                <select id="ctrl-korelasi_tag_Sealing_Cross_dan_Vertikal"
                                                    name="korelasi_tag_Sealing_Cross_dan_Vertikal"
                                                    data-load-select-options="kategori_ketidaksesuaian_Sealing_Cross_dan_Vertikal"
                                                    class="custom-select" placeholder="Pilih Korelasi Tag ...">
                                                    <option value="" selected hidden disabled>Pilih Korelasi Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($korelasi_tag_options)) {
                                                        foreach ($korelasi_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group ">
                                            <label class="control-label">Klasifikasi Tag <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-klasifikasi_tag_Sealing_Cross_dan_Vertikal-holder" class="">
                                                <select name="klasifikasi_tag_Sealing_Cross_dan_Vertikal"
                                                    class="custom-select" placeholder="Pilih Klasifikasi Tag ...">
                                                    <option value="" selected hidden disabled>Pilih Klasifikasi Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($klasifikasi_tag_options)) {
                                                        foreach ($klasifikasi_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group ">
                                            <label class="control-label">Kategori Ketidaksesuaian <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-kategori_ketidaksesuaian_Sealing_Cross_dan_Vertikal-holder"
                                                class="">
                                                <select id="ctrl-kategori_ketidaksesuaian_Sealing_Cross_dan_Vertikal"
                                                    name="kategori_ketidaksesuaian_Sealing_Cross_dan_Vertikal"
                                                    data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>"
                                                    class="custom-select"
                                                    placeholder="Pilih Kategori Ketidaksesuaian ...">
                                                    <option value="" selected disabled hidden>Pilih Kategori
                                                        Ketidaksesuaian ...</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label" for="Guarding_Akrilik">Guarding Akrilik <span
                                            class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="column" id="paddingimg">
                                            <a href="http://<?php echo $serverIp; ?>/form-am/assets/images/sig/sig akrilik.png"
                                                target="_blank">
                                                <img src="http://<?php echo $serverIp; ?>/form-am/assets/images/sig/sig akrilik.png"
                                                    id="img">
                                            </a>
                                        </div>
                                        <div class="column" id="tabel">
                                            <table border="1" cellpadding=3 width=350px>
                                                <tr>
                                                    <th>Metode</th>
                                                    <td>Dilap</td>
                                                </tr>
                                                <tr>
                                                    <th>Alat</th>
                                                    <td>Quiltec</td>
                                                </tr>
                                                <tr>
                                                    <th>Standard</th>
                                                    <td>Bersih dari kotoran</td>
                                                </tr>
                                                <tr>
                                                    <th>Durasi</th>
                                                    <td>2'</td>
                                                </tr>
                                                <tr>
                                                    <th>Pelaksanaan</td>
                                                    <td>Harian(Setiap Awal Shift 1)</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col" id="field">
                                            <div id="ctrl-Guarding_Akrilik-holder" class="">
                                                <?php
                                                $Guarding_akrilik_options = Menu::$Kondisi_Harian;
                                                if (!empty($Guarding_akrilik_options)) {
                                                    foreach ($Guarding_akrilik_options as $option) {
                                                        $value = $option['value'];
                                                        $label = $option['label'];
                                                        // Asumsi framework menggunakan fungsi ini untuk mengecek status checked di radio
                                                        $checked = ($value == $this->set_field_value('Guarding_Akrilik', "")) ? "checked" : "";
                                                        ?>
                                                        <div class="custom-control custom-radio mt-2"> <input <?php echo $checked ?> type="radio" required
                                                                id="ctrl-Guarding_Akrilik-<?php echo $value ?>"
                                                                name="Guarding_Akrilik" value="<?php echo $value ?>"
                                                                class="custom-control-input part-kondisi"
                                                                data-part="Guarding_Akrilik">
                                                            <label class="custom-control-label"
                                                                for="ctrl-Guarding_Akrilik-<?php echo $value ?>">
                                                                <?php echo $label ?>
                                                            </label>
                                                        </div>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="report-ctrl-Guarding_Akrilik"
                                        style="display: none; border-bottom: 1px dashed #ccc; padding-top:15px; margin-bottom:15px;">
                                        <div class="form-group ">
                                            <label class="control-label">Kendala selama AM <span
                                                    class="text-danger">*</span></label>
                                            <div>
                                                <textarea placeholder="Enter Kendala" name="Kendala_Guarding_Akrilik"
                                                    class="form-control"></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">Kategori Tag <span
                                                    class="text-danger">*</span></label>
                                            <div>
                                                <select name="kategori_tag_Guarding_Akrilik"
                                                    class="custom-select form-control">
                                                    <option value="" disabled hidden selected>Pilih Kategori Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($kategori_tag_options)) {
                                                        foreach ($kategori_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group ">
                                            <label class="control-label">Korelasi Tag <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-korelasi_tag_Guarding_Akrilik-holder" class="">
                                                <select id="ctrl-korelasi_tag_Guarding_Akrilik"
                                                    name="korelasi_tag_Guarding_Akrilik"
                                                    data-load-select-options="kategori_ketidaksesuaian_Guarding_Akrilik"
                                                    class="custom-select" placeholder="Pilih Korelasi Tag ...">
                                                    <option value="" selected hidden disabled>Pilih Korelasi Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($korelasi_tag_options)) {
                                                        foreach ($korelasi_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group ">
                                            <label class="control-label">Klasifikasi Tag <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-klasifikasi_tag_Guarding_Akrilik-holder" class="">
                                                <select name="klasifikasi_tag_Guarding_Akrilik"
                                                    class="custom-select" placeholder="Pilih Klasifikasi Tag ...">
                                                    <option value="" selected hidden disabled>Pilih Klasifikasi Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($klasifikasi_tag_options)) {
                                                        foreach ($klasifikasi_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group ">
                                            <label class="control-label">Kategori Ketidaksesuaian <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-kategori_ketidaksesuaian_Guarding_Akrilik-holder" class="">
                                                <select id="ctrl-kategori_ketidaksesuaian_Guarding_Akrilik"
                                                    name="kategori_ketidaksesuaian_Guarding_Akrilik"
                                                    data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>"
                                                    class="custom-select"
                                                    placeholder="Pilih Kategori Ketidaksesuaian ...">
                                                    <option value="" selected disabled hidden>Pilih Kategori
                                                        Ketidaksesuaian ...</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label" for="Jalur_Conveyor">Jalur Conveyor <span
                                            class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="column" id="paddingimg">
                                            <a href="http://<?php echo $serverIp; ?>/form-am/assets/images/sig/sig conveyor.png"
                                                target="_blank">
                                                <img src="http://<?php echo $serverIp; ?>/form-am/assets/images/sig/sig conveyor.png"
                                                    id="img">
                                            </a>
                                        </div>
                                        <div class="column" id="tabel">
                                            <table border="1" cellpadding=3 width=350px>
                                                <tr>
                                                    <th>Metode</th>
                                                    <td>Dilap</td>
                                                </tr>
                                                <tr>
                                                    <th>Alat</th>
                                                    <td>Quiltec</td>
                                                </tr>
                                                <tr>
                                                    <th>Standard</th>
                                                    <td>Bersih dari kotoran</td>
                                                </tr>
                                                <tr>
                                                    <th>Durasi</th>
                                                    <td>2'</td>
                                                </tr>
                                                <tr>
                                                    <th>Pelaksanaan</td>
                                                    <td>Harian(Setiap Awal Shift 1)</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col" id="field">
                                            <div id="ctrl-Jalur_Conveyor-holder" class="">
                                                <?php
                                                $Jalur_conveyor_options = Menu::$Kondisi_Harian;
                                                if (!empty($Jalur_conveyor_options)) {
                                                    foreach ($Jalur_conveyor_options as $option) {
                                                        $value = $option['value'];
                                                        $label = $option['label'];
                                                        // Asumsi framework menggunakan fungsi ini untuk mengecek status checked di radio
                                                        $checked = ($value == $this->set_field_value('Jalur_Conveyor', "")) ? "checked" : "";
                                                        ?>
                                                        <div class="custom-control custom-radio mt-2"> <input <?php echo $checked ?> type="radio" required
                                                                id="ctrl-Jalur_Conveyor-<?php echo $value ?>"
                                                                name="Jalur_Conveyor" value="<?php echo $value ?>"
                                                                class="custom-control-input part-kondisi"
                                                                data-part="Jalur_Conveyor">
                                                            <label class="custom-control-label"
                                                                for="ctrl-Jalur_Conveyor-<?php echo $value ?>">
                                                                <?php echo $label ?>
                                                            </label>
                                                        </div>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="report-ctrl-Jalur_Conveyor"
                                        style="display: none; border-bottom: 1px dashed #ccc; padding-top:15px; margin-bottom:15px;">
                                        <div class="form-group ">
                                            <label class="control-label">Kendala selama AM <span
                                                    class="text-danger">*</span></label>
                                            <div>
                                                <textarea placeholder="Enter Kendala" name="Kendala_Jalur_Conveyor"
                                                    class="form-control"></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">Kategori Tag <span
                                                    class="text-danger">*</span></label>
                                            <div>
                                                <select name="kategori_tag_Jalur_Conveyor"
                                                    class="custom-select form-control">
                                                    <option value="" disabled hidden selected>Pilih Kategori Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($kategori_tag_options)) {
                                                        foreach ($kategori_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group ">
                                            <label class="control-label">Korelasi Tag <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-korelasi_tag_Jalur_Conveyor-holder" class="">
                                                <select id="ctrl-korelasi_tag_Jalur_Conveyor"
                                                    name="korelasi_tag_Jalur_Conveyor"
                                                    data-load-select-options="kategori_ketidaksesuaian_Jalur_Conveyor"
                                                    class="custom-select" placeholder="Pilih Korelasi Tag ...">
                                                    <option value="" selected hidden disabled>Pilih Korelasi Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($korelasi_tag_options)) {
                                                        foreach ($korelasi_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group ">
                                            <label class="control-label">Klasifikasi Tag <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-klasifikasi_tag_Jalur_Conveyor-holder" class="">
                                                <select name="klasifikasi_tag_Jalur_Conveyor"
                                                    class="custom-select" placeholder="Pilih Klasifikasi Tag ...">
                                                    <option value="" selected hidden disabled>Pilih Klasifikasi Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($klasifikasi_tag_options)) {
                                                        foreach ($klasifikasi_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group ">
                                            <label class="control-label">Kategori Ketidaksesuaian <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-kategori_ketidaksesuaian_Jalur_Conveyor-holder" class="">
                                                <select id="ctrl-kategori_ketidaksesuaian_Jalur_Conveyor"
                                                    name="kategori_ketidaksesuaian_Jalur_Conveyor"
                                                    data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>"
                                                    class="custom-select"
                                                    placeholder="Pilih Kategori Ketidaksesuaian ...">
                                                    <option value="" selected disabled hidden>Pilih Kategori
                                                        Ketidaksesuaian ...</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label" for="Vacuum_Hood">Vacuum Hood <span
                                            class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="column" id="paddingimg">
                                            <a href="http://<?php echo $serverIp; ?>/form-am/assets/images/sig/sig vacuum hood.png"
                                                target="_blank">
                                                <img src="http://<?php echo $serverIp; ?>/form-am/assets/images/sig/sig vacuum hood.png"
                                                    id="img">
                                            </a>
                                        </div>
                                        <div class="column" id="tabel">
                                            <table border="1" cellpadding=3 width=350px>
                                                <tr>
                                                    <th>Metode</th>
                                                    <td>Disemprot angin dan sistem filtrasi by SMC</td>
                                                </tr>
                                                <tr>
                                                    <th>Alat</th>
                                                    <td>Air Gun</td>
                                                </tr>
                                                <tr>
                                                    <th>Standard</th>
                                                    <td>Bersih dari kotoran</td>
                                                </tr>
                                                <tr>
                                                    <th>Durasi</th>
                                                    <td>6'</td>
                                                </tr>
                                                <tr>
                                                    <th>Pelaksanaan</td>
                                                    <td>Harian(Setiap Awal Shift 1)</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col" id="field">
                                            <div id="ctrl-Vacuum_Hood-holder" class="">
                                                <?php
                                                $Vacuum_Hood_options = Menu::$Kondisi_Harian;
                                                if (!empty($Vacuum_Hood_options)) {
                                                    foreach ($Vacuum_Hood_options as $option) {
                                                        $value = $option['value'];
                                                        $label = $option['label'];
                                                        // Asumsi framework menggunakan fungsi ini untuk mengecek status checked di radio
                                                        $checked = ($value == $this->set_field_value('Vacuum_Hood', "")) ? "checked" : "";
                                                        ?>
                                                        <div class="custom-control custom-radio mt-2"> <input <?php echo $checked ?> type="radio" required
                                                                id="ctrl-Vacuum_Hood-<?php echo $value ?>" name="Vacuum_Hood"
                                                                value="<?php echo $value ?>"
                                                                class="custom-control-input part-kondisi"
                                                                data-part="Vacuum_Hood">
                                                            <label class="custom-control-label"
                                                                for="ctrl-Vacuum_Hood-<?php echo $value ?>">
                                                                <?php echo $label ?>
                                                            </label>
                                                        </div>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="report-ctrl-Vacuum_Hood"
                                        style="display: none; border-bottom: 1px dashed #ccc; padding-top:15px; margin-bottom:15px;">
                                        <div class="form-group ">
                                            <label class="control-label">Kendala selama AM <span
                                                    class="text-danger">*</span></label>
                                            <div>
                                                <textarea placeholder="Enter Kendala" name="Kendala_Vacuum_Hood"
                                                    class="form-control"></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">Kategori Tag <span
                                                    class="text-danger">*</span></label>
                                            <div>
                                                <select name="kategori_tag_Vacuum_Hood"
                                                    class="custom-select form-control">
                                                    <option value="" disabled hidden selected>Pilih Kategori Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($kategori_tag_options)) {
                                                        foreach ($kategori_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group ">
                                            <label class="control-label">Korelasi Tag <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-korelasi_tag_Vacuum_Hood-holder" class="">
                                                <select id="ctrl-korelasi_tag_Vacuum_Hood"
                                                    name="korelasi_tag_Vacuum_Hood"
                                                    data-load-select-options="kategori_ketidaksesuaian_Vacuum_Hood"
                                                    class="custom-select" placeholder="Pilih Korelasi Tag ...">
                                                    <option value="" selected hidden disabled>Pilih Korelasi Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($korelasi_tag_options)) {
                                                        foreach ($korelasi_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group ">
                                            <label class="control-label">Klasifikasi Tag <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-klasifikasi_tag_Vacuum_Hood-holder" class="">
                                                <select name="klasifikasi_tag_Vacuum_Hood"
                                                    class="custom-select" placeholder="Pilih Klasifikasi Tag ...">
                                                    <option value="" selected hidden disabled>Pilih Klasifikasi Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($klasifikasi_tag_options)) {
                                                        foreach ($klasifikasi_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group ">
                                            <label class="control-label">Kategori Ketidaksesuaian <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-kategori_ketidaksesuaian_Vacuum_Hood-holder" class="">
                                                <select id="ctrl-kategori_ketidaksesuaian_Vacuum_Hood"
                                                    name="kategori_ketidaksesuaian_Vacuum_Hood"
                                                    data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>"
                                                    class="custom-select"
                                                    placeholder="Pilih Kategori Ketidaksesuaian ...">
                                                    <option value="" selected disabled hidden>Pilih Kategori
                                                        Ketidaksesuaian ...</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label" for="Antistatic">Antistatic
                                        <!-- <span class="text-danger">*</span></label> -->
                                    <div class="row">
                                        <div class="column" id="paddingimg">
                                            <a href="http://<?php echo $serverIp; ?>/form-am/assets/images/sig/sig antistatic.png"
                                                target="_blank">
                                                <img src="http://<?php echo $serverIp; ?>/form-am/assets/images/sig/sig antistatic.png"
                                                    id="img">
                                            </a>
                                        </div>
                                        <div class="column" id="tabel">
                                            <table border="1" cellpadding=3 width=350px>
                                                <tr>
                                                    <th>Metode</th>
                                                    <td>Dilap</td>
                                                </tr>
                                                <tr>
                                                    <th>Alat</th>
                                                    <td>Quiltec</td>
                                                </tr>
                                                <tr>
                                                    <th>Standard</th>
                                                    <td>Bersih dari kotoran</td>
                                                </tr>
                                                <tr>
                                                    <th>Durasi</th>
                                                    <td>6'</td>
                                                </tr>
                                                <tr>
                                                    <th style="background-color: rgba(0, 204, 255, 0.4);">Pelaksanaan</td>
                                                    <td class="" style="background-color: rgba(0, 204, 255, 0.4);">Bulanan(Setiap W1 Senin Shift 1)</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col" id="field">
                                            <div id="ctrl-Antistatic-holder" class="">
                                                <?php
                                                $Antistatic_options = Menu::$antistatic;
                                                if (!empty($Antistatic_options)) {
                                                    foreach ($Antistatic_options as $option) {
                                                        $value = $option['value'];
                                                        $label = $option['label'];
                                                        // Asumsi framework menggunakan fungsi ini untuk mengecek status checked di radio
                                                        $checked = ($value == $this->set_field_value('Antistatic', "")) ? "checked" : "";
                                                        ?>
                                                        <div class="custom-control custom-radio mt-2"> <input <?php echo $checked ?> type="radio" required
                                                                id="ctrl-Antistatic-<?php echo $value ?>" name="Antistatic"
                                                                value="<?php echo $value ?>"
                                                                class="custom-control-input part-kondisi"
                                                                data-part="Antistatic">
                                                            <label class="custom-control-label"
                                                                for="ctrl-Antistatic-<?php echo $value ?>">
                                                                <?php echo $label ?>
                                                            </label>
                                                        </div>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="report-ctrl-Antistatic"
                                        style="display: none; border-bottom: 1px dashed #ccc; padding-top:15px; margin-bottom:15px;">
                                        <div class="form-group ">
                                            <label class="control-label">Kendala selama AM <span
                                                    class="text-danger">*</span></label>
                                            <div>
                                                <textarea placeholder="Enter Kendala" name="Kendala_Antistatic"
                                                    class="form-control"></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">Kategori Tag <span
                                                    class="text-danger">*</span></label>
                                            <div>
                                                <select name="kategori_tag_Antistatic"
                                                    class="custom-select form-control">
                                                    <option value="" disabled hidden selected>Pilih Kategori Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($kategori_tag_options)) {
                                                        foreach ($kategori_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>">
                                                                <?php echo $label; ?>
                                                            </option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group ">
                                            <label class="control-label">Korelasi Tag <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-korelasi_tag_Antistatic-holder" class="">
                                                <select id="ctrl-korelasi_tag_Antistatic" name="korelasi_tag_Antistatic"
                                                    data-load-select-options="kategori_ketidaksesuaian_Antistatic"
                                                    class="custom-select" placeholder="Pilih Korelasi Tag ...">
                                                    <option value="" selected hidden disabled>Pilih Korelasi Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($korelasi_tag_options)) {
                                                        foreach ($korelasi_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>">
                                                                <?php echo $label; ?>
                                                            </option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group ">
                                            <label class="control-label">Klasifikasi Tag <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-klasifikasi_tag_Antistatic-holder" class="">
                                                <select name="klasifikasi_tag_Antistatic"
                                                    class="custom-select" placeholder="Pilih Klasifikasi Tag ...">
                                                    <option value="" selected hidden disabled>Pilih Klasifikasi Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($klasifikasi_tag_options)) {
                                                        foreach ($klasifikasi_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group ">
                                            <label class="control-label">Kategori Ketidaksesuaian <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-kategori_ketidaksesuaian_Antistatic-holder" class="">
                                                <select id="ctrl-kategori_ketidaksesuaian_Antistatic"
                                                    name="kategori_ketidaksesuaian_Antistatic"
                                                    data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>"
                                                    class="custom-select"
                                                    placeholder="Pilih Kategori Ketidaksesuaian ...">
                                                    <option value="" selected disabled hidden>Pilih Kategori
                                                        Ketidaksesuaian ...</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- ================================= BATAS CLEANING SIG ================================= BATAS CLEANING SIG ================================ BATAS CLEANING SIG ======================================================= BATAS CLEANING SIG ========================== -->

                                <div class="subheader-container d-flex justify-content-between "
                                    style="padding-bottom:20px; gap: 15px;">
                                    <h4 class="text-primary">STANDAR PENGECEKAN & PENGENCANGAN (INSPECTION & TIGHTENING)
                                    </h4>
                                    <button type="button" class="btn btn-sm btn-outline-success btn-auto-ok"
                                        data-type="inspection">
                                        <i class="fa fa-check"></i> Semua Kondisi Baik
                                    </button>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label" for="Tekanan_Angin_Suplai">Tekanan
                                        Angin Suplai <span class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="column" id="paddingimg">
                                            <a href="http://<?php echo $serverIp; ?>/form-am/assets/images/sig/sig pressure.png"
                                                target="_blank">
                                                <img src="http://<?php echo $serverIp; ?>/form-am/assets/images/sig/sig pressure.png"
                                                    id="img">
                                            </a>
                                        </div>
                                        <div class="column" id="tabel">
                                            <table border="1" cellpadding=3 width=350px>
                                                <tr>
                                                    <th>Metode</th>
                                                    <td>Dicek, disetting</td>
                                                </tr>
                                                <tr>
                                                    <th>Alat</th>
                                                    <td>Visual</td>
                                                </tr>
                                                <tr>
                                                    <th>Standard</th>
                                                    <td>Tekanan angin 0.8 - 1.5 bar</td>
                                                </tr>
                                                <tr>
                                                    <th>Durasi</th>
                                                    <td>1'</td>
                                                </tr>
                                                <tr>
                                                    <th>Pelaksanaan</td>
                                                    <td>Harian(Setiap Awal Shift 1)</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col" id="field">
                                            <div id="ctrl-Tekanan_Angin_Suplai-holder" class="">
                                                <?php
                                                $Tekanan_Angin_Suplai_options = Menu::$Kondisi_Harian;
                                                if (!empty($Tekanan_Angin_Suplai_options)) {
                                                    foreach ($Tekanan_Angin_Suplai_options as $option) {
                                                        $value = $option['value'];
                                                        $label = $option['label'];
                                                        // Asumsi framework menggunakan fungsi ini untuk mengecek status checked di radio
                                                        $checked = ($value == $this->set_field_value('Tekanan_Angin_Suplai', "")) ? "checked" : "";
                                                        ?>
                                                        <div class="custom-control custom-radio mt-2"> <input <?php echo $checked ?> type="radio" required
                                                                id="ctrl-Tekanan_Angin_Suplai-<?php echo $value ?>"
                                                                name="Tekanan_Angin_Suplai" value="<?php echo $value ?>"
                                                                class="custom-control-input part-kondisi"
                                                                data-part="Tekanan_Angin_Suplai">
                                                            <label class="custom-control-label"
                                                                for="ctrl-Tekanan_Angin_Suplai-<?php echo $value ?>">
                                                                <?php echo $label ?>
                                                            </label>
                                                        </div>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                                <div class="form-group additional-field">
                                                    <div class="input-group">
                                                        <input
                                                            value="<?php echo $this->set_field_value('Value_Tekanan_Angin', ""); ?>"
                                                            type="text" placeholder="Tekanan Angin Pemakaian" step="1"
                                                            required="" name="Value_Tekanan_Angin"
                                                            class="form-control " />
                                                        <div class="input-group-append">
                                                            <span class="input-group-text">BAR</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="report-ctrl-Tekanan_Angin_Suplai"
                                        style="display: none; border-bottom: 1px dashed #ccc; padding-top:15px; margin-bottom:15px;">
                                        <div class="form-group ">
                                            <label class="control-label">Kendala selama AM <span
                                                    class="text-danger">*</span></label>
                                            <div>
                                                <textarea placeholder="Enter Kendala"
                                                    name="Kendala_Tekanan_Angin_Suplai" class="form-control"></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">Kategori Tag <span
                                                    class="text-danger">*</span></label>
                                            <div>
                                                <select name="kategori_tag_Tekanan_Angin_Suplai"
                                                    class="custom-select form-control">
                                                    <option value="" disabled hidden selected>Pilih Kategori Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($kategori_tag_options)) {
                                                        foreach ($kategori_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?>
                                                            </option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group ">
                                            <label class="control-label">Korelasi Tag <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-korelasi_tag_Tekanan_Angin_Suplai-holder" class="">
                                                <select id="ctrl-korelasi_tag_Tekanan_Angin_Suplai"
                                                    name="korelasi_tag_Tekanan_Angin_Suplai"
                                                    data-load-select-options="kategori_ketidaksesuaian_Tekanan_Angin_Suplai"
                                                    class="custom-select" placeholder="Pilih Korelasi Tag ...">
                                                    <option value="" selected hidden disabled>Pilih Korelasi Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($korelasi_tag_options)) {
                                                        foreach ($korelasi_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?>
                                                            </option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group ">
                                            <label class="control-label">Klasifikasi Tag <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-klasifikasi_tag_Tekanan_Angin_Suplai-holder" class="">
                                                <select name="klasifikasi_tag_Tekanan_Angin_Suplai"
                                                    class="custom-select" placeholder="Pilih Klasifikasi Tag ...">
                                                    <option value="" selected hidden disabled>Pilih Klasifikasi Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($klasifikasi_tag_options)) {
                                                        foreach ($klasifikasi_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group ">
                                            <label class="control-label">Kategori Ketidaksesuaian <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-kategori_ketidaksesuaian_Tekanan_Angin_Suplai-holder"
                                                class="">
                                                <select id="ctrl-kategori_ketidaksesuaian_Tekanan_Angin_Suplai"
                                                    name="kategori_ketidaksesuaian_Tekanan_Angin_Suplai"
                                                    data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>"
                                                    class="custom-select"
                                                    placeholder="Pilih Kategori Ketidaksesuaian ...">
                                                    <option value="" selected disabled hidden>Pilih Kategori
                                                        Ketidaksesuaian ...</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label" for="Jarak_Slider_dengan_Nozzle">Jarak Slider
                                        dengan
                                        Nozzle <span class="text-danger">*</span>
                                    </label>
                                    <div class="row">
                                        <div class="column" id="paddingimg">
                                            <a href="http://<?php echo $serverIp; ?>/form-am/assets/images/sig/sig slider nozzle.png"
                                                target="_blank">
                                                <img src="http://<?php echo $serverIp; ?>/form-am/assets/images/sig/sig slider nozzle.png"
                                                    id="img">
                                            </a>
                                        </div>
                                        <div class="column" id="tabel">
                                            <table border="1" cellpadding=3 width=350px>
                                                <tr>
                                                    <th>Metode</th>
                                                    <td>Dicek</td>
                                                </tr>
                                                <tr>
                                                    <th>Alat</th>
                                                    <td>Filler Gauge</td>
                                                </tr>
                                                <tr>
                                                    <th>Standard</th>
                                                    <td>Filler gauge 0.2 tidak ada bulk yang keluar</td>
                                                </tr>
                                                <tr>
                                                    <th>Durasi</th>
                                                    <td>5'</td>
                                                </tr>
                                                <tr>
                                                    <th>Pelaksanaan</td>
                                                    <td>Harian(Setiap Awal Shift 1)</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col" id="field">
                                            <div id="ctrl-Jarak_Slider_dengan_Nozzle-holder" class="">
                                                <?php
                                                $Jarak_Slider_dengan_Nozzle_options = Menu::$Kondisi_Harian;
                                                if (!empty($Jarak_Slider_dengan_Nozzle_options)) {
                                                    foreach ($Jarak_Slider_dengan_Nozzle_options as $option) {
                                                        $value = $option['value'];
                                                        $label = $option['label'];
                                                        // Asumsi framework menggunakan fungsi ini untuk mengecek status checked di radio
                                                        $checked = ($value == $this->set_field_value('Jarak_Slider_dengan_Nozzle', "")) ? "checked" : "";
                                                        ?>
                                                        <div class="custom-control custom-radio mt-2"> <input <?php echo $checked ?> type="radio" required
                                                                id="ctrl-Jarak_Slider_dengan_Nozzle-<?php echo $value ?>"
                                                                name="Jarak_Slider_dengan_Nozzle" value="<?php echo $value ?>"
                                                                class="custom-control-input part-kondisi"
                                                                data-part="Jarak_Slider_dengan_Nozzle">
                                                            <label class="custom-control-label"
                                                                for="ctrl-Jarak_Slider_dengan_Nozzle-<?php echo $value ?>">
                                                                <?php echo $label ?>
                                                            </label>
                                                        </div>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="report-ctrl-Jarak_Slider_dengan_Nozzle"
                                        style="display: none; border-bottom: 1px dashed #ccc; padding-top:15px; margin-bottom:15px;">
                                        <div class="form-group ">
                                            <label class="control-label">Kendala selama AM <span
                                                    class="text-danger">*</span></label>
                                            <div>
                                                <textarea placeholder="Enter Kendala"
                                                    name="Kendala_Jarak_Slider_dengan_Nozzle"
                                                    class="form-control"></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">Kategori Tag <span
                                                    class="text-danger">*</span></label>
                                            <div>
                                                <select name="kategori_tag_Jarak_Slider_dengan_Nozzle"
                                                    class="custom-select form-control">
                                                    <option value="" disabled hidden selected>Pilih Kategori Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($kategori_tag_options)) {
                                                        foreach ($kategori_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?>
                                                            </option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group ">
                                            <label class="control-label">Korelasi Tag <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-korelasi_tag_Jarak_Slider_dengan_Nozzle-holder" class="">
                                                <select id="ctrl-korelasi_tag_Jarak_Slider_dengan_Nozzle"
                                                    name="korelasi_tag_Jarak_Slider_dengan_Nozzle"
                                                    data-load-select-options="kategori_ketidaksesuaian_Jarak_Slider_dengan_Nozzle"
                                                    class="custom-select" placeholder="Pilih Korelasi Tag ...">
                                                    <option value="" selected hidden disabled>Pilih Korelasi Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($korelasi_tag_options)) {
                                                        foreach ($korelasi_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?>
                                                            </option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group ">
                                            <label class="control-label">Klasifikasi Tag <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-klasifikasi_tag_Jarak_Slider_dengan_Nozzle-holder" class="">
                                                <select name="klasifikasi_tag_Jarak_Slider_dengan_Nozzle"
                                                    class="custom-select" placeholder="Pilih Klasifikasi Tag ...">
                                                    <option value="" selected hidden disabled>Pilih Klasifikasi Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($klasifikasi_tag_options)) {
                                                        foreach ($klasifikasi_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group ">
                                            <label class="control-label">Kategori Ketidaksesuaian <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-kategori_ketidaksesuaian_Jarak_Slider_dengan_Nozzle-holder"
                                                class="">
                                                <select id="ctrl-kategori_ketidaksesuaian_Jarak_Slider_dengan_Nozzle"
                                                    name="kategori_ketidaksesuaian_Jarak_Slider_dengan_Nozzle"
                                                    data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>"
                                                    class="custom-select"
                                                    placeholder="Pilih Kategori Ketidaksesuaian ...">
                                                    <option value="" selected disabled hidden>Pilih Kategori
                                                        Ketidaksesuaian ...</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label" for="Rol_Penarik_Sachet_dan_Foil_Slitting_Shim">Rol
                                        Penarik Sachet dan Foil
                                        Slitting Shim <span class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="column" id="paddingimg">
                                            <a href="http://<?php echo $serverIp; ?>/form-am/assets/images/sig/sig roll penarik &slitting shim.png"
                                                target="_blank">
                                                <img src="http://<?php echo $serverIp; ?>/form-am/assets/images/sig/sig roll penarik &slitting shim.png"
                                                    id="img">
                                            </a>
                                        </div>
                                        <div class="column" id="tabel">
                                            <table border="1" cellpadding=3 width=350px>
                                                <tr>
                                                    <th>Metode</th>
                                                    <td>Dicek</td>
                                                </tr>
                                                <tr>
                                                    <th>Alat</th>
                                                    <td>Visual</td>
                                                </tr>
                                                <tr>
                                                    <th>Standard</th>
                                                    <td>Tidak aus, tidak ada cacat, berfungsi, dan kondisi blade
                                                        didak
                                                        melengkung</td>
                                                </tr>
                                                <tr>
                                                    <th>Durasi</th>
                                                    <td>1'</td>
                                                </tr>
                                                <tr>
                                                    <th>Pelaksanaan</td>
                                                    <td>Harian(Setiap Awal Shift 1)</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col" id="field">
                                            <div id="ctrl-Rol_Penarik_Sachet_dan_Foil_Slitting_Shim-holder" class="">
                                                <?php
                                                $Rol_Penarik_Sachet_dan_Foil_Slitting_Shim_options = Menu::$Kondisi_Harian;
                                                if (!empty($Rol_Penarik_Sachet_dan_Foil_Slitting_Shim_options)) {
                                                    foreach ($Rol_Penarik_Sachet_dan_Foil_Slitting_Shim_options as $option) {
                                                        $value = $option['value'];
                                                        $label = $option['label'];
                                                        // Asumsi framework menggunakan fungsi ini untuk mengecek status checked di radio
                                                        $checked = ($value == $this->set_field_value('Rol_Penarik_Sachet_dan_Foil_Slitting_Shim', "")) ? "checked" : "";
                                                        ?>
                                                        <div class="custom-control custom-radio mt-2"> <input <?php echo $checked ?> type="radio" required
                                                                id="ctrl-Rol_Penarik_Sachet_dan_Foil_Slitting_Shim-<?php echo $value ?>"
                                                                name="Rol_Penarik_Sachet_dan_Foil_Slitting_Shim"
                                                                value="<?php echo $value ?>"
                                                                class="custom-control-input part-kondisi"
                                                                data-part="Rol_Penarik_Sachet_dan_Foil_Slitting_Shim">
                                                            <label class="custom-control-label"
                                                                for="ctrl-Rol_Penarik_Sachet_dan_Foil_Slitting_Shim-<?php echo $value ?>">
                                                                <?php echo $label ?>
                                                            </label>
                                                        </div>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="report-ctrl-Rol_Penarik_Sachet_dan_Slitting_Shim"
                                        style="display: none; border-bottom: 1px dashed #ccc; padding-top:15px; margin-bottom:15px;">
                                        <div class="form-group ">
                                            <label class="control-label">Kendala selama AM <span
                                                    class="text-danger">*</span></label>
                                            <div>
                                                <textarea placeholder="Enter Kendala"
                                                    name="Kendala_Rol_Penarik_Sachet_dan_Slitting_Shim"
                                                    class="form-control"></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">Kategori Tag <span
                                                    class="text-danger">*</span></label>
                                            <div>
                                                <select name="kategori_tag_Rol_Penarik_Sachet_dan_Slitting_Shim"
                                                    class="custom-select form-control">
                                                    <option value="" disabled hidden selected>Pilih Kategori Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($kategori_tag_options)) {
                                                        foreach ($kategori_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?>
                                                            </option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group ">
                                            <label class="control-label">Korelasi Tag <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-korelasi_tag_Rol_Penarik_Sachet_dan_Slitting_Shim-holder"
                                                class="">
                                                <select id="ctrl-korelasi_tag_Rol_Penarik_Sachet_dan_Slitting_Shim"
                                                    name="korelasi_tag_Rol_Penarik_Sachet_dan_Slitting_Shim"
                                                    data-load-select-options="kategori_ketidaksesuaian_Rol_Penarik_Sachet_dan_Slitting_Shim"
                                                    class="custom-select" placeholder="Pilih Korelasi Tag ...">
                                                    <option value="" selected hidden disabled>Pilih Korelasi Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($korelasi_tag_options)) {
                                                        foreach ($korelasi_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?>
                                                            </option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group ">
                                            <label class="control-label">Klasifikasi Tag <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-klasifikasi_tag_Rol_Penarik_Sachet_dan_Foil_Slitting_Shim-holder" class="">
                                                <select name="klasifikasi_tag_Rol_Penarik_Sachet_dan_Foil_Slitting_Shim"
                                                    class="custom-select" placeholder="Pilih Klasifikasi Tag ...">
                                                    <option value="" selected hidden disabled>Pilih Klasifikasi Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($klasifikasi_tag_options)) {
                                                        foreach ($klasifikasi_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group ">
                                            <label class="control-label">Kategori Ketidaksesuaian <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-kategori_ketidaksesuaian_Rol_Penarik_Sachet_dan_Slitting_Shim-holder"
                                                class="">
                                                <select
                                                    id="ctrl-kategori_ketidaksesuaian_Rol_Penarik_Sachet_dan_Slitting_Shim"
                                                    name="kategori_ketidaksesuaian_Rol_Penarik_Sachet_dan_Slitting_Shim"
                                                    data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>"
                                                    class="custom-select"
                                                    placeholder="Pilih Kategori Ketidaksesuaian ...">
                                                    <option value="" selected disabled hidden>Pilih Kategori
                                                        Ketidaksesuaian ...</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label" for="Pisau_Belah">Pisau Belah <span
                                            class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="column" id="paddingimg">
                                            <a href="http://<?php echo $serverIp; ?>/form-am/assets/images/sig/sig pisau belah.png"
                                                target="_blank">
                                                <img src="http://<?php echo $serverIp; ?>/form-am/assets/images/sig/sig pisau belah.png"
                                                    id="img">
                                            </a>
                                        </div>
                                        <div class="column" id="tabel">
                                            <table border="1" cellpadding=3 width=350px>
                                                <tr>
                                                    <th>Metode</th>
                                                    <td>Dicek</td>
                                                </tr>
                                                <tr>
                                                    <th>Alat</th>
                                                    <td>Visual</td>
                                                </tr>
                                                <tr>
                                                    <th>Standard</th>
                                                    <td>Kondisi pisau tidak geripis</td>
                                                </tr>
                                                <tr>
                                                    <th>Durasi</th>
                                                    <td>1'</td>
                                                </tr>
                                                <tr>
                                                    <th>Pelaksanaan</td>
                                                    <td>Harian(Setiap Awal Shift 1)</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col" id="field">
                                            <div id="ctrl-Pisau_Belah-holder" class="">
                                                <?php
                                                $Pisau_Belah_options = Menu::$Kondisi_Harian;
                                                if (!empty($Pisau_Belah_options)) {
                                                    foreach ($Pisau_Belah_options as $option) {
                                                        $value = $option['value'];
                                                        $label = $option['label'];
                                                        // Asumsi framework menggunakan fungsi ini untuk mengecek status checked di radio
                                                        $checked = ($value == $this->set_field_value('Pisau_Belah', "")) ? "checked" : "";
                                                        ?>
                                                        <div class="custom-control custom-radio mt-2"> <input <?php echo $checked ?> type="radio" required
                                                                id="ctrl-Pisau_Belah-<?php echo $value ?>" name="Pisau_Belah"
                                                                value="<?php echo $value ?>"
                                                                class="custom-control-input part-kondisi"
                                                                data-part="Pisau_Belah">
                                                            <label class="custom-control-label"
                                                                for="ctrl-Pisau_Belah-<?php echo $value ?>">
                                                                <?php echo $label ?>
                                                            </label>
                                                        </div>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="report-ctrl-Pisau_Belah"
                                        style="display: none; border-bottom: 1px dashed #ccc; padding-top:15px; margin-bottom:15px;">
                                        <div class="form-group ">
                                            <label class="control-label">Kendala selama AM <span
                                                    class="text-danger">*</span></label>
                                            <div>
                                                <textarea placeholder="Enter Kendala" name="Kendala_Pisau_Belah"
                                                    class="form-control"></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">Kategori Tag <span
                                                    class="text-danger">*</span></label>
                                            <div>
                                                <select name="kategori_tag_Pisau_Belah"
                                                    class="custom-select form-control">
                                                    <option value="" disabled hidden selected>Pilih Kategori Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($kategori_tag_options)) {
                                                        foreach ($kategori_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?>
                                                            </option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group ">
                                            <label class="control-label">Korelasi Tag <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-korelasi_tag_Pisau_Belah-holder" class="">
                                                <select id="ctrl-korelasi_tag_Pisau_Belah"
                                                    name="korelasi_tag_Pisau_Belah"
                                                    data-load-select-options="kategori_ketidaksesuaian_Pisau_Belah"
                                                    class="custom-select" placeholder="Pilih Korelasi Tag ...">
                                                    <option value="" selected hidden disabled>Pilih Korelasi Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($korelasi_tag_options)) {
                                                        foreach ($korelasi_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?>
                                                            </option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group ">
                                            <label class="control-label">Klasifikasi Tag <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-klasifikasi_tag_Pisau_Belah-holder" class="">
                                                <select name="klasifikasi_tag_Pisau_Belah"
                                                    class="custom-select" placeholder="Pilih Klasifikasi Tag ...">
                                                    <option value="" selected hidden disabled>Pilih Klasifikasi Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($klasifikasi_tag_options)) {
                                                        foreach ($klasifikasi_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group ">
                                            <label class="control-label">Kategori Ketidaksesuaian <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-kategori_ketidaksesuaian_Pisau_Belah-holder" class="">
                                                <select id="ctrl-kategori_ketidaksesuaian_Pisau_Belah"
                                                    name="kategori_ketidaksesuaian_Pisau_Belah"
                                                    data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>"
                                                    class="custom-select"
                                                    placeholder="Pilih Kategori Ketidaksesuaian ...">
                                                    <option value="" selected disabled hidden>Pilih Kategori
                                                        Ketidaksesuaian ...</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label" for="Modul_Pisau">Modul Pisau <span
                                            class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="column" id="paddingimg">
                                            <a href="http://<?php echo $serverIp; ?>/form-am/assets/images/sig/sig modul pisau.png"
                                                target="_blank">
                                                <img src="http://<?php echo $serverIp; ?>/form-am/assets/images/sig/sig modul pisau.png"
                                                    id="img">
                                            </a>
                                        </div>
                                        <div class="column" id="tabel">
                                            <table border="1" cellpadding=3 width=350px>
                                                <tr>
                                                    <th>Metode</th>
                                                    <td>Dicek</td>
                                                </tr>
                                                <tr>
                                                    <th>Alat</th>
                                                    <td>Visual</td>
                                                </tr>
                                                <tr>
                                                    <th>Standard</th>
                                                    <td>Inject grease secukupnya</td>
                                                </tr>
                                                <tr>
                                                    <th>Durasi</th>
                                                    <td>1'</td>
                                                </tr>
                                                <tr>
                                                    <th>Pelaksanaan</td>
                                                    <td>Harian(Setiap Awal Shift 1)</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col" id="field">
                                            <div id="ctrl-Modul_Pisau-holder" class="">
                                                <?php
                                                $Modul_Pisau_options = Menu::$Kondisi_Harian;
                                                if (!empty($Modul_Pisau_options)) {
                                                    foreach ($Modul_Pisau_options as $option) {
                                                        $value = $option['value'];
                                                        $label = $option['label'];
                                                        // Asumsi framework menggunakan fungsi ini untuk mengecek status checked di radio
                                                        $checked = ($value == $this->set_field_value('Modul_Pisau', "")) ? "checked" : "";
                                                        ?>
                                                        <div class="custom-control custom-radio mt-2"> <input <?php echo $checked ?> type="radio" required
                                                                id="ctrl-Modul_Pisau-<?php echo $value ?>" name="Modul_Pisau"
                                                                value="<?php echo $value ?>"
                                                                class="custom-control-input part-kondisi"
                                                                data-part="Modul_Pisau">
                                                            <label class="custom-control-label"
                                                                for="ctrl-Modul_Pisau-<?php echo $value ?>">
                                                                <?php echo $label ?>
                                                            </label>
                                                        </div>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="report-ctrl-Modul_Pisau"
                                        style="display: none; border-bottom: 1px dashed #ccc; padding-top:15px; margin-bottom:15px;">
                                        <div class="form-group ">
                                            <label class="control-label">Kendala selama AM <span
                                                    class="text-danger">*</span></label>
                                            <div>
                                                <textarea placeholder="Enter Kendala" name="Kendala_Modul_Pisau"
                                                    class="form-control"></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">Kategori Tag <span
                                                    class="text-danger">*</span></label>
                                            <div>
                                                <select name="kategori_tag_Modul_Pisau"
                                                    class="custom-select form-control">
                                                    <option value="" disabled hidden selected>Pilih Kategori Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($kategori_tag_options)) {
                                                        foreach ($kategori_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?>
                                                            </option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group ">
                                            <label class="control-label">Korelasi Tag <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-korelasi_tag_Modul_Pisau-holder" class="">
                                                <select id="ctrl-korelasi_tag_Modul_Pisau"
                                                    name="korelasi_tag_Modul_Pisau"
                                                    data-load-select-options="kategori_ketidaksesuaian_Modul_Pisau"
                                                    class="custom-select" placeholder="Pilih Korelasi Tag ...">
                                                    <option value="" selected hidden disabled>Pilih Korelasi Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($korelasi_tag_options)) {
                                                        foreach ($korelasi_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?>
                                                            </option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group ">
                                            <label class="control-label">Klasifikasi Tag <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-klasifikasi_tag_Modul_Pisau-holder" class="">
                                                <select name="klasifikasi_tag_Modul_Pisau"
                                                    class="custom-select" placeholder="Pilih Klasifikasi Tag ...">
                                                    <option value="" selected hidden disabled>Pilih Klasifikasi Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($klasifikasi_tag_options)) {
                                                        foreach ($klasifikasi_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group ">
                                            <label class="control-label">Kategori Ketidaksesuaian <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-kategori_ketidaksesuaian_Modul_Pisau-holder" class="">
                                                <select id="ctrl-kategori_ketidaksesuaian_Modul_Pisau"
                                                    name="kategori_ketidaksesuaian_Modul_Pisau"
                                                    data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>"
                                                    class="custom-select"
                                                    placeholder="Pilih Kategori Ketidaksesuaian ...">
                                                    <option value="" selected disabled hidden>Pilih Kategori
                                                        Ketidaksesuaian ...</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group ">
                                    <label class="control-label" for="Inkjet">Inkjet <span
                                            class="text-danger">*</span></label>
                                    <div class="row">
                                        <div class="column" id="paddingimg">
                                            <a href="http://<?php echo $serverIp; ?>/form-am/assets/images/sig/sig inkjet.png"
                                                target="_blank">
                                                <img src="http://<?php echo $serverIp; ?>/form-am/assets/images/sig/sig inkjet.png"
                                                    id="img">
                                            </a>
                                        </div>
                                        <div class="column" id="tabel">
                                            <table border="1" cellpadding=3 width=350px>
                                                <tr>
                                                    <th>Metode</th>
                                                    <td>Tes Fungsi</td>
                                                </tr>
                                                <tr>
                                                    <th>Alat</th>
                                                    <td>Visual Control</td>
                                                </tr>
                                                <tr>
                                                    <th>Standard</th>
                                                    <td>Tidak bleber & hasil coding tidak pudar</td>
                                                </tr>
                                                <tr>
                                                    <th>Durasi</th>
                                                    <td>6'</td>
                                                </tr>
                                                <tr>
                                                    <th>Pelaksanaan</td>
                                                    <td>Harian(Setiap Awal Shift 1)</td>
                                                </tr>
                                            </table>
                                        </div>
                                        <div class="col" id="field">
                                            <div id="ctrl-Inkjet-holder" class="">
                                                <?php
                                                $Inkjet_options = Menu::$Kondisi_Harian;
                                                if (!empty($Inkjet_options)) {
                                                    foreach ($Inkjet_options as $option) {
                                                        $value = $option['value'];
                                                        $label = $option['label'];
                                                        // Asumsi framework menggunakan fungsi ini untuk mengecek status checked di radio
                                                        $checked = ($value == $this->set_field_value('Inkjet', "")) ? "checked" : "";
                                                        ?>
                                                        <div class="custom-control custom-radio mt-2"> <input <?php echo $checked ?> type="radio" required
                                                                id="ctrl-Inkjet-<?php echo $value ?>" name="Inkjet"
                                                                value="<?php echo $value ?>"
                                                                class="custom-control-input part-kondisi" data-part="Inkjet">
                                                            <label class="custom-control-label"
                                                                for="ctrl-Inkjet-<?php echo $value ?>">
                                                                <?php echo $label ?>
                                                            </label>
                                                        </div>
                                                        <?php
                                                    }
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="report-ctrl-Inkjet"
                                        style="display: none; border-bottom: 1px dashed #ccc; padding-top:15px; margin-bottom:15px;">
                                        <div class="form-group ">
                                            <label class="control-label">Kendala selama AM <span
                                                    class="text-danger">*</span></label>
                                            <div>
                                                <textarea placeholder="Enter Kendala" name="Kendala_Inkjet"
                                                    class="form-control"></textarea>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label class="control-label">Kategori Tag <span
                                                    class="text-danger">*</span></label>
                                            <div>
                                                <select name="kategori_tag_Inkjet" class="custom-select form-control">
                                                    <option value="" disabled hidden selected>Pilih Kategori Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($kategori_tag_options)) {
                                                        foreach ($kategori_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?>
                                                            </option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group ">
                                            <label class="control-label">Korelasi Tag <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-korelasi_tag_Inkjet-holder" class="">
                                                <select id="ctrl-korelasi_tag_Inkjet" name="korelasi_tag_Inkjet"
                                                    data-load-select-options="kategori_ketidaksesuaian_Inkjet"
                                                    class="custom-select" placeholder="Pilih Korelasi Tag ...">
                                                    <option value="" selected hidden disabled>Pilih Korelasi Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($korelasi_tag_options)) {
                                                        foreach ($korelasi_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?>
                                                            </option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group ">
                                            <label class="control-label">Klasifikasi Tag <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-klasifikasi_tag_Inkjet-holder" class="">
                                                <select name="klasifikasi_tag_Inkjet"
                                                    class="custom-select" placeholder="Pilih Klasifikasi Tag ...">
                                                    <option value="" selected hidden disabled>Pilih Klasifikasi Tag ...
                                                    </option>
                                                    <?php
                                                    if (!empty($klasifikasi_tag_options)) {
                                                        foreach ($klasifikasi_tag_options as $option) {
                                                            $value = (!empty($option['value']) ? $option['value'] : null);
                                                            $label = (!empty($option['label']) ? $option['label'] : $value);
                                                            ?>
                                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group ">
                                            <label class="control-label">Kategori Ketidaksesuaian <span
                                                    class="text-danger">*</span></label>
                                            <div id="ctrl-kategori_ketidaksesuaian_Inkjet-holder" class="">
                                                <select id="ctrl-kategori_ketidaksesuaian_Inkjet"
                                                    name="kategori_ketidaksesuaian_Inkjet"
                                                    data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>"
                                                    class="custom-select"
                                                    placeholder="Pilih Kategori Ketidaksesuaian ...">
                                                    <option value="" selected disabled hidden>Pilih Kategori
                                                        Ketidaksesuaian ...</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                            <div class="form-group form-submit-btn-holder text-center mt-3 d-flex justify-content-center">
                                <div class="form-ajax-status"></div>
                                <a class="btn btn-secondary mx-1" href="<?php print_link("sig/list2"); ?>">
                                    <i class="fa fa-arrow-left"></i>
                                    Kembali
                                </a>
                                <button class="btn btn-primary mx-1" type="submit">
                                    Submit
                                    <i class="fa fa-send"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                    <div class="">
                        <script>
                            // 1. Array penampung keys nama field untuk masing-masing kategori
                            const partsCleaning = [
                                'Sealing_Cross_dan_Vertikal', 'Guarding_Akrilik', 'Jalur_Conveyor',
                                'Vacuum_Hood', 
                                // 'Antistatic'
                            ];
                            const partsInspection = [
                                'Tekanan_Angin_Suplai', 'Jarak_Slider_dengan_Nozzle', 'Rol_Penarik_Sachet_dan_Foil_Slitting_Shim',
                                'Pisau_Belah', 'Modul_Pisau', 'Inkjet'
                            ];

                            // 2. Fungsi check diubah untuk menerima nama part, bukan elemen select
                            function checkPartAndShowForm(partName) {
                                const targetId = 'report-ctrl-' + partName;
                                // Perhatikan ada perbedaan penamaan ID di kodinganmu untuk rol penarik
                                // Pastikan targetId di HTML div report sesuai dengan yang dicari JS
                                const reportForm = document.getElementById(targetId);
                                const checkedRadio = document.querySelector(`input[name="${partName}"]:checked`);

                                if (reportForm && checkedRadio) {
                                    // Ubah 'NOK' menjadi value dari array PHP kamu yang menandakan kondisi tidak baik
                                    if (checkedRadio.value === 'NOK') {
                                        reportForm.style.display = 'block';
                                        reportForm.querySelectorAll('input, textarea, select').forEach(input => {
                                            input.setAttribute('required', 'required');
                                        });
                                    } else {
                                        reportForm.style.display = 'none';
                                        reportForm.querySelectorAll('input, textarea, select').forEach(input => {
                                            input.removeAttribute('required');
                                        });
                                    }
                                }
                            }

                            document.addEventListener('DOMContentLoaded', () => {
                                // 3. Listener untuk klik di tiap radio button manual
                                document.querySelectorAll('.part-kondisi').forEach(radio => {
                                    radio.addEventListener('change', function () {
                                        checkPartAndShowForm(this.getAttribute('data-part'));
                                    });
                                    // Cek kondisi awal saat halaman reload
                                    if (radio.checked) checkPartAndShowForm(radio.getAttribute('data-part'));
                                });

                                // 4. Listener untuk tombol "Semua Kondisi Baik"
                                document.querySelectorAll('.btn-auto-ok').forEach(btn => {
                                    btn.addEventListener('click', function () {
                                        const type = this.getAttribute('data-type');
                                        const targetParts = type === 'cleaning' ? partsCleaning : partsInspection;

                                        targetParts.forEach(part => {
                                            // Ubah 'OK' sesuai dengan value di array PHP untuk kondisi baik
                                            const radioOk = document.querySelector(`input[name="${part}"][value="OK"]`);
                                            if (radioOk) {
                                                radioOk.checked = true;
                                                checkPartAndShowForm(part); // Trigger fungsi untuk menyembunyikan form kendala
                                            }
                                        });
                                    });
                                });
                            });
                        </script>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
