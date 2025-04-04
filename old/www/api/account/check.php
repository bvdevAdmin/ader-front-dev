<?php
/*
 +=============================================================================
 | 
 | 임시 비밀번호 설정
 | -------
 |
 | 최초 작성    : 박성혁
 | 최초 작성일   : 2022.11.30
 | 최종 수정일   : 
 | 버전       : 1.0
 | 설명       : 
 |            
 | 
 +=============================================================================
*/

include_once(dir_f_api."/common.php");
include_once(dir_f_api."/send/send-mail.php");
include_once(dir_f_api."/send/send-kakao.php");

if (!isset($country) || !isset($member_id)) {
    $json_result['result'] = false;
    $json_result['code'] = 401;
    $json_result['msg'] = getMsgToMsgCode($db, null, 'MSG_B_WRN_0003', []);
    echo json_encode($json_result);
    exit;
}

/* 회원 정보 존재 여부 체크 */
$cnt_member = $db->count("MEMBER_".$country, "MEMBER_ID = ?", [$member_id]);

if ($cnt_member > 0) {
    /* 임시 비밀번호 설정 대상 회원정보 조회 */
    $select_member_sql = "
        SELECT
            MB.IDX            AS MEMBER_IDX,
            MB.MEMBER_NAME    AS MEMBER_NAME
        FROM 
            MEMBER_{$country} MB
        WHERE
            MB.MEMBER_ID = ?
    ";
    
    $db->query($select_member_sql, [$member_id]);
    
    foreach ($db->fetch() as $data) {
        $member_idx = $data['MEMBER_IDX'];
        $member_name = $data['MEMBER_NAME'];
        $tmp_pw = makeTmpPw();
        
        $db->update(
            "MEMBER_{$country}",
            ['MEMBER_PW' => md5($tmp_pw)],
            'IDX = ?',
            [$member_idx]
        );
        
        /* ========== NAVER CLOUD PLATFORM::신규가입 알림톡 발송 ========== */
        if ($country === "KR") {
            /* PARAM::KAKAO */
            $param_kakao = [
                'kakao_code' => "KAKAO_CODE_0012",
                'member_idx' => $member_idx
            ];
            
            /* PARAM::KAKAO DATA */
            $param_data = [
                'data_type'  => "MEMBER",
                'member_idx' => $member_idx,
                'tmp_pw'     => $tmp_pw
            ];
            
            /* PARAM::DATA */
            $data_kakao = getDATA_kakao($db, $param_data);
            
            /* 신규가입 알림톡 발송 */
            // callSEND_kakao($db, $param_kakao, $data_kakao);
            
            /* ========== NAVER CLOUD PLATFORM::임시 비밀번호 발급 메일 발송 ========== */
            /* PARAM::MAIL */
            $param_mail = [
                'country'      => $country,
                'mail_type'    => "M",
                'mail_code'    => "MAIL_CODE_0003",
                'param_member' => $member_idx,
                'param_admin'  => null
            ];
            
            /* PARAM::MAIL DATA */
            $param_data = [
                'member_name' => $member_name,
                'member_id'   => $member_id,
                'tmp_pw'      => $tmp_pw
            ];
            
            /* 임시 비밀번호 발급 메일 발송 */
            // callSEND_mail($db, $param_mail, $param_data);
        } else {
            /* ========== NAVER CLOUD PLATFORM::임시 비밀번호 발급 메일 발송 ========== */
            /* PARAM::MAIL */
            $param_mail = [
                'country'      => $country,
                'mail_type'    => "M",
                'mail_code'    => "MAIL_CODE_0003",
                'param_member' => $member_idx,
                'param_admin'  => null
            ];
            
            /* PARAM::MAIL DATA */
            $param_data = [
                'member_name' => $member_name,
                'member_id'   => $member_id,
                'tmp_pw'      => $tmp_pw
            ];
            
            /* 임시 비밀번호 발급 메일 발송 */
            // callSEND_mail($db, $param_mail, $param_data);
        }
    }
} else {
    $json_result['result'] = false;
    $json_result['code'] = 300;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0080', []);
    echo json_encode($json_result);
    exit;
}

function makeTmpPw(){
    return mt_rand(1000000,9999999);
}
?>
