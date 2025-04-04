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
?>
<?php
    /* 1. 본인확인 인증결과 MOKAuth API 또는 MOKResend API 요청 URL */
    $MOK_REQUEST_URL = "https://scert-dir.mobile-ok.com/agent/v1/auth/request"; // 개발
    // $MOK_REQUEST_URL = "https://cert-dir.mobile-ok.com/agent/v1/auth/request"; // 운영
    $MOK_RESEND_URL = "https://scert-dir.mobile-ok.com/agent/v1/auth/resend";     // 개발
    // $MOK_RESEND_URL = "https://cert-dir.mobile-ok.com/agent/v1/auth/resend";   // 운영

    /* 완료 버튼 클릭시 이동 PHP (mobileOK-Result PHP)*/
    $MOK_API_RESULT_PHP		= dir_f_api."/mok/mok_api_result.php";
    
	/* 취소 버튼 클릭시 이동 PHP (mobileOK-GetToken PHP)*/
    $MOK_API_GET_TOKEN_PHP	= dir_f_api."/mok/mok_api_gettoken.php";
    
	/* 재전송 버튼 클릭시 이동 PHP (mobileOK-Request PHP)*/
    $MOK_API_REQUEST_PHP	= dir_f_api."/mok/mok_api_request.php";
    
	/* 완료 버튼 클릭시 이동 PHP (mobileOK-CALL PHP) => ARS일 경우*/
    $MOK_API_CALL_PHP		= dir_f_api."/mok/mok_ars_call.php";

    /* 2. 본인확인 서비스 API 초기화 */
    $mobileOK = new mobileOK_Key_Manager();
    /* 본인확인 키파일은 반드시 서버의 안전한 로컬경로에 별도 저장. 웹URL 경로에 파일이 있을경우 키파일이 외부에 노출될 수 있음 주의 */
    $key_path = "/본인확인-API 키정보파일 Path/mok_keyInfo.dat";
    $password = "키파일 패스워드";
    $mobileOK->key_init($key_path, $password);
    $mobileOK->set_site_url("본인확인-API 등록 사이트 URL");

    if (isset($_POST['normalFlag'])) { 
        $MOK_AUTH_REQUEST_RESULT = mobileOK_api_auth_request($mobileOK, $MOK_REQUEST_URL);
    } else if (isset($_POST['MOKAuthResendData'])) {
        $MOK_AUTH_REQUEST_RESULT = mobileOK_api_auth_resend($_POST['MOKAuthResendData'], $MOK_RESEND_URL);
    } else if (isset($_POST['MOKConfirmRetryData'])) {
        $MOK_confirm_retry_data = json_decode($_POST['MOKConfirmRetryData']);
        $MOK_confirm_retry_data -> resultCode = "2000";
        $MOK_AUTH_REQUEST_RESULT = json_encode($MOK_confirm_retry_data);
    } else {
        $error_data = array(
            'resultMsg' => '올바르지 않는 인증 사업자 구분코드 또는 본인확인 인증 처리 Type 입니다.'
        );

        $MOK_AUTH_REQUEST_RESULT = json_encode($error_data, JSON_UNESCAPED_SLASHES);
    }
?>

