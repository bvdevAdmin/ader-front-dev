<?php
/*
 +=============================================================================
 | 
 | A/S 신청내역 목록 조회
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
	$param_bind = array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']);
	
	$json_result = array(
		'total'			=>$db->count("MEMBER_AS","COUNTRY = ? AND MEMBER_IDX = ?",array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'])),
		'page'			=>$page
	);
	
	$select_member_as_sql = "
		SELECT
			MA.IDX							AS AS_IDX,
			MA.AS_CODE						AS AS_CODE,
			MA.AS_STATUS					AS AS_STATUS,
			
			MA.BLUEMARK_FLG					AS BLUEMARK_FLG,
			IFNULL(
				J_BI.SERIAL_CODE,''
			)								AS SERIAL_CODE,
			IFNULL(
				J_BI.PURCHASE_MALL,''
			)								AS PURCHASE_MALL,
			IFNULL(
				J_BI.REG_DATE,''
			)								AS REG_DATE,
			
			J_PI.IMG_LOCATION				AS IMG_LOCATION,
			J_AI.IMG_LOCATION				AS IMG_LOCATION_AP,
			
			PR.PRODUCT_NAME					AS PRODUCT_NAME,
			PR.COLOR						AS COLOR,
			PR.COLOR_RGB					AS COLOR_RGB,
			MA.OPTION_NAME					AS OPTION_NAME,
			MA.BARCODE						AS BARCODE,

			PR.PRICE_KR						AS PRICE_KR,
			PR.DISCOUNT_KR					AS DISCOUNT_KR,
			PR.SALES_PRICE_KR				AS SALES_PRICE_KR,

			PR.PRICE_EN						AS PRICE_EN,
			PR.DISCOUNT_EN					AS DISCOUNT_EN,
			PR.SALES_PRICE_EN				AS SALES_PRICE_EN,
			
			MA.HOUSING_IDX					AS HOUSING_IDX,
			MA.AS_REPAIR_IDX				AS AS_REPAIR_IDX,
			MA.AS_PRICE_FLG					AS AS_PRICE_FLG,

			DATE_FORMAT(
				MA.CREATE_DATE,
				'%Y.%m.%d'
			)								AS CREATE_DATE
		FROM
			MEMBER_AS MA
			
			LEFT JOIN AS_CATEGORY AC ON
			MA.AS_CATEGORY_IDX = AC.IDX
			
			LEFT JOIN (
				SELECT
					S_AI.AS_IDX			AS AS_IDX,
					S_AI.IMG_LOCATION	AS IMG_LOCATION
				FROM
					AS_IMG S_AI
				WHERE
					S_AI.IMG_TYPE = 'P' AND
					S_AI.DEL_FLG = FALSE
				GROUP BY
					S_AI.AS_IDX
			) AS J_AI ON
			MA.IDX = J_AI.AS_IDX
			
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
			
			LEFT JOIN (
				SELECT
					S_BI.SERIAL_CODE		AS SERIAL_CODE,
					S_BL.PURCHASE_MALL		AS PURCHASE_MALL,
					S_BL.REG_DATE			AS REG_DATE
				FROM
					BLUEMARK_INFO S_BI
					
					LEFT JOIN BLUEMARK_LOG S_BL ON
					S_BI.IDX = S_BL.BLUEMARK_IDX
				WHERE
					S_BI.DEL_FLG = FALSE AND
					S_BL.ACTIVE_FLG = TRUE
			) AS J_BI ON
			MA.SERIAL_CODE = J_BI.SERIAL_CODE
		WHERE
			MA.COUNTRY = ? AND
			MA.MEMBER_IDX = ?
		ORDER BY
			MA.COMPLETE_FLG ASC, MA.IDX DESC
	";
	
	if ($rows != null) {
		$limit_start = (intval($page)-1) * $rows;
		
		$select_member_as_sql .= " LIMIT ?,? ";
		
		array_push($param_bind,$limit_start);
		array_push($param_bind,$rows);
	}
	
	$db->query($select_member_as_sql,$param_bind);
	
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
		
		$serial_code = array(
			'KR'		=>"블루마크 미인증",
			'EN'		=>"Bluemark unverified"
		);

		$purchase_mall	= array(
			'KR'		=>"구매처 확인 불가",
			'EN'		=>"Unable to verify"
		);

		$reg_date		= array(
			'KR'		=>"인증내역 없음",
			'EN'		=>"Unable to verify",
		);
		
		$img_location	= $data['IMG_LOCATION'];
		if ($data['IMG_LOCATION'] == null) {
			$img_location = $data['IMG_LOCATION_AP'];
		}
		
		$product_name	= array(
			'KR'		=>"블루마크 미인증",
			'EN'		=>"Bluemark unverified",
		);

		$color			= "-";
		$color_rgb		= "-";
		$option_name	= "-";
		$barcode		= "-";
		$price			= "-";
		$discount		= "-";
		$sales_price	= "-";

		if ($data['BLUEMARK_FLG'] == true) {
			$product_name[$_SERVER['HTTP_COUNTRY']]		= $data['PRODUCT_NAME'];
			$purchase_mall[$_SERVER['HTTP_COUNTRY']]	= $data['PURCHASE_MALL'];
			$serial_code[$_SERVER['HTTP_COUNTRY']]		= strtoupper($data['SERIAL_CODE']);
			$reg_date[$_SERVER['HTTP_COUNTRY']]			= $data['REG_DATE'];

			$color			= $data['COLOR'];
			$color_rgb		= $data['COLOR_RGB'];
			$option_name	= $data['OPTION_NAME'];
			$barcode		= $data['BARCODE'];
			
			$discount		= $data['DISCOUNT_'.$_SERVER['HTTP_COUNTRY']];

			$price			= number_format($data['PRICE_'.$_SERVER['HTTP_COUNTRY']]);
			$sales_price	= number_format($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']]);
			if ($_SERVER['HTTP_COUNTRY'] == "EN") {
				$price			= number_format($data['PRICE_'.$_SERVER['HTTP_COUNTRY']],1);
				$sales_price	= number_format($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']],1);
			}
		}
		
		$json_result['data'][] = array(
			'as_idx'			=>$data['AS_IDX'],
			'as_code'			=>$data['AS_CODE'],
			'as_status'			=>setTXT_status($as_status),
			
			'bluemark_flg'		=>$data['BLUEMARK_FLG'],
			'serial_code'		=>$serial_code[$_SERVER['HTTP_COUNTRY']],
			'purchase_mall'		=>$purchase_mall[$_SERVER['HTTP_COUNTRY']],
			'reg_date'			=>$reg_date[$_SERVER['HTTP_COUNTRY']],
			
			'img_location'		=>$img_location,
			
			'product_name'		=>$product_name[$_SERVER['HTTP_COUNTRY']],
			'color'				=>$color,
			'color_rgb'			=>$color_rgb,
			'option_name'		=>$option_name,
			'barcode'			=>$barcode,
			
			'price'				=>$price,
			'discount'			=>$discount,
			'sales_price'		=>$sales_price,
			
			'create_date'  		=>$data['CREATE_DATE']
			
		);
	}
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
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