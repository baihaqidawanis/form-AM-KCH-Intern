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
$pending_counts = !empty($view_data->pending_counts) ? $view_data->pending_counts : array();
// Badge notifikasi kayak notif WA -- cuma muncul kalau ada record yang
// belum di-approve (approval IS NULL) buat mesin itu.
$approval_badge = function ($machine_key) use ($pending_counts) {
  $count = !empty($pending_counts[$machine_key]) ? intval($pending_counts[$machine_key]) : 0;
  if ($count < 1) { return ''; }
  return ' <span class="badge badge-danger rounded-pill ml-1">' . $count . '</span>';
};
?>
<section class="page" id="<?php echo $page_element_id; ?>" data-page-type="list"  data-display-type="table" data-page-url="<?php print_link($current_page); ?>">
    <?php
    if( $show_header == true ){
    ?>
    <div  class="bg-light p-3 mb-3">
        <div class="container">
            <div class="row ">
                <div class="col ">
                    <h3 ><div class="title">
                        <strong>Approval List</strong>
                    </div>
                    <style>
                        .title {
                        text-align: center;
                        }
                    </style></h3>
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
                    <h4 ><div class="alert">
                        <strong>Compounding</strong>
                    </div>
                    <style>
                        .alert {
                        padding: 20px;
                        background-color: DodgerBlue;
                        color: white;
                        text-align: center;
                        }
                    </style></h4>
                    <div class="card mb-4">
                        <div class="card-header p-0 pt-2 px-2">
                            <ul class="nav  nav-tabs   ">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#TabPage-1-Page1" role="tab" aria-selected="true">
                                        Cosmec<?php echo $approval_badge('cosmec'); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#TabPage-1-Page2" role="tab" aria-selected="true">
                                        FBD Jaw Chuan<?php echo $approval_badge('fbd_jaw_chuan'); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#TabPage-1-Page3" role="tab" aria-selected="true">
                                        FBD Glatt<?php echo $approval_badge('fbd_glatt'); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#TabPage-1-Page4" role="tab" aria-selected="true">
                                        Supermixer<?php echo $approval_badge('supermixer'); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#TabPage-1-Page8" role="tab" aria-selected="true">
                                        Granulator<?php echo $approval_badge('granulator'); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#TabPage-1-Page5" role="tab" aria-selected="true">
                                        Storage Tank Silverson<?php echo $approval_badge('storage_tank'); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#TabPage-1-Page7" role="tab" aria-selected="true">
                                        Storage Tank Tetrapak<?php echo $approval_badge('storage_tank_tetrapak'); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#TabPage-1-Page6" role="tab" aria-selected="true">
                                        Mixing Tank<?php echo $approval_badge('mixing_tank'); ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="tab-pane show active fade" id="TabPage-1-Page1" role="tabpanel">
                                    <h4 >Cosmec</h4>
                                    <div class=" ">
                                        <?php
                                        $this->render_page("cosmec/list2?limit_count=20" , array( 'show_header' => false ));
                                        ?>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="TabPage-1-Page2" role="tabpanel">
                                    <h4 >FBD Jaw Chuan</h4>
                                    <div class="bg-light reset-grids">
                                        <?php
                                        $this->render_page("fbd_jaw_chuan/list2?limit_count=20" , array( 'show_header' => false ));
                                        ?>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="TabPage-1-Page3" role="tabpanel">
                                    <h4 >FBD Glatt</h4>
                                    <div class="bg-light reset-grids">
                                        <?php
                                        $this->render_page("fbd_glatt/list2?limit_count=20" , array( 'show_header' => false ));
                                        ?>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="TabPage-1-Page4" role="tabpanel">
                                    <h4 >Supermixer</h4>
                                    <div class="bg-light reset-grids">
                                        <?php
                                        $this->render_page("supermixer/list2?limit_count=20" , array( 'show_header' => false ));
                                        ?>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="TabPage-1-Page8" role="tabpanel">
                                    <h4 >Granulator</h4>
                                    <div class="bg-light reset-grids">
                                        <?php
                                        $this->render_page("granulator/list2?limit_count=20" , array( 'show_header' => false ));
                                        ?>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="TabPage-1-Page5" role="tabpanel">
                                    <h4 >Storage Tank Silverson</h4>
                                    <div class="bg-light reset-grids">
                                        <?php
                                        $this->render_page("storage_tank/list2?limit_count=20" , array( 'show_header' => false ));
                                        ?>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="TabPage-1-Page7" role="tabpanel">
                                    <h4 >Storage Tank Tetrapak</h4>
                                    <div class="bg-light reset-grids">
                                        <?php
                                        $this->render_page("storage_tank_tetrapak/list2?limit_count=20" , array( 'show_header' => false ));
                                        ?>
                                    </div>
                                </div>
                                <div class="tab-pane fade" id="TabPage-1-Page6" role="tabpanel">
                                    <h4 >Mixing Tank</h4>
                                    <div class="bg-light reset-grids">
                                        <?php
                                        $this->render_page("mixing_tank/list2?limit_count=20" , array( 'show_header' => false ));
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h4 ><div class="alert">
                        <strong>Filling</strong>
                    </div>
                    <style>
                        .alert {
                        padding: 20px;
                        background-color: DodgerBlue;
                        color: white;
                        text-align: center;
                        }
                    </style></h4>
                    <div class="card mb-4">
                        <div class="card-header p-0 pt-2 px-2">
                            <ul class="nav  nav-tabs   ">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#TabPage-2-Page1" role="tab" aria-selected="true">
                                        SIG<?php echo $approval_badge('sig'); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link " data-toggle="tab" href="#TabPage-2-Page2" role="tab" aria-selected="true">
                                        Joeya<?php echo $approval_badge('joeya'); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link " data-toggle="tab" href="#TabPage-2-Page3" role="tab" aria-selected="true">
                                        Illapak 1 - 2<?php echo $approval_badge('illapak_1_2'); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link " data-toggle="tab" href="#TabPage-2-Page4" role="tab" aria-selected="true">
                                        Illapak 3 - 12<?php echo $approval_badge('illapak_3_12'); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link " data-toggle="tab" href="#TabPage-2-Page5" role="tab" aria-selected="true">
                                        Unifill B<?php echo $approval_badge('unifill_b'); ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="tab-pane show active fade" id="TabPage-2-Page1" role="tabpanel">
                                    <h4 >SIG</h4>
                                    <div class=" ">
                                        <?php
                                        $this->render_page("sig/list2?limit_count=20" , array( 'show_header' => false ));
                                        ?>
                                    </div>
                                </div>
                                <div class="tab-pane  fade" id="TabPage-2-Page2" role="tabpanel">
                                    <h4 >Joeya</h4>
                                    <div class="bg-light reset-grids">
                                        <?php
                                        $this->render_page("joeya/list2?limit_count=20" , array( 'show_header' => false ));
                                        ?>
                                    </div>
                                </div>
                                <div class="tab-pane  fade" id="TabPage-2-Page3" role="tabpanel">
                                    <h4 >Illapak 1 - 2</h4>
                                    <div class="bg-light reset-grids">
                                        <?php
                                        $this->render_page("illapak_1_2/list2?limit_count=20" , array( 'show_header' => false ));
                                        ?>
                                    </div>
                                </div>
                                <div class="tab-pane  fade" id="TabPage-2-Page4" role="tabpanel">
                                    <h4 >Illapak 3 - 12</h4>
                                    <div class="bg-light reset-grids">
                                        <?php
                                        $this->render_page("illapak_3_12/list2?limit_count=20" , array( 'show_header' => false ));
                                        ?>
                                    </div>
                                </div>
                                <div class="tab-pane  fade" id="TabPage-2-Page5" role="tabpanel">
                                    <h4 >Unifill B</h4>
                                    <div class="bg-light reset-grids">
                                        <?php
                                        $this->render_page("unifill_b/list2?limit_count=20" , array( 'show_header' => false ));
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h4 ><div class="alert">
                        <strong>Kemas</strong>
                    </div>
                    <style>
                        .alert {
                        padding: 20px;
                        background-color: DodgerBlue;
                        color: white;
                        text-align: center;
                        }
                    </style></h4>
                    <div class="card mb-4">
                        <div class="card-header p-0 pt-2 px-2">
                            <ul class="nav  nav-tabs   ">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#TabPage-3-Page1" role="tab" aria-selected="true">
                                        Jihcheng<?php echo $approval_badge('jihcheng'); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link " data-toggle="tab" href="#TabPage-3-Page2" role="tab" aria-selected="true">
                                        Jinsung 1 - 4<?php echo $approval_badge('jinsung_1_4'); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link " data-toggle="tab" href="#TabPage-3-Page3" role="tab" aria-selected="true">
                                        Jinsung 5<?php echo $approval_badge('jinsung_5'); ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="tab-pane show active fade" id="TabPage-3-Page1" role="tabpanel">
                                    <h4 >Jihcheng</h4>
                                    <div class="bg-light reset-grids">
                                        <?php
                                        $this->render_page("jihcheng/list2?limit_count=20" , array( 'show_header' => false ));
                                        ?>
                                    </div>
                                </div>
                                <div class="tab-pane  fade" id="TabPage-3-Page2" role="tabpanel">
                                    <h4 >Jinsung 1 - 4</h4>
                                    <div class="bg-light reset-grids">
                                        <?php
                                        $this->render_page("jinsung_1_4/list2?limit_count=20" , array( 'show_header' => false ));
                                        ?>
                                    </div>
                                </div>
                                <div class="tab-pane  fade" id="TabPage-3-Page3" role="tabpanel">
                                    <h4 >Jinsung 5</h4>
                                    <div class="bg-light reset-grids">
                                        <?php
                                        $this->render_page("jinsung_5/list2?limit_count=20" , array( 'show_header' => false ));
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <h4 ><div class="alert">
                        <strong>Wrapping dan Pack Cartoning</strong>
                    </div>
                    <style>
                        .alert {
                        padding: 20px;
                        background-color: DodgerBlue;
                        color: white;
                        text-align: center;
                        }
                    </style></h4>
                    <div class="card mb-4">
                        <div class="card-header p-0 pt-2 px-2">
                            <ul class="nav  nav-tabs   ">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#TabPage-4-Page1" role="tab" aria-selected="true">
                                        Chimei<?php echo $approval_badge('chimei'); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link " data-toggle="tab" href="#TabPage-4-Page2" role="tab" aria-selected="true">
                                        Temach<?php echo $approval_badge('temach'); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link " data-toggle="tab" href="#TabPage-4-Page3" role="tab" aria-selected="true">
                                        Best Pack<?php echo $approval_badge('best_pack'); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link " data-toggle="tab" href="#TabPage-4-Page4" role="tab" aria-selected="true">
                                        Check Weigher<?php echo $approval_badge('check_weigher'); ?>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link " data-toggle="tab" href="#TabPage-4-Page5" role="tab" aria-selected="true">
                                        Conveyor SIG<?php echo $approval_badge('conveyor_sig'); ?>
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="tab-pane show active fade" id="TabPage-4-Page1" role="tabpanel">
                                    <h4 >Chimei</h4>
                                    <div class=" ">
                                        <?php
                                        $this->render_page("chimei/list2?limit_count=20" , array( 'show_header' => false ));
                                        ?>
                                    </div>
                                </div>
                                <div class="tab-pane  fade" id="TabPage-4-Page2" role="tabpanel">
                                    <h4 >Temach</h4>
                                    <div class="bg-light reset-grids">
                                        <?php
                                        $this->render_page("temach/list2?limit_count=20" , array( 'show_header' => false ));
                                        ?>
                                    </div>
                                </div>
                                <div class="tab-pane  fade" id="TabPage-4-Page3" role="tabpanel">
                                    <h4 >Inkjet Kemas & Best Pack</h4>
                                    <div class="bg-light reset-grids">
                                        <?php
                                        $this->render_page("best_pack/list2?limit_count=20" , array( 'show_header' => false ));
                                        ?>
                                    </div>
                                </div>
                                <div class="tab-pane  fade" id="TabPage-4-Page4" role="tabpanel">
                                    <h4 >Check Weigher</h4>
                                    <div class="bg-light reset-grids">
                                        <?php
                                        $this->render_page("check_weigher/list2?limit_count=20" , array( 'show_header' => false ));
                                        ?>
                                    </div>
                                </div>
                                <div class="tab-pane  fade" id="TabPage-4-Page5" role="tabpanel">
                                    <h4 >Conveyor SIG</h4>
                                    <div class="bg-light reset-grids">
                                        <?php
                                        $this->render_page("conveyor_sig/list2?limit_count=20" , array( 'show_header' => false ));
                                        ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
