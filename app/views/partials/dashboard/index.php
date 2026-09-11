<?php
$data = is_array($this->view_data) ? $this->view_data : array();
$days = in_array(intval($data['days'] ?? 7), array(7, 30), true) ? intval($data['days']) : 7;
$summary = array_merge(array(
    'today_total' => 0,
    'urgent_total' => 0,
    'approved_total' => 0,
    'approved_percent' => 0,
), is_array($data['summary'] ?? null) ? $data['summary'] : array());
$trend = is_array($data['trend'] ?? null) ? $data['trend'] : array('labels' => array(), 'areas' => array());
$queue = is_array($data['queue'] ?? null) ? $data['queue'] : array();
$machine_groups = is_array($data['machine_groups'] ?? null) ? $data['machine_groups'] : array();
$load_error = !empty($data['load_error']);
$operational_date = (string)($data['operational_date'] ?? date('Y-m-d'));
$operational_timestamp = strtotime($operational_date);
$operational_label = $operational_timestamp ? date('d M Y', $operational_timestamp) : $operational_date;
$escape = function($value){
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};

$area_colors = array(
    'Compounding' => '#009639',
    'Filling' => '#86BD40',
    'Kemas' => '#FF9500',
    'Wrapping & Pack Cartoning' => '#0284C7',
);
$visible_groups = array_filter($machine_groups, function($machines){
    return is_array($machines) && !empty($machines);
});
?>

