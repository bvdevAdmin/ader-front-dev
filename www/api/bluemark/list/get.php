<?php
/*
 +=============================================================================
 | 
 | 마이페이지 블루마크 - 블루마크 내역 조회
 | -------
 |
 | 최초 작성	: 윤재은
 | 최초 작성일	: 2023.01.09
 | 최종 수정    : 양한빈
 | 최종 수정일	: 2024.05.07
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	$bluemark_info = array();
	
	$table = "
		BLUEMARK_INFO BI
		
		LEFT JOIN BLUEMARK_LOG BL ON
		BI.IDX = BL.BLUEMARK_IDX
		
		LEFT JOIN SHOP_PRODUCT PR ON
		PR.IDX = BI.PRODUCT_IDX
		
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
				S_MA.SERIAL_CODE			AS SERIAL_CODE,
				S_MA.COUNTRY				AS COUNTRY,
				S_MA.MEMBER_IDX				AS MEMBER_IDX,
				COUNT(S_MA.SERIAL_CODE)		AS CNT_AS
			FROM
				MEMBER_AS S_MA
			WHERE
				S_MA.AS_STATUS != 'ACP' AND
				S_MA.DEL_FLG = FALSE
			GROUP BY
				S_MA.SERIAL_CODE
		) AS J_MA ON
		BI.SERIAL_CODE = J_MA.SERIAL_CODE AND
		BL.COUNTRY = J_MA.COUNTRY AND
		BL.MEMBER_IDX = J_MA.MEMBER_IDX
	";
	
	$where = "
		BI.COUNTRY = ? AND
		BI.MEMBER_IDX = ? AND
		BI.DEL_FLG = FALSE AND
		BL.ACTIVE_FLG = TRUE
	";
	
	$param_bind = array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']);
	
	$json_result = array(
		'total'	=>$db->count($table,$where,$param_bind),
		'page'	=>$page
	);
	
	$select_bluemark_sql = "
		SELECT 
			DISTINCT BI.IDX				AS BLUEMARK_IDX,
			BL.IDX						AS LOG_IDX,
			BI.SERIAL_CODE    			AS SERIAL_CODE,
			J_PI.IMG_LOCATION			AS IMG_LOCATION,
			PR.PRODUCT_NAME				AS PRODUCT_NAME,
			
			PR.PRICE_KR					AS PRICE_KR,
			PR.DISCOUNT_KR				AS DISCOUNT_KR,
			PR.SALES_PRICE_KR			AS SALES_PRICE_KR,

			PR.PRICE_EN					AS PRICE_EN,
			PR.DISCOUNT_EN				AS DISCOUNT_EN,
			PR.SALES_PRICE_EN			AS SALES_PRICE_EN,

			PR.COLOR					AS COLOR,
			PR.COLOR_RGB				AS COLOR_RGB,
			BI.OPTION_NAME				AS OPTION_NAME,
			DATE_FORMAT(
				BL.REG_DATE,
				'%Y.%m.%d'
			)							AS REG_DATE,
			
			BL.PURCHASE_MALL			AS PURCHASE_MALL,
			IFNULL(J_MA.CNT_AS,0)		AS CNT_AS
		FROM
			".$table."
		WHERE
			".$where."
		ORDER BY
			BL.IDX DESC
	";
	
	if ($rows != null) {
		$limit_start = (intval($page)-1)*$rows;
		
		$select_bluemark_sql .= " LIMIT ?,? ";
		
		array_push($param_bind,$limit_start);
		array_push($param_bind,$rows);
	}
	
	$db->query($select_bluemark_sql,$param_bind);

	foreach($db->fetch() as $data) {
		$img_location = "/default/default_product.jpg";
		if ($data['IMG_LOCATION'] != null) {
			$img_location = $data['IMG_LOCATION'];
		}
		
		$bluemark_info[] = array(
			'bluemark_idx'		=>$data['BLUEMARK_IDX'],
			'log_idx'			=>$data['LOG_IDX'],
			'serial_code'   	=>strtoupper($data['SERIAL_CODE']),
			'img_location'  	=>$data['IMG_LOCATION'],
			'product_name'		=>$data['PRODUCT_NAME'],
			'price'				=>number_format($data['PRICE_'.$_SERVER['HTTP_COUNTRY']]),
			'discount'			=>$data['DISCOUNT_'.$_SERVER['HTTP_COUNTRY']],
			'sales_price'		=>number_format($data['SALES_PRICE_'.$_SERVER['HTTP_COUNTRY']]),
			'color'				=>$data['COLOR'],
			'color_rgb'			=>$data['COLOR_RGB'],
			'option_name'		=>$data['OPTION_NAME'],
			'reg_date'			=>$data['REG_DATE'],
			
			'purchase_mall'		=>$data['PURCHASE_MALL'],
			'cnt_as'			=>$data['CNT_AS']
		);
	}

	$json_result['data'] = $bluemark_info;
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'], 'MSG_B_ERR_0018', array());
	
	echo json_encode($json_result);
	exit;
}

?>