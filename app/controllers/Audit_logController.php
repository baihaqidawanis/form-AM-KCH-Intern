<?php 
/**
 * Audit_log Page Controller
 * @category  Controller
 */
class Audit_logController extends SecureController{
	function __construct(){
		parent::__construct();
		$this->tablename = "audit_log";
	}
	/**
     * List page records
     * @param $fieldname (filter record by a field) 
     * @param $fieldvalue (filter field value)
     * @return BaseView
     */
	function index($fieldname = null , $fieldvalue = null){
		$request = $this->request;
		$db = $this->GetModel();
		$tablename = $this->tablename;
		$fields = array("log_id",
			'"Timestamp"',
			'"Action"',
			'"TableName"',
			'"UserID"');
		$pagination = $this->get_pagination(MAX_RECORD_COUNT); // get current pagination e.g array(page_number, page_limit)
		//search table record
		if(!empty($request->search)){
			$text = trim($request->search);
			// Kolom audit_log huruf besar (peninggalan MySQL yang gak peduli besar-kecil huruf kolom) --
			// wajib di-quote di Postgres, kalau enggak otomatis di-lowercase-in dan gagal match kolom asli.
			$search_condition = '(
				CAST(audit_log.log_id AS VARCHAR) LIKE ? OR
				audit_log."Timestamp" LIKE ? OR
				audit_log.id_log LIKE ? OR
				audit_log."Action" LIKE ? OR
				audit_log."TableName" LIKE ? OR
				audit_log."UserID" LIKE ? OR
				audit_log."SQLQuery" LIKE ? OR
				audit_log."ServerIP" LIKE ? OR
				audit_log."RequestURL" LIKE ? OR
				audit_log."RequestData" LIKE ? OR
				audit_log."RequestCompleted" LIKE ? OR
				audit_log."RequestMsg" LIKE ?
			)';
			$search_params = array(
				"%$text%","%$text%","%$text%","%$text%","%$text%","%$text%","%$text%","%$text%","%$text%","%$text%","%$text%","%$text%"
			);
			//setting search conditions
			$db->where($search_condition, $search_params);
			 //template to use when ajax search
			$this->view->search_template = "audit_log/search.php";
		}
		if(!empty($request->action_filter)){
			$db->where('audit_log."Action"', trim($request->action_filter));
		}
		if(!empty($request->table_filter)){
			$db->where('audit_log."TableName"', trim($request->table_filter));
		}
		if(!empty($request->date_from)){
			$db->where('audit_log."Timestamp"', trim($request->date_from) . " 00:00:00", ">=");
		}
		if(!empty($request->date_to)){
			$db->where('audit_log."Timestamp"', trim($request->date_to) . " 23:59:59", "<=");
		}
		if(!empty($request->orderby)){
			$orderby = $request->orderby;
			$ordertype = (!empty($request->ordertype) ? $request->ordertype : ORDER_TYPE);
			$db->orderBy($orderby, $ordertype);
		}
		else{
			$db->orderBy("audit_log.log_id", ORDER_TYPE);
		}
		if($fieldname){
			$db->where($fieldname , $fieldvalue); //filter by a single field name
		}
		$tc = $db->withTotalCount();
		$records = $db->get($tablename, $pagination, $fields);
		$records_count = count($records);
		$total_records = intval($tc->totalCount);
		$page_limit = $pagination[1];
		$total_pages = ceil($total_records / $page_limit);
		$data = new stdClass;
		$data->records = $records;
		$data->record_count = $records_count;
		$data->total_records = $total_records;
		$data->total_page = $total_pages;
		$table_rows = $db->rawQuery('SELECT DISTINCT "TableName" FROM audit_log WHERE "TableName" IS NOT NULL AND "TableName" <> \'\' ORDER BY "TableName"');
		$data->table_options = array_column($table_rows, 'TableName');
		if($db->getLastError()){
			$this->set_page_error();
		}
		$page_title = $this->view->page_title = "Audit Log";
		$this->view->report_filename = date('Y-m-d') . '-' . $page_title;
		$this->view->report_title = $page_title;
		$this->view->report_layout = "report_layout.php";
		$this->view->report_paper_size = "A4";
		$this->view->report_orientation = "portrait";
		$this->render_view("audit_log/list.php", $data); //render the full page
	}
	/**
     * View record detail 
	 * @param $rec_id (select record by table primary key) 
     * @param $value value (select record by value of field name(rec_id))
     * @return BaseView
     */
	function view($rec_id = null, $value = null){
		$request = $this->request;
		$db = $this->GetModel();
		$rec_id = $this->rec_id = urldecode($rec_id);
		$tablename = $this->tablename;
		$fields = array("log_id",
			'"Timestamp"',
			"id_log",
			'"Action"',
			'"TableName"',
			'"UserID"',
			'"SQLQuery"',
			'"ServerIP"',
			'"RequestURL"',
			'"RequestData"',
			'"RequestCompleted"',
			'"RequestMsg"');
		if($value){
			$db->where($rec_id, urldecode($value)); //select record based on field name
		}
		else{
			$db->where("audit_log.log_id", $rec_id); //select record based on primary key
		}
		$record = $db->getOne($tablename, $fields );
		if($record){
			$page_title = $this->view->page_title = "View  Audit Log";
		$this->view->report_filename = date('Y-m-d') . '-' . $page_title;
		$this->view->report_title = $page_title;
		$this->view->report_layout = "report_layout.php";
		$this->view->report_paper_size = "A4";
		$this->view->report_orientation = "portrait";
		}
		else{
			if($db->getLastError()){
				$this->set_page_error();
			}
			else{
				$this->set_page_error("No record found");
			}
		}
		return $this->render_view("audit_log/view.php", $record);
	}
}
