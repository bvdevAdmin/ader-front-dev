<?php
/*
 +=============================================================================
 | 
 | 공통 라이브러리
 | ----------
 |
 | 최초 작성	: 양한빈
 | 최초 작성일	: 2015.12.08
 | 최종 수정일	: 2024.06.23
 | 버전		: 3.0
 | 설명		: 
 | 
 +=============================================================================
*/
if(!function_exists('mb_str_split')) {
	function mb_str_split($string, $split_length = 1, $encoding = null) {

		if (null !== $string && !is_scalar($string) && !(is_object($string) && method_exists($string, '__toString'))) {
			trigger_error('mb_str_split(): expects parameter 1 to be string, '.gettype($string).' given', E_USER_WARNING);
			return null;
		}
		if (null !== $split_length && !is_bool($split_length) && !is_numeric($split_length)) {
			trigger_error('mb_str_split(): expects parameter 2 to be int, '.gettype($split_length).' given', E_USER_WARNING);
			return null;
		}
		$split_length = (int) $split_length;
		if (1 > $split_length) {
			trigger_error('mb_str_split(): The length of each segment must be greater than zero', E_USER_WARNING);
			return false;
		}
		if (null === $encoding) {
			$encoding = mb_internal_encoding();
		} else {
			$encoding = (string) $encoding;
		}
	   
		if (! in_array($encoding, mb_list_encodings(), true)) {
			static $aliases;
			if ($aliases === null) {
				$aliases = [];
				foreach (mb_list_encodings() as $encoding) {
					$encoding_aliases = mb_encoding_aliases($encoding);
					if ($encoding_aliases) {
						foreach ($encoding_aliases as $alias) {
							$aliases[] = $alias;
						}
					}
				}
			}
			if (! in_array($encoding, $aliases, true)) {
				trigger_error('mb_str_split(): Unknown encoding "'.$encoding.'"', E_USER_WARNING);
				return null;
			}
		}
	   
		$result = [];
		$length = strlen($string);
		for ($i = 0; $i < $length; $i += $split_length) {
			$result[] = mb_strcut($string, $i, $split_length, $encoding);
		}
		return $result;
	}
}

function is_json($string) {
	json_decode($string);
	return (json_last_error() == JSON_ERROR_NONE);
}

function getmicrotime() {
	list($usec, $sec) = explode(' ',microtime());
	return ((float)$usec + (float)$sec);
}

function tel_format($number = '') {
	if(preg_match( '/(\d{3})(\d{4})(\d{4})$/', $number,  $matches)) {
		return $matches[1].'-'.$matches[2].'-'.$matches[3];
	}
	else {
		return $number;
	}
}

function file_modified_timestamp($file) {
	return stat($file)['mtime'];
}

function is_phone_number($number) {
	$is_rule = false;
	$re_phoneNum = preg_replace('/-/', '', $number);

	$mobile = preg_match('/^01[016789]{1}-?([0-9]{3,4})-?[0-9]{4}$/', $number);
	$tel = preg_match('/^(02|0[3-6]{1}[1-5]{1})-?([0-9]{3,4})-?[0-9]{4}$/', $number);
	$rep = preg_match('/^(15|16|18)[0-9]{2}-?[0-9]{4}$/', $number);
	$rep2 = preg_match('/^(02|0[3-6]{1}[1-5]{1})-?(15|16|18)[0-9]{2}-?[0-9]{4}$/', $number);
	$num = preg_match('/^(070|(050[2-8]{0,1})|080|013)-?([0-9]{3,4})-?[0-9]{4}$/', $number);

	if ($mobile != false) {
		$is_rule = true;
		if (strlen($re_phoneNum) > 11) {
			$is_rule = false;
		}
	} else if ($tel != false) {
		$is_rule = true;
		if (strlen($re_phoneNum) > 11) {
			$is_rule = false;
		}
	} else if ($rep != false) {
		$is_rule = true;
		if (strlen($re_phoneNum) != 8) {
			$is_rule = false;
		}
	} else if ($num != false) {
		$is_rule = true;
		if (strlen($re_phoneNum) > 12) {
			$is_rule = false;
		}
	}

	if ($rep2 == true) {
		$is_rule = false;
	}

	return $is_rule;
}
function is_tel($number) {
	return is_phone_number($number);
}


