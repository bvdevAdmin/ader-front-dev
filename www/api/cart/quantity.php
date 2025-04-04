<?php
/*
 +=============================================================================
 | 
 | 쇼핑백 화면 - 쇼핑백 상품 수량 변경
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.17
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

if (!isset($_SERVER['HTTP_COUNTRY']) && !isset($_SESSION['MEMBER_IDX'])) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'], 'MSG_F_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

/* 쇼핑백 상품 수량 변경 */
if ($basket_idx != null) {
	/* 1. 수량을 변경하려는 상품의 쇼핑백 존재 여부 체크 */
	$cnt_basket = $db->count("BASKET_INFO","IDX = ? AND COUNTRY = ? AND MEMBER_IDX = ? AND DEL_FLG = FALSE ",array($basket_idx,$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']));
	if ($cnt_basket == 0) {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0049',array());
		
		echo json_encode($json_result);
		exit;
	}
	
	/* 3. 수량을 변경하려는 상품의 세트상품 여부 체크 */
	$cnt_set = $db->count("BASKET_INFO","COUNTRY = ? AND MEMBER_IDX = ? AND PARENT_IDX = ? AND DEL_FLG = FALSE",array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'],$basket_idx));
	if ($cnt_set > 0) {
		/* 쇼핑백 수량 변경 처리 - 세트 상품 */
		
		/* 4. 수량을 변경하려는 쇼핑백 상품의 재고정보 조회 */
		$basket_limit = getBasket_limit($db,"S",$basket_idx);
		if (sizeof($basket_limit) > 0) {
			$cnt_soldout = 0;
			
			foreach($basket_limit as $limit) {
				/* 5-1. 쇼핑백 수량이 입고 기준 구매 가능 수량을 초과하는 경우 */
				if ($basket_qty > $limit['limit_qty']) {
					$cnt_soldout++;
				}
				
				/* 5-2. 쇼핑백 수량이 WCC 상품 재고를 초과하는 경우 */
				if ($basket_qty > $limit['remain_qty']) {
					$cnt_soldout++;
				}
				
				/* 5-3. 쇼핑백 수량이 옵션별 구매 제한 수량을 초과하는 경우 */
				if ($basket_qty > $limit['option_qty']) {
					$cnt_soldout++;
				}
			}
			
			if ($cnt_soldout > 0) {
				$json_result['code'] = 302;
				$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0128',array());
				
				echo json_encode($json_result);
				exit;
			} else {
				/* 6. 일반상품 쇼핑백 상품 수량 변경 */
				$db->update(
					"BASKET_INFO",
					array(
						'PRODUCT_QTY'	=>$basket_qty,
						'UPDATER'		=>$_SESSION['MEMBER_ID'],
						'UPDATE_DATE'	=>NOW()
					),
					"IDX = ?",
					array($basket_idx)
				);
				
				$db->update(
					"BASKET_INFO",
					array(
						'PRODUCT_QTY'	=>$basket_qty,
						'UPDATER'		=>$_SESSION['MEMBER_ID'],
						'UPDATE_DATE'	=>NOW()
					),
					"PARENT_IDX = ?",
					array($basket_idx)
				);
			}
		} else {
			$json_result['code'] = 305;
			$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0024',array());
			
			echo json_encode($json_result);
			exit;
		}
	} else {
		/* 쇼핑백 수량 변경 처리 - 일반 상품 */
		
		/* 4. 수량을 변경하려는 쇼핑백 상품의 재고정보 조회 */
		$basket_limit = getBasket_limit($db,"B",$basket_idx);
		if (sizeof($basket_limit) > 0) {
			/* 5-1. 쇼핑백 수량이 입고 기준 구매 가능 수량을 초과하는 경우 */
			if ($basket_qty > $basket_limit[0]['limit_qty']) {
				$json_result['code'] = 302;
				$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0126',array());
				
				echo json_encode($json_result);
				exit;
			}
			
			/* 5-2. 쇼핑백 수량이 WCC 상품 재고를 초과하는 경우 */
			if ($basket_qty > $basket_limit[0]['remain_qty']) {
				$json_result['code'] = 303;
				$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0126',array());
				
				echo json_encode($json_result);
				exit;
			}
			
			/* 5-3. 쇼핑백 수량이 옵션별 구매 제한 수량을 초과하는 경우 */
			if ($basket_qty > $basket_limit[0]['option_qty']) {
				$json_result['code'] = 304;
				$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0127',array());
				
				echo json_encode($json_result);
				exit;
			}
			
			/* 6. 일반상품 쇼핑백 상품 수량 변경 */
			$db->update(
				"BASKET_INFO",
				array(
					'PRODUCT_QTY'	=>$basket_qty,
					
					'UPDATER'		=>$_SESSION['MEMBER_ID'],
					'UPDATE_DATE'	=>NOW()
				),
				"IDX = ?",
				array($basket_idx)
			);
		} else {
			$json_result['code'] = 305;
			$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0024',array());
			
			echo json_encode($json_result);
			exit;
		}
	}
	
	/* 7. 현재 쇼핑백에 담겨있는 상품 수량 반환처리 */
	$json_result['data'] = array(
		'basket_cnt'		=>$db->count("BASKET_INFO","COUNTRY = ? AND MEMBER_IDX = ? AND DEL_FLG = FALSE",array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']))
	);
}

/* 수량을 변경하려는 쇼핑백 상품의 재고정보 조회 */
function getBasket_limit($db,$product_type,$basket_idx) {
	$basket_limit = null;
	
	$where = "";
	if ($product_type == "B") {
		$where .= "BI.IDX = ?";
	} else {
		$where .= "BI.PARENT_IDX = ?";
	}
	
	$select_basket_qty_sql = "
		SELECT
			IFNULL(
				PO.LIMIT_QTY_FLG,FALSE
			)					AS LIMIT_QTY_FLG,
			IFNULL(
				PO.LIMIT_OPTION_QTY,0
			)					AS OPTION_QTY,
			IFNULL(
				V_ST.REMAIN_WCC_QTY,0
			)					AS REMAIN_QTY,
			IFNULL(
				V_ST.PURCHASEABLE_QTY,0
			)					AS LIMIT_QTY
		FROM
			BASKET_INFO BI
			
			LEFT JOIN PRODUCT_OPTION PO ON
			BI.PRODUCT_IDX = PO.PRODUCT_IDX AND
			BI.OPTION_IDX = PO.OPTION_IDX
			
			LEFT JOIN V_STOCK V_ST ON
			BI.PRODUCT_IDX = V_ST.PRODUCT_IDX AND
			BI.OPTION_IDX = V_ST.OPTION_IDX
		WHERE
			".$where."
	";
	
	$db->query($select_basket_qty_sql,array($basket_idx));
	
	foreach($db->fetch() as $data) {
		$option_qty = 9;
		if ($data['LIMIT_QTY_FLG'] == true) {
			$option_qty = $data['OPTION_QTY'];
		}

		$basket_limit[] = array(
			'option_qty'		=>$option_qty,
			'remain_qty'		=>$data['REMAIN_QTY'],
			'limit_qty'			=>$data['LIMIT_QTY']
		);
	}
	
	return $basket_limit;
}

?>