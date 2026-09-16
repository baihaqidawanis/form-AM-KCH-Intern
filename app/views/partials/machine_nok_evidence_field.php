<?php $existing_photo = $abn['foto_before'] ?? ''; ?>
<div class="nok-photo-box mt-3" data-existing-photo="<?php echo $existing_photo ? '1' : '0'; ?>">
  <label class="font-weight-bold mb-1">Foto Before <span class="text-danger">*</span></label>
  <div class="small text-muted mb-2">Wajib untuk kondisi NOK. JPEG/PNG/WebP, maksimal 5 MB; sistem menyimpan JPEG terkompresi.</div>
  <?php if ($existing_photo) { ?>
    <div class="mb-2"><a class="part-image-link" target="_blank" href="<?php print_link($existing_photo); ?>"><img src="<?php print_link($existing_photo); ?>" alt="Foto Before" style="max-width:160px;max-height:120px;border-radius:8px;border:1px solid #ddd"></a></div>
  <?php } ?>
  <div class="mb-2"><label class="small">Upload dari perangkat</label><input type="file" name="foto_before_<?php echo $field; ?>" class="form-control-file nok-photo-input" accept="image/jpeg,image/png,image/webp"><button type="button" class="btn btn-sm btn-outline-secondary mt-2 nok-photo-cancel d-none">Batalkan pilihan foto</button><div class="nok-photo-preview-wrap d-none mt-2"><div class="small text-muted mb-1">Preview foto baru</div><img class="nok-photo-preview" alt="Preview Foto Before" style="max-width:160px;max-height:120px;border-radius:8px;border:1px solid #ddd"></div></div>
</div>