function implode_quotes($arr) {
	if(is_array($arr)) {
		for($i=0;$i<sizeof($arr);$i++) $arr[$i] = '"'.$arr[$i].'"';
		$arr = implode(',',$arr);
	}
	elseif($arr != '') {
		$arr = '"'.$arr.'"';
	}

	return $arr;
}


function del_html($str) {
	$str = str_replace( '>', '&gt;',$str );
	$str = str_replace( '<', '&lt;',$str );
	return $str;
}

function addzero($str,$len = 6) {
	if(!is_numeric($str)) {
		return false;
	}
	else {
		$str = (string)$str;
		if(strlen($str) < $len) {
			for($i= strlen($str) ; $i<=$len ; $i++) {
				$str = '0'.$str;
				if(strlen($str) == $len) break;
			}
		}
		return $str;
	}
}


/***************************************************************************
 * 주소에서 호스트명만 리턴
 **************************************************************************/
function get_hostname($url) {
	$r = explode('/',$url);
	return $r[2];
}


/***************************************************************************
 * 이전 페이지의 URL을 검사하여 짐 페이지와 다를 경우 1 리턴
 **************************************************************************/
function check_posturl($prev) {
  $now = $_SERVER['HTTP_HOST'];
  $prev = get_hostname($prev);
  
  if($now != $prev) return 1;
  return 0;
}

/***************************************************************************
 * 글자수 리턴
 **************************************************************************/
function str_count($str,$mbstring){
	$kChar = 0;
	for( $i = 0 ; $i < strlen($str) ;$i++){
		$lastChar = ord($str[$i]);
		if($lastChar >= 127) {
			$i= $i+2;
			if($mbstring) $kChar++;
		}
		$kChar++;
	}
	return $kChar;
}

function curl($url, $header = array(), $data = array(), $is_json = false, $timeout = 60) {
	$header_org = array(
		'Accept: application/json',
		'user-agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.139 Safari/537.36'
	);

	if(is_array($data) && sizeof($data) > 0) {
		if($is_json) {
			$send_data = json_encode($data,JSON_UNESCAPED_UNICODE);
		}
		else {
			$send_data = http_build_query($data);
		}
	}

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	if(isset($send_data)) {
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $send_data);
		//curl_setopt($ch, CURLOPT_POSTFIELDSIZE, 0);
	}
	curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	if($header != NULL) {
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge($header,$header_org));
	}
	$g = curl_exec($ch);
	$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
	$header = substr($g, 0, $header_size);
	$body = substr($g, $header_size);
	curl_close($ch);
	return ($is_json) ? $g : $body;
	
}

function api($url, $data = array(), $timeout = 60) {
	$body = curl($url, array(), $data, true, $timeout);
	return json_decode($body, JSON_UNESCAPED_UNICODE);
}

function str2bool($val) {
	if(!is_string($val)) return false;
	$val = strtolower((string)$val);
	if(in_array($val,['true','false','y','n','0','1'])) {
		if( $val == 'y' || $val == '1') $val = 'true';
		elseif( $val == 'n' || $val == '0') $val = 'false';
	}
	else $val = 'false';

	return $val == 'true' ? true : false;
}

/***************************************************************************
 * 파일 업로드
 * -----------
 * 작성일 : 2014.06.14
 * 사용법 : file_up(파일폼명,저장 위치,업로드 가능 확장자,에러메시지)
 * 반환   : 업로드 파일명
 **************************************************************************/
