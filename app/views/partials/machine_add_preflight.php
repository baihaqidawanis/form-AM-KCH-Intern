<?php
$data = $this->view_data;
$machine_key = $data['machine_key'];
$display_name = $data['display_name'];
$shifts = $data['shifts'] ?? array('1');
$selected_shift = (string)($data['selected_shift'] ?? '');
$units = $data['units'] ?? array();
$selected_machine_id = intval($data['selected_machine_id'] ?? 0);
$single_unit = !empty($data['single_unit']);
$duplicate = $data['duplicate'] ?? null;
$operational_date = $data['operational_date'] ?? '';
?>
<section class="page">
  <div class="bg-light p-3 mb-3">
    <div class="container-fluid">
      <h4 class="record-title mb-1">Add Autonomous Maintenance <?php echo htmlspecialchars($display_name); ?></h4>
      <p class="text-muted mb-0">Pilih konteks pemeriksaan sebelum membuka checklist.</p>
    </div>
  </div>
  <div class="container-fluid">
    <?php $this::display_page_errors(); ?>
    <div class="row justify-content-center">
      <div class="col-lg-7 col-xl-6">
        <div class="card border-0 shadow-sm">
          <div class="card-body p-4">
            <h5 class="font-weight-bold mb-1">Pilih Pemeriksaan</h5>
            <p class="text-muted mb-4">Checklist baru ditampilkan setelah kombinasi shift dan unit dipastikan belum pernah diisi.</p>

            <?php if ($duplicate) { ?>
              <div class="alert alert-warning">
                <strong><i class="fa fa-exclamation-triangle"></i> AM sudah diisi.</strong>
                <div class="mt-1">Kombinasi ini sudah tersimpan untuk tanggal operasional <strong><?php echo htmlspecialchars($operational_date); ?></strong><?php echo !empty($duplicate['user_create']) ? ' oleh <strong>' . htmlspecialchars($duplicate['user_create']) . '</strong>' : ''; ?><?php echo !empty($duplicate['created_at']) ? ' pada ' . htmlspecialchars($duplicate['created_at']) : ''; ?>. Pilih kombinasi lain.</div>
              </div>
            <?php } ?>

            <form id="am-preflight-form" method="get" action="<?php print_link($machine_key . '/add'); ?>">
              <div class="form-group">
                <label class="font-weight-bold" for="preflight-shift">Shift <span class="text-danger">*</span></label>
                <select required id="preflight-shift" name="shift" class="custom-select">
                  <option value="">Pilih shift ...</option>
                  <?php foreach ($shifts as $shift) { ?><option value="<?php echo htmlspecialchars($shift); ?>" <?php echo $selected_shift === (string)$shift ? 'selected' : ''; ?>>Shift <?php echo htmlspecialchars($shift); ?></option><?php } ?>
                </select>
              </div>

              <?php if ($single_unit) { ?>
                <input type="hidden" name="mesin" value="<?php echo intval($units[0]['id']); ?>">
                <div class="form-group">
                  <label class="font-weight-bold">Unit Mesin</label>
                  <div class="form-control bg-light"><?php echo htmlspecialchars($units[0]['nama_mesin']); ?></div>
                </div>
              <?php } else { ?>
                <div class="form-group">
                  <label class="font-weight-bold" for="preflight-machine">Unit Mesin <span class="text-danger">*</span></label>
                  <select required id="preflight-machine" name="mesin" class="custom-select">
                    <option value="">Pilih unit mesin ...</option>
                    <?php foreach ($units as $unit) { ?><option value="<?php echo intval($unit['id']); ?>" <?php echo $selected_machine_id === intval($unit['id']) ? 'selected' : ''; ?>><?php echo htmlspecialchars($unit['nama_mesin']); ?><?php echo !empty($unit['nomor_seri']) ? ' - ' . htmlspecialchars($unit['nomor_seri']) : ''; ?></option><?php } ?>
                  </select>
                </div>
              <?php } ?>

              <button type="submit" class="btn btn-primary px-4">Mulai Pengisian AM</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
<script>
$(function () {
  $('#am-preflight-form').on('submit', function (event) {
    event.preventDefault();
    event.stopImmediatePropagation();
    if (!this.checkValidity()) {
      this.reportValidity();
      return false;
    }
    window.location.assign(this.action + '?' + $(this).serialize());
    return false;
  });
});
</script>
