<?php
/*
 +=============================================================================
 | 
 | 장바구니 - 상품 리스트 조회 // /api/order/basket/list/get.php
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.14


 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once $_CONFIG['PATH']['API'].'_legacy/common.php';

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	$discount_per = 0;
	
	$member_per = checkMember_percentage($db);
	if (sizeof($member_per) > 0 && isset($member_per['discount_per'])) {
		$discount_per = $member_per['discount_per'];
	}
	
	$basket_info = array();
	
	$t_basket_price = 0;

	$select_basket_info_sql = "
		SELECT
			BI.IDX						AS BASKET_IDX,
			BI.COUNTRY					AS COUNTRY,
			BI.PRODUCT_IDX				AS PRODUCT_IDX,
			BI.PRODUCT_TYPE				AS PRODUCT_TYPE,
			BI.PARENT_IDX				AS PARENT_IDX,
			PR.SET_TYPE					AS SET_TYPE,
			J_PI.IMG_LOCATION			AS IMG_LOCATION,
			
			BI.PRODUCT_NAME				AS PRODUCT_NAME,
			PR.COLOR					AS COLOR,
			PR.COLOR_RGB				AS COLOR_RGB,
			IFNULL(
				OO.OPTION_NAME,'Set'
			)							AS OPTION_NAME,
			
			PR.PRICE_KR					AS PRICE_KR,
			PR.DISCOUNT_KR				AS DISCOUNT_KR,
			PR.SALES_PRICE_KR			AS SALES_PRICE_KR,
			
			PR.PRICE_EN					AS PRICE_EN,
			PR.DISCOUNT_EN				AS DISCOUNT_EN,
			PR.SALES_PRICE_EN			AS SALES_PRICE_EN,
			
			PR.DISCOUNT_FLG				AS DISCOUNT_FLG,
			IFNULL(
				J_PD.DISCOUNT_PER,0
			)							AS DISCOUNT_PER,
			
			BI.PRODUCT_QTY				AS BASKET_QTY,
			PR.SOLD_OUT_QTY				AS SOLD_OUT_QTY,
			IFNULL(
				V_ST.REMAIN_WCC_QTY,0
			)							AS REMAIN_QTY,
			IFNULL(
				V_ST.PURCHASEABLE_QTY,0
			)							AS LIMIT_QTY
		FROM
			BASKET_INFO BI
			
			LEFT JOIN SHOP_PRODUCT PR ON
			BI.PRODUCT_IDX = PR.IDX
			
			LEFT JOIN SHOP_OPTION OO ON
			BI.OPTION_IDX = OO.IDX
			
			LEFT JOIN (
				SELECT
					S_PD.PRODUCT_IDX	AS PRODUCT_IDX,
					S_PD.DISCOUNT_PER	AS DISCOUNT_PER
				FROM
					PRODUCT_DISCOUNT S_PD
				WHERE
					S_PD.LEVEL_IDX = ?
			) AS J_PD ON
			PR.IDX = J_PD.PRODUCT_IDX
			
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
			PR.IDX = J_PI.PRODUCT_IDX
			
			LEFT JOIN V_STOCK V_ST ON
			PR.IDX = V_ST.PRODUCT_IDX AND
			BI.OPTION_IDX = V_ST.OPTION_IDX
		WHERE
			BI.MEMBER_IDX = ? AND
			BI.PARENT_IDX = 0 AND
			BI.DEL_FLG = FALSE AND
			
			PR.SALE_FLG = TRUE AND
			PR.DEL_FLG = FALSE
		ORDER BY
			BI.IDX DESC
	";
	
	$db->query($select_basket_info_sql,array($_SESSION['LEVEL_IDX'],$_SESSION['MEMBER_IDX']));
	
	$parent_idx = array();
	
	foreach($db->fetch() as $data) {
		array_push($parent_idx,$data['BASKET_IDX']);
		
		/* 상품 재고상태 설정 */
		$stock_status = "STIN";
		
		if ($data['LIMIT_QTY'] == 0) {
			$stock_status = "STSO";
		} else {
			if ($data['REMAIN_QTY'] == 0) {
				$stock_status = "STSO";
			} else {
				if ($data['REMAIN_QTY'] <= $data['SOLD_OUT_QTY']) {
					$stock_status = "STCL";
				}
			}
		}

		$price			= $data['PRICE_'.$_SERVER['HTTP_COUNTRY']];
		$t_price		= number_format($data['PRICE_'.$_SERVER['HTTP_COUNTRY']]);
		$sales_price	= $data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']];
		$t_sales_price	= number_format($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']]);
		
		if ($_SERVER['HTTP_COUNTRY'] == "EN") {
			$price			= round($data['PRICE_'.$_SERVER['HTTP_COUNTRY']],1);
			$t_price		= number_format($data['PRICE_'.$_SERVER['HTTP_COUNTRY']],1);
			$sales_price	= round($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']],1);
			$t_sales_price	= number_format($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']],1);
		}
		
		$member_discount = $data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']];
		if ($data['DISCOUNT_FLG'] == true && $data['DISCOUNT_PER'] > 0) {
			$member_discount = $sales_price * ($data['DISCOUNT_PER'] / 100);
		} else {
			$member_discount = $sales_price * ($discount_per / 100);
		}

		$t_member_discount = "0";
		if ($member_discount > 0) {
			if ($_SERVER['HTTP_COUNTRY'] == "KR") {
				$t_member_discount = number_format($member_discount);
			} else if ($_SERVER['HTTP_COUNTRY'] == "EN") {
				$t_member_discount = number_format($member_discount,1);
			}
		}

		$basket_price = $data['BASKET_QTY'] * $sales_price;
		$t_basket_price += $basket_price;
		if ($_SERVER['HTTP_COUNTRY'] == "KR") {
			$basket_price = number_format($basket_price);
		} else if ($_SERVER['HTTP_COUNTRY'] == "EN") {
			$basket_price = number_format($basket_price,1);
		}
		
		$basket_info[] = array(
			'basket_idx'		=>$data['BASKET_IDX'],
			'product_idx'		=>$data['PRODUCT_IDX'],
			'product_type'		=>$data['PRODUCT_TYPE'],
			'set_type'			=>$data['SET_TYPE'],
			'product_img'		=>$data['IMG_LOCATION'],
			'product_name'		=>$data['PRODUCT_NAME'],
			'color'				=>$data['COLOR'],
			'color_rgb'			=>$data['COLOR_RGB'],
			'option_name'		=>$data['OPTION_NAME'],
			
			'price'				=>$price,
			't_price'			=>$t_price,
			'discount'			=>$data['DISCOUNT_'.$_SERVER['HTTP_COUNTRY']],
			'sales_price'		=>$sales_price,
			't_sales_price'		=>$t_sales_price,
			
			'member_discount'	=>$member_discount,
			't_member_discount'	=>$t_member_discount,
			
			'stock_status'		=>$stock_status,
			'basket_qty'		=>$data['BASKET_QTY'],
			'basket_price'		=>$basket_price
		);
	}
	
	if (count($parent_idx) > 0) {
		$basket_child = getBasket_child($db,$parent_idx);
		if (sizeof($basket_info) > 0 && sizeof($basket_child) > 0) {
			foreach($basket_info as $key => $basket) {
				$parent_idx = $basket['basket_idx'];
				$parent_status = $basket['stock_status'];
				
				if (isset($basket_child[$parent_idx])) {
					$cnt_soldout = 0;
					
					$tmp_child = $basket_child[$parent_idx];
					foreach($tmp_child as $child) {
						$basket_qty = $child['basket_qty'];
						$sold_out_qty = $child['sold_out_qty'];
						$remain_qty = $child['remain_qty'];
						$limit_qty	= $child['limit_qty'];
						
						$child_status = "STIN";
						if ($limit_qty == 0) {
							$child_status = "STSO";
						} else {
							if ($remain_qty == 0) {
								$child_status = "STSO";
							} else {
								if ($remain_qty <= $sold_out_qty) {
									$child_status = "STCL";
								}
							}
						}
						
						if ($child_status == "STSO") {
							$cnt_soldout++;
						}
					}
					
					if ($cnt_soldout > 0) {
						$parent_status = "STSO";
					} else {
						$parent_status = "STIN";
					}
					
					$basket_info[$key]['set_product'] = $basket_child[$parent_idx];
					$basket_info[$key]['stock_status'] = $parent_status;
				}
			}
		}
	}
	
	$cnt_basket = $db->count("BASKET_INFO","MEMBER_IDX = ? AND PARENT_IDX = 0 AND DEL_FLG = FALSE",array($_SESSION['MEMBER_IDX']));
	
	$default_address = false;
	$price_delivery = 0;
	
	$order_to = getOrder_to($db,$_SERVER['HTTP_COUNTRY'],null);
	if ($order_to != null) {
		$default_address = true;

		if ($_SERVER['HTTP_COUNTRY'] == "KR") {
			/* 한국몰 && 상품 판매금액 80,000 KRW 미만 */
			$price_delivery = $order_to['delivery_price'];
		} else if ($_SERVER['HTTP_COUNTRY'] == "EN") {
			/* 영문몰 && 상품 판매금액 300 USD 미만 */
			$price_delivery = $order_to['delivery_price'];
		}
	}

	$json_result['data'] = array(
		'basket_cnt'		=>$cnt_basket,
		'basket_info'		=>$basket_info,

		'default_address'	=>$default_address,
		'price_delivery'	=>$price_delivery
	);
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

function getBasket_child($db,$parent_idx) {
	$basket_child = array();
	
	$select_basket_child_sql = "
		SELECT
			BI.IDX						AS BASKET_IDX,
			BI.PARENT_IDX				AS PARENT_IDX,
			J_PI.IMG_LOCATION			AS IMG_LOCATION,
			BI.PRODUCT_NAME				AS PRODUCT_NAME,
			PR.COLOR					AS COLOR,
			PR.COLOR_RGB				AS COLOR_RGB,
			OO.OPTION_NAME				AS OPTION_NAME,
			
			BI.PRODUCT_QTY				AS BASKET_QTY,
			PR.SOLD_OUT_QTY				AS SOLD_OUT_QTY,
			IFNULL(
				V_ST.REMAIN_WCC_QTY,0
			)							AS REMAIN_QTY,
			IFNULL(
				V_ST.PURCHASEABLE_QTY,0
			)							AS LIMIT_QTY
		FROM
			BASKET_INFO BI
			
			LEFT JOIN SHOP_PRODUCT PR ON
			BI.PRODUCT_IDX = PR.IDX
			
			LEFT JOIN SHOP_OPTION OO ON
			BI.OPTION_IDX = OO.IDX
			
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
			BI.PRODUCT_IDX = J_PI.PRODUCT_IDX
			
			LEFT JOIN V_STOCK V_ST ON
			PR.IDX = V_ST.PRODUCT_IDX AND
			OO.IDX = V_ST.OPTION_IDX
		WHERE
			BI.PARENT_IDX IN (".implode(',',array_fill(0,count($parent_idx),'?')).") AND
			BI.DEL_FLG = FALSE
	";
	
	$db->query($select_basket_child_sql,$parent_idx);
	
	foreach($db->fetch() as $data) {
		$basket_child[$data['PARENT_IDX']][] = array(
			'basket_idx'		=>$data['BASKET_IDX'],
			'parent_idx'		=>$data['PARENT_IDX'],
			'product_img'		=>$data['IMG_LOCATION'],
			'product_name'		=>$data['PRODUCT_NAME'],
			'color'				=>$data['COLOR'],
			'color_rgb'			=>$data['COLOR_RGB'],
			'option_name'		=>$data['OPTION_NAME'],
			
			'basket_qty'		=>$data['BASKET_QTY'],
			'sold_out_qty'		=>$data['SOLD_OUT_QTY'],
			'remain_qty'		=>$data['REMAIN_QTY'],
			'limit_qty'			=>$data['LIMIT_QTY']
		);
	}
	
	return $basket_child;
}

function checkOrder_to($db,$order_to) {

}

?>