<?php
/*
 +=============================================================================
 | 
 | 영문몰 계정찾기 인증메일 발송
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2025.01.02
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

if ($_SERVER['HTTP_COUNTRY'] == "EN" && $action_type != null) {
	switch ($action_type) {
		case "FIND" :
			if ($tel_mobile != null) {
				$cnt_member = $db->count("MEMBER","COUNTRY = 'EN' AND MEMBER_STATUS = 'NML' AND TEL_MOBILE = ?",array($tel_mobile));
				if ($cnt_member > 0) {
					$member_id = $db->get("MEMBER","COUNTRY = 'EN' AND MEMBER_STATUS = 'NML' AND TEL_MOBILE = ?",array($tel_mobile))[0]['MEMBER_ID'];

					/* 자동메일 발송설정 체크처리 */
					$mail_setting = checkMAIL_setting($db,$country,"MAIL_CODE_0015");
					if ($mail_setting['mail_flg'] == true && ($mail_setting['template_id'] != null && $mail_setting['template_id'] != "00000")) {
						/* PARAM::MAIL */
						$param_mail = array(
							'user_email'		=>$member_id,
							'template_id'		=>$mail_setting['template_id']
						);
						
						/* PARAM::DATA */
						/*
						$mail_data = array(
							'member_id'			=>$member_id
						);
						*/
						
						/* (공통) NCP - 메일 발송 */
						callSEND_mail($db,$param_mail,array());
					}
				} else {
					$json_result['code'] = 300;
					$json_result['msg'] = "Member information does not exist.";

					echo json_encode($json_result);
					exit;
				}
			} else {
				$json_result['code'] = 300;
				$json_result['msg'] = "An error has occured while find member information.";

				echo json_encode($json_result);
				exit;
			}
			
			break;
	
		case "INIT" :
			if ($member_id != null) {
				$cnt_member = $db->count("MEMBER","COUNTRY = 'EN' AND MEMBER_ID = ?",array($member_id));
				if ($cnt_member > 0) {
					$character	= "ABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
					$len		= strlen($character);
					
					$verify_code = "";

					for ($i=0; $i<6;  $i++) {
						$verify_code .= $character[rand(0,$len - 1)];
					}

					/* 자동메일 발송설정 체크처리 */
					$mail_setting = checkMAIL_setting($db,$country,"MAIL_CODE_0015");
					if ($mail_setting['mail_flg'] == true && ($mail_setting['template_id'] != null && $mail_setting['template_id'] != "00000")) {
						/* PARAM::MAIL */
						$param_mail = array(
							'user_email'		=>$member_id,
							'template_id'		=>$mail_setting['template_id']
						);
						
						/* PARAM::DATA */
						/*
						$mail_data = array(
							'member_id'			=>$member_id,
							'verify_code'		=>$verify_code
						);
						*/
						
						/* (공통) NCP - 메일 발송 */
						callSEND_mail($db,$param_mail,array());
					}
				} else {
					$json_result['code'] = 300;
					$json_result['msg'] = "Member information does not exist.";

					echo json_encode($json_result);
					exit;
				}
			}
			
			break;
	}
}

?>