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
<section class="page" id="<?php echo $page_element_id; ?>" data-page-type="view"  data-display-type="table" data-page-url="<?php print_link($current_page); ?>">
    <?php
    if( $show_header == true ){
    ?>
    <div  class="bg-light p-3 mb-3">
        <div class="container">
            <div class="row ">
                <div class="col ">
                    <h4 class="record-title">View  Audit Log</h4>
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
                    <div class="card mb-4 animated fadeIn page-content">
                        <?php
                        $counter = 0;
                        if(!empty($data)){
                        $rec_id = (!empty($data['log_id']) ? urlencode($data['log_id']) : null);
                        $counter++;
                        ?>
                        <div id="page-report-body" class="audit-log-detail-table">
                            <table class="table table-hover table-borderless table-striped mb-0">
                                <!-- Table Body Start -->
                                <tbody class="page-data" id="page-data-<?php echo $page_element_id; ?>">
                                    <tr  class="td-log_id">
                                        <th class="title"> Log Id: </th>
                                        <td class="value"> <?php echo $data['log_id']; ?></td>
                                    </tr>
                                    <tr  class="td-Timestamp">
                                        <th class="title"> Timestamp: </th>
                                        <td class="value"> <?php echo $data['Timestamp']; ?></td>
                                    </tr>
                                    <tr  class="td-id_log">
                                        <th class="title"> Id Log: </th>
                                        <td class="value"> <?php echo $data['id_log']; ?></td>
                                    </tr>
                                    <tr  class="td-Action">
                                        <th class="title"> Action: </th>
                                        <td class="value"> <?php echo $data['Action']; ?></td>
                                    </tr>
                                    <tr  class="td-TableName">
                                        <th class="title"> Tablename: </th>
                                        <td class="value"> <?php echo $data['TableName']; ?></td>
                                    </tr>
                                    <tr  class="td-UserID">
                                        <th class="title"> Userid: </th>
                                        <td class="value">
                                            <a size="sm" class="btn btn-sm btn-primary page-modal" href="<?php print_link("masterdetail/index/audit_log/users/nama/" . urlencode($data['UserID'])) ?>">
                                                <i class="fa fa-eye"></i> <?php echo !empty($data['user_username']) ? htmlspecialchars($data['user_username']) : $data['UserID']; ?>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr  class="td-SQLQuery">
                                        <th class="title"> Sqlquery: </th>
                                        <td class="value"><div class="audit-log-code-scroll"><pre><?php echo htmlspecialchars((string)($data['SQLQuery'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></pre></div></td>
                                    </tr>
                                    <tr  class="td-ServerIP">
                                        <th class="title"> Serverip: </th>
                                        <td class="value"> <?php echo $data['ServerIP']; ?></td>
                                    </tr>
                                    <tr  class="td-RequestURL">
                                        <th class="title"> Requesturl: </th>
                                        <td class="value"> <?php echo $data['RequestURL']; ?></td>
                                    </tr>
                                    <tr  class="td-RequestData">
                                        <th class="title"> Requestdata: </th>
                                        <td class="value"><div class="audit-log-code-scroll"><pre><?php echo htmlspecialchars((string)($data['RequestData'] ?? ''), ENT_QUOTES, 'UTF-8'); ?></pre></div></td>
                                    </tr>
                                    <tr  class="td-RequestCompleted">
                                        <th class="title"> Requestcompleted: </th>
                                        <td class="value"> <?php echo $data['RequestCompleted']; ?></td>
                                    </tr>
                                    <tr  class="td-RequestMsg">
                                        <th class="title"> Requestmsg: </th>
                                        <td class="value"> <?php echo $data['RequestMsg']; ?></td>
                                    </tr>
                                </tbody>
                                <!-- Table Body End -->
                            </table>
                        </div>
                        <div class="p-3 d-flex">
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
                                            <?php
                                            }
                                            else{
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
<style>
  .audit-log-detail-table { overflow: hidden; }
  .audit-log-detail-table table { table-layout: fixed; width: 100%; }
  .audit-log-detail-table th.title { width: 175px; vertical-align: top; }
  .audit-log-detail-table td.value { min-width: 0; vertical-align: top; }
  .audit-log-code-scroll { max-width: 100%; overflow-x: auto; border: 1px solid #E2E8E3; border-radius: 8px; background: #F7FAF7; }
  .audit-log-code-scroll pre { margin: 0; padding: 12px; min-width: max-content; white-space: pre; font: 12px/1.5 SFMono-Regular, Consolas, "Liberation Mono", monospace; color: #334155; }
  @media (max-width: 767px) { .audit-log-detail-table th.title { width: 118px; font-size: .78rem; } }
</style>
