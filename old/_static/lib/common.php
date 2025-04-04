<?php
/*
 +=============================================================================
 | 
 | 공통 라이브러리
 | ----------
 |
 | 최초 작성	: 양한빈
 | 최초 작성일	: 2015.12.08
 | 최종 수정일	: 2021.01.13
 | 버전		: 2.0
 | 설명		: 
 | 
 +=============================================================================
*/

/*
function mb_str_split($string, $split_length = 1, $encoding = null) {

    if (null !== $string && !\is_scalar($string) && !(\is_object($string) && \method_exists($string, '__toString'))) {
        trigger_error('mb_str_split(): expects parameter 1 to be string, '.\gettype($string).' given', E_USER_WARNING);
        return null;
    }
    if (null !== $split_length && !\is_bool($split_length) && !\is_numeric($split_length)) {
        trigger_error('mb_str_split(): expects parameter 2 to be int, '.\gettype($split_length).' given', E_USER_WARNING);
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
*/

function getmicrotime() {
	list($usec, $sec) = explode(' ',microtime());
	return ((float)$usec + (float)$sec);
}

function order_number() {
	return intval((getmicrotime()-1600000000)*1000000);
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

function curl($url, $header = array(), $data = array(), $timeout = 60) {
	$header_org = array(
		'Accept: application/json',
		'user-agent:Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.139 Safari/537.36'
	);

	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	if(sizeof($data) > 0) {
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
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
	return $body;
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
		$filename = $files['name'][$i];				// 파일 이름 알아내기
		$file_tmp = $files['tmp_name'][$i];			// 파일 임시 저장 장소 알아내기
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

		$filename = str_replace(' ','_',strip_tags($filename));
		
		$filename_arr = explode('.',$filename);
		// 파일 이름을 타임 스탬프로 바꾸기
		if(!array_key_exists('original_name',$args) || $args['original_name'] == true) {
			$filename = $filename_arr[0].'_'.time().".".$filename_arr[1];
		} else {
			$filename = time().'.'.strtolower($file_info['extension']);
		}

		// 파일을 정해진 저장 디렉토리에 저장
		$filename_real = $filename;
		$res = move_uploaded_file($file_tmp,$path.$filename_real);
		
		$upload_file = array();
		
		//중 이미지생성
		if ($res == true) {
			array_push($upload_file,[
				"filename" => $filename,
				"img_size" => "org",
			]);
			
			$imgname = $path.$filename_real;
			
			$size = getimageSize($imgname);
			$width = $size[0];
			$height = $size[1];
			$imgtype = $size[2];
			
			if($imgtype==1)      $img_lrg = ImageCreateFromGif($imgname);
			else if($imgtype==2) $img_lrg = ImageCreateFromJpeg($imgname);
			else if($imgtype==3) $img_lrg = ImageCreateFromPng($imgname);
			
			$makesize_mdl = 600;
			$makesize_sml = 130;
			
			$filename_mdl = "";
			if ($width > $makesize_mdl || $height > $makesize_mdl) {				
				$filename_mdl = $filename_arr[0]."_mdl_".time().".".$filename_arr[1];
				
				if ($width >= $height) {
					$mdl_width = $makesize_mdl;
					$mdl_height = ($height*$makesize_mdl)/$width;
					
				} else if($width < $height) {
					$mdl_width = ($width * $makesize_mdl)/$height;
					$mdl_height = $makesize_mdl;
				}

				if ($imgtype==1) {
					$img_mdl = ImageCreate($mdl_width,$mdl_height); // GIF일경우
					$white = ImageColorAllocate($img_mdl, 255,255,255);
					
					imagefill($img_mdl,1,1,$white);
					ImageCopyResized($img_mdl,$img_lrg,0,0,0,0,$mdl_width,$mdl_height,$width,$height);
					$upload_result = imageGIF($img_mdl,$path.filename_mdl);
					
				} else if ($imgtype==2) {
					$img_mdl=ImageCreateTrueColor($mdl_width,$mdl_height); // JPG일경우
					$white = ImageColorAllocate($img_mdl, 255,255,255);
					
					imagefill($img_mdl,1,1,$white);
					imagecopyresampled($img_mdl,$img_lrg,0,0,0,0,$mdl_width,$mdl_height,$width,$height);
					$upload_result = imageJPEG($img_mdl,$path.$filename_mdl,90);
					
				} else {
					$img_mdl=ImageCreateTrueColor($mdl_width,$mdl_height); // PNG일경우
					imagesavealpha($img_mdl, true);
					$white = Imagecolorallocatealpha($img_mdl,0x00,0x00,0x00,127);
					
					imagefill($img_mdl,0,0,$white);
					imagecopyresampled($img_mdl,$img_lrg,0,0,0,0,$mdl_width,$mdl_height,$width,$height);
					$upload_result = imagePNG($img_mdl,$path.$filename_mdl);
				}
				
				if ($upload_result == true) {
					array_push($upload_file,[
						"filename" => $filename_mdl,
						"img_size" => "mdl",
					]);
				}

				ImageDestroy($img_mdl);
			}
			
			if ($width > $makesize_sml || $height > $makesize_sml) {				
				$filename_sml = $filename_arr[0]."_sml_".time().".".$filename_arr[1];
				
				if ($width >= $height) {
					$sml_width = $makesize_sml;
					$sml_height = ($height*$makesize_sml)/$width;
					
				} else if($width < $height) {
					$sml_width = ($width * $makesize_sml)/$height;
					$sml_height = $makesize_sml;
				}

				if ($imgtype==1) {
					$img_sml = ImageCreate($sml_width,$sml_height); // GIF일경우
					$white = ImageColorAllocate($img_sml, 255,255,255);
					
					imagefill($img_sml,1,1,$white);
					ImageCopyResized($img_sml,$img_lrg,0,0,0,0,$sml_width,$sml_height,$width,$height);
					$upload_result = imageGIF($img_sml,$path.$filename_sml);
					
				} else if ($imgtype==2) {
					$img_sml=ImageCreateTrueColor($sml_width,$sml_height); // JPG일경우
					$white = ImageColorAllocate($img_sml, 255,255,255);
					
					imagefill($img_sml,1,1,$white);
					imagecopyresampled($img_sml,$img_lrg,0,0,0,0,$sml_width,$sml_height,$width,$height);
					$upload_result = imageJPEG($img_sml,$path.$filename_sml,90);
					
				} else {
					$img_sml=ImageCreateTrueColor($sml_width,$sml_height); // PNG일경우
					imagesavealpha($img_sml, true);
					$white = Imagecolorallocatealpha($img_sml,0x00,0x00,0x00,127);
					
					imagefill($img_sml,0,0,$white);
					imagecopyresampled($img_sml,$img_lrg,0,0,0,0,$sml_width,$sml_height,$width,$height);
					$upload_result = imagePNG($img_sml,$path.$filename_sml);
				}
				
				if ($upload_result == true) {
					array_push($upload_file,[
						"filename" => $filename_sml,
						"img_size" => "sml",
					]);
				}

				ImageDestroy($img_sml);
			}
			
			ImageDestroy($img_lrg);
		}
		
		// 권한 설정
		@chmod($path.$filename_real,0777);
		
		$result = array();
		if($res) {
			if($args['original_name_return']) {
				array_push($result[$filename],$files['name'][$i]);
			}
			else {
				array_push($result,$upload_file);
			}
		}
		else {
			array_push($result,false);
		}
	}
	if(sizeof($result) == 1 && !$args['original_name_return']) return $result[0];
	else return $result;
}


/***************************************************************************
 * 파일 업로드
 * -----------
 * 작성일 : 2022.09.07
 * 사용법 : file_up_url(file_url,파일 이름,저장 위치)
 * 반환   : 업로드 파일명
 **************************************************************************/
function file_up_url($file_url,$file_name,$path) {
	global $_CONFIG;

	$ext = strtolower(pathinfo($file_url, PATHINFO_EXTENSION));

	// 실행 파일 업로드 불가
	if($ext != null && strpos($exp,'.php,.asp,.jsp,.aspx')) {
		throw new Exception('Can not upload file : Permition Denied');
		return false;
	}
	$filename_arr = explode('.',$file_name);
	// 파일 이름을 타임 스탬프로 바꾸기
	$file_name = $filename_arr[0].'_'.time().".".$filename_arr[1];
	
	// 파일을 정해진 저장 디렉토리에 저장
	$filename_org = $file_name;
	//$res = move_uploaded_file($file_url,$path.$filename_org);
	$fp = fopen($path.$filename_org,'w');
	
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $file_url);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	$contents = curl_exec($ch);
	curl_close($ch);
	
	fwrite($fp,$contents);// 가져올 외부이미지 주소
	fclose($fp);
	
	$upload_file = array();
	//print_r("[URL : ".$file_url."]");
	//print_r("[FILE : ".$filename_org."]");
	//print_r("[RES : ".$res."]");
	//중 이미지생성
	if (filesize($path.$filename_org) > 0) {
		array_push($upload_file,[
			"filename" => $filename_org,
			"img_size" => "org",
		]);
		
		$imgname = $path.$filename_org;
		
		$size = getimageSize($imgname);
		$width = $size[0];
		$height = $size[1];
		$imgtype = $size[2];
		
		if($imgtype==1)      $img_lrg = ImageCreateFromGif($imgname);
		else if($imgtype==2) $img_lrg = ImageCreateFromJpeg($imgname);
		else if($imgtype==3) $img_lrg = ImageCreateFromPng($imgname);
		
		$makesize_mdl = 600;
		$makesize_sml = 130;
		
		$filename_mdl = "";
		if ($width > $makesize_mdl || $height > $makesize_mdl) {				
			$filename_mdl = $filename_arr[0]."_mdl_".time().".".$filename_arr[1];
			
			if ($width >= $height) {
				$mdl_width = $makesize_mdl;
				$mdl_height = ($height*$makesize_mdl)/$width;
				
			} else if($width < $height) {
				$mdl_width = ($width * $makesize_mdl)/$height;
				$mdl_height = $makesize_mdl;
			}

			if ($imgtype==1) {
				$img_mdl = ImageCreate($mdl_width,$mdl_height); // GIF일경우
				$white = ImageColorAllocate($img_mdl, 255,255,255);
				
				imagefill($img_mdl,1,1,$white);
				ImageCopyResized($img_mdl,$img_lrg,0,0,0,0,$mdl_width,$mdl_height,$width,$height);
				$upload_result = imageGIF($img_mdl,$path.filename_mdl);
				
			} else if ($imgtype==2) {
				$img_mdl=ImageCreateTrueColor($mdl_width,$mdl_height); // JPG일경우
				$white = ImageColorAllocate($img_mdl, 255,255,255);
				
				imagefill($img_mdl,1,1,$white);
				imagecopyresampled($img_mdl,$img_lrg,0,0,0,0,$mdl_width,$mdl_height,$width,$height);
				$upload_result = imageJPEG($img_mdl,$path.$filename_mdl,90);
				
			} else {
				$img_mdl=ImageCreateTrueColor($mdl_width,$mdl_height); // PNG일경우
				imagesavealpha($img_mdl, true);
				$white = Imagecolorallocatealpha($img_mdl,0x00,0x00,0x00,127);
				
				imagefill($img_mdl,0,0,$white);
				imagecopyresampled($img_mdl,$img_lrg,0,0,0,0,$mdl_width,$mdl_height,$width,$height);
				$upload_result = imagePNG($img_mdl,$path.$filename_mdl);
			}
			
			if ($upload_result == true) {
				array_push($upload_file,[
					"filename" => $filename_mdl,
					"img_size" => "mdl",
				]);
			}

			ImageDestroy($img_mdl);
		}
		
		if ($width > $makesize_sml || $height > $makesize_sml) {				
			$filename_sml = $filename_arr[0]."_sml_".time().".".$filename_arr[1];
			
			if ($width >= $height) {
				$sml_width = $makesize_sml;
				$sml_height = ($height*$makesize_sml)/$width;
				
			} else if($width < $height) {
				$sml_width = ($width * $makesize_sml)/$height;
				$sml_height = $makesize_sml;
			}

			if ($imgtype==1) {
				$img_sml = ImageCreate($sml_width,$sml_height); // GIF일경우
				$white = ImageColorAllocate($img_sml, 255,255,255);
				
				imagefill($img_sml,1,1,$white);
				ImageCopyResized($img_sml,$img_lrg,0,0,0,0,$sml_width,$sml_height,$width,$height);
				$upload_result = imageGIF($img_sml,$path.$filename_sml);
				
			} else if ($imgtype==2) {
				$img_sml=ImageCreateTrueColor($sml_width,$sml_height); // JPG일경우
				$white = ImageColorAllocate($img_sml, 255,255,255);
				
				imagefill($img_sml,1,1,$white);
				imagecopyresampled($img_sml,$img_lrg,0,0,0,0,$sml_width,$sml_height,$width,$height);
				$upload_result = imageJPEG($img_sml,$path.$filename_sml,90);
				
			} else {
				$img_sml=ImageCreateTrueColor($sml_width,$sml_height); // PNG일경우
				imagesavealpha($img_sml, true);
				$white = Imagecolorallocatealpha($img_sml,0x00,0x00,0x00,127);
				
				imagefill($img_sml,0,0,$white);
				imagecopyresampled($img_sml,$img_lrg,0,0,0,0,$sml_width,$sml_height,$width,$height);
				$upload_result = imagePNG($img_sml,$path.$filename_sml);
			}
			
			if ($upload_result == true) {
				array_push($upload_file,[
					"filename" => $filename_sml,
					"img_size" => "sml",
				]);
			}

			ImageDestroy($img_sml);
		}
		
		ImageDestroy($img_lrg);
	}
	
	unlink($path.$filename_org);
	
	// 권한 설정
	@chmod($path.$filename_real,0777);
	
	$result = array();
	if($res) {
		if($args['original_name_return']) {
			array_push($result[$filename],$files['name'][$i]);
		}
		else {
			array_push($result,$upload_file);
		}
	}
	else {
		array_push($result,false);
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
	return preg_match('/^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}$/i',$email);
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


function mailform($org,$str) {
	global $_CONFIG;

	$result = $org;

	$result = str_replace('{{SITENAME}}',$_CONFIG['SITE_TITLE'],$result);
	$result = str_replace('{{SITEURL}}',$_CONFIG['URL'],$result);
	$result = str_replace('{{DATE}}',date('Y-m-d H:i:s'),$result);

	foreach($str as $key => $value) { 
		$str[$key] = xss_clean($value); 
		$result = str_replace('{{'.$key.'}}',$value,$result);
	} 

	return $result;
}


/***************************************************************************
 * 약관 불러오기
 * ----------
 * 작성일	 : 2015.03.29
 * 사용법	 : term(약관명)
 * 반환	: 약관 내용
 **************************************************************************/
function term($contents) {
	global $connect,$_TABLE,$_CONFIG;

	$data = db_get($_TABLE['SITE_TERM'],'CATEGORY="'.$contents.'" AND STATUS="Y"','CONTENTS');
	if($_CONFIG['MOBILEURL'] != '') {
		if(!(strpos($_SERVER['PHP_SELF'],$_CONFIG['MOBILEURL']) > -1)) {
			$data = str_replace('font-size',' --temp-font-size',$data);
			$data = str_replace('line-height',' --temp-line-height',$data);
		}
	}
	$data = str_replace('\\\'','\'',$data);

	return $data;
}



/***************************************************************************
 * 관리자 여부 판단
 * -------------
 * 작성일 : 2015.03.29
 * 사용법 : is_admin(권한)
 * 반환  : 관리자 로그인 여부 및 관리자 모듈 여부 반환
 **************************************************************************/
function memberinfo($id = '') {
	global $connect,$_TABLE;

	if($id == '') {
		if($_SESSION[SS_HEAD.'ID']) $id = $_SESSION[SS_HEAD.'ID'];
		else return false;
	}

	$data = db_get($_TABLE['MEMBER'],'ID="'.$id.'"');
	$data['ZIPCODE1'] = substr($data['ZIPCODE'],0,3);
	$data['ZIPCODE2'] = substr($data['ZIPCODE'],3,3);

	if(strpos($data['MOBILE'],'-') > -1) {
		$_tmp = explode("-",$data['MOBILE']);
		$data['MOBILE1'] = $_tmp[0];
		$data['MOBILE2'] = $_tmp[1];
		$data['MOBILE3'] = $_tmp[2];
	}
	else {
		if(strlen($data['MOBILE']) == 11) {	// 11자리일경우
			$data['MOBILE1'] = substr($data['MOBILE'],0,3);
			$data['MOBILE2'] = substr($data['MOBILE'],3,4);
			$data['MOBILE3'] = substr($data['MOBILE'],7,4);
		}
		else {
			$data['MOBILE1'] = substr($data['MOBILE'],0,3);
			$data['MOBILE2'] = substr($data['MOBILE'],3,3);
			$data['MOBILE3'] = substr($data['MOBILE'],6,4);
		}
	}

	if(strpos($data['TEL'],'-') > -1) {
		$_tmp = explode("-",$data['TEL']);
		$data['TEL1'] = $_tmp[0];
		$data['TEL2'] = $_tmp[1];
		$data['TEL3'] = $_tmp[2];
	}
	else {
		if(strlen($data['TEL']) == 11) {	// 11자리일경우
			$data['TEL1'] = substr($data['TEL'],0,3);
			$data['TEL2'] = substr($data['TEL'],3,4);
			$data['TEL3'] = substr($data['TEL'],7,4);
		}
		elseif(strlen($data['TEL']) == 10) {
			if(substr($data['TEL'],0,2) == '02') {
				$data['TEL1'] = substr($data['TEL'],0,2);
				$data['TEL2'] = substr($data['TEL'],2,4);
			}
			else {
				$data['TEL1'] = substr($data['TEL'],0,3);
				$data['TEL2'] = substr($data['TEL'],3,3);
			}
			$data['TEL3'] = substr($data['TEL'],6,4);
		}
		else {
			$data['TEL1'] = substr($data['TEL'],0,2);
			$data['TEL2'] = substr($data['TEL'],2,3);
			$data['TEL3'] = substr($data['TEL'],5,4);
		}
	}

	$_tmp = explode('-',$data['FAX']);
	$data['FAX1'] = $_tmp[0];
	$data['FAX2'] = $_tmp[1];
	$data['FAX3'] = $_tmp[2];

	$data['BIRTHDAY_Y'] = intval(substr($data['BIRTHDAY'],0,4));
	$data['BIRTHDAY_M'] = intval(substr($data['BIRTHDAY'],4,2));
	$data['BIRTHDAY_D'] = intval(substr($data['BIRTHDAY'],6,2));

	$data['RECEIVE'] = ($data['RECEIVE_TEL']=='Y'&&$data['RECEIVE_SMS']=='Y'&&$data['RECEIVE_EMAIL']=='Y')?true:false;
	$data['RECEIVE_TEL'] = ($data['RECEIVE_TEL']=='Y')?true:false;
	$data['RECEIVE_SMS'] = ($data['RECEIVE_SMS']=='Y')?true:false;
	$data['RECEIVE_EMAIL'] = ($data['RECEIVE_EMAIL']=='Y')?true:false;
	$data['CERTIFY'] = ($data['CERTIFY']=='Y')?true:false;

	if(strpos($data['ETC'],'||') > -1) {
		$data['ETC'] = explode('||',$data['ETC']);
	}

	return $data;
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

/***************************************************************************
 * 임시 비번 생성
 * -----------
 * 작성일 : 2015.03.29
 * 사용법 : create_temp_password(회원아이디,길이)
 * 반환  : 성공 true, 실패 false
 **************************************************************************/
function create_temp_password($id,$len) {
	// 임시비번 
	$pwd = PHPRandom::getString($len);
	$result = update_password($id,$pwd);

	return $result;
}


/***************************************************************************
 * 비밀번호 갱신
 * ----------
 * 작성일 : 2015.03.29
 * 사용법 : update_password(회원아이디,비밀번호)
 * 반환  : 성공 true, 실패 false
 **************************************************************************/
function update_password($id,$pwd) {
	global $_TABLE;

	$pw_until_date = date('Y-m-d H:i:s',strtotime('+3month'));	// 3달 후 비번 변경
	return db_update($_TABLE['MEMBER'],'PW_PREV=PW,PW="'.$pwd.'",PW_DATE="'.$pw_until_date.'"','ID="'.$id.'"');
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
?>