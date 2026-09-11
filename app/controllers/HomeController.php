<?php 

/**
 * Home Page Controller
 * @category  Controller
 */
class HomeController extends SecureController{
	/** Definisi tunggal untuk query dashboard dan ringkasan unit. */
	private function dashboardMachines(){
		return array(
			array('key' => 'cosmec', 'label' => 'Cosmec', 'area' => 'Compounding'),
			array('key' => 'fbd_jaw_chuan', 'label' => 'FBD Jaw Chuan', 'area' => 'Compounding'),
			array('key' => 'fbd_glatt', 'label' => 'FBD Glatt', 'area' => 'Compounding'),
			array('key' => 'supermixer', 'label' => 'Supermixer', 'area' => 'Compounding'),
			array('key' => 'granulator', 'label' => 'Granulator', 'area' => 'Compounding'),
			array('key' => 'storage_tank', 'label' => 'Storage Tank Silverson', 'area' => 'Compounding'),
			array('key' => 'storage_tank_tetrapak', 'label' => 'Storage Tank Tetrapak', 'area' => 'Compounding'),
			array('key' => 'mixing_tank', 'label' => 'Mixing Tank', 'area' => 'Compounding'),
			array('key' => 'sig', 'label' => 'SIG', 'area' => 'Filling'),
			array('key' => 'joeya', 'label' => 'JOYEA', 'area' => 'Filling'),
			array('key' => 'illapak_1_2', 'label' => 'Ilapak 1 - 2', 'area' => 'Filling'),
			array('key' => 'illapak_3_12', 'label' => 'Ilapak 3 - 12', 'area' => 'Filling'),
			array('key' => 'unifill_b', 'label' => 'Unifill', 'area' => 'Filling'),
			array('key' => 'jihcheng', 'label' => 'Jihcheng', 'area' => 'Kemas'),
			array('key' => 'jinsung_1_4', 'label' => 'Jinsung 1 - 4', 'area' => 'Kemas'),
			array('key' => 'jinsung_5', 'label' => 'Jinsung 5', 'area' => 'Kemas'),
			array('key' => 'chimei', 'label' => 'Chimei', 'area' => 'Wrapping & Pack Cartoning'),
			array('key' => 'temach', 'label' => 'Temach', 'area' => 'Wrapping & Pack Cartoning'),
			array('key' => 'best_pack', 'label' => 'Best Pack', 'area' => 'Wrapping & Pack Cartoning'),
			array('key' => 'check_weigher', 'label' => 'Check Weigher', 'area' => 'Wrapping & Pack Cartoning'),
			array('key' => 'conveyor_sig', 'label' => 'Conveyor SIG', 'area' => 'Wrapping & Pack Cartoning'),
		);
	}

	private function operationalDate($at = null){
		$time = $at ? new DateTimeImmutable($at, new DateTimeZone('Asia/Jakarta')) : new DateTimeImmutable('now', new DateTimeZone('Asia/Jakarta'));
		if($time->format('H:i') < '06:45'){
			$time = $time->modify('-1 day');
		}
		return $time->format('Y-m-d');
	}

	private function allowedDashboardMachines(){
		$role = intval(get_active_user('user_role_id'));
		$area = get_active_user('area');
		return array_values(array_filter($this->dashboardMachines(), function($machine) use ($role, $area){
			return ACL::is_machine_allowed($machine['key'], $role, $area);
		}));
	}

	/**
	 * Satu round-trip database untuk seluruh sumber AM yang dapat diakses user.
	 * UNION ALL lebih murah daripada UNION karena form_id hanya unik di tabel asal.
	 */
	private function dashboardFormRows($machines, $startDate, $overdueBefore){
		if(empty($machines)){
			return array();
		}

		$branches = array();
		$params = array();
		foreach($machines as $machine){
			$key = $machine['key'];
			if(!preg_match('/^[a-z0-9_]+$/', $key)){
				throw new RuntimeException('Invalid dashboard machine key.');
			}
			$table = 'tb_mesin_' . $key;
			$issueTable = 'kendala_' . $key;
			$idColumn = 'id_' . $key;
			$area = str_replace("'", "''", $machine['area']);
			$label = str_replace("'", "''", $machine['label']);

			$branches[] = "
				SELECT '{$key}' AS machine_key, '{$area}' AS area, '{$label}' AS module_label,
					f.{$idColumn} AS form_id, f.mesin AS mesin_id,
					COALESCE(NULLIF(m.nama_mesin, ''), '{$label}') AS machine_name,
					COALESCE(f.operational_date, DATE(f.created_at)) AS operational_date,
					f.created_at, f.updated_at, COALESCE(NULLIF(f.shift, ''), '1') AS shift,
					f.approval, COUNT(DISTINCT k.nama_bagian) AS nok_count
				FROM {$table} f
				LEFT JOIN mesin m ON m.id = f.mesin
				LEFT JOIN {$issueTable} k ON k.id_am = f.{$idColumn}
				WHERE COALESCE(f.operational_date, DATE(f.created_at)) >= ?
					OR (f.approval IS NULL AND f.created_at < ?)
				GROUP BY f.{$idColumn}, f.mesin, m.nama_mesin, f.operational_date,
					f.created_at, f.updated_at, f.shift, f.approval";
			$params[] = $startDate;
			$params[] = $overdueBefore;
		}

		return $this->GetModel()->rawQuery(implode("\nUNION ALL\n", $branches), $params);
	}

