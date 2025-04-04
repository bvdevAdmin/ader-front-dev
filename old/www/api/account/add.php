<?php
/*
 +=============================================================================
 | 
 | 회원 가입 - 신규 회원 가입
 | -------
 |
 | 최초 작성    : 박성혁
 | 최초 작성일    : 2022.11.30
 | 최종 수정일    : 
 | 버전        : 1.0
 | 설명        : 
 |            
 | 
 +=============================================================================
*/

include_once(dir_f_api."/common.php");
include_once(dir_f_api."/send/send-mail.php");
include_once(dir_f_api."/send/send-kakao.php");

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

/* 1. 동일 ID 중복체크 */
$check_result = checkDuplicate($db, $member_id);
if ($check_result == true) {
    $param_member_id = $member_id ? ['MEMBER_ID', $member_id] : null;
    $param_member_pw = $member_pw ? ['MEMBER_PW', md5($member_pw)] : null;
    $param_member_name = $member_name ? ['MEMBER_NAME', $member_name] : null;

    /* PARAM 회원 주소정보 */
    $param_lot_addr = $lot_addr ? ['LOT_ADDR', $lot_addr] : null;
    $param_road_addr = $road_addr ? ['ROAD_ADDR', $road_addr] : null;
    $param_detail_addr = $addr_detail ? ['DETAIL_ADDR', $addr_detail] : null;

    /* 쇼핑몰별 주소정보 설정 */
    if ($country == 'KR') {
        // 한국몰 주소정보 설정
        $param_lot_addr = $lot_addr ? ['LOT_ADDR', $lot_addr] : null;
        $param_road_addr = $road_addr ? ['ROAD_ADDR', $road_addr] : null;
        $param_detail_addr = $addr_detail ? ['DETAIL_ADDR', $addr_detail] : null;
    } else if ($country == 'EN' || $country == 'CN') {
        // 영문/중문 몰 주소정보 설정
        $country_name = $db->get('COUNTRY_INFO', 'COUNTRY_CODE = ?', [$country_code])[0]['COUNTRY_NAME'];
        $province_name = $province_idx ? $db->get('PROVINCE_INFO', 'IDX = ?', [$province_idx])[0]['PROVINCE_NAME'] : '';

        $param_lot_addr = ['LOT_ADDR', $country_name . " " . $province_name . " " . $city];
        $param_road_addr = ['ROAD_ADDR', $country_name . " " . $province_name . " " . $city];
        $param_detail_addr = $address ? ['DETAIL_ADDR', $address] : null;
    }

    $param_zipcode = $zipcode ? ['ZIPCODE', $zipcode] : null;
    $param_tel_mobile = $tel_mobile ? ['TEL_MOBILE', $tel_mobile] : null;
    $param_member_birth = ($birth_year && $birth_month && $birth_day) ? ['MEMBER_BIRTH', "$birth_year-$birth_month-$birth_day"] : null;
    $param_gender = $gender ? ['MEMBER_GENDER', $gender] : null;
    $param_country = ['COUNTRY_CODE', isset($country_code)?$country_code:null];
    
    $param_terms_flg = isset($accept_terms_flg) ? ['ACCEPT_TERMS_FLG', 1] : ['ACCEPT_TERMS_FLG', 0];
    
    $param_sms_flg = isset($receive_sms_flg) ? ['RECEIVE_SMS_FLG', 1] : ['RECEIVE_SMS_FLG', 0];
    $param_sms_date = isset($receive_sms_flg) ? ['RECEIVE_SMS_DATE', date('Y-m-d H:i:s')] : null;
    
    $param_email_flg = isset($receive_email_flg) ? ['RECEIVE_EMAIL_FLG', 1] : ['RECEIVE_EMAIL_FLG', 0];
    $param_email_date = isset($receive_email_flg) ? ['RECEIVE_EMAIL_DATE', date('Y-m-d H:i:s')] : null;

    $db->begin_transaction();

    try {
        /* 2. 회원 정보 등록처리 */
        $db->insert(
            "MEMBER_$country",
            array_merge(
                [
                    'COUNTRY'          => $country,
                    'MEMBER_STATUS'    => 'NML',
                    'JOIN_DATE'        => NOW()
                ],
                $param_member_id ? [$param_member_id[0] => $param_member_id[1]] : [],
                $param_member_pw ? [$param_member_pw[0] => $param_member_pw[1]] : [],
                $param_member_name ? [$param_member_name[0] => $param_member_name[1]] : [],
                $param_lot_addr ? [$param_lot_addr[0] => $param_lot_addr[1]] : [],
                $param_road_addr ? [$param_road_addr[0] => $param_road_addr[1]] : [],
                $param_detail_addr ? [$param_detail_addr[0] => $param_detail_addr[1]] : [],
                $param_zipcode ? [$param_zipcode[0] => $param_zipcode[1]] : [],
                $param_country ? [$param_country[0] => $param_country[1]] : [],
                $param_tel_mobile ? [$param_tel_mobile[0] => $param_tel_mobile[1]] : [],
                $param_gender ? [$param_gender[0] => $param_gender[1]] : [],
                $param_member_birth ? [$param_member_birth[0] => $param_member_birth[1]] : [],
                $param_terms_flg ? [$param_terms_flg[0] => $param_terms_flg[1]] : [],
                $param_sms_flg ? [$param_sms_flg[0] => $param_sms_flg[1]] : [],
                $param_sms_date ? [$param_sms_date[0] => $param_sms_date[1]] : [],
                $param_email_flg ? [$param_email_flg[0] => $param_email_flg[1]] : [],
                $param_email_date ? [$param_email_date[0] => $param_email_date[1]] : []
            )
        );

        /* 신규 회원 IDX */
        $member_idx = $db->last_id();
        if (!empty($member_idx)) {
            /* 3. 회원 기본 배송지 */
            
            /* 3-1. 회원 기본 배송지 이름 설정 */
            $default_name = '';
            switch ($country) {
                case 'KR':
                    $default_name = '기본 배송지';
                    break;
                case 'EN':
                    $default_name = 'Default';
                    break;
                case 'CN':
                    $default_name = '基本配送地';
                    break;
            }
            
            /* 3-2. 회원 배송지 정보 등록 PARAM 설정 */
            $param_order_to = $country == "KR" ?
                [
                    'country'       => $country,
                    'member_idx'    => $member_idx,
                    'default_name'  => $default_name,
                    'member_name'   => $member_name,
                    'tel_mobile'    => $tel_mobile,
                    'zipcode'       => $zipcode,
                    'lot_addr'      => $lot_addr,
                    'road_addr'     => $road_addr,
                    'addr_detail'   => $addr_detail
                ] :
                [
                    'country'       => $country,
                    'member_idx'    => $member_idx,
                    'default_name'  => $default_name,
                    'member_name'   => $member_name,
                    'tel_mobile'    => $tel_mobile,
                    'zipcode'       => $zipcode,
                    'country_code'  => $country_code,
                    'province_idx'  => $province_idx,
                    'city'          => $city,
                    'address'       => $address
                ];

            /* 3-3. 회원 기본 배송지 등록 */
            addDefaultOrderTo($db, $country, $param_order_to);
            
            /* 4. 가입 적립금 등록 */
            addMileageInfo($db, $country, $member_idx, $member_id);
        }
        
        $db->commit();
        
        /* ========== NAVER CLOUD PLATFORM::신규가입 메일 발송 ========== */
        /* PARAM::MAIL */
        $param_mail = [
            'country'       => $country,
            'mail_type'     => "M",
            'mail_code'     => "MAIL_CODE_0001",
            'param_member'  => $member_idx,
            'param_admin'   => null
        ];
        
        /* PARAM::MAIL DATA */
        $param_data = [
            'member_name'   => $member_name,
            'member_id'     => $member_id
        ];
        
        /* 신규가입 메일 발송 */
        // callSEND_mail($db, $param_mail, $param_data);
        
        /* ========== NAVER CLOUD PLATFORM::신규가입 알림톡 발송 ========== */
        if ($country == "KR") {
            /* PARAM::KAKAO */
            $param_kakao = [
                'kakao_code'    => "KAKAO_CODE_0011",
                'member_idx'    => $member_idx
            ];
            
            /* PARAM::KAKAO DATA */
            $param_data = [
                'data_type'     => "MEMBER",
                'member_idx'    => $member_idx
            ];
            
            /* PARAM::DATA */
            $data_kakao = getDATA_kakao($db, $param_data);
            
            /* 신규가입 알림톡 발송 */
            // callSEND_kakao($db, $param_kakao, $data_kakao);
        }
    } catch (mysqli_sql_exception $e) {
        $db->rollback();
        
        print_r($e);
        
        $json_result['code'] = 401;
        $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0101', []);
        
        echo json_encode($json_result);
        exit;
    }
} else {
    $json_result['code'] = 303;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0059', []);
    
    echo json_encode($json_result);
    exit;
}
/* 1. 동일 ID 중복체크 */
function checkDuplicate($db, $member_id) {
    $cnt_member = $db->count("MEMBER_KR", "MEMBER_ID = ?", [$member_id]);
    $cnt_member += $db->count("MEMBER_EN", "MEMBER_ID = ?", [$member_id]);
    $cnt_member += $db->count("MEMBER_CN", "MEMBER_ID = ?", [$member_id]);
    
    return $cnt_member == 0;
}

