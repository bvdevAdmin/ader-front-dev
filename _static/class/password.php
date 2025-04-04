<?php
/**
 *
 * Password Class
 * =============================
 * Author : 양한빈
 * Date : 2024-04-13 17:00
 * Version : 1.0
 * Describe : 비밀번호와 관련된 함수를 모음.
 * History : 2024-04-13 최초작성
 *
 */


class password {
	
	public $check_result = -1;
	public $check_result_str = '';
	
	public function __construct() {
	}
	
	public function check($pw) {

		$this->check_result = true;
		$this->check_result_str = '';

		$num = preg_match('/[0-9]/u', $pw);
		$eng = preg_match('/[a-z]/u', $pw);
		$spe = preg_match('/[\!\@\#\$\%\^\&\*]/u',$pw);

		if(strlen($pw) < 10 || strlen($pw) > 30) {
			$this->check_result = -1;
			$this->check_result_str = '비밀번호는 영문, 숫자, 특수문자를 혼합하여 최소 10자리 ~ 최대 30자리 이내로 입력해주세요.';
		}
		
		elseif(preg_match('/\s/u', $pw) == true) {
			$this->check_result = -1;
			$this->check_result_str = '비밀번호는 공백없이 입력해주세요.';
		}

		elseif( $num == 0 || $eng == 0 || $spe == 0) {
			$this->check_result = -1;
			$this->check_result_str = '영문, 숫자, 특수문자를 혼합하여 입력해주세요.';
		}

		return $this->check_result;
	}

	// 암호화
	public function encode($str) {
		return base64_encode(
			openssl_encrypt(
				$str, 
				'aes-256-cbc', 
				AES_PASSWORD, 
				OPENSSL_RAW_DATA, 
				AES_IV_128
			)
		);
	}

	// 복호화
	public function decode($str) {
		return openssl_decrypt(
			base64_decode($str), 
			'aes-256-cbc', 
			AES_PASSWORD, 
			OPENSSL_RAW_DATA, 
			AES_IV_128
		);
	}
}
