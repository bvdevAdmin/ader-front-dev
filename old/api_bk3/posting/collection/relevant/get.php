<?php
/*
 +=============================================================================
 | 
 | 게시물_룩북 - 룩북 이미지 개별 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.02.10
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/
include_once("/var/www/www/api/common.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
} else if (isset($_SERVER['HTTP_COUNTRY'])) {
	$country = $_SERVER['HTTP_COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if (!isset($c_product_idx)) {
	$json_result['code'] = 301;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0030', array());
	
	echo json_encode($json_result);
	exit;
}

$relevant_flg = $db->get('COLLECTION_PRODUCT', 'IDX = ?', array($c_product_idx))[0]['RELEVANT_FLG'];
if (isset($c_product_idx) && $relevant_flg == true) {
	$select_relevant_product_sql = "
		SELECT
			PR.IDX				AS PRODUCT_IDX,
			PR.PRODUCT_NAME		AS PRODUCT_NAME,
			PR.PRODUCT_TYPE		AS PRODUCT_TYPE,
			PR.SET_TYPE			AS SET_TYPE,
			IFNULL((
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
			),'/product_img/default_product_img.jpg')					
								AS IMG_LOCATION,
			RP.SOLD_OUT_FLG
		FROM
			COLLECTION_RELEVANT_PRODUCT RP
			LEFT JOIN SHOP_PRODUCT PR ON
			RP.PRODUCT_IDX = PR.IDX
		WHERE
			RP.C_PRODUCT_IDX = ".$c_product_idx." AND
			RP.DISPLAY_FLG = TRUE AND
			PR.DEL_FLG = FALSE
		ORDER BY
			RP.DISPLAY_NUM
	";
	
	$db->query($select_relevant_product_sql);
	
	foreach($db->fetch() as $relevant_data) {
		$whish_flg = false;
		if($member_idx > 0){
			$whish_count = $db->count("WHISH_LIST","MEMBER_IDX = ".$member_idx." AND PRODUCT_IDX = ".$relevant_data['PRODUCT_IDX']." AND DEL_FLG = FALSE");
			$whish_flg = $whish_count>0?true:false;
		}

		$product_size = getProductSize($db,$relevant_data['PRODUCT_TYPE'],$relevant_data['SET_TYPE'],$relevant_data['PRODUCT_IDX']);
		$stock_status = null;
		$stock_close_cnt = 0;
		for ($i=0; $i<count($product_size); $i++) {
			$tmp_stock_status = $product_size[$i]['stock_status'];
			if ($tmp_stock_status == "STCL") {
				$stock_close_cnt++;
			}
		}
		$stcl_flg = false;
		if ($stock_close_cnt > 0 && $relevant_data['SOLD_OUT_FLG'] == true) {
			$stcl_flg = true;
		}

		$json_result['data'][] = array(
			'product_idx'		=>$relevant_data['PRODUCT_IDX'],
			'product_name'		=>$relevant_data['PRODUCT_NAME'],
			'img_location'		=>$relevant_data['IMG_LOCATION'],
			'stcl_flg'			=>$stcl_flg,
			'stcl_cnt'			=>$stock_close_cnt,
			'sold_out_flg'		=>$relevant_data['SOLD_OUT_FLG'],
			'whish_flg'			=>$whish_flg
		);
	}
}

?>