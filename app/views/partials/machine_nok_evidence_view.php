<td>
  <?php if (!empty($abn['foto_before'])) { ?>
    <a class="part-image-link" target="_blank" href="<?php print_link($abn['foto_before']); ?>"><img src="<?php print_link($abn['foto_before']); ?>" alt="Foto Before" style="max-width:72px;max-height:54px;border-radius:6px"></a>
  <?php } else { echo '-'; } ?>
</td>
