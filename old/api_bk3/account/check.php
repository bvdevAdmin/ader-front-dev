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

include_once("/var/www/www/api/common/mail.php");
include_once("/var/www/www/api/common.php");
include_once("/var/www/www/api/send/send_mail.php");

$country = null;
if (isset($_POST['country'])) {
	$country = $_POST['country'];
} else{
	$result = false;
	$code = 300;
	$msg = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0072', array());
}

if ($country != null && $member_id != null) {
	/* 회원 정보 존재 여부 체크 */
    $cnt_member = $db->count("MEMBER_".$country,"MEMBER_ID = '".$member_id."'");
    if ($cnt_member > 0) {
		/* 임시 비밀번호 설정 대상 회원정보 조회 */
	    $select_member_sql = "
	        SELECT
	            MB.IDX				AS MEMBER_IDX,
	            MB.MEMBER_NAME		AS MEMBER_NAME
	        FROM 
	            MEMBER_".$country." MB
	        WHERE
	            MB.MEMBER_ID = ?
	    ";
	    
	    $db->query($select_member_sql,array($member_id));
	    
	    foreach ($db->fetch() as $data) {
	        $member_idx = $data['MEMBER_IDX'];
	        $member_name = $data['MEMBER_NAME'];
			
			$tmp_pw = makeTmpPw();
			
			$db->update(
				"MEMBER_".$country,
				array(
					'MEMBER_PW'		=>$tmp_pw
				),
				'IDX = '.$member_idx
			);
			
			/* 메일 데이터 PARAM 설정처리 */
			$param_mail_data = array(
				'member_name'	=>$member_name,
				'member_id'		=>$member_id,
				'tmp_pw'		=>$tmp_pw
			);
			
			/* 자동메일 발송설정 체크처리 */
			$mail_setting = checkMailSetting($db,$country,"MAIL_CODE_0007");
			if (isset($mail_setting['template_member']) && $mail_setting['template_member'] != null) {
				/* 메일 발송 */
				sendMail($db,$country,"M",$member_idx,null,$mail_setting['template_member'],$param_mail_data);
			}
			
			if (isset($mail_setting['template_admin']) && $mail_setting['template_admin'] != null) {
				/* 메일 발송 */
				sendMail($db,$country,"A","/member/info",null,$mail_setting['template_member'],$param_mail_data);
			}
	    }
    } else {
        $result = false;
        $code = 300;
        $msg = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0080', array());
    }
} else {
	$json_reuls['result'] = false;
    $json_result['code'] = 401;
    $json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_WRN_0003', array());
	
	echo json_encode($json_result);
	exit;
}

function makeTmpPw(){
    return mt_rand(1000000,9999999);
}
?>
