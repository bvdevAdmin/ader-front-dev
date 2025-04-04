<?php
/*
 +=============================================================================
 | 
 | A/S신청 리스트 조회
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
include_once(dir_f_api."/common.php");
include_once(dir_f_api."/mypage/as/common.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if (isset($country) && $member_idx > 0 && isset($as_idx)) {
	$as_cnt = $db->count("MEMBER_AS","IDX = ".$as_idx." AND COUNTRY = '".$country."' AND MEMBER_IDX = ".$member_idx);
	
	if ($as_cnt > 0) {
		$select_member_as_sql = "
			SELECT
				MA.IDX							AS AS_IDX,
				MA.SERIAL_CODE					AS SERIAL_CODE,
				MA.BLUEMARK_FLG					AS BLUEMARK_FLG,
				MA.AS_CODE						AS AS_CODE,
				MA.AS_STATUS					AS AS_STATUS,
				
				AC.TXT_CATEGORY					AS TXT_CATEGORY,
				
				(
					SELECT
						S_PI.IMG_LOCATION
					FROM
						PRODUCT_IMG S_PI
					WHERE
						S_PI.PRODUCT_IDX = PR.IDX AND
						S_PI.IMG_TYPE = 'P' AND
						S_PI.IMG_SIZE = 'S'
					ORDER BY
						S_PI.IDX ASC
					LIMIT
						0,1
				)								AS IMG_LOCATION,
				PR.PRODUCT_NAME					AS PRODUCT_NAME,
				PR.COLOR						AS COLOR,
				PR.COLOR_RGB					AS COLOR_RGB,
				IFNULL(
					MA.BARCODE,'-'
				)								AS BARCODE,
				MA.OPTION_NAME					AS OPTION_NAME,
				PR.PRICE_".$country."			AS PRICE,
				PR.DISCOUNT_".$country."		AS DISCOUNT_PRICE,
				PR.SALES_PRICE_".$country."		AS SALES_PRICE,
				
				MA.AS_CONTENTS					AS AS_CONTENTS,
				MA.AS_REPAIR_TYPE				AS AS_REPAIR_TYPE,
				MA.AS_REPAIR_IDX				AS AS_REPAIR_IDX,
				AR.REPAIR_DESC					AS REPAIR_DESC,
				MA.AS_PRICE						AS AS_PRICE,
				MA.AS_PRICE_FLG					AS AS_PRICE_FLG,
				
				DATE_FORMAT(
					MA.COMPLETION_DATE,
					'%Y.%m.%d'
				)								AS COMPLETION_DATE,
				MA.COMPLETE_FLG					AS COMPLETE_FLG,
				
				MA.HOUSING_COMPANY				AS HOUSING_COMPANY,
				MA.HOUSING_NUM					AS HOUSING_NUM,
				DATE_FORMAT(
					MA.HOUSING_START_DATE,
					'%Y.%m.%d'
				)								AS HOUSING_START_DATE,
				DATE_FORMAT(
					MA.HOUSING_END_DATE,
					'%Y.%m.%d'
				)								AS HOUSING_END_DATE,
				MA.DELIVERY_IDX					AS DELIVERY_IDX,
				DC.COMPANY_NAME					AS COMPANY_NAME,
				MA.DELIVERY_NUM					AS DELIVERY_NUM,
				
				MA.DELIVERY_STATUS				AS DELIVERY_STATUS,
				DATE_FORMAT(
					MA.DELIVERY_START_DATE,
					'%Y.%m.%d'
				)								AS DELIVERY_START_DATE,
				DATE_FORMAT(
					MA.DELIVERY_END_DATE,
					'%Y.%m.%d'
				)								AS DELIVERY_END_DATE,
				
				TO_PLACE						AS TO_PLACE,
				TO_NAME							AS TO_NAME,
				TO_MOBILE						AS TO_MOBILE,
				TO_ZIPCODE						AS TO_ZIPCODE,
				TO_LOT_ADDR						AS TO_LOT_ADDR,
				TO_ROAD_ADDR					AS TO_ROAD_ADDR,
				TO_DETAIL_ADDR					AS TO_DETAIL_ADDR,
				TO_COUNTRY_CODE					AS TO_COUNTRY_CODE,
				TO_PROVINCE_IDX					AS TO_PROVINCE_IDX,
				TO_CITY							AS TO_CITY,
				ORDER_MEMO						AS ORDER_MEMO,
				
				DATE_FORMAT(
					MA.CREATE_DATE,
					'%Y.%m.%d'
				)								AS CREATE_DATE,
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
				LEFT JOIN SHOP_PRODUCT PR ON
				MA.PRODUCT_IDX = PR.IDX
				LEFT JOIN DELIVERY_COMPANY DC ON
				MA.DELIVERY_IDX = DC.IDX
			WHERE
				MA.IDX = ".$as_idx." AND
				MA.COUNTRY = '".$country."' AND
				MA.MEMBER_IDX = ".$member_idx."
		";
		
		$db->query($select_member_as_sql);
		
		foreach($db->fetch() as $as_data) {
			$as_idx = $as_data['AS_IDX'];
			$as_status = $as_data['AS_STATUS'];
			
			$txt_as_status = "";
			$txt_pay_confirm_status = "";
			$txt_unrepairable = "";
			switch($country) {
				case "KR":
					$txt_pay_confirm_status = "결제 확인 및 배송 준비 중";
					$txt_unrepairable = "수선 불가";
					break;
				case "EN":
					$txt_pay_confirm_status = "Confirming payment and preparing for delivery";
					$txt_unrepairable = "Unrepairable";
					break;
				case "CN":
					$txt_pay_confirm_status = "确认付款和准备交付";
					$txt_unrepairable = "无法修复";
					break;
			}

			if ($as_status != "APG") {
				$txt_as_status = setTxtParam($as_status, $country);
			} else {
				if ($as_data['AS_PRICE_FLG'] == true) {
					$txt_as_status = $txt_pay_confirm_status;
				} else {
					$txt_as_status = setTxtParam($as_status, $country);
				}
			}
			
			$as_price = $as_data['AS_PRICE'];
			
			$repair_desc = "";
			if ($as_data['AS_REPAIR_TYPE'] != "URP" && $as_data['AS_REPAIR_IDX'] > 0) {
				$repair_desc = $as_data['REPAIR_DESC'];
			} else {
				$repair_desc = $txt_unrepairable;
			}
			
			$serial_code = $as_data['SERIAL_CODE'];
			$bluemark_flg = $as_data['BLUEMARK_FLG'];
			
			$bluemark_info = array();
			if (!empty($serial_code) && $bluemark_flg == true) {
				$bluemark_info = getBluemarkInfo($db,$serial_code,$member_idx);
			}
			
			$reg_date = null;
			$purchase_mall = null;
			
			if ($bluemark_info != null && $bluemark_info > 0) {
				$serial_code = $bluemark_info['serial_code'];
				$purchase_mall = $bluemark_info['purchase_mall'];
				$reg_date = $bluemark_info['reg_date'];
			}
			
			$img_info = array();
			$payment_info = array();
			
			$select_as_img_sql = "
				SELECT
					AI.IDX				AS IMG_IDX,
					AI.IMG_TYPE			AS IMG_TYPE,
					AI.IMG_LOCATION		AS IMG_LOCATION
				FROM
					AS_IMG AI
				WHERE
					AI.AS_IDX = ".$as_idx." AND
					DEL_FLG = FALSE
			";
			
			$db->query($select_as_img_sql);
			
			foreach($db->fetch() as $img_data) {
				$img_info[] = array(
					'img_idx'			=>$img_data['IMG_IDX'],
					'img_type'			=>$img_data['IMG_TYPE'],
					'img_location'		=>$img_data['IMG_LOCATION']
				);
			}
			
			if ($as_price > 0) {
				$select_as_payment_sql = "
					SELECT
						AP.PG_PAYMENT			AS PG_PAYMENT,
						AP.PG_DATE				AS PG_DATE,
						AP.PG_PRICE				AS PG_PRICE,
						AP.PG_RECEIPT_URL		AS PG_RECEIPT_URL
					FROM
						AS_PAYMENT AP
					WHERE
						AP.AS_IDX = ".$as_idx."
				";
				
				$db->query($select_as_payment_sql);
				
				foreach($db->fetch() as $payment_data) {
					$payment_info = array(
						'pg_payment'		=>$payment_data['PG_PAYMENT'],
						'pg_date'			=>$payment_data['PG_DATE'],
						'pg_price'			=>number_format($payment_data['PG_PRICE']),
						'pg_receipt_url'	=>$payment_data['PG_RECEIPT_URL']
					);
				}
			}

			$order_memo_list = array();

			$select_order_memo_list_sql = "
				SELECT
					OM.IDX					AS MEMO_IDX,
					OM.COUNTRY				AS COUNTRY,
					OM.PLACEHOLDER_FLG		AS PLACEHOLDER_FLG,
					OM.MEMO_TXT				AS MEMO_TXT,
					OM.DIRECT_FLG			AS DIRECT_FLG
				FROM
					ORDER_MEMO OM
				WHERE
					COUNTRY = '".$country."'
				ORDER BY
					OM.DISPLAY_NUM ASC
			";

			$db->query($select_order_memo_list_sql);

			foreach($db->fetch() as $memo_data) {
				$order_memo_list[] = array(
					'memo_idx'				=>$memo_data['MEMO_IDX'],
					'country'				=>$memo_data['COUNTRY'],
					'placeholder_flg'		=>$memo_data['PLACEHOLDER_FLG'],
					'memo_txt'				=>$memo_data['MEMO_TXT'],
					'direct_flg'			=>$memo_data['DIRECT_FLG']
				);
			}


			
			$json_result['data'] = array(
				'as_idx'				=>$as_data['AS_IDX'],
				'as_code'				=>$as_data['AS_CODE'],
				'bluemark_flg'			=>$as_data['BLUEMARK_FLG'],
				
				'txt_category'			=>$as_data['TXT_CATEGORY'],
				
				'img_location'			=>$as_data['IMG_LOCATION'],
				'product_name'			=>$as_data['PRODUCT_NAME'],
				'color'					=>$as_data['COLOR'],
				'color_rgb'				=>$as_data['COLOR_RGB'],
				'barcode'				=>$as_data['BARCODE'],
				'option_name'			=>$as_data['OPTION_NAME'],
				'price'					=>number_format($as_data['PRICE']),
				'discount_price'		=>number_format($as_data['DISCOUNT_PRICE']),
				'sales_price'			=>number_format($as_data['SALES_PRICE']),
				
				'as_status'				=>$as_data['AS_STATUS'],
				'txt_as_status'			=>$txt_as_status,
				'as_contents'			=>$as_data['AS_CONTENTS'],
				'as_repair_type'		=>$as_data['AS_REPAIR_TYPE'],
				'as_repair_idx'			=>$as_data['AS_REPAIR_IDX'],
				'repair_desc'			=>$repair_desc,
				'as_price'				=>$as_data['AS_PRICE'],
				'txt_as_price'			=>number_format($as_data['AS_PRICE']),
				'as_price_flg'			=>$as_data['AS_PRICE_FLG'],
				
				'completion_date'		=>$as_data['COMPLETION_DATE'],
				'complete_flg'			=>$as_data['COMPLETE_FLG'],
				
				'housing_company'		=>$as_data['HOUSING_COMPANY'],
				'housing_num'			=>$as_data['HOUSING_NUM'],
				'housing_start_date'	=>$as_data['HOUSING_START_DATE'],
				'housing_end_date'		=>$as_data['HOUSING_END_DATE'],
				
				'delivery_idx'			=>$as_data['DELIVERY_IDX'],
				'company_name'			=>$as_data['COMPANY_NAME'],
				'delivery_num'			=>$as_data['DELIVERY_NUM'],
				
				'delivery_status'		=>$as_data['DELIVERY_STATUS'],
				'delivery_start_date'	=>$as_data['DELIVERY_START_DATE'],
				'delivery_end_date'		=>$as_data['DELIVERY_END_DATE'],
				
				'to_place'				=>$as_data['TO_PLACE'],
				'to_name'				=>$as_data['TO_NAME'],
				'to_mobile'				=>$as_data['TO_MOBILE'],
				'to_zipcode'			=>$as_data['TO_ZIPCODE'],
				'to_lot_addr'			=>$as_data['TO_LOT_ADDR'],
				'to_road_addr'			=>$as_data['TO_ROAD_ADDR'],
				'to_detail_addr'		=>$as_data['TO_DETAIL_ADDR'],
				'to_country_code'		=>$as_data['TO_COUNTRY_CODE'],
				'to_province_idx'		=>$as_data['TO_PROVINCE_IDX'],
				'to_city'				=>$as_data['TO_CITY'],
				'order_memo'			=>$as_data['ORDER_MEMO'],
				
				'create_date'			=>$as_data['CREATE_DATE'],
				'as_complete_date'  =>$as_data['AS_COMPLETE_DATE'],
				
				'serial_code'			=>$serial_code,
				'reg_date'				=>$reg_date,
				'purchase_mall'			=>$purchase_mall,
				
				'img_info'				=>$img_info,
				'payment_info'			=>$payment_info,
				'order_memo_list'		=>$order_memo_list
			);
		}
	} else {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_WRN_0006', array());
	}
}

?>