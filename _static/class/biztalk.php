<?php
/**
 *
 * 비즈톡 발송 API Class
 * =============================
 * Author : 양한빈
 * Date : 2022-07-01
 * Version : 1.0
 * Describe : 
 * History : 2022-07-01 최초작성
 *
 */

class biztalk {

	protected $svc;
	protected $api_url;
	protected $api_token_url;

	public function __construct($svc = null) {
		$svc = (defined('BIZTALK'))?BIZTALK['SERVICE']:null;
		$this->svc = $svc;

		switch($svc) {
			case '비즈뿌리오':
				$this->api_url = 'https://api.bizppurio.com/v3/message';
				$this->api_token_url = 'https://api.bizppurio.com/v1/token';
			break;

			case '비즈엠':
			break;
		}
	}

	public function send($tel_number = null,$templete = null,$arg = []) {
		if($templete == NULL) return;
		if(!isset($arg['keyword']) || !array_key_exists('keyword',$arg) || !is_array($arg['keyword'])) $arg['keyword'] = array();
		$tel_number = str_replace('-','',$tel_number);

		switch($this->svc) {
			case '비즈뿌리오':
				return $this->send_bizppurio($tel_number,$templete,$arg);
			break;

			case '비즈엠':
				return $this->send_bizm($tel_number,$templete,$arg);
			break;
		}
	}

	public function get_token() {
		global $_CONFIG,$_TABLE;

		$db = new db();
		$data = null;
		$result = null;

		switch($this->svc) {
			case '비즈뿌리오':
				/** 기존 DB에서 가져옴 **/
				if(array_key_exists('_BIZTALK',$_CONFIG['SITE'])) $data = $_CONFIG['SITE']['_BIZTALK'];

				/** 토큰 재발행 **/
				if($data == null || time() > strtotime($data['expired'])) {
					$data = curl($this->api_token_url,array(
						'Accept: application/json',
						'Content-Type:application/json',
						'Authorization: Basic '. base64_encode(BIZTALK['ID'].':'.BIZTALK['PW'])
					),array('data1'=>'test1','data2'=>'test2'),30,array('json'=>true,'header_merge'=>false));

					if($data != '') {
						/** DB에 업데이트 **/
						$db->insert($_TABLE['CONFIG'],array('SITE'=>'_biztalk','CODE'=>'_biztalk','VAL'=>$data),'SITE=? AND CODE=?',array('_biztalk','_biztalk'));
						$data = json_decode($data,true);
					}
					if($data == NULL) {
						$data = array('type' => '','accesstoken'=>'');
					}
				}
				$result = $data['type'].' '.$data['accesstoken'];
			break;
		}

		$db->close();
		return $result;
	}

	public function send_bizm($tel_number = null,$templete = null,$arg = []) {
		global $_CONFIG,$_TABLE;

		/** 01. 변수 정리 **/
		$sms = false;
		if(!is_array($tel_number)) $tel_number = array($tel_number);
		if(array_key_exists('sms',$arg) === TRUE) $sms = true; // SMS 여부 확인
		if(array_key_exists('reserved_time',$arg) === FALSE) $arg['reserved_time'] = '00000000000000';
		if(array_key_exists('keyword',$arg) === FALSE || !is_array($arg['keyword'])) $arg['keyword'] = array();
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
			$biztalk = $db->get($_TABLE['BIZTALK_DEF'],'TEMPLETE=?',array($templete))[0];
			if($biztalk == null || BIZTALK['KEY'] == '') {
				return false;
			}
			if($biztalk['CONTENTS'] != '' && array_key_exists('message',$arg) === FALSE) $arg['message'] = $biztalk['CONTENTS'];
			foreach($arg['keyword'] as $key => $val) {
				$arg['message'] = str_replace('#{'.$key.'}',$val,$arg['message']);
			}
		}

		/** 04. 발송 **/
		for($i=0;$i<sizeof($tel_number);$i++) {
			$send_body = array(
				'profile_key' => BIZTALK['KEY'],
				'receiver_num' => $tel_number[$i],
				'message' => $arg['message'],
				'reserved_time' => $arg['reserved_time'],
				'sender_num' => BIZTALK['TEL'],
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

			$db->insert($_TABLE['BIZTALK_SEND'],array(
				'BIZTALK_NO' => $biztalk['IDX'],
				'RECEIVER_NUM' => $tel_number[$i],
				'MESSAGE' => $arg['message'],
				'SEND_VALUES' => json_encode([$send_body])
			));
		}
	}

