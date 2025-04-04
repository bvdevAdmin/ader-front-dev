<script src="/scripts/member/login.js"></script>

<?php
$code = null;
if (isset($_GET['code'])) {
	$code = $_GET['code'];
	
	/* 1. KAKAO - 사용자 토큰 발급 */
	$token_json = getKAKAO_token($code);
	if ($token_json['access_token']) {
		/* 2. KAKAO - 사용자 정보 조회 */
		$data_user = getUSER_kakao($token_json['access_token']);
		if (!empty($data_user)) {
			$kakao_account = $data_user['kakao_account'];
			if (!empty($kakao_account)) {
				echo "
					<script>
						memberLogin_sns('KAKAO','".$data_user['id']."','".$kakao_account['email']."','".$kakao_account['name']."','N','".$kakao_account['phone_number']."',null);
					</script>
				";
			} else {
				/* KAKAO - 사용자 정보 조회 실패 예외처리 */
				echo "
					<script>
						makeMsgNoti(getLanguage(), 'MSG_F_ERR_0074', null);
						
						let btn_close = document.querySelector(`#notimodal-modal .close-btn`);
						if (btn_close != null) {
							btn_close.addEventListener('click', () => { location.href='/login' });
						}
					</script>
				";
			}
		} else {
			/* KAKAO - 사용자 토큰 발급 예외처리 */
			echo "
				<script>
					makeMsgNoti(getLanguage(), 'MSG_F_ERR_0074', null);
					
					let btn_close = document.querySelector(`#notimodal-modal .close-btn`);
					if (btn_close != null) {
						btn_close.addEventListener('click', () => { location.href='/login' });
					}
				</script>
			";
		}
	} else {
		/* KAKAO - 사용자 토큰 발급 예외처리 */
		echo "
			<script>
				makeMsgNoti(getLanguage(), 'MSG_F_ERR_0074', null);
				
				let btn_close = document.querySelector(`#notimodal-modal .close-btn`);
				if (btn_close != null) {
					btn_close.addEventListener('click', () => { location.href='/login' });
				}
			</script>
		";
	}
}

function getKAKAO_token($code) {
	$token_kakao = null;
	
	$curl = curl_init();
	
	curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://kauth.kakao.com/oauth/token',
		CURLOPT_POST => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_POSTFIELDS => http_build_query([
			'grant_type'	=>"authorization_code",
			'client_id'		=>"b43df682b08d3270e40a79b5c51506b5",
			'redirect_url'	=>"https://dev.adererror.com/kakao/login",
			'code'			=>$code,
			'client_secret'	=>"pf9SQRNtAt5sjeO8VHYcryaf3z2IXLw1"
		]),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);
	
	curl_close($curl);
	
	if (!$err) {
		$token_kakao = json_decode($response,true);
	}
	
	return $token_kakao;
}

function getUSER_kakao($access_token) {
	$data_user = null;
	
	$curl = curl_init();
	
	curl_setopt_array($curl, array(
		CURLOPT_URL				=> 'https://kapi.kakao.com/v2/user/me',
		CURLOPT_RETURNTRANSFER	=> true,
		CURLOPT_TIMEOUT			=> 30,
		CURLOPT_HTTPHEADER		=> [
			"Authorization: Bearer ".$access_token
		]
	));
	
	$response = curl_exec($curl);
	$err = curl_error($curl);
	
	curl_close($curl);
	
	if (!$err) {
		$data_user = json_decode($response,true);
	}
	
	return $data_user;
}

?>

<main>
    <div style="height:100vh"></div>
</main>