function file_up($files,$path,$args = array(
						'extension'=>null,
						'original_name'=>true,
						'original_name_return'=>false,
						'thumbnail'=>false,
						'thumbnail_width'=>120,
						'thumbnail_height'=>120 )) {
	global $_CONFIG;
	if(!is_array($args)) $args = array();
	$args = array_merge(array(
						'extension'=>null,
						'original_name'=>true,
						'original_name_return'=>false,
						'thumbnail'=>false,
						'thumbnail_width'=>120,
						'thumbnail_height'=>120 ),$args);

	if(is_string($files)) $files = $_FILES[$files];
	if(!is_array($files['name'])) {
		$files = array(
			'name' => array($files['name']),
			'type' => array($files['type']),
			'tmp_name' => array($files['tmp_name']),
			'error' => array($files['error']),
			'size' => array($files['size'])
		);
	}


	// 파일 저장 디렉토리 절대 경로 구하기
	$thisfilename	= basename(__FILE__); 
	$temp_filename	= realpath(__FILE__); 
	if(!$temp_filename) $temp_filename=__FILE__; 
	unset($temp_filename); 

	for($i=0;$i<sizeof($files['name']);$i++) {
		$filename = $files['name'][$i];		// 파일 이름 알아내기
		$file_tmp = $files['tmp_name'][$i];	// 파일 임시 저장 장소 알아내기
		$file_info = pathinfo($filename);			// 파일 확장자 알아내기


		// 실행 파일 업로드 불가
		if(array_key_exists('extension',$file_info) && strpos(strtolower($file_info['extension']),'.php,.asp,.jsp,.aspx')) {
			throw new Exception('Can not upload file : Permition Denied');
			return false;
		}
		// 정해진 확장자가 아닐 경우 에러메시지 표시후 실행 중단
		if(array_key_exists('extension',$args) && (is_array($args['extension']) || is_string($args['extension']))) {
			if(is_string($args['extension'])) $args['extension'] = [$args['extension']];
			if(in_array(strtolower($file_info['extension']),$args['extension'])) {
				throw new Exception('Can not upload file : Accept '.implode(',',$args['extension']).' files only.');
				return false;
			}
		}
		// 디렉토리 만들기
		/*
		$temp_path = explode('/',str_replace($_CONFIG['PATH']['UPLOAD'],'',$path.'/thumbnail'));
		$temp_path_root = $_CONFIG['PATH']['UPLOAD'];
		for($i=0;$i<sizeof($temp_path);$i++) {
			if($temp_path[$i] == '') continue;
			$temp_path_root .= $temp_path[$i].'/';
			if(!is_dir($temp_path_root)) @mkdir($temp_path_root,0777);
		}
		*/
		$temp_path = explode('/',$path.'/thumbnail');
		$temp_path_root = '';
		for($j=0;$j<sizeof($temp_path);$j++) {
			$temp_path_root .= $temp_path[$j].'/';
			if(!is_dir($temp_path_root)) @mkdir($temp_path_root,0777);
		}

		$filename = str_replace(' ','_',strip_tags($filename));

		// 파일 이름을 타임 스탬프로 바꾸기
		if(!array_key_exists('original_name',$args) || $args['original_name'] == true) {
			$filename = time().'&&'.$filename;
		} else {
			$filename = time().'.'.strtolower($file_info['extension']);
		}

		// 파일을 정해진 저장 디렉토리에 저장
		//$filename_real = mb_convert_encoding($filename, 'utf8', 'euc-kr');
		$filename_real = $filename;
		$res = move_uploaded_file($file_tmp,$path.$filename_real);

		// 권한 설정
		@chmod($path.$filename_real,0777);

		if($res) {
			// 썸네일 만들기
			if($args['thumbnail'] === true) {
				if(function_exists('create_thumbnail')) {
					create_thumbnail($path.$filename_real, $path.'thumbnail/'.$filename_real, $args['thumbnail_width'], $args['thumbnail_height']);
					//make_thumbnail($path.$filename_real, $args['thumbnail_width'], $args['thumbnail_height'], $path.'thumbnail/'.$filename_real);
				}
			}
			if($args['original_name_return']) {
				$result[] = array($filename,$files['name'][$i]);
			}
			else {
				$result[] = $filename;
			}
		}
		else {
			$result[] = false;
		}
	}
	if(sizeof($result) == 1 && !$args['original_name_return']) return $result[0];
	else return $result;
}


