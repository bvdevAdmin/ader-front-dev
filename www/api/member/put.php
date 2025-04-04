<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 변경 / 인증
 | -------
 |
 | 최초 작성	: 이은형
 | 최초 작성일	: 2023.01.16
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

include_once $_CONFIG['PATH']['API'].'_legacy/common.php';

if (!isset($_SESSION['MEMBER_IDX']) && isset($action_type)) {
	/* 1. 미로그인 상태 회원정보 수정 처리 */

	if ($action_type == "CHECK") {
		/* 1-1. ID/PW 찾기 - 회원 계정정보 체크 처리 */
		if ($member_id != null && $tel_mobile != null) {
			$cnt_member = $db->count("MEMBER","MEMBER_STATUS = 'NML' AND MEMBER_ID = ? AND TEL_MOBILE = ?",array($member_id,$tel_mobile));
			if ($cnt_member > 0) {
				$member = $db->get("MEMBER","MEMBER_STATUS = 'NML' AND MEMBER_ID = ? AND TEL_MOBILE = ?",array($member_id,$tel_mobile));
				if (sizeof($member) > 0) {
					$member_id = $member[0]['MEMBER_ID'];
	
					$json_result['data'] = $member_id;
				}
			} else {
				$json_result['code'] = 300;
				$json_result['msg'] = "회원 정보가 존재하지 않습니다.";
			}
		} else {
			$json_result['code'] = 300;
			$json_result['msg'] = "회원 정보가 존재하지 않습니다.";
		}
	} else if ($action_type == "CHANGE") {
		/* 1-2. ID/PW 찾기 - 회원 비밀번호 변경 처리 */
		if ($member_id != null && $member_pw != null) {
			$db->update(
				"MEMBER",
				array(
					'MEMBER_PW'		=>md5($member_pw),
					'PW_DATE'		=>NOW()
				),
				"MEMBER_ID = ?",
				array($member_id)
			);

			addMember_log($db,null,$member_id);
		} else {
			$json_result['code'] = 300;
			$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_WRN_0067',array());
	
			echo json_encode($json_result);
			exit;
		}
	}
} else if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	/* 2. 로그인 상태 회원정보 수정 처리 */

	if ($action_type != null) {
		switch ($action_type) {
			case "INFO" :
				$db->update(
					"MEMBER",
					array(
						'MEMBER_NAME'		=>$member_name,
						'MEMBER_BIRTH'		=>$member_birth,
						'TEL_MOBILE'		=>$tel_mobile,
						'AUTH_FLG'			=>1
					),
					"IDX = ?",
					array($_SESSION['MEMBER_IDX'])
				);

				addMember_log($db,$_SESSION['MEMBER_IDX'],null);
				
				$json_result['code'] = 200;
				$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_INF_0016',array());
				
				echo json_encode($json_result);
				exit;

				break;
			
			case "PASSWORD" :
				if ($member_pw != null) {
					$cnt_pw = $db->count("MEMBER","IDX = ? AND MEMBER_PW = ?",array($_SESSION['MEMBER_IDX'],md5($member_pw)));
					if ($cnt_pw == 0) {
						$db->update(
							"MEMBER",
							array(
								'MEMBER_PW'		=>md5($member_pw),
								'PW_DATE'		=>NOW()
							),
							"IDX = ?",
							array($_SESSION['MEMBER_IDX'])
						);

						addMember_log($db,$_SESSION['MEMBER_IDX'],null);
					} else {
						$json_result['code'] = 300;
						$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_F_WRN_0067',array());
						
						echo json_encode($json_result);
						exit;
					}
				}
				
				break;
			
			case "MARKETING" :
				$tmp_flg_M = 0;
				if (isset($email) && $email == "y") {
					$tmp_flg_M = 1;
				}
				
				$tmp_flg_S = 0;
				if (isset($sms) && $sms == "y") {
					$tmp_flg_S = 1;
				}	
				
				$tmp_flg_T = 0;
				if (isset($tel) && $tel == "y") {
					$tmp_flg_T = 1;
				}	
				
				$db->update(
					"MEMBER",
					array(
						'RECEIVE_EMAIL_FLG'		=>$tmp_flg_M,
						'RECEIVE_EMAIL_DATE'	=>NOW(),
						
						'RECEIVE_SMS_FLG'		=>$tmp_flg_S,
						'RECEIVE_SMS_DATE'		=>NOW(),
						
						'RECEIVE_TEL_FLG'		=>$tmp_flg_T,
						'RECEIVE_TEL_DATE'		=>NOW()
					),
					"IDX = ?",
					array($_SESSION['MEMBER_IDX'])
				);

				addMember_log($db,$_SESSION['MEMBER_IDX'],null);
				
				break;
		}
	}
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
}

?>