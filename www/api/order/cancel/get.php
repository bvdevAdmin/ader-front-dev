<?php
/*
 +=============================================================================
 | 
 | 주문 취소 화면
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
			$order_remain	= getOrder_remain($db,$order_code);
			$order_product	= getProduct_cancel($db,$order_code);
			$order_reason	= getOrder_reason($db,"OCC");
			
			$json_result['data'] = array(
				'order_remain'		=>$order_remain,
				'order_product'		=>$order_product,
				'order_reason'		=>$order_reason
			);
		} else {
			$json_result['code'] = 301;
			$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0046',array());
			
			echo json_encode($json_result);
			exit;
		}
	} else {
		$json_result['code'] = 300;
		$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0046',array());

		echo json_encode($json_result);
		exit;
	}
} else {
	$json_result['code'] = 401;
    $json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());

    echo json_encode($json_result);
    exit;
}

function getOrder_remain($db,$order_code) {
	$order_remain = array();
	
	$select_order_remain_sql = "
        SELECT 
			OI.ORDER_CODE						AS ORDER_CODE,
			DATE_FORMAT(
				OI.CREATE_DATE,
				'%Y.%m.%d %H:%i'
			)									AS CREATE_DATE,
			OI.PG_REMAIN_PRICE					AS PG_REMAIN,
			(
				SELECT
					IFNULL(
						SUM(
							(S_OP.PRODUCT_PRICE / S_OP.PRODUCT_QTY) * S_OP.REMAIN_QTY
						),0
					)
				FROM
					ORDER_PRODUCT S_OP
				WHERE
					S_OP.ORDER_CODE = OI.ORDER_CODE AND
					S_OP.PRODUCT_TYPE NOT REGEXP 'V|D' AND
					S_OP.REMAIN_QTY > 0
			)									AS REMAIN_PRODUCT,
			(
				SELECT
					IFNULL(
						SUM(
							(S_OP.MEMBER_PRICE / S_OP.PRODUCT_QTY) * S_OP.REMAIN_QTY
						),0
					)
				FROM
					ORDER_PRODUCT S_OP
				WHERE
					S_OP.ORDER_CODE = OI.ORDER_CODE AND
					S_OP.PRODUCT_TYPE NOT REGEXP 'V|D' AND
					S_OP.REMAIN_QTY > 0
			)									AS REMAIN_MEMBER,
			OI.REMAIN_DISCOUNT					AS REMAIN_DISCOUNT,
            OI.REMAIN_MILEAGE					AS REMAIN_MILEAGE,
			OI.REMAIN_DELIVERY					AS REMAIN_DELIVERY,
			
			IFNULL(
				J_OC_E.PRICE_DELIVERY,0
			)									AS EXTRA_DELIVERY,
			IFNULL(
				J_OC_R.PRICE_DELIVERY,0
			)									AS DELIVERY_RETURN
        FROM
            ORDER_INFO OI

			LEFT JOIN (
				SELECT
					S_OC.ORDER_CODE				AS ORDER_CODE,
					SUM(S_OC.PRICE_DELIVERY)	AS PRICE_DELIVERY
				FROM
					ORDER_CANCEL S_OC
				GROUP BY
					S_OC.ORDER_CODE
			) AS J_OC_E ON
			OI.ORDER_CODE = J_OC_E.ORDER_CODE

			LEFT JOIN (
				SELECT
					S_OC.ORDER_CODE				AS ORDER_CODE,
					SUM(S_OC.DELIVERY_RETURN)	AS PRICE_DELIVERY
				FROM
					ORDER_CANCEL S_OC
				GROUP BY
					S_OC.ORDER_CODE
			) AS J_OC_R ON
			OI.ORDER_CODE = J_OC_R.ORDER_CODE
        WHERE
            OI.ORDER_CODE = ?
    ";
	
	$db->query($select_order_remain_sql,array($order_code));
	
	foreach($db->fetch() as $data) {
		$pg = $data['PG_REMAIN'];

		$product		= $data['REMAIN_PRODUCT'];
		$member			= $data['REMAIN_MEMBER'];
		$discount		= $data['REMAIN_DISCOUNT'];
		$mileage		= $data['REMAIN_MILEAGE'];
		$delivery		= $data['REMAIN_DELIVERY'] + $data['EXTRA_DELIVERY'];
		$return			= $data['DELIVERY_RETURN'];

		$cancel			= $data['REMAIN_PRODUCT'] - $data['REMAIN_MEMBER'] - $data['REMAIN_DISCOUNT'] - $data['REMAIN_MILEAGE'];
		
		if ($pg == ($cancel + $delivery)) {
			$return += $delivery;
			$cancel += $delivery;
		}

		$t_product		= number_format($product);
		$t_member		= number_format($member);
		$t_discount		= number_format($discount);
		$t_mileage		= number_format($mileage);
		$t_delivery		= number_format($delivery);
		$t_return		= number_format($return);
		$t_cancel 		= number_format($cancel);

		if ($_SERVER['HTTP_COUNTRY'] != "KR") {
			$t_product		= number_format($product,1);
			$t_member		= number_format($member,1);
			$t_discount		= number_format($discount,1);
			$t_mileage		= number_format($mileage,1);
			$t_delivery		= number_format($delivery,1);
			$t_return		= number_format($return,1);
			$t_cancel 		= number_format($cancel,1);
		}
		
		$order_remain = array(
			'order_code'		=>$data['ORDER_CODE'],
			'create_date'		=>$data['CREATE_DATE'],
			'product'			=>$data['REMAIN_PRODUCT'],
			'member'			=>$data['REMAIN_MEMBER'],
			'discount'			=>$data['REMAIN_DISCOUNT'],
			'mileage'			=>$data['REMAIN_MILEAGE'],
			'delivery'			=>$data['REMAIN_DELIVERY'],
			'return'			=>$return,
			'cancel'			=>$cancel,
			
			't_product'			=>$t_product,
			't_member'			=>$t_member,
			't_discount'		=>$t_discount,
			't_mileage'			=>$t_mileage,
			't_delivery'		=>$t_delivery,
			't_return'			=>$t_return,
			't_cancel'			=>$t_cancel
		);
	}
	
	return $order_remain;
}

function getProduct_cancel($db,$order_code) {
	$order_product = array();
	
	$select_order_product_sql = "
		SELECT
			OP.IDX						AS OP_IDX,
			OP.ORDER_PRODUCT_CODE		AS ORDER_PRODCT_CODE,
			OP.PRODUCT_NAME				AS PRODUCT_NAME,
			PR.COLOR					AS COLOR,
			PR.COLOR_RGB				AS COLOR_RGB,
			J_PI.IMG_LOCATION			AS IMG_LOCATION,
			OP.OPTION_NAME				AS OPTION_NAME,
			OP.REMAIN_QTY				AS PRODUCT_QTY,
			OP.REMAIN_PRICE				AS PRODUCT_PRICE
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
			OP.ORDER_STATUS = 'PCP' AND
			OP.PRODUCT_TYPE NOT REGEXP 'V|D' AND
			OP.PARENT_IDX = 0 AND
			OP.REMAIN_QTY > 0
		ORDER BY
			OP.IDX ASC
	";
	
	$db->query($select_order_product_sql,array($order_code));
	
	foreach($db->fetch() as $data) {
		for ($i=1; $i<=$data['PRODUCT_QTY']; $i++) {
			$product_price = $data['PRODUCT_PRICE'] / $data['PRODUCT_QTY'];

			$t_product_price = number_format($product_price);
			if ($_SERVER['HTTP_COUNTRY'] != "KR") {
				$product_price = number_format($product_price,1);
			}
			
			$order_product[] = array(
				'op_idx'				=>$data['OP_IDX'],
				'order_product_code'	=>$data['ORDER_PRODCT_CODE'],
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

?>