/***************************************************************************
 * 파일 삭제
 * ---------
 * 작성일 : 2014.06.14
 * 사용법 : file_up(파일명)
 * 반환   : 파일 삭제 여부
 **************************************************************************/
function file_del($filename) {
	return unlink($filename);
}


/***************************************************************************
 * 이메일 검사
 * -------
 * 작성일 : 2014.12.19
 * 사용법 : is_email(이메일주소)
 * 반환  : 이메일 무결성 여부
 **************************************************************************/
function is_email($email) {
	if(is_string($email)) {
		$email = array($email);
	}
	for($i=0;$i<sizeof($email);$i++) {
		$chk_email = (is_array($email[$i]))? $email[$i][0] : $email[$i];
		if(preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i',$chk_email) == false) {
			return false;
		}
	}
	return true;
}

/***************************************************************************
 * 페이징
 * ------
 * 작성일 :
 * 사용법 : paging(전체자료수,화면당 자료수,페이징 갯수,현재 페이지)
 * 템플릿 : css 수정
 **************************************************************************/
function paging($total,$num_div,$num_pag,$current) {
	// 총 페이지 수 구함
	$Paging['TOTAL'] = 1;
	if(!$num_div) $num_div = 1;
	if($total) $Paging['TOTAL'] = ceil($total / $num_div);

	// 시작 페이지
	if($current + $num_pag > $total_page && $total_page >= $num_pag) {
		$Paging['START'] = $total_page - $num_pag;
	}
	elseif($now_page - ($num_pag/2) < 1) $Paging['START'] = 1;
	else $Paging['START'] = $now_page - ($num_pag/2);
	$Paging['START'] = round($Paging['START'],0);

	// 페이지 네비 끝
	if($Paging['START'] + $num_pag > $Paging['TOTAL']) $Paging['END'] = $Paging['TOTAL'];
	else $Paging['END'] = $Paging['START'] + $num_pag;
	$Paging['END'] = round($Paging['END'] + 0.49,0);

	// 이전
	$Paging['PREV'] = $Paging['START'] - $num_pag;
	if($Paging['PREV'] < 1) $Paging['PREV'] = 1;

	// 다음
	$Paging['NEXT'] = $Paging['END'] + $num_pag;
	if($Paging['NEXT'] > $Paging['TOTAL']) $Paging['NEXT'] = $Paging['TOTAL'];

	return $Paging;
}


/***************************************************************************
 * 관리자 여부 판단
 * -------------
 * 작성일 : 2015.03.29
 * 사용법 : send_mail(보내는 이메일,보내는 이,받는 이메일,받는이,제목,본문)
 * 반환  : 메일 발송 성공 여부
 **************************************************************************/
