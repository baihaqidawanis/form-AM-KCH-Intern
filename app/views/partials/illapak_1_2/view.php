<?php
$data = $this->view_data;
$parts = isset($data['parts']) ? $data['parts'] : array();
$abnormal = isset($data['abnormalitas']) ? $data['abnormalitas'] : array();
$id = isset($data['id_illapak_1_2']) ? $data['id_illapak_1_2'] : null;
$csrf_token = Csrf::$token;
$current_page = $this->set_current_page_link();
?>
<section class="page"><div class="bg-light p-3 mb-3"><div class="container-fluid"><h4>View Autonomous Maintenance Illapak 1-2 #<?php echo $id; ?></h4></div></div>
<div class="container-fluid"><?php $this::display_page_errors(); ?>
<?php if ($id) { ?>
<div id="page-report-body">
<div class="card mb-3"><div class="card-body"><div class="row"><div class="col-md-3"><strong>Mesin:</strong> <?php echo $data['nm_mesin'] ?? '-'; ?></div><div class="col-md-3"><strong>Pembuat:</strong> <?php echo $data['user_create'] ?? '-'; ?></div><div class="col-md-3"><strong>Waktu:</strong> <?php echo $data['created_at'] ?? '-'; ?></div><div class="col-md-3"><strong>Approval:</strong> <?php echo $data['approval'] ?? '-'; ?> (<?php echo $data['user_approve'] ?? '-'; ?>)</div></div></div></div>
<div class="card mb-3"><div class="card-body"><h5>Ringkasan Hasil Inspeksi Part</h5><table class="table table-bordered table-sm"><thead><tr><th>Nama Part</th><th>Status Kondisi</th><th>Kendala / Tagging</th></tr></thead><tbody><?php foreach ($parts as $field => $label) { $val = isset($data[$field]) ? $data[$field] : '-'; $ab = isset($abnormal[$field]) ? $abnormal[$field] : null; ?><tr><td><strong><?php echo $label; ?></strong></td><td><?php if ($val==='OK') echo '<span class="badge badge-success">OK</span>'; elseif ($val==='NOK') echo '<span class="badge badge-danger">NOK</span>'; else echo '-'; ?></td><td><?php if ($ab) { echo '<div><strong>Kendala:</strong> ' . htmlspecialchars($ab['kendala'] ?? '-') . '</div>'; echo '<div><small>Tag: ' . htmlspecialchars($ab['teks_kategori'] ?? '-') . ' | Korelasi: ' . htmlspecialchars($ab['teks_korelasi'] ?? '-') . ' | Klasifikasi: ' . htmlspecialchars($ab['teks_klasifikasi'] ?? '-') . ' | Ketidaksesuaian: ' . htmlspecialchars($ab['teks_ketidaksesuaian'] ?? '-') . '</small></div>'; } else echo '-'; ?></td></tr><?php } ?></tbody></table></div></div>
</div>
<?php
// Hak akses tombol aksi - pola sama persis kayak joeya/sig view.php
$current_user = USER_NAME;
$user_role = get_active_user('user_role_id');
$izinKhusus = [25, 13, 17, 26]; // Role Supervisor ke atas
$admin_roles = [16, 23, 22, 10]; // Role Admin
$can_approve = (in_array($user_role, $izinKhusus) || in_array($user_role, $admin_roles));
$can_edit = ($current_user == ($data['user_create'] ?? null) || in_array($user_role, $admin_roles));
$can_delete = in_array($user_role, $admin_roles);
?>
<div class="mb-3 d-flex flex-wrap align-items-center">
  <a class="btn btn-secondary mx-1" href="<?php print_link('illapak_1_2') ?>"><i class="fa fa-arrow-left"></i> Kembali</a>
  <div class="dropup export-btn-holder mx-1">
    <button class="btn btn-primary dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      <i class="fa fa-save"></i> Export
    </button>
    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
      <a class="dropdown-item export-link-btn" data-format="print" href="<?php print_link($this->set_current_page_link(array('format' => 'print'))); ?>" target="_blank"><i class="fa fa-print mr-2"></i> PRINT</a>
      <a class="dropdown-item export-link-btn" data-format="pdf" href="<?php print_link($this->set_current_page_link(array('format' => 'pdf'))); ?>" target="_blank"><i class="fa fa-file-pdf-o mr-2"></i> PDF</a>
      <a class="dropdown-item export-link-btn" data-format="word" href="<?php print_link($this->set_current_page_link(array('format' => 'word'))); ?>" target="_blank"><i class="fa fa-file-word-o mr-2"></i> WORD</a>
      <a class="dropdown-item export-link-btn" data-format="csv" href="<?php print_link($this->set_current_page_link(array('format' => 'csv'))); ?>" target="_blank"><i class="fa fa-file-text-o mr-2"></i> CSV</a>
      <a class="dropdown-item export-link-btn" data-format="excel" href="<?php print_link($this->set_current_page_link(array('format' => 'excel'))); ?>" target="_blank"><i class="fa fa-file-excel-o mr-2"></i> EXCEL</a>
    </div>
  </div>
  <?php if ($can_approve) { ?>
    <a class="btn btn-sm btn-info has-tooltip mx-1" title="Approve This Record" href="<?php print_link("illapak_1_2/edit/$id"); ?>">
      <i class="fa fa-check-circle"></i> Approval
    </a>
  <?php } ?>
  <?php if ($can_edit) { ?>
    <a class="btn btn-sm btn-warning mx-1" href="<?php print_link("illapak_1_2/edit_data/$id"); ?>">
      <i class="fa fa-edit"></i> Edit Data
    </a>
  <?php } ?>
  <?php if ($can_delete) { ?>
    <a class="btn btn-sm btn-danger record-delete-btn mx-1"
      href="<?php print_link("illapak_1_2/delete/$id/?csrf_token=$csrf_token&redirect=$current_page"); ?>"
      data-prompt-msg="Are you sure you want to delete this record?" data-display-style="none">
      <i class="fa fa-times"></i> Delete
    </a>
  <?php } ?>
</div>
<?php } ?>
</div></section>
