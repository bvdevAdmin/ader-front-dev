<?php

define('PW',new password());

/** 01. 변수 정리 **/
if(!isset($client_id) || !isset($client_key)) {
	$code = 752;
}
elseif(isset($client_secret) && $client_secret != '') {
	$code = 753;
}
else {
	/** 02. key 확인 **/
	if(!isset($client_secret)) $client_secret = '';
	$where = 'ID=? AND AUTH_KEY=? AND SECRET_KEY = ? ';
	$where_values = array($client_id,$client_key,$client_secret);	
	$data = $db->get($_TABLE['OAUTH'],$where,$where_values);
	if(!is_array($data) || sizeof($data) == 0) {
		$code = 300;
	}
	else {
		$data = $data[0];
		if($data['STATUS'] == 'N') {
			$code = 751;
		}
		else {
			$timestamp = time()+ (ACCESS_TOKEN_EXPIRE*60);
			$expire_datetime = date('Y-m-d H:i:s',$timestamp); // 유효기간 2시간
			$access_token = PW->encode($client_id.$expire_datetime.get_rand_number(10)); // 토큰 생성

			$refresh_timestamp = time()+ (REFRESH_TOKEN_EXPIRE*60);
			$refresh_expire_datetime = date('Y-m-d H:i:s',$refresh_timestamp);
			$refresh_token = PW->encode(get_rand_number(10).$client_id.$expire_datetime); 

			// 인증정보 업데이트
			try {
				if($db->update(
					$_TABLE['OAUTH'],
					array(
						'ACCESS_TOKEN' => $access_token,
						'EXPIRE_DATE' => $expire_datetime,
						'REFRESH_TOKEN' => $refresh_token,
						'REFRESH_EXPIRE_DATE' => $refresh_expire_datetime,
						'IP' => $_SERVER['REMOTE_ADDR']
					),$where,$where_values
				)) {
					$json_result = array(
						'token_type' => 'bearer',
						'access_token' => $access_token,
						'server_timestamp' => time(),
						'expire_timestamp' => $timestamp,
						'expire_datetime' => $expire_datetime,
						'refresh_token' => $refresh_token,
						'refresh_expire_timestamp' => $refresh_timestamp,
						'refresh_expire_datetime' => $refresh_expire_datetime
					);
				}
				else {
					$code = 750;
				}	
			}
			catch(Exceiption $e) {
				$code = 500;
				$msg = $e->getMessage();
			}
		}
	}
}