/* 3-3. 회원 기본 배송지 등록 */
function addDefaultOrderTo($db, $country, $param) {
    if ($country == "KR") {
        /* 국내몰 배송지 등록 */
        $db->insert(
            "ORDER_TO",
            [
                'COUNTRY'        => $param['country'],
                'MEMBER_IDX'     => $param['member_idx'],
                'TO_PLACE'       => $param['default_name'],
                'TO_NAME'        => $param['member_name'],
                'TO_MOBILE'      => $param['tel_mobile'],
                'TO_ZIPCODE'     => $param['zipcode'],
                'TO_LOT_ADDR'    => $param['lot_addr'],
                'TO_ROAD_ADDR'   => $param['road_addr'],
                'TO_DETAIL_ADDR' => $param['addr_detail'],
                'DEFAULT_FLG'    => 1
            ]
        );
    } else {
        /* 영문몰,중문몰 배송지 등록 */
        $db->insert(
            "ORDER_TO",
            [
                'COUNTRY'          => $param['country'],
                'MEMBER_IDX'       => $param['member_idx'],
                'TO_PLACE'         => $param['default_name'],
                'TO_NAME'          => $param['member_name'],
                'TO_MOBILE'        => $param['tel_mobile'],
                'TO_ZIPCODE'       => $param['zipcode'],
                'TO_COUNTRY_CODE'  => $param['country_code'],
                'TO_PROVINCE_IDX'  => $param['province_idx'],
                'TO_CITY'          => $param['city'],
                'TO_ADDRESS'       => $param['address'],
                'DEFAULT_FLG'      => 1
            ]
        );
    }
}

/* 4. 가입 적립금 등록 */
function addMileageInfo($db, $country, $member_idx, $member_id) {
    $mileage = $country == "KR" ? "5000" : "5";
    
    $db->insert(
        "MILEAGE_INFO",
        [
            'COUNTRY'             => $country,
            'MEMBER_IDX'          => $member_idx,
            'ID'                  => $member_id,
            'MILEAGE_CODE'        => 'NEW',
            'MILEAGE_UNUSABLE'    => 0,
            'MILEAGE_USABLE_INC'  => $mileage,
            'MILEAGE_USABLE_DEC'  => 0,
            'MILEAGE_BALANCE'     => $mileage,
            'CREATER'             => 'system',
            'UPDATER'             => 'system',
        ]
    );
}
?>
