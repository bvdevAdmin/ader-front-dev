<?php
	error_reporting( E_ALL );
	ini_set( "display_errors", 1 );
	
    // 각 버전 별 맞는 mobileOKManager-php를 사용
    $mobileOK_path = dir_f_api."/mok/mobileOK_manager_phpseclib_v3.0_v1.0.2.php";
	
    if(!file_exists($mobileOK_path)) {
        die('1000|mobileOK_Key_Manager파일이 존재하지 않습니다.');
    } else {
        require_once $mobileOK_path;
    }
?>

<?php
    // local시간 설정이 다르게 될  수 있음으로 기본 시간 설정을 서울로 해놓는다.
    date_default_timezone_set('Asia/Seoul');
	
    session_start();
?>

<?php
	/* 1. 본인확인 인증결과 MOKGetToken API 요청 URL */
    $MOK_GET_TOKEN_URL = "https://scert-dir.mobile-ok.com/agent/v1/token/get";  // 개발
    // $MOK_GET_TOKEN_URL = "https://cert-dir.mobile-ok.com/agent/v1/token/get";  // 운영

    /* 요청하기 버튼 클릭시 이동 PHP (mobileOK-Request PHP) */
    $MOK_API_REQUEST_PHP = dir_f_api."/mok/mok_api_request.php";
	
	/* 2. 본인확인 키파일을 통한 비밀키 설정 */
    $mobileOK = new mobileOK_Key_Manager();
    
	/* 키파일은 반드시 서버의 안전한 로컬경로에 별도 저장. 웹URL 경로에 파일이 있을경우 키파일이 외부에 노출될 수 있음 주의 */
    $key_path = dir_f_api."/mok/mok_keyInfo.dat";
    $password = "Dkejdpfj1!";
	
    $mobileOK->key_init($key_path, $password);
    $mobileOK->set_site_url("dev.adererror.com");
	
	// 이용기관 거래ID생성시 이용기관별 유일성 보장을 위해 설정, 이용기관식별자는 이용기관코드 영문자로 반드시 수정
    $PREFIX_ID = 'ADER';  // 8자이내 영대소문자,숫자  (예) MOK, TESTCOKR
	
	$auth_request_string = mobileOK_api_gettoken($mobileOK, $PREFIX_ID, $MOK_GET_TOKEN_URL);
	
	/* 본인확인 API 거래 정보 요청 예제 함수 */
    function mobileOK_api_gettoken ($mobileOK, $PREFIX_ID, $MOK_GET_TOKEN_URL) {
        /* 3. 본인확인-API 거래요청정보 생성  */

        /* 3.1 이용기관 거래ID 생성, 20자 이상 40자 이내 이용기관 고유 트랜잭션ID (예시) 이용기관식별자+UUID, ...  */
        // - 본인확인-API 거래ID 는 유일한 값이어야 하며 기 사용한 거래ID가 있는 경우 오류 발생 
        // - 이용기관이 고유식별 ID로 유일성을 보장할 경우 고객이 이용하는 ID사용 가능 
		$uuid = uuid();
		
        $sample_client_tx_id = $PREFIX_ID.$uuid;
		
        /* 3.2 인증 결과 검증을 위한 거래 ID 세션 저장 (권고) */
        // 동일한 세션내 요청과 결과가 동일한지 확인 및 인증결과 재사용 방지처리 구현
        $_SESSION['sessionClientTxId'] = $sample_client_tx_id;
		
        /* 4. 본인확인 토큰요청 데이터 설정 (아래 MOKGetTokenRequestToJsonString() 참조) */
        $MOK_get_token_request_json_string = MOKGetTokenRequestToJsonString(
            $mobileOK,
			$mobileOK->get_service_id(),	/* 본인확인 서비스 ID */
			$sample_client_tx_id,			/* 암호화된 이용기관 식별 ID */
			$mobileOK->get_site_url()		/* 본인확인-API 등록 사이트 URL */
        );
		print("json String : ".$MOK_get_token_request_json_string);
		print("json String : ".$MOK_GET_TOKEN_URL);
        /* 5. 본인확인 토큰요청 */
        $MOK_get_token_response_json = sendPost($MOK_get_token_request_json_string, $MOK_GET_TOKEN_URL);

        /* 6. 본인확인 토큰요청 후 응답 데이터 설정*/
        $MOK_get_token_response_array = json_decode($MOK_get_token_response_json);

        /* 6-1. 본인확인 인증요청 API에서 이용할 데이터 설정 */
        $MOK_auth_request_data = array (
            'encryptMOKToken'	=>$MOK_get_token_response_array->encryptMOKToken,
			'publicKey'			=>$MOK_get_token_response_array->publicKey,
			'resultCode'		=>$MOK_get_token_response_array->resultCode,
			'resultMsg'			=>$MOK_get_token_response_array->resultMsg
        );
		
        return json_encode($MOK_auth_request_data, JSON_UNESCAPED_SLASHES);
    }
	
	/* 본인확인-API 토큰요청 입력데이터 설정 예제 함수 */
    function MOKGetTokenRequestToJsonString($mobileOK,$service_id,$client_tx_id,$site_url) {
        $date_time = date("YmdHis");
        $client_tx_id = $client_tx_id."|".$date_time;

        $encrypt_req_client_info = $mobileOK->rsa_encrypt($client_tx_id);

        $MOK_token_request_array = array(
            'serviceId'				=>$service_id,
			'encryptReqClientInfo'	=>$encrypt_req_client_info,
			'siteUrl'				=>$site_url
        );

        return json_encode($MOK_token_request_array,JSON_UNESCAPED_SLASHES);
    }
	
	/* 본인확인 이용기관 샘플 거래ID(clientTxId(uuid)) 생성 예제 함수 */
    function uuid() {
        return sprintf('%04x%04x%04x%04x%04x%04x%04x%04x',
			// 32 bits for "time_low"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff),

			// 16 bits for "time_mid"
			mt_rand(0, 0xffff),

			// 16 bits for "time_hi_and_version",
			// four most significant bits holds version number 4
			mt_rand(0, 0x0fff) | 0x4000,

			// 16 bits, 8 bits for "clk_seq_hi_res",
			// 8 bits for "clk_seq_low",
			// two most significant bits holds zero and one for variant DCE1.1
			mt_rand(0, 0x3fff) | 0x8000,

			// 48 bits for "node"
			mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );
    }
	
	/* 본인확인 서버 통신 예제 함수 */
    function sendPost($data, $url) {
        $curl = curl_init();                                                              // curl 초기화
        curl_setopt($curl, CURLOPT_URL, $url);                                            // 데이터를 전송 할 URL
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);                                 // 요청결과를 문자열로 반환
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));  // 전송 ContentType을 Json형식으로 설정
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);                                // 원격 서버의 인증서가 유효한지 검사 여부
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);                                    // POST DATA
        curl_setopt($curl, CURLOPT_POST, true);                                           // POST 전송 여부
		curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 0); 
		curl_setopt($curl, CURLOPT_TIMEOUT, 400);										  //timeout in seconds
        
		$response = curl_exec($curl);                                                     // 전송
		
        curl_close($curl);                                                                // 연결해제

        return $response;
    }
