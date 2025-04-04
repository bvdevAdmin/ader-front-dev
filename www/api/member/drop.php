<?php
/*
 +=============================================================================
 | 
 | 마이페이지 회원정보 - 회원 탈퇴
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

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
	$db->update(
		"MEMBER",
		array(
			'MEMBER_STATUS'	=>'DRP',
			'DROP_TYPE'		=>'NDP',
			'DROP_DATE'		=>NOW()
		),
		"IDX = ?",
		array($member_idx)
	);

	$select_order_cancel_sql = "
		SELECT
			MB.MEMBER_ID			AS MEMBER_ID,
			MB.MEMBER_NAME			AS MEMBER_NAME,
			MB.TEL_MOBILE			AS TEL_MOBILE,
			DATE_FORMAT(
				MB.DROP_DATE,
				'%Y.%m.%d %H%:i'
			)						AS DROP_DATE
		FROM
			MEMBER MB
		WHERE
			MB.IDX = ?
	";

	$db->query($select_member_sql,array($member_idx));

	foreach($db->fetch() as $data) {
		/* 알림톡 발송설정 체크처리 */
		if ($data['COUNTRY'] == "KR") {
			$kakao_setting = checkKAKAO_setting($db,"KAKAO_CODE_0013");
			if ($kakao_setting['kakao_flg'] == true && $kakao_setting['template_id'] != null) {
				
				/* KAKAO::PARAM */
				$param_kakao = array(
					'user_email'		=>$data['MEMBER_ID'],
					'user_name'			=>$data['MEMBER_NAME'],
					'tel_mobile'		=>$data['TEL_MOBILE'],
					'template_id'		=>$kakao_setting['template_id']
				);
				
				/* KAKAO::DATA */
				/*
				$kakao_data = array(
					'member_id'			=>$data['MEMBER_ID'],
					'member_name'		=>$data['MEMBER_NAME'],
					
					'drop_date'			=>$data['DROP_DATE']
				);
				*/
				
				/* (공통) NCP - 메일 발송 */
				callSEND_kakao($db,$param_kakao,array());
			}
		}

		/* 자동메일 발송설정 체크처리 */
		$mail_setting = checkMAIL_setting($db,$data['COUNTRY'],"MAIL_CODE_0002");
		if ($mail_setting['mail_flg'] == true && ($mail_setting['template_id'] != null && $mail_setting['template_id'] != "00000")) {
			/* MAIL::PARAM */
			$param_mail = array(
				'user_email'		=>$data['MEMBER_ID'],
				'user_name'			=>$data['MEMBER_NAME'],
				'tel_mobile'		=>$data['TEL_MOBILE'],
				'template_id'		=>$mail_setting['template_id']
			);
			
			/* MAIL::DATA */
			/*
			$mail_data = array(
				'member_id'			=>$data['MEMBER_ID'],
				'member_name'		=>$data['MEMBER_NAME'],
				
				'drop_date'			=>$data['DROP_DATE']
			);
			*/
			
			/* (공통) NCP - 메일 발송 */
			callSEND_mail($db,$param_mail,array());
		}
	}
	
	$db_result = $db->affectedRows();
	if ($db_result > 0) {
		$json_result['code'] = 200;
		$json_result['msg'] = getMsgToMsgCode($db,$country,'MSG_B_INF_0001',array());
		
		session_destroy();
		
		echo json_encode($json_result);
		exit;
	} else {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db,$country,'MSG_B_ERR_0103',array());
		
		echo json_encode($json_result);
		exit;
	}
} else {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db,$country,'MSG_B_ERR_0018',array());
	
	echo json_encode($json_result);
	exit;
}
