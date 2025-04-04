<?php

/*
+=============================================================================
| 
| 최대 마일리지 체크 API
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
include_once("/var/www/www/api/common.php");
include_once("/var/www/www/api/mileage/common.php");

$country = null;
if (isset($_SESSION['COUNTRY'])) {
	$country = $_SESSION['COUNTRY'];
}

$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

$param_mileage = 0;
if(isset($_POST['param_mileage'])){
	$param_mileage = intval($_POST['param_mileage']);
}

if (!isset($country) || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
}

if ($param_mileage > 0 && isset($param_mileage)) {
	/* 적립금 사용 불가 상품 체크 */
	$check_result = checkBasketMileageFlg($db,$country,$member_idx,$basket_idx);
	if ($check_result == false) {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0075', array());
		
		echo json_encode($json_result);
		exit;
	}
	
	/* 현재 보유중인 적립금 총액 */
	$total_mileage_price = getTotalMileagePrice($db,$country,$member_idx);
	
	/* 구매하려는 쇼핑백 내 상품의 총액 */
	$total_basket_price = getTotalBasketPrice($db,$country,$member_idx,$basket_idx,$to_country_code);
	
	$check_result = checkBasketPrice($country,$total_basket_price);
	if ($check_result == true) {
		/* 입력 한 적립금 금액이 현재 보유량을 초과하는 경우 */
		if($param_mileage > $total_mileage_price) {
			$param_mileage = $total_mileage_price;
		}
		
		/* 입력 한 적립금 금액이 구매하려는 쇼핑백 내 상품의 총액을 초과하는 경우 */
		if ($param_mileage > $total_basket_price) {
			$param_mileage = $total_basket_price;
		}
	} else {
		$param_mileage = 0;
	}
	
	$usable_mileage = 0;
	if ($country == "KR") {
		$usable_mileage = floor(intval($param_mileage)/1000) * 1000;
	} else {
		$usable_mileage = floor(intval($param_mileage)/1) * 1;
	}
	
	
	$json_result['data'] = array(
		'mileage_point'			=>$usable_mileage,
		'txt_mileage_point'		=>number_format($usable_mileage)
	);
}

?>