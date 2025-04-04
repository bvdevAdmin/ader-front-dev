<?php
/*
 +=============================================================================
 | 
 | 마이페이지 적립금 이력 조회
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
	$mileage_info = array();
	
	$table = "
		MILEAGE_INFO MI
		
		LEFT JOIN MILEAGE_CODE MC ON
		MI.MILEAGE_CODE = MC.MILEAGE_CODE
		
		LEFT JOIN ORDER_INFO OI ON
		MI.ORDER_CODE = OI.ORDER_CODE

		LEFT JOIN ORDER_PRODUCT OP ON
		MI.ORDER_PRODUCT_CODE = OP.ORDER_PRODUCT_CODE
	";
	
	$where = "
		MI.COUNTRY		= ? AND
		MI.MEMBER_IDX	= ? AND
		MI.DEL_FLG = FALSE
	";
	
	$param_bind = array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']);
	
	$json_result = array(
		'total' => $db->count($table,$where,$param_bind),
		'page' => $page
	);
	
	$select_mileage_info_sql = "
		SELECT
			DATE_FORMAT(
				MI.CREATE_DATE,
				'%Y.%m.%d'
			)						AS CREATE_DATE,
			
			MC.MILEAGE_TYPE_KR		AS MILEAGE_TYPE_KR,
			MC.MILEAGE_TYPE_EN		AS MILEAGE_TYPE_EN,
			
			IFNULL(
				MI.ORDER_CODE,'-'
			)						AS ORDER_CODE,
			MI.MILEAGE_CODE			AS MILEAGE_CODE,
			IFNULL(
				OI.PRICE_PRODUCT,'-'
			)						AS PRICE_TOTAL,
			IFNULL(
				MI.ORDER_PRODUCT_CODE,'-'
			)						AS ORDER_PRODUCT_CODE,
			IFNULL(
				OP.PRODUCT_NAME,'-'
			)						AS PRODUCT_NAME,
			IFNULL(
				OP.REMAIN_QTY,'-'
            )						AS PRODUCT_QTY,
			IFNULL(
				OP.REMAIN_PRICE,'0'
			)						AS PRODUCT_PRICE,
			
			MI.MILEAGE_UNUSABLE		AS MILEAGE_UNUSABLE,
			IFNULL(
				DATE_FORMAT(
					MI.MILEAGE_USABLE_DATE,
					'%Y.%m.%d'
				),'-'
			)						AS USABLE_DATE,
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

	$param_PC = array();
	$param_PE = array();
	$param_PF = array();
	
	$db->query($select_mileage_info_sql,$param_bind);
	
	foreach ($db->fetch() as $data) {
		switch ($data['MILEAGE_CODE']) {
			case "CIN" :
			case "CDC" :
				array_push($param_PC,$data['ORDER_PRODUCT_CODE']);

				break;
			
			case "EIN" :
			case "EDC" :
				array_push($param_PE,$data['ORDER_PRODUCT_CODE']);

				break;
			
			case "RIN" :
			case "RDC" :
				array_push($param_PF,$data['ORDER_PRODUCT_CODE']);

				break;
		}

		$usable_date = $data['USABLE_DATE'];
		if ($data['MILEAGE_CODE'] == "AIN" || $data['MILEAGE_CODE'] == "ADC") {
			$usable_date = "-";
		}

		/* 한국몰/영문몰 금액 표기 수정 */
		$price_total	= "-";
		
		if ($data['PRICE_TOTAL'] != "-") {
			$price_total = number_format($data['PRICE_TOTAL']);
		}

		$product_price	= number_format($data['PRODUCT_PRICE']);
		$mileage_unu	= number_format($data['MILEAGE_UNUSABLE']);
		$mileage_inc	= number_format($data['MILEAGE_USABLE_INC']);
		$mileage_dec	= number_format($data['MILEAGE_USABLE_DEC']);
		$mileage_bal	= number_format($data['MILEAGE_BALANCE']);
		if ($_SERVER['HTTP_COUNTRY'] == "EN") {
			if ($data['PRICE_TOTAL'] != "-") {
				$price_total = number_format($data['PRICE_TOTAL'],1);
			}

			$product_price	= number_format($data['PRODUCT_PRICE'],1);
			$mileage_unu	= number_format($data['MILEAGE_UNUSABLE'],1);
			$mileage_inc	= number_format($data['MILEAGE_USABLE_INC'],1);
			$mileage_dec	= number_format($data['MILEAGE_USABLE_DEC'],1);
			$mileage_bal	= number_format($data['MILEAGE_BALANCE'],1);
		}

		$mileage_info[] = array(
			'create_date'			=>$data['CREATE_DATE'],
			'mileage_type'			=>$data['MILEAGE_TYPE_'.$_SERVER['HTTP_COUNTRY']],
			'order_code'			=>$data['ORDER_CODE'],
			
			'price_total'			=>$price_total,
			'order_product_code'	=>$data['ORDER_PRODUCT_CODE'],
			'product_name'			=>$data['PRODUCT_NAME'],
			'product_qty'			=>$data['PRODUCT_QTY'],
			'product_price'			=>$product_price,
			
			'mileage_unu'			=>$mileage_unu,
			'usable_date'			=>$usable_date,
			'mileage_inc'			=>$mileage_inc,
			'mileage_dec'			=>$mileage_dec,
			'mileage_bal'			=>$mileage_bal
		);
	}

	if (count($param_PC) > 0) {
		$mileage_PC = getMileage_order($db,"PC",$param_PC);
	}

	if (count($param_PE) > 0) {
		$mileage_PE = getMileage_order($db,"PE",$param_PE);
	}
	
	if (count($param_PF) > 0) {
		$mileage_PF = getMileage_order($db,"PF",$param_PF);
	}

	if (count($mileage_info) > 0) {
		foreach($mileage_info as $key => $mileage) {
			$tmp = 0;
			$tmp_code = $mileage['order_product_code'];

			$price_total	= null;
			$update_code	= null;
			$product_name	= null;
			$product_qty	= null;
			$product_price	= null;

			if (isset($mileage_PC[$tmp_code])) {
				$tmp++;
				$price_total	= $mileage_PC[$tmp_code]['price_total'];
				$update_code	= $mileage_PC[$tmp_code]['update_code'];
				$product_name	= $mileage_PC[$tmp_code]['product_name'];
				$product_qty	= $mileage_PC[$tmp_code]['product_qty'];
				$product_price	= $mileage_PC[$tmp_code]['product_price'];
			}

			if (isset($mileage_PE[$tmp_code])) {
				$tmp++;
				$price_total	= $mileage_PE[$tmp_code]['price_total'];
				$update_code	= $mileage_PE[$tmp_code]['update_code'];
				$product_name	= $mileage_PE[$tmp_code]['product_name'];
				$product_qty	= $mileage_PE[$tmp_code]['product_qty'];
				$product_price	= $mileage_PE[$tmp_code]['product_price'];
			}

			if (isset($mileage_PF[$tmp_code])) {
				$tmp++;
				$price_total	= $mileage_PF[$tmp_code]['price_total'];
				$update_code	= $mileage_PF[$tmp_code]['update_code'];
				$product_name	= $mileage_PF[$tmp_code]['product_name'];
				$product_qty	= $mileage_PF[$tmp_code]['product_qty'];
				$product_price	= $mileage_PF[$tmp_code]['product_price'];
			}

			if ($tmp > 0) {
				$mileage_info[$key]['price_total']		= $price_total;
				$mileage_info[$key]['update_code']		= $update_code;
				$mileage_info[$key]['product_name']		= $product_name;
				$mileage_info[$key]['product_qty']		= $product_qty;
				$mileage_info[$key]['product_price']	= $product_price;
			}
		}
	}

	$json_result['data'] = $mileage_info;
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
}

