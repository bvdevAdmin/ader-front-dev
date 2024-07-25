<?php
/**
 *
 * KAKAO API Class
 * =============================
 * Author : 양한빈
 * Date : 2021-01-13 17:00
 * Version : 1.0
 * Describe : 
 * History : 2020-01-13 최초작성
 *
 */

class kakao {

	public function get_token() {
	}
	public function refresh_token() {
	}

	public function login() {
	}

	public function logout() {
	}

	public function biztalk($tel_number = null,$arg = []) {
		global $connect,$client_no,$store_no,$pos_no,$store_data,$_TABLE;

		/** 01. 변수 정리 **/
		if(!is_numeric($pos_no)) $pos_no = 0;
		if(!is_array($arg['keyword'])) $arg['keyword'] = array();
		if(!is_array($tel_number)) $tel_number = array($tel_number);
		if(!in_array('title',$arg)) $arg['title'] = $arg['mcode'].' '.$arg['scode'];
		for($i=0;$i<sizeof($tel_number);$i++) {
			$tel_number[$i] = str_replace(array('-',' ',chr(10),chr(13)),'',$tel_number[$i]);
		}
		if(is_array($_CONFIG['BIZTALK']['KEYWORD'])) {
			$arg['keyword'] = array_merge($arg['keyword'],$_CONFIG['BIZTALK']['KEYWORD']);
		}

		/** 02. 템플릿 불러옮 **/
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
		$biztalk = db_get($_TABLE['BIZTALK_DEF'],$where);
		if($biztalk == null) {
			@record('사용자','알림톡 전송 ['.$arg['title'].']',array('tel'=>$tel_number,'remark'=>'존재하지 않는 템플릿','status'=>'실패'));
			echo '존재하지 않는 템플릿';
			return false;
		}
		elseif($store_data['KAKAO_APIKEY'] == '') {
			@record('사용자','알림톡 전송 ['.$arg['title'].']',array('tel'=>$tel_number,'remark'=>'APIKEY 없음','status'=>'실패'));
			echo 'APIKEY 없음';
			return false;
		}
		if($biztalk['CONTENTS'] != '' && $arg['message'] == '') $arg['message'] = $biztalk['CONTENTS'];
		foreach($arg['keyword'] as $key => $val) {
			$arg['message'] = str_replace('#{'.$key.'}',$val,$arg['message']);
		}

		/** 03. 발송 환경설정 **/
		$where = 'STORE_NO='.$store_no.' AND BIZTALK_NO="'.$biztalk['IDX'].'"';
		if(db_count($_TABLE['BIZTALK'],$where) == 0) { 
			@db_insert($_TABLE['BIZTALK'],'STORE_NO,BIZTALK_NO,STATUS',$store_no.','.$biztalk['IDX'].',"Y"');
		}
		$biztalk_status = db_get($_TABLE['BIZTALK'],$where);

		/** 04. 발송 **/
		if($biztalk_status['STATUS'] == 'N') {
			@record('사용자','알림톡 전송 ['.$arg['title'].']',array('tel'=>$tel_number[0],'remark'=>'미발송 설정된 템플릿','status'=>'실패'));
			return false;
		}
		else {
			for($i=0;$i<sizeof($tel_number);$i++) {
				list($usec, $sec) = explode(' ',microtime());
				$msgid = $sec.intval(floatval($usec)*1000000).addzero($i,3);

				$result = $db->insert(
					$_TABLE['BIZTALK_SEND'],
					array(
						'STORE_NO' => $store_no,
						'POS_NO' => $pos_no,
						'BIZTALK_NO' => $biztalk['IDX'],
						'RECEIVER_NUM' => $tel_number[$i],
						'MESSAGE' => $arg['message'],
						'SEND_VALUES' => 
							json_encode([array(
								'msgid' => $msgid,
								'profile_key' => $store_data['KAKAO_APIKEY'],
								'template_code' => $biztalk['TEMPLETE_CODE'],
								'receiver_num' => $tel_number[$i],
								'message' => $arg['message'],
								'reserved_time' => '00000000000000',
								'sender_num' => $store_data['TEL'],
								'sms_only' => 'N'
							)])
					)
				);
			}
		}
	}
}
?>