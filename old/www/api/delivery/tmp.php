<?php
/*
 +=============================================================================
 | 
 | CJ 대한통운 - 1Day 토큰 공통함수
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.27
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once(dir_f_api."/delivery/common.php");

$token_num = checkToken($db);
print_r("TOKEN_NUM : ".$token_num);
$curl = curl_init();

curl_setopt_array($curl, [
	CURLOPT_URL				=>"https://dxapi-dev.cjlogistics.com:5054/ReqInvcNo",
	CURLOPT_RETURNTRANSFER	=>true,
	CURLOPT_ENCODING		=>"",
	CURLOPT_MAXREDIRS		=>10,
	CURLOPT_TIMEOUT			=>30,
	CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST	=>"POST",
	CURLOPT_POSTFIELDS		=>'
		{
			"DATA" : {
				"CLNTNUM"	:30426467,
				"TOKEN_NUM"	:"'.$token_num.'"
			}
		}
	',
	CURLOPT_HTTPHEADER => [
		"CJ-Gateway-APIKey:".$token_num,
		"Content-type:application/json",
		"Accept:application/json"
	],
]);

$response = curl_exec($curl);
$err = curl_error($curl);

if (!$err) {
	$result = json_decode($response,true);
	print_r($result);
	$result_cd = $result['RESULT_CD'];
	
	if ($result_cd == "S") {
		$delivery_num = $result['DATA']['INVC_NO'];
		print_r("DELIVERY_NUM : ".$delivery_num);
		
		return $delivery_num;
	}
}

?>