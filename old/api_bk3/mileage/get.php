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

include_once("/var/www/www/api/mileage/common.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if (!isset($country) || $member_idx == 0) {
    $json_result = false;
    $code	= 401;
    $msg = '로그인 후 다시 시도해주세요';
}

if (isset($basket_idx)) {
	$total_mileage_price = getTotalMileagePrice($db,$country,$member_idx);
	$total_basket_price = 0;
	
	if ($basket_idx != "ALL") {
		$total_basket_price = getTotalBasketPrice($db,$country,$member_idx,$basket_idx,$to_country_code);
		
		$check_result = checkBasketPrice($country,$total_basket_price);
		if ($check_result == true) {
			if ($total_mileage_price >= $total_basket_price) {
				$total_mileage_price = $total_basket_price;
			}
		} else {
			$total_mileage_price = 0;
		}
	}
	
	$usable_total_mileage = 0;
	if ($country == "KR") {
		$usable_total_mileage = floor(intval($total_mileage_price)/1000) * 1000;
	} else {
		$usable_total_mileage = floor(intval($total_mileage_price)/1) * 1;
	}
	
	$json_result['data'] = array(
		'total_mileage_point'	=>$usable_total_mileage,
		'txt_mileage_point'		=>number_format($usable_total_mileage),
	);
}

?>