/*
function send_mail($fromEmail,$fromName,$toEmail,$toName,$subject,$body) {
	global $_CONFIG;

	// 보내는 이 정보가 없을 경우 사이트 관리자로 보냄
	if($fromEmail == '') $fromEmail = $_CONFIG['ADMIN_EMAIL'];
	if($fromName == '') $fromName = $_CONFIG['ADMIN_NAME'];

	$charset = 'UTF-8'; // 문자셋 : UTF-8
	$strBoundary = '=_' . md5(uniqid(time()));
	$encoded_subject = '=?'.$charset.'?B?'.base64_encode($subject).'?='; // 인코딩된 제목
	$to		= '"=?'.$charset.'?B?'.base64_encode($toName).'?=" <'.$toEmail.'>' ; // 인코딩된 받는이
	$from	= '"=?'.$charset.'?B?'.base64_encode($fromName).'?=" <'.$fromEmail.'>' ; // 인코딩된 보내는이

	$headers  = 'MIME-Version: 1.0'.chr(10);
	$headers .= 'Content-Type: text/html; charset='.$charset.chr(10);
	$headers .= 'To: '.$to.chr(10);
	$headers .= 'From: '.$from.chr(10);
	//$headers .= 'Return-Path: '.$from.chr(10);
	$headers .= 'Content-Transfer-Encoding: 8bit'.chr(10); // 헤더 설정

	$message  = '--' . $strBoundary .chr(10);
	$message .= 'Content-Type: text/html;'.chr(10);
	$message .= 'Content-Transfer-Encoding: base64'.chr(10).chr(10);
	$message .= chunk_split(base64_encode($body)).chr(10).chr(10);
	$message .= '--' . $strBoundary . '--'.chr(10).chr(10);

	$confirm = mail($to, $encoded_subject , $body, $headers); // 메일함수

	return $confirm;
}
*/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
function send_mail($arr){
	global $_CONFIG, $msg;
	
	if(!is_array($arr)) {
		return false;
	}
	
	if(defined('SMTP')) {
		$def = array(
			'debug' => false,
			'host' => SMTP['HOST'],
			'id' => SMTP['ID'],
			'password' => SMTP['PASSWORD'],
			'port' => SMTP['PORT'],
			'from' => array(MAILER['FROM'],MAILER['NAME']),
			'body' => ''
		);
		$arr = array_merge($def,$arr);
		if(is_string($arr['to'])) {
			$arr['to'] = array(array($arr['to']));
		}
		/*
		if(!is_email($arr['to'])) {
			return false;
		}
		*/
		
		// 템플릿 불러오기
		if(array_key_exists('template',$arr)) {
			$inc_path = array(
				$_CONFIG['PATH']['MAIL'].$arr['template'].'.php',
				$_CONFIG['PATH']['MAIL'].$arr['template'].'.html'
			);
			foreach($inc_path as $form_path) {
				if(file_exists($form_path)) {
					$fp = fopen($form_path,'r') or die('템플릿 파일을 열 수 없습니다');
					while(!feof($fp)) {
						$line = fgets($fp);
						$arr['body'] .= $line;
						
						if(mb_strpos(trim($line),'<!--[[제목]') === 0) {
							$arr['subject'] = str_replace(['<!--[[제목]','-->'],'',trim($line));
						}
					}
					fclose($fp);
				}
			}
		}
		
		// 변수 변경
		$keyword = array(
			'url_domain' => ((isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == 'on') ? 'https:':'http:') . BASE_DOMAIN
		);
		if(array_key_exists('keyword',$arr)) {
			$arr['keyword'] = array_merge($keyword,$arr['keyword']);
		}
		foreach($arr['keyword'] as $key => $val) {
			$arr['body'] = str_replace('{$'.$key.'}',$val,$arr['body']);
		}

		require_once $_CONFIG['PATH']['CLASS'].'PHPMailer/src/Exception.php';
		require_once $_CONFIG['PATH']['CLASS'].'PHPMailer/src/PHPMailer.php';
		require_once $_CONFIG['PATH']['CLASS'].'PHPMailer/src/SMTP.php';

		$mail = new PHPMailer(true);

		try {
			//Server settings
			if($arr['debug']) $mail->SMTPDebug = SMTP::DEBUG_SERVER;				//Enable verbose debug output
			$mail->isSMTP();										//Send using SMTP
			$mail->Host       = $arr['host'];					//Set the SMTP server to send through
			$mail->SMTPAuth   = true;								//Enable SMTP authentication
			$mail->Username   = $arr['id'];				//SMTP username
			$mail->Password   = $arr['password'];					//SMTP password
			$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;		//Enable implicit TLS encryption
			$mail->Port       = $arr['port'];						//TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
			$mail->CharSet    = 'utf-8';

			//Recipients
			$mail->setFrom($arr['from'][0], $arr['from'][1]);
			for($i=0;$i<sizeof($arr['to']);$i++) {
				if(is_string($arr['to'][$i])) {
					$mail->addAddress($arr['to'][$i]);               //Name is optional
				}
				else {
					$mail->addAddress($arr['to'][$i][0], $arr['to'][$i][1]);     //Add a recipient
				}
			}
			if(array_key_exists('reply',$arr)) {
				$mail->addReplyTo($arr['reply'][0], $arr['reply'][1]);
			}
			if(array_key_exists('cc',$arr) && is_array($arr['cc'])) {
				foreach($arr['cc'] as $cc) {
					$mail->addCC($cc[0], $cc[1]);
				}
			}
			if(array_key_exists('bcc',$arr) && is_array($arr['bcc'])) {
				foreach($arr['bcc'] as $bcc) {
					$mail->addBCC($bcc[0], $bcc[1]);
				}
			}

			// 첨부파일
			if(array_key_exists('file',$arr) && is_array($arr['file'])) {
				foreach($arr['file'] as $file) {
					if(is_string($file)) {
						$mail->addAttachment($file);         //Add attachments
					}
					elseif(is_array($file)) {
						$mail->addAttachment($file[0], $file[1]);    //Optional name
					}
				}
			}

			//Content
			$mail->isHTML(true);                                  //Set email format to HTML
			//$mail->Subject = mb_convert_encoding($arr['subject'], 'EUC-KR', 'UTF-8');
			//$mail->Body    = mb_convert_encoding($arr['body'], 'EUC-KR', 'UTF-8');
			$mail->Subject = $arr['subject'];
			$mail->Body    = $arr['body'];
			if(array_key_exists('text',$arr)) { // html 지원이 안되는 사용자를 위한 plain text 설정
				$mail->AltBody = $arr['text'];
			}
			$mail->send();
			return true;
		} 
		catch (Exception $e) {
			$msg = '메일 발송에 실패하였습니다 : '.$mail->ErrorInfo;
			return false;
		}
	}
}

