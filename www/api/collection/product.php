<?php
/*
 +=============================================================================
 | 
 | 컬렉션 조회 - 컬렉션 상품 이미지 조회
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

if (isset($_SERVER['HTTP_COUNTRY'])) {
	if (isset($project_idx) || isset($no)) {
		/* 컬렉션 조회 - 프로젝트별 컬렉션 상품 이미지 조회 */
		if (isset($project_idx)) {
			$result = getCollection_P_list($db,$project_idx,$last_idx);
			
			$json_result['data']		= $result['data'];
			$json_result['first_index']	= $result['first_index'];
			$json_result['last_index']	= $result['last_index'];
		}
		
		/* 컬렉션 조회 - 상품 이미지 개별 조회 */
		if (isset($no)) {
			$collection_product = getCollection_P($db,$no);
			
			$json_result['data'] = $collection_product;
		}
	} else {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0030', array());
		
		echo json_encode($json_result);
		exit;
	}
}

function getCollection_P_list($db,$project_idx,$last_idx) {
	$collection_product = array();
	
	$param_bind = array($project_idx);
	
	$select_collection_product_sql = "
		SELECT
			CP.IDX					AS C_PRODUCT_IDX,
			J_CI.IMG_LOCATION		AS IMG_LOCATION,
			CP.RELEVANT_FLG			AS RELEVANT_FLG
		FROM
			COLLECTION_PRODUCT CP
			
			LEFT JOIN (
				SELECT
					S_CI.C_PRODUCT_IDX	AS C_PRODUCT_IDX,
					S_CI.IMG_LOCATION	AS IMG_LOCATION
				FROM
					COLLECTION_IMG S_CI
				WHERE
					S_CI.IMG_SIZE = 'L' AND
					S_CI.DEL_FLG = FALSE
			) J_CI ON
			CP.IDX = J_CI.C_PRODUCT_IDX
		WHERE
			CP.PROJECT_IDX = ?
		ORDER BY
			CP.DISPLAY_NUM ASC
		LIMIT
			?,?
	";
	
	if ($last_idx != null && $last_idx > 0) {
		array_push($param_bind,$last_idx);
		array_push($param_bind,20);
	} else {
		array_push($param_bind,0);
		array_push($param_bind,20);
	}
	
	$db->query($select_collection_product_sql,$param_bind);
	
	$c_product_info = array();
	
	foreach($db->fetch() as $data) {
		$c_product_info[] = array(
			'c_product_idx'		=>$data['C_PRODUCT_IDX'],
			'relevant_flg'		=>$data['RELEVANT_FLG'],
			'img_location'		=>$data['IMG_LOCATION']
		);
	}
	
	$calc_idx = intval($last_idx + count($c_product_info));
	
	$collection_product = array(
		'data'			=>$c_product_info,
		'first_index'	=>$last_idx,
		'last_index'	=>$calc_idx,
	);
	
	return $collection_product;
}

function getCollection_P($db,$no) {
	$collection_product = array();
	
	$select_collection_product_sql = "
		SELECT
			CP.IDX				AS C_PRODUCT_IDX,
			CI.IMG_URL			AS IMG_LOCATION,
			CP.RELEVANT_FLG		AS RELEVANT_FLG
		FROM
			COLLECTION_PRODUCT CP
			
			LEFT JOIN COLLECTION_IMG CI ON
			CP.IDX = CI.C_PRODUCT_IDX
		WHERE
			CP.IDX = ?
	";
	
	$db->query($select_collection_product_sql,array($no));
	
	foreach($db->fetch() as $data) {
		$collection_product = array(
			'c_product_idx'		=>$data['C_PRODUCT_IDX'],
			'img_location'		=>$data['IMG_LOCATION'],
			'relevant_flg'		=>$data['RELEVANT_FLG']
		);
	}
	
	return $collection_product;
}

?>