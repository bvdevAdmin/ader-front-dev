<?php
/*
 +=============================================================================
 | 
 | 마이페이지 바우처 목록 
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

include_once("/var/www/www/api/common.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if (!isset($country) || $member_idx == 0) {
    $json_result['code'] = 401;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
} 

if(isset($country) && $member_idx > 0 && isset($list_type)){
	$where = '';
	$where .= "
		VI.MEMBER_IDX = ".$member_idx." AND
		VI.COUNTRY = '".$country."'
	";
	
	if ($list_type == 'possession') {
		$where .= "
			AND (
				VI.USED_FLG = FALSE AND
				VI.USABLE_END_DATE > NOW()
			)
		";
	} else if ($list_type == 'use') {
		$where .= "
			AND (
				VI.USED_FLG = TRUE OR
				VI.USABLE_END_DATE < NOW()
			)
		";
	} else {
		$where .= " AND 1 = 0 ";
	}

	$select_voucher_issue_sql = "
		SELECT
			VI.VOUCHER_ISSUE_CODE	VOUCHER_ISSUE_CODE,
			CASE
				WHEN
					VM.SALE_TYPE = 'PRC'
					THEN
						CONCAT(VM.SALE_PRICE,'원 OFF')
				WHEN
					VM.SALE_TYPE = 'PER'
					THEN
						CONCAT(VM.SALE_PRICE,'% OFF')
			END						AS SALE_PRICE_TYPE,
			VM.MIN_PRICE			AS MIN_PRICE,
			VM.VOUCHER_NAME			AS VOUCHER_NAME,
			VI.USED_FLG				AS USED_FLG,
			DATE_FORMAT(
				VI.USABLE_START_DATE,
				'%Y.%m.%d'
			)						AS USABLE_START_DATE,
			DATE_FORMAT(
				VI.USABLE_END_DATE,
				'%Y.%m.%d'
			)						AS USABLE_END_DATE,
			TIMESTAMPDIFF(
				DAY,CURDATE(),VI.USABLE_END_DATE
			)						AS DATE_INTERVAL,
			DATE_FORMAT(
				VI.UPDATE_DATE,
				'%Y.%m.%d'
			)						AS UPDATE_DATE
		FROM
			VOUCHER_ISSUE VI
			LEFT JOIN VOUCHER_MST VM ON
			VI.VOUCHER_IDX = VM.IDX
		WHERE
			".$where."
	";

	$db->query($select_voucher_issue_sql);

	foreach($db->fetch() as $data){
		$json_result['data'][] = array(
			'voucher_issue_code'	=> $data['VOUCHER_ISSUE_CODE'],
			'sale_price_type'		=> $data['SALE_PRICE_TYPE'],
			'min_price'				=> $data['MIN_PRICE'],
			'voucher_name'			=> $data['VOUCHER_NAME'],
			'usable_start_date'		=> $data['USABLE_START_DATE'],
			'usable_end_date'		=> $data['USABLE_END_DATE'],
			'date_interval'			=> $data['DATE_INTERVAL'],
			'used_flg'				=> $data['USED_FLG'],
			'update_date'			=> $data['UPDATE_DATE']
		);
	}
}

?>