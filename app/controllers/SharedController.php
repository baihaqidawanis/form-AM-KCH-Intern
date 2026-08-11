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
     * toyo_line_mesin_option_list Model Action
     * @return array
     */
	function toyo_line_mesin_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , line_name AS label FROM line WHERE id IN (9) ORDER BY label ASC");
	}

	/**
     * toyo_kategori_tag_option_list Model Action
     * @return array
     */
	function toyo_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , kategori_tag AS label FROM tag ORDER BY label ASC");
	}

	/**
     * toyo_korelasi_tag_option_list Model Action
     * @return array
     */
	function toyo_korelasi_tag_option_list(){
		return $this->_option_list("SELECT  DISTINCT id AS value,nama AS label FROM new_breakdown_management_2.korelasi ORDER BY nama ASC");
	}

	/**
     * toyo_kategori_ketidaksesuaian_option_list Model Action
     * @return array
     */
	function toyo_kategori_ketidaksesuaian_option_list($lookup_korelasi_tag){
		return $this->_option_list("SELECT id AS value, kategori AS label FROM new_breakdown_management_2.kategori WHERE korelasi_id = ? ", array($lookup_korelasi_tag));
	}

	/**
     * pampac_line_option_list Model Action
     * @return array
     */
	function pampac_line_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , line_name AS label FROM line WHERE area_id IN (2) AND id NOT IN (13, 33, 39, 44, 45) ORDER BY label ASC");
	}

	/**
     * pampac_kategori_tag_option_list Model Action
     * @return array
     */
	function pampac_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * jinsung_c_line_option_list Model Action
     * @return array
     */
	function jinsung_c_line_option_list(){
		return $this->_option_list("SELECT  DISTINCT id AS value,line_name AS label FROM line WHERE id = 13");
	}

	/**
     * jinsung_c_kategori_tag_option_list Model Action
     * @return array
     */
	function jinsung_c_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * chimei_line_option_list Model Action
     * @return array
     */
	function chimei_line_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , line_name AS label FROM line WHERE ID IN (2, 8, 26, 29, 31, 33, 39, 36, 38) ORDER BY label ASC");
	}

	/**
     * chimei_kategori_tag_option_list Model Action
     * @return array
     */
	function chimei_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * stv_line_mesin_option_list Model Action
     * @return array
     */
	function stv_line_mesin_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value, line_name AS label 
FROM line 
WHERE area_id = 2 AND id IN (13, 14, 15, 18, 20, 22, 24) 
ORDER BY label ASC;
");
	}

	/**
     * stv_kategori_tag_option_list Model Action
     * @return array
     */
	function stv_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * jih_cheng_line_jihcheng_option_list Model Action
     * @return array
     */
	function jih_cheng_line_jihcheng_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , line_name AS label FROM line WHERE id IN (39) ORDER BY label ASC");
	}

	/**
     * jih_cheng_kategori_tag_option_list Model Action
     * @return array
     */
	function jih_cheng_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , kategori_tag AS label FROM tag ORDER BY label ASC");
	}

	/**
     * pallet_mover_lokasi_pallet_mover_option_list Model Action
     * @return array
     */
	function pallet_mover_lokasi_pallet_mover_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value, area_name AS label 
