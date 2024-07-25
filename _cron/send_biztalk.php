<?php
require_once '../_static/autoload.php';

/** 중복 실행 방지 **/
$filename = '_send_biztalk.txt';
if(file_exists($filename)) exit;
$file = fopen($filename, 'w') or die('Unable to open file!');
fwrite($file, time().' '.array_sum(explode(' ', microtime())));
fclose($file);

/** 발송해야할 메시지 **/
$db->query('
	SELECT 
			A.IDX,A.STORE_NO,A.POS_NO,A.BIZTALK_NO,A.RECEIVER_NUM,A.MESSAGE,A.TRY_COUNT,A.SEND_VALUES,
			B.KAKAO_APIKEY
		FROM '.$_TABLE['BIZTALK_SEND'].' AS A 
		LEFT JOIN '.$_TABLE['STORE'].' AS B ON A.STORE_NO = B.IDX 
	WHERE 
		A.STATUS = "N" 
');

$j = 0;
$db2 = new db();
$arr_idx = null;
foreach($db->fetch() as $data) {
	/** 발송 중 상태로 바꿈 **/
	$db2->update($_TABLE['BIZTALK_SEND'],array('STATUS'=>'Y'),'IDX=?',array($data['IDX']));

	/** 발송ID 업데이트 **/
	$send_arr = json_decode($data['SEND_VALUES'],true);
	list($usec, $sec) = explode(' ',microtime());
	$send_arr[0]['msgid'] = $sec.addzero(intval(floatval($usec)*10000),6).addzero($j++,3);

	/** 비즈톡 서버에 발송 요청 **/
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, BIZTALK['APIURL'].'/v2/'.trim($data['KAKAO_APIKEY']).'/sendMessage');
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($send_arr));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json','userid:'.BIZTALK['ID']));
	$output = curl_exec($ch);
	$err = @curl_error($curl);
	curl_close($ch);
	$output_json = json_decode($output,true);
	echo $output;

	/** 결과 처리 **/
	for($i=0;$i<sizeof($output_json);$i++) {
		$output_json[$i]['result'] = ($output_json[$i]['result']=='Y')?'성공':'실패';

		// 성공 혹은 총 시도횟수 3회가 되었을 경우
		if(intval($data['TRY_COUNT']) >= 3 || $output_json[$i]['result'] == '성공') {
			$arr_idx[] = $data['IDX'];
			$db2->insert(
				$_TABLE['BIZTALK_LOG'],
				array(
					'STORE_NO'=>$data['STORE_NO'],
					'POS_NO'=>$data['POS_NO'],
					'TEMPLETE_NO'=>$data['BIZTALK_NO'],
					'TEL'=>$data['RECEIVER_NUM'],
					'CONTENTS'=>mysqli_real_escape_string($connect,$data['MESSAGE']),
					'MSG_ID'=>$output_json[$i]['msgid'],
					'IS_SMS'=>$send_arr[0]['sms_only'],
					'RESPONSE_CODE'=>$output_json[$i]['code'],
					'RESPONSE_MSG'=>$output_json[$i]['error'],
					'RESPONSE_REMARK'=>BIZTALK['MSG'][$output_json[$i]['code']],
					'STATUS'=>$output_json[$i]['result']
				)
			);
		}

		/** 미발송, 시도횟수+1로 상태 바꿈 **/
		else {
			$db2->update(
				$_TABLE['BIZTALK_SEND'],
				array(
					'STATUS'=>'N',
					'TRY_COUNT'=>intval($data['TRY_COUNT'])+1
				),
				'IDX=?',
				array($data['IDX'])
			);
		}
	}
}

/** 발송 완료된 메시지 삭제 **/
if(is_array($arr_idx)) {
	$db->delete($_TABLE['BIZTALK_SEND'],'IDX IN (?)',array(implode(',',$arr_idx)));
}

/** 중복 발송 검사 파일 삭제 **/
unlink($filename);