	private function buildDashboardData($days){
		$timezone = new DateTimeZone('Asia/Jakarta');
		$now = new DateTimeImmutable('now', $timezone);
		$operationalDate = $this->operationalDate($now->format('Y-m-d H:i:s'));
		$today = new DateTimeImmutable($operationalDate, $timezone);
		$start = $today->modify('-' . ($days - 1) . ' days');
		$overdueBefore = $now->modify('-24 hours');
		$machines = $this->allowedDashboardMachines();
		$rows = $this->dashboardFormRows($machines, $start->format('Y-m-d'), $overdueBefore->format('Y-m-d H:i:s'));

		$areaLabels = array('Compounding', 'Filling', 'Kemas', 'Wrapping & Pack Cartoning');
		$trend = array();
		$labels = array();
		$dateKeys = array();
		foreach($areaLabels as $area){
			$trend[$area] = array_fill(0, $days, 0);
		}
		for($i = 0; $i < $days; $i++){
			$date = $start->modify('+' . $i . ' days');
			$dateKeys[$date->format('Y-m-d')] = $i;
			$labels[] = $date->format('d M');
		}

		$todayTotal = 0;
		$todayApproved = 0;
		$urgent = array();
		$todayByMachine = array();
		foreach($machines as $machine){
			$todayByMachine[$machine['key']] = 0;
		}

		foreach($rows as $row){
			$rowDate = substr((string)$row['operational_date'], 0, 10);
			$nokCount = intval($row['nok_count'] ?? 0);
			$approved = (($row['approval'] ?? null) === 'Approved');
			if(isset($dateKeys[$rowDate]) && isset($trend[$row['area']])){
				$trend[$row['area']][$dateKeys[$rowDate]] += $nokCount;
			}
			if($rowDate === $operationalDate){
				$todayTotal++;
				$todayByMachine[$row['machine_key']] = intval($todayByMachine[$row['machine_key']] ?? 0) + 1;
				if($approved){
					$todayApproved++;
				}
			}

			$createdAt = !empty($row['created_at']) ? new DateTimeImmutable($row['created_at'], $timezone) : $now;
			$isPending = empty($row['approval']);
			$isOverdue = $isPending && $createdAt < $overdueBefore;
			if(!$approved && ($nokCount > 0 || $isOverdue)){
				$pendingHours = max(0, intval(floor(($now->getTimestamp() - $createdAt->getTimestamp()) / 3600)));
				$canApprove = ACL::is_allowed($row['machine_key'] . '/edit');
				$row['nok_count'] = $nokCount;
				$row['pending_hours'] = $pendingHours;
				$row['is_overdue'] = $isOverdue;
				$row['action_label'] = $canApprove ? 'Review' : 'Lihat';
				$row['action_path'] = $row['machine_key'] . '/' . ($canApprove ? 'edit' : 'view') . '/' . rawurlencode($row['form_id']);
				$urgent[] = $row;
			}
		}

		usort($urgent, function($a, $b){
			$scoreA = (!empty($a['is_overdue']) ? 2 : 0) + (intval($a['nok_count']) > 0 ? 1 : 0);
			$scoreB = (!empty($b['is_overdue']) ? 2 : 0) + (intval($b['nok_count']) > 0 ? 1 : 0);
			if($scoreA !== $scoreB){
				return $scoreB <=> $scoreA;
			}
			return strcmp((string)$a['created_at'], (string)$b['created_at']);
		});

		$machineGroups = array();
		foreach($areaLabels as $area){
			$machineGroups[$area] = array();
		}
		foreach($machines as $machine){
			$machine['today_count'] = intval($todayByMachine[$machine['key']] ?? 0);
			$machineGroups[$machine['area']][] = $machine;
		}

		return array(
			'days' => $days,
			'operational_date' => $operationalDate,
			'summary' => array(
				'today_total' => $todayTotal,
				'urgent_total' => count($urgent),
				'approved_total' => $todayApproved,
				'approved_percent' => $todayTotal > 0 ? round(($todayApproved / $todayTotal) * 100) : 0,
			),
			'trend' => array('labels' => $labels, 'areas' => $trend),
			'queue' => array_slice($urgent, 0, 7),
			'queue_total' => count($urgent),
			'machine_groups' => $machineGroups,
			'has_restricted_scope' => in_array(intval(get_active_user('user_role_id')), array(4, 5), true),
		);
	}

	/**
     * Index Action
     * @return View
     */
	function index(){
		$days = isset($this->request->days) ? intval($this->request->days) : 7;
		if(!in_array($days, array(7, 30), true)){
			$days = 7;
		}
		$this->view->page_title = 'Dashboard';
		try{
			$data = $this->buildDashboardData($days);
			$data['load_error'] = false;
		}catch(Throwable $e){
			error_log('Dashboard aggregation failed: ' . $e->getMessage());
			$data = array(
				'days' => $days,
				'operational_date' => $this->operationalDate(),
				'summary' => array('today_total' => 0, 'urgent_total' => 0, 'approved_total' => 0, 'approved_percent' => 0),
				'trend' => array('labels' => array(), 'areas' => array()),
				'queue' => array(), 'queue_total' => 0, 'machine_groups' => array(),
				'has_restricted_scope' => in_array(intval(get_active_user('user_role_id')), array(4, 5), true),
				'load_error' => true,
			);
		}
		$this->render_view("home/index.php", $data, "main_layout.php");

	}

	/**
	 * Endpoint ringan buat idle-timer session timeout (URS 1.3) -- dipanggil AJAX
	 * pas user klik "Saya Masih Di Sini", cukup buat bikin request baru yang
	 * lewat SecureController::authenticate_user() sehingga last_activity ke-update.
	 * @return null
	 */
	function ping(){
		render_json(array('ok' => true));
	}
}
