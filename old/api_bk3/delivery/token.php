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

/* 1Day 토큰 체크 */
function checkToken($db) {
	$cnt_token = $db->count("DELIVERY_TOKEN","DATE_FORMAT(NOW(),'%Y-%m-%d') <= DATE_FORMAT(TOKEN_DATE,'%Y-%m-%d')");
	
	$token_num = null;
	if ($cnt_token > 0) {
		$delivery_token = $db->get("DELIVERY_TOKEN","DATE_FORMAT(TOKEN_DATE,'%Y-%m-%d %H:%i:%s') < ?",array("DATE_FORMAT('%Y-%m-%d %H:%i:%s')"))[0];
		$token_num = $delivery_token['TOKEN_NUM'];
	} else {
		$token = generateToken();
		setToken($db,$token);
		
		$token_num = $token['token_num'];
	}
	
	return $token_num;
}

/* 1Day 토큰 발행 */
function generateToken() {
	$curl = curl_init();

	curl_setopt_array($curl, [
		CURLOPT_URL				=>"https://dxapi-dev.cjlogistics.com:5054/ReqOneDayToken",
		CURLOPT_RETURNTRANSFER	=>true,
		CURLOPT_ENCODING		=>"",
		CURLOPT_MAXREDIRS		=>10,
		CURLOPT_TIMEOUT			=>30,
		CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST	=>"POST",
		CURLOPT_POSTFIELDS		=>'
			{
				"DATA" : {
					"CUST_ID"		:30426467,
					"BIZ_REG_NUM"	:7608701757
				}
			}
		',
		CURLOPT_HTTPHEADER => [
			"Content-type:application/json",
			"Accept:application/json"
		],
	]);

	$response = curl_exec($curl);
	$err = curl_error($curl);

	if (!$err) {
		$result = json_decode($response,true);
		
		$result_cd = $result['RESULT_CD'];
		
		if ($result_cd == "S") {
			$token_num = $result['DATA']['TOKEN_NUM'];
			$token_date = date("Y-m-d H:i:s", strtotime($result['DATA']['TOKEN_EXPRTN_DTM']));
			
			$token = array(
				"token_num"		=>$token_num,
				"token_date"	=>$token_date,
			);
			
			return $token;
		}
	}
}

/* 1Day 토큰 저장 */
function setToken($db,$token) {
	$cnt_token = $db->count("DELIVERY_TOKEN");
	
	$result = null;
	if ($cnt_token > 0) {
		$result = $db->update(
			"DELIVERY_TOKEN",
			array(
				'TOKEN_NUM'		=>$token['token_num'],
				'TOKEN_DATE'	=>$token['token_date']
			)
		);
	} else {
		$db->insert(
			"DELIVERY_TOKEN",
			array(
				'TOKEN_NUM'		=>$token['token_num'],
				'TOKEN_DATE'	=>$token['token_date']
			)
		);
		
		$result = $db->last_id();
	}
	
	if ($result > 0) {
		$json_result['code'] = 200;
	} else {
		$json_result['code'] = 400;
	}
}

?>