function getMileage_order($db,$type,$param) {
	$order_info = array();

	$table_order = array(
		'PC'		=>"ORDER_CANCEL",
		'PE'		=>"ORDER_EXCHANGE",
		'PF'		=>"ORDER_REFUND"
	);

	$select_mileage_order_sql = "
		SELECT
			OI.PRICE_PRODUCT		AS PRICE_PRODUCT,
			OI.ORDER_UPDATE_CODE	AS ORDER_UPDATE_CODE,
			'-'						AS PRODUCT_NAME,
			'-'						AS PRODUCT_QTY,
			'-'						AS PRODUCT_PRICE
		FROM
			".$table_order[$type]." OI
		WHERE
			OI.ORDER_UPDATE_CODE IN (".implode(',',array_fill(0,count($param),'?')).")
	";

	$db->query($select_mileage_order_sql,$param);

	foreach($db->fetch() as $data) {
		$order_info[$data['ORDER_UPDATE_CODE']] = array(
			'price_total'			=>$data['PRICE_PRODUCT'],
			'update_code'			=>$data['ORDER_UPDATE_CODE'],
			'product_name'			=>$data['PRODUCT_NAME'],
			'product_qty'			=>$data['PRODUCT_QTY'],
			'product_price'			=>$data['PRODUCT_PRICE']
		);
	}

	return $order_info;
}

?>