<?php
$comp_model = new SharedController;
$page_element_id = "view-page-" . random_str();
$current_page = $this->set_current_page_link();
$csrf_token = Csrf::$token;
//Page Data Information from Controller
$data = $this->view_data;
//$rec_id = $data['__tableprimarykey'];
$page_id = $this->route->page_id; //Page id from url
$view_title = $this->view_title;
$show_header = $this->show_header;
$show_edit_btn = $this->show_edit_btn;
$show_delete_btn = $this->show_delete_btn;
$show_export_btn = $this->show_export_btn;
?>
<section class="page" id="<?php echo $page_element_id; ?>" data-page-type="view" data-display-type="table"
    data-page-url="<?php print_link($current_page); ?>">
    <style>
        #<?php echo $page_element_id; ?> .table-bordered th,
        #<?php echo $page_element_id; ?> .table-bordered td {
            border: 1px solid #c6c6c6;
        }
    </style>
    <?php
    if ($show_header == true) {
        ?>
        <div class="bg-light p-3 mb-3">
            <div class="container">
                <div class="row ">
                    <div class="col ">
                        <h4 class="record-title">View AM SIG</h4>
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
                                    <table class="table table-hover table-bordered table-striped">
                                        <thead>
                                            <tr class="td-Mesin">
                                                <th class="title"> Nama Mesin: </th>
                                                <td class="value" colspan="6"><?php echo $data['nm_mesin']; ?></td>
                                            </tr>
                                            <tr class="td-created_at" style="border-bottom: 1px solid">
                                                <th class="title"> Date Created: </th>
                                                <td class="value" colspan="6">
                                                    <?php echo !empty($data['created_at']) ? date('d-m-Y H:i:s', strtotime($data['created_at'])) : '-'; ?>
                                                </td>
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

                                            <tr class="td-Sealing_Cross_dan_Vertikal">
                                                <th class="title"> Sealing Cross dan Vertikal: </th>
                                                <td class="value"> <?php echo $data['sealing_cross_dan_vertikal']; ?></td>
                                                <?php
                                                // If there is abnormal data, break it into 4 columns.
                                                if (isset($data['abnormalitas']['Sealing_Cross_dan_Vertikal'])) {
                                                    $abn = $data['abnormalitas']['Sealing_Cross_dan_Vertikal'];
                                                    ?>
                                                    <td><?php echo $abn['kendala']; ?></td>
                                                    <td><?php echo $abn['teks_kategori']; ?></td>
                                                    <td><?php echo $abn['teks_korelasi']; ?></td>
                                                    <td><?php echo $abn['teks_klasifikasi']; ?></td>
                                                    <td><?php echo $abn['teks_ketidaksesuaian']; ?></td>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                <?php } ?>
                                            </tr>
                                            <tr class="td-Guarding_Akrilik">
                                                <th class="title"> Guarding Akrilik: </th>
                                                <td class="value"> <?php echo $data['guarding_akrilik']; ?></td>
                                                <?php
                                                // If there is abnormal data, break it into 4 columns.
                                                if (isset($data['abnormalitas']['Guarding_Akrilik'])) {
                                                    $abn = $data['abnormalitas']['Guarding_Akrilik'];
                                                    ?>
                                                    <td><?php echo $abn['kendala']; ?></td>
                                                    <td><?php echo $abn['teks_kategori']; ?></td>
                                                    <td><?php echo $abn['teks_korelasi']; ?></td>
                                                    <td><?php echo $abn['teks_klasifikasi']; ?></td>
                                                    <td><?php echo $abn['teks_ketidaksesuaian']; ?></td>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                <?php } ?>
                                            </tr>
                                            <tr class="td-Jalur_Conveyor">
                                                <th class="title"> Jalur Conveyor: </th>
                                                <td class="value"> <?php echo $data['jalur_conveyor']; ?></td>
                                                <?php
                                                // If there is abnormal data, break it into 4 columns.
                                                if (isset($data['abnormalitas']['Jalur_Conveyor'])) {
                                                    $abn = $data['abnormalitas']['Jalur_Conveyor'];
                                                    ?>
                                                    <td><?php echo $abn['kendala']; ?></td>
                                                    <td><?php echo $abn['teks_kategori']; ?></td>
                                                    <td><?php echo $abn['teks_korelasi']; ?></td>
                                                    <td><?php echo $abn['teks_klasifikasi']; ?></td>
                                                    <td><?php echo $abn['teks_ketidaksesuaian']; ?></td>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                <?php } ?>
                                            </tr>
                                            <tr class="td-Vacuum_Hood">
                                                <th class="title"> Vacuum Hood: </th>
                                                <td class="value"> <?php echo $data['vacuum_hood']; ?></td>
                                                <?php
                                                // If there is abnormal data, break it into 4 columns.
                                                if (isset($data['abnormalitas']['Vacuum_Hood'])) {
                                                    $abn = $data['abnormalitas']['Vacuum_Hood'];
                                                    ?>
                                                    <td><?php echo $abn['kendala']; ?></td>
                                                    <td><?php echo $abn['teks_kategori']; ?></td>
                                                    <td><?php echo $abn['teks_korelasi']; ?></td>
                                                    <td><?php echo $abn['teks_klasifikasi']; ?></td>
                                                    <td><?php echo $abn['teks_ketidaksesuaian']; ?></td>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                <?php } ?>
                                            </tr>
                                            <tr class="td-Antistatic">
                                                <th class="title"> Antistatic: </th>
                                                <td class="value"> <?php echo $data['antistatic']; ?></td>
                                                <?php
                                                // If there is abnormal data, break it into 4 columns.
                                                if (isset($data['abnormalitas']['Antistatic'])) {
                                                    $abn = $data['abnormalitas']['Antistatic'];
                                                    ?>
                                                    <td><?php echo $abn['kendala']; ?></td>
                                                    <td><?php echo $abn['teks_kategori']; ?></td>
                                                    <td><?php echo $abn['teks_korelasi']; ?></td>
                                                    <td><?php echo $abn['teks_klasifikasi']; ?></td>
                                                    <td><?php echo $abn['teks_ketidaksesuaian']; ?></td>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                <?php } ?>
                                            </tr>
                                            <tr class="td-Tekanan_Angin_Suplai">
                                                <th class="title"> Tekanan Angin Suplai: </th>
                                                <td class="value"> <?php echo $data['tekanan_angin_suplai']; ?></td>
                                                <?php
                                                // If there is abnormal data, break it into 4 columns.
                                                if (isset($data['abnormalitas']['Tekanan_Angin_Suplai'])) {
                                                    $abn = $data['abnormalitas']['Tekanan_Angin_Suplai'];
                                                    ?>
                                                    <td><?php echo $abn['kendala']; ?></td>
                                                    <td><?php echo $abn['teks_kategori']; ?></td>
                                                    <td><?php echo $abn['teks_korelasi']; ?></td>
                                                    <td><?php echo $abn['teks_klasifikasi']; ?></td>
                                                    <td><?php echo $abn['teks_ketidaksesuaian']; ?></td>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                <?php } ?>
                                            </tr>
                                            <tr class="td-Value_Tekanan_Angin">
                                                <th class="title"> Value Tekanan Angin: </th>
                                                <td class="value" colspan="6"> <?php echo $data['value_tekanan_angin']; ?>
                                                    bar</td>
                                            </tr>
                                            <tr class="td-Jarak_Slider_dengan_Nozzle">
                                                <th class="title"> Jarak Slider dengan Nozzle: </th>
                                                <td class="value"> <?php echo $data['jarak_slider_dengan_nozzle']; ?></td>
                                                <?php
                                                // If there is abnormal data, break it into 4 columns.
                                                if (isset($data['abnormalitas']['Jarak_Slider_dengan_Nozzle'])) {
                                                    $abn = $data['abnormalitas']['Jarak_Slider_dengan_Nozzle'];
                                                    ?>
                                                    <td><?php echo $abn['kendala']; ?></td>
                                                    <td><?php echo $abn['teks_kategori']; ?></td>
                                                    <td><?php echo $abn['teks_korelasi']; ?></td>
                                                    <td><?php echo $abn['teks_klasifikasi']; ?></td>
                                                    <td><?php echo $abn['teks_ketidaksesuaian']; ?></td>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                <?php } ?>
                                            </tr>
                                            <tr class="td-Rol_Penarik_Sachet_dan_Foil_Slitting_Shim">
                                                <th class="title"> Rol Penarik Sachet dan Foil Slitting Shim: </th>
                                                <td class="value">
                                                    <?php echo $data['rol_penarik_sachet_dan_foil_slitting_shim']; ?>
                                                </td>
                                                <?php
                                                // If there is abnormal data, break it into 4 columns.
                                                if (isset($data['abnormalitas']['Rol_Penarik_Sachet_dan_Foil_Slitting_Shim'])) {
                                                    $abn = $data['abnormalitas']['Rol_Penarik_Sachet_dan_Foil_Slitting_Shim'];
                                                    ?>
                                                    <td><?php echo $abn['kendala']; ?></td>
                                                    <td><?php echo $abn['teks_kategori']; ?></td>
                                                    <td><?php echo $abn['teks_korelasi']; ?></td>
                                                    <td><?php echo $abn['teks_klasifikasi']; ?></td>
                                                    <td><?php echo $abn['teks_ketidaksesuaian']; ?></td>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                <?php } ?>
                                            </tr>
                                            <tr class="td-Pisau Belah">
                                                <th class="title"> Pisau Belah: </th>
                                                <td class="value"> <?php echo $data['pisau_belah']; ?></td>
                                                <?php
                                                // If there is abnormal data, break it into 4 columns.
                                                if (isset($data['abnormalitas']['Pisau_Belah'])) {
                                                    $abn = $data['abnormalitas']['Pisau_Belah'];
                                                    ?>
                                                    <td><?php echo $abn['kendala']; ?></td>
                                                    <td><?php echo $abn['teks_kategori']; ?></td>
                                                    <td><?php echo $abn['teks_korelasi']; ?></td>
                                                    <td><?php echo $abn['teks_klasifikasi']; ?></td>
                                                    <td><?php echo $abn['teks_ketidaksesuaian']; ?></td>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                <?php } ?>
                                            </tr>
                                            <tr class="td-Modul_Pisau">
                                                <th class="title"> Modul Pisau: </th>
                                                <td class="value"> <?php echo $data['modul_pisau']; ?></td>
                                                <?php
                                                // If there is abnormal data, break it into 4 columns.
                                                if (isset($data['abnormalitas']['Modul_Pisau'])) {
                                                    $abn = $data['abnormalitas']['Modul_Pisau'];
                                                    ?>
                                                    <td><?php echo $abn['kendala']; ?></td>
                                                    <td><?php echo $abn['teks_kategori']; ?></td>
                                                    <td><?php echo $abn['teks_korelasi']; ?></td>
                                                    <td><?php echo $abn['teks_klasifikasi']; ?></td>
                                                    <td><?php echo $abn['teks_ketidaksesuaian']; ?></td>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                <?php } ?>
                                            </tr>
                                            <tr class="td-Inkjet">
                                                <th class="title"> Inkjet: </th>
                                                <td class="value"> <?php echo $data['inkjet']; ?></td>
                                                <?php
                                                // If there is abnormal data, break it into 4 columns.
                                                if (isset($data['abnormalitas']['Inkjet'])) {
                                                    $abn = $data['abnormalitas']['Inkjet'];
                                                    ?>
                                                    <td><?php echo $abn['kendala']; ?></td>
                                                    <td><?php echo $abn['teks_kategori']; ?></td>
                                                    <td><?php echo $abn['teks_korelasi']; ?></td>
                                                    <td><?php echo $abn['teks_klasifikasi']; ?></td>
                                                    <td><?php echo $abn['teks_ketidaksesuaian']; ?></td>
                                                    <?php
                                                } else {
                                                    ?>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                    <td>-</td>
                                                <?php } ?>
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
                                            <tr class="td-tanggal_perubahan">
                                                <th class="title"> Tanggal Approve: </th>
                                                <td class="value" colspan="6">
                                                    <?php echo !empty($data['tanggal_perubahan']) ? date('d-m-Y H:i:s', strtotime($data['tanggal_perubahan'])) : '<i><span class="text-muted">Belum ada tindakan</span></i>'; ?>
                                                </td>
                                            </tr>
                                            <tr class="td-user_perubah">
                                                <th class="title"> User Update: </th>
                                                <td class="value" colspan="6">
                                                    <?php echo !empty($data['user_perubah']) ? $data['user_perubah'] : '<i><span class="text-muted">Tidak ada tindakan</span></i>'; ?>
                                                </td>
                                            </tr>
                                            <tr class="td-updated_at">
                                                <th class="title"> Tanggal Update: </th>
                                                <td class="value" colspan="6">
                                                    <?php echo !empty($data['updated_at']) ? date('d-m-Y H:i:s', strtotime($data['updated_at'])) : '<i><span class="text-muted">Belum ada tindakan</span></i>'; ?>
                                                </td>
                                            </tr>
                                            <tr class="td-perubahan">
                                                <th class="title"> Perubahan: </th>
                                                <td class="value" colspan="6">
                                                    <?php echo !empty($data['perubahan']) ? $data['perubahan'] : '<i><span class="text-muted">Tidak ada tindakan</span></i>'; ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                        <!-- Table Body End -->
                                    </table>
                                </div>
                                <div class="p-3 d-flex">
                                    <a class="btn btn-sm btn-secondary mx-1" href="<?php print_link("sig/list2"); ?>">
                                        <i class="fa fa-arrow-left"></i> Kembali
                                    </a>
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
                                            <?php $export_pdf_link = $this->set_current_page_link(array('format' => 'print')); ?>
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
                                    <?php
                                    // 1. AMBIL DATA USER AKTIF
                                    $current_user = USER_NAME;
                                    $user_role = get_active_user('user_role_id');

                                    // 2. DEFINISI ROLE
                                    $izinKhusus = [25, 13, 17, 26]; // Role Supervisor ke atas
                                    $admin_roles = [16, 23, 22, 10]; // MASUKKAN ID ROLE ADMIN DI SINI (Contoh: 1)
                                
                                    // 3. LOGIKA HAK AKSES
                                    // Bisa Approve JIKA: Dia Supervisor ATAU dia Admin
                                    $can_approve = (in_array($user_role, $izinKhusus) || in_array($user_role, $admin_roles));

                                    // Bisa Edit JIKA: Dia yang buat data ATAU dia Admin
                                    $can_edit = ($current_user == $data['user_create'] || in_array($user_role, $admin_roles));

                                    // Bisa Delete HANYA JIKA: Dia Admin
                                    $can_delete = in_array($user_role, $admin_roles);
                                    ?>

                                    <!-- Tombol Approval (Muncul untuk Supervisor & Admin) -->
                                    <?php if ($can_approve) { ?>
                                        <a class="btn btn-sm btn-info has-tooltip" title="Approve This Record"
                                            href="<?php print_link("sig/edit/$rec_id"); ?>">
                                            <i class="fa fa-check-circle"></i> Approval
                                        </a>
                                    <?php } ?>

                                    <!-- Tombol Edit (Muncul untuk Pembuat Data & Admin) -->
                                    <?php if ($can_edit) { ?>
                                        <a class="btn btn-sm btn-warning mx-1"
                                            href="<?php print_link("sig/edit_data/$rec_id"); ?>">
                                            <i class="fa fa-edit"></i> Edit Data
                                        </a>
                                    <?php } ?>

                                    <!-- Tombol Delete (Hanya Admin) -->
                                    <?php if ($can_delete) { ?>
                                        <a class="btn btn-sm btn-danger record-delete-btn mx-1"
                                            href="<?php print_link("sig/delete/$rec_id/?csrf_token=$csrf_token&redirect=$current_page"); ?>"
                                            data-prompt-msg="Are you sure you want to delete this record?"
                                            data-display-style="none">
                                            <i class="fa fa-times"></i> Delete
                                        </a>
                                    <?php } ?>
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
</section>