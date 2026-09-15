<?php
$comp_model = new SharedController;
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
$table_options = isset($view_data->table_options) ? $view_data->table_options : array();
$action_options = array('add', 'edit', 'edit_data', 'delete');
?>
<section class="page" id="<?php echo $page_element_id; ?>" data-page-type="list"  data-display-type="table" data-page-url="<?php print_link($current_page); ?>">
    <?php
    if( $show_header == true ){
    ?>
    <div class="bg-white p-3 mb-3 border-bottom shadow-sm rounded">
        <div class="container-fluid">
            <div class="row align-items-center mb-3">
                <div class="col">
                    <h3 class="record-title font-weight-bold text-dark mb-0">Audit Trail</h3>
                    <div class="text-muted small">Log Activity</div>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <form class="filter-form" action="<?php print_link('audit_log'); ?>" method="get">
                        <div class="form-row align-items-end">
                            <div class="col-md-2 form-group mb-2">
                                <label class="small font-weight-bold text-muted mb-1" for="filter-date_from">Start Date</label>
                                <input value="<?php echo get_value('date_from'); ?>" class="form-control form-control-sm" type="date" id="filter-date_from" name="date_from" />
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <label class="small font-weight-bold text-muted mb-1" for="filter-date_to">End Date</label>
                                <input value="<?php echo get_value('date_to'); ?>" class="form-control form-control-sm" type="date" id="filter-date_to" name="date_to" />
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <label class="small font-weight-bold text-muted mb-1" for="filter-table">Modul / Tabel</label>
                                <select class="custom-select custom-select-sm" id="filter-table" name="table_filter">
                                    <option value="">Semua Modul</option>
                                    <?php foreach ($table_options as $t) { $selected = (get_value('table_filter') == $t) ? 'selected' : ''; ?>
                                    <option <?php echo $selected; ?> value="<?php echo $t; ?>"><?php echo $t; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <label class="small font-weight-bold text-muted mb-1" for="filter-action">Action</label>
                                <select class="custom-select custom-select-sm" id="filter-action" name="action_filter">
                                    <option value="">Semua Action</option>
                                    <?php foreach ($action_options as $a) { $selected = (get_value('action_filter') == $a) ? 'selected' : ''; ?>
                                    <option <?php echo $selected; ?> value="<?php echo $a; ?>"><?php echo strtoupper($a); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <label class="small font-weight-bold text-muted mb-1" for="filter-search">Search</label>
                                <input value="<?php echo get_value('search'); ?>" class="form-control form-control-sm" type="text" id="filter-search" name="search" placeholder="Cari nama, IP, activity..." />
                            </div>
                            <div class="col-md-2 form-group mb-2 d-flex">
                                <button type="submit" class="btn btn-sm btn-success mr-1 flex-grow-1" style="background-color: #799351; border-color: #799351;"><i class="fa fa-search"></i> Search</button>
                                <a href="<?php print_link('audit_log'); ?>" class="btn btn-sm btn-outline-secondary" title="Reset filter"><i class="fa fa-refresh"></i></a>
                            </div>
                        </div>
                    </form>
                </div>
                    <div class="col-md-12 comp-grid">
                        <div class="">
                            <!-- Page bread crumbs components-->
                            <?php
                            if(!empty($field_name) || !empty($_GET['search'])){
                            ?>
                            <hr class="sm d-block d-sm-none" />
                            <nav class="page-header-breadcrumbs mt-2" aria-label="breadcrumb">
                                <ul class="breadcrumb m-0 p-1">
                                    <?php
                                    if(!empty($field_name)){
                                    ?>
                                    <li class="breadcrumb-item">
                                        <a class="text-decoration-none" href="<?php print_link('audit_log'); ?>">
                                            <i class="fa fa-angle-left"></i>
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item">
                                        <?php echo (get_value("tag") ? get_value("tag")  :  make_readable($field_name)); ?>
                                    </li>
                                    <li  class="breadcrumb-item active text-capitalize font-weight-bold">
                                        <?php echo (get_value("label") ? get_value("label")  :  make_readable(urldecode($field_value))); ?>
                                    </li>
                                    <?php 
                                    }   
                                    ?>
                                    <?php
                                    if(get_value("search")){
                                    ?>
                                    <li class="breadcrumb-item">
                                        <a class="text-decoration-none" href="<?php print_link('audit_log'); ?>">
                                            <i class="fa fa-angle-left"></i>
                                        </a>
                                    </li>
                                    <li class="breadcrumb-item text-capitalize">
                                        Search
                                    </li>
                                    <li  class="breadcrumb-item active text-capitalize font-weight-bold"><?php echo get_value("search"); ?></li>
                                    <?php
                                    }
                                    ?>
                                </ul>
                            </nav>
                            <!--End of Page bread crumbs components-->
                            <?php
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        }
        ?>
        <div  class="">
            <div class="container-fluid">
                <div class="row ">
                    <div class="col-md-12 comp-grid">
                        <?php $this :: display_page_errors(); ?>
                        <div  class=" animated fadeIn page-content">
                            <div id="audit_log-list-records">
                                <div id="page-report-body" class="table-responsive">
                                    <table class="table table-hover table-striped table-sm text-left border">
                                        <thead class="table-header bg-light">
                                            <tr>
                                                <th class="td-sno text-center" style="width: 45px;">No</th>
                                                <th class="td-Date" style="width: 100px;">Date</th>
                                                <th class="td-Time" style="width: 90px;">Time</th>
                                                <th class="td-IP" style="width: 130px;">IP Address</th>
                                                <th class="td-Name" style="width: 180px;">Name</th>
                                                <th class="td-Area" style="width: 130px;">Area</th>
                                                <th class="td-Activity">Activity</th>
                                                <th class="td-btn text-center" style="width: 85px;">Detail</th>
                                            </tr>
                                        </thead>
                                        <?php
                                        if(!empty($records)){
                                        ?>
                                        <tbody class="page-data" id="page-data-<?php echo $page_element_id; ?>">
                                            <!--record-->
                                            <?php
                                            $counter = 0;
                                            foreach($records as $data){
                                            $rec_id = (!empty($data['log_id']) ? urlencode($data['log_id']) : null);
                                            $counter++;
                                            $act = strtolower($data['Action'] ?? '');
                                            $act_badge = 'badge-action-other';
                                            if ($act === 'add' || $act === 'insert') {
                                                $act_badge = 'badge-action-add';
                                            } elseif ($act === 'edit' || $act === 'edit_data' || $act === 'update') {
                                                $act_badge = 'badge-action-edit';
                                            } elseif ($act === 'delete') {
                                                $act_badge = 'badge-action-delete';
                                            } elseif ($act === 'sign_period') {
                                                $act_badge = 'badge-action-other';
                                            }

                                            $ts = !empty($data['Timestamp']) ? strtotime($data['Timestamp']) : null;
                                            $date_val = $ts ? date('d/m/Y', $ts) : '-';
                                            $time_val = $ts ? date('H:i:s', $ts) : '-';
                                            $ip_val = !empty($data['ServerIP']) ? htmlspecialchars($data['ServerIP']) : '-';
                                            $name_val = !empty($data['user_nama']) ? htmlspecialchars($data['user_nama']) : (!empty($data['user_username']) ? htmlspecialchars($data['user_username']) : htmlspecialchars($data['UserID'] ?? '-'));
                                            $area_val = !empty($data['user_area']) ? htmlspecialchars($data['user_area']) : '-';

                                            $table_label = htmlspecialchars($data['TableName'] ?? '');
                                            if (!empty($data['RequestMsg'])) {
                                                $activity_text = htmlspecialchars($data['RequestMsg']);
                                            } elseif ($act === 'add' || $act === 'insert') {
                                                $activity_text = "$name_val menambahkan data baru pada $table_label";
                                            } elseif ($act === 'edit' || $act === 'edit_data' || $act === 'update') {
                                                $activity_text = "$name_val memperbarui data pada $table_label";
                                            } elseif ($act === 'delete') {
                                                $activity_text = "$name_val menghapus data pada $table_label";
                                            } elseif ($act === 'sign_period') {
                                                $activity_text = "$name_val menandatangani checklist digital periode $table_label";
                                            } elseif ($act === 'login') {
                                                $activity_text = "$name_val berhasil login";
                                            } else {
                                                $activity_text = "$name_val melakukan $act pada $table_label";
                                            }
                                            ?>
                                            <tr>
                                                <th class="td-sno text-muted text-center"><?php echo $counter; ?></th>
                                                <td class="td-Date font-monospace-apple text-muted"><?php echo $date_val; ?></td>
                                                <td class="td-Time font-monospace-apple text-muted"><?php echo $time_val; ?></td>
                                                <td class="td-IP font-monospace-apple"><span class="badge badge-light border"><?php echo $ip_val; ?></span></td>
                                                <td class="td-Name font-weight-medium">
                                                    <a size="sm" class="audit-user-link page-modal" href="<?php print_link("masterdetail/index/audit_log/users/nama/" . urlencode($data['UserID'])) ?>">
                                                        <span class="audit-user-icon"><i class="fa fa-user"></i></span> <?php echo $name_val; ?>
                                                    </a>
                                                </td>
                                                <td class="td-Area"><span class="badge badge-pill badge-light border px-2 py-1"><?php echo $area_val; ?></span></td>
                                                <td class="td-Activity">
                                                    <span class="badge badge-pill <?php echo $act_badge; ?> mr-1"><?php echo strtoupper($data['Action']); ?></span>
                                                    <span class="activity-desc"><?php echo $activity_text; ?></span>
                                                </td>
                                                <td class="td-btn text-center">
                                                    <a class="btn btn-sm btn-outline-success btn-view-ghost has-tooltip" title="Lihat Detail Log" href="<?php print_link("audit_log/view/$rec_id"); ?>">
                                                        <i class="fa fa-eye"></i> View
                                                    </a>
                                                </td>
                                            </tr>
                                            <?php 
                                            }
                                            ?>
                                            <!--endrecord-->
                                        </tbody>
                                        <tbody class="search-data" id="search-data-<?php echo $page_element_id; ?>"></tbody>
                                        <?php
                                        }
                                        ?>
                                    </table>
                                    <?php 
                                    if(empty($records)){
                                    ?>
                                    <h4 class="bg-light text-center border-top text-muted animated bounce  p-3">
                                        <i class="fa fa-ban"></i> No record found
                                    </h4>
                                    <?php
                                    }
                                    ?>
                                </div>
                                <?php
                                if( $show_footer && !empty($records)){
                                ?>
                                <div class=" border-top mt-2">
                                    <div class="row justify-content-center">    
                                        <div class="col-md-auto justify-content-center">    
                                            <div class="p-3 d-flex justify-content-between">    
                                                <div class="dropup export-btn-holder mx-1">
                                                    <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                        <i class="fa fa-save"></i> Export
                                                    </button>
                                                    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                        <?php $export_print_link = $this->set_current_page_link(array('format' => 'print')); ?>
                                                        <a class="dropdown-item export-link-btn" data-format="print" href="<?php print_link($export_print_link); ?>" target="_blank">
                                                            <img src="<?php print_link('assets/images/print.png') ?>" class="mr-2" /> PRINT
                                                            </a>
                                                            <?php $export_pdf_link = $this->set_current_page_link(array('format' => 'pdf')); ?>
                                                            <a class="dropdown-item export-link-btn" data-format="pdf" href="<?php print_link($export_pdf_link); ?>" target="_blank">
                                                                <img src="<?php print_link('assets/images/pdf.png') ?>" class="mr-2" /> PDF
                                                                </a>
                                                                <?php $export_word_link = $this->set_current_page_link(array('format' => 'word')); ?>
                                                                <a class="dropdown-item export-link-btn" data-format="word" href="<?php print_link($export_word_link); ?>" target="_blank">
                                                                    <img src="<?php print_link('assets/images/doc.png') ?>" class="mr-2" /> WORD
                                                                    </a>
                                                                    <?php $export_csv_link = $this->set_current_page_link(array('format' => 'csv')); ?>
                                                                    <a class="dropdown-item export-link-btn" data-format="csv" href="<?php print_link($export_csv_link); ?>" target="_blank">
                                                                        <img src="<?php print_link('assets/images/csv.png') ?>" class="mr-2" /> CSV
                                                                        </a>
                                                                        <?php $export_excel_link = $this->set_current_page_link(array('format' => 'excel')); ?>
                                                                        <a class="dropdown-item export-link-btn" data-format="excel" href="<?php print_link($export_excel_link); ?>" target="_blank">
                                                                            <img src="<?php print_link('assets/images/xsl.png') ?>" class="mr-2" /> EXCEL
                                                                            </a>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="col">   
                                                                <?php
                                                                if($show_pagination == true){
                                                                $pager = new Pagination($total_records, $record_count);
                                                                $pager->route = $this->route;
                                                                $pager->show_page_count = true;
                                                                $pager->show_record_count = true;
                                                                $pager->show_page_limit =true;
                                                                $pager->limit_count = $this->limit_count;
                                                                $pager->show_page_number_list = true;
                                                                $pager->pager_link_range=5;
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
                                    </div>
                                </div>
                            </div>
                        </section>
