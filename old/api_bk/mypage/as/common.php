<?php
/*
 +=============================================================================
 | 
 | A/S 공통함수
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

function setTxtParam($param, $country) {
	$txt_param = "-";
	
	if ($param != null && $country != null) {
		switch ($param) {
			case "APL" :
				if($country == "KR") {
					$txt_param = "검토 대기 중";
				} else if($country == "EN") {
					$txt_param = "Awaiting Review";
				} else {
					$txt_param = "等待审查";
				}
				break;
			
			case "HOS" :
				if($country == "KR") {
					$txt_param = "제품 회수 중";
				} else if($country == "EN") {
					$txt_param = "Reclaiming Products";
				} else {
					$txt_param = "回收产品";
				}
				break;
			
			case "RPR" :
				if($country == "KR") {
					$txt_param = "제품 수선 중";
				} else if($country == "EN") {
					$txt_param = "Product being repaired";
				} else {
					$txt_param = "正在修理的产品";
				}
				break;
			
			case "APG" :
				if($country == "KR") {
					$txt_param = "배송 정보 및 결제 대기 중";
				} else if($country == "EN") {
					$txt_param = "Waiting for shipping information and payment";
				} else {
					$txt_param = "等待发货信息和付款";
				}
				break;
			
			case "DLV" :
				if($country == "KR") {
					$txt_param = "배송중";
				} else if($country == "EN") {
					$txt_param = "In transit";
				} else {
					$txt_param = "过境中";
				}
				break;
			
			case "ACP" :
				
				if($country == "KR") {
					$txt_param = "A/S 완료";
				} else if($country == "EN") {
					$txt_param = "A/S complete";
				} else {
					$txt_param = "A/S完成";
				}
				break;
			
			case "RPA" :
				
				if($country == "KR") {
					$txt_param = "수선가능";
				} else if($country == "EN") {
					$txt_param = "Repairable";
				} else {
					$txt_param = "可修复的";
				}
				break;
			
			case "URP" :
				
				if($country == "KR") {
					$txt_param = "수선불가";
				} else if($country == "EN") {
					$txt_param = "Unrepairable";
				} else {
					$txt_param = "无法修复";
				}
				break;
		}
	}
	
	return $txt_param;
}

function getBluemarkInfo($db,$serial_code,$member_idx) {
	$bluemark_info = array();
	
	$select_bluemark_sql = "
		SELECT
			BI.SERIAL_CODE		AS SERIAL_CODE,
			DATE_FORMAT(
				BL.REG_DATE,
				'%Y.%m.%d'
			)					AS REG_DATE,
			BL.PURCHASE_MALL	AS PURCHASE_MALL
		FROM
			BLUEMARK_INFO BI
			LEFT JOIN BLUEMARK_LOG BL ON
			BI.IDX = BL.BLUEMARK_IDX
		WHERE
			BI.SERIAL_CODE = '".$serial_code."' AND
			BL.MEMBER_IDX = ".$member_idx." AND
			BL.ACTIVE_FLG = TRUE
	";
	
	$db->query($select_bluemark_sql);
	
	foreach($db->fetch() as $bluemark_data) {
		$bluemark_info = array(
			'serial_code'		=>strtoupper($bluemark_data['SERIAL_CODE']),
			'purchase_mall'		=>$bluemark_data['PURCHASE_MALL'],
			'reg_date'		=>$bluemark_data['REG_DATE'],
		);
	}
	
	return $bluemark_info;
}

?>