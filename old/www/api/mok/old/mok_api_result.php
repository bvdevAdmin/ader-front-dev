<?php
	$mok_path = dir_f_api."/mok/phpseclib_path_3.0.php";
	
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
    /* 1. 본인확인 인증결과 MOKConfirm API 또는 MOKResult API 요청 URL */
    $MOK_CONFIRM_URL = "https://scert-dir.mobile-ok.com/agent/v1/confirm/request";  // 개발
    // $MOK_CONFIRM_URL = "https://cert-dir.mobile-ok.com/agent/v1/confirm/request";  // 운영

    /* 처음페이지 버튼 클릭시 이동 PHP (mobileOK-GetToken PHP)*/
    $MOK_API_GET_TOKEN_PHP	= dir_f_api."/mok/mok_api_gettoken.php";
    
	/* 재시도 버튼 클릭시 이동 PHP (mobileOK-Request PHP)*/
    $MOK_API_REQUEST_PHP	= dir_f_api."/mok/mok_api_request.php";

    /* 2. 본인확인 서비스 API 설정 */
    $mobileOK = new mobileOK_Key_Manager();
    /* 키파일은 반드시 서버의 안전한 로컬경로에 별도 저장. 웹URL 경로에 파일이 있을경우 키파일이 외부에 노출될 수 있음 주의 */
    $key_path = "/본인확인-API 키정보파일 Path/mok_keyInfo.dat";
    $password = "키파일 패스워드";
    $mobileOK->key_init($key_path, $password);

    $MOK_RESULT = mobileOK_api_result($mobileOK, $MOK_CONFIRM_URL);
