<?php 
/**
 * Form_tagging Page Controller
 * @category  Controller
 */
class Form_taggingController extends SecureController{
	function __construct(){
		parent::__construct();
		$this->tablename = "form_tagging";
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
		$fields = array("id", 
			"machine_id", 
			"area_id", 
			"line_id", 
			"abnormality", 
			"kategori_tag", 
			"korelasi_tag", 
			"kategori_ketidaksesuaian");
		$pagination = $this->get_pagination(MAX_RECORD_COUNT); // get current pagination e.g array(page_number, page_limit)
		//search table record
		if(!empty($request->search)){
			$text = trim($request->search); 
			$search_condition = "(
				form_tagging.id LIKE ? OR 
				form_tagging.machine_id LIKE ? OR 
				form_tagging.area_id LIKE ? OR 
				form_tagging.line_id LIKE ? OR 
				form_tagging.abnormality LIKE ? OR 
				form_tagging.kategori_tag LIKE ? OR 
				form_tagging.korelasi_tag LIKE ? OR 
				form_tagging.kategori_ketidaksesuaian LIKE ?
			)";
			$search_params = array(
				"%$text%","%$text%","%$text%","%$text%","%$text%","%$text%","%$text%","%$text%"
			);
			//setting search conditions
			$db->where($search_condition, $search_params);
			 //template to use when ajax search
			$this->view->search_template = "form_tagging/search.php";
		}
		if(!empty($request->orderby)){
			$orderby = $request->orderby;
			$ordertype = (!empty($request->ordertype) ? $request->ordertype : ORDER_TYPE);
			$db->orderBy($orderby, $ordertype);
		}
		else{
			$db->orderBy("form_tagging.id", ORDER_TYPE);
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
		if($db->getLastError()){
			$this->set_page_error();
		}
		$page_title = $this->view->page_title = "Form Tagging";
		$this->view->report_filename = date('Y-m-d') . '-' . $page_title;
		$this->view->report_title = $page_title;
		$this->view->report_layout = "report_layout.php";
		$this->view->report_paper_size = "A4";
		$this->view->report_orientation = "portrait";
		$this->render_view("form_tagging/list.php", $data); //render the full page
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
		$fields = array("id", 
			"machine_id", 
			"area_id", 
			"line_id", 
			"abnormality", 
			"kategori_tag", 
			"korelasi_tag", 
			"kategori_ketidaksesuaian");
		if($value){
			$db->where($rec_id, urldecode($value)); //select record based on field name
		}
		else{
			$db->where("form_tagging.id", $rec_id); //select record based on primary key
		}
		$record = $db->getOne($tablename, $fields );
		if($record){
			$page_title = $this->view->page_title = "View  Form Tagging";
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
		return $this->render_view("form_tagging/view.php", $record);
	}
	/**
     * Insert new record to the database table
	 * @param $formdata array() from $_POST
     * @return BaseView
     */
	function add($formdata = null){
		if($formdata){
			$db = $this->GetModel();
			$tablename = $this->tablename;
			$request = $this->request;
			//fillable fields
			$fields = $this->fields = array("machine_id","area_id","line_id","abnormality","kategori_tag","korelasi_tag","kategori_ketidaksesuaian");
			$postdata = $this->format_request_data($formdata);
			$this->rules_array = array(
				'machine_id' => 'required|numeric',
				'area_id' => 'required|numeric',
				'line_id' => 'required|numeric',
				'abnormality' => 'required|numeric',
				'kategori_tag' => 'required',
				'korelasi_tag' => 'required',
				'kategori_ketidaksesuaian' => 'required',
			);
			$this->sanitize_array = array(
				'machine_id' => 'sanitize_string',
				'area_id' => 'sanitize_string',
				'line_id' => 'sanitize_string',
				'abnormality' => 'sanitize_string',
				'kategori_tag' => 'sanitize_string',
				'korelasi_tag' => 'sanitize_string',
				'kategori_ketidaksesuaian' => 'sanitize_string',
			);
			$this->filter_vals = true; //set whether to remove empty fields
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			if($this->validated()){
				$rec_id = $this->rec_id = $db->insert($tablename, $modeldata);
				if($rec_id){
					$this->set_flash_msg("Record added successfully", "success");
					return	$this->redirect("form_tagging");
				}
				else{
					$this->set_page_error();
				}
			}
		}
		$page_title = $this->view->page_title = "Add New Form Tagging";
		$this->render_view("form_tagging/add.php");
	}
	/**
     * Update table record with formdata
	 * @param $rec_id (select record by table primary key)
	 * @param $formdata array() from $_POST
     * @return array
     */
	function edit($rec_id = null, $formdata = null){
		$request = $this->request;
		$db = $this->GetModel();
		$this->rec_id = $rec_id;
		$tablename = $this->tablename;
		 //editable fields
		$fields = $this->fields = array("id","machine_id","area_id","line_id","abnormality","kategori_tag","korelasi_tag","kategori_ketidaksesuaian");
		if($formdata){
			$postdata = $this->format_request_data($formdata);
			$this->rules_array = array(
				'machine_id' => 'required|numeric',
				'area_id' => 'required|numeric',
				'line_id' => 'required|numeric',
				'abnormality' => 'required|numeric',
				'kategori_tag' => 'required',
				'korelasi_tag' => 'required',
				'kategori_ketidaksesuaian' => 'required',
			);
			$this->sanitize_array = array(
				'machine_id' => 'sanitize_string',
				'area_id' => 'sanitize_string',
				'line_id' => 'sanitize_string',
				'abnormality' => 'sanitize_string',
				'kategori_tag' => 'sanitize_string',
				'korelasi_tag' => 'sanitize_string',
				'kategori_ketidaksesuaian' => 'sanitize_string',
			);
			$modeldata = $this->modeldata = $this->validate_form($postdata);
			if($this->validated()){
				$db->where("form_tagging.id", $rec_id);
				$bool = $db->update($tablename, $modeldata);
				$numRows = $db->getRowCount(); //number of affected rows. 0 = no record field updated
				if($bool && $numRows){
					$this->set_flash_msg("Record updated successfully", "success");
					return $this->redirect("form_tagging");
				}
				else{
					if($db->getLastError()){
						$this->set_page_error();
					}
					elseif(!$numRows){
						//not an error, but no record was updated
						$page_error = "No record updated";
						$this->set_page_error($page_error);
						$this->set_flash_msg($page_error, "warning");
						return	$this->redirect("form_tagging");
					}
				}
			}
		}
		$db->where("form_tagging.id", $rec_id);
		$data = $db->getOne($tablename, $fields);
		$page_title = $this->view->page_title = "Edit  Form Tagging";
		if(!$data){
			$this->set_page_error();
		}
		return $this->render_view("form_tagging/edit.php", $data);
	}
	/**
     * Delete record from the database
	 * Support multi delete by separating record id by comma.
     * @return BaseView
     */
	function delete($rec_id = null){
		Csrf::cross_check();
		$request = $this->request;
		$db = $this->GetModel();
		$tablename = $this->tablename;
		$this->rec_id = $rec_id;
		//form multiple delete, split record id separated by comma into array
		$arr_rec_id = array_map('trim', explode(",", $rec_id));
		$db->where("form_tagging.id", $arr_rec_id, "in");
		$bool = $db->delete($tablename);
		if($bool){
			$this->set_flash_msg("Record deleted successfully", "success");
		}
		elseif($db->getLastError()){
			$page_error = $db->getLastError();
			$this->set_flash_msg($page_error, "danger");
		}
		return	$this->redirect("form_tagging");
	}
}
