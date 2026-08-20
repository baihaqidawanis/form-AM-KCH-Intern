<?php
$page_id = null;
$comp_model = new SharedController;
$current_page = $this->set_current_page_link();
?>
<div>
    <div  class="">
        <div class="container">
            <div class="row ">
                <div class="col-md-12 comp-grid">
                    <div class=""></div>
                </div>
            </div>
        </div>
    </div>
    <div  class="">
        <div class="container">
            <div class="row ">
                <div class="col-md-12 comp-grid">
                    <h4 ><b>Total AM Hari ini</b></h4>
                </div>
            </div>
        </div>
    </div>
    <div  class="">
        <div class="container">
            <div class="row ">
                <div class="col-md-12 comp-grid">
                    <div class="card ">
                        <div class="card-header p-0 pt-2 px-2">
                            <ul class="nav  nav-tabs   ">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#TabPage-1-Page1" role="tab" aria-selected="true">
                                        Filling
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#TabPage-1-Page2" role="tab" aria-selected="true">
                                        Packaging
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#TabPage-1-Page3" role="tab" aria-selected="true">
                                        Compounding
                                    </a>
                                </li>
                            </ul>
                        </div>
                        <div class="card-body">
                            <div class="tab-content">
                                <div class="tab-pane show active fade" id="TabPage-1-Page1" role="tabpanel">
                                    <?php
                                    $filling_machines = array(
                                        array('path' => 'sig', 'label' => 'SIG', 'count' => $comp_model->getcount_sig()),
                                        array('path' => 'joeya', 'label' => 'Joeya', 'count' => $comp_model->getcount_joeya()),
                                        array('path' => 'illapak_1_2', 'label' => 'Illapak 1 - 2', 'count' => $comp_model->getcount_illapak_1_2()),
                                        array('path' => 'illapak_3_12', 'label' => 'Illapak 3 - 12', 'count' => $comp_model->getcount_illapak_3_12()),
                                        array('path' => 'unifill_b', 'label' => 'Unifill', 'count' => $comp_model->getcount_unifill_b()),
                                    );
                                    foreach ($filling_machines as $fm) {
                                    ?>
                                    <a class="animated zoomIn record-count card bg-light text-dark"  href="<?php print_link($fm['path'] . "/") ?>">
                                        <div class="row">
                                            <div class="col-2">
                                                <i class="fa fa-globe"></i>
                                            </div>
                                            <div class="col-10">
                                                <div class="flex-column justify-content align-center">
                                                    <div class="title"><?php echo $fm['label']; ?></div>
                                                    <small class=""></small>
                                                </div>
                                            </div>
                                            <h4 class="value"><strong><?php echo $fm['count']; ?></strong></h4>
                                        </div>
                                    </a>
                                    <?php } ?>
                                </div>
                                <div class="tab-pane fade" id="TabPage-1-Page2" role="tabpanel">
                                    <?php
                                    $packaging_machines = array(
                                        array('path' => 'chimei', 'label' => 'Chimei', 'count' => $comp_model->getcount_chimei()),
                                        array('path' => 'temach', 'label' => 'Temach', 'count' => $comp_model->getcount_temach()),
                                        array('path' => 'jihcheng', 'label' => 'Jihcheng', 'count' => $comp_model->getcount_jihcheng()),
                                        array('path' => 'jinsung_1_4', 'label' => 'Jinsung 1 - 4', 'count' => $comp_model->getcount_jinsung_1_4()),
                                        array('path' => 'jinsung_5', 'label' => 'Jinsung 5', 'count' => $comp_model->getcount_jinsung_5()),
                                        array('path' => 'best_pack', 'label' => 'Best Pack', 'count' => $comp_model->getcount_best_pack()),
                                    );
                                    foreach ($packaging_machines as $pm) {
                                    ?>
                                    <a class="animated zoomIn record-count card bg-light text-dark"  href="<?php print_link($pm['path'] . "/") ?>">
                                        <div class="row">
                                            <div class="col-2">
                                                <i class="fa fa-globe"></i>
                                            </div>
                                            <div class="col-10">
                                                <div class="flex-column justify-content align-center">
                                                    <div class="title"><?php echo $pm['label']; ?></div>
                                                    <small class=""></small>
                                                </div>
                                            </div>
                                            <h4 class="value"><strong><?php echo $pm['count']; ?></strong></h4>
                                        </div>
                                    </a>
                                    <?php } ?>
                                </div>
                                <div class="tab-pane fade" id="TabPage-1-Page3" role="tabpanel">
                                    <?php
                                    $compounding_machines = array(
                                        array('path' => 'cosmec', 'label' => 'Cosmec', 'count' => $comp_model->getcount_cosmec()),
                                        array('path' => 'fbd_jaw_chuan', 'label' => 'FBD Jaw Chuan', 'count' => $comp_model->getcount_fbd_jaw_chuan()),
                                        array('path' => 'fbd_glatt', 'label' => 'FBD Glatt', 'count' => $comp_model->getcount_fbd_glatt()),
                                        array('path' => 'supermixer', 'label' => 'Supermixer', 'count' => $comp_model->getcount_supermixer()),
                                        array('path' => 'storage_tank', 'label' => 'Storage Tank Silverson', 'count' => $comp_model->getcount_storage_tank()),
                                        array('path' => 'storage_tank_tetrapak', 'label' => 'Storage Tank Tetrapak', 'count' => $comp_model->getcount_storage_tank_tetrapak()),
                                        array('path' => 'mixing_tank', 'label' => 'Mixing Tank', 'count' => $comp_model->getcount_mixing_tank()),
                                    );
                                    foreach ($compounding_machines as $cm) {
                                    ?>
                                    <a class="animated zoomIn record-count card bg-light text-dark"  href="<?php print_link($cm['path'] . "/") ?>">
                                        <div class="row">
                                            <div class="col-2">
                                                <i class="fa fa-globe"></i>
                                            </div>
                                            <div class="col-10">
                                                <div class="flex-column justify-content align-center">
                                                    <div class="title"><?php echo $cm['label']; ?></div>
                                                    <small class=""></small>
                                                </div>
                                            </div>
                                            <h4 class="value"><strong><?php echo $cm['count']; ?></strong></h4>
                                        </div>
                                    </a>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
