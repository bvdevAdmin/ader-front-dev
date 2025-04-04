<?php
include 'scheduler._config.php';

$response_remark = array(
	'E101'=>'Request 데이터 오류 (올바르지 않은 jsonarray 형식)',
	'E102'=>'발신프로필키가 없거나 유효하지 않음',
	'E103'=>'템플릿 코드가 없음',
	'E104'=>'유효하지 않은 사용자 전화번호 (전화번호 형식 오류, 안심번호 등)',
	'E105'=>'유효하지 않은 SMS 발신번호 (전화번호 형식 오류, 안심번호 등)',
	'E106'=>'메시지 내용이 없음',
	'E107'=>'카카오 발송 실패시 SMS 전환 발송을 하는 경우 SMS 메시지 내용이 없음',
	'E108'=>'예약일자 이상(잘못된 예약일자 요청)',
	'E109'=>'중복된 MsgId 요청',
	'E110'=>'MsgId 를 찾을 수 없음',
	'E111'=>'첨부 이미지 URL 정보를 찾을 수 없음',
	'E112'=>'메시지 길이제한 오류(메시지 제한길이 또는 1,000 자 초과)',
	'E113'=>'메시지 ID 길이제한 오류(메시지 ID 20 자 초과)',
	'E114'=>'삭제된 발신프로필',
	'E115'=>'차단 상태의 발신프로필',
	'E116'=>'차단 상태의 플러스친구 (플러스친구 운영툴에서 확인)',
	'E117'=>'닫힘 상태의 플러스친구 (플러스친구 운영툴에서 확인)',
	'E118'=>'삭제된 플러스친구 (플러스친구 운영툴에서 확인)',
	'E119'=>'삭제대기 상태의 플러스친구 (플러스친구 운영툴에서 확인)',
	'E998'=>'최대 요청 수 초과',
	'E999'=>'시스템 오류',
	'K000'=>'카카오 비즈메시지 발송 성공',
	'K101'=>'메시지를 전송할 수 없음 (알림톡/친구톡 수신불가 조건의 사용자)',
	'K102'=>'전화번호 오류',
	'K103'=>'메시지 길이제한 오류(메시지 제한길이 또는 1,000 자 초과)',
	'K104'=>'템플릿을 찾을 수 없음',
	'K105'=>'메시지 내용이 템플릿과 일치하지 않음',
	'K106'=>'첨부 이미지 URL 또는 링크 정보가 올바르지 않음',
	'K201'=>'삭제된 발신프로필',
	'K202'=>'차단 상태의 발신프로필',
	'K203'=>'차단 상태의 플러스친구 (플러스친구 운영툴에서 확인)',
	'K204'=>'닫힘 상태의 플러스친구 (플러스친구 운영툴에서 확인)',
	'K205'=>'삭제된 플러스친구 (플러스친구 운영툴에서 확인)',
	'K206'=>'삭제대기 상태의 플러스친구 (플러스친구 운영툴에서 확인)',
	'K207'=>'메시지차단 상태의 플러스친구 (플러스친구 운영툴에서 확인)',
	'K208'=>'링크버튼 형식 오류 (잘못된 파라메터 요청)',
	'K301'=>'메시지 전송 실패(테스트 서버에서 친구관계가 아닌 경우)',
	'K302'=>'템플릿 일치 확인시 오류 발생',
	'K303'=>'메시지 발송 가능한 시간이 아님(친구톡 / 마케팅 메시지는 08 시부터 20 시까지 발송 가능)',
	'K304'=>'변수 글자수 제한 초과',
	'K998'=>'기타 오류로 메시지 전송 실패',
	'K999'=>'시스템 오류',
	'M000'=>'SMS/LMS 발송 성공',
	'M001'=>'SMS 발송 처리 중',
	'M101'=>'메시지를 전송할 수 없음',
	'M102'=>'전화번호 오류',
	'M103'=>'수신자 착신거부',
	'M104'=>'스팸 번호로 등록 됨',
	'M105'=>'수신자 단말기 전원 꺼짐',
	'M106'=>'메시지 길이제한 오류(메시지 제한길이 또는 1,000 자 초과)',
	'M107'=>'미등록된 SMS 발신번호',
	'M998'=>'이동통신사 결과 수신 시간 초과',
	'M999'=>'기타 시스템 오류',
	'R000'=>'발송 예약 성공',
	'R109'=>'중복된 MsgId 요청'
);
$j=0;
$db->query('
	SELECT 
			A.*,
			B.KAKAO_APIKEY
		FROM '.$_TABLE['BIZTALK_SEND'].' AS A 
		LEFT JOIN '.$_TABLE['STORE'].' AS B ON A.STORE_NO = B.IDX 
');
foreach($db->fetch() as $data) {
	$send_arr = json_decode($data['SEND_VALUES'],true);

	list($usec, $sec) = explode(' ',microtime());
	$send_arr[0]['msgid'] = $sec.addzero(intval(floatval($usec)*10000),6).addzero($j++,3);
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, BIZTALK['APIURL'].'/v2/'.$data['KAKAO_APIKEY'].'/sendMessage');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($send_arr));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json','userid:'.BIZTALK['ID']));
	$output = curl_exec($ch);
	$err = @curl_error($ch);
	curl_close($ch);
	$output_json = json_decode($output,true);
	print_r($output_json);

	for($i=0;$i<sizeof($output_json);$i++) {
		$output_json[$i]['result'] = ($output_json[$i]['result']=='Y')?'성공':'실패';

		// 성공 혹은 총 시도횟수 3회가 되었을 경우
		if(intval($data['TRY_COUNT']) >= 3 || $output_json[$i]['result'] == '성공') {
			$db->insert(
				$_TABLE['BIZTALK_LOG'],
				array(
					'STORE_NO'=>$data['STORE_NO'],
					'POS_NO'=>$data['POS_NO'],
					'TEMPLETE_NO'=>$data['BIZTALK_NO'],
					'MSG_ID'=>$output_json[$i]['msgid'],
					'TEL'=>$data['RECEIVER_NUM'],
					'CONTENTS'=>$data['MESSAGE'],
					'RESPONSE_CODE'=>$output_json[$i]['code'],
					'RESPONSE_MSG'=>$output_json[$i]['error'],
					'RESPONSE_REMARK'=>$response_remark[$output_json[$i]['code']],
					'STATUS'=>$output_json[$i]['result']
				)
			);
			$db->delete($_TABLE['BIZTALK_SEND'],'IDX=?',array($data['IDX']));
		}
		elseif($i==0) {
			$db->update(
				$_TABLE['BIZTALK_SEND'],
				array('TRY_COUNT'=>$data['TRY_COUNT']+1),
				'IDX=?',
				array($data['IDX'])
			);
		}
	}
}
?>