?>
<?php
    /* 본인확인 API 검증요청 예제 함수 */
    function mobileOK_api_result($mobileOK, $MOK_CONFIRM_URL) {
        /* 3. 본인확인 검증요청 입력정보 설정 (아래 MOKConfirmRequestToJsonString() 참조) */
        $MOK_confirm_data = json_decode($_POST['MOKConfirmData']);

        $encryptMOKToken = isset($MOK_confirm_data->encryptMOKToken) ? $MOK_confirm_data->encryptMOKToken : null;
        if ($encryptMOKToken == null) {
            $errorResult = array("resultMsg" => "-1|본인확인 요청 MOKToken이 없습니다.");
            return json_encode($errorResult);
        } 

        $MOK_confirm_request_json_string = '';
        if (null != $_POST['authNumber']
                && '' != $_POST['authNumber']) {
            $MOK_confirm_request_json_string = MOKConfirmRequestToJsonString(
                $mobileOK
                , $MOK_confirm_data -> encryptMOKToken /* MOK_API_AUTH_REQUEST에서 발급 받은 encryptMOKToken */
                , $MOK_confirm_data -> publicKey       /* MOK_API_GET_TOKEN에서 발급 받은 publicKey */
                , $_POST['authNumber']                 /* 본인확인 인증요청으로 수신한 인증번호(authNumber)" */
            );
        } else {
            $MOK_confirm_request_json_string = MOKConfirmRequestToJsonString(
                $mobileOK
                , $MOK_confirm_data -> encryptMOKToken /* MOK_API_AUTH_REQUEST에서 발급 받은 encryptMOKToken */
                , $MOK_confirm_data -> publicKey       /* MOK_API_GET_TOKEN에서 발급 받은 publicKey */
                , ''                                   /* 본인확인 인증요청으로 수신한 인증번호(authNumber)" */
            );
        }

        /* 4. 본인확인 인증결과 확인 요청 */
        $MOK_confirm_response_json = sendPost($MOK_confirm_request_json_string, $MOK_CONFIRM_URL);

        /* 5. 본인확인 결과 JSON 정보 복호화 */
        try {
            $MOK_confirm_response_array = json_decode($MOK_confirm_response_json);
            if ('2000' != $MOK_confirm_response_array->resultCode) {
                $MOK_confirm_retry_data = array(
                    "resultCode" => $MOK_confirm_response_array -> resultCode
                    , "resultMsg" => $MOK_confirm_response_array -> resultMsg
                    , "publicKey" => $MOK_confirm_data -> publicKey
                );

                if (isset($MOK_confirm_response_array -> encryptMOKToken)) {
                    $MOK_confirm_retry_data["encryptMOKToken"] = $MOK_confirm_response_array -> encryptMOKToken;
                }

                return json_encode($MOK_confirm_retry_data, JSON_UNESCAPED_SLASHES);
            }

            /* 5-1 본인확인 결과정보 복호화 */
            $encrypt_MOK_result = $MOK_confirm_response_array->encryptMOKResult;
            $decrypt_result_json = $mobileOK->get_result($encrypt_MOK_result);
        } catch (Exception $e) {
            return $decrypt_result_json;
        }

        /* 6. 본인확인 인증결과 반환데이터 설정 */
        /* 사용자 이름 */
        $decrypt_result_array = json_decode($decrypt_result_json);
        $user_name = isset($decrypt_result_array->userName) ? $decrypt_result_array->userName : null;
        /* 이용기관 ID */
        $site_id = isset($decrypt_result_array->siteID) ? $decrypt_result_array->siteID : null;
        /* 이용기관 거래 ID */
        $client_tx_id = isset($decrypt_result_array->clientTxId) ? $decrypt_result_array->clientTxId : null;
        /* 본인확인 거래 ID */
        $tx_id = isset($decrypt_result_array->txId) ? $decrypt_result_array->txId : null;
        /* 서비스제공자(인증사업자) ID */
        $provider_id = isset($decrypt_result_array->providerId) ? $decrypt_result_array->providerId : null;
        /* 이용 서비스 유형 */
        $service_type = isset($decrypt_result_array->serviceType) ? $decrypt_result_array->serviceType : null;
        /* 시용자 CI */
        $ci = isset($decrypt_result_array->ci) ? $decrypt_result_array->ci : null;
        /* 사용자 DI */
        $di = isset($decrypt_result_array->di) ? $decrypt_result_array->di : null;
        /* 사용자 전화번호 */
        $user_phone = isset($decrypt_result_array->userPhone) ? $decrypt_result_array->userPhone : null;
        /* 사용자 생년월일 */
        $user_birthday = isset($decrypt_result_array->userBirthday) ? $decrypt_result_array->userBirthday : null;
        /* 사용자 성별 (1: 남자, 2: 여자) */
        $user_gender = isset($decrypt_result_array->userGender) ? $decrypt_result_array->userGender : null;
        /* 사용자 국적 (0: 내국인, 1: 외국인) */
        $user_nation = isset($decrypt_result_array->userNation) ? $decrypt_result_array->userNation : null;
        /* 본인확인 인증 종류 */
        $req_auth_type = $decrypt_result_array->reqAuthType;
        /* 본인확인 요청 시간 */
        $req_date = $decrypt_result_array->reqDate;
        /* 본인확인 인증 서버 */
        $issuer = $decrypt_result_array->issuer;
        /* 본인확인 인증 시간 */
        $issue_date = $decrypt_result_array->issueDate;

        /* 7. 이용기관 응답데이터 셔션 및 검증유효시간 처리  */
        // 세션 내 요청 clientTxId 와 수신한 clientTxId 가 동일한지 비교(권고)
        $session_client_tx_id = $_SESSION['sessionClientTxId'];
        if ($session_client_tx_id !== $client_tx_id){
            $errorResult = array("resultMsg" => "-4|세션값에 저장된 거래ID 비교 실패");
            return json_encode($errorResult);
        }
        // 검증정보 유효시간 검증 (토큰 생성 후 10분 이내 검증 권고)
        $date_time = date("Y-m-d H:i:s");

        $old = strtotime($issue_date);
        $old = date("Y-m-d H:i:s", $old);

        $time_limit = strtotime($old."+10 minutes");
        $time_limit = date("Y-m-d H:i:s", $time_limit);

        if ($time_limit < $date_time) {
            $errorResult = array("resultMsg" => "-5|토큰 생성 10분 경과");
            return '';
        }

        /* 8. 이용기관 서비스 기능 처리 */

        // - 이용기관에서 수신한 개인정보 검증 확인 처리

        // - 이용기관에서 수신한 CI 확인 처리


        /* 9. 본인확인 결과 응답 */

        // 복호화된 개인정보는 DB보관 또는 세션보관하여 개인정보 저장시 본인확인에서 획득한 정보로 저장하도록 처리 필요

        $result_array = array(
            "resultCode" => "2000"
            , "resultMsg" => "성공"
            , "userName" => $user_name
        );
        $result_json = json_encode($result_array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $result_json;
    }

    /* 본인확인-API 검증요청 정보 */
    function MOKConfirmRequestToJsonString($mobileOK, $encrypt_MOK_token, $public_key, $auth_number) {
        $confirm_request_array = array(
            'encryptMOKToken'=> $encrypt_MOK_token
        );

        if (null != $auth_number
                && '' != $auth_number) {
            $auth_number_array = array(
                'authNumber' => $auth_number
            );
            $auth_number_json = json_encode($auth_number_array, JSON_UNESCAPED_SLASHES);

            $enc_auth_number = $mobileOK->rsa_public_encrypt($public_key, $auth_number_json);

            $confirm_request_array['encryptMOKVerifyInfo'] = $enc_auth_number;
        }

        return json_encode($confirm_request_array, JSON_UNESCAPED_SLASHES);
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
        $response = curl_exec($curl);                                                     // 전송
        curl_close($curl);                                                                // 연결해제

        return $response;
    }
?>

<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>mok_api_result</title>
		<script>
			let MOKResultJson = '<?php echo $MOK_RESULT ?>';
			MOKResultJson = JSON.parse(MOKResultJson);

			if (MOKResultJson.resultCode != '2000') {
				/* 오류발생시 */
				window.alert(MOKResultJson.resultCode + ', ' + MOKResultJson.resultMsg);
			} else {
				/* 정상작동시 */
				document.addEventListener("DOMContentLoaded", function () {
					document.getElementById("MOKResultJsonString").innerText = JSON.stringify(MOKResultJson);
				});
			}
		</script>
	</head>
	
	<body>
		<p id='MOKResultJsonString'>
		</p>
		<br>
	   <form action='<?php echo $MOK_API_REQUEST_PHP ?>' method="post">
			<input type="hidden" id="MOKConfirmRetryData" name="MOKConfirmRetryData" value='<?php echo $MOK_RESULT ?>' />
			<input type="submit" style="width:160px; height:30px; margin-left:5px; margin-bottom:15px;" value='재시도' />
			<a href='<?php echo $MOK_API_GET_TOKEN_PHP ?>'> 
				<input type="button" style="width:160px; height:30px; margin-left:5px; margin-bottom:15px;" value='처음페이지' />
			</a>
		</form>

	</body>
</html>
