<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<title><?php echo $this->report_title; ?></title>
	<style>
		@page {
			size: A4 landscape;
			margin: 5mm;
		}
		html, body {
			margin: 0;
			padding: 0;
			font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;
			background: #fff;
			-webkit-print-color-adjust: exact;
			print-color-adjust: exact;
		}
		#report-body {
			margin: 0;
			padding: 0;
		}
	</style>
</head>
<body>
	<div id="report-body">
		<?php $this->render_body(); ?>
	</div>
	<?php if ($this->force_print) { ?>
		<script>
			window.print();
		</script>
	<?php } ?>
</body>
</html>
