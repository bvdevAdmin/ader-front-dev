<?php
/*
 +=============================================================================
 | 
 | 상품 상세 - 상품 상세 정보 조회 // /var/www/www/api/product/get.php
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.17
 | 최종 수정    : 양한빈
 | 최종 수정일	: 2024.05.07
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

include $_CONFIG['PATH']['API'].'_legacy/common/check.php';

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

/** 최근 본 상품 저장을 위해 **/
if (isset($_SESSION['MEMBER_IDX'])) {
    $db->insert(
        "SHOP_GOODS_RECENTLYVIEW",
        array(
            'ID'			=>$_SESSION['MEMBER_ID'],
            'GOODS_NO'		=>$product_idx,
            'REG_DATE'		=>NOW()
        ),
        "ID = ? AND GOODS_NO = ?",
        array($_SESSION['MEMBER_ID'],$product_idx)
    );
}

if (isset($_SERVER['HTTP_COUNTRY']) && isset($product_idx)) {
	$db->begin_transaction();

	try {
		$check_result = checkProductSaleFlg($db,"PRD",$product_idx);
		
		$tmp_flg = false;
		if (isset($_POST['tmp_flg'])) {
			$tmp_flg = $_POST['tmp_flg'];
		}
		
		if ($check_result['result'] == false && $tmp_flg == false) {
			$json_result['code'] = 301;
			$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0072', array());
			
			echo json_encode($json_result);
			exit;
		}
		
		$table = "
			SHOP_PRODUCT PR
			
			LEFT JOIN (
				SELECT
					S_WL.PRODUCT_IDX	AS PRODUCT_IDX,
					COUNT(S_WL.IDX)		AS CNT_WISH
				FROM
					WHISH_LIST S_WL
				WHERE
					S_WL.COUNTRY = ? AND
					S_WL.MEMBER_IDX = ? AND
					S_WL.DEL_FLG = FALSE
				GROUP BY
					S_WL.PRODUCT_IDX
			) AS J_WL ON
			PR.IDX = J_WL.PRODUCT_IDX
		";
		
		$select_shop_product_sql = "
			SELECT
				PR.IDX					AS PRODUCT_IDX,
				PR.PRODUCT_TYPE			AS PRODUCT_TYPE,
				PR.SET_TYPE				AS SET_TYPE,
				PR.PRODUCT_NAME			AS PRODUCT_NAME,
				PR.COLOR				AS COLOR,
				
				PR.PRICE_KR				AS PRICE_KR,
				PR.DISCOUNT_KR			AS DISCOUNT_KR,
				PR.SALES_PRICE_KR		AS SALES_PRICE_KR,
				
				PR.PRICE_EN				AS PRICE_EN,
				PR.DISCOUNT_EN			AS DISCOUNT_EN,
				PR.SALES_PRICE_EN		AS SALES_PRICE_EN,
				
				PR.DETAIL_KR			AS DETAIL_KR,
				PR.CARE_KR				AS CARE_KR,
				PR.MATERIAL_KR			AS MATERIAL_KR,
				
				PR.DETAIL_EN			AS DETAIL_EN,
				PR.CARE_EN				AS CARE_EN,
				PR.MATERIAL_EN			AS MATERIAL_EN,
				
				PR.REFUND_MSG_FLG		AS REFUND_MSG_FLG,
				PR.REFUND_MSG_KR		AS REFUND_MSG_KR,
				PR.REFUND_MSG_EN		AS REFUND_MSG_EN,
				
				PR.REFUND_FLG			AS REFUND_FLG,
				PR.REFUND_KR			AS REFUND_KR,
				PR.REFUND_EN			AS REFUND_EN,
				
				PR.RELEVANT_IDX			AS RELEVANT_IDX,
				PR.SOLD_OUT_FLG			AS SOLD_OUT_FLG,
				
				PR.REORDER_FLG			AS REORDER_FLG,
				
				PR.IMG_SEQ				AS IMG_SEQ,

				IFNULL(
					J_WL.CNT_WISH,0
				)						AS CNT_WISH
			FROM
				".$table."
			WHERE
				PR.IDX = ? AND
				PR.SALE_FLG = TRUE AND
				PR.DEL_FLG = FALSE
		";
		
		$db->query($select_shop_product_sql,array($_SERVER['HTTP_COUNTRY'],$member_idx,$product_idx));
		
		foreach($db->fetch() as $data) {
			$product_idx	= $data['PRODUCT_IDX'];
			
			$product_type	= $data['PRODUCT_TYPE'];
			$set_type		= $data['SET_TYPE'];
			
			$img_T = array();
			$img_M = array();
			
			/* 1. 상품 이미지 조회 */
			$product_img = getProduct_img($db,$data['IMG_SEQ'],$product_idx);
			
			/* 일반상품 옵션정보 조회 */
			$option_info = array();
			if ($product_type == "B") {
				$option_info = getProduct_option($db,$product_idx);
			}
			
			/* 상품 컬러/사이즈 정보 조회 */
			$product_color = array();
			$product_size = array();
			
			switch ($product_type) {
				case "B" :
					$product_color	= getProduct_color_detail($db,array($product_idx));
					$product_size	= getProduct_size_B($db,array($product_idx));
					
					break;
				
				case "S" :
					$product_size	= getProduct_size_S($db,array($product_idx));
					
					break;
			}
			
			$stock_status = null;
			
			if ($data['SOLD_OUT_FLG'] == true) {
				$stock_status = "STSO";
			} else {
				if (count($product_size) > 0) {
					$cnt_soldout = 0;
					
					$tmp_size = $product_size[$product_idx];
					
					if ($product_type == "B") {
						for ($i=0; $i<count($tmp_size); $i++) {
							if ($tmp_size[$i]['stock_status'] == "STSO") {
								$cnt_soldout++;
							}
						}
						
						if (count($tmp_size) == $cnt_soldout) {
							$stock_status = "STSO";
						}
					} else if ($product_type == "S") {
						foreach($tmp_size as $tmp) {
							if (isset($tmp['set_option'])) {
								$set_option = $tmp['set_option'];
								
								foreach($set_option as $set) {
									if ($set['stock_status'] == "STSO") {
										$cnt_soldout++;
									}
								}
							}
						}
						
						if ($cnt_soldout > 0) {
							$stock_status = "STSO";
						}
					}
				}
			}
			
			/* 위시리스트 선택 여부 조회 */
			$wish_flg = false;
			if ($member_idx > 0) {
				if ($data['CNT_WISH'] > 0) {
					$wish_flg = true;
				}
			}

			$price			= number_format($data['PRICE_'.$_SERVER['HTTP_COUNTRY']]);
			$discount		= $data['DISCOUNT_'.$_SERVER['HTTP_COUNTRY']];
			$sales_price	= number_format($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']]);

			if ($_SERVER['HTTP_COUNTRY'] == "EN") {
				$price			= number_format($data['PRICE_'.$_SERVER['HTTP_COUNTRY']],1);
				$sales_price	= number_format($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']],1);
			}

			if (!isset($product_size[$data['PRODUCT_IDX']])) {
				$json_result['code'] = 300;
				$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0029',array());
				
				echo json_encode($json_result);
				exit;
			}
			
			$json_result['data'] = array(
				'product_idx'		=>$data['PRODUCT_IDX'],
				'product_type'		=>$data['PRODUCT_TYPE'],
				'set_type'			=>$data['SET_TYPE'],
				'img_main'			=>$product_img,
				'product_name'		=>$data['PRODUCT_NAME'],
				'color'				=>$data['COLOR'],
				
				'price'				=>$price,
				'discount'			=>$discount,
				'sales_price'		=>$sales_price,
				
				'material'			=>$data['MATERIAL_'.$_SERVER['HTTP_COUNTRY']],
				'detail'			=>$data['DETAIL_'.$_SERVER['HTTP_COUNTRY']],
				'care'				=>$data['CARE_'.$_SERVER['HTTP_COUNTRY']],
				
				'refund_msg_flg'	=>$data['REFUND_MSG_FLG'],
				'refund_msg'		=>$data['REFUND_MSG_'.$_SERVER['HTTP_COUNTRY']],
				'refund'			=>$data['REFUND_'.$_SERVER['HTTP_COUNTRY']],
				
				'relevant_idx'		=>$data['RELEVANT_IDX'],
				'sold_out_flg'		=>$data['SOLD_OUT_FLG'],
				'reorder_flg'		=>$data['REORDER_FLG'],
				
				'option_info'		=>$option_info,
				'product_color'		=>$product_color,
				'product_size'		=>$product_size[$data['PRODUCT_IDX']],
				'stock_status'		=>$stock_status,
				'whish_flg'			=>$wish_flg
			);
		}
	} catch (mysqli_sql_exception $e) {
		print_r($e);

		$json_result['code'] = 300;
		$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0029',array());
		
		echo json_encode($json_result);
		exit;
	}
}

/* 1. 상품 이미지 조회 */
function getProduct_img($db,$img_seq,$product_idx) {
	$product_img = array();
	
	$seq = array("P","O","D");
	if ($img_seq != null && strlen($img_seq) > 0) {
		$seq = explode(",",$img_seq);
	}

	$display_num = 0;

	foreach($seq as $value) {
		$select_product_img_main_sql = "
			SELECT
				PI.IDX				AS IMG_IDX,
				PI.IMG_LOCATION		AS IMG_LOCATION,
				PI.IMG_URL			AS IMG_URL
			FROM
				PRODUCT_IMG PI
			WHERE
				PI.PRODUCT_IDX	= ? AND
				PI.IMG_TYPE		= ? AND
				PI.IMG_TYPE NOT LIKE 'T%' AND
				PI.IMG_SIZE		= 'L' AND
				PI.DEL_FLG		= FALSE
			ORDER BY
				PI.IDX ASC
		";
		
		$db->query($select_product_img_main_sql,array($product_idx,$value));
		
		foreach($db->fetch() as $data) {
			$display_num++;

			$product_img[] = array(
				'img_idx'		=>$data['IMG_IDX'],
				'display_num'	=>$display_num,
				'img_location'	=>$data['IMG_LOCATION'],
				'img_url'		=>$data['IMG_URL']
			);
		}
	}
	
	return $product_img;
}

/* 1-1. 상품 이미지 조회 - 썸네일 이미지 */
function getProduct_img_T($db,$product_idx) {
	$img_thumb = null;
	
	$select_product_img_thumb_sql = "
		(
			SELECT
				S_PI.IMG_LOCATION
			FROM
				PRODUCT_IMG S_PI
			WHERE
				S_PI.PRODUCT_IDX = ? AND
				S_PI.IMG_TYPE = 'O' AND
				S_PI.IMG_SIZE = 'S' AND
				S_PI.DEL_FLG = FALSE
			ORDER BY
				S_PI.IDX DESC
			LIMIT
				0,1
		) UNION (
			SELECT
				S_PI.IMG_LOCATION
			FROM
				PRODUCT_IMG S_PI
			WHERE
				S_PI.PRODUCT_IDX = ? AND
				S_PI.IMG_TYPE = 'P' AND
				S_PI.IMG_SIZE = 'S' AND
				S_PI.DEL_FLG = FALSE
			ORDER BY
				IDX DESC
			LIMIT
				0,1
		)
	";
	
	$db->query($select_product_img_thumb_sql,array($product_idx,$product_idx));
	
	foreach($db->fetch() as $data) {
		$img_thumb[] = array(
			'display_num'	=>0,
			'img_location'	=>$data['IMG_LOCATION']
		);
	}
	
	return $img_thumb;
	
}

function getProduct_option($db,$product_idx) {
	$option_info = array();
	
	$select_shop_option_sql = "
		SELECT
			OO.IDX				AS OPTION_IDX,
			OO.OPTION_NAME		AS OPTION_NAME
		FROM
			SHOP_OPTION OO
		WHERE
			OO.PRODUCT_IDX = ?
		ORDER BY
			OO.IDX ASC
	";
	
	$db->query($select_shop_option_sql,array($product_idx));
	
	foreach($db->fetch() as $data) {
		$option_info[] = array(
			'option_idx'		=>$data['OPTION_IDX'],
			'option_name'		=>$data['OPTION_NAME']
		);
	}
	
	return $option_info;
}

function getProduct_color_detail($db,$product_idx) {
	$product_color = array();
	
	$select = "
		PR.IDX			AS PRODUCT_IDX,
		PR.COLOR		AS COLOR,
		PR.COLOR_RGB	AS COLOR_RGB,
		
		IFNULL(
			J_ST.LIMIT_QTY,0
		)				AS LIMIT_QTY
	";
	
	$table = "
		SHOP_PRODUCT PR
		
		LEFT JOIN (
			SELECT
				V_ST.PRODUCT_IDX	AS PRODUCT_IDX,
				SUM(
					V_ST.PURCHASEABLE_QTY
				)					AS LIMIT_QTY
			FROM
				V_STOCK V_ST
			GROUP BY
				V_ST.PRODUCT_IDX
		) AS J_ST ON
		PR.IDX = J_ST.PRODUCT_IDX
	";
	
	$param_bind = array();
	
	if (isset($_SESSION['MEMBER_IDX'])) {
		$select .= "
			,IFNULL(J_RE.CNT_REORDER,0)		AS CNT_REORDER
		";
		
		$table .= "
			LEFT JOIN (
				SELECT
					S_RE.PRODUCT_IDX		AS PRODUCT_IDX,
					COUNT(S_RE.PRODUCT_IDX)	AS CNT_REORDER
				FROM
					REORDER_INFO S_RE
				WHERE
					S_RE.COUNTRY = ? AND
					S_RE.MEMBER_IDX = ?
				GROUP BY
					S_RE.PRODUCT_IDX
			) AS J_RE ON
			PR.IDX = J_RE.PRODUCT_IDX
		";
		
		array_push($param_bind,$_SERVER['HTTP_COUNTRY']);
		array_push($param_bind,$_SESSION['MEMBER_IDX']);
	} else {
		$select .= "
			,0								AS CNT_REORDER
		";
	}
	
	$where = "
		PR.SALE_FLG = TRUE AND
		PR.STYLE_CODE IN (
			SELECT
				S_PR.STYLE_CODE
			FROM
				SHOP_PRODUCT S_PR
			WHERE
				S_PR.IDX IN (".implode(',',array_fill(0,count($product_idx),'?')).")
		)
	";
	
	$param_bind = array_merge($param_bind,$product_idx);
	
	$select_product_color_sql = "
		SELECT
			".$select."
		FROM
			".$table."
		WHERE
			".$where."
	";
	
	$db->query($select_product_color_sql,$param_bind);
	
	foreach($db->fetch() as $data) {
		$stock_status = "";
		$reorder_flg = false;
		
		if ($data['LIMIT_QTY'] > 0) {
			$stock_status = "STIN";	//재고 있음 (Stock in)
		} else {
			$stock_status = "STSO";	//재고 없음(사선)		→ 증가 예정 재고 없음 (Stock sold out)
		}
		
		$reorder_flg = false;
		if ($data['CNT_REORDER'] > 0) {
			$reorder_flg = true;
		}
		
		$product_color[] = array(
			'product_idx'		=>$data['PRODUCT_IDX'],
			'color'				=>$data['COLOR'],
			'color_rgb'			=>$data['COLOR_RGB'],
			
			'stock_status'		=>$stock_status,
			'reorder_flg'		=>$reorder_flg
		);
	}
	
	return $product_color;
}

/** 사이즈 가이드 **/
$data = $json_result['data'];
include 'size.php'; 

$json_result['sizeguide'] = $json_result['data'];
$json_result['data'] = $data;
