<?php
    // 각 버전 별 맞는 mobileOKManager-php를 사용
    $mobileOK_path = dir_f_api."/mok/mobileOK_manager_phpseclib_v3.0_v1.0.2.php";

    if(!file_exists($mobileOK_path)) {
        die('1000|mobileOK_Key_Manager파일이 존재하지 않습니다.');
    } else {
        require_once $mobileOK_path;
    }
	
    header("Content-Type:text/html;charset=utf-8");

    /* 1. 본인확인 서비스 API 설정 */
    $mobileOK = new mobileOK_Key_Manager();
    /* 키파일은 반드시 서버의 안전한 로컬경로에 별도 저장. 웹URL 경로에 파일이 있을경우 키파일이 외부에 노출될 수 있음 주의 */
    $key_path = dir_f_api."/mok/mok_keyInfo.dat";
    $password = "Dkejdpfj1!";
    $mobileOK->key_init($key_path, $password);

    // 이용기관 거래ID생성시 이용기관별 유일성 보장을 위해 설정, 이용기관식별자는 이용기관코드 영문자로 반드시 수정
    $clientPrefix = "ADER";     // 8자이내 영대소문자,숫자 (예) MOK, TESTCOKR
	
    /* 결과 수신 후 전달 URL 설정 */
    $result_return_url = "https://dev.adererror.com/_api/mok/mok-result";
	
    /* 2. 거래 정보 호출 */
    echo mobileOK_std_request($mobileOK,$result_return_url,$clientPrefix);
	
    /* 본인확인 표준창 인증 요청 예제 함수 */
    function mobileOK_std_request($mobileOK, $result_return_url, $clientPrefix) {
        // local시간 설정이 다르게 될  수 있음으로 기본 시간 설정을 서울로 해놓는다.
        date_default_timezone_set('Asia/Seoul');

        /* 3. 본인확인-표준창 거래요청정보 생성  */

        /* 3.1 이용기관 거래ID 생성, 20자 이상 40자 이내 이용기관 고유 트랜잭션ID (예시) 이용기관식별자+UUID, ...  */
        // - 본인확인-표준창 거래ID 는 유일한 값이어야 하며 기 사용한 거래ID가 있는 경우 오류 발생 
        // - 이용기관이 고유식별 ID로 유일성을 보장할 경우 고객이 이용하는 ID사용 가능 
		$uuid = uuid();
        $client_tx_id = $clientPrefix.$uuid;

        /* 3.2 인증 결과 검증을 위한 거래 ID 세션 저장 (권고) */
        // 동일한 세션내 요청과 결과가 동일한지 확인 및 인증결과 재사용 방지처리, "MOKResult" 응답결과 처리시 필수 구현
        session_start();
        $_SESSION['sessionClientTxId'] = $client_tx_id;

        /* 3.3 거래 ID, 인증 시간을 통한 본인확인 거래 요청 정보 생성  */
        $date_time = date("YmdHis");
        $req_client_info = $client_tx_id."|".$date_time;

        /* 3.4 생성된 거래정보 암호화 */
        $encrypt_req_client_info = $mobileOK->rsa_encrypt($req_client_info);

        /* 3.5 거래 요청 정보 JSON 생성 */
        $send_data = array(
            /* 본인확인 서비스 용도 */
            /* 01001 : 회원가입, 01002 : 정보변경, 01003 : ID찾기, 01004 : 비밀번호찾기, 01005 : 본인확인용, 01006 : 성인인증, 01007 : 상품구매/결제, 01999 : 기타 */
            'usageCode'=> '01001'
            /* 본인확인 서비스 ID */
            , 'serviceId'=>$mobileOK->get_service_id()
            /* 암호화된 본인확인 거래 요청 정보 */
            , 'encryptReqClientInfo'=>$encrypt_req_client_info
            /* 이용상품 코드 */
            /* 이용상품 코드, telcoAuth : 휴대폰본인확인 (SMS인증시 인증번호 발송 방식 "SMS")*/
            /* 이용상품 코드, telcoAuth-LMS : 휴대폰본인확인 (SMS인증시 인증번호 발송 방식 "LMS")*/
            , 'serviceType'=>'telcoAuth'
            /* 본인확인 결과 타입 */
            /* 본인확인 결과 타입, "MOKToken"  : 개인정보 응답결과를 이용기관 서버에서 본인확인 서버에 요청하여 수신 후 처리 */
            /* 본인확인 결과 타입, "MOKResult" : 개인정보 응답결과를 이용자 브라우져로 수신 후 처리 (이용시 반드시 재사용 방지처리 개발) */
            , 'retTransferType'=>'MOKToken'
            // , 'retTransferType'=>'MOKResult'
            /* 본인확인 결과 수신 URL - "https://" 포함한 URL 입력 */
            , 'returnUrl'=>$result_return_url
        );

        /* 3.6 거래 요청 정보 JSON 반환 */
        // JSON Encoding시 '/'입력시 '\\/'로 입력되는 현상을 방지하기 위해서 아래의 옵션을 사용
        return json_encode($send_data, JSON_UNESCAPED_SLASHES);
    }

    /* 거래 ID(uuid) 생성 예제 함수 */
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
?>
