<?php
/*
 +=============================================================================
 | 
 | 마이페이지 마일리지 리스트 정보 취득
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.01.09
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/
include_once("/var/www/www/api/common/common.php");
$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if(isset($_SESSION['MEMBER_IDX'])){
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$list_type = null;
if(isset($_POST['list_type'])){
	$list_type = $_POST['list_type'];
}

$rows = null;
if(isset($_POST['rows'])){
	$rows = $_POST['rows'];
}

$page = null;
if(isset($_POST['page'])){
	$page = $_POST['page'];
}

if ($country == null || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	return $json_result;
}

if ($member_idx > 0 && $country != null) {
	$mileage_cnt = $db->count("MILEAGE_INFO","COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx);
	
	if ($mileage_cnt > 0) {
		$where = " MI.COUNTRY = '".$country."' AND MI.MEMBER_IDX = ".$member_idx." AND MI.DEL_FLG = FALSE ";	
		
		switch($list_type){
			case 'INC':
				$where .= " AND (MI.MILEAGE_UNUSABLE > 0 OR MI.MILEAGE_USABLE_INC > 0) ";
				break;
				
			case 'DEC':
				$where .= " AND (MI.MILEAGE_USABLE_DEC > 0) ";
				break;
		}
		
		$where_cnt = $where;
		
		$json_result = array(
			'total' => $db->count("MILEAGE_INFO MI",$where_cnt),
			'page' => $page
		);
		
		$limit_start = (intval($page)-1)*$rows;
		
		$select_mileage_info_sql = "
			SELECT
				DATE_FORMAT(
					MI.UPDATE_DATE,
					'%Y.%m.%d'
				)						AS UPDATE_DATE,
				IFNULL(
					MI.ORDER_CODE,'-'
				)						AS ORDER_CODE,
				IFNULL(
					OI.PRICE_TOTAL,'0'
				)						AS PRICE_TOTAL,
				MI.MILEAGE_CODE			AS MILEAGE_CODE,
				MC.MILEAGE_TYPE			AS MILEAGE_TYPE,
				MI.MILEAGE_UNUSABLE		AS MILEAGE_UNUSABLE,
				MI.MILEAGE_USABLE_INC	AS MILEAGE_USABLE_INC,
				MI.MILEAGE_USABLE_DEC	AS MILEAGE_USABLE_DEC,
				MI.MILEAGE_BALANCE		AS MILEAGE_BALANCE
			FROM
				MILEAGE_INFO MI
				LEFT JOIN MILEAGE_CODE MC ON
				MI.MILEAGE_CODE = MC.MILEAGE_CODE
				LEFT JOIN ORDER_INFO OI ON
				MI.ORDER_CODE = OI.ORDER_CODE
			WHERE
				".$where."
			ORDER BY
				MI.IDX DESC
			LIMIT
				".$limit_start.",".$rows;

		$db->query($select_mileage_info_sql);

		foreach($db->fetch() as $data){
			$json_result['data'][] = array(
				'update_date'			=>$data['UPDATE_DATE'],
				'order_code'			=>$data['ORDER_CODE'],
				'price_total'			=>number_format($data['PRICE_TOTAL']),
				
				'mileage_code'			=>$data['MILEAGE_CODE'],
				'mileage_type'			=>$data['MILEAGE_TYPE'],
				'mileage_unu'			=>$data['MILEAGE_UNUSABLE'],
				'txt_mileage_unu'		=>number_format($data['MILEAGE_UNUSABLE']),
				'mileage_inc'			=>number_format($data['MILEAGE_USABLE_INC']),
				'mileage_dec'			=>number_format($data['MILEAGE_USABLE_DEC']),
				'mileage_sum'			=>number_format($data['MILEAGE_BALANCE'])
			);
		}
	} else {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_WRN_0005', array());
		
		return $json_result;
	}
}

?>