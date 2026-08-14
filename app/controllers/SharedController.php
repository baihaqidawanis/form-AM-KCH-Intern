<?php 

/**
 * SharedController Controller
 * @category  Controller / Model
 */
class SharedController extends BaseController{

	/**
     * Run a raw SELECT and return rows shaped for dropdown options (value/label)
     * @return array
     */
	private function _option_list($sql, $params = null){
		$db = $this->GetModel();
		return $db->rawQuery($sql, $params);
	}

	/**
     * Check whether a value already exists in a given table/column
     * @return bool
     */
	private function _value_exists($table, $column, $val){
		$db = $this->GetModel();
		$db->where($column, $val);
		return $db->has($table);
	}

	/**
     * Count today's records for a given table/date-column
     * @return int
     */
	private function _count_today($table, $dateColumn){
		$db = $this->GetModel();
		$sql = "SELECT COUNT(*) AS num FROM {$table} WHERE DATE({$dateColumn}) = DATE(NOW())";
		$val = $db->rawQueryValue($sql, null);
		if(is_array($val)){
			return $val[0];
		}
		return $val;
	}

	/**
     * rvs_Line_option_list Model Action
     * @return array
     */
	function sig_Line_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value, nama_mesin AS label FROM mesin ORDER BY label ASC;");
	}

	/**
     * rvs_kategori_tag_option_list Model Action
     * @return array
     */
	function sig_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , kategori_tag AS label FROM tag ORDER BY id ASC");
	}

	/**
     * rvs_korelasi_tag_option_list Model Action
     * @return array
     */
	function sig_korelasi_tag_option_list(){
		return $this->_option_list("SELECT  DISTINCT id AS value,nama AS label FROM korelasi ORDER BY id ASC");
	}

	/**
     * rvs_korelasi_tag_option_list Model Action
     * @return array
     */
	function sig_klasifikasi_tag_option_list(){
		return $this->_option_list("SELECT  DISTINCT id AS value,nama AS label FROM klasifikasi ORDER BY id ASC");
	}

	/**
     * rvs_kategori_ketidaksesuaian_option_list Model Action
     * @return array
     */
	function sig_kategori_ketidaksesuaian_option_list($lookup_korelasi_tag){
		return $this->_option_list("SELECT id AS value, kategori AS label FROM kategori WHERE korelasi_id = ? ", array($lookup_korelasi_tag));
	}

	/**
     * getcount_sig Model Action
     * @return Value
     */
	function getcount_sig(){
		return $this->_count_today("sig", "created_at");
	}

	/**
     * getcount_joeya Model Action
     * @return Value
     */
	function getcount_joeya(){
		return $this->_count_today("joeya", "created_at");
	}

	/**
     * getcount_illapak_1_2 Model Action
     * @return Value
     */
	function getcount_illapak_1_2(){
		return $this->_count_today("illapak_1_2", "created_at");
	}

	/**
     * getcount_illapak_3_12 Model Action
     * @return Value
     */
	function getcount_illapak_3_12(){
		return $this->_count_today("illapak_3_12", "created_at");
	}

	/**
     * getcount_unifill_b Model Action
     * @return Value
     */
	function getcount_unifill_b(){
		return $this->_count_today("unifill_b", "created_at");
	}

	/**
     * getcount_chimei Model Action
     * @return Value
     */
	function getcount_chimei(){
		return $this->_count_today("chimei", "created_at");
	}

	/**
     * getcount_temach Model Action
     * @return Value
     */
	function getcount_temach(){
		return $this->_count_today("temach", "created_at");
	}

	/**
     * getcount_jinsung_1_4 Model Action
     * @return Value
     */
	function getcount_jinsung_1_4(){
		return $this->_count_today("jinsung_1_4", "created_at");
	}

	/**
     * getcount_jinsung_5 Model Action
     * @return Value
     */
	function getcount_jinsung_5(){
		return $this->_count_today("jinsung_5", "created_at");
	}

	/**
     * getcount_cosmec Model Action
     * @return Value
     */
	function getcount_cosmec(){
		return $this->_count_today("cosmec", "created_at");
	}

	/**
     * getcount_best_pack Model Action
     * @return Value
     */
	function getcount_best_pack(){
		return $this->_count_today("best_pack", "created_at");
	}

	/**
     * users_user_role_id_option_list Model Action
     * @return array
     */
	function users_user_role_id_option_list(){
		return $this->_option_list("SELECT role_id AS value, role_name AS label FROM roles");
	}

	/**
     * users_email_value_exist Model Action
     * @return array
     */
	function users_email_value_exist($val){
		return $this->_value_exists("users", "email", $val);
	}

	/**
     * users_username_value_exist Model Action
     * @return array
     */
	function users_username_value_exist($val){
		return $this->_value_exists("users", "username", $val);
	}


	/**
     * getcount_jihcheng Model Action
     * @return Value
     */
	function getcount_jihcheng(){
		return $this->_count_today("jihcheng", "created_at");
	}

	/**
     * getcount_fbd_jaw_chuan Model Action
     * @return Value
     */
	function getcount_fbd_jaw_chuan(){
		return $this->_count_today("fbd_jaw_chuan", "created_at");
	}

	/**
     * getcount_fbd_glatt Model Action
     * @return Value
     */
	function getcount_fbd_glatt(){
		return $this->_count_today("fbd_glatt", "created_at");
	}

	/**
     * getcount_supermixer Model Action
     * @return Value
     */
	function getcount_supermixer(){
		return $this->_count_today("supermixer", "created_at");
	}

	/**
     * getcount_storage_tank Model Action
     * @return Value
     */
	function getcount_storage_tank(){
		return $this->_count_today("storage_tank", "created_at");
	}

	/**
     * getcount_mixing_tank Model Action
     * @return Value
     */
	function getcount_mixing_tank(){
		return $this->_count_today("mixing_tank", "created_at");
	}

}





