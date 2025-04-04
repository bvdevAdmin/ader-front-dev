<?php
/*
 +=============================================================================
 | 
 | A/S 신청내용 개별 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.04.23
 | 최종 수정일	: 
 | 버전		: 1.1
 | 설명		: 
 | 
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	if (isset($as_idx)) {
		$cnt_as = $db->count("MEMBER_AS","IDX = ? AND COUNTRY = ? AND MEMBER_IDX = ?",array($as_idx,$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']));
		if ($cnt_as > 0) {
			/* 임시 A/S 결제정보 삭제 처리 */
			$db->delete("AS_PAYMENT","AS_IDX = ? AND PAYMENT_STATUS = 'PWT'",array($as_idx));
			
			$cnt_as = $db->count("MEMBER_AS","IDX = ? AND COUNTRY = ? AND MEMBER_IDX = ?",array($as_idx,$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']));
			if ($cnt_as > 0) {
				$select_product = "
						NULL							AS IMG_LOCATION,
						0								AS PRODUCT_IDX,
						'-'								AS PRODUCT_NAME,
						'-'								AS COLOR,
						'-'								AS COLOR_RGB,
						'-'								AS BARCODE,
						'-'								AS OPTION_NAME,
						
						'0'								AS PRICE_KR,
						'0'								AS DISCOUNT_KR,
						'0'								AS SALES_PRICE_KR,
						
						'0'								AS PRICE_EN,
						'0'								AS DISCOUNT_EN,
						'0'								AS SALES_PRICE_EN,
				";

				$cnt_barcode = $db->count("SHOP_OPTION","BARCODE = (SELECT BARCODE FROM MEMBER_AS WHERE IDX = ?)",array($as_idx));
				if ($cnt_barcode > 0) {
					$select_product = "
						J_PI.IMG_LOCATION				AS IMG_LOCATION,
						MA.PRODUCT_IDX					AS PRODUCT_IDX,
						PR.PRODUCT_NAME					AS PRODUCT_NAME,
						PR.COLOR						AS COLOR,
						PR.COLOR_RGB					AS COLOR_RGB,
						IFNULL(
							MA.BARCODE,'-'
						)								AS BARCODE,
						MA.OPTION_NAME					AS OPTION_NAME,
						
						PR.PRICE_KR						AS PRICE_KR,
						PR.DISCOUNT_KR					AS DISCOUNT_KR,
						PR.SALES_PRICE_KR				AS SALES_PRICE_KR,
						
						PR.PRICE_EN						AS PRICE_EN,
						PR.DISCOUNT_EN					AS DISCOUNT_EN,
						PR.SALES_PRICE_EN				AS SALES_PRICE_EN,
					";
				}

				$select_member_as_sql = "
					SELECT
						MA.IDX							AS AS_IDX,
						MA.AS_CODE						AS AS_CODE,
						DATE_FORMAT(
							MA.CREATE_DATE,
							'%Y.%m.%d'
						)								AS CREATE_DATE,
						MA.AS_STATUS					AS AS_STATUS,

						MA.BLUEMARK_FLG					AS BLUEMARK_FLG,
						BL.PURCHASE_MALL				AS PURCHASE_MALL,
						MA.SERIAL_CODE					AS SERIAL_CODE,
						BL.REG_DATE						AS REG_DATE,
						
						AC.TXT_CATEGORY_KR				AS TXT_CATEGORY_KR,
						AC.TXT_CATEGORY_EN				AS TXT_CATEGORY_EN,
						
						".$select_product."

						MA.AS_CONTENTS					AS AS_CONTENTS,

						MA.AS_REPAIR_TYPE				AS AS_REPAIR_TYPE,
						MA.AS_REPAIR_IDX				AS AS_REPAIR_IDX,
						IFNULL(
							AR.REPAIR_DESC_KR,
							'-'
						)								AS REPAIR_DESC_KR,
						IFNULL(
							AR.REPAIR_DESC_EN,
							'-'
						)								AS REPAIR_DESC_EN,
						
						MA.AS_PRICE						AS AS_PRICE,
						MA.AS_PRICE_FLG					AS AS_PRICE_FLG,
						
						J_AP.PG_PAYMENT					AS PG_PAYMENT,
						J_AP.PG_DATE					AS PG_DATE,
						J_AP.PG_PRICE					AS PG_PRICE,
						J_AP.PG_RECEIPT_URL				AS PG_RECEIPT_URL,

						J_AD.PG_PAYMENT					AS D_PG_PAYMENT,
						
						DATE_FORMAT(
							MA.COMPLETION_DATE,
							'%Y.%m.%d'
						)								AS COMPLETION_DATE,
						
						MA.HOUSING_IDX					AS HOUSING_IDX,
						MA.HOUSING_TYPE					AS HOUSING_TYPE,
						HC.COMPANY_NAME					AS HOUSING_COMPANY,
						MA.HOUSING_NUM					AS HOUSING_NUM,
						IFNULL(
							DATE_FORMAT(
								MA.HOUSING_START_DATE,
								'%Y.%m.%d'
							),'-'
						)								AS HOUSING_START_DATE,
						IFNULL(
							DATE_FORMAT(
								MA.HOUSING_END_DATE,
								'%Y.%m.%d'
							),'-'
						)								AS HOUSING_END_DATE,
						
						DC.COMPANY_NAME					AS COMPANY_NAME,
						MA.DELIVERY_NUM					AS DELIVERY_NUM,
						
						MA.DELIVERY_STATUS				AS DELIVERY_STATUS,
						IFNULL(
							DATE_FORMAT(
								MA.DELIVERY_START_DATE,
								'%Y.%m.%d'
							),'-'
						)								AS DELIVERY_START_DATE,
						IFNULL(
							DATE_FORMAT(
								MA.DELIVERY_END_DATE,
								'%Y.%m.%d'
							),'-'
						)								AS DELIVERY_END_DATE,
						
						TO_IDX							AS TO_IDX,
						TO_PLACE						AS TO_PLACE,
						TO_NAME							AS TO_NAME,
						TO_MOBILE						AS TO_MOBILE,
						TO_ZIPCODE						AS TO_ZIPCODE,
						TO_ROAD_ADDR					AS TO_ROAD_ADDR,
						TO_DETAIL_ADDR					AS TO_DETAIL_ADDR,

						ORDER_MEMO						AS ORDER_MEMO,

						MA.COMPLETE_FLG					AS COMPLETE_FLG,
						IF(MA.AS_STATUS = 'ACP', DATE_FORMAT(
							MA.UPDATE_DATE,
							'%Y.%m.%d'
						),NULL)							AS AS_COMPLETE_DATE
					FROM
						MEMBER_AS MA
						
						LEFT JOIN AS_CATEGORY AC ON
						MA.AS_CATEGORY_IDX = AC.IDX
						
						LEFT JOIN AS_REPAIR AR ON
						MA.AS_REPAIR_IDX = AR.IDX

						LEFT JOIN (
							SELECT
								S_AP.AS_IDX				AS AS_IDX,
								S_AP.PG_PAYMENT			AS PG_PAYMENT,
								S_AP.PG_DATE			AS PG_DATE,
								S_AP.PG_PRICE			AS PG_PRICE,
								S_AP.PG_RECEIPT_URL		AS PG_RECEIPT_URL
							FROM
								AS_PAYMENT S_AP
							WHERE
								S_AP.PAYMENT_TYPE = 'P' AND
								S_AP.PG_STATUS = 'DONE'
						) AS J_AP ON
						MA.IDX = J_AP.AS_IDX

						LEFT JOIN (
							SELECT
								S_AD.AS_IDX				AS AS_IDX,
								S_AD.PG_PAYMENT			AS PG_PAYMENT
							FROM
								AS_PAYMENT S_AD
							WHERE
								S_AD.PAYMENT_TYPE = 'D' AND
								S_AD.PG_STATUS = 'DONE'
						) AS J_AD ON
						MA.IDX = J_AD.AS_IDX
						
						LEFT JOIN BLUEMARK_INFO BI ON
						MA.SERIAL_CODE = BI.SERIAL_CODE

						LEFT JOIN BLUEMARK_LOG BL ON
						BI.IDX = BL.BLUEMARK_IDX

						LEFT JOIN SHOP_PRODUCT PR ON
						MA.PRODUCT_IDX = PR.IDX

						LEFT JOIN (
							SELECT
								S_PI.PRODUCT_IDX		AS PRODUCT_IDX,
								S_PI.IMG_LOCATION		AS IMG_LOCATION
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
						
						LEFT JOIN DELIVERY_COMPANY DC ON
						MA.DELIVERY_IDX = DC.IDX

						LEFT JOIN DELIVERY_COMPANY HC ON
						MA.HOUSING_IDX = HC.IDX
					WHERE
						MA.IDX			= ? AND
						MA.COUNTRY		= ? AND
						MA.MEMBER_IDX	= ?
				";

				$db->query($select_member_as_sql,array($as_idx,$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']));

				foreach($db->fetch() as $data) {
					$as_status = $data['AS_STATUS'];
					switch ($data['AS_STATUS']) {
						case "HOS" :
							$as_status = "HOS_F";
							if ($data['HOUSING_IDX'] > 0) {
								$as_status = "HOS_T";
							}
							
							break;
						
						case "RPR" :
							$as_status = "RPR_F";
							if ($data['AS_REPAIR_IDX'] != null) {
								$as_status = "RPR_T";
							}
							
							break;
						
						case "APG" :
							$as_status = "APG_F";
							if ($data['AS_PRICE_FLG'] == true) {
								$as_status = "APG_T";
							}
							
							break;
					}

					$t_as_status = setTXT_status($as_status);
					
					$t_as_price = number_format($data['AS_PRICE']);
					if ($_SERVER['HTTP_COUNTRY'] == "EN") {
						$t_as_price = number_format($data['AS_PRICE'],2);
					}

					$discount		= $data['DISCOUNT_'.$_SERVER['HTTP_COUNTRY']];
					
					$price			= number_format($data['PRICE_'.$_SERVER['HTTP_COUNTRY']]);
					$sales_price	= number_format($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']]);

					if ($_SERVER['HTTP_COUNTRY'] == "EN") {
						$price			= number_format($data['PRICE_'.$_SERVER['HTTP_COUNTRY']],2);
						$sales_price	= number_format($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']],2);
					}
					
					$as_info = array(
						'as_idx'				=>$data['AS_IDX'],
						'as_code'				=>$data['AS_CODE'],
						'create_date'			=>$data['CREATE_DATE'],
						'as_status'				=>$as_status,
						't_as_status'			=>$t_as_status,

						'bluemark_flg'			=>$data['BLUEMARK_FLG'],
						'purchase_mall'			=>$data['PURCHASE_MALL'],
						'serial_code'			=>$data['SERIAL_CODE'],
						'reg_date'				=>$data['REG_DATE'],
						
						'txt_category'			=>$data['TXT_CATEGORY_'.$_SERVER['HTTP_COUNTRY']],
						
						'img_location'			=>$data['IMG_LOCATION'],
						'product_idx'			=>$data['PRODUCT_IDX'],
						'product_name'			=>$data['PRODUCT_NAME'],
						'color'					=>$data['COLOR'],
						'color_rgb'				=>$data['COLOR_RGB'],
						'barcode'				=>$data['BARCODE'],
						'option_name'			=>$data['OPTION_NAME'],
						'price'					=>$price,
						'discount'				=>$discount,
						'sales_price'			=>$sales_price,
						
						'price'					=>$price,
						'discount_price'		=>$discount,
						'sales_price'			=>$sales_price,
						
						'as_contents'			=>$data['AS_CONTENTS'],

						'as_repair_type'		=>$data['AS_REPAIR_TYPE'],
						'as_repair_idx'			=>$data['AS_REPAIR_IDX'],
						'repair_desc'			=>$data['REPAIR_DESC_'.$_SERVER['HTTP_COUNTRY']],
						'as_price'				=>$data['AS_PRICE'],
						't_as_price'			=>$t_as_price,
						'as_price_flg'			=>$data['AS_PRICE_FLG'],
						'pg_payment'			=>$data['PG_PAYMENT'],
						'pg_date'				=>$data['PG_DATE'],
						'pg_price'				=>$data['PG_PRICE'],
						'pg_receipt_url'		=>$data['PG_RECEIPT_URL'],
						'd_payment'				=>$data['D_PG_PAYMENT'],
						
						'completion_date'		=>$data['COMPLETION_DATE'],
						
						'housing_type'			=>$data['HOUSING_TYPE'],
						'housing_company'		=>$data['HOUSING_COMPANY'],
						'housing_num'			=>$data['HOUSING_NUM'],
						'housing_start_date'	=>$data['HOUSING_START_DATE'],
						'housing_end_date'		=>$data['HOUSING_END_DATE'],
						
						'delivery_company'		=>$data['COMPANY_NAME'],
						'delivery_num'			=>$data['DELIVERY_NUM'],
						
						'delivery_status'		=>$data['DELIVERY_STATUS'],
						'delivery_start_date'	=>$data['DELIVERY_START_DATE'],
						'delivery_end_date'		=>$data['DELIVERY_END_DATE'],
						
						'to_idx'				=>$data['TO_IDX'],
						'to_place'				=>$data['TO_PLACE'],
						'to_name'				=>$data['TO_NAME'],
						'to_mobile'				=>$data['TO_MOBILE'],
						'to_zipcode'			=>$data['TO_ZIPCODE'],
						'to_road_addr'			=>$data['TO_ROAD_ADDR'],
						'to_detail_addr'		=>$data['TO_DETAIL_ADDR'],
						
						'order_memo'			=>$data['ORDER_MEMO'],

						'as_complete_date'  	=>$data['AS_COMPLETE_DATE'],
						'complete_flg'			=>$data['COMPLETE_FLG']
					);
				}
				
				$housing_company = array();
				if ($as_info['as_status'] == "HOS_F") {
					$housing_company = getAS_housing($db);
				}
				
				$order_to = getAS_address($db);
				
				$json_result['data'] = array(
					'as_info'				=>$as_info,
					'as_img_P'				=>getAS_img($db,$as_idx,"P"),
					'as_img_R'				=>getAS_img($db,$as_idx,"R"),
					
					'order_to'				=>$order_to,
					
					'as_memo'				=>getAS_memo($db),
					'housing_company'		=>$housing_company
				);
			} else {
				$json_result['code'] = 303;
				$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_WRN_0006',array());
				
				echo json_encode($json_result);
				exit;
			}
		} else {
			$json_result['code'] = 302;
			$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_WRN_0003',array());
			
			echo json_encode($json_result);
			exit;
		}
	} else {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_WRN_0003',array());
		
		echo json_encode($json_result);
		exit;
	}
} else {
	$json_result['code'] = 401;
    $json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
}

function getAS_img($db,$as_idx,$img_type) {
	$as_img = array();

	$select_as_img_sql = "
		SELECT
			AI.IDX					AS IMG_IDX,
			AI.IMG_TYPE				AS IMG_TYPE,
			AI.IMG_LOCATION			AS IMG_LOCATION
		FROM
			AS_IMG AI
		WHERE
			AI.AS_IDX = ? AND
			AI.IMG_TYPE = ? AND
			AI.DEL_FLG = FALSE
	";

	$db->query($select_as_img_sql,array($as_idx,$img_type));

	foreach($db->fetch() as $data) {
		$as_img[] = array(
			'img_idx'			=>$data['IMG_IDX'],
			'img_type'			=>$data['IMG_TYPE'],
			'img_location'		=>$data['IMG_LOCATION']
		);
	}

	return $as_img;
}

function getAS_address($db) {
	$order_to = null;
	
	$select_order_to_sql = "
		SELECT
			OT.IDX					AS TO_IDX,
			OT.COUNTRY				AS COUNTRY,
			OT.TO_PLACE				AS TO_PLACE,
			OT.TO_NAME				AS TO_NAME,
			OT.TO_MOBILE			AS TO_MOBILE,
			OT.TO_ZIPCODE			AS TO_ZIPCODE,
			OT.TO_ROAD_ADDR			AS TO_ROAD_ADDR,
			IFNULL(
				OT.TO_DETAIL_ADDR,''
			)						AS TO_DETAIL_ADDR,
			
			CI.COUNTRY_NAME			AS COUNTRY_NAME,
			PI.PROVINCE_NAME		AS PROVINCE_NAME,
			OT.TO_CITY				AS CITY,
			OT.TO_ADDRESS			AS ADDRESS,
			
			OT.DEFAULT_FLG			AS DEFAULT_FLG,
			IFNULL(
				DZ.COST,0
			)						AS DELIVERY_PRICE
		FROM
			ORDER_TO OT
			
			LEFT JOIN COUNTRY_INFO CI ON
			OT.TO_COUNTRY_CODE = CI.COUNTRY_CODE
			
			LEFT JOIN PROVINCE_INFO PI ON
			OT.TO_PROVINCE_IDX = PI.IDX
			
			LEFT JOIN DHL_ZONES DZ ON
			CI.ZONE_NUM = DZ.ZONE_NUM
		WHERE
			OT.COUNTRY = ? AND
			OT.MEMBER_IDX = ? AND
			OT.DEFAULT_FLG = TRUE
	";
	
	$db->query($select_order_to_sql,array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']));
	
	foreach($db->fetch() as $data) {
		$txt_addr = $data['TO_ROAD_ADDR'];
		if ($data['COUNTRY'] != "KR") {
			$txt_addr = $data['COUNTRY_NAME']." ".$data['PROVINCE_NAME']." ".$data['CITY']." ".$data['ADDRESS'];
		}

		$delivery_price = 0;
		if ($data['DELIVERY_PRICE'] > 0) {
			$delivery_price = (currency_EN * $data['DELIVERY_PRICE']);
		}
		
		$order_to = array(
			'to_idx'			=>$data['TO_IDX'],
			'to_place'			=>$data['TO_PLACE'],
			'to_name'			=>$data['TO_NAME'],
			'to_mobile'			=>$data['TO_MOBILE'],
			'to_zipcode'		=>$data['TO_ZIPCODE'],
			'txt_addr'			=>$txt_addr,
			'to_detail_addr'	=>$data['TO_DETAIL_ADDR'],
			'default_flg'		=>$data['DEFAULT_FLG'],
			'delivery_price'	=>round($delivery_price,2),
			't_delivery_price'	=>number_format($delivery_price,1)
		);
	}
	
	return $order_to;
}

function getAS_memo($db) {
	$as_memo = array();

	$select_order_memo_sql = "
		SELECT
			OM.IDX					AS MEMO_IDX,
			OM.MEMO_TXT_KR			AS MEMO_TXT_KR,
			OM.MEMO_TXT_EN			AS MEMO_TXT_EN,
			OM.DIRECT_FLG			AS DIRECT_FLG
		FROM
			ORDER_MEMO OM
		ORDER BY
			OM.DISPLAY_NUM ASC
	";

	$db->query($select_order_memo_sql);

	foreach($db->fetch() as $data) {
		$as_memo[] = array(
			'memo_idx'				=>$data['MEMO_IDX'],
			'memo_txt'				=>$data['MEMO_TXT_'.$_SERVER['HTTP_COUNTRY']],
			'direct_flg'			=>$data['DIRECT_FLG']
		);
	}

	return $as_memo;
}

function getAS_housing($db) {
	$housing_company = array();
	
	$where = "";
	if ($_SERVER['HTTP_COUNTRY'] == "KR") {
		$where .= " AND (COUNTRY = 'KR') ";
	} else {
		$where .= " AND (COUNTRY = 'FR') ";
	}
	
	$select_delivery_company_sql = "
		SELECT
			HC.IDX				AS HOUSING_IDX,
			HC.COMPANY_NAME		AS HOUSING_COMPANY
		FROM
			DELIVERY_COMPANY HC
		WHERE
			HC.DEL_FLG = FALSE
			".$where."
	";
	
	$db->query($select_delivery_company_sql);
	
	foreach($db->fetch() as $data) {
		$housing_company[] = array(
			'housing_idx'		=>$data['HOUSING_IDX'],
			'housing_company'	=>$data['HOUSING_COMPANY']
		);
	}
	
	return $housing_company;
}

function setTXT_status($param_status) {
	$txt_status = "";

	$status = array(
		'KR'		=>array(
			'APL'		=>"검토 대기",
			'HOS_F'		=>"제품 미회수",
			'HOS_T'		=>"회수 대기",
			'RPR_F'		=>"수선대기",
			'RPR_T'		=>"제품 수선중",
			'APG_F'		=>"결제 대기",
			'APG_T'		=>"결제 완료",
			'DLV'		=>"배송중",
			'ACP'		=>"A/S 완료",
			'RPA'		=>"수선가능",
			'URP'		=>"수선불가"
		),
		'EN'			=>array(
			'APL'		=>"Awaiting Review",
			'HOS'		=>"Reclaiming Products",
			'RPR'		=>"Product being repaired",
			'APG_F'		=>"Waiting  payment",
			'APG_T'		=>"Payment completed",
			'DLV'		=>"In transit",
			'ACP'		=>"A/S complete",
			'RPA'		=>"Repairable",
			'URP'		=>"Unrepairable"
		)
	);
    
	if (isset($status[$_SERVER['HTTP_COUNTRY']][$param_status])) {
		$txt_status = $status[$_SERVER['HTTP_COUNTRY']][$param_status];
	}

    return $txt_status;
}

?>