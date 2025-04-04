<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 배송지 수정
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2024.10.15
 | 최종 수정	: 
 | 최종 수정일	: 
 | 버전		: 
 | 설명		: 
 |
 +=============================================================================
*/

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	try {
		switch ($action_type) {
			case "DELETE" :
				if ($address_idx != null) {
					$db->delete(
						"ORDER_TO",
						"IDX = ?",
						array($address_idx)
					);
				}
				
				break;
			
			case "DEFAULT" :
				if ($address_idx != null) {
					$db->update(
						"ORDER_TO",
						array(
							'DEFAULT_FLG'		=>0,
						),
						"COUNTRY = ? AND MEMBER_IDX = ?",
						array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'])
					);
					
					$db->update(
						"ORDER_TO",
						array(
							'DEFAULT_FLG'		=>1
						),
						"IDX = ?",
						array($address_idx)
					);
				}
				
				break;
			
			case "UPDATE" :
				$tmp_flg = 0;
				if (isset($default_flg) && $default_flg == "T") {
					$tmp_flg = 1;
					
					$db->update(
						"ORDER_TO",
						array(
							'DEFAULT_FLG'		=>0
						),
						"IDX != ? AND COUNTRY = ? AND MEMBER_IDX = ?",
						array($_SESSION['MEMBER_IDX'],$_SERVER['HTTP_COUNTRY'],$no)
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
							'TO_LOT_ADDR'		=>$to_lot_addr,
							'TO_ROAD_ADDR'		=>$to_road_addr,
							'TO_DETAIL_ADDR'	=>$to_detail_addr
						)
					);
				} else {
					$values = array_merge(
						$values,
						array(
							'TO_COUNTRY_CODE'	=>$to_country_code,
							'TO_PROVINCE_IDX'	=>$to_province_idx,
							'TO_CITY'			=>$to_city,
							'TO_ADDRESS'		=>$to_address
						)
					);
				}
				
				$db->update(
					"ORDER_TO",$values,
					"IDX = ?",
					array($no)
				);
				
				break;
		}
	} catch (mysqli_sql_exception $e) {
		$db->rollback();
		
		print_r($e);

		$json_encode['code'] = 302;
		$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_ERR_0069',array());
		
		echo json_encode($json_result);
		exit;
	}
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
}

?>