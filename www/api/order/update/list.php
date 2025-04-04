<?php
/*
 +=============================================================================
 | 
 | 주문 교환/반품 접수 화면
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2024.12.05
 | 최종 수정	: 
 | 최종 수정일	: 
 | 버전		: 
 | 설명		: 
 | 
 +=============================================================================
*/	

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	if ($order_code != null) {
		$cnt_order = $db->count("ORDER_INFO","COUNTRY = ? AND MEMBER_IDX = ? AND ORDER_CODE = ?",array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'],$order_code));
		if ($cnt_order > 0) {
			$select_order_info_sql = "
				SELECT
					OI.ORDER_CODE			AS ORDER_CODE,
					DATE_FORMAT(
						OI.CREATE_DATE,
						'%Y.%m.%d %H:%i'
					)						AS CREATE_DATE
				FROM
					ORDER_INFO OI
				WHERE
					ORDER_CODE = ?
			";
	
			$db->query($select_order_info_sql,array($order_code));
	
			foreach($db->fetch() as $data) {
				$order_info = array(
					'order_code'		=>$data['ORDER_CODE'],
					'create_date'		=>$data['CREATE_DATE']
				);
			}
	
			/* 주문교환 배송비 미결제 접수상품 삭제 */
			initOrder_update($db,"OEX",$order_code);
			
			/* 주문반품 배송비 미결제 접수상품 삭제 */
			initOrder_update($db,"ORF",$order_code);
			
			$order_product	 = getProduct_complete($db,$order_code);
			
			$delivery_company = getDelivery_company($db);
			
			$reason_exchange = getOrder_reason($db,"OEX");
			$reason_refund	 = getOrder_reason($db,"ORF");
			
			$json_result['data'] = array(
				'order_info'		=>$order_info,
				'order_product'		=>$order_product,
				'reason_exchange'	=>$reason_exchange,
				'reason_refund'		=>$reason_refund,
				'delivery_company'	=>$delivery_company
			);
		} else {
			$json_result['code'] = 300;
			$json_result['msg'] = "";
			
			echo json_encode($json_result);
			exit;
		}
	}
} else {
	$json_result['code'] = 401;
    $json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());

    echo json_encode($json_result);
    exit;
}

function getProduct_complete($db,$order_code) {
	$order_product = array();
	
	$select_order_product_sql = "
		SELECT
			OP.IDX						AS OP_IDX,
			OP.ORDER_PRODUCT_CODE		AS ORDER_PRODCT_CODE,
			OP.PRODUCT_NAME				AS PRODUCT_NAME,
			PR.PRODUCT_TYPE				AS PRODUCT_TYPE,
			PR.COLOR					AS COLOR,
			PR.COLOR_RGB				AS COLOR_RGB,
			J_PI.IMG_LOCATION			AS IMG_LOCATION,
			OP.OPTION_NAME				AS OPTION_NAME,
			OP.REMAIN_QTY				AS REMAIN_QTY,
			OP.REMAIN_PRICE				AS REMAIN_PRICE 
		FROM
			ORDER_PRODUCT OP
			
			LEFT JOIN SHOP_PRODUCT PR ON
			OP.PRODUCT_IDX = PR.IDX
			
			LEFT JOIN (
				SELECT
					S_PI.PRODUCT_IDX	AS PRODUCT_IDX,
					S_PI.IMG_LOCATION	AS IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.IMG_TYPE = 'P' AND
					S_PI.IMG_SIZE = 'S' AND
					S_PI.DEL_FLG = FALSE
				GROUP BY
					S_PI.PRODUCT_IDX
			) AS J_PI ON
			OP.PRODUCT_IDX = J_PI.PRODUCT_IDX
		WHERE
			OP.ORDER_CODE = ? AND
			OP.ORDER_STATUS = 'DCP' AND
			OP.PRODUCT_TYPE NOT REGEXP 'V|D' AND
			OP.PARENT_IDX = 0 AND
			OP.REMAIN_QTY > 0 AND

			PR.REFUND_FLG = FALSE
		ORDER BY
			OP.IDX ASC
	";
	
	$db->query($select_order_product_sql,array($order_code));
	
	foreach($db->fetch() as $data) {
		for ($i=1; $i<=$data['REMAIN_QTY']; $i++) {
			$product_price = $data['REMAIN_PRICE'] / $data['REMAIN_QTY'];
			$t_product_price = number_format($product_price);
			if ($_SERVER['HTTP_COUNTRY'] != "KR") {
				$product_price = number_format($product_price,1);
			}
			
			$order_product[] = array(
				'op_idx'				=>$data['OP_IDX'],
				'order_product_code'	=>$data['ORDER_PRODCT_CODE'],
				'product_type'			=>$data['PRODUCT_TYPE'],
				'product_name'			=>$data['PRODUCT_NAME'],
				'color'					=>$data['COLOR'],
				'color_rgb'				=>$data['COLOR_RGB'],
				'img_location'			=>$data['IMG_LOCATION'],
				'option_name'			=>$data['OPTION_NAME'],
				
				'product_qty'			=>1,
				
				'product_price'			=>$product_price,
				't_product_price'		=>$t_product_price
			);
		}
	}
	
	return $order_product;
}