?>

<html>
	<head>
		<meta charset="utf-8"/>
		<title>mok_api_gettoken</title>
		<script>
			const MOKAuthRequestData = <?php echo $auth_request_string ?>;
			if (MOKAuthRequestData.resultCode != '2000') {
				/* 오류발생시 */
				window.alert(MOKAuthRequestData.resultCode + ', ' + MOKAuthRequestData.resultMsg);
			} else {
				document.addEventListener("DOMContentLoaded", function () {
					/* 정상작동시 */
					document.getElementById("MOKAuthRequestData").value = JSON.stringify(MOKAuthRequestData);

				});
			}

		</script>
	</head>
	
	<body>
		<form action=<?php echo $MOK_API_REQUEST_PHP ?> method="post">
			<p>이용상품 코드 별 사용자 인증요청 정보 (MOKAuthInfo) 정보를 참고하여 작성</p>
			(공통 필수) <input type="text" required id="serviceType" name="serviceType"  value="" placeholder="telcoAuth" /> 이용상품 코드 (serviceType => "telcoAuth":휴대폰본인확인, telcoAuth-LMS : "휴대폰본인확인 LMS") <br>
			(공통 필수) <input type="text" required id="providerid" name="providerid" value="" placeholder="SKT" style="left-padding:10px" /> 인증 사업자 구분코드 (providerId => "SKT","KT","LGU","SKTMVNO","KTMVNO","LGUMVNO") <br>
			(공통 필수) <input type="text" required id="reqAuthType" name="reqAuthType" value="" placeholder="PASS" /> 본인확인 인증 처리 Type 코드 (reqAuthType => "SMS" : SMS인증, "PASS" : PASS앱 인증) <br>
			(공통 선택) <input type="text" id="usageCode" name="usageCode" value="" placeholder="01001" /> 서비스 이용 코드 (usageCode) <br>
			(공통 필수) <input type="text" id="retTransferType" name="retTransferType" value="MOKResult" /> 요청 대상의 결과 전송 타입 (retTransferType) <br>
			(공통 필수) <input type="text" required id="userName" name="userName" placeholder="홍길동" value="" /> 이름 (userName) <br> 
			(공통 필수) <input type="tel" required id="userPhoneNum" name="userPhoneNum" value="" placeholder="숫자만 (-제외)" /> 핸드폰 번호 (userPhone) <br>
			<input type="text" id="arsCode" name="arsCode" placeholder="ARS-TEST-NVR-11" style="width:250px" /> 운영자와 협의 된 ARS 코드 (arsCode) <br>
			<input type="tel" id="userBirthday" name="userBirthday" value="" placeholder="20000101" style="width:250px"/> 생년월일(8자리) (userBirthday) <br>
			<input type="text" id="userGender" name="userGender" value="" placeholder="남자 : 1, 여자 : 2" style="width:250px"/> 성별 (userGender => "1" : 남자, "2" : 여자) <br>
			<input type="text" id="userNation" name="userNation" value="" placeholder="내국인 : 0, 외국인 : 1" style="width:250px"/> 내외국인 정보 (userNation => "0" : 내국인, "1" : 외국인) <br>
			<textarea id="sendMsg" name="sendMsg" placeholder="[드림시큐리티] 인증번호 [000000]" style="width:250px"></textarea> SMS 전송 시 사용할 메시지로 사용할 값 (sendMsg)<br>
			<input type="text" id="replyNumber" name="replyNumber" value="" placeholder="15880000" style="width:250px"/> SMS 또는 ARS 전송 시 사용할 송신번호 (replyNumber) <br>
			<input type="text" id="arsRequestMsg" name="arsRequestMsg" placeholder='{"companyName":"이용기관명"}' style="width:350px"/> ARS 점유인증 요청시 추가정보 입력 extension/arsRequestMsg <br>
			<input type="submit" style="width:400px; height:30px; margin-top:10px; margin-bottom:10px" value="요청하기"/><br>

			<input type="hidden" id="MOKAuthRequestData" name="MOKAuthRequestData" value=''/>
			<input type="hidden" id="normalFlag" name="normalFlag" value='Y'/>
		</form>
	</body>
</html>