<?php
function get_category($category_no) {
	global $db,$_TABLE;

	$data = $db->get($_TABLE['CAT'],'IDX=?',array($category_no));
	if($data) {
		if(intval($data[0]['FATHER_NO']) > 0) $result = get_category($data[0]['FATHER_NO']);
		$result[] = $data[0]['TITLE'];
	}
	return $result;
}


function member_session_regist($data) {
	if(is_array($data)) {
		$_SESSION[SESSION['HEAD'].'NO'] = $data['IDX'];
		$_SESSION[SESSION['HEAD'].'ID'] = $data['ID'];
		$_SESSION[SESSION['HEAD'].'NAME'] = $data['NAME'];
		$_SESSION[SESSION['HEAD'].'LEVEL'] = $data['LEVEL'];
	}
}

function get_user_google($_code) {
	$ch = curl_init();		
	curl_setopt($ch, CURLOPT_URL, 'https://www.googleapis.com/oauth2/v4/token');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
			'client_id'=>SNS['GOOGLE']['CLIENT_ID'],
			'redirect_uri'=>SNS['GOOGLE']['REDIRECT_URI'],
			'client_secret'=>SNS['GOOGLE']['SECRET_PW'],
			'code'=>$_code,
			'grant_type'=>'authorization_code'
		)));
	$token_data = json_decode(curl_exec($ch), true);
	$json_result['code'] = curl_getinfo($ch,CURLINFO_HTTP_CODE);
	if($json_result['code'] == 200) {
		$json_result['response'] =  json_decode(curl('https://www.googleapis.com/oauth2/v2/userinfo?fields=name,email,gender,id,picture,verified_email',
		array('Authorization: Bearer '.$token_data['access_token']),array()),true);
	}
	else {
		$json_result['response'] =  $token_data;
	}

	$json_result['response']['social'] = 'google';
	return $json_result;
}

function get_user_kakao($_code) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://kauth.kakao.com/oauth/token');
	curl_setopt($ch, CURLOPT_POST, false);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array(
		'client_id'=>SNS['KAKAO']['CLIENT_ID'],
		'redirect_uri'=>SNS['KAKAO']['LOGIN']['REDIRECT_URL'],
		'code'=>$_code,
		'grant_type'=>'authorization_code'
	)));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$token_data = json_decode(curl_exec($ch),true);
	$json_result['code'] = curl_getinfo($ch,CURLINFO_HTTP_CODE);
	if($json_result['code'] == 200) {
		$data = json_decode(curl('https://kapi.kakao.com/v2/user/me',array('Authorization: Bearer '.$token_data['access_token']), array()),true);

		$json_result['response'] = array(
			'id'=>$data['id'],
			'name'=>$data['properties']['nickname'],
			'email'=>$data['kakao_account']['email'],
			'picture'=>$data['properties']['profile_image'],
			'social'=>'kakao'
		);
	}
	else {
		$json_result['response'] =  $token_data;
	}

	return $json_result;
}

function get_user_naver($access_code,$state) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, 'https://nid.naver.com/oauth2.0/token?'.http_build_query(array(
		'client_id'=>SNS['NAVER']['CLIENT_ID'],
		'client_secret'=>SNS['NAVER']['SECRET_KEY'],
		'redirect_uri'=>SNS['NAVER']['LOGIN']['REDIRECT_URL'],
		'grant_type'=>'authorization_code',
		'state'=>$state,
		'code'=>$access_code
	)));
	curl_setopt($ch, CURLOPT_POST, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$token_data = json_decode(curl_exec($ch),true);
	$json_result['code'] = curl_getinfo($ch,CURLINFO_HTTP_CODE);
	if($json_result['code'] == 200) {
		curl_setopt($ch, CURLOPT_URL, 'https://openapi.naver.com/v1/nid/me');
		curl_setopt($ch, CURLOPT_POST, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token_data['access_token']));
		$data = json_decode(curl_exec($ch),true);
		curl_close($ch);
		$json_result['response'] = array(
			'id'=>$data['response']['id'],
			'name'=>$data['response']['name'],
			'email'=>$data['response']['email'],
			'picture'=>$data['response']['profile_image'],
			'mobile'=>str_replace('-','',$data['response']['mobile']),
			'social'=>'naver'
		);
	}
	else {
		$json_result['response'] =  $token_data;
	}

	return $json_result;
}

