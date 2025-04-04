<?php
/*
 +=============================================================================
 | 
 | 마이페이지 바우처 목록 // '/var/www/www/api/mypage/voucher/list/get.php';
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.01.09
 | 최종 수정    : 양한빈
 | 최종 수정일	: 2024.05.07
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	$table = "
		VOUCHER_ISSUE VI
		
		LEFT JOIN VOUCHER_MST VM ON
		VI.VOUCHER_IDX = VM.IDX
	";
	
	$where = "
		VI.IDX IS NOT NULL AND
		VM.IDX IS NOT NULL AND
		VI.MEMBER_IDX = ? AND
		VI.COUNTRY = ?
	";
	
	$param_bind = array($_SESSION['MEMBER_IDX'],$_SERVER['HTTP_COUNTRY']);
	
	$select_voucher_issue_sql = "
		SELECT
			VI.VOUCHER_ISSUE_CODE	AS VOUCHER_ISSUE_CODE,
			VM.SALE_TYPE			AS SALE_TYPE,
			VM.SALE_PRICE			AS SALE_PRICE,
			VM.MIN_PRICE			AS MIN_PRICE,
			VM.VOUCHER_NAME			AS VOUCHER_NAME,
			VI.USED_FLG				AS USED_FLG,
			DATE_FORMAT(
				VI.USABLE_START_DATE,
				'%Y-%m-%d %H:%i'
			)						AS USABLE_START_DATE,
			DATE_FORMAT(
				VI.USABLE_START_DATE,
				'%Y.%m.%d'
			)						AS T_USABLE_START_DATE,
			DATE_FORMAT(
				VI.USABLE_END_DATE,
				'%Y-%m-%d %H:%i'
			)						AS USABLE_END_DATE,
			DATE_FORMAT(
				VI.USABLE_END_DATE,
				'%Y.%m.%d'
			)						AS T_USABLE_END_DATE,
			TIMESTAMPDIFF(
				DAY,
				CURDATE(),
				VI.USABLE_END_DATE
			)						AS DATE_INTERVAL,
			DATE_FORMAT(
				VI.UPDATE_DATE,
				'%Y.%m.%d'
			)						AS UPDATE_DATE
		FROM
			".$table."
		WHERE
			".$where."
		ORDER BY
			VI.USED_FLG ASC, DATE_INTERVAL DESC
	";
	
	if (isset($rows)) {
		$limit_start = (intval($page)-1)*$rows;
		
		$select_voucher_issue_sql .= " LIMIT ?,? ";
		
		array_push($param_bind,$limit_start);
		array_push($param_bind,$rows);
	}

	$db->query($select_voucher_issue_sql,$param_bind);
	
	$voucher_used	= array();
	$voucher_usable	= array();
	
	foreach($db->fetch() as $data){
		$sale_price_type = "";
		
		if ($data['SALE_TYPE'] == "PER") {
			$sale_price_type = $data['SALE_PRICE']."% OFF";
		} else if ($data['SALE_TYPE'] == "PRC") {
			$sale_price_type = number_format($data['SALE_PRICE'])." OFF";
		}
		
		$usable_flg = false;
		
		$today				= strtotime(date('Y-m-d H:i'));
		$usable_start_date	= strtotime($data['USABLE_START_DATE']);
		$usable_end_date	= strtotime($data['USABLE_END_DATE']);
		
		if ($today >= $usable_start_date && $today <= $usable_end_date) {
			$usable_flg = true;
		}
		
		if ($data['USED_FLG'] == true) {
			$voucher_used[] = array(
				'voucher_issue_code'	=>$data['VOUCHER_ISSUE_CODE'],
				'sale_price_type'		=>$sale_price_type,
				'min_price'				=>$data['MIN_PRICE'],
				'voucher_name'			=>$data['VOUCHER_NAME'],
				'usable_start_date'		=>$data['T_USABLE_START_DATE'],
				'usable_end_date'		=>$data['T_USABLE_END_DATE'],
				'date_interval'			=>$data['DATE_INTERVAL'],
				'used_flg'				=>$data['USED_FLG'],
				'update_date'			=>$data['UPDATE_DATE']
			);
		} else {
			$voucher_usable[] = array(
				'voucher_issue_code'	=>$data['VOUCHER_ISSUE_CODE'],
				'sale_price_type'		=>$sale_price_type,
				'min_price'				=>$data['MIN_PRICE'],
				'voucher_name'			=>$data['VOUCHER_NAME'],
				'usable_start_date'		=>$data['T_USABLE_START_DATE'],
				'usable_end_date'		=>$data['T_USABLE_END_DATE'],
				'date_interval'			=>$data['DATE_INTERVAL'],
				'used_flg'				=>$data['USED_FLG'],
				'update_date'			=>$data['UPDATE_DATE'],
				'usable_flg'			=>$usable_flg
			);
		}
	}

	$json_result['data'] = array(
		'voucher_usable'	=>$voucher_usable,
		'voucher_used'		=>$voucher_used
	);
} else {
    $json_result = array(
		'code'		=>401,
		'msg'		=>getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0018', array())
	);
}

?>