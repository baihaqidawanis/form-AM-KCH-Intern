<?php
$comp_model = new SharedController;
$page_element_id = "edit-page-" . random_str();
$current_page = $this->set_current_page_link();
$csrf_token = Csrf::$token;
$data = $this->view_data;
//$rec_id = $data['__tableprimarykey'];
$page_id = $this->route->page_id;
$show_header = $this->show_header;
$view_title = $this->view_title;
$redirect_to = $this->redirect_to;
?>
<section class="page" id="<?php echo $page_element_id; ?>" data-page-type="edit" data-display-type=""
    data-page-url="<?php print_link($current_page); ?>">
    <?php
    if ($show_header == true) {
        ?>
        <div class="bg-light p-3 mb-3">
            <div class="container">
                <div class="row ">
                    <div class="col ">
                        <h4 class="record-title">Approve AM SIG</h4>
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
                    <div class=" ">
                        <?php
                        $this->render_page("sig/view/$data[id_sig]", array('show_header' => false, 'show_footer' => false));
                        ?>
                    </div>
                </div>
                <div class="col-md-12 comp-grid">
                    <?php $this::display_page_errors(); ?>
                    <div class="bg-light p-3 animated fadeIn page-content">
                        <form novalidate id="" role="form" enctype="multipart/form-data"
                            class="form page-form form-horizontal needs-validation"
                            action="<?php print_link("sig/edit/$page_id/?csrf_token=$csrf_token"); ?>" method="post">
                            <div>
                                <div class="form-group ">
                                    <div class="row">
                                        <div class="col-sm-4">
                                            <label class="control-label" for="approval">Approval <span
                                                    class="text-danger">*</span></label>
                                        </div>
                                        <div class="col-sm-8">
                                            <div class="">
                                                <select required="" id="ctrl-approval" name="approval"
                                                    placeholder="Pilih Approval ..." class="custom-select">
                                                    <option value="" selected hidden disabled>Pilih Approval ...
                                                    </option>
                                                    <?php
                                                    $approval_options = Menu::$approval;
                                                    $field_value = $data['approval'];
                                                    if (!empty($approval_options)) {
                                                        foreach ($approval_options as $option) {
                                                            $value = $option['value'];
                                                            $label = $option['label'];
                                                            $selected = ($value == $field_value ? 'selected' : null);
                                                            ?>
                                                            <option <?php echo $selected ?> value="<?php echo $value ?>">
                                                                <?php echo $label ?>
                                                            </option>
                                                            <?php
                                                        }
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-ajax-status"></div>
                            <div class="form-group text-center d-flex justify-content-center">
                                <a class="btn btn-secondary mx-1" href="<?php print_link("sig/list2"); ?>">
                                    <i class="fa fa-arrow-left"></i>
                                    Kembali
                                </a>
                                <button class="btn btn-primary mx-1" type="submit">
                                    Approve
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