/***************************************************************************
 * 관리자 여부 판단
 * -------------
 * 작성일 : 2015.03.29
 * 사용법 : is_admin(권한)
 * 반환  : 관리자 로그인 여부 및 관리자 모듈 여부 반환
 **************************************************************************/
function unescape($val) {
  return urldecode(preg_replace_callback('/%u([[:alnum:]]{4})/', 'unescapeEx', $val));
}
function unescapeEx($val){
  return iconv('UTF-16LE', 'UTF-8', chr(hexdec(substr($val[1], 2, 2))).chr(hexdec(substr($val[1],0,2))));
}


function get_file_list($path, $only_filename=true, $arr=array()){
    $dir = opendir($path);
    while($file = readdir($dir)){
        if($file == '.' || $file == '..'){
            continue;
        }else if(is_dir($path.'/'.$file)){
            //$arr = get_file_list($path.'/'.$file, $only_filename, $arr);
        }else{
			if($only_filename) $arr[] = $file;
            else $arr[] = $path.'/'.$file;
        }
    }
    closedir($dir);
    return $arr;
}

function get_file_name($path) {
	$arr = explode('/',$path);
	return $arr[sizeof($arr)-1];
}

function create_path($path,$has_file = false) {
	global $_CONFIG;
	$temp_path = explode($_CONFIG['SEPARATOR'],$path);
	$temp_path_root = '';
	if(strpos($path,$_CONFIG['PATH']['UPLOAD'])<0) $temp_path_root = $_CONFIG['PATH']['UPLOAD'];
	if($has_file) $num = sizeof($temp_path)-1;
	else $num = sizeof($temp_path);
	for($i=0;$i<$num;$i++) {
		$temp_path_root .= $temp_path[$i].$_CONFIG['SEPARATOR'];
		if(!is_dir($temp_path_root)) @mkdir($temp_path_root,0777);
		@chmod($temp_path_root,0777);
	}
}

