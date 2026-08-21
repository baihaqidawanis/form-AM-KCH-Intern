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
    <div  class="bg-light p-3 mb-3">
        <div class="container-fluid">
            <div class="row ">
                <div class="col ">
                    <h4 class="record-title">Audit Log</h4>
                    <small class="text-muted">Log ini digenerate otomatis oleh sistem tiap ada perubahan data — tidak bisa ditambah manual.</small>
                </div>
                <div class="col-sm-12 mt-2">
                    <form class="filter-form" action="<?php print_link('audit_log'); ?>" method="get">
                        <div class="form-row align-items-end">
                            <div class="col-md-2 form-group mb-2">
                                <label class="small text-muted mb-1" for="filter-table">Modul / Tabel</label>
                                <select class="custom-select custom-select-sm" id="filter-table" name="table_filter">
                                    <option value="">Semua Modul</option>
                                    <?php foreach ($table_options as $t) { $selected = (get_value('table_filter') == $t) ? 'selected' : ''; ?>
                                    <option <?php echo $selected; ?> value="<?php echo $t; ?>"><?php echo $t; ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <label class="small text-muted mb-1" for="filter-action">Action</label>
                                <select class="custom-select custom-select-sm" id="filter-action" name="action_filter">
                                    <option value="">Semua Action</option>
                                    <?php foreach ($action_options as $a) { $selected = (get_value('action_filter') == $a) ? 'selected' : ''; ?>
                                    <option <?php echo $selected; ?> value="<?php echo $a; ?>"><?php echo ucfirst($a); ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <label class="small text-muted mb-1" for="filter-date_from">Tanggal Dari</label>
                                <input value="<?php echo get_value('date_from'); ?>" class="form-control form-control-sm" type="date" id="filter-date_from" name="date_from" />
                            </div>
                            <div class="col-md-2 form-group mb-2">
                                <label class="small text-muted mb-1" for="filter-date_to">Tanggal Sampai</label>
                                <input value="<?php echo get_value('date_to'); ?>" class="form-control form-control-sm" type="date" id="filter-date_to" name="date_to" />
                            </div>
                            <div class="col-md-3 form-group mb-2">
                                <label class="small text-muted mb-1" for="filter-search">Pencarian</label>
                                <input value="<?php echo get_value('search'); ?>" class="form-control form-control-sm" type="text" id="filter-search" name="search" placeholder="Cari user, URL, dll..." />
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="col-12 text-right">
                                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter"></i> Terapkan Filter</button>
                                <a href="<?php print_link('audit_log'); ?>" class="btn btn-sm btn-outline-secondary"><i class="fa fa-times"></i> Reset</a>
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
                                    <table class="table table-hover table-striped table-sm text-left">
                                        <thead class="table-header bg-light">
                                            <tr>
                                                <th class="td-sno">#</th>
                                                <th  class="td-Timestamp"> Timestamp</th>
                                                <th  class="td-Action"> Action</th>
                                                <th  class="td-TableName"> Tablename</th>
                                                <th  class="td-UserID"> Userid</th>
                                                <th class="td-btn"></th>
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
                                            ?>
                                            <tr>
                                                <th class="td-sno"><?php echo $counter; ?></th>
                                                <td class="td-Timestamp"> <?php echo $data['Timestamp']; ?></td>
                                                <td class="td-Action"> <?php echo $data['Action']; ?></td>
                                                <td class="td-TableName"> <?php echo $data['TableName']; ?></td>
                                                <td class="td-UserID">
                                                    <a size="sm" class="btn btn-sm btn-primary page-modal" href="<?php print_link("masterdetail/index/audit_log/users/nama/" . urlencode($data['UserID'])) ?>">
                                                        <i class="fa fa-eye"></i> <?php echo !empty($data['user_username']) ? htmlspecialchars($data['user_username']) : $data['UserID']; ?>
                                                    </a>
                                                </td>
                                                <th class="td-btn">
                                                    <a class="btn btn-sm btn-success has-tooltip" title="View Record" href="<?php print_link("audit_log/view/$rec_id"); ?>">
                                                        <i class="fa fa-eye"></i> View
                                                    </a>
                                                </th>
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
