<?php
$comp_model = new SharedController;
$Line_options = $comp_model->sig_Line_option_list();
$page_element_id = "list-page-" . random_str();
$current_page = $this->set_current_page_link();
$csrf_token = Csrf::$token;
//Page Data From Controller
$view_data = $this->view_data;
$records = $view_data->records;
$record_count = $view_data->record_count;
$total_records = $view_data->total_records;
$field_name = $this->route->field_name;
$field_value = $this->route->field_value;
$view_title = $this->view_title;
$show_header = $this->show_header;
$show_footer = $this->show_footer;
$show_pagination = $this->show_pagination;
?>
<section class="page" id="<?php echo $page_element_id; ?>" data-page-type="list" data-display-type="table"
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
            <div class="container-fluid">
                <div class="row ">
                    <div class="col ">
                        <h4 class="record-title">SIG</h4>
                    </div>
                    <div class="col-md-2 comp-grid">
                        <a class="btn btn btn-primary my-1" href="<?php print_link("sig/add") ?>">
                            <i class="fa fa-plus"></i>
                            Add New SIG
                        </a>
                    </div>
                    <div class="col-md-10 comp-grid">
                        <form class="search filter-form" action="<?php print_link('sig/list2'); ?>" method="get">
                            <div class="form-row align-items-end">
                                <div class="col-md-3 form-group mb-2">
                                    <label class="small text-muted mb-1" for="filter-date_from">Tanggal Dari</label>
                                    <input value="<?php echo get_value('date_from'); ?>" class="form-control form-control-sm"
                                        type="date" id="filter-date_from" name="date_from" />
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="small text-muted mb-1" for="filter-date_to">Tanggal Sampai</label>
                                    <input value="<?php echo get_value('date_to'); ?>" class="form-control form-control-sm"
                                        type="date" id="filter-date_to" name="date_to" />
                                </div>
                                <div class="col-md-3 form-group mb-2">
                                    <label class="small text-muted mb-1" for="filter-mesin">Nama Mesin</label>
                                    <select class="custom-select custom-select-sm" id="filter-mesin" name="mesin">
                                        <option value="">Semua Mesin</option>
                                        <?php
                                        if (!empty($Line_options)) {
                                            foreach ($Line_options as $option) {
                                                $value = (!empty($option['value']) ? $option['value'] : null);
                                                $label = (!empty($option['label']) ? $option['label'] : $value);
												if (strcasecmp($label, 'SIG') !== 0) { continue; }
                                                $selected = (get_value('mesin') == $value) ? 'selected' : '';
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
                                <div class="col-md-3 form-group mb-2">
                                    <label class="small text-muted mb-1" for="filter-search">Pencarian</label>
                                    <div class="input-group input-group-sm">
                                        <input value="<?php echo get_value('search'); ?>"
                                            class="form-control form-control-sm" type="text" id="filter-search"
                                            name="search" placeholder="Cari user, approval, dll..." />
                                        <div class="input-group-append">
                                            <button class="btn btn-primary btn-sm" type="submit">
                                                <i class="fa fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="col-12 text-right">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="fa fa-filter"></i> Terapkan Filter
                                    </button>
                                    <a href="<?php print_link('sig/list2'); ?>" class="btn btn-sm btn-outline-secondary">
                                        <i class="fa fa-times"></i> Reset
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="col-md-12 comp-grid">
                        <div class="">
                            <div class="subheader-container" style="padding-bottom:20px;">
                                <class="record-title">No:CR-PR-PR-1203.00 (25 Okt 2021)
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
                <div class="row ">
                    <div class="col-md-12 comp-grid">
                        <?php $this::display_page_errors(); ?>
                        <div class=" animated fadeIn page-content">
                            <div id="sig-list2-records">
                                <div id="page-report-body" class="table-responsive">
                                    <table class="table table-hover table-striped table-bordered table-sm text-left">
                                        <thead class="table-header bg-light">
                                            <tr>
                                                <!-- <th class="td-checkbox">
                                                    <label class="custom-control custom-checkbox custom-control-inline">
                                                        <input class="toggle-check-all custom-control-input"
                                                            type="checkbox" />
                                                        <span class="custom-control-label"></span>
                                                    </label>
                                                </th> -->
                                                <th class="td-btn"></th>
                                                <th class="td-sno">#</th>
                                                <th <?php echo (get_value('orderby') == 'created_at' ? 'class="sortedby td-created_at"' : null); ?>>
                                                    <?php Html::get_field_order_link('created_at', "Date Created"); ?>
                                                </th>
                                                <th <?php echo (get_value('orderby') == 'Line' ? 'class="sortedby td-Line"' : null); ?>>
                                                    <?php Html::get_field_order_link('Mesin', "Mesin"); ?>
                                                </th>
                                                <th <?php echo (get_value('orderby') == 'user_create' ? 'class="sortedby td-user_create"' : null); ?>>
                                                    <?php Html::get_field_order_link('user_create', "User Create"); ?>
                                                </th>
                                                <!-- <th class="td-Kendala"> Kendala</th> -->
                                                <th <?php echo (get_value('orderby') == 'user_approve' ? 'class="sortedby td-user_approve"' : null); ?>>
                                                    <?php Html::get_field_order_link('user_approve', "User Approve"); ?>
                                                </th>
                                                <th <?php echo (get_value('orderby') == 'approval' ? 'class="sortedby td-approval"' : null); ?>>
                                                    <?php Html::get_field_order_link('approval', "Approval"); ?>
                                                </th>
                                                <th <?php echo (get_value('orderby') == 'tanggal_perubahan' ? 'class="sortedby td-tanggal_perubahan"' : null); ?>>
                                                    <?php Html::get_field_order_link('tanggal_perubahan', "Tanggal Approve"); ?>
                                                </th>
                                                <th <?php echo (get_value('orderby') == 'user_perubah' ? 'class="sortedby td-user_perubah"' : null); ?>>
                                                    <?php Html::get_field_order_link('user_perubah', "User Pengubah"); ?>
                                                </th>
                                                <th <?php echo (get_value('orderby') == 'date_updated' ? 'class="sortedby td-date_updated"' : null); ?>>
                                                    <?php Html::get_field_order_link('updated_at', "Tanggal Update"); ?>
                                                </th>
                                                <!-- <th class="td-kategori_tag"> Kategori Tag</th> -->
                                                <!-- <th class="td-id_tagging"> Id Tagging</th> -->
                                                <!-- <th  class="td-bukaan_slider"> Bukaan Slider</th> -->
                                            </tr>
                                        </thead>
                                        <?php
                                        if (!empty($records)) {
                                            ?>
                                            <tbody class="page-data" id="page-data-<?php echo $page_element_id; ?>">
                                                <!--record-->
                                                <?php
                                                $counter = 0;
                                                foreach ($records as $data) {
                                                    $rec_id = (!empty($data['id_sig']) ? urlencode($data['id_sig']) : null);
                                                    $counter++;

                                                    // --- CEK ADA TIDAKNYA KENDALA DI PART MANA PUN ---
                                                    $ada_kendala = false;
                                                    $daftar_part = ['sealing_cross_dan_vertikal', 'guarding_akrilik', 'jalur_conveyor', 'antistatic', 'vacuum_hood', 'tekanan_angin_suplai', 'jarak_slider_dengan_nozzle', 'rol_penarik_sachet_dan_foil_slitting_shim', 'pisau_belah', 'modul_pisau', 'inkjet'];

                                                    foreach ($daftar_part as $part) {
                                                        if (isset($data[$part]) && ($data[$part] == 'NOK' || $data[$part] == 'Tidak Baik')) {
                                                            $ada_kendala = true;
                                                            break; // Berhenti ngecek jika sudah nemu 1 yang rusak
                                                        }
                                                    }

                                                    // Pilih warna: table-warning (kuning pastel) atau table-danger (merah pastel)
                                                    $row_bg = $ada_kendala ? 'table-danger' : '';
                                                    // --------------------------------------------------
                                                    ?>
                                                    <!-- Sisipkan variabel $row_bg ke dalam class tr -->
                                                    <tr class="<?php echo $row_bg; ?>">
                                                        <!-- <th class=" td-checkbox">
                                                            <label class="custom-control custom-checkbox custom-control-inline">
                                                                <input class="optioncheck custom-control-input"
                                                                    name="optioncheck[]" value="<?php echo $data['id_sig'] ?>"
                                                                    type="checkbox" />
                                                                <span class="custom-control-label"></span>
                                                            </label>
                                                        </th> -->
                                                        <th class="td-sno"><?php echo $counter; ?></th>
                                                        <th class="td-btn">
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

                                                            <!-- Tombol View (Semua Boleh Lihat) -->
                                                            <a class="btn btn-sm btn-success has-tooltip" title="View Record"
                                                                href="<?php print_link("sig/view/$rec_id"); ?>">
                                                                <i class="fa fa-eye"></i> View
                                                            </a>

                                                            <!-- Tombol Approval (Muncul untuk Supervisor & Admin) -->
                                                            <?php if ($can_approve) { ?>
                                                                <a class="btn btn-sm btn-info has-tooltip"
                                                                    title="Approve This Record"
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
                                                                    <i class="fa fa-times"></i> 
                                                                </a>
                                                            <?php } ?>
                                                        </th>
                                                        <td class="td-created_at">
                                                            <?php echo !empty($data['created_at']) ? date('d-m-Y H:i:s', strtotime($data['created_at'])) : '-'; ?>
                                                        </td>
                                                        <td class="td-Line"> <span><?php echo $data['nm_mesin']; ?></span>
                                                        </td>
                                                        <td class="td-user_create"> <?php echo $data['user_create']; ?></td>
                                                        <!-- <td class="td-Kendala"> <?php echo $data['kendala']; ?></td> -->
                                                        <td class="td-user_approve">
                                                            <span data-value="<?php echo $data['user_approve']; ?>">
                                                                <?php echo !empty($data['user_approve']) ? $data['user_approve'] : '-' ?>
                                                            </span>
                                                        </td>
                                                        <td class="td-approval">
                                                            <span
                                                                data-source='<?php echo json_encode_quote(Menu::$approval); ?>'
                                                                data-value="<?php echo $data['approval']; ?>">
                                                                <?php echo !empty($data['approval']) ? $data['approval'] : '-' ?>
                                                            </span>
                                                        </td>
                                                        <td class="td-tanggal_perubahan">
                                                            <span data-value="<?php echo $data['tanggal_perubahan']; ?>">
                                                                <?php echo !empty($data['tanggal_perubahan']) ? date('d-m-Y H:i:s', strtotime($data['tanggal_perubahan'])) : '-'; ?>
                                                            </span>
                                                        </td>
                                                        <td class="td-user_approve">
                                                            <span data-value="<?php echo $data['user_perubah']; ?>">
                                                                <?php echo !empty($data['user_perubah']) ? $data['user_perubah'] : '-' ?>
                                                            </span>
                                                        </td>
                                                        <td class="td-date_updated">
                                                            <span data-value="<?php echo $data['updated_at']; ?>">
                                                                <?php echo !empty($data['updated_at']) ? date('d-m-Y H:i:s', strtotime($data['updated_at'])) : '-'; ?>
                                                            </span>
                                                        </td>
                                                        <!-- <td class="td-kategori_tag"><?php
                                                        if (isset($data['kategori_tag'])) {
                                                            if ($data['kategori_tag'] == '1') {
                                                                echo "Red Tag";
                                                            } elseif ($data['kategori_tag'] == '2') {
                                                                echo "White Tag";
                                                            } else {
                                                                echo "-";
                                                            }
                                                        } else {
                                                            echo "-";
                                                        }
                                                        ?>
                                                        </td> -->
                                                        <!-- <td class="td-id_tagging"> -->
                                                        <!-- <?php if (!empty($data['id_tagging']) && $data['id_tagging'] != 0): ?> -->
                                                            <!-- <a href="http://10.127.17.10/breakdown_management/tag_filling_kemas/view/<?php echo $data['id_tagging']; ?>"> -->
                                                            <!-- <?php echo $data['id_tagging']; ?> -->
                                                            <!-- </a> -->
                                                            <!-- <?php else: ?> -->
                                                            <!-- - -->
                                                            <!-- <?php endif; ?> -->
                                                        <!-- </td> -->
                                                        <!-- <td class="td-bukaan_slider"> <?php echo $data['bukaan_slider']; ?></td> -->
                                                    </tr>
                                                    <?php
                                                }
                                                ?>
                                                <!--endrecord-->
                                            </tbody>
                                            <tbody class="search-data" id="search-data-<?php echo $page_element_id; ?>">
                                            </tbody>
                                            <?php
                                        }
                                        ?>
                                    </table>
                                    <?php
                                    if (empty($records)) {
                                        ?>
                                        <h4 class="bg-light text-center border-top text-muted animated bounce  p-3">
                                            <i class="fa fa-ban"></i> No record found
                                        </h4>
                                        <?php
                                    }
                                    ?>
                                </div>
                                <?php
                                if ($show_footer && !empty($records)) {
                                    ?>
                                    <div class=" border-top mt-2">
                                        <div class="row justify-content-center">
                                            <div class="col-md-auto justify-content-center">
                                                <div class="p-3 d-flex justify-content-between">
                                                    <!-- <button data-prompt-msg="Are you sure you want to delete these records?"
                                                        data-display-style="none"
                                                        data-url="<?php print_link("sig/delete/{sel_ids}/?csrf_token=$csrf_token&redirect=$current_page"); ?>"
                                                        class="btn btn-sm btn-danger btn-delete-selected d-none">
                                                        <i class="fa fa-times"></i> Delete Selected
                                                    </button> -->
                                                    <div class="dropup export-btn-holder mx-1">
                                                        <button class="btn btn-sm btn-primary dropdown-toggle" type="button"
                                                            data-toggle="dropdown" aria-haspopup="true"
                                                            aria-expanded="false">
                                                            <i class="fa fa-save"></i> Export
                                                        </button>
                                                        <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                            <?php $export_print_link = $this->set_current_page_link(array('format' => 'print')); ?>
                                                            <a class="dropdown-item export-link-btn" data-format="print"
                                                                href="<?php print_link($export_print_link); ?>"
                                                                target="_blank">
                                                                <img src="<?php print_link('assets/images/print.png') ?>"
                                                                    class="mr-2" /> PRINT
                                                            </a>
                                                            <?php $export_pdf_link = $this->set_current_page_link(array('format' => 'print')); ?>
                                                            <a class="dropdown-item export-link-btn" data-format="pdf"
                                                                href="<?php print_link($export_pdf_link); ?>"
                                                                target="_blank">
                                                                <img src="<?php print_link('assets/images/pdf.png') ?>"
                                                                    class="mr-2" /> PDF
                                                            </a>
                                                            <?php $export_word_link = $this->set_current_page_link(array('format' => 'word')); ?>
                                                            <a class="dropdown-item export-link-btn" data-format="word"
                                                                href="<?php print_link($export_word_link); ?>"
                                                                target="_blank">
                                                                <img src="<?php print_link('assets/images/doc.png') ?>"
                                                                    class="mr-2" /> WORD
                                                            </a>
                                                            <?php $export_csv_link = $this->set_current_page_link(array('format' => 'csv')); ?>
                                                            <a class="dropdown-item export-link-btn" data-format="csv"
                                                                href="<?php print_link($export_csv_link); ?>"
                                                                target="_blank">
                                                                <img src="<?php print_link('assets/images/csv.png') ?>"
                                                                    class="mr-2" /> CSV
                                                            </a>
                                                            <?php $export_excel_link = $this->set_current_page_link(array('format' => 'excel')); ?>
                                                            <a class="dropdown-item export-link-btn" data-format="excel"
                                                                href="<?php print_link($export_excel_link); ?>"
                                                                target="_blank">
                                                                <img src="<?php print_link('assets/images/xsl.png') ?>"
                                                                    class="mr-2" /> EXCEL
                                                            </a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col">
                                                <?php
                                                if ($show_pagination == true) {
                                                    $pager = new Pagination($total_records, $record_count);
                                                    $pager->route = $this->route;
                                                    $pager->show_page_count = true;
                                                    $pager->show_record_count = true;
                                                    $pager->show_page_limit = true;
                                                    $pager->limit_count = $this->limit_count;
                                                    $pager->show_page_number_list = true;
                                                    $pager->pager_link_range = 5;
                                                    $pager->render();
                                                }
                                                ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 comp-grid">
                    </div>
                </div>
            </div>
        </div>
</section>
