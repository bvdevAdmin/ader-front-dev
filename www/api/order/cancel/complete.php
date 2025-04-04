<?php
/*
 +=============================================================================
 | 
 | 취소 완료 화면
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

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
    $member_idx = $_SESSION['MEMBER_IDX'];
}

if (isset($_SERVER['HTTP_COUNTRY']) && $member_idx > 0) {
	if ($update_code != null) {
		$cnt_order = $db->count("ORDER_CANCEL","ORDER_UPDATE_CODE = ?",array($update_code));
		if ($cnt_order > 0) {
			$cancel_price	= getCancel_price($db,$update_code);
			
			$cancel_product	= getCancel_product($db,$update_code);
			
			$json_result['data'] = array(
				'cancel_price'		=>$cancel_price,
				'cancel_product'	=>$cancel_product
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

function getCancel_price($db,$update_code) {
	$cancel_price = array();
	
	$select_order_cancel_sql = "
        SELECT
			OC.ORDER_CODE			AS ORDER_CODE,
			DATE_FORMAT(
				OC.CREATE_DATE,
				'%Y.%m.%d %H:%i'
			)						AS CREATE_DATE,
			
            OC.PRICE_PRODUCT		AS PRICE_PRODUCT,
			
			(
				SELECT
					SUM(
						(OP.MEMBER_PRICE / OP.PRODUCT_QTY) * PC.PRODUCT_QTY
					)
				FROM
					ORDER_PRODUCT_CANCEL PC

					LEFT JOIN ORDER_PRODUCT OP ON
					PC.ORDER_PRODUCT_CODE = OP.ORDER_PRODUCT_CODE
				WHERE
					PC.ORDER_UPDATE_CODE = OC.ORDER_UPDATE_CODE
			)						AS PRICE_MEMBER,

			OC.PRICE_DISCOUNT		AS PRICE_DISCOUNT,
            OC.PRICE_MILEAGE_POINT	AS PRICE_MILEAGE,
			OC.PRICE_CANCEL			AS PRICE_CANCEL,
			OC.PRICE_REFUND			AS PRICE_REFUND,
			
			OC.PRICE_DELIVERY		AS DELIVERY_PRICE,
			OC.DELIVERY_RETURN		AS DELIVERY_RETURN
        FROM 
            ORDER_CANCEL OC
        WHERE
            OC.ORDER_UPDATE_CODE = ?
    ";
	
	$db->query($select_order_cancel_sql,array($update_code));
	
	foreach($db->fetch() as $data) {
		$t_product		= number_format($data['PRICE_PRODUCT']);
		$t_member		= number_format($data['PRICE_MEMBER']);
		$t_discount		= number_format($data['PRICE_DISCOUNT']);
		$t_mileage		= number_format($data['PRICE_MILEAGE']);
		$t_cancel		= number_format($data['PRICE_CANCEL']);
		$t_refund		= number_format($data['PRICE_REFUND']);
		$t_delivery 	= number_format($data['DELIVERY_PRICE']);
		$t_return 		= number_format($data['DELIVERY_RETURN']);
		
		if ($_SERVER['HTTP_COUNTRY'] != "KR") {
			$t_product		= number_format($data['PRICE_PRODUCT'],1);
			$t_member		= number_format($data['PRICE_MEMBER'],1);
			$t_discount		= number_format($data['PRICE_DISCOUNT'],1);
			$t_mileage		= number_format($data['PRICE_MILEAGE'],1);
			$t_cancel		= number_format($data['PRICE_CANCEL'],1);
			$t_refund		= number_format($data['PRICE_REFUND'],1);
			$t_delivery 	= number_format($data['DELIVERY_PRICE'],1);
			$t_return 		= number_format($data['DELIVERY_RETURN']);
		}
		
		$cancel_price = array(
			'order_code'	=>$data['ORDER_CODE'],
			'create_date'	=>$data['CREATE_DATE'],
			
			't_product'		=>$t_product,
			't_member'		=>$t_member,
			't_discount'	=>$t_discount,
			't_mileage'		=>$t_mileage,
			't_cancel'		=>$t_cancel,
			't_refund'		=>$t_refund,
			't_delivery'	=>$t_delivery,
			't_return'		=>$t_return
		);
	}
	
	return $cancel_price;
}

function getCancel_product($db,$update_code) {
	$cancel_product = array();
	
	$select_cancel_product_sql = "
		SELECT
			OP.IDX						AS OP_IDX,
			OP.ORDER_PRODUCT_CODE		AS ORDER_PRODCT_CODE,
			OP.PRODUCT_NAME				AS PRODUCT_NAME,
			PR.COLOR					AS COLOR,
			PR.COLOR_RGB				AS COLOR_RGB,
			J_PI.IMG_LOCATION			AS IMG_LOCATION,
			OP.OPTION_NAME				AS OPTION_NAME,
			OP.PRODUCT_QTY				AS PRODUCT_QTY,
			OP.CANCEL_PRICE				AS PRODUCT_PRICE
		FROM
			ORDER_PRODUCT_CANCEL OP
			
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
			OP.ORDER_UPDATE_CODE = ? AND
			OP.PRODUCT_TYPE NOT REGEXP 'V|D' AND
			OP.PARENT_IDX = 0
		ORDER BY
			OP.IDX ASC
	";
	
	$db->query($select_cancel_product_sql,array($update_code));
	
	foreach($db->fetch() as $data) {
		$product_price = number_format($data['PRODUCT_PRICE']);
		if ($_SERVER['HTTP_COUNTRY'] != "KR") {
			$product_price = number_format($data['PRODUCT_PRICE'],1);
		}
		
		$cancel_product[] = array(
			'op_idx'				=>$data['OP_IDX'],
			'order_product_code'	=>$data['ORDER_PRODCT_CODE'],
			'product_name'			=>$data['PRODUCT_NAME'],
			'color'					=>$data['COLOR'],
			'color_rgb'				=>$data['COLOR_RGB'],
			'img_location'			=>$data['IMG_LOCATION'],
			'option_name'			=>$data['OPTION_NAME'],
			
			'product_qty'			=>$data['PRODUCT_QTY'],
			
			'product_price'			=>$product_price
		);
	}
	
	return $cancel_product;
}

?>