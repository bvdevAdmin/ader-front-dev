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

$basket_idx = null;
if (isset($_POST['basket_idx'])) {
	$basket_idx = $_POST['basket_idx'];
}

$param_mileage = 0;
if(isset($_POST['param_mileage'])){
	$param_mileage = intval($_POST['param_mileage']);
}

$to_country_code = null;
if (isset($_POST['to_country_code'])) {
	$to_country_code = $_POST['to_country_code'];
}

if ($country == null || $member_idx == 0) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0018', array());
}

if ($param_mileage > 0 && $basket_idx != null) {
	$check_result = checkBasketMileageFlg($db,$country,$member_idx,$basket_idx);
	
	if ($check_result == false) {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0075', array());
		
		echo json_encode($json_result);
		exit;
	}
	
	$total_mileage_price = getTotalMileagePrice($db,$country,$member_idx);
	
	$total_basket_price = getTotalBasketPrice($db,$country,$member_idx,$basket_idx,$to_country_code);
	
	if($param_mileage > $total_mileage_price) {
		$param_mileage = $total_mileage_price;
	}
	
	if ($param_mileage > $total_basket_price) {
		$param_mileage = $total_basket_price;
	}
	$usable_mileage = floor(intval($param_mileage)/1000) * 1000;
	$json_result['data'] = array(
		'mileage_point'			=>$usable_mileage,
		'txt_mileage_point'		=>number_format($usable_mileage)
	);
}

?>