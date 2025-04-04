<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 배송지 추가 // '/var/www/www/api/mypage/member/order_to/add.php'
 | -------
 |
 | 최초 작성	: 윤재은
 | 최초 작성일	: 2023.01.12
 | 최종 수정    : 양한빈
 | 최종 수정일	: 2024.05.28
 | 버전		: 1.1
 | 설명		: 2024.05.28 DB클래스 적용, 중복코드 정리, 필요없는 변수 삭제, 오류 발생시 json 출력이 되도록 수정
 |
 +=============================================================================
*/

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	$db->begin_transaction();
	
	try {
		$tmp_flg = 0;
		if (isset($default_flg) && $default_flg == "T") {
			$tmp_flg = 1;
			
			$db->update(
				"ORDER_TO",
				array(
					'DEFAULT_FLG'		=>0,
				),
				"COUNTRY = ? AND MEMBER_IDX = ?",
				array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'])
			);
		}

		$values = array(
			'COUNTRY'			=>$_SERVER['HTTP_COUNTRY'],
			'MEMBER_IDX'		=>$_SESSION['MEMBER_IDX'],
			'TO_PLACE'			=>$to_place,
			'TO_NAME'			=>$to_name,
			'TO_MOBILE'			=>$to_mobile,
			'TO_ZIPCODE'		=>$to_zipcode,
			'DEFAULT_FLG'		=>$tmp_flg
		);
		
		if ($_SERVER['HTTP_COUNTRY'] == "KR") {
			$values = array_merge(
				$values,
				array(
					'TO_LOT_ADDR'			=>$to_lot_addr,
					'TO_ROAD_ADDR'			=>$to_road_addr,
					'TO_DETAIL_ADDR'		=>$to_detail_addr
				)
			);
		} else {
			$values = array_merge(
				$values,
				array(
					'TO_COUNTRY_CODE'		=>$to_country_code,
					'TO_PROVINCE_IDX'		=>$to_province_idx,
					'TO_CITY'				=>$to_city,
					'TO_ADDRESS'			=>$to_address,
					'TO_DETAIL_ADDR'		=>$to_detail_addr
				)
			);
		}
		
		$db->insert("ORDER_TO",$values);
		
		$result_idx = $db->last_id();
		
		$db->commit();
		
		$json_result['data'] = $result_idx;
	} catch (mysqli_sql_exception $e) {
		$db->rollback();

		$json_encode['code'] = 302;
		$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0050',array());
		
		echo json_encode($json_result);
		exit;
	}
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
}

?>