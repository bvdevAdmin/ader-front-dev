<?php
/*
 +=============================================================================
 | 
 | 상품 리스트 - 상품 리스트 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.19
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once("/var/www/www/api/common.php");

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} else if (isset($_POST['country'])) {
	$country = $_POST['country'];
}

if ($country != null) {
	$select_best_info_sql = "
		SELECT
			BI.PRODUCT_TYPE		AS PRODUCT_TYPE,
			
			BI.MIN_PRODUCT_PRICE	AS MIN_PRODUCT_PRICE,
			BI.MAX_PRODUCT_PRICE	AS MAX_PRODUCT_PRICE,
			BI.MIN_DISCOUNT			AS MIN_DISCOUNT,
			BI.MAX_DISCOUNT			AS MAX_DISCOUNT,
			BI.MIN_SALES_PRICE		AS MIN_SALES_PRICE,
			BI.MAX_SALES_PRICE		AS MAX_SALES_PRICE,
			
			BI.MIN_STOCK_QTY		AS MIN_STOCK_QTY,
			BI.MAX_STOCK_QTY		AS MAX_STOCK_QTY,
			BI.MIN_ORDER_QTY		AS MIN_ORDER_QTY,
			BI.MAX_ORDER_QTY		AS MAX_ORDER_QTY,
			BI.MIN_PRODUCT_QTY		AS MIN_PRODUCT_QTY,
			BI.MAX_PRODUCT_QTY		AS MAX_PRODUCT_QTY,
			
			BI.MIN_CREATE_DATE		AS MIN_CREATE_DATE,
			BI.MAX_CREATE_DATE		AS MAX_CREATE_DATE,
			BI.MIN_UPDATE_DATE		AS MIN_UPDATE_DATE,
			BI.MAX_UPDATE_DATE		AS MAX_UPDATE_DATE,
			
			BI.ORDER_COLUMN			AS ORDER_COLUMN,
			BI.ORDER_TYPE			AS ORDER_TYPE,
			
			BI.DISPLAY_CNT			AS DISPLAY_CNT
		FROM
			BEST_INFO BI
		WHERE
			COUNTRY = '".$country."'
	";
	
	$db->query($select_best_info_sql);
	
	$best_info = array();
	foreach($db->fetch() as $best_data) {
		$best_info = array(
			'product_type'			=>$best_data['PRODUCT_TYPE'],
			
			'min_product_price'		=>$best_data['MIN_PRODUCT_PRICE'],
			'max_product_price'		=>$best_data['MAX_PRODUCT_PRICE'],
			'min_discount'			=>$best_data['MIN_DISCOUNT'],
			'max_discount'			=>$best_data['MAX_DISCOUNT'],
			'min_sales_price'		=>$best_data['MIN_SALES_PRICE'],
			'max_sales_price'		=>$best_data['MAX_SALES_PRICE'],
			
			'min_stock_qty'			=>$best_data['MIN_STOCK_QTY'],
			'max_stock_qty'			=>$best_data['MAX_STOCK_QTY'],
			'min_order_qty'			=>$best_data['MIN_ORDER_QTY'],
			'max_order_qty'			=>$best_data['MAX_ORDER_QTY'],
			'min_product_qty'		=>$best_data['MIN_PRODUCT_QTY'],
			'max_product_qty'		=>$best_data['MAX_PRODUCT_QTY'],
			
			'min_create_date'		=>$best_data['MIN_CREATE_DATE'],
			'max_create_date'		=>$best_data['MAX_CREATE_DATE'],
			'min_update_date'		=>$best_data['MIN_UPDATE_DATE'],
			'max_update_date'		=>$best_data['MAX_UPDATE_DATE'],
			
			'order_column'			=>$best_data['ORDER_COLUMN'],
			'order_type'			=>$best_data['ORDER_TYPE'],
			
			'display_cnt'			=>$best_data['DISPLAY_CNT']
		);
	}
	
	if ($best_info != null && count($best_info) > 0) {
		$product_type			= $best_info['product_type'];
		
		$min_product_price		= $best_info['min_product_price'];
		$max_product_price		= $best_info['max_product_price'];
		$min_discount			= $best_info['min_discount'];
		$max_discount			= $best_info['max_discount'];
		$min_sales_price		= $best_info['min_sales_price'];
		$max_sales_price		= $best_info['max_sales_price'];
		
		$min_stock_qty			= $best_info['min_stock_qty'];
		$max_stock_qty			= $best_info['max_stock_qty'];
		$min_order_qty			= $best_info['min_order_qty'];
		$max_order_qty			= $best_info['max_order_qty'];
		$min_product_qty		= $best_info['min_product_qty'];
		$max_product_qty		= $best_info['max_product_qty'];
		
		$min_create_date		= $best_info['min_create_date'];
		$max_create_date		= $best_info['max_create_date'];
		$min_update_date		= $best_info['min_update_date'];
		$max_update_date		= $best_info['max_update_date'];

		$order_column			= $best_info['order_column'];
		$order_type				= $best_info['order_type'];
		
		$display_cnt			= $best_info['display_cnt'];

		$where = "1=1";
		$order = "";
		$limit = "";
		
		if ($product_type != null && $product_type != "ALL") {
			$where .= " AND (PRODUCT_TYPE = '".$product_type."') ";
		}
		
		if ($min_product_price != null || $max_product_price != null) {
			if ($min_product_price != null && $max_product_price == null) {
				$where .= " AND (PRICE >= ".$min_product_price.") ";
			} else if ($min_product_price == null && $max_product_price != null) {
				$where .= " AND (PRICE <= ".$max_product_price.") ";
			} else if ($min_product_price != null && $max_product_price != null) {
				$where .= " AND (PRICE BETWEEN ".$min_product_price." AND ".$max_product_price.") ";
			}
		}
		
		if ($min_discount != null || $max_discount != null) {
			if ($min_discount != null && $max_discount == null) {
				$where .= " AND (DISCOUNT >= ".$min_discount.") ";
			} else if ($min_discount == null && $max_discount != null) {
				$where .= " AND (DISCOUNT <= ".$max_discount.") ";
			} else if ($min_discount != null && $max_discount != null) {
				$where .= " AND (DISCOUNT BETWEEN ".$min_discount." AND ".$max_discount.") ";
			}
		}
		
		if ($min_sales_price != null || $max_sales_price != null) {
			if ($min_sales_price != null && $max_sales_price == null) {
				$where .= " AND (SALES_PRICE >= ".$min_sales_price.") ";
			} else if ($min_sales_price == null && $max_sales_price != null) {
				$where .= " AND (SALES_PRICE <= ".$max_sales_price.") ";
			} else if ($min_sales_price != null && $max_sales_price != null) {
				$where .= " AND (SALES_PRICE BETWEEN ".$min_sales_price." AND ".$max_sales_price.") ";
			}
		}
		
		if ($min_order_qty != null || $max_order_qty != null) {
			if ($min_order_qty != null && $max_order_qty == null) {
				$where .= " AND (ORDER_QTY >= ".$min_order_qty.") ";
			} else if ($min_order_qty == null && $max_order_qty != null) {
				$where .= " AND (ORDER_QTY <= ".$max_order_qty.") ";
			} else if ($min_order_qty != null && $max_order_qty != null) {
				$where .= " AND (ORDER_QTY BETWEEN ".$min_order_qty." AND ".$max_order_qty.") ";
			}
		}
		
		if ($min_stock_qty != null || $max_stock_qty != null) {
			if ($min_stock_qty != null && $max_stock_qty == null) {
				$where .= " AND (STOCK_QTY >= ".$min_stock_qty.") ";
			} else if ($min_stock_qty == null && $max_stock_qty != null) {
				$where .= " AND (STOCK_QTY <= ".$max_stock_qty.") ";
			} else if ($min_stock_qty != null && $max_stock_qty != null) {
				$where .= " AND (STOCK_QTY BETWEEN ".$min_stock_qty." AND ".$max_stock_qty.") ";
			}
		}
		
		if ($min_product_qty != null || $max_product_qty != null) {
			if ($min_product_qty != null && $max_product_qty == null) {
				$where .= " AND (PRODUCT_QTY >= ".$min_product_qty.") ";
			} else if ($min_product_qty == null && $max_product_qty != null) {
				$where .= " AND (PRODUCT_QTY <= ".$max_product_qty.") ";
			} else if ($min_product_qty != null && $max_product_qty != null) {
				$where .= " AND (PRODUCT_QTY BETWEEN ".$min_product_qty." AND ".$max_product_qty.") ";
			}
		}
		
		if ($min_create_date != null || $max_create_date != null) {
			$create_date = " DATE_FORMAT(CREATE_DATE,'%Y-%m-%d') ";
			if ($min_create_date != null && $max_create_date == null) {
				$where .= " AND (".$create_date." >= '".$min_create_date."') ";
			} else if ($min_create_date == null && $max_create_date != null) {
				$where .= " AND (".$create_date." <= '".$max_create_date."') ";
			} else if ($min_create_date != null && $max_create_date != null) {
				$where .= " AND (".$create_date." BETWEEN '".$min_create_date."' AND '".$max_create_date."') ";
			}
		}
		
		if ($min_update_date != null || $max_update_date != null) {
			$update_date = " DATE_FORMAT(UPDATE_DATE,'%Y-%m-%d') ";
			if ($min_update_date != null && $max_update_date == null) {
				$where .= " AND (".$update_date." >= '".$min_update_date."') ";
			} else if ($min_update_date == null && $max_update_date != null) {
				$where .= " AND (".$update_date." <= '".$max_update_date."') ";
			} else if ($min_update_date != null && $max_update_date != null) {
				$where .= " AND (".$update_date." BETWEEN '".$min_update_date."' AND '".$max_update_date."') ";
			}
		}
		
		if (strlen($where) > 0) {
			$where = " WHERE ".$where;
		}
		
		if ($order_column != null && $order_type != null) {
			$order = " ORDER BY ".$order_column." ".$order_type;
		}
		
		if ($display_cnt != null) {
			$limit = " LIMIT 0,".$display_cnt;
		}
		
		$select_best_product_sql = "
			SELECT
				*
			FROM
				(
					SELECT
						PR.IDX						AS PRODUCT_IDX,
						PR.PRODUCT_TYPE				AS PRODUCT_TYPE,
						PR.SET_TYPE					AS SET_TYPE,
						PR.PRODUCT_NAME				AS PRODUCT_NAME,
						(
							SELECT
								S_PI.IMG_LOCATION
							FROM
								PRODUCT_IMG S_PI
							WHERE
								S_PI.PRODUCT_IDX = PR.IDX AND
								IMG_TYPE = 'P' AND
								IMG_SIZE = 'M'
							ORDER BY
								S_PI.IDX ASC
							LIMIT
								0,1
						)							AS IMG_LOCATION,
						PR.PRICE_".$country."		AS PRICE,
						PR.DISCOUNT_".$country."	AS DISCOUNT,
						PR.SALES_PRICE_".$country."	AS SALES_PRICE,
						PR.COLOR					AS COLOR,
						(
							SELECT
								IFNULL(
									SUM(S_PS.STOCK_QTY),0
								)
							FROM
								PRODUCT_STOCK S_PS
							WHERE
								S_PS.PRODUCT_IDX = PR.IDX AND
								S_PS.STOCK_DATE <= NOW()
						)							AS STOCK_QTY,
						(
							(
								SELECT
									IFNULL(
										SUM(S_OP.PRODUCT_QTY),0
									)
								FROM
									ORDER_PRODUCT S_OP
								WHERE
									S_OP.PRODUCT_IDX = PR.IDX AND
									S_OP.PRODUCT_TYPE NOT IN ('V','D') AND
									S_OP.PRODUCT_QTY > 0
							) + (
								SELECT
									IFNULL(
										SUM(S_OE.PRODUCT_QTY),0
									)
								FROM
									ORDER_PRODUCT_EXCHANGE S_OE
								WHERE
									S_OE.PRODUCT_IDX AND
									S_OE.ORDER_STATUS = 'OEP' AND
									S_OE.STOCK_FLG = TRUE
							) + (
								SELECT
									IFNULL(
										SUM(S_OR.PRODUCT_QTY),0
									)
								FROM
									ORDER_PRODUCT_REFUND S_OR
								WHERE
									S_OR.PRODUCT_IDX = PR.IDX AND
									S_OR.ORDER_STATUS = 'ORP' AND
									S_OR.STOCK_FLG = TRUE
							)
						)							AS ORDER_QTY,
						(
							(
								SELECT
									IFNULL(
										SUM(S_OP.PRODUCT_PRICE),0
									)
								FROM
									ORDER_PRODUCT S_OP
								WHERE
									S_OP.PRODUCT_IDX = PR.IDX AND
									S_OP.ORDER_STATUS NOT IN ('OCC','OEX','OEE','OEP','ORF','ORE','ORP') AND
									S_OP.PRODUCT_TYPE NOT IN ('V','D') AND
									S_OP.PRODUCT_QTY > 0
							)
						)							AS ORDER_PRICE,
						(
							(
								SELECT
									IFNULL(
										SUM(S_PS.STOCK_QTY),0
									)
								FROM
									PRODUCT_STOCK S_PS
								WHERE
									S_PS.PRODUCT_IDX = PR.IDX AND
									S_PS.STOCK_DATE <= NOW()
							) - (
								(
									SELECT
										IFNULL(
											SUM(S_OP.PRODUCT_QTY),0
										)
									FROM
										ORDER_PRODUCT S_OP
									WHERE
										S_OP.PRODUCT_IDX = PR.IDX AND
										S_OP.PRODUCT_TYPE NOT IN ('V','D') AND
										S_OP.PRODUCT_QTY > 0
								) + (
									SELECT
										IFNULL(
											SUM(S_OE.PRODUCT_QTY),0
										)
									FROM
										ORDER_PRODUCT_EXCHANGE S_OE
									WHERE
										S_OE.PRODUCT_IDX = PR.IDX AND
										S_OE.ORDER_STATUS = 'OEP' AND
										S_OE.STOCK_FLG = TRUE
								) + (
									SELECT
										IFNULL(
											SUM(S_OR.PRODUCT_QTY),0
										)
									FROM
										ORDER_PRODUCT_REFUND S_OR
									WHERE
										S_OR.PRODUCT_IDX = PR.IDX AND
										S_OR.ORDER_STATUS = 'ORP' AND
										S_OR.STOCK_FLG = TRUE
								)
							)
						)							AS PRODUCT_QTY
					FROM
						SHOP_PRODUCT PR
					WHERE
						PR.SALE_FLG = TRUE AND
						PR.INDP_FLG = FALSE AND
						PR.DEL_FLG = FALSE
				) AS BEST
			".$where."
			".$order."
			".$limit."
		";
		
		$db->query($select_best_product_sql);
		
		$display_num = 1;
		foreach($db->fetch() as $product_data) {
			$product_idx = $product_data['PRODUCT_IDX'];
			
			$whish_flg = false;
			if ($member_idx > 0) {
				$whish_count = $db->count("WHISH_LIST","MEMBER_IDX = ".$member_idx." AND PRODUCT_IDX = ".$product_idx." AND DEL_FLG = FALSE");
				if ($whish_count > 0) {
					$whish_flg = true;
				}
			}
			
			$product_color = array();
			
			$tmp_product_color = getProductColor($db,$product_idx);
			if (count($tmp_product_color) > 0) {
				for ($i=0; $i<count($tmp_product_color); $i++) {
					if ($tmp_product_color[$i]['stock_status'] == 'STIN') {
						array_push($product_color,$tmp_product_color[$i]);
					}
				}
			}
			
			$json_result['data'][] = array(
				'display_num'		=>$display_num,
				'product_idx'		=>$product_data['PRODUCT_IDX'],
				'product_type'		=>$product_data['PRODUCT_TYPE'],
				'set_type'			=>$product_data['SET_TYPE'],
				'product_name'		=>$product_data['PRODUCT_NAME'],
				'img_location'		=>$product_data['IMG_LOCATION'],
				'price'				=>number_format($product_data['PRICE']),
				'discount'			=>$product_data['DISCOUNT'],
				'sales_price'		=>number_format($product_data['SALES_PRICE']),
				'color'				=>$product_data['COLOR'],
				
				'product_color'		=>$product_color,
				'whish_flg'			=>$whish_flg
			);
			
			$display_num++;
		}
	}
}

?>