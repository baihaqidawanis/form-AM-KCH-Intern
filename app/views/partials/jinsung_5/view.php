<?php
$data = $this->view_data;
$parts = $data['parts'];
$rec_id = !empty($data['id_jinsung_5']) ? $data['id_jinsung_5'] : null;
$csrf_token = Csrf::$token;
$current_page = $this->set_current_page_link();
?>
<section class="page">
  <div class="bg-light p-3 mb-3"><div class="container"><h4>View AM Jinsung 5</h4></div></div>
  <div class="container">
    <?php $this::display_page_errors(); ?>
    <?php if (!empty($data['id_jinsung_5'])) { ?>
    <div class="card">
      <div class="card-body">
        <div id="page-report-body">
          <table class="table table-bordered">
            <tr><th width="25%">Nama Mesin</th><td><?php echo $data['nm_mesin'] ?: '-'; ?></td></tr>
            <tr><th>Tanggal</th><td><?php echo $data['created_at']; ?></td></tr>
            <tr><th>Pembuat</th><td><?php echo $data['user_create']; ?></td></tr>
          </table>
          <div class="table-responsive">
            <table class="table table-bordered">
              <thead><tr><th>Nama Part</th><th>Kondisi</th><th>Kendala</th><th>Kategori Tag</th><th>Korelasi</th><th>Klasifikasi</th><th>Ketidaksesuaian</th></tr></thead>
              <tbody>
                <?php foreach ($parts as $field => $label) { $abn = $data['abnormalitas'][$field] ?? null; ?>
                  <tr>
                    <th><?php echo $label; ?></th>
                    <td><?php $val = $data[$field] ?? ''; if ($val === 'OK') { echo '<span class="badge badge-success">OK</span>'; } elseif ($val === 'NOK') { echo '<span class="badge badge-danger">NOK</span>'; } else { echo '-'; } ?></td>
                    <td><?php echo $abn['kendala'] ?? '-'; ?></td>
                    <td><?php echo $abn['teks_kategori'] ?? '-'; ?></td>
                    <td><?php echo $abn['teks_korelasi'] ?? '-'; ?></td>
                    <td><?php echo $abn['teks_klasifikasi'] ?? '-'; ?></td>
                    <td><?php echo $abn['teks_ketidaksesuaian'] ?? '-'; ?></td>
                  </tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>

        <?php
        // Hak akses tombol aksi - pola sama persis kayak sig/view.php
        $current_user = USER_NAME;
        $user_role = get_active_user('user_role_id');
        $izinKhusus = [25, 13, 17, 26]; // Role Supervisor ke atas
        $admin_roles = [16, 23, 22, 10]; // Role Admin
        $can_approve = (in_array($user_role, $izinKhusus) || in_array($user_role, $admin_roles));
        $can_edit = ($current_user == $data['user_create'] || in_array($user_role, $admin_roles));
        $can_delete = in_array($user_role, $admin_roles);
        ?>
        <div class="mt-3 d-flex flex-wrap align-items-center">
          <a class="btn btn-secondary mx-1" href="<?php print_link('jinsung_5') ?>"><i class="fa fa-arrow-left"></i> Kembali</a>
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
            <a class="btn btn-sm btn-info has-tooltip mx-1" title="Approve This Record" href="<?php print_link("jinsung_5/edit/$rec_id"); ?>">
              <i class="fa fa-check-circle"></i> Approval
            </a>
          <?php } ?>
          <?php if ($can_edit) { ?>
            <a class="btn btn-sm btn-warning mx-1" href="<?php print_link("jinsung_5/edit_data/$rec_id"); ?>">
              <i class="fa fa-edit"></i> Edit Data
            </a>
          <?php } ?>
          <?php if ($can_delete) { ?>
            <a class="btn btn-sm btn-danger record-delete-btn mx-1"
              href="<?php print_link("jinsung_5/delete/$rec_id/?csrf_token=$csrf_token&redirect=$current_page"); ?>"
              data-prompt-msg="Are you sure you want to delete this record?" data-display-style="none">
              <i class="fa fa-times"></i> Delete
            </a>
          <?php } ?>
        </div>
      </div>
    </div>
    <?php } ?>
  </div>
</section>
