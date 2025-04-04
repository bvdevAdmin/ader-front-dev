<?php
/*
 +=============================================================================
 | 
 | 마이페이지 마일리지 리스트 정보 취득 // /member/mileage/list/get
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

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	$table = "
		MILEAGE_INFO MI
		
		LEFT JOIN MILEAGE_CODE MC ON
		MI.MILEAGE_CODE = MC.MILEAGE_CODE
		
		LEFT JOIN ORDER_INFO OI ON
		MI.ORDER_CODE = OI.ORDER_CODE
	";
			
	$where = "
		MI.COUNTRY		= ? AND
		MI.MEMBER_IDX	= ? AND
		MI.DEL_FLG = FALSE
	";
	
	$param_bind = array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']);
	
	if (isset($list_type)) {
		if ($list_type == "INC") {
			$where .= " AND (MI.MILEAGE_USABLE_INC > 0) ";
		} else if ($list_type == "DEC") {
			$where .= " AND (MI.MILEAGE_USABLE_DEC > 0) ";
		}
	}
	
	$json_result = array(
		'total' => $db->count($table,$where,$param_bind),
		'page' => $page
	);
	
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
				OI.PRICE_PRODUCT,'0'
			)						AS PRICE_TOTAL,
			
			MI.MILEAGE_CODE			AS MILEAGE_CODE,
			MC.MILEAGE_TYPE_KR		AS MILEAGE_TYPE_KR,
			MC.MILEAGE_TYPE_EN		AS MILEAGE_TYPE_EN,
			
			MI.MILEAGE_UNUSABLE		AS MILEAGE_UNUSABLE,
			MI.MILEAGE_USABLE_INC	AS MILEAGE_USABLE_INC,
			MI.MILEAGE_USABLE_DEC	AS MILEAGE_USABLE_DEC,
			MI.MILEAGE_BALANCE		AS MILEAGE_BALANCE
		FROM
			".$table."
		WHERE
			".$where."
		ORDER BY
			MI.IDX DESC
	";
	
	if (isset($rows)) {
		$limit_start = (intval($page)-1)*$rows;
		
		$select_mileage_info_sql .= " LIMIT ?,? ";
		
		array_push($param_bind,$limit_start);
		array_push($param_bind,$rows);
	}
	
	$db->query($select_mileage_info_sql,$param_bind);
	
	foreach($db->fetch() as $data) {
		$price_total = number_format($data['PRICE_TOTAL']);
		$mileage_unu = number_format($data['MILEAGE_UNUSABLE']);
		$mileage_inc = number_format($data['MILEAGE_USABLE_INC']);
		$mileage_dec = number_format($data['MILEAGE_USABLE_DEC']);
		$mileage_sum = number_format($data['MILEAGE_BALANCE']);

		if ($_SERVER['HTTP_COUNTRY'] == "EN") {
			$price_total = number_format($data['PRICE_TOTAL'],1);
			$mileage_unu = number_format($data['MILEAGE_UNUSABLE'],1);
			$mileage_inc = number_format($data['MILEAGE_USABLE_INC'],1);
			$mileage_dec = number_format($data['MILEAGE_USABLE_DEC'],1);
			$mileage_sum = number_format($data['MILEAGE_BALANCE'],1);
		}

		$json_result['data'][] = array(
			'update_date'			=>$data['UPDATE_DATE'],
			'order_code'			=>$data['ORDER_CODE'],
			'price_total'			=>$price_total,
			
			'mileage_type'			=>$data['MILEAGE_TYPE_'.$_SERVER['HTTP_COUNTRY']],
			
			'mileage_unu'			=>$data['MILEAGE_UNUSABLE'],
			'txt_mileage_unu'		=>$mileage_unu,
			'mileage_inc'			=>$mileage_inc,
			'mileage_dec'			=>$mileage_dec,
			'mileage_sum'			=>$mileage_sum
		);
	}
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
}

?>