<?php
/**
 *
 * FCM Push Server Class
 * =============================
 * Author : 양한빈
 * Date : 2021-01-13 17:00
 * Version : 1.0
 * Describe : 연동 서비스별 SMS 문자 발송 클래스
 * History : 2020-01-13 최초작성
 *
 */

class sms {
	private $api;
    protected $conn;
	protected $query;
    protected $show_errors = true;
    protected $query_closed = true;
	public $query_count = 0;

	public function __construct() {
		$this->db = new db();
	}

	public function send() {
		switch(str_replace(' ','',strtolower(SMS['CP']))) {
			case 'cafe24':
				send_cafe24(func_get_args());
				break;
			case 'gabia':
				$api = new gabia_sms();
				send_gabia(func_get_args());
				break;
			case 'lgu+':
			case 'lguplus':
				send_lguplus(func_get_args());
				break;
			case 'biztalk':
			case 'kakao':
				send_kakaobiztalk(func_get_args());
				break;
			default:
				return false;
		}
	}

	public function send_cafe24() {
		$result = false;

		//$sms_url = 'http://sslsms.cafe24.com/sms_sender.php'; // 전송요청 URL
		$sms_url = 'https://sslsms.cafe24.com/sms_sender.php'; // HTTPS 전송요청 URL
		$sms = array(
			'user_id' => SMS['ID'],		//SMS 아이디.
			'secure' => SMS['APIKEY'],	//인증키
			'msg' => stripslashes($msg),
			'rphone' => $to,
			'sphone1' => '031',
			'sphone2' => '211',
			'sphone3' => '0410',
			'rdate' => $_POST['rdate'],
			'rtime' => $_POST['rtime'],
			'mode' => '1', // base64 사용시 반드시 모드값을 1로 주셔야 합니다.
			'returnurl' => $_POST['returnurl'],
			'testflag' => '',
			'destination' => $_POST['destination'],
			'repeatFlag' => $_POST['repeatFlag'],
			'repeatNum' => $_POST['repeatNum'],
			'repeatTime' => $_POST['repeatTime']
		);
		$host_info = explode('/', $sms_url);
		$host = $host_info[2];
		$path = $host_info[3].'/'.$host_info[4];

		srand((double)microtime()*1000000);
		$boundary = '---------------------'.substr(md5(rand(0,32000)),0,10);

		// 본문 생성
		foreach($sms as $key => $val){
			$data .= '--'.$boundary.chr(13).chr(10)
				  .  'Content-Disposition: form-data; name=\"'.$key.'\"'.chr(13).chr(10).chr(13).chr(10)
				  .  base64_encode($val).chr(13).chr(10)
				  .  '--'.$boundary.chr(13).chr(10);
		}

		$header = 'POST /'.$path.' HTTP/1.0'.chr(13).chr(10)
				. 'Host: '.$host.chr(13).chr(10)
				. 'Content-type: multipart/form-data, boundary='.$boundary.chr(13).chr(10)
				. 'Content-length: '.strlen($data).chr(13).chr(10).chr(13).chr(10);

		$fp = fsockopen($host, 443);
		if ($fp) {
			fputs($fp, $header.$data);
			$rsp = '';
			while(!feof($fp)) {
				$rsp .= fgets($fp,8192);
			}
			fclose($fp);
			$resultmsg2 = trim($rsp);
			$rmsg_arr = explode(chr(13).chr(10).chr(13).chr(10),trim($rsp));
			$rMsg = explode(',', $rmsg_arr[1]);
			$resultcode = $rMsg[0]; //발송결과
			$Count= $rMsg[1]; //잔여건수


			//발송결과 알림
			switch($resultcode) {
				case 'success':
					$result = true;
					$resulttxt = '성공';
					$resultmsg = '문자를 성공적으로 발송했습니다. 잔여건수는 '.$Count.'건 입니다.';
					break;
				case 'reserved':
					$result = true;
					$resulttxt = '성공';
					$resultmsg = '성공적으로 예약되었습니다. 잔여건수는 '.$Count.'건 입니다.';
					break;
				case '3205':
					$resulttxt = '실패';
					$resultmsg = '잘못된 번호형식입니다.';
					break;
				case '0044':
					$resulttxt = '실패';
					$resultmsg = '스팸문자는발송되지 않습니다.';
					break;
				default:
					$resulttxt = '실패';
					$resultmsg = '[Error]'.$result;
			}
		}
		else {
			$resulttxt = '실패';
			$resultmsg2 = 'Connection Failed.';
		}

		finish($result,$resultcode,$resultmsg,$resultmsg2,$code,$msg,$id,$send_tel,$from_tel);
	}

	public function send_gabia() {
		if($smsapi->getSmsCount() <= 0) { // 남은 문자 발송 횟수
			return false;
		} 
		else {
			$msg = htmlspecialchars($msg);
			// 발송시에 _REF_KEY_는 나중에 개별적인 발송 결과를 확인하고자 할 때 사용되는 값입니다.
			// 고객 내부의 규칙에 따른 40byte 이내의 unique한 값을 넣어주시면 됩니다.
			// 발송번호, 회신번호, 본문, ref_key, 예약
			if(mb_strlen($msg,'euc-kr') <= 80) {
				$r = $smsapi->sms_send($to,$recv,$msg,$uniquekey,$reservedate);
			}
			else {
				$r = $smsapi->lms_send($to,$recv,$msg,$title,$uniquekey,$reservedate);
			}

			// 발송 성공
			if ($r == gabiaSmsApi::$RESULT_OK) {
				$result = true;
				$resulttxt = '성공';
				$resultmsg = '성공';
			}
			// 발송 실패
			else {
				$result = false;
				$resulttxt = '실패';
				$resultmsg = '실패';
			}

			$resultcode = $smsapi->getResultCode();
			$resultmsg2 = $smsapi->getResultMessage();
		}

		finish($result,$resultcode,$resultmsg,$resultmsg2,$code,$msg,$id,$send_tel,$from_tel);
	}

	public function send_lguplus() {
	}

	public function send_kakaobiztalk() {
	}

	private function finish() {
		$this->db->insert(
			$_TABLE['SMS_SENDLIST'],
			array(
				'RESULT'=>$args[0],
				'RESULTCODE'=>$args[1],
				'RESULTMSG'=>$args[2],
				'RESULTMSG2'=>$args[3],
				'CODE'=>$args[4],
				'MSG'=>$args[5],
				'ID'=>$args[6],
				'SEND_TEL'=>$args[7],
				'FROM_TEL'=>$args[8]
			)
		);

		return $result;
	}
}

?>