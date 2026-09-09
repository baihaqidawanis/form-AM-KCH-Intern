<?php
$records = $this->view_data['records'] ?? array();
$csrf_token = Csrf::$token;
?>
<section class="page" id="master-mesin-page">
    <div class="bg-light p-3 mb-3">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h4 class="record-title m-0">Status Operasional Mesin</h4>
                    <small class="text-muted">Kelola status aktif / deaktivasi mesin fisik (Standar CPOB / GMP)</small>
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid">
        <?php $this::display_page_errors(); ?>

        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-striped mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th style="width: 50px;" class="text-center">No</th>
                                <th>Nama Mesin</th>
                                <th>Nomor Seri</th>
                                <th style="width: 150px;" class="text-center">Status</th>
                                <th>Detail Deaktivasi</th>
                                <th style="width: 180px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($records)) { ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Tidak ada data mesin.</td>
                                </tr>
                            <?php } else {
                                $no = 1;
                                foreach ($records as $row) {
                                    $is_deactive = ($row['status_operasional'] ?? 'AKTIF') === 'DEAKTIVASI';
                            ?>
                                <tr>
                                    <td class="text-center align-middle font-weight-bold"><?php echo $no++; ?></td>
                                    <td class="align-middle font-weight-bold text-primary">
                                        <?php echo htmlspecialchars($row['nama_mesin'] ?? '-'); ?>
                                    </td>
                                    <td class="align-middle text-muted">
                                        <?php echo htmlspecialchars($row['nomor_seri'] ?? '-'); ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php if ($is_deactive) { ?>
                                            <span class="badge badge-warning px-3 py-2" style="font-size: 13px;">
                                                <i class="fa fa-pause-circle"></i> DEAKTIVASI
                                            </span>
                                        <?php } else { ?>
                                            <span class="badge badge-success px-3 py-2" style="font-size: 13px;">
                                                <i class="fa fa-check-circle"></i> AKTIF
                                            </span>
                                        <?php } ?>
                                    </td>
                                    <td class="align-middle">
                                        <?php if ($is_deactive) { ?>
                                            <div><strong>Alasan:</strong> <?php echo htmlspecialchars($row['reason'] ?? '-'); ?></div>
                                            <?php if (!empty($row['notes'])) { ?>
                                                <small class="text-muted d-block">Catatan: <?php echo htmlspecialchars($row['notes']); ?></small>
                                            <?php } ?>
                                            <small class="text-secondary d-block">
                                                Oleh: <strong><?php echo htmlspecialchars($row['action_by_username'] ?? '-'); ?></strong>
                                                (<?php echo htmlspecialchars($row['started_at'] ?? '-'); ?>)
                                            </small>
                                        <?php } else { ?>
                                            <span class="text-muted">&mdash; Mesin beroperasi normal &mdash;</span>
                                        <?php } ?>
                                    </td>
                                    <td class="text-center align-middle">
                                        <?php if ($is_deactive) { ?>
                                            <form method="post" action="<?php print_link("master_mesin/reactivate?csrf_token=$csrf_token"); ?>" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin mengaktifkan kembali mesin ini?');">
                                                <input type="hidden" name="mesin_id" value="<?php echo $row['id']; ?>">
                                                <button type="submit" class="btn btn-sm btn-success">
                                                    <i class="fa fa-play"></i> Re-Aktivasi
                                                </button>
                                            </form>
                                        <?php } else { ?>
                                            <button type="button" class="btn btn-sm btn-outline-warning btn-deactivate"
                                                data-id="<?php echo $row['id']; ?>"
                                                data-nama="<?php echo htmlspecialchars($row['nama_mesin']); ?>">
                                                <i class="fa fa-power-off"></i> Deaktivasi
                                            </button>
                                        <?php } ?>
                                    </td>
                                </tr>
                            <?php } } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Modal Deaktivasi Mesin -->
<div class="modal fade" id="modalDeactivate" tabindex="-1" role="dialog" aria-labelledby="modalDeactivateLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <form method="post" action="<?php print_link("master_mesin/deactivate?csrf_token=$csrf_token"); ?>" class="needs-validation" novalidate>
                <input type="hidden" name="mesin_id" id="deactivate-mesin-id" value="">
                <div class="modal-header bg-warning text-dark">
                    <h5 class="modal-title font-weight-bold" id="modalDeactivateLabel">
                        <i class="fa fa-exclamation-triangle"></i> Deaktivasi Mesin
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info py-2 mb-3">
                        Mesin yang akan dideaktivasi: <strong id="deactivate-mesin-nama"></strong>
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold" for="deactivate-reason">Alasan Deaktivasi <span class="text-danger">*</span></label>
                        <select required id="deactivate-reason" name="reason" class="custom-select">
                            <option value="" disabled selected>Pilih alasan penonaktifan ...</option>
                            <option value="Overhaul Rutin Teknik">Overhaul / PM Rutin Teknik</option>
                            <option value="Tidak Ada Jadwal Produksi (Line Idle)">Tidak Ada Jadwal Produksi (Line Idle)</option>
                            <option value="Perbaikan Besar / Menunggu Sparepart">Perbaikan Besar / Menunggu Sparepart</option>
                            <option value="Sanitasi / Line Cleaning">Sanitasi / Line Cleaning</option>
                            <option value="Kualifikasi / Kalibrasi Mesin">Kualifikasi / Kalibrasi Mesin</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="form-group" id="group-reason-custom" style="display: none;">
                        <label class="font-weight-bold" for="deactivate-reason-custom">Sebutkan Alasan Lainnya <span class="text-danger">*</span></label>
                        <input type="text" id="deactivate-reason-custom" name="reason_custom" class="form-control" placeholder="Tuliskan alasan spesifik penonaktifan mesin...">
                    </div>

                    <div class="form-group">
                        <label class="font-weight-bold" for="deactivate-notes">Catatan Tambahan (Opsional)</label>
                        <textarea id="deactivate-notes" name="notes" class="form-control" rows="3" placeholder="Misal: estimasi overhaul selesai 3 hari..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning font-weight-bold">
                        <i class="fa fa-pause-circle"></i> Konfirmasi Deaktivasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var buttons = document.querySelectorAll('.btn-deactivate');
    var reasonSelect = document.getElementById('deactivate-reason');
    var customGroup = document.getElementById('group-reason-custom');
    var customInput = document.getElementById('deactivate-reason-custom');

    if (reasonSelect && customGroup && customInput) {
        reasonSelect.addEventListener('change', function() {
            if (this.value === 'Lainnya') {
                customGroup.style.display = 'block';
                customInput.setAttribute('required', 'required');
                customInput.focus();
            } else {
                customGroup.style.display = 'none';
                customInput.removeAttribute('required');
                customInput.value = '';
            }
        });
    }

    buttons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var id = this.getAttribute('data-id');
            var nama = this.getAttribute('data-nama');
            document.getElementById('deactivate-mesin-id').value = id;
            document.getElementById('deactivate-mesin-nama').innerText = nama;
            if (reasonSelect) { reasonSelect.value = ''; }
            if (customGroup) { customGroup.style.display = 'none'; }
            if (customInput) { customInput.removeAttribute('required'); customInput.value = ''; }
            if (window.$) {
                $('#modalDeactivate').modal('show');
            }
        });
    });
});
</script>