<?php
    /* 본인확인 API 인증요청 예제 함수 */
    function mobileOK_api_auth_request($mobileOK, $MOK_REQUEST_URL) {
        $MOK_auth_request_data = json_decode($_POST['MOKAuthRequestData']);

        /* 3. 본인확인 인증요청 입력정보 설정 (아래 MOKAuthInfoToJson() 참조) */
        $MOK_auth_request_json_string = '';
        if ('PASS' == $_POST['reqAuthType']) {
            $MOK_auth_request_json_string = MOKAuthRequestToJsonString(
                $mobileOK
                , $MOK_auth_request_data->publicKey       /* MOK_API_Gettoken 응답으로 받은 publicKey */
                , $MOK_auth_request_data->encryptMOKToken /* MOK_API_Gettoken 응답으로 받은 encryptMOKToken */
                , $mobileOK->get_site_url()  /* 본인확인-API 등록 사이트 URL */
                , $_POST['providerid']       /* 인증 사업자 구분코드 */
                , $_POST['reqAuthType']      /* 본인확인 인증 처리 Type 코드 */
                , $_POST['usageCode']        /* 서비스 이용 코드 */
                , $_POST['serviceType']      /* 이용상품 코드 */
                , $_POST['userName']         /* 사용자 이름 */
                , $_POST['userPhoneNum']     /* 사용자 전화번호 */
                , $_POST['retTransferType']  /* 요청 대상의 결과 전송 타입 */
                , ''                         /* 운영자와 협의 된 ARS 코드 */
                , ''                         /* 사용자 생년월일 */
                , ''                         /* 사용자 성별 */
                , ''                         /* 사용자 내외국인 정보 */
                , ''                         /* SMS 발송내용 또는 LMS 발송내용 */
                , ''                         /* SMS 발송번호 */
                , null                       /* 이용기관별 추가정보 전달처리시 이용 JSON Object */
            );
        } else if ('telcoAuth-ARSAuth' == $_POST['serviceType']) {
            $extension = array("arsRequestMsg" => $_POST['arsRequestMsg']);

            $MOK_auth_request_json_string = MOKAuthRequestToJsonString(
                $mobileOK
                , $MOK_auth_request_data->publicKey       /* MOK_API_Gettoken 응답으로 받은 publicKey */
                , $MOK_auth_request_data->encryptMOKToken /* MOK_API_Gettoken 응답으로 받은 encryptMOKToken */
                , $mobileOK->get_site_url()  /* 본인확인-API 등록 사이트 URL */
                , $_POST['providerid']       /* 인증 사업자 구분코드 */
                , $_POST['reqAuthType']      /* 본인확인 인증 처리 Type 코드 */
                , $_POST['usageCode']        /* 서비스 이용 코드 */
                , $_POST['serviceType']      /* 이용상품 코드 */
                , $_POST['userName']         /* 사용자 이름 */
                , $_POST['userPhoneNum']     /* 사용자 전화번호 */
                , $_POST['retTransferType']  /* 요청 대상의 결과 전송 타입 */
                , $_POST['arsCode']          /* 운영자와 협의 된 ARS 코드 */
                , $_POST['userBirthday']     /* 사용자 생년월일 */
                , $_POST['userGender']       /* 사용자 성별 */
                , $_POST['userNation']       /* 사용자 내외국인 정보 */
                , $_POST['sendMsg']          /* SMS 발송내용 또는 LMS 발송내용 */
                , $_POST['replyNumber']      /* SMS 발송번호 */
                , $extension                 /* 이용기관별 추가정보 전달처리시 이용 JSON Object */
            );
        } else if (strpos($_POST['serviceType'], 'telcoAuth') !== false
                && 'SMS' == $_POST['reqAuthType']
                || 'LMS' == $_POST['reqAuthType']) {
            $MOK_auth_request_json_string = MOKAuthRequestToJsonString(
                $mobileOK
                , $MOK_auth_request_data->publicKey       /* MOK_API_Gettoken 응답으로 받은 publicKey */
                , $MOK_auth_request_data->encryptMOKToken /* MOK_API_Gettoken 응답으로 받은 encryptMOKToken */
                , $mobileOK->get_site_url()  /* 본인확인-API 등록 사이트 URL */
                , $_POST['providerid']       /* 인증 사업자 구분코드 */
                , $_POST['reqAuthType']      /* 본인확인 인증 처리 Type 코드 */
                , $_POST['usageCode']        /* 서비스 이용 코드 */
                , $_POST['serviceType']      /* 이용상품 코드 */
                , $_POST['userName']         /* 사용자 이름 */
                , $_POST['userPhoneNum']     /* 사용자 전화번호 */
                , $_POST['retTransferType']  /* 요청 대상의 결과 전송 타입 */
                , ''                         /* 운영자와 협의 된 ARS 코드 */
                , $_POST['userBirthday']     /* 사용자 생년월일 */
                , $_POST['userGender']       /* 사용자 성별 */
                , $_POST['userNation']       /* 사용자 내외국인 정보 */
                , $_POST['sendMsg']          /* SMS 발송내용 또는 LMS 발송내용 */
                , $_POST['replyNumber']      /* SMS 발송번호 */
                , null                       /* 이용기관별 추가정보 전달처리시 이용 JSON Object */
            );
        } else if ('SMSAuth' == $_POST['serviceType']) {
            $MOK_auth_request_json_string = MOKAuthRequestToJsonString(
                $mobileOK
                , $MOK_auth_request_data->publicKey       /* MOK_API_Gettoken 응답으로 받은 publicKey */
                , $MOK_auth_request_data->encryptMOKToken /* MOK_API_Gettoken 응답으로 받은 encryptMOKToken */
                , $mobileOK->get_site_url()  /* 본인확인-API 등록 사이트 URL */
                , $_POST['providerid']       /* 인증 사업자 구분코드 */
                , $_POST['reqAuthType']      /* 본인확인 인증 처리 Type 코드 */
                , $_POST['usageCode']        /* 서비스 이용 코드 */
                , $_POST['serviceType']      /* 이용상품 코드 */
                , $_POST['userName']         /* 사용자 이름 */
                , $_POST['userPhoneNum']     /* 사용자 전화번호 */
                , $_POST['retTransferType']  /* 요청 대상의 결과 전송 타입 */
                , ''                         /* 운영자와 협의 된 ARS 코드 */
                , ''                         /* 사용자 생년월일 */
                , ''                         /* 사용자 성별 */
                , ''                         /* 사용자 내외국인 정보 */
                , $_POST['sendMsg']          /* SMS 발송내용 또는 LMS 발송내용 */
                , $_POST['replyNumber']      /* SMS 발송번호 */
                , null                       /* 이용기관별 추가정보 전달처리시 이용 JSON Object */
            );
        } else {
            $error_data = array(
                'resultMsg' => '지정된 인증 사업자 구분코드(reqAuthType) 또는 이용상품 코드(serviceType)이 아닙니다.'
            );

            $MOK_AUTH_REQUEST_RESULT = json_encode($error_data, JSON_UNESCAPED_SLASHES);
        }

        /* 4. 본인확인 인증요청 */
        $MOK_auth_response_json = sendPost($MOK_auth_request_json_string, $MOK_REQUEST_URL);
        $MOK_auth_response_array = json_decode($MOK_auth_response_json);

        /* 5. 본인확인 검증요청 API에서 이용할 데이터 설정 */
        if('telcoAuth-ARSAuth' == $_POST['serviceType']){
            $MOK_confirm_data = array(
                  'encryptMOKToken' => $MOK_auth_response_array->encryptMOKToken
                , 'publicKey' => $MOK_auth_request_data->publicKey
                , 'resultCode' => $MOK_auth_response_array->resultCode
                , 'resultMsg' => $MOK_auth_response_array->resultMsg
                , 'arsOtpNumber' => $MOK_auth_response_array->arsOtpNumber
            );
        } else {
            $MOK_confirm_data = array(
                  'encryptMOKToken' => $MOK_auth_response_array->encryptMOKToken
                , 'publicKey' => $MOK_auth_request_data->publicKey
                , 'resultCode' => $MOK_auth_response_array->resultCode
                , 'resultMsg' => $MOK_auth_response_array->resultMsg
                , 'arsOtpNumber' => ''
            );
        }

        return json_encode($MOK_confirm_data, JSON_UNESCAPED_SLASHES);
    }

    /* 본인확인-API 인증재요청 예제 함수 */
    function mobileOK_api_auth_resend($MOK_auth_resend_data, $MOK_RESEND_URL) {
        $MOK_auth_resend_data_array = json_decode($MOK_auth_resend_data);

        /* 1. 본인확인 재전송 요청 입력정보 설정 */
        $MOK_auth_resend_request_array = array(
            'encryptMOKToken' => $MOK_auth_resend_data_array->encryptMOKToken
        );
        $MOK_auth_resend_request_json = json_encode($MOK_auth_resend_request_array);

        /* 2. 본인확인 재전송 요청 */
        $MOK_auth_resend_response_json = sendPost($MOK_auth_resend_request_json, $MOK_RESEND_URL);

        /* 3. 본인확인 재전송 결과 정보 */
        $MOK_auth_resend_response_array = json_decode($MOK_auth_resend_response_json);

        /* 4. 본인확인 재전송 실패시 */
        if ($MOK_auth_resend_response_array->resultCode != '2000') {
            return json_encode($MOK_auth_resend_response_array);
        }

        /* 5 본인확인 결과요청 입력 정보 설정 */
        $MOK_confirm_data = array(
            'reqAuthType' => $MOK_auth_resend_data_array->reqAuthType
            , 'encryptMOKToken' => $MOK_auth_resend_response_array->encryptMOKToken
            , 'publicKey' => $MOK_auth_resend_data_array->publicKey
            , 'resultCode' => $MOK_auth_resend_response_array->resultCode
            , 'resultMsg' => $MOK_auth_resend_response_array->resultMsg
        );

        return json_encode($MOK_confirm_data, JSON_UNESCAPED_SLASHES);
    }

    /* 본인확인-API 인증요청 정보 */
    function MOKAuthRequestToJsonString(
        $mobileOK
        , $public_key
        , $encrypt_MOK_token
        , $site_url
        , $provider_id
        , $req_auth_type
        , $usage_code
        , $service_type
        , $user_name
        , $user_phone_num
        , $ret_transfer_type
        , $ars_code
        , $user_birthday
        , $user_gender
        , $user_nation
        , $send_msg
        , $reply_number
        , $extension) {

        $MOK_auth_info_array = array(
            "providerId" => $provider_id
            , "reqAuthType" => $req_auth_type
            , "usageCode" => $usage_code
            , "serviceType" => $service_type
            , "userName" => $user_name
            , "userPhone" => $user_phone_num
            , "retTransferType" => $ret_transfer_type
        );
        if ('telcoAuth-ARSAuth' == $_POST['serviceType']) {
            $MOK_auth_info_array["arsCode"] = $ars_code;
            $MOK_auth_info_array["userBirthday"] = $user_birthday;
            $MOK_auth_info_array["userGender"] = $user_gender;
            $MOK_auth_info_array["userNation"] = $user_nation;
            $MOK_auth_info_array["extension"] = $extension;

            if ("" != $reply_number) {
                $MOK_auth_info_array["replyNumber"] = $reply_number;
            }
        } else if (strpos($_POST['serviceType'], 'telcoAuth') !== false
                && 'SMS' == $_POST['reqAuthType']
                || 'LMS' == $_POST['reqAuthType']) {
            $MOK_auth_info_array["userBirthday"] = $user_birthday;
            $MOK_auth_info_array["userGender"] = $user_gender;
            $MOK_auth_info_array["userNation"] = $user_nation;
            if ("" != $send_msg) {
                $MOK_auth_info_array["sendMsg"] = $send_msg;
            }
            if ("" != $reply_number) {
                $MOK_auth_info_array["replyNumber"] = $reply_number;
            }
        } else if ("SMSAuth" == $service_type) {
            if ("" != $send_msg) {
                $MOK_auth_info_array["sendMsg"] = $send_msg;
            }
            if ("" != $reply_number) {
                $MOK_auth_info_array["replyNumber"] = $reply_number;
            }
        } 

        $enc_MOK_auth_info = $mobileOK->rsa_public_encrypt($public_key, json_encode($MOK_auth_info_array, JSON_UNESCAPED_SLASHES));

        $auth_request_array = array(
            "siteUrl" => $site_url
            , "encryptMOKToken" => $encrypt_MOK_token
            , "encryptMOKAuthInfo" => $enc_MOK_auth_info
        );

        return json_encode($auth_request_array, JSON_UNESCAPED_SLASHES);
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
		<title>mok_api_auth_request</title>
		<script>
			const MOKAuthRequestJson = decodeURIComponent('<?php echo $MOK_AUTH_REQUEST_RESULT ?>');
			const MOKAuthRequestJsonObject = JSON.parse(MOKAuthRequestJson);

			document.addEventListener("DOMContentLoaded", function () {
				if (MOKAuthRequestJsonObject.resultCode != '2000') {
					/* 오류발생시 */
					window.alert(MOKAuthRequestJsonObject.resultCode + ', ' + MOKAuthRequestJsonObject.resultMsg);
				} else {
					/* 정장작동시 */
					document.getElementById("MOKConfirmData").value = MOKAuthRequestJson;
					document.getElementById("MOKAuthResendData").value = MOKAuthRequestJson;
					document.getElementById("MOKCallData").value = MOKAuthRequestJson;
					document.getElementById("arsOtpNumber").innerText = MOKAuthRequestJsonObject.arsOtpNumber;
				}
			});
		</script>
	</head>
	
	<body>
		<form action='<?php echo $MOK_API_RESULT_PHP ?>' method="post">
			<h1> Auth Request Page </h1>
			<input type="hidden" id="MOKConfirmData" name="MOKConfirmData" value='' />

			<h3>인증완료 후 완료버튼을 눌러주세요.</h2>
			<input type="submit" value="완료" style="width:160px; height:30px; margin-right:5px;" />

			<a href='<?php echo $MOK_API_GET_TOKEN_PHP ?>'> 
				<input type="button" style="width:160px; height:30px; margin-left:5px; margin-bottom:15px;" value='취소' />
			</a>
			
			<!-- 인증방식이 SMS일 경우에만 사용 START -->
			<h3>(인증번호를 수신하는 경우) 인증번호를 입력 후 완료버튼을 눌러주세요.</h3>
			인증번호 : <input type="text" id="authNumber" name="authNumber" value='' style="width: 245px; height: 20px; margin-bottom: 15px;"/> (인증방식 SMS, LMS일 경우 필수) <br>
			<input type="submit" value="완료" style="width:160px; height:30px; margin-right:5px;" />

				<a href='<?php echo $MOK_API_GET_TOKEN_PHP ?>'> 
					<input type="button" style="width:160px; height:30px; margin-left:5px; margin-bottom:15px;" value='취소' />
				</a>
			<!-- 인증방식이 SMS일 경우에만 사용 END -->
		</form>
		
		<form action="<?php echo $MOK_API_REQUEST_PHP ?>" method="post">
			<input type="hidden" id="MOKAuthResendData" name="MOKAuthResendData" value='' />
			<button id="resend" name="resend" style="width:335px; height:30px;" >재요청</button>
		</form>
		<!-- 인증방식이 ARS 점유인증일 경우에만 사용 END -->
		<form action="<?php echo $MOK_API_CALL_PHP ?>" method="post">
			<input type="hidden" id="MOKCallData" name="MOKCallData" value='' />     
			<h3>ARS 전화요청 인증번호 (인증번호를 ARS전화요청시 입력하여 주십시오.)</h3>
			<div>인증번호 : <span id="arsOtpNumber"></span></div>
			<input type="submit" value="전화걸기" style="width:160px; height:30px; margin-right:5px;" />
			<a href="<?php $MOK_API_GET_TOKEN_PHP ?>"> 
				<input type="button" style="width:160px; height:30px; margin-left:5px; margin-bottom:15px;" value='취소' />
			</a>
		</form>
	</body>
</html>
