<div class="row mt-2">
  <div class="col-md-6">
    <label>Nomor WR (Work Request) <small class="text-muted">(maks. 20 digit angka)</small></label>
    <input type="text" inputmode="numeric" pattern="[0-9]*" maxlength="20"
      name="no_wr_<?php echo $field; ?>" class="form-control"
      value="<?php echo htmlspecialchars($abn['no_wr'] ?? ''); ?>"
>
  </div>
</div>
