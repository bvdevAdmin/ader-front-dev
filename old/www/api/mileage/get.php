<?php
/*
 +=============================================================================
 | 
 | 회원별 마일리지 정보 취득 API
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2023.01.02
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 |            
 | 
 +=============================================================================
*/

include_once(dir_f_api."/mileage/common.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if (!isset($country) || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$country,'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
}

if (isset($basket_idx)) {
	/* 현재 보유중인 적립금 총액 조회 */
	$total_mileage_price = getTotalMileagePrice($db,$country,$member_idx);
	$total_basket_price = 0;
	
	if ($basket_idx != "ALL") {
		/* 구매하려는 쇼핑백 내 상품의 총액 조회 */
		$total_basket_price = getTotalBasketPrice($db,$country,$member_idx,$basket_idx,$to_country_code);
		
		/* 국가별 적립금 사용 가능금액 체크 */
		$check_result = checkBasketPrice($country,$total_basket_price);
		if ($check_result == true) {
			if ($total_mileage_price >= $total_basket_price) {
				$total_mileage_price = $total_basket_price;
			}
		} else {
			$total_mileage_price = 0;
		}
	}
	
	/* 6. 적립금 최소금액 절사처리 */
	$usable_total_mileage = 0;
	if ($country == "KR") {
		/* 한국몰 적립금 절사금액 : 1000원 */
		$usable_total_mileage = floor(intval($total_mileage_price)/1000) * 1000;
	} else {
		/* 영문몰/중문몰 적립금 절사금액 : 1원 */
		$usable_total_mileage = floor(intval($total_mileage_price)/1) * 1;
	}
	
	$json_result['data'] = array(
		'total_mileage_point'	=>$usable_total_mileage,
		'txt_mileage_point'		=>number_format($usable_total_mileage),
	);
}

?>