function getDelivery_company($db) {
	$delivery_company = array();
	
	$where = " COUNTRY = 'KR' ";
	if ($_SERVER['HTTP_COUNTRY'] != "KR") {
		$where = " COUNTRY = 'FR' ";
	}

	$select_delivery_company_sql = "
		SELECT
			DC.IDX				AS DELIVERY_IDX,
			DC.COMPANY_NAME		AS DELIVERY_COMPANY
		FROM
			DELIVERY_COMPANY DC
		WHERE
			".$where."
		ORDER BY
			DC.IDX ASC
	";
	
	$db->query($select_delivery_company_sql);
	
	foreach($db->fetch() as $data) {
		$delivery_company[] = array(
			'delivery_idx'		=>$data['DELIVERY_IDX'],
			'delivery_company'	=>$data['DELIVERY_COMPANY']
		);
	}
	
	return $delivery_company;
}

function getOrder_reason($db,$param_status) {
	$select_reason_depth1_sql = "
		SELECT
			D1.IDX				AS D1_IDX,
			D1.PG_FLG			AS PG_FLG,
			D1.REASON_TXT		AS REASON_TXT
		FROM
			REASON_DEPTH_1 D1
		WHERE
			D1.COUNTRY = ? AND
			D1.REASON_TYPE = ?
		ORDER BY
			D1.DISPLAY_NUM ASC
	";
	
	$db->query($select_reason_depth1_sql,array($_SERVER['HTTP_COUNTRY'],$param_status));
	
	foreach($db->fetch() as $data) {
		$tmp_flg = "F";
		if ($data['PG_FLG'] == true) {
			$tmp_flg = "T";
		}
		
		$reason_d1[] = array(
			'd1_idx'		=>$data['D1_IDX'],
			'pg_flg'		=>$data['PG_FLG'],
			'reason_txt'	=>$data['REASON_TXT']
		);
	}
	
	$select_reason_depth2_sql = "
		SELECT
			D2.IDX				AS D2_IDX,
			D2.DEPTH_1_IDX		AS D1_IDX,
			D2.PG_FLG			AS PG_FLG,
			D2.REASON_TXT		AS REASON_TXT
		FROM
			REASON_DEPTH_2 D2
		WHERE
			D2.COUNTRY = ? AND
			D2.REASON_TYPE = ?
		ORDER BY
			D2.DISPLAY_NUM ASC
	";
	
	$db->query($select_reason_depth2_sql,array($_SERVER['HTTP_COUNTRY'],$param_status));
	
	foreach($db->fetch() as $data) {
		$tmp_flg = "F";
		if ($data['PG_FLG'] == true) {
			$tmp_flg = "T";
		}
		
		$reason_d2[$data['D1_IDX']][] = array(
			'd2_idx'		=>$data['D2_IDX'],
			'pg_flg'		=>$data['PG_FLG'],
			'reason_txt'	=>$data['REASON_TXT']
		);
	}
	
	$order_reason = array(
		'reason_d1'		=>$reason_d1,
		'reason_d2'		=>$reason_d2,
	);
	
	return $order_reason;
}

/* 주문교환/반품 배송비 미결제 접수상품 삭제 */
function initOrder_update($db,$param_status,$order_code) {
	$table_I = array(
		'OEX'		=>"ORDER_EXCHANGE",
		'ORF'		=>"ORDER_REFUND",
	);
	
	$table_P = array(
		'OEX'		=>"ORDER_PRODUCT_EXCHANGE",
		'ORF'		=>"ORDER_PRODUCT_REFUND",
	);
	
	$status_D = array(
		'OEX'		=>"OET",
		'ORF'		=>"ORT"
	);
	
	$select_order_update_sql = "
		SELECT
			OP.IDX					AS OP_IDX,
			OP.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
			OP.PRODUCT_QTY			AS PRODUCT_QTY
		FROM
			".$table_P[$param_status]." OP
		WHERE
			OP.ORDER_CODE = ? AND
			OP.ORDER_STATUS LIKE '%T' AND
			OP.PRODUCT_TYPE NOT REGEXP 'V|D'
	";
	
	$db->query($select_order_update_sql,array($order_code));
	
	foreach($db->fetch() as $data) {
		$op_idx				= $data['OP_IDX'];
		$order_product_code = $data['ORDER_PRODUCT_CODE'];
		$product_qty		= $data['PRODUCT_QTY'];
		
		$init_product_B_sql = "
			UPDATE
				ORDER_PRODUCT
			SET
				REMAIN_PRICE	= (PRODUCT_PRICE / PRODUCT_QTY) * (REMAIN_QTY + ?),
				REMAIN_QTY		= REMAIN_QTY + ?
			WHERE
				ORDER_PRODUCT_CODE = ?
		";
		
		$db->query($init_product_B_sql,array($product_qty,$product_qty,$order_product_code));
		
		$cnt_set = $db->count($table_P[$param_status],"PARENT_IDX = ?",array($op_idx));
		if ($cnt_set > 0) {
			$init_product_S_sql = "
				UPDATE
					ORDER_PRODUCT
				SET
					REMAIN_PRICE	= (PRODUCT_PRICE / PRODUCT_QTY) * (REMAIN_QTY + ?),
					REMAIN_QTY		= REMAIN_QTY + ?
				WHERE
					PARENT_IDX = ?
			";
			
			$db->query($init_product_B_sql,array($product_qty,$product_qty,$op_idx));
		}
	}
	
	$db->delete("ORDER_PRODUCT","ORDER_CODE = ? AND PRODUCT_TYPE = 'D' AND ORDER_STATUS = 'PWT'",array($order_code));
	$db->delete($table_I[$param_status],"ORDER_CODE = ? AND ORDER_STATUS = ?",array($order_code,$status_D[$param_status]));
	$db->delete($table_P[$param_status],"ORDER_CODE = ? AND ORDER_STATUS = ?",array($order_code,$status_D[$param_status]));
}

?>