FROM new_breakdown_management_2.areas WHERE id NOT IN (3)
");
	}

	/**
     * pallet_mover_kategori_tag_option_list Model Action
     * @return array
     */
	function pallet_mover_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * jinsung_n_line_jinsung_option_list Model Action
     * @return array
     */
	function jinsung_n_line_jinsung_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , line_name AS label FROM line WHERE id IN (33) ORDER BY label ASC");
	}

	/**
     * jinsung_n_kategori_tag_option_list Model Action
     * @return array
     */
	function jinsung_n_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , kategori_tag AS label FROM tag ORDER BY label ASC");
	}

	/**
     * mf_line_mf_option_list Model Action
     * @return array
     */
	function mf_line_mf_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , line_name AS label FROM line WHERE area_id = 2 AND id NOT IN (44, 45) ORDER BY label ASC");
	}

	/**
     * mf_kategori_tag_option_list Model Action
     * @return array
     */
	function mf_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * lt3_docking_station_kategori_tag_option_list Model Action
     * @return array
     */
	function lt3_docking_station_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * lt4_k1r1_kategori_tag_option_list Model Action
     * @return array
     */
	function lt4_k1r1_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * lt4_k1r2_kategori_tag_option_list Model Action
     * @return array
     */
	function lt4_k1r2_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * lt4_k1r3_kategori_tag_option_list Model Action
     * @return array
     */
	function lt4_k1r3_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * lt4_k1r4_kategori_tag_option_list Model Action
     * @return array
     */
	function lt4_k1r4_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * lt4_k1r8_kategori_tag_option_list Model Action
     * @return array
     */
	function lt4_k1r8_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * lt4_conveyor_kategori_tag_option_list Model Action
     * @return array
     */
	function lt4_conveyor_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * lt4_milling_kategori_tag_option_list Model Action
     * @return array
     */
	function lt4_milling_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * lt3_k1r1_kategori_tag_option_list Model Action
     * @return array
     */
	function lt3_k1r1_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * lt3_k1r2_kategori_tag_option_list Model Action
     * @return array
     */
	function lt3_k1r2_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * lt3_k1r3_kategori_tag_option_list Model Action
     * @return array
     */
	function lt3_k1r3_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * lt3_k1r4_kategori_tag_option_list Model Action
     * @return array
     */
	function lt3_k1r4_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * lt3_k1r6_kategori_tag_option_list Model Action
     * @return array
     */
	function lt3_k1r6_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * lt3_k1r7_kategori_tag_option_list Model Action
     * @return array
     */
	function lt3_k1r7_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * lt3_k1r8_kategori_tag_option_list Model Action
     * @return array
     */
	function lt3_k1r8_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * lt2_discharge_station_kategori_tag_option_list Model Action
     * @return array
     */
	function lt2_discharge_station_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * lt2_ibc_kategori_tag_option_list Model Action
     * @return array
     */
	function lt2_ibc_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * lt2_ibc_korelasi_tag_option_list Model Action
     * @return array
     */
	function lt2_ibc_korelasi_tag_option_list(){
		return $this->_option_list("SELECT  DISTINCT id AS value,nama AS label FROM new_breakdown_management_2.korelasi ORDER BY nama ASC");
	}

	/**
     * lt2_ibc_kategori_ketidaksesuaian_option_list Model Action
     * @return array
     */
	function lt2_ibc_kategori_ketidaksesuaian_option_list($lookup_korelasi_tag){
		return $this->_option_list("SELECT id AS value, kategori AS label FROM new_breakdown_management_2.kategori WHERE korelasi_id = ? ", array($lookup_korelasi_tag));
	}

	/**
     * lt2_moduwash_kategori_tag_option_list Model Action
     * @return array
     */
	function lt2_moduwash_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * lt2_fbd_no_fbd_option_list Model Action
     * @return array
     */
	function lt2_fbd_no_fbd_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , machine_name AS label FROM new_breakdown_management_2.machine_list WHERE id IN (18, 19) ORDER BY label ASC");
	}

	/**
     * lt2_fbd_kategori_tag_option_list Model Action
     * @return array
     */
	function lt2_fbd_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * lt2_blender_no_blender_option_list Model Action
     * @return array
     */
	function lt2_blender_no_blender_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , machine_name AS label FROM new_breakdown_management_2.machine_list WHERE id IN (6, 10) ORDER BY label ASC");
	}

	/**
     * lt2_blender_kategori_tag_option_list Model Action
     * @return array
     */
	function lt2_blender_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * lt2_track_motion_kategori_tag_option_list Model Action
     * @return array
     */
	function lt2_track_motion_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
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
     * tatung_kategori_tag_option_list Model Action
     * @return array
     */
	function tatung_kategori_tag_option_list(){
		return $this->_option_list("SELECT DISTINCT id AS value , id AS label FROM tag ORDER BY label ASC");
	}

	/**
     * getcount_mf Model Action
     * @return Value
     */
	function getcount_mf(){
		return $this->_count_today("mf", "date_created");
	}

	/**
     * getcount_rvs Model Action
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
     * getcount_best_pack Model Action
     * @return Value
     */
	function getcount_best_pack(){
		return $this->_count_today("best_pack", "created_at");
	}

	/**
     * getcount_toyo Model Action
     * @return Value
     */
	function getcount_toyo(){
		return $this->_count_today("toyo", "date_created");
	}

	/**
     * getcount_stv Model Action
     * @return Value
     */
	function getcount_stv(){
		return $this->_count_today("stv", "date_created");
	}

	/**
     * getcount_pampac Model Action
     * @return Value
     */
	function getcount_pampac(){
		return $this->_count_today("pampac", "date_created");
	}

	/**
     * getcount_jinsungc Model Action
     * @return Value
     */
	function getcount_jinsungc(){
		return $this->_count_today("jinsung_c", "date_created");
	}

	/**
     * getcount_jinsungn Model Action
     * @return Value
     */
	function getcount_jinsungn(){
		return $this->_count_today("jinsung_n", "date_created");
	}

	/**
     * getcount_palletmover Model Action
     * @return Value
     */
	function getcount_palletmover(){
		return $this->_count_today("pallet_mover", "date_created");
	}

	/**
     * getcount_jihcheng Model Action
     * @return Value
     */
	function getcount_jihcheng(){
		return $this->_count_today("jihcheng", "created_at");
	}

	/**
     * getcount_ibc Model Action
     * @return Value
     */
	function getcount_ibc(){
		return $this->_count_today("lt2_ibc", "date_created");
	}

	/**
     * getcount_fbd Model Action
     * @return Value
     */
	function getcount_fbd(){
		return $this->_count_today("lt2_fbd", "date_create");
	}

	/**
     * getcount_blender Model Action
     * @return Value
     */
	function getcount_blender(){
		return $this->_count_today("lt2_blender", "date_created");
	}

	/**
     * getcount_moduwash Model Action
     * @return Value
     */
	function getcount_moduwash(){
		return $this->_count_today("lt2_moduwash", "date_created");
	}

	/**
     * getcount_dischargestation Model Action
     * @return Value
     */
	function getcount_dischargestation(){
		return $this->_count_today("lt2_discharge_station", "date_created");
	}

	/**
     * getcount_agv Model Action
     * @return Value
     */
	function getcount_agv(){
		return $this->_count_today("agv", "date_created");
	}

	/**
     * getcount_l3k1r1p01smp Model Action
     * @return Value
     */
	function getcount_l3k1r1p01smp(){
		return $this->_count_today("lt3_k1r1", "date_created");
	}

	/**
     * getcount_l3k1r2p01camilk Model Action
     * @return Value
     */
	function getcount_l3k1r2p01camilk(){
		return $this->_count_today("lt3_k1r2", "date_created");
	}

	/**
     * getcount_l3k1r3p01sodbic Model Action
     * @return Value
     */
	function getcount_l3k1r3p01sodbic(){
		return $this->_count_today("lt3_k1r3", "date_created");
	}

	/**
     * getcount_l3k1r4p01minor Model Action
     * @return Value
     */
	function getcount_l3k1r4p01minor(){
		return $this->_count_today("lt3_k1r4", "date_created");
	}

	/**
     * getcount_l3k1r6p01sugar Model Action
     * @return Value
     */
	function getcount_l3k1r6p01sugar(){
		return $this->_count_today("lt3_k1r6", "date_created");
	}

	/**
     * getcount_l3k1r7p01citric Model Action
     * @return Value
     */
	function getcount_l3k1r7p01citric(){
		return $this->_count_today("lt3_k1r7", "date_created");
	}

	/**
     * getcount_l3k1r8p01minor Model Action
     * @return Value
     */
	function getcount_l3k1r8p01minor(){
		return $this->_count_today("lt3_k1r8", "date_created");
	}

	/**
     * getcount_l3dockingstation Model Action
     * @return Value
     */
	function getcount_l3dockingstation(){
		return $this->_count_today("lt3_docking_station", "date_created");
	}

	/**
     * getcount_l4k1r1p01smp Model Action
     * @return Value
     */
	function getcount_l4k1r1p01smp(){
		return $this->_count_today("lt4_k1r1", "date_created");
	}

	/**
     * getcount_l4k1r2p01camilk Model Action
     * @return Value
     */
	function getcount_l4k1r2p01camilk(){
		return $this->_count_today("lt4_k1r2", "date_created");
	}

	/**
     * getcount_l4k1r3p01hoppersodbic Model Action
     * @return Value
     */
	function getcount_l4k1r3p01hoppersodbic(){
		return $this->_count_today("lt4_k1r3", "date_created");
	}

	/**
     * getcount_l4k1r4p01minor Model Action
     * @return Value
     */
	function getcount_l4k1r4p01minor(){
		return $this->_count_today("lt4_k1r4", "date_created");
	}

	/**
     * getcount_l4k1r8p01hm1sugar Model Action
     * @return Value
     */
	function getcount_l4k1r8p01hm1sugar(){
		return $this->_count_today("lt4_k1r8", "date_created");
	}

	/**
     * getcount_l4milling Model Action
     * @return Value
     */
	function getcount_l4milling(){
		return $this->_count_today("lt4_milling", "date_created");
	}

	/**
     * getcount_l4conveyor Model Action
     * @return Value
     */
	function getcount_l4conveyor(){
		return $this->_count_today("lt4_conveyor", "date_created");
	}

	/**
     * getcount_tatung_2 Model Action
     * @return Value
     */
	function getcount_tatung_2(){
		return $this->_count_today("tatung", "date_created");
	}

	/**
     * getcount_mesinsealer_2 Model Action
     * @return Value
     */
	function getcount_mesinsealer_2(){
		return $this->_count_today("mesin_sealer", "date_created");
	}

}
