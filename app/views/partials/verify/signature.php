<?php
$d = $this->view_data;
$sig = $d['signature'] ?? null;
$is_valid = !empty($d['is_valid']);
$month_names = array(1 => 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
?>
<div class="container py-4 my-3" style="max-width: 680px;">
    <!-- Brand Header -->
    <div class="text-center mb-4">
        <img src="<?php print_link('assets/images/logo.png'); ?>" alt="Kalbe Bintang Toedjoe" style="max-height: 48px; margin-bottom: 12px;">
        <h5 class="font-weight-bold text-dark mb-0">PT BINTANG TOEDJOE</h5>
        <p class="text-muted small mb-0">Autonomous Maintenance Electronic Verification System</p>
    </div>

    <?php if ($is_valid && $sig) { ?>
        <!-- Valid Card -->
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-success text-white text-center py-3" style="background: linear-gradient(135deg, #198754 0%, #157347 100%) !important;">
                <div class="mb-2">
                    <i class="fa fa-check-circle" style="font-size: 52px;"></i>
                </div>
                <h4 class="font-weight-bold mb-1">DOKUMEN TERVERIFIKASI ASLI</h4>
                <p class="mb-0 small" style="opacity: 0.9;">Tanda Tangan Digital Sah & Integritas Dokumen Terjamin (CPOB Compliant)</p>
            </div>

            <div class="card-body p-4">
                <div class="alert alert-success d-flex align-items-center mb-4" style="background: #e8f5e9; border-color: #c8e6c9; color: #1b5e20;">
                    <i class="fa fa-shield fa-2x mr-3 text-success"></i>
                    <div class="small">
                        Dokumen Autonomous Maintenance Check Sheet ini telah ditandatangani secara elektronik resmi sesuai standar CPOB / BPOM dan tersimpan dalam audit trail sistem.
                    </div>
                </div>

                <h6 class="text-uppercase text-muted font-weight-bold mb-3" style="font-size: 11px; letter-spacing: 0.8px;">
                    <i class="fa fa-info-circle mr-1"></i> Informasi Penandatangan
                </h6>

                <table class="table table-sm table-borderless mb-4" style="font-size: 13.5px;">
                    <tr>
                        <td class="text-muted" style="width: 40%;">Kapasitas TTD:</td>
                        <td class="font-weight-bold">
                            <span class="badge badge-primary px-2 py-1" style="font-size: 12px;">
                                <?php echo htmlspecialchars($sig['verified_role']); ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Nama Penandatangan:</td>
                        <td class="font-weight-bold text-dark">
                            <?php echo htmlspecialchars($sig['signer_user']['nama'] ?? '-'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">NIK / Username:</td>
                        <td>
                            <code><?php echo htmlspecialchars($sig['signer_user']['username'] ?? '-'); ?></code>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Waktu Tanda Tangan:</td>
                        <td class="font-weight-bold text-dark">
                            <?php 
                            if (!empty($sig['verified_signed_at'])) {
                                echo date('d F Y, H:i:s', strtotime($sig['verified_signed_at'])) . ' WIB';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                    </tr>
                </table>

                <h6 class="text-uppercase text-muted font-weight-bold mb-3 border-top pt-3" style="font-size: 11px; letter-spacing: 0.8px;">
                    <i class="fa fa-cogs mr-1"></i> Data Dokumen Autonomous Maintenance
                </h6>

                <table class="table table-sm table-borderless mb-4" style="font-size: 13.5px;">
                    <tr>
                        <td class="text-muted" style="width: 40%;">Mesin / Line:</td>
                        <td class="font-weight-bold text-dark">
                            <?php echo htmlspecialchars($sig['machine_name']); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Periode Check Sheet:</td>
                        <td class="font-weight-bold text-dark">
                            Bulan <?php echo ($month_names[$sig['bulan']] ?? $sig['bulan']) . ' ' . $sig['tahun']; ?> (Periode <?php echo $sig['periode']; ?>)
                        </td>
                    </tr>
                    <tr>
                        <td class="text-muted">Status Dokumen:</td>
                        <td>
                            <span class="badge badge-<?php echo $sig['status'] === 'approved' ? 'success' : 'info'; ?> px-2 py-1">
                                <?php echo strtoupper(htmlspecialchars($sig['status'])); ?>
                            </span>
                        </td>
                    </tr>
                </table>

                <h6 class="text-uppercase text-muted font-weight-bold mb-3 border-top pt-3" style="font-size: 11px; letter-spacing: 0.8px;">
                    <i class="fa fa-lock mr-1"></i> Bukti Integritas Kriptografis
                </h6>

                <div class="bg-light p-3 rounded mb-3" style="border: 1px dashed #bbb;">
                    <div class="mb-2">
                        <small class="text-muted d-block font-weight-bold mb-1">SHA-256 Document Checksum:</small>
                        <code class="d-block text-break" style="font-size: 11px; color: #0d6efd;"><?php echo htmlspecialchars($sig['document_hash'] ?? '-'); ?></code>
                    </div>
                    <div>
                        <small class="text-muted d-block font-weight-bold mb-1">Verification Token:</small>
                        <code class="d-block text-break" style="font-size: 11px; color: #198754;"><?php echo htmlspecialchars($d['token']); ?></code>
                    </div>
                </div>

                <div class="text-center pt-2">
                    <small class="text-muted">
                        <i class="fa fa-qrcode mr-1"></i> Dipindai melalui Kalbe AM Digital Signature QR System
                    </small>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <!-- Invalid Card -->
        <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-danger text-white text-center py-4">
                <div class="mb-2">
                    <i class="fa fa-times-circle" style="font-size: 52px;"></i>
                </div>
                <h4 class="font-weight-bold mb-1">TANDA TANGAN TIDAK VALID</h4>
                <p class="mb-0 small">Dokumen tidak terdaftar atau token verifikasi tidak ditemukan dalam sistem.</p>
            </div>
            <div class="card-body p-4 text-center">
                <p class="text-muted mb-4">
                    QR Code yang Anda pindai tidak mengarah ke catatan tanda tangan digital yang sah di basis data PT Bintang Toedjoe. Mohon pastikan dokumen dicetak dari sistem Form AM resmi.
                </p>
                <?php if (!empty($d['token'])) { ?>
                    <div class="bg-light p-2 rounded mb-3 text-muted" style="font-size: 12px;">
                        Token yang diperiksa: <code><?php echo htmlspecialchars($d['token']); ?></code>
                    </div>
                <?php } ?>
                <a href="<?php print_link(''); ?>" class="btn btn-secondary px-4">
                    <i class="fa fa-home mr-1"></i> Kembali ke Beranda
                </a>
            </div>
        </div>
    <?php } ?>

    <!-- Footer Security Notice -->
    <div class="text-center text-muted small mt-4">
        &copy; <?php echo date('Y'); ?> PT Bintang Toedjoe - Site Pulogadung. Hak Cipta Dilindungi.<br>
        Dokumen ini dilindungi integritas kriptografis dan audit trail sistem.
    </div>
</div>
