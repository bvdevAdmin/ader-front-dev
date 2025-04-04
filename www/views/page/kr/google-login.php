<?php

$db->begin_transaction();

try {
	if (isset($_GET['code'])) {
		$code = $_GET['code'];

		/* 1. GOOGLE - 사용자 토큰 발급 */
		$token_google = getTOKEN_google($code);
		if (sizeof($token_google) > 0) {
			/* 2. GOOGLE - 사용자 정보 조회 */
			if ($token_google['access_token']) {
				$data_user = getUSER_google($token_google['access_token']);
				if (sizeof($data_user) > 0 && isset($data_user['id'])) {
					$account_key = md5($data_user['id']);			

					$cnt_key = $db->count("MEMBER","GOOGLE_ACCOUNT_KEY = ?",array($account_key));
					if ($cnt_key > 0) {
						$member = getMEMBER($db,"KEY",$account_key);

						$login_result = setMember_login($db,$account_key);
						if ($login_result != false) {
							putMember_login($db,$account_key);

							echo "
								<script>
									localStorage.setItem('lang','KR');
									location.href = '/kr';
								</script>
							";
						} else {
							/* GOOGLE - 간편 로그인 실패 예외처리 */
							echo "
								<script>
									alert(
										'구글 로그인처리중 오류가 발생했습니다.',
										function() {
											localStorage.setItem('lang','KR');
											location.href = '/kr';
										}
									);
								</script>
							";
						}
					} else {
						$cnt_mail = $db->count("MEMBER","MEMBER_ID = ?",array($data_user['email']));
						if ($cnt_mail > 0) {
							$member = getMEMBER($db,"MID",$data_user['email']);

							$db->update(
								"MEMBER",
								array(
									'GOOGLE_ACCOUNT_KEY'		=>$account_key
								),
								"MEMBER_ID = ?",
								array($data_user['email'])
							);

							$login_result = setMember_login($db,$account_key);
							if ($login_result != false) {
								putMember_login($db,$account_key);

								echo "
									<script>
										localStorage.setItem('lang','KR');
										location.href = '/kr';
									</script>
								";
							} else {
								/* GOOGLE - 간편 로그인 실패 예외처리 */
								echo "
									<script>
										alert(
											'구글 로그인처리중 오류가 발생했습니다.',
											function() {
												localStorage.setItem('lang','KR');
												location.href = '/kr';
											}
										);
									</script>
								";
							}
						} else {
							$join_result = setMember_join($db,$account_key,$data_user);
							if ($join_result != false) {
								echo "
									<script>
										localStorage.setItem('lang','KR');
										location.href = '/kr';
									</script>
								";
							} else {
								/* GOOGLE - 간편가입 실패 예외처리 */
								echo "
									<script>
										alert(
											'구글 가입처리중 오류가 발생했습니다.',
											function() {
												localStorage.setItem('lang','KR');
												location.href = '/kr';
											}
										);
									</script>
								";
							}
						}
					}
				} else {
					/* GOOGLE - 사용자 정보 조회 실패 예외처리 */
					echo "
						<script>
							alert(
								'구글 계정정보 조회처리중 오류가 발생했습니다.',
								function() {
									localStorage.setItem('lang','KR');
									location.href = '/kr';
								}
							);
						</script>
					";
				}

				$db->commit();
			} else {
				/* GOOGLE - 사용자 토큰 발급 예외처리 */
				echo "
					<script>
						alert(
							'구글 토큰 발급처리중 오류가 발생했습니다.',
							function() {
								localStorage.setItem('lang','KR');
								location.href = '/kr';
							}
						);
					</script>
				";
			}
		} else {
			/* GOOGLE - 사용자 토큰 발급 예외처리 */
			echo "
				<script>
					alert(
						'구글 토큰 발급처리중 오류가 발생했습니다.',
						function() {
							localStorage.setItem('lang','KR');
							location.href = '/kr';
						}
					);
				</script>
			";
		}
	}
} catch (mysqli_sql_exception $e) {
	$db->rollback();
	
	echo "
		<script>
			alert(
				'구글 로그인 처리중 오류가 발생했습니다.',
				function() {
					localStorage.setItem('lang','KR');
					location.href = '/kr';
				}
			)
		</script>
	";
}