	public function send_bizppurio($tel_number = null,$templete = null,$arg = []) {
		global $_CONFIG,$_TABLE;

		$response_code = array(
			1000 => '성공',
			2000 => '메시지가 유효하지 않음',
			3000 => '비즈 뿌리오 계정에 접속 허용 아이피가 등록되어있지 않음',
			3001 => '인증 토큰 발급 호출 시 Basic Authentication 정보가 유효하지 않음',
			3002 => '토큰이 유효하지 않음',
			3003 => '아이피가 유효하지 않음',
			3004 => '계정이 유효하지 않음',
			3005 => '인증 정보가 유효하지 않음 (bearer)',
			3006 => '비즈뿌리오 계정이 존재하지 않음',
			3007 => '비즈뿌리오 계정의 암호가 유효하지 않음',
			3008 => '비즈뿌리오에 허용된 접속 수를 초과함',
			3009 => '비즈뿌리오 계정이 중지 상태임',
			3010 => '비즈뿌리오 계정에 등록된 접속 허용 IP 와 일치하지 않음',
			3011 => '비즈뿌리오 내에서 알 수 없는 오류가 발생됨',
			3012 => '비즈뿌리오에 존재하지 않은 메시지 예 : 보관 주기 35 일이 지난 메시지',
			3013 => '완료 처리 되지 않은 메시지 예 통신사로부터 결과 미 수신',
			5000 => '전송 결과 재 요청 실패',
			5001 => '요청한 URI 리소스가 존재하지 않음',
			9000 => '알 수 없는 오류 발생'
		);

		$button = array();
		$token = $this->get_token(); // 토큰 가져옴
		$db = new db(); // DB접속
		$data = $db->get($_TABLE['BIZTALK_DEF'],'TEMPLETE=? AND STATUS="Y"',array($templete));
		if(is_array($data)) $data = $data[0];
		else return;
		foreach($arg['keyword'] as $key => $val) { // 내용 변수 대입
			$data['CONTENTS'] = str_replace('#{'.$key.'}',$val,$data['CONTENTS']);
		}
		/** 버튼 생성 **/
		if(array_key_exists('button',$arg) && is_array($arg['button'])) {
			foreach($arg['button'] as $row) {
				$button[] = array(
					'name'=>$row[0],
					'type'=>'WL',
					'url_pc'=>$row[1],
					'url_mobile'=>(sizeof($row)>2)?$row[2]:$row[1]
				);
			}
		}
		for($i=1;$i<=5;$i++) {
			if($data['BUTTON_'.$i.'_NAME'] != '' && in_array($data['BUTTON_'.$i.'_TYPE'],array('WL','AL'))) {
				$button[] = array(
					'name'=>$data['BUTTON_'.$i.'_NAME'],
					'type'=>$data['BUTTON_'.$i.'_TYPE'],
					'url_pc'=>$data['BUTTON_'.$i.'_LINK_PC'],
					'url_mobile'=>$data['BUTTON_'.$i.'_LINK_M']
				);
				switch($data['BUTTON_'.$i.'_TYPE']) {
					case 'AL': // 앱링크
						$button[sizeof($button)-1]['scheme_ios'] = $data['BUTTON_'.$i.'_IOS'];
						$button[sizeof($button)-1]['scheme_android'] = $data['BUTTON_'.$i.'_ANDROID'];
					break;
				}
			}
		}

		$result = curl($this->api_url,array(
						'Accept: application/json',
						'Content-Type:application/json',
						'Authorization:'.$token
					),array(
						'account'=>BIZTALK['ID'],
						'type'=>'at',
						'from'=>BIZTALK['TEL'],
						'to'=>$tel_number,
						'refkey'=>'P'.time(),
						'content'=>array(
							'at'=>array(
								'message'=>$data['CONTENTS'],
								'senderkey'=>BIZTALK['KEY'],
								'templatecode'=>$data['TEMPLETE_CODE'],
								'button'=>$button
							)
						)
					),3,array('json'=>true,'header_merge'=>false));

		/** 결과 기록 **/
		$result_org = $result;
		$result = json_decode($result,true);
		$contents_no = (array_key_exists('contents_no',$arg))?$arg['contents_no']:0;
		$member_no = (array_key_exists('member_no',$arg))?$arg['member_no']:0;
		$msg_id = (is_array($result) && array_key_exists('MSGID',$result))?$result['MSGID']:'';
		$db->insert($_TABLE['BIZTALK_LOG'],array(
			'CONTENTS_NO'=>$contents_no,
			'MEMBER_NO'=>$member_no,
			'BIZTALK_NO'=>$data['IDX'],
			'TO_TEL'=>$tel_number,
			'MSG_ID'=>$msg_id,
			'CONTENTS'=>$data['CONTENTS'],
			'RESPONSE_CODE'=>$result['code'],
			'RESPONSE_MSG'=>'',
			'RESPONSE_REMARK'=>$response_code[$result['code']],
			'STATUS'=>($result['code'] == 1000)?'성공':'실패'
		));

		$db->close();
	}
}
