<?php
$data = $this->view_data ?: array();
$records = $data['records'] ?? array();
$role_labels = $data['role_labels'] ?? array();
$printed_at = $data['printed_at'] ?? datetime_now();
?>
<div class="roster-sheet">
	<div class="d-print-none" style="text-align:right; margin-bottom:10px;">
		<button type="button" onclick="window.print()" style="border:1px solid #0b4f8a; background:#0b4f8a; color:#fff; border-radius:4px; padding:7px 13px; cursor:pointer;">
			Cetak / Print
		</button>
	</div>
	<table style="width:100%; border-collapse:collapse; margin-bottom:8px;">
		<tr>
			<td style="width:18%; border:1px solid #222; padding:6px; text-align:center;">
				<img src="<?php print_link('assets/images/logo.png'); ?>" alt="Bintang Toedjoe" style="max-width:145px; max-height:48px; object-fit:contain;">
			</td>
			<td style="width:60%; border:1px solid #222; padding:7px; text-align:center;">
				<div style="font-size:16px; font-weight:bold;">DAFTAR AKUN, IDENTITAS &amp; SPESIMEN PARAF PENGGUNA</div>
				<div style="font-size:11px; margin-top:4px;">Autonomous Maintenance Standard - Site Pulogadung</div>
			</td>
			<td style="width:22%; border:1px solid #222; padding:6px; font-size:9px; line-height:1.5;">
				<strong>Tanggal Cetak</strong><br><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($printed_at)), ENT_QUOTES, 'UTF-8'); ?><br>
				<strong>Dokumen Kontrol</strong><br>CR-PR-PR-1203.00
			</td>
		</tr>
	</table>

	<table style="width:100%; border-collapse:collapse; table-layout:fixed; font-size:8.5px;">
		<thead>
			<tr style="background:#d9eaf7;">
				<th style="width:3%; border:1px solid #222; padding:5px;">No</th>
				<th style="width:7%; border:1px solid #222; padding:5px;">ID Sistem</th>
				<th style="width:10%; border:1px solid #222; padding:5px;">NIK (Username)</th>
				<th style="width:16%; border:1px solid #222; padding:5px;">Nama Lengkap</th>
				<th style="width:10%; border:1px solid #222; padding:5px;">Role</th>
				<th style="width:12%; border:1px solid #222; padding:5px;">Area Penugasan</th>
				<th style="width:17%; border:1px solid #222; padding:5px;">Mesin Penugasan</th>
				<th style="width:15%; border:1px solid #222; padding:5px;">Spesimen Paraf</th>
				<th style="width:10%; border:1px solid #222; padding:5px;">Status Akun</th>
			</tr>
		</thead>
		<tbody>
		<?php if (!$records) { ?>
			<tr><td colspan="9" style="border:1px solid #222; padding:12px; text-align:center;">Tidak ada data pengguna.</td></tr>
		<?php } else { foreach ($records as $index => $record) {
			$paraf = $record['paraf_image'] ?? null;
			$has_paraf = is_valid_base64_png_data_uri($paraf);
		?>
			<tr style="page-break-inside:avoid;">
				<td style="border:1px solid #222; padding:4px; text-align:center;"><?php echo $index + 1; ?></td>
				<td style="border:1px solid #222; padding:4px; text-align:center; font-weight:bold;">ID: <?php echo intval($record['id_user']); ?></td>
				<td style="border:1px solid #222; padding:4px;"><?php echo htmlspecialchars($record['username'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
				<td style="border:1px solid #222; padding:4px;"><?php echo htmlspecialchars($record['nama'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
				<td style="border:1px solid #222; padding:4px;"><?php echo htmlspecialchars($role_labels[intval($record['user_role_id'] ?? 0)] ?? 'Tidak Dikenal', ENT_QUOTES, 'UTF-8'); ?></td>
				<td style="border:1px solid #222; padding:4px;"><?php echo htmlspecialchars($record['area'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
				<td style="border:1px solid #222; padding:4px; overflow-wrap:anywhere;"><?php echo htmlspecialchars($record['mesin'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
				<td style="border:1px solid #222; padding:3px; height:36px; text-align:center;">
					<?php if ($has_paraf) { ?>
						<img src="<?php echo htmlspecialchars($paraf, ENT_QUOTES, 'UTF-8'); ?>" alt="Paraf <?php echo htmlspecialchars($record['nama'] ?? '', ENT_QUOTES, 'UTF-8'); ?>" style="max-width:90px; max-height:32px; object-fit:contain;">
					<?php } else { ?><em>(Belum Ada Paraf)</em><?php } ?>
				</td>
				<td style="border:1px solid #222; padding:4px; text-align:center;"><?php echo htmlspecialchars($record['account_status'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
			</tr>
		<?php }} ?>
		</tbody>
	</table>
	<div style="margin-top:7px; font-size:8px; color:#333; display:flex; justify-content:space-between;">
		<span>Dokumen ini merupakan daftar spesimen paraf pengguna sistem AM.</span>
		<span>Total pengguna: <?php echo count($records); ?></span>
	</div>
</div>
