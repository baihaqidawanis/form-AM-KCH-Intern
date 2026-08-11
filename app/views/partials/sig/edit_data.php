<?php
$comp_model = new SharedController;

// 1. Deklarasi Variabel Pilihan Tag
$kategori_tag_options = $comp_model->sig_kategori_tag_option_list();
$korelasi_tag_options = $comp_model->sig_korelasi_tag_option_list();
$klasifikasi_tag_options = $comp_model->sig_klasifikasi_tag_option_list();

$page_element_id = "edit-data-page-" . random_str();
$current_page = $this->set_current_page_link();
$csrf_token = Csrf::$token;
//Page Data Information from Controller
$data = $this->view_data;
//$rec_id = $data['__tableprimarykey'];
$page_id = $this->route->page_id; //Page id from url
$view_title = $this->view_title;
$show_header = $this->show_header;
?>
<section class="page" id="<?php echo $page_element_id; ?>" data-page-type="view" data-display-type="table"
    data-page-url="<?php print_link($current_page); ?>">
    <?php
    if ($show_header == true) {
        ?>
        <div class="bg-light p-3 mb-3">
            <div class="container">
                <div class="row ">
                    <div class="col ">
                        <h4 class="record-title">Edit Data AM SIG</h4>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }
    ?>
    <div class="">
        <div class="container">
            <div class="row ">
                <div class="col-md-12 comp-grid">
                    <div class="">
                        <div class="subheader-container" style="padding-bottom:20px;">
                            <class="record-title">No:CR-PR-PR-1203.00 (25 Okt 2021)
                        </div>
                        <?php $this::display_page_errors(); ?>
                        <div class="card animated fadeIn page-content">
                            <?php
                            $counter = 0;
                            if (!empty($data)) {
                                $rec_id = (!empty($data['id_sig']) ? urlencode($data['id_sig']) : null);
                                $counter++;
                                ?>
                                <div id="page-report-body" class="">
                                    <form method="post"
                                        action="<?php print_link("sig/edit_data/$page_id/?csrf_token=$csrf_token"); ?>">
                                        <table class="table table-hover table-bordered table-striped">
                                            <thead>
                                                <tr class="td-Mesin">
                                                    <th class="title"> Nama Mesin: </th>
                                                    <td class="value" colspan="6"><?php echo $data['nm_mesin']; ?></td>
                                                </tr>
                                                <tr class="td-created_at" style="border-bottom: 1px solid">
                                                    <th class="title"> Date Created: </th>
                                                    <td class="value" colspan="6"> <?php echo $data['created_at']; ?></td>
                                                </tr>
                                                <tr>
                                                    <th>Nama Part</th>
                                                    <th>Kondisi</th>
                                                    <th width="20%">Kendala</th>
                                                    <th>Kategori Tag</th>
                                                    <th>Korelasi Tag</th>
                                                    <th>Klasifikasi Tag</th>
                                                    <th>Ketidaksesuaian</th>
                                                </tr>
                                            </thead>
                                            <!-- Table Body Start -->
                                            <tbody class="page-data" id="page-data-<?php echo $page_element_id; ?>">

                                                <!-- Part 1: Sealing Cross dan Vertikal -->
                                                <tr class="td-Sealing_Cross_dan_Vertikal">
                                                    <th class="title"> Sealing Cross dan Vertikal: </th>
                                                    <td class="value">
                                                        <select required="" name="sealing_cross_dan_vertikal"
                                                            class="custom-select form-control part-kondisi"
                                                            data-part="Sealing_Cross_dan_Vertikal">
                                                            <option value="" disabled hidden>Pilih Kondisi ...</option>
                                                            <?php
                                                            $Kondisi_options = Menu::$Kondisi_Harian;
                                                            $current_value = $data['sealing_cross_dan_vertikal'];
                                                            if (!empty($Kondisi_options)) {
                                                                foreach ($Kondisi_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($val == $current_value ? 'selected' : null);
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <?php $abn = isset($data['abnormalitas']['Sealing_Cross_dan_Vertikal']) ? $data['abnormalitas']['Sealing_Cross_dan_Vertikal'] : null; ?>
                                                    <td>
                                                        <textarea name="Kendala_Sealing_Cross_dan_Vertikal"
                                                            class="form-control form-control-sm abn-input-Sealing_Cross_dan_Vertikal"
                                                            style="display:none;"
                                                            placeholder="Kendala..."><?php echo $abn ? $abn['kendala'] : ''; ?></textarea>
                                                    </td>
                                                    <td>
                                                        <select name="kategori_tag_Sealing_Cross_dan_Vertikal"
                                                            class="custom-select custom-select-sm abn-input-Sealing_Cross_dan_Vertikal"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($kategori_tag_options)) {
                                                                foreach ($kategori_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['kategori_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select id="ctrl-korelasi_tag_Sealing_Cross_dan_Vertikal"
                                                            name="korelasi_tag_Sealing_Cross_dan_Vertikal"
                                                            data-load-select-options="kategori_ketidaksesuaian_Sealing_Cross_dan_Vertikal"
                                                            class="custom-select custom-select-sm abn-input-Sealing_Cross_dan_Vertikal"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($korelasi_tag_options)) {
                                                                foreach ($korelasi_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['korelasi_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="klasifikasi_tag_Sealing_Cross_dan_Vertikal"
                                                            class="custom-select custom-select-sm abn-input-Sealing_Cross_dan_Vertikal"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($klasifikasi_tag_options)) {
                                                                foreach ($klasifikasi_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['klasifikasi_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select
                                                            id="ctrl-kategori_ketidaksesuaian_Sealing_Cross_dan_Vertikal"
                                                            name="kategori_ketidaksesuaian_Sealing_Cross_dan_Vertikal"
                                                            data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>"
                                                            class="custom-select custom-select-sm abn-input-Sealing_Cross_dan_Vertikal"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if ($abn && !empty($abn['kategori_ketidaksesuaian'])) {
                                                                echo "<option selected value=\"{$abn['kategori_ketidaksesuaian']}\">{$abn['teks_ketidaksesuaian']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>

                                                <!-- Part 2: Guarding Akrilik -->
                                                <tr class="td-Guarding_Akrilik">
                                                    <th class="title"> Guarding Akrilik: </th>
                                                    <td class="value">
                                                        <select required="" name="guarding_akrilik"
                                                            class="custom-select form-control part-kondisi"
                                                            data-part="Guarding_Akrilik">
                                                            <option value="" disabled hidden>Pilih Kondisi ...</option>
                                                            <?php
                                                            $Kondisi_options = Menu::$Kondisi_Harian;
                                                            $current_value = $data['guarding_akrilik'];
                                                            if (!empty($Kondisi_options)) {
                                                                foreach ($Kondisi_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($val == $current_value ? 'selected' : null);
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <?php $abn = isset($data['abnormalitas']['Guarding_Akrilik']) ? $data['abnormalitas']['Guarding_Akrilik'] : null; ?>
                                                    <td>
                                                        <textarea name="Kendala_Guarding_Akrilik"
                                                            class="form-control form-control-sm abn-input-Guarding_Akrilik"
                                                            style="display:none;"
                                                            placeholder="Kendala..."><?php echo $abn ? $abn['kendala'] : ''; ?></textarea>
                                                    </td>
                                                    <td>
                                                        <select name="kategori_tag_Guarding_Akrilik"
                                                            class="custom-select custom-select-sm abn-input-Guarding_Akrilik"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($kategori_tag_options)) {
                                                                foreach ($kategori_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['kategori_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select id="ctrl-korelasi_tag_Guarding_Akrilik"
                                                            name="korelasi_tag_Guarding_Akrilik"
                                                            data-load-select-options="kategori_ketidaksesuaian_Guarding_Akrilik"
                                                            class="custom-select custom-select-sm abn-input-Guarding_Akrilik"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($korelasi_tag_options)) {
                                                                foreach ($korelasi_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['korelasi_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="klasifikasi_tag_Guarding_Akrilik"
                                                            class="custom-select custom-select-sm abn-input-Guarding_Akrilik"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($klasifikasi_tag_options)) {
                                                                foreach ($klasifikasi_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['klasifikasi_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select id="ctrl-kategori_ketidaksesuaian_Guarding_Akrilik"
                                                            name="kategori_ketidaksesuaian_Guarding_Akrilik"
                                                            data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>"
                                                            class="custom-select custom-select-sm abn-input-Guarding_Akrilik"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if ($abn && !empty($abn['kategori_ketidaksesuaian'])) {
                                                                echo "<option selected value=\"{$abn['kategori_ketidaksesuaian']}\">{$abn['teks_ketidaksesuaian']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>

                                                <!-- Part 3: Jalur Conveyor -->
                                                <tr class="td-Jalur_Conveyor">
                                                    <th class="title"> Jalur Conveyor: </th>
                                                    <td class="value">
                                                        <select required="" name="jalur_conveyor"
                                                            class="custom-select form-control part-kondisi"
                                                            data-part="Jalur_Conveyor">
                                                            <option value="" disabled hidden>Pilih Kondisi ...</option>
                                                            <?php
                                                            $Kondisi_options = Menu::$Kondisi_Harian;
                                                            $current_value = $data['jalur_conveyor'];
                                                            if (!empty($Kondisi_options)) {
                                                                foreach ($Kondisi_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($val == $current_value ? 'selected' : null);
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <?php $abn = isset($data['abnormalitas']['Jalur_Conveyor']) ? $data['abnormalitas']['Jalur_Conveyor'] : null; ?>
                                                    <td>
                                                        <textarea name="Kendala_Jalur_Conveyor"
                                                            class="form-control form-control-sm abn-input-Jalur_Conveyor"
                                                            style="display:none;"
                                                            placeholder="Kendala..."><?php echo $abn ? $abn['kendala'] : ''; ?></textarea>
                                                    </td>
                                                    <td>
                                                        <select name="kategori_tag_Jalur_Conveyor"
                                                            class="custom-select custom-select-sm abn-input-Jalur_Conveyor"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($kategori_tag_options)) {
                                                                foreach ($kategori_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['kategori_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select id="ctrl-korelasi_tag_Jalur_Conveyor"
                                                            name="korelasi_tag_Jalur_Conveyor"
                                                            data-load-select-options="kategori_ketidaksesuaian_Jalur_Conveyor"
                                                            class="custom-select custom-select-sm abn-input-Jalur_Conveyor"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($korelasi_tag_options)) {
                                                                foreach ($korelasi_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['korelasi_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="klasifikasi_tag_Jalur_Conveyor"
                                                            class="custom-select custom-select-sm abn-input-Jalur_Conveyor"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($klasifikasi_tag_options)) {
                                                                foreach ($klasifikasi_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['klasifikasi_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select id="ctrl-kategori_ketidaksesuaian_Jalur_Conveyor"
                                                            name="kategori_ketidaksesuaian_Jalur_Conveyor"
                                                            data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>"
                                                            class="custom-select custom-select-sm abn-input-Jalur_Conveyor"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if ($abn && !empty($abn['kategori_ketidaksesuaian'])) {
                                                                echo "<option selected value=\"{$abn['kategori_ketidaksesuaian']}\">{$abn['teks_ketidaksesuaian']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>

                                                <!-- Part 4: Antistatic -->
                                                <tr class="td-Antistatic">
                                                    <th class="title"> Antistatic: </th>
                                                    <td class="value">
                                                        <select required="" name="antistatic"
                                                            class="custom-select form-control part-kondisi"
                                                            data-part="Antistatic">
                                                            <option value="" disabled hidden>Pilih Kondisi ...</option>
                                                            <?php
                                                            $Kondisi_options = Menu::$Kondisi_Harian;
                                                            $current_value = $data['antistatic'];
                                                            if (!empty($Kondisi_options)) {
                                                                foreach ($Kondisi_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($val == $current_value ? 'selected' : null);
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <?php $abn = isset($data['abnormalitas']['Antistatic']) ? $data['abnormalitas']['Antistatic'] : null; ?>
                                                    <td>
                                                        <textarea name="Kendala_Antistatic"
                                                            class="form-control form-control-sm abn-input-Antistatic"
                                                            style="display:none;"
                                                            placeholder="Kendala..."><?php echo $abn ? $abn['kendala'] : ''; ?></textarea>
                                                    </td>
                                                    <td>
                                                        <select name="kategori_tag_Antistatic"
                                                            class="custom-select custom-select-sm abn-input-Antistatic"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($kategori_tag_options)) {
                                                                foreach ($kategori_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['kategori_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select id="ctrl-korelasi_tag_Antistatic"
                                                            name="korelasi_tag_Antistatic"
                                                            data-load-select-options="kategori_ketidaksesuaian_Antistatic"
                                                            class="custom-select custom-select-sm abn-input-Antistatic"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($korelasi_tag_options)) {
                                                                foreach ($korelasi_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['korelasi_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="klasifikasi_tag_Antistatic"
                                                            class="custom-select custom-select-sm abn-input-Antistatic"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($klasifikasi_tag_options)) {
                                                                foreach ($klasifikasi_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['klasifikasi_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select id="ctrl-kategori_ketidaksesuaian_Antistatic"
                                                            name="kategori_ketidaksesuaian_Antistatic"
                                                            data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>"
                                                            class="custom-select custom-select-sm abn-input-Antistatic"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if ($abn && !empty($abn['kategori_ketidaksesuaian'])) {
                                                                echo "<option selected value=\"{$abn['kategori_ketidaksesuaian']}\">{$abn['teks_ketidaksesuaian']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>

                                                <!-- Part 5: Vacuum Hood -->
                                                <tr class="td-Vacuum_Hood">
                                                    <th class="title"> Vacuum Hood: </th>
                                                    <td class="value">
                                                        <select required="" name="vacuum_hood"
                                                            class="custom-select form-control part-kondisi"
                                                            data-part="Vacuum_Hood">
                                                            <option value="" disabled hidden>Pilih Kondisi ...</option>
                                                            <?php
                                                            $Kondisi_options = Menu::$Kondisi_Harian;
                                                            $current_value = $data['vacuum_hood'];
                                                            if (!empty($Kondisi_options)) {
                                                                foreach ($Kondisi_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($val == $current_value ? 'selected' : null);
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <?php $abn = isset($data['abnormalitas']['Vacuum_Hood']) ? $data['abnormalitas']['Vacuum_Hood'] : null; ?>
                                                    <td>
                                                        <textarea name="Kendala_Vacuum_Hood"
                                                            class="form-control form-control-sm abn-input-Vacuum_Hood"
                                                            style="display:none;"
                                                            placeholder="Kendala..."><?php echo $abn ? $abn['kendala'] : ''; ?></textarea>
                                                    </td>
                                                    <td>
                                                        <select name="kategori_tag_Vacuum_Hood"
                                                            class="custom-select custom-select-sm abn-input-Vacuum_Hood"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($kategori_tag_options)) {
                                                                foreach ($kategori_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['kategori_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select id="ctrl-korelasi_tag_Vacuum_Hood"
                                                            name="korelasi_tag_Vacuum_Hood"
                                                            data-load-select-options="kategori_ketidaksesuaian_Vacuum_Hood"
                                                            class="custom-select custom-select-sm abn-input-Vacuum_Hood"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($korelasi_tag_options)) {
                                                                foreach ($korelasi_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['korelasi_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="klasifikasi_tag_Vacuum_Hood"
                                                            class="custom-select custom-select-sm abn-input-Vacuum_Hood"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($klasifikasi_tag_options)) {
                                                                foreach ($klasifikasi_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['klasifikasi_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select id="ctrl-kategori_ketidaksesuaian_Vacuum_Hood"
                                                            name="kategori_ketidaksesuaian_Vacuum_Hood"
                                                            data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>"
                                                            class="custom-select custom-select-sm abn-input-Vacuum_Hood"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if ($abn && !empty($abn['kategori_ketidaksesuaian'])) {
                                                                echo "<option selected value=\"{$abn['kategori_ketidaksesuaian']}\">{$abn['teks_ketidaksesuaian']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>

                                                <!-- Part 6: Tekanan Angin Suplai -->
                                                <tr class="td-Tekanan_Angin_Suplai">
                                                    <th class="title"> Tekanan Angin Suplai: </th>
                                                    <td class="value">
                                                        <select required="" name="tekanan_angin_suplai"
                                                            class="custom-select form-control part-kondisi"
                                                            data-part="Tekanan_Angin_Suplai">
                                                            <option value="" disabled hidden>Pilih Kondisi ...</option>
                                                            <?php
                                                            $Kondisi_options = Menu::$Kondisi_Harian;
                                                            $current_value = $data['tekanan_angin_suplai'];
                                                            if (!empty($Kondisi_options)) {
                                                                foreach ($Kondisi_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($val == $current_value ? 'selected' : null);
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <?php $abn = isset($data['abnormalitas']['Tekanan_Angin_Suplai']) ? $data['abnormalitas']['Tekanan_Angin_Suplai'] : null; ?>
                                                    <td>
                                                        <textarea name="Kendala_Tekanan_Angin_Suplai"
                                                            class="form-control form-control-sm abn-input-Tekanan_Angin_Suplai"
                                                            style="display:none;"
                                                            placeholder="Kendala..."><?php echo $abn ? $abn['kendala'] : ''; ?></textarea>
                                                    </td>
                                                    <td>
                                                        <select name="kategori_tag_Tekanan_Angin_Suplai"
                                                            class="custom-select custom-select-sm abn-input-Tekanan_Angin_Suplai"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($kategori_tag_options)) {
                                                                foreach ($kategori_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['kategori_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select id="ctrl-korelasi_tag_Tekanan_Angin_Suplai"
                                                            name="korelasi_tag_Tekanan_Angin_Suplai"
                                                            data-load-select-options="kategori_ketidaksesuaian_Tekanan_Angin_Suplai"
                                                            class="custom-select custom-select-sm abn-input-Tekanan_Angin_Suplai"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($korelasi_tag_options)) {
                                                                foreach ($korelasi_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['korelasi_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="klasifikasi_tag_Tekanan_Angin_Suplai"
                                                            class="custom-select custom-select-sm abn-input-Tekanan_Angin_Suplai"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($klasifikasi_tag_options)) {
                                                                foreach ($klasifikasi_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['klasifikasi_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select id="ctrl-kategori_ketidaksesuaian_Tekanan_Angin_Suplai"
                                                            name="kategori_ketidaksesuaian_Tekanan_Angin_Suplai"
                                                            data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>"
                                                            class="custom-select custom-select-sm abn-input-Tekanan_Angin_Suplai"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if ($abn && !empty($abn['kategori_ketidaksesuaian'])) {
                                                                echo "<option selected value=\"{$abn['kategori_ketidaksesuaian']}\">{$abn['teks_ketidaksesuaian']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>

                                                <!-- Part 7: Value Tekanan Angin (Special Case Number) -->
                                                <tr class="td-Value_Tekanan_Angin">
                                                    <th class="title"> Value Tekanan Angin: </th>
                                                    <td class="value" colspan="6">
                                                        <div class="input-group" style="max-width: 200px;">
                                                            <input type="number" step="0.1" name="value_tekanan_angin"
                                                                value="<?php echo $data['value_tekanan_angin']; ?>"
                                                                class="form-control" required>
                                                            <div class="input-group-append">
                                                                <span class="input-group-text">BAR</span>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>

                                                <!-- Part 8: Jarak Slider dengan Nozzle -->
                                                <tr class="td-Jarak_Slider_dengan_Nozzle">
                                                    <th class="title"> Jarak Slider dengan Nozzle: </th>
                                                    <td class="value">
                                                        <select required="" name="jarak_slider_dengan_nozzle"
                                                            class="custom-select form-control part-kondisi"
                                                            data-part="Jarak_Slider_dengan_Nozzle">
                                                            <option value="" disabled hidden>Pilih Kondisi ...</option>
                                                            <?php
                                                            $Kondisi_options = Menu::$Kondisi_Harian;
                                                            $current_value = $data['jarak_slider_dengan_nozzle'];
                                                            if (!empty($Kondisi_options)) {
                                                                foreach ($Kondisi_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($val == $current_value ? 'selected' : null);
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <?php $abn = isset($data['abnormalitas']['Jarak_Slider_dengan_Nozzle']) ? $data['abnormalitas']['Jarak_Slider_dengan_Nozzle'] : null; ?>
                                                    <td>
                                                        <textarea name="Kendala_Jarak_Slider_dengan_Nozzle"
                                                            class="form-control form-control-sm abn-input-Jarak_Slider_dengan_Nozzle"
                                                            style="display:none;"
                                                            placeholder="Kendala..."><?php echo $abn ? $abn['kendala'] : ''; ?></textarea>
                                                    </td>
                                                    <td>
                                                        <select name="kategori_tag_Jarak_Slider_dengan_Nozzle"
                                                            class="custom-select custom-select-sm abn-input-Jarak_Slider_dengan_Nozzle"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($kategori_tag_options)) {
                                                                foreach ($kategori_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['kategori_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select id="ctrl-korelasi_tag_Jarak_Slider_dengan_Nozzle"
                                                            name="korelasi_tag_Jarak_Slider_dengan_Nozzle"
                                                            data-load-select-options="kategori_ketidaksesuaian_Jarak_Slider_dengan_Nozzle"
                                                            class="custom-select custom-select-sm abn-input-Jarak_Slider_dengan_Nozzle"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($korelasi_tag_options)) {
                                                                foreach ($korelasi_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['korelasi_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="klasifikasi_tag_Jarak_Slider_dengan_Nozzle"
                                                            class="custom-select custom-select-sm abn-input-Jarak_Slider_dengan_Nozzle"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($klasifikasi_tag_options)) {
                                                                foreach ($klasifikasi_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['klasifikasi_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select
                                                            id="ctrl-kategori_ketidaksesuaian_Jarak_Slider_dengan_Nozzle"
                                                            name="kategori_ketidaksesuaian_Jarak_Slider_dengan_Nozzle"
                                                            data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>"
                                                            class="custom-select custom-select-sm abn-input-Jarak_Slider_dengan_Nozzle"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if ($abn && !empty($abn['kategori_ketidaksesuaian'])) {
                                                                echo "<option selected value=\"{$abn['kategori_ketidaksesuaian']}\">{$abn['teks_ketidaksesuaian']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>

                                                <!-- Part 9: Rol Penarik Sachet dan Foil Slitting Shim -->
                                                <tr class="td-Rol_Penarik_Sachet_dan_Foil_Slitting_Shim">
                                                    <th class="title"> Rol Penarik Sachet dan Foil Slitting Shim: </th>
                                                    <td class="value">
                                                        <select required="" name="rol_penarik_sachet_dan_foil_slitting_shim"
                                                            class="custom-select form-control part-kondisi"
                                                            data-part="Rol_Penarik_Sachet_dan_Foil_Slitting_Shim">
                                                            <option value="" disabled hidden>Pilih Kondisi ...</option>
                                                            <?php
                                                            $Kondisi_options = Menu::$Kondisi_Harian;
                                                            $current_value = $data['rol_penarik_sachet_dan_foil_slitting_shim'];
                                                            if (!empty($Kondisi_options)) {
                                                                foreach ($Kondisi_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($val == $current_value ? 'selected' : null);
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <?php $abn = isset($data['abnormalitas']['Rol_Penarik_Sachet_dan_Foil_Slitting_Shim']) ? $data['abnormalitas']['Rol_Penarik_Sachet_dan_Foil_Slitting_Shim'] : null; ?>
                                                    <td>
                                                        <textarea name="Kendala_Rol_Penarik_Sachet_dan_Foil_Slitting_Shim"
                                                            class="form-control form-control-sm abn-input-Rol_Penarik_Sachet_dan_Foil_Slitting_Shim"
                                                            style="display:none;"
                                                            placeholder="Kendala..."><?php echo $abn ? $abn['kendala'] : ''; ?></textarea>
                                                    </td>
                                                    <td>
                                                        <select
                                                            name="kategori_tag_Rol_Penarik_Sachet_dan_Foil_Slitting_Shim"
                                                            class="custom-select custom-select-sm abn-input-Rol_Penarik_Sachet_dan_Foil_Slitting_Shim"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($kategori_tag_options)) {
                                                                foreach ($kategori_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['kategori_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select
                                                            id="ctrl-korelasi_tag_Rol_Penarik_Sachet_dan_Foil_Slitting_Shim"
                                                            name="korelasi_tag_Rol_Penarik_Sachet_dan_Foil_Slitting_Shim"
                                                            data-load-select-options="kategori_ketidaksesuaian_Rol_Penarik_Sachet_dan_Foil_Slitting_Shim"
                                                            class="custom-select custom-select-sm abn-input-Rol_Penarik_Sachet_dan_Foil_Slitting_Shim"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($korelasi_tag_options)) {
                                                                foreach ($korelasi_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['korelasi_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select
                                                            name="klasifikasi_tag_Rol_Penarik_Sachet_dan_Foil_Slitting_Shim"
                                                            class="custom-select custom-select-sm abn-input-Rol_Penarik_Sachet_dan_Foil_Slitting_Shim"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($klasifikasi_tag_options)) {
                                                                foreach ($klasifikasi_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['klasifikasi_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select
                                                            id="ctrl-kategori_ketidaksesuaian_Rol_Penarik_Sachet_dan_Foil_Slitting_Shim"
                                                            name="kategori_ketidaksesuaian_Rol_Penarik_Sachet_dan_Foil_Slitting_Shim"
                                                            data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>"
                                                            class="custom-select custom-select-sm abn-input-Rol_Penarik_Sachet_dan_Foil_Slitting_Shim"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if ($abn && !empty($abn['kategori_ketidaksesuaian'])) {
                                                                echo "<option selected value=\"{$abn['kategori_ketidaksesuaian']}\">{$abn['teks_ketidaksesuaian']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>

                                                <!-- Part 10: Pisau Belah -->
                                                <tr class="td-Pisau_Belah">
                                                    <th class="title"> Pisau Belah: </th>
                                                    <td class="value">
                                                        <select required="" name="pisau_belah"
                                                            class="custom-select form-control part-kondisi"
                                                            data-part="Pisau_Belah">
                                                            <option value="" disabled hidden>Pilih Kondisi ...</option>
                                                            <?php
                                                            $Kondisi_options = Menu::$Kondisi_Harian;
                                                            $current_value = $data['pisau_belah'];
                                                            if (!empty($Kondisi_options)) {
                                                                foreach ($Kondisi_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($val == $current_value ? 'selected' : null);
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <?php $abn = isset($data['abnormalitas']['Pisau_Belah']) ? $data['abnormalitas']['Pisau_Belah'] : null; ?>
                                                    <td>
                                                        <textarea name="Kendala_Pisau_Belah"
                                                            class="form-control form-control-sm abn-input-Pisau_Belah"
                                                            style="display:none;"
                                                            placeholder="Kendala..."><?php echo $abn ? $abn['kendala'] : ''; ?></textarea>
                                                    </td>
                                                    <td>
                                                        <select name="kategori_tag_Pisau_Belah"
                                                            class="custom-select custom-select-sm abn-input-Pisau_Belah"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($kategori_tag_options)) {
                                                                foreach ($kategori_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['kategori_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select id="ctrl-korelasi_tag_Pisau_Belah"
                                                            name="korelasi_tag_Pisau_Belah"
                                                            data-load-select-options="kategori_ketidaksesuaian_Pisau_Belah"
                                                            class="custom-select custom-select-sm abn-input-Pisau_Belah"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($korelasi_tag_options)) {
                                                                foreach ($korelasi_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['korelasi_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="klasifikasi_tag_Pisau_Belah"
                                                            class="custom-select custom-select-sm abn-input-Pisau_Belah"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($klasifikasi_tag_options)) {
                                                                foreach ($klasifikasi_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['klasifikasi_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select id="ctrl-kategori_ketidaksesuaian_Pisau_Belah"
                                                            name="kategori_ketidaksesuaian_Pisau_Belah"
                                                            data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>"
                                                            class="custom-select custom-select-sm abn-input-Pisau_Belah"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if ($abn && !empty($abn['kategori_ketidaksesuaian'])) {
                                                                echo "<option selected value=\"{$abn['kategori_ketidaksesuaian']}\">{$abn['teks_ketidaksesuaian']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>

                                                <!-- Part 11: Modul Pisau -->
                                                <tr class="td-Modul_Pisau">
                                                    <th class="title"> Modul Pisau: </th>
                                                    <td class="value">
                                                        <select required="" name="modul_pisau"
                                                            class="custom-select form-control part-kondisi"
                                                            data-part="Modul_Pisau">
                                                            <option value="" disabled hidden>Pilih Kondisi ...</option>
                                                            <?php
                                                            $Kondisi_options = Menu::$Kondisi_Harian;
                                                            $current_value = $data['modul_pisau'];
                                                            if (!empty($Kondisi_options)) {
                                                                foreach ($Kondisi_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($val == $current_value ? 'selected' : null);
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <?php $abn = isset($data['abnormalitas']['Modul_Pisau']) ? $data['abnormalitas']['Modul_Pisau'] : null; ?>
                                                    <td>
                                                        <textarea name="Kendala_Modul_Pisau"
                                                            class="form-control form-control-sm abn-input-Modul_Pisau"
                                                            style="display:none;"
                                                            placeholder="Kendala..."><?php echo $abn ? $abn['kendala'] : ''; ?></textarea>
                                                    </td>
                                                    <td>
                                                        <select name="kategori_tag_Modul_Pisau"
                                                            class="custom-select custom-select-sm abn-input-Modul_Pisau"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($kategori_tag_options)) {
                                                                foreach ($kategori_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['kategori_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select id="ctrl-korelasi_tag_Modul_Pisau"
                                                            name="korelasi_tag_Modul_Pisau"
                                                            data-load-select-options="kategori_ketidaksesuaian_Modul_Pisau"
                                                            class="custom-select custom-select-sm abn-input-Modul_Pisau"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($korelasi_tag_options)) {
                                                                foreach ($korelasi_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['korelasi_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="klasifikasi_tag_Modul_Pisau"
                                                            class="custom-select custom-select-sm abn-input-Modul_Pisau"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($klasifikasi_tag_options)) {
                                                                foreach ($klasifikasi_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['klasifikasi_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select id="ctrl-kategori_ketidaksesuaian_Modul_Pisau"
                                                            name="kategori_ketidaksesuaian_Modul_Pisau"
                                                            data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>"
                                                            class="custom-select custom-select-sm abn-input-Modul_Pisau"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if ($abn && !empty($abn['kategori_ketidaksesuaian'])) {
                                                                echo "<option selected value=\"{$abn['kategori_ketidaksesuaian']}\">{$abn['teks_ketidaksesuaian']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>

                                                <!-- Part 12: Inkjet -->
                                                <tr class="td-Inkjet">
                                                    <th class="title"> Inkjet: </th>
                                                    <td class="value">
                                                        <select required="" name="inkjet"
                                                            class="custom-select form-control part-kondisi"
                                                            data-part="Inkjet">
                                                            <option value="" disabled hidden>Pilih Kondisi ...</option>
                                                            <?php
                                                            $Kondisi_options = Menu::$Kondisi_Harian;
                                                            $current_value = $data['inkjet'];
                                                            if (!empty($Kondisi_options)) {
                                                                foreach ($Kondisi_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($val == $current_value ? 'selected' : null);
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <?php $abn = isset($data['abnormalitas']['Inkjet']) ? $data['abnormalitas']['Inkjet'] : null; ?>
                                                    <td>
                                                        <textarea name="Kendala_Inkjet"
                                                            class="form-control form-control-sm abn-input-Inkjet"
                                                            style="display:none;"
                                                            placeholder="Kendala..."><?php echo $abn ? $abn['kendala'] : ''; ?></textarea>
                                                    </td>
                                                    <td>
                                                        <select name="kategori_tag_Inkjet"
                                                            class="custom-select custom-select-sm abn-input-Inkjet"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($kategori_tag_options)) {
                                                                foreach ($kategori_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['kategori_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select id="ctrl-korelasi_tag_Inkjet" name="korelasi_tag_Inkjet"
                                                            data-load-select-options="kategori_ketidaksesuaian_Inkjet"
                                                            class="custom-select custom-select-sm abn-input-Inkjet"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($korelasi_tag_options)) {
                                                                foreach ($korelasi_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['korelasi_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select name="klasifikasi_tag_Inkjet"
                                                            class="custom-select custom-select-sm abn-input-Inkjet"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if (!empty($klasifikasi_tag_options)) {
                                                                foreach ($klasifikasi_tag_options as $option) {
                                                                    $val = $option['value'];
                                                                    $label = $option['label'];
                                                                    $selected = ($abn && $abn['klasifikasi_tag'] == $val) ? 'selected' : '';
                                                                    echo "<option $selected value=\"$val\">$label</option>";
                                                                }
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <select id="ctrl-kategori_ketidaksesuaian_Inkjet"
                                                            name="kategori_ketidaksesuaian_Inkjet"
                                                            data-load-path="<?php print_link('api/json/sig_kategori_ketidaksesuaian_option_list') ?>"
                                                            class="custom-select custom-select-sm abn-input-Inkjet"
                                                            style="display:none;">
                                                            <option value="" disabled hidden selected>Pilih...</option>
                                                            <?php
                                                            if ($abn && !empty($abn['kategori_ketidaksesuaian'])) {
                                                                echo "<option selected value=\"{$abn['kategori_ketidaksesuaian']}\">{$abn['teks_ketidaksesuaian']}</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                    </td>
                                                </tr>

                                                <tr class="td-updated_at">
                                                    <th class="title"> Date Updated: </th>
                                                    <td class="value" colspan="6">
                                                        <?php echo !empty($data['updated_at']) ? $data['updated_at'] : '<i><span class="text-muted">Belum ada tindakan</span></i>'; ?>
                                                    </td>
                                                </tr>
                                                <tr class="td-user_create">
                                                    <th class="title"> User Create: </th>
                                                    <td class="value" colspan="6"> <?php echo $data['user_create']; ?></td>
                                                </tr>
                                                <tr class="td-user_approve">
                                                    <th class="title"> User Approve: </th>
                                                    <td class="value" colspan="6">
                                                        <?php echo !empty($data['user_approve']) ? $data['user_approve'] : '<i><span class="text-muted">Belum ada tindakan</span></i>'; ?>
                                                    </td>
                                                </tr>
                                                <tr class="td-approval">
                                                    <th class="title"> Approval: </th>
                                                    <td class="value" colspan="6">
                                                        <?php echo !empty($data['approval']) ? $data['approval'] : '<i><span class="text-muted">Belum ada tindakan</span></i>'; ?>
                                                    </td>
                                                </tr>
                                                <tr class="td-perubahan">
                                                    <th class="title"> Perubahan (Log Edit): </th>
                                                    <td class="value" colspan="6">
                                                        <?php echo !empty($data['perubahan']) ? $data['perubahan'] : '<i><span class="text-muted">Belum ada tindakan</span></i>'; ?>
                                                    </td>
                                                </tr>
                                                <tr class="td-user_perubah">
                                                    <th class="title"> User Perubah: </th>
                                                    <td class="value" colspan="6">
                                                        <?php echo !empty($data['user_perubah']) ? $data['user_perubah'] : '<i><span class="text-muted">Belum ada tindakan</span></i>'; ?>
                                                    </td>
                                                </tr>
                                                <tr class="td-tanggal_perubahan">
                                                    <th class="title"> Tanggal Perubahan: </th>
                                                    <td class="value" colspan="6">
                                                        <?php echo !empty($data['tanggal_perubahan']) ? $data['tanggal_perubahan'] : '<i><span class="text-muted">Belum ada tindakan</span></i>'; ?>
                                                    </td>
                                                </tr>
                                            </tbody>
                                            <!-- Table Body End -->
                                        </table>
                                        <div class="form-group mt-4 px-3">
                                            <label class="control-label font-weight-bold" for="perubahan">Perubahan yang
                                                dilakukan <span class="text-danger">*</span></label>
                                            <textarea
                                                placeholder="Masukkan detail perubahan yang dilakukan pada data ini..."
                                                id="ctrl-perubahan" rows="4" name="perubahan" class="form-control"
                                                required></textarea>
                                        </div>

                                        <div class="form-group text-center mt-3 d-flex justify-content-center">
                                            <a class="btn btn-secondary mx-1" href="<?php print_link("sig/list2"); ?>">
                                                <i class="fa fa-arrow-left"></i>
                                                Kembali
                                            </a>
                                            <button class="btn btn-primary mx-1" type="submit">
                                                Simpan Perubahan
                                                <i class="fa fa-save"></i>
                                            </button>
                                        </div>
                                    </form>
                                </div>
                                <div class="p-3 d-flex">
                                    <div class="dropup export-btn-holder mx-1">
                                        <button class="btn btn-sm btn-primary dropdown-toggle" type="button"
                                            data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            <i class="fa fa-save"></i> Export
                                        </button>
                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                            <?php $export_print_link = $this->set_current_page_link(array('format' => 'print')); ?>
                                            <a class="dropdown-item export-link-btn" data-format="print"
                                                href="<?php print_link($export_print_link); ?>" target="_blank">
                                                <img src="<?php print_link('assets/images/print.png') ?>" class="mr-2" />
                                                PRINT
                                            </a>
                                            <?php $export_pdf_link = $this->set_current_page_link(array('format' => 'pdf')); ?>
                                            <a class="dropdown-item export-link-btn" data-format="pdf"
                                                href="<?php print_link($export_pdf_link); ?>" target="_blank">
                                                <img src="<?php print_link('assets/images/pdf.png') ?>" class="mr-2" /> PDF
                                            </a>
                                            <?php $export_word_link = $this->set_current_page_link(array('format' => 'word')); ?>
                                            <a class="dropdown-item export-link-btn" data-format="word"
                                                href="<?php print_link($export_word_link); ?>" target="_blank">
                                                <img src="<?php print_link('assets/images/doc.png') ?>" class="mr-2" /> WORD
                                            </a>
                                            <?php $export_csv_link = $this->set_current_page_link(array('format' => 'csv')); ?>
                                            <a class="dropdown-item export-link-btn" data-format="csv"
                                                href="<?php print_link($export_csv_link); ?>" target="_blank">
                                                <img src="<?php print_link('assets/images/csv.png') ?>" class="mr-2" /> CSV
                                            </a>
                                            <?php $export_excel_link = $this->set_current_page_link(array('format' => 'excel')); ?>
                                            <a class="dropdown-item export-link-btn" data-format="excel"
                                                href="<?php print_link($export_excel_link); ?>" target="_blank">
                                                <img src="<?php print_link('assets/images/xsl.png') ?>" class="mr-2" />
                                                EXCEL
                                            </a>
                                        </div>
                                    </div>
                                </div>

                                <?php
                            } else {
                                ?>
                                <!-- Empty Record Message -->
                                <div class="text-muted p-3">
                                    <i class="fa fa-ban"></i> No Record Found
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Memasang JavaScript Trigger -->
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // Fungsi untuk menampilkan/menyembunyikan form kendala
                function checkPartAndShowForm(partName) {
                    // Cari select kondisi utama
                    const selectEl = document.querySelector(`select[data-part="${partName}"]`);
                    // Cari semua input kendala/tagging di baris tersebut
                    const inputs = document.querySelectorAll(`.abn-input-${partName}`);

                    if (selectEl) {
                        // Sesuaikan 'NOK' dengan value di database kamu yang menandakan error
                        if (selectEl.value === 'NOK' || selectEl.value === 'Tidak Baik') {
                            inputs.forEach(input => {
                                input.style.display = 'block'; // Tampilkan input
                                input.setAttribute('required', 'required'); // Jadikan wajib diisi
                                input.removeAttribute('disabled'); // Pastikan bisa dikirim
                            });
                        } else {
                            inputs.forEach(input => {
                                input.style.display = 'none'; // Sembunyikan
                                input.removeAttribute('required'); // Cabut wajib diisi
                                input.setAttribute('disabled', 'disabled'); // Cegah dikirim kosong
                            });
                        }
                    }
                }

                // Pasang listener di semua dropdown kondisi
                document.querySelectorAll('.part-kondisi').forEach(select => {
                    // Saat nilainya diubah oleh user
                    select.addEventListener('change', function () {
                        checkPartAndShowForm(this.getAttribute('data-part'));
                    });

                    // Pengecekan awal saat halaman baru di-load (untuk memunculkan form jika data lamanya NOK)
                    checkPartAndShowForm(select.getAttribute('data-part'));
                });
            });
        </script>

</section>