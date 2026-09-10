<!DOCTYPE html>
<html lang="id">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo htmlspecialchars($this->get_page_title(), ENT_QUOTES, 'UTF-8'); ?></title>
	<style>
		@page { size: A4 landscape; margin: 8mm; }
		html, body { margin: 0; padding: 0; background: #fff; color: #111; font-family: Arial, Helvetica, sans-serif; }
		@media screen { body { padding: 16px; background: #eef1f5; } .roster-sheet { max-width: 1120px; margin: auto; background: #fff; padding: 20px; box-shadow: 0 2px 12px rgba(0,0,0,.12); } }
		@media print { .d-print-none { display: none !important; } .roster-sheet { padding: 0; box-shadow: none; } }
	</style>
</head>
<body>
	<?php $this->render_body(); ?>
</body>
</html>
