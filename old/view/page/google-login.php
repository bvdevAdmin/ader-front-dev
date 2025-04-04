<script src="/scripts/member/login.js"></script>
<script>
<?php 
if (isset($_GET['code'])) {
	$code = $_GET['code'];
	
	$token_google = getTOKEN_google($code);
	
	//print_r(" [ TOKEN GOOGLE ] ");
	//print_r($token_google);
	
	if ($token_google != null) {
		$data_user = getUSER_google($token_google);
		
		//print_r(" [ DATA USER ] ");
		//print_r($data_user);
		
		if ($data_user != null) {
			/*
			param_login(
				'account_key'	=>$data_user['id'],
				'member_id'		=>$data_user['email'],
				'member_name'	=>$data_user['name'],
				'gender'		=>null,
				'tel_mobile'	=>null,
				'member_birth'	=>null
			);
			*/
			
			if ($data_user != null) {
				echo "
					memberLogin_sns('GOOGLE','".$data_user['id']."','".$data_user['email']."','".$data_user['name']."',null,null,null);
				";
			}
		}
	}
} else {
	echo "
		makeMsgNoti(getLanguage(),'MSG_F_ERR_0074',null);
		
		let btn_close = document.querySelector(`#notimodal-modal .close-btn`);
		if (btn_close != null) {
			btn_close.addEventListener('click',function() {
				location.href = '/login';
			});
		}
	";
}

function getTOKEN_google($code) {
	$token_google = null;
	
	$client_id		= "824115093434-0mj6bh3el4ndur8u9cglu3u0sojhub9f.apps.googleusercontent.com";
	$client_secret	= "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";
	$redirect_url	= "https://dev.adererror.com/google/login";
	
	$curl = curl_init();
	
	curl_setopt_array($curl, array(
		CURLOPT_URL => 'https://www.googleapis.com/oauth2/v4/token',
		CURLOPT_POST => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 30,
		CURLOPT_POSTFIELDS => http_build_query([
			'code'			=>$code,
			'client_id'		=>$client_id,
			'client_secret'	=>$client_secret,
			'redirect_uri'	=>$redirect_url,
			'grant_type'	=>'authorization_code',
		]),
	));

	$response = curl_exec($curl);
	$err = curl_error($curl);

	if (!$err) {
		$token_google = json_decode($response,true);
	}
	
	return $token_google;
}

function getUSER_google($token) {
	$data_user = null;
	
	$curl = curl_init();
	
	curl_setopt_array($curl, array(
		CURLOPT_URL				=>'https://www.googleapis.com/oauth2/v2/userinfo?fields=name,email,gender,id',
		CURLOPT_RETURNTRANSFER	=>true,
		CURLOPT_SSL_VERIFYPEER	=>false,
		CURLOPT_TIMEOUT			=>30,
		CURLOPT_HTTPHEADER => [
			"Authorization: Bearer ".$token['access_token']
		]
	));
	
	$response = curl_exec($curl);
	$err = curl_error($curl);

	if (!$err) {
		$data_user = json_decode($response,true);
	}
	
	return $data_user;
}

?>
</script>
<main>
    <div style="height:100vh"></div>
</main>