function getTOKEN_google($code) {
	$token_google = null;
	
	$client_id		= "999124937022-qgkvknpulb77vgvdntunoqsj90ka2jga.apps.googleusercontent.com";
	$client_secret	= "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";
	$redirect_uri	= "https://stg.adererror.com/kr/google-login";
	
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
			'redirect_uri'	=>$redirect_uri,
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
		CURLOPT_URL				=>'https://www.googleapis.com/oauth2/v2/userinfo',
		CURLOPT_RETURNTRANSFER	=>true,
		CURLOPT_SSL_VERIFYPEER	=>false,
		CURLOPT_TIMEOUT			=>30,
		CURLOPT_HTTPHEADER => [
			"Authorization: Bearer ".$token
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

function setMember_login($db,$account_key) {
	$login_result = false;

	$select_member_sql = "
		SELECT
			MB.IDX					AS MEMBER_IDX,
			MB.MEMBER_ID			AS MEMBER_ID,
			MB.LEVEL_IDX			AS MEMBER_LEVEL,
			MB.MEMBER_NAME			AS MEMBER_NAME,
			MB.TEL_MOBILE			AS TEL_MOBILE,
			MB.MEMBER_ID			AS MEMBER_ID,
			MB.MEMBER_BIRTH			AS MEMBER_BIRTH,
			MB.AUTH_FLG				AS AUTH_FLG
		FROM
			MEMBER MB
		WHERE
			MB.GOOGLE_ACCOUNT_KEY = ?
	";

	$db->query($select_member_sql,array($account_key));

	foreach($db->fetch() as $data) {
		$_SESSION['MEMBER_IDX']		= $data['MEMBER_IDX'];
		$_SESSION['MEMBER_ID']		= $data['MEMBER_ID'];
		$_SESSION['LEVEL_IDX']		= $data['MEMBER_LEVEL'];
		$_SESSION['MEMBER_NAME']	= $data['MEMBER_NAME'];
		$_SESSION['TEL_MOBILE']		= $data['TEL_MOBILE'];
		$_SESSION['MEMBER_EMAIL']	= $data['MEMBER_ID'];
		$_SESSION['MEMBER_BIRTH']	= date('Ymd',strtotime($data['MEMBER_BIRTH']));
		$_SESSION['AUTH_FLG']		= $data['AUTH_FLG'];
	}

	if (isset($_SESSION['MEMBER_IDX']) && $_SESSION['MEMBER_IDX'] > 0) {
		$login_result = true;
	}

	return $login_result;
}

function putMember_login($db,$account_key) {
	$update_member_sql = "
		UPDATE
			MEMBER
		SET
			LOGIN_IP = ?,
			LOGIN_CNT = LOGIN_CNT + 1,
			LOGIN_DATE = NOW()
		WHERE
			GOOGLE_ACCOUNT_KEY = ?
	";

	$db->query($update_member_sql,$_SERVER['REMOTE_ADDR'],$account_key);
	
	$db->insert(
		"MEMBER_LOGIN_HISTORY",
		array(
			'COUNTRY'		=>"KR",
			'MEMBER_IDX'	=>$_SESSION['MEMBER_IDX'],
			'MEMBER_IP'		=>$_SERVER['REMOTE_ADDR'],
			'MEMBER_ID'		=>$_SESSION['MEMBER_ID'],
			'MEMBER_NAME'	=>$_SESSION['MEMBER_NAME'],
			'CREATE_DATE'	=>NOW()
		)
	);
}

function setMember_join($db,$account_key,$param) {
	$join_result = false;

	$db->insert(
		"MEMBER",
		array(
			'COUNTRY'				=>"KR",
			'MEMBER_STATUS'			=>"NML",
			'MEMBER_ID'				=>$param['email'],
			'MEMBER_NAME'			=>$param['name'],

			'GOOGLE_ACCOUNT_KEY'	=>$account_key,
			'AUTH_FLG'				=>0
		)
	);
	
	$member_idx = $db->last_id();
	if (isset($member_idx) && $member_idx > 0) {
		$join_result = true;

        $db->insert(
            "MILEAGE_INFO",
            array(
                'COUNTRY'				=>"KR",
                'MEMBER_IDX'			=>$member_idx,
                'ID'					=>$param['email'],
                'MILEAGE_CODE'			=>'NEW',
                'MILEAGE_UNUSABLE'		=>0,
                'MILEAGE_USABLE_INC'	=>5000,
                'MILEAGE_USABLE_DEC'	=>0,
                'MILEAGE_BALANCE'		=>5000,
                'CREATER'				=>'system',
                'UPDATER'				=>'system',
            )
        );

		$_SESSION['MEMBER_IDX']		= $member_idx;
		$_SESSION['MEMBER_ID']		= $param['email'];
		$_SESSION['LEVEL_IDX']		= 1;
		$_SESSION['MEMBER_NAME']	= $param['name'];
		$_SESSION['MEMBER_EMAIL']	= $param['email'];
		$_SESSION['AUTH_FLG']		= false;
	}

	return $join_result;
}

function getMEMBER($db,$type,$param) {
	$result = null;

	switch ($type) {
		case "KEY" :
			$where = "GOOGLE_ACCOUNT_KEY = ?";
			break;
		
		case "TEL" :
			$where = "TEL_MOBILE = ?";
			break;
		
		case "MID" :
			$where = "MEMBER_ID = ?";
			break;
	}

	$member = $db->get("MEMBER",$where,array($param));
	if (sizeof($member) > 0) {
		$result = $member[0];
	}

	return $result;
}

?>

<main>
    <div style="height:100vh"></div>
</main>