function date_ago($from,$to=null,$days=0,$tail='전') {
	if(is_null($from)) $from = date('Y-m-d H:i:s');
	if(is_null($to)) $to = date('Y-m-d H:i:s');
	if($from != '') {
		$temp = @date_diff(date_create($from),date_create($to));
		if($days > 0) {
			if($days > $temp->d && $temp->m < 1 && $temp->y < 1) {
				if($temp->d > 0) $result = $temp->d.'일 ';
				elseif($temp->h > 0) $result = $temp->h.'시간 ';
				elseif($temp->i > 0) $result = $temp->i.'분 ';
				else $result = $temp->s.' 초';
				$result .= $tail;
			}
			else {
				$ampm = (date('a',strtotime($from))=='am')?'오전':'오후';
				$result = date('Y년 m월 d일 '.$ampm.' g시 i분',strtotime($from));
			}
		}
		elseif($days == -1) {
			$result = '';
			if($temp->y > 0) $result .= $temp->y.'년';
			if($temp->m > 0) $result .= $temp->m.'개월';
			if($temp->d > 0) $result .= $temp->d.'일';
			if($temp->h > 0) $result .= $temp->h.'시간';
		} else {
			$result = $temp->s.'초';
			if($temp->i > 0) $result = $temp->i.'분';
			if($temp->h > 0) $result = $temp->h.'시간';
			if($temp->d > 0) $result = $temp->d.'일';
			if($temp->m > 0) $result = $temp->m.'개월';
			if($temp->y > 0) $result = $temp->y.'년';
		}
	}

	return $result;
}


function xlstotime($time,$time_string = false){
	if(!is_numeric($time)) {
		return false;
	}
	else {
		$t = (intval($time)- 25569) * 86400-60*60*9;
		$t = round($t*10)/10;

		if($time_string == true) {
			$t = date('Y-m-d H:i:s',$t);
		}
		return $t;
	}
}

function strlen2($str) {
	return mb_strlen($str,"utf-8") + (strlen($str) - mb_strlen($str,"utf-8")) / 2;
}

function implode2($arr) {
	if(is_array($arr)) {
		for($i=0;$i<sizeof($arr);$i++) {
			$arr[$i] = '"'.$arr[$i].'"';
		}
		return implode(',',$arr);
	}
	else {
		return array($arr);
	}
}


function now($strototime = null) {
	if($strototime == null) {
		return date('Y-m-d H:i:s');
	}
	else {
		return date('Y-m-d H:i:s',strtotime($strtotime));
	}
}

/*==============================================================
	세션 로그인이 되어 있을 경우 활동 기록
  ==============================================================*/
function activity_log($path = null) {
	global $code,$msg,$_TABLE,$_CONFIG;
	
	$db = new db();
	if(isset($_SESSION[SESSION['HEAD'].'NO'])) {
		if(isset($_TABLE['LOG']) && $db->table_exist($_TABLE['LOG'])) {
			if($path == null) $path = $_SERVER['REQUEST_URI'];
			$app = '';
			if(sizeof($_CONFIG['M']) > 0) {
				$app = (PAGE_TYPE == '') ? $_CONFIG['M'][0] : $_CONFIG['M'][1];
			}
			try {
				$db->begin();
				$db->insert(
					$_TABLE['LOG'],
					array(
						'ACCOUNT_NO' => $_SESSION[SESSION['HEAD'].'NO'],
						'PAGE_TYPE' => PAGE_TYPE,
						'APP' => $app,
						'CODE' => $code,
						'RESPONSE' => json_encode(array(
								'msg' => $msg
							),JSON_UNESCAPED_UNICODE),
						//'URL' => (((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443) ? 'https' : 'http' ). '://'.$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'],
						'URL' => $_SERVER['REQUEST_METHOD'].' '.$_SERVER['REQUEST_URI'],
						'PATH' => $path,
						'FORMDATA' => json_encode(array(
								'post' => $_POST,
								'get' => $_GET,
								'files' => $_FILES
							),JSON_UNESCAPED_UNICODE),
						'IP' => $_SERVER['REMOTE_ADDR']
					)
				);
				$db->commit();
			}
			catch(Exception $e) {
				echo $e->getMessage();
				exit(0);
				return false;
			}
		}
	}
	$db->close();
	return true;
}


function bool($val) {
	return (is_string($val)) ? in_array(strtolower($val),array('y','true')) : $val;
}