function join_user_social($data) {
	global $_TABLE;
	$db = new db();

	$where = 'EMAIL=?';
	$where_values = array($data['email']);
	if($db->count($_TABLE['MEMBER'],$where,$where_values) > 0) {
		$member_data = $db->get($_TABLE['MEMBER'],$where,$where_values)[0];

		$member_data = array_merge($member_data,array(
			'LOGIN_DATE' => date('Y-m-d H:i:s',time() + (60*60*9)),
			'LOGIN_CNT' => intval($member_data['LOGIN_CNT']) + 1,
			'IP' => $_SERVER['REMOTE_ADDR'],
			'SESSION_ID' => session_id()
		));
		$db->update($_TABLE['MEMBER'],$member_data,'ID=?',array($member_data['ID']));
		$member_data['SNS'] = $data['social'];

		// SNS 정보에 기록
		$db->insert($_TABLE['SNS'],array(
				'MEMBER_NO'=>$member_data['IDX'],
				'SNS'=>$data['social'],
				'ID'=>$member_data['ID'],
				'DATA'=>json_encode($data),
				'LOGIN_DATE'=>date('Y-m-d H:i:s'),
				'IP'=>$_SERVER['REMOTE_ADDR']
			),
			'MEMBER_NO=?',
			array($member_data['IDX'])
		);
		member_session_regist($member_data);

		return true;
	}
	else return false;
}

function point($type,$tel,$pay_no,$point = 0,$remark = '') {
	global $_TABLE,$client_no,$client_data,$store_no,$store_data;

	$tel = trim(str_replace('-','',$tel));
	$pay_no = intval($pay_no);
	$point = intval($point);
	$member_data = db_get($_TABLE['MEMBER'],'ID="'.$tel.'"');

	if(is_array($pay_no)) {
		$pay_data = $pay_no;
		$pay_no = $pay_data['IDX'];
	}
	else {
		$pay_no = intval($pay_no);
		if($pay_no > 0) {
			$pay_data = db_get($_TABLE['PAY'],'IDX='.$pay_no);
		}
	}

	switch($type) {
		case '적립':
			break;

		case '환불':
			$point = db_get($_TABLE['POINT'],'MEMBER_NO='.$member_data['IDX'].' AND PAY_NO='.$pay_no,'SUM(MILEAGE)');
		case '사용':
			$point *= -1;
			break;
		
	}

	$where = 'MEMBER_NO="'.$member_data['IDX'].'" AND STATUS = "Y"';
	if($client_data['IS_SHARE'] == 'Y' && is_numeric($client_no)) { // 지점간 티켓 공유를 하는 지점인지 확인
		$where .= ' 
		AND 
			STORE_NO IN (
				SELECT IDX 
					FROM '.$_TABLE['STORE'].' 
				WHERE 
					CATEGORY="'.$store_data['CATEGORY'].'" 
					AND CLIENT_NO='.$client_no.' 
					AND STATUS="Y"
			)
		';
	}
	elseif(is_numeric($store_no)) {
		$where .= ' AND STORE_NO = '.$store_no.' ';
	}

	if(db_count($_TABLE['POINT_MEMBER'],$where) > 0) {
		$result = db_update($_TABLE['POINT_MEMBER'],'MILEAGE=MILEAGE+'.$point,$where);
		$result = db_insert(
			$_TABLE['POINT'],
			'
				MEMBER_NO,STORE_NO,PAY_NO,
				ADMIN_ID,MILEAGE,IP,REMARK,STATUS
			',
			'
				'.$member_data['IDX'].','.$store_no.','.$pay_no.',
				"'.$_SESSION[SS_HEAD.'ID'].'",'.$point.',"'.$ip.'","'.$remark.'","'.$type.'"
			'
		);
	}
}

function now() {
	return date('Y-m-d H:i:s');
}

function minutetostr($minute,$to_day = false) {
	$minute = intval($minute);
	$result = null;

	if($to_day && $minute/1440 > 1) {
		$result[] = intval($minute/1440).'일';
		if(intval($minute/60) > 0 && intval($minute/60) < 24) $result[] = intval($minute/60).'시간';
	}
	else if(intval($minute/60) > 0) $result[] = intval($minute/60).'시간';
	if($minute%60 > 0) $result[] = intval($minute%60).'분';

	if(is_array($result)) $result = implode(' ',$result);
	return $result;
}

function send_biztalk($tel_number = null,$arg = []) {
	global $client_no,$store_no,$pos_no,$store_data,$_TABLE,$db;

	/** 01. 변수 정리 **/
	$pos_no = intval($pos_no);
	$sms = false;
	if(!is_numeric($pos_no)) $pos_no = 0;
	if(!is_array($tel_number)) $tel_number = array($tel_number);
	if(array_key_exists('sms',$arg) === TRUE) $sms = true; // SMS 여부 확인
	if(array_key_exists('reserved_time',$arg) === FALSE) $arg['reserved_time'] = '00000000000000';
	if(array_key_exists('keyword',$arg) === FALSE || !is_array($arg['keyword'])) $arg['keyword'] = array();
	if(!array_key_exists('title',$arg) && !$sms) $arg['title'] = $arg['mcode'].' '.$arg['scode'];
	for($i=0;$i<sizeof($tel_number);$i++) {
		$tel_number[$i] = str_replace(array('-',' ',chr(10),chr(13)),'',$tel_number[$i]);
	}
	$arg['keyword'] = array_merge($arg['keyword'],array(
		'년-월-일'=>date('Y-m-d'),
		'지점이름'=>$store_data['TITLE'],
		'결제한번호'=>$tel_number[0],
		'점주번호'=>tel_format($store_data['HOST_TEL'])
	));
	$_biztalk_type = array(
		'웹링크'=>'WL',
		'앱링크'=>'AL',
		'배송조회'=>'DS',
		'봇키워드'=>'BK',
		'메시지전달'=>'MD'
	);

	/** 02. 템플릿 불러옮 **/
	if(!$sms) {
		/*
		$where = '
			(
				(CLIENT_NO = 0 AND STORE_NO = 0)
				OR
				(CLIENT_NO = '.$client_no.' AND STORE_NO = 0)
				OR
				STORE_NO = '.$store_no.'
			)
			AND STATUS = "Y" 
			AND LCODE = "'.$store_data['CATEGORY'].'"
		';
		if($arg['mcode'] != '' && $arg['scode'] != '') {
			$where .= ' AND MCODE = "'.$arg['mcode'].'" AND SCODE = "'.$arg['scode'].'" ';
		}
		else {
			$where .= ' AND TEMPLETE = "'.$arg['title'].'" ';
		}
		$where .= ' ORDER BY STORE_NO DESC,CLIENT_NO DESC LIMIT 1';
		*/
		$biztalk = $db->get($_TABLE['BIZTALK_DEF'],'TEMPLETE=?',array($arg['title']))[0];
		if($biztalk == null || $store_data['KAKAO_APIKEY'] == '') {
			return false;
		}
		if($biztalk['CONTENTS'] != '' && array_key_exists('message',$arg) === FALSE) $arg['message'] = $biztalk['CONTENTS'];
		foreach($arg['keyword'] as $key => $val) {
			$arg['message'] = str_replace('#{'.$key.'}',$val,$arg['message']);
		}

		/** 03. 발송 환경설정 **/
		$where = 'STORE_NO=? AND BIZTALK_NO=?';
		$where_value = array($store_no,$biztalk['IDX']);
		if($db->count($_TABLE['BIZTALK'],$where,$where_value) == 0) { 
			$db->insert(
				$_TABLE['BIZTALK'],
				array(
					'STORE_NO'=>$store_no,
					'BIZTALK_NO'=>$biztalk['IDX'],
					'STATUS'=>'Y'
				)
			);
		}
		if($db->get($_TABLE['BIZTALK'],$where,$where_value)[0]['STATUS'] == 'N' && !$sms) {
			return false;
		}
	}

	/** 04. 발송 **/
	for($i=0;$i<sizeof($tel_number);$i++) {
		$send_body = array(
			'profile_key' => $store_data['KAKAO_APIKEY'],
			'receiver_num' => $tel_number[$i],
			'message' => $arg['message'],
			'reserved_time' => $arg['reserved_time'],
			'sender_num' => $store_data['HOST_TEL'],
			'sms_only' => 'N'
		);

		if($sms) { // SMS 발송일 경우
			$biztalk['IDX'] = 0;
			$sms_kind = 'S';
			if(mb_strwidth($arg['message'], 'UTF-8') > 80 || $arg['img_url'] != '') {
				$sms_kind = ($arg['img_url'] == '')?'L':'M';
				$biztalk['IDX'] = ($arg['img_url'] == '')?1:2;
			}
			if(!array_key_exists('title',$arg) || trim($arg['title']) == '') {
				$arg['title'] = mb_strcut($arg['message'], 0, 120, 'UTF-8');
			}
			$send_body = array_merge($send_body,array(
				'sms_title' => $arg['title'],	// LMS 발송시 메시지 제목 120자
				'sms_kind' => $sms_kind,	// S:SMS, L:LMS, M:MMS
				'sms_only' => 'Y',
				'sms_message' => $arg['message'],
				'image_url' => $arg['img_url']
			));
		}
		else {
			$send_body['template_code'] = $biztalk['TEMPLETE_CODE'];
			for($j=1;$j<=5;$j++) {
				if($biztalk['BUTTON_'.$j.'_TYPE'] != '') {
					$button_link_m = $biztalk['BUTTON_'.$j.'_LINK_M'];
					if(array_key_exists('button_'.$j.'_link_m',$arg)) {
						foreach($arg['button_'.$j.'_link_m'] as $key => $val) {
							$button_link_m = str_replace('#{'.$key.'}',$val,$button_link_m);
						}
					}
					$button_link_pc = $biztalk['BUTTON_'.$j.'_LINK_PC'];
					if(array_key_exists('button_'.$j.'_link_pc',$arg)) {
						foreach($arg['button_'.$j.'_link_pc'] as $key => $val) {
							$button_link_pc = str_replace('#{'.$key.'}',$val,$button_link_m);
						}
					}

					$send_body['button'.$j] = array(
						'name' => $biztalk['BUTTON_'.$j.'_NAME'],
						'type' => $_biztalk_type[$biztalk['BUTTON_'.$j.'_TYPE']]
					);
					switch($biztalk['BUTTON_'.$j.'_TYPE']) {
						case '웹링크':
							if(trim($biztalk['BUTTON_'.$j.'_LINK_PC']) != '') {
								$send_body['button'.$j]['url_pc'] = $button_link_pc;
							}
							if(trim($biztalk['BUTTON_'.$j.'_LINK_M']) != '') {
								$send_body['button'.$j]['url_mobile'] = $button_link_m;
							}
						break;

						case 'IOS':
							$send_body['button'.$j]['schema_ios'] = $biztalk['BUTTON_'.$j.'_IOS'];
						break;

						case '안드로이드':
							$send_body['button'.$j]['schema_android'] = $biztalk['BUTTON_'.$j.'_ANDROID'];
						break;
					}
				}
			}
		}

		$db->insert(
			$_TABLE['BIZTALK_SEND'],
			array(
				'STORE_NO' => $store_no,
				'POS_NO' => $pos_no,
				'BIZTALK_NO' => $biztalk['IDX'],
				'RECEIVER_NUM' => $tel_number[$i],
				'MESSAGE' => $arg['message'],
				'SEND_VALUES' => json_encode([$send_body])
			)
		);
	}
	return true;
}


function get_child_warehouse_category_no($father_no) {
	global $_TABLE;

	$db = new db();
	$result[] = intval($father_no);
	
	$db->query('
		SELECT 
				IDX 
			FROM '.$_TABLE['SHOP_WARE_CATEGORY'].'
			WHERE 
				FATHER_NO = ?
	',array($father_no));
	foreach($db->fetch() as $data) {
		$result = array_merge($result,get_child_warehouse_category_no($data['IDX']));
	}
	return $result;
}