<style>
.am-dashboard { color: var(--ak-text, #1D1D1F); padding: 10px 0 30px; }
.am-dashboard-header { gap: 18px; margin-bottom: 20px; }
.am-dashboard-title { font-size: 1.65rem; font-weight: 650; letter-spacing: -.035em; margin: 0 0 4px; }
.am-dashboard-subtitle { color: var(--ak-muted, #6E6E73); font-size: .9rem; margin: 0; }
.am-period-toggle { background: rgba(118,118,128,.12); border-radius: 9px; display: inline-flex; padding: 3px; }
.am-period-toggle .btn { border: 0 !important; border-radius: 7px !important; box-shadow: none !important; color: var(--ak-muted, #6E6E73) !important; font-size: .82rem; font-weight: 600; padding: 6px 13px !important; transform: none !important; }
.am-period-toggle .btn.active { background: #FFF !important; color: var(--ak-green, #009639) !important; box-shadow: 0 1px 4px rgba(0,0,0,.09) !important; }
.am-dashboard-card { height: 100%; overflow: hidden; }
.am-kpi-card .card-body { min-height: 132px; padding: 20px; }
.am-kpi-label { color: var(--ak-muted, #6E6E73); font-size: .78rem; font-weight: 650; letter-spacing: .045em; margin-bottom: 11px; text-transform: uppercase; }
.am-kpi-row { align-items: center; display: flex; justify-content: space-between; gap: 8px; }
.am-kpi-value { font-size: 2rem; font-weight: 650; letter-spacing: -.045em; line-height: 1.1; }
.am-kpi-meta { color: var(--ak-muted, #6E6E73); font-size: .82rem; margin-top: 10px; }
.am-kpi-accent { border-top: 3px solid var(--ak-green, #009639); }
.am-kpi-alert { border-top: 3px solid #FF3B30; }
.am-kpi-approved { border-top: 3px solid var(--ak-green-accent, #86BD40); }
.am-alert-count { background: #FEECEB; border: 1px solid #F9CCC8; border-radius: 999px; color: #D92D20; display: inline-flex; align-items: center; font-size: .72rem; font-weight: 700; line-height: 1.2; padding: 4px 9px; white-space: nowrap; }
.am-card-heading { align-items: flex-start; display: flex; justify-content: space-between; padding: 18px 20px 0; }
.am-card-title { font-size: 1rem; font-weight: 650; letter-spacing: -.018em; margin: 0; }
.am-card-note { color: var(--ak-muted, #6E6E73); font-size: .78rem; margin: 4px 0 0; }
.am-chart-wrap { height: 330px; padding: 18px 18px 16px; position: relative; }
.am-queue { list-style: none; margin: 0; padding: 10px 18px 16px; }
.am-queue-item { align-items: center; border-bottom: 1px solid var(--ak-border, rgba(0,0,0,.07)); display: flex; gap: 12px; padding: 13px 0; }
.am-queue-item:last-child { border-bottom: 0; }
.am-queue-main { min-width: 0; flex: 1; }
.am-queue-machine { font-size: .9rem; font-weight: 650; margin: 0 0 4px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.am-queue-meta { color: var(--ak-muted, #6E6E73); font-size: .76rem; }
.am-queue-badges { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 7px; }
.am-queue-badge { border-radius: 999px; display: inline-flex; font-size: .68rem; font-weight: 700; padding: 3px 7px; }
.am-queue-badge--nok { background: #FEECEB; color: #D92D20; }
.am-queue-badge--pending { background: #FFF6E5; color: #A75E00; }
.am-queue-action { flex: 0 0 auto; font-size: .75rem !important; padding: 6px 10px !important; }
.am-empty-state { color: var(--ak-green, #009639); padding: 50px 24px; text-align: center; }
.am-empty-state .fa { display: block; font-size: 1.5rem; margin-bottom: 10px; }
.am-unit-card .card-header { padding: 0; }
.am-unit-toggle { align-items: center; background: transparent !important; border: 0 !important; box-shadow: none !important; color: var(--ak-text, #1D1D1F) !important; display: flex; justify-content: space-between; padding: 18px 20px !important; text-align: left; transform: none !important; width: 100%; }
.am-unit-toggle .fa { color: var(--ak-muted, #6E6E73); transition: transform .18s ease; }
.am-unit-toggle[aria-expanded="true"] .fa { transform: rotate(180deg); }
.am-unit-tabs { border-bottom: 1px solid var(--ak-border, rgba(0,0,0,.07)); gap: 6px; margin: 12px 18px 14px; padding: 0 0 12px; }
.am-unit-tabs .nav-link { font-size: .78rem; padding: 7px 12px; }
.am-machine-grid { display: grid; gap: 10px; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); padding: 18px; }
.am-machine-link { align-items: center; background: #FAFAFB; border: 1px solid var(--ak-border, rgba(0,0,0,.07)); border-radius: 10px; color: var(--ak-text, #1D1D1F); display: flex; justify-content: space-between; min-height: 52px; padding: 10px 12px; transition: border-color .16s ease, background-color .16s ease, transform .16s ease; }
.am-machine-link:hover { background: var(--ak-mint, #F0F8EC); border-color: var(--ak-mint-border, #D1EBB8); color: var(--ak-green, #009639); text-decoration: none; transform: translateY(-1px); }
.am-machine-name { font-size: .83rem; font-weight: 650; padding-right: 8px; }
.am-machine-count { background: #FFF; border: 1px solid var(--ak-border, rgba(0,0,0,.07)); border-radius: 999px; color: var(--ak-green, #009639); flex: 0 0 auto; font-size: .72rem; font-weight: 700; min-width: 27px; padding: 3px 7px; text-align: center; }
@media (max-width: 991.98px) { .am-chart-wrap { height: 290px; } .am-dashboard-card { height: auto; } }
@media (max-width: 575.98px) { .am-dashboard-title { font-size: 1.4rem; } .am-dashboard-header { align-items: flex-start !important; flex-direction: column; } .am-period-toggle { width: 100%; } .am-period-toggle .btn { flex: 1; } .am-chart-wrap { height: 260px; padding-left: 10px; padding-right: 10px; } .am-machine-grid { grid-template-columns: 1fr; } }
</style>

<section class="am-dashboard">
    <div class="container-fluid">
        <header class="am-dashboard-header d-flex align-items-center justify-content-between">
            <div>
                <h1 class="am-dashboard-title">Dashboard AM</h1>
                <p class="am-dashboard-subtitle">
                    Tanggal operasional <?php echo $escape($operational_label); ?>
                    <?php if(!empty($data['has_restricted_scope'])){ ?> &middot; Sesuai area penugasan Anda<?php } ?>
                </p>
            </div>
            <div class="am-period-toggle" role="group" aria-label="Periode tren temuan NOK">
                <a class="btn <?php echo $days === 7 ? 'active' : ''; ?>" href="<?php print_link('dashboard?days=7'); ?>" aria-pressed="<?php echo $days === 7 ? 'true' : 'false'; ?>">7 Hari</a>
                <a class="btn <?php echo $days === 30 ? 'active' : ''; ?>" href="<?php print_link('dashboard?days=30'); ?>" aria-pressed="<?php echo $days === 30 ? 'true' : 'false'; ?>">30 Hari</a>
            </div>
        </header>

        <?php if($load_error){ ?>
            <div class="alert alert-warning" role="alert"><i class="fa fa-exclamation-triangle mr-2"></i>Data monitoring belum dapat dimuat. Silakan muat ulang halaman atau hubungi administrator.</div>
        <?php } ?>

        <div class="row mb-4">
            <div class="col-md-4 mb-3 mb-md-0">
                <article class="card am-dashboard-card am-kpi-card am-kpi-accent"><div class="card-body">
                    <div class="am-kpi-label">Inspeksi Hari Ini <i class="fa fa-info-circle text-muted ml-1" data-toggle="tooltip" data-trigger="hover" title="Dihitung per sesi shift pengisian (Shift 1, 2, 3), bukan per unit mesin. Pada Check Sheet Periode, seluruh shift otomatis digabung menjadi 1 formulir utuh."></i></div>
                    <div class="am-kpi-value"><?php echo intval($summary['today_total']); ?></div>
                    <div class="am-kpi-meta">Sesi shift AM terisi pada tanggal operasional ini</div>
                </div></article>
            </div>
            <div class="col-md-4 mb-3 mb-md-0">
                <article class="card am-dashboard-card am-kpi-card am-kpi-alert"><div class="card-body">
                    <div class="am-kpi-label">Antrean Temuan NOK &amp; Urgent</div>
                    <div class="am-kpi-row">
                        <div class="am-kpi-value"><?php echo intval($summary['urgent_total']); ?></div>
                        <?php if(intval($summary['urgent_total']) > 0){ ?><span class="am-alert-count">Perlu tindakan</span><?php } ?>
                    </div>
                    <div class="am-kpi-meta">Sesi shift dengan temuan NOK atau butuh review</div>
                </div></article>
            </div>
            <div class="col-md-4">
                <article class="card am-dashboard-card am-kpi-card am-kpi-approved"><div class="card-body">
                    <div class="am-kpi-label">Fully Approved Hari Ini <i class="fa fa-info-circle text-muted ml-1" data-toggle="tooltip" data-trigger="hover" title="Rasio persetujuan dihitung dari total sesi shift yang masuk pada hari operasional ini."></i></div>
                    <div class="am-kpi-value"><?php echo intval($summary['approved_total']); ?> <small class="text-muted" style="font-size:1rem;font-weight:600">/ <?php echo intval($summary['today_total']); ?></small></div>
                    <div class="am-kpi-meta"><?php echo intval($summary['approved_percent']); ?>% sesi shift hari ini berstatus Approved</div>
                </div></article>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-lg-8 mb-3 mb-lg-0">
                <article class="card am-dashboard-card">
                    <div class="am-card-heading"><div>
                        <h2 class="am-card-title">Tren Temuan Part NOK per Area</h2>
                        <p class="am-card-note"><?php echo $days; ?> hari operasional terakhir &middot; jumlah part berstatus NOK</p>
                    </div></div>
                    <div class="am-chart-wrap"><canvas id="amNokTrendChart" role="img" aria-label="Grafik tren temuan part NOK per area">Grafik tren temuan NOK tidak didukung oleh browser ini.</canvas></div>
                </article>
            </div>
            <div class="col-lg-4">
                <article class="card am-dashboard-card">
                    <div class="am-card-heading">
                        <div><h2 class="am-card-title">Daftar Temuan NOK &amp; Butuh Review</h2><p class="am-card-note">Prioritas tindakan terbaru</p></div>
                        <?php if(intval($summary['urgent_total']) > 0){ ?><span class="am-alert-count"><?php echo intval($summary['urgent_total']); ?></span><?php } ?>
                    </div>
                    <?php if(!empty($queue)){ ?>
                        <ul class="am-queue">
                            <?php foreach($queue as $item){
                                $item_date = !empty($item['operational_date']) ? substr((string)$item['operational_date'], 0, 10) : substr((string)($item['created_at'] ?? ''), 0, 10);
                                $item_timestamp = strtotime($item_date);
                                $item_date_label = $item_timestamp ? date('d M Y', $item_timestamp) : $item_date;
                                $shift = trim((string)($item['shift'] ?? ''));
                                $nok_count = intval($item['nok_count'] ?? 0);
                            ?>
                                <li class="am-queue-item">
                                    <div class="am-queue-main">
                                        <p class="am-queue-machine"><?php echo $escape($item['machine_name'] ?? $item['module_label'] ?? '-'); ?></p>
                                        <div class="am-queue-meta"><?php echo $escape($item_date_label); ?><?php if($shift !== ''){ ?> &middot; Shift <?php echo $escape($shift); ?><?php } ?></div>
                                        <div class="am-queue-badges">
                                            <?php if($nok_count > 0){ ?><span class="am-queue-badge am-queue-badge--nok"><?php echo $nok_count; ?> Part NOK</span><?php } ?>
                                            <?php if(empty($item['approval'])){ ?><span class="am-queue-badge am-queue-badge--pending">Pending</span><?php } ?>
                                        </div>
                                    </div>
                                    <?php if(!empty($item['action_path'])){ ?><a class="btn btn-sm btn-primary am-queue-action" href="<?php print_link($item['action_path']); ?>"><?php echo $escape($item['action_label'] ?? 'Lihat'); ?></a><?php } ?>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php } elseif($load_error){ ?>
                        <div class="am-empty-state text-muted"><i class="fa fa-database"></i>Antrean belum dapat dimuat.</div>
                    <?php } else { ?>
                        <div class="am-empty-state"><i class="fa fa-check-circle"></i>Semua form bersih dan telah ditinjau</div>
                    <?php } ?>
                </article>
            </div>
        </div>

        <article class="card am-unit-card">
            <div class="card-header">
                <button class="am-unit-toggle" type="button" data-toggle="collapse" data-target="#amUnitSummary" aria-expanded="true" aria-controls="amUnitSummary">
                    <span><strong>Ringkasan Unit Mesin</strong><small class="d-block text-muted mt-1">Counter form AM hari ini per modul mesin</small></span>
                    <i class="fa fa-chevron-up" aria-hidden="true"></i>
                </button>
            </div>
            <div id="amUnitSummary" class="collapse show">
                <?php if(!empty($visible_groups)){ ?>
                    <ul class="nav nav-pills am-unit-tabs" role="tablist">
                        <?php $area_index = 0; foreach($visible_groups as $area => $machines){ $area_index++; ?>
                            <li class="nav-item"><a class="nav-link <?php echo $area_index === 1 ? 'active' : ''; ?>" id="am-area-tab-<?php echo $area_index; ?>" data-toggle="pill" href="#am-area-<?php echo $area_index; ?>" role="tab" aria-controls="am-area-<?php echo $area_index; ?>" aria-selected="<?php echo $area_index === 1 ? 'true' : 'false'; ?>"><?php echo $escape($area); ?></a></li>
                        <?php } ?>
                    </ul>
                    <div class="tab-content">
                        <?php $area_index = 0; foreach($visible_groups as $area => $machines){ $area_index++; ?>
                            <div class="tab-pane fade <?php echo $area_index === 1 ? 'show active' : ''; ?>" id="am-area-<?php echo $area_index; ?>" role="tabpanel" aria-labelledby="am-area-tab-<?php echo $area_index; ?>">
                                <div class="am-machine-grid">
                                    <?php foreach($machines as $machine){ ?>
                                        <a class="am-machine-link" href="<?php print_link(($machine['key'] ?? '') . '/'); ?>">
                                            <span class="am-machine-name"><?php echo $escape($machine['label'] ?? $machine['key'] ?? '-'); ?></span>
                                            <span class="am-machine-count" title="Form hari ini"><?php echo intval($machine['today_count'] ?? 0); ?></span>
                                        </a>
                                    <?php } ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>
                <?php } elseif($load_error){ ?>
                    <div class="p-4 text-muted">Ringkasan unit belum dapat dimuat.</div>
                <?php } else { ?>
                    <div class="p-4 text-muted">Tidak ada unit mesin yang tersedia untuk area penugasan ini.</div>
                <?php } ?>
            </div>
        </article>
    </div>
</section>

<?php Html::page_js('chartjs-2.3.0.js'); ?>
<script>
(function(){
    var canvas = document.getElementById('amNokTrendChart');
    if (!canvas || typeof Chart === 'undefined') { return; }
    var labels = <?php echo json_encode(array_values($trend['labels'] ?? array()), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    var areaData = <?php echo json_encode($trend['areas'] ?? array(), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    var colors = <?php echo json_encode($area_colors, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    var areaOrder = ['Compounding', 'Filling', 'Kemas', 'Wrapping & Pack Cartoning'];
    var datasets = [];
    areaOrder.forEach(function(area){
        if (!Object.prototype.hasOwnProperty.call(areaData, area)) { return; }
        datasets.push({ label: area, data: areaData[area], backgroundColor: colors[area], borderColor: colors[area], borderWidth: 0, hoverBackgroundColor: colors[area] });
    });
    var maxDataVal = 0;
    datasets.forEach(function(ds){
        if (Array.isArray(ds.data)) {
            ds.data.forEach(function(val){
                var num = Number(val) || 0;
                if (num > maxDataVal) { maxDataVal = num; }
            });
        }
    });
    var dynamicCeiling = 1;
    if (maxDataVal > 0) {
        dynamicCeiling = maxDataVal <= 3 ? (maxDataVal + 1) : Math.ceil(maxDataVal * 1.18);
    }
    new Chart(canvas.getContext('2d'), {
        type: 'bar',
        data: { labels: labels, datasets: datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            animation: { duration: 450 },
            legend: { position: 'bottom', labels: { boxWidth: 10, fontColor: '#6E6E73', fontSize: 11, padding: 16 } },
            tooltips: {
                mode: 'index', intersect: false,
                callbacks: { label: function(item, chartData){ var set = chartData.datasets[item.datasetIndex]; return ' ' + set.label + ': ' + item.yLabel + ' temuan NOK'; } }
            },
            scales: {
                xAxes: [{ stacked: false, gridLines: { display: false }, ticks: { fontColor: '#6E6E73', fontSize: 10, maxRotation: <?php echo $days === 30 ? 45 : 0; ?>, minRotation: 0 } }],
                yAxes: [{
                    ticks: {
                        beginAtZero: true,
                        stepSize: 1,
                        min: 0,
                        suggestedMax: dynamicCeiling,
                        fontColor: '#6E6E73',
                        userCallback: function(value){
                            if(Math.floor(value) === value){
                                return value;
                            }
                        }
                    },
                    gridLines: { color: 'rgba(0,0,0,0.05)', drawBorder: false }
                }]
            }
        }
    });
    if (window.jQuery && typeof jQuery.fn.tooltip === 'function') {
        jQuery(function($){
            $('[data-toggle="tooltip"]').tooltip({
                trigger: 'hover',
                placement: 'bottom'
            });
            $(document).on('click', '[data-toggle="tooltip"]', function(){
                $(this).tooltip('hide');
            });
        });
    }
})();
</script>
