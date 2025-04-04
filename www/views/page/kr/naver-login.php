<?php

$db->begin_transaction();

try {
	if (isset($_GET['state']) && isset($_GET['code'])) {
		$state	= $_GET['state'];
		$code	= $_GET['code'];
		
		/* 1. NAVER - 사용자 토큰 발급 */
		$token_naver = getNAVER_token($state,$code);
		if (sizeof($token_naver) > 0) {
			/* 2. NAVER - 사용자 정보 조회 */
			$data_user = getUSER_naver($token_naver);
			if (sizeof($data_user['response']) > 0) {
				$account_key = $data_user['response']['id'];
	
				$cnt_key = $db->count("MEMBER","NAVER_ACCOUNT_KEY = ?",array($account_key));
				if ($cnt_key > 0) {
					$member = getMEMBER($db,"KEY",$account_key);

					$login_result = setMember_login($db,$account_key);
					if ($login_result != false) {
						putMember_login($db,$account_key);

						echo "
							<script>
								localStorage.setItem('lang','".$member['COUNTRY']."');
								location.href = '/".strtolower($member['COUNTRY'])."';
							</script>
						";
					} else {
						/* NAVER - 간편 로그인 실패 예외처리 */
						echo "
							<script>
								alert(
									'네이버 로그인처리중 오류가 발생했습니다.',
									function() {
										localStorage.setItem('lang','".$member['COUNTRY']."');
										location.href = '/".strtolower($member['COUNTRY'])."';
									}
								);
							</script>
						";
					}
				} else {
					$cnt_mail	= $db->count("MEMBER","MEMBER_ID = ?",array($data_user['response']['email']));
					$cnt_tel	= 0;

					$phone_number = null;
					if (isset($data_user['response']['mobile'])) {
						$phone_number = $data_user['response']['mobile'];

						if ($phone_number != null) {
							$cnt_tel = $db->count("MEMBER","TEL_MOBILE = ?",array($data_user['response']['mobile']));
						}
					}

					if ($cnt_mail > 0) {
						$member = getMEMBER($db,"MID",$data_user['response']['email']);

						$db->update(
							"MEMBER",
							array(
								'NAVER_ACCOUNT_KEY'		=>$account_key
							),
							"MEMBER_ID = ?",
							array($data_user['response']['email'])
						);

						$login_result = setMember_login($db,$account_key);
						if ($login_result != false) {
							putMember_login($db,$account_key);

							echo "
								<script>
									localStorage.setItem('lang','".$member['COUNTRY']."');
									location.href = '/".strtolower($member['COUNTRY'])."';
								</script>
							";
						} else {
							/* NAVER - 간편 로그인 실패 예외처리 */
							echo "
								<script>
									alert(
										'네이버 로그인처리중 오류가 발생했습니다.',
										function() {
											localStorage.setItem('lang','".$member['COUNTRY']."');
											location.href = '/".strtolower($member['COUNTRY'])."';
										}
									);
								</script>
							";
						}
					} else if ($cnt_tel > 0) {
						$member = getMEMBER($db,"TEL",$phone_number);

						$db->update(
							"MEMBER",
							array(
								'NAVER_ACCOUNT_KEY'		=>$account_key
							),
							"TEL_MOBILE = ?",
							array($phone_number)
						);

						$login_result = setMember_login($db,$account_key);
						if ($login_result != false) {
							putMember_login($db,$account_key);

							echo "
								<script>
									localStorage.setItem('lang','".$member['COUNTRY']."');
									location.href = '/".strtolower($member['COUNTRY'])."';
								</script>
							";
						} else {
							/* NAVER - 간편 로그인 실패 예외처리 */
							echo "
								<script>
									alert(
										'네이버 로그인처리중 오류가 발생했습니다.',
										function() {
											localStorage.setItem('lang','".$member['COUNTRY']."');
											location.href = '/".strtolower($member['COUNTRY'])."';
										}
									);
								</script>
							";
						}
					} else {
						$join_result = setMember_join($db,$data_user['response']);
						if ($join_result != false) {
							echo "
								<script>
									localStorage.setItem('lang','KR');
									location.href = '/".strtolower($member['COUNTRY'])."/login-auth';
								</script>
							";
						} else {
							/* NAVER - 간편가입 실패 예외처리 */
							echo "
								<script>
									alert(
										'네이버 가입처리중 오류가 발생했습니다.',
										function() {
											localStorage.setItem('lang','KR');
											location.href = '/".strtolower($member['COUNTRY'])."';
										}
									);
								</script>
							";
						}
					}
				}
			} else {
				/* NAVER - 사용자 정보 조회 실패 예외처리 */
				echo "
					<script>
						alert(
							'네이버 계정정보 조회처리중 오류가 발생했습니다.',
							function() {
								localStorage.setItem('lang','KR');
								location.href = '/".strtolower($member['COUNTRY'])."';
							}
						);
					</script>
				";
			}

			$db->commit();
		} else {
			/* NAVER - 사용자 토큰 발급 예외처리 */
			echo "
				<script>
					alert(
						'네이버 토큰 발급처리중 오류가 발생했습니다.',
						function() {
							localStorage.setItem('lang','KR');
							location.href = '/".strtolower($member['COUNTRY'])."';
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
				'네이버 로그인 처리중 오류가 발생했습니다.',
				function() {
					localStorage.setItem('lang','KR');
					location.href = '/".strtolower($member['COUNTRY'])."';
				}
			)
		</script>
	";
}

function getNAVER_token($state,$code) {
	$token_naver = null;
	
	$oauth_url		= "https://nid.naver.com/oauth2.0/";
	$client_id		= "k4gK4Eon6TG0GwnX5zhM";
	$client_secret	= "AyIIX91Li3";
	$redirect_uri	= urlencode("https://stg.adererror.com/kr/naver-login");
	
	$curl = curl_init();
	
	curl_setopt_array($curl, array(
		CURLOPT_URL => $oauth_url."token?grant_type=authorization_code&client_id=".$client_id."&client_secret=".$client_secret."&redirect_uri=".$redirect_uri."&code=".$code."&state=".$state,
		CURLOPT_POST => false,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_TIMEOUT => 30
	));

	$response	= curl_exec($curl);
	$err		= curl_error($curl);
	
	if (!$err) {
		$token_naver = json_decode($response,true);
	}
	
	curl_close($curl);
	
	return $token_naver;
}

function getUSER_naver($token) {
	$data_user = null;
	
	$curl = curl_init();
	
	curl_setopt_array($curl, array(
		CURLOPT_URL				=> 'https://openapi.naver.com/v1/nid/me',
		CURLOPT_POST			=> true,
		CURLOPT_RETURNTRANSFER	=> true,
		CURLOPT_TIMEOUT			=> 30,
		CURLOPT_HTTPHEADER		=> [
			"Authorization: Bearer ".$token['access_token']
		]
	));
	
	$response = curl_exec($curl);
	$err = curl_error($curl);
	
	if (!$err) {
		$data_user = json_decode($response,true);
	}
	
	curl_close($curl);

	return $data_user;
}

function setMember_login($db,$account_key) {
	$login_result = false;

	$select_member_sql = "
		SELECT
			MB.IDX				AS MEMBER_IDX,
			MB.MEMBER_ID		AS MEMBER_ID,
			MB.LEVEL_IDX		AS MEMBER_LEVEL,
			MB.MEMBER_NAME		AS MEMBER_NAME,
			MB.TEL_MOBILE		AS TEL_MOBILE,
			MB.MEMBER_ID		AS MEMBER_ID,
			MB.MEMBER_BIRTH		AS MEMBER_BIRTH,
			MB.AUTH_FLG			AS AUTH_FLG
		FROM
			MEMBER MB
		WHERE
			MB.NAVER_ACCOUNT_KEY = ?
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
			NAVER_ACCOUNT_KEY = ?
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

function setMember_join($db,$param) {
	$join_result = false;

	$db->insert(
		"MEMBER",
		array(
			'COUNTRY'				=>"KR",
			'MEMBER_STATUS'			=>"NML",
			'MEMBER_ID'				=>$param['email'],
			'MEMBER_NAME'			=>$param['name'],
			'TEL_MOBILE'			=>$param['mobile'],
			'MEMBER_BIRTH'			=>$param['birthyear']."-".$param['birthday'],

			'NAVER_ACCOUNT_KEY'		=>$param['id']
		)
	);

	$member_idx = $db->last_id();
	if (isset($member_idx) && $member_idx > 0) {
		$join_result = true;

		if (isset($param['gender'])) {
			$db->insert(
				"MEMBER_CUSTOM",
				array(
					'COUNTRY'			=>"KR",
					'MEMBER_IDX'		=>$member_idx,
					'MEMBER_GENDER'		=>$param['gender']
				)
			);
		}

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
		$_SESSION['TEL_MOBILE']		= $param['mobile'];
		$_SESSION['MEMBER_EMAIL']	= $param['email'];
		$_SESSION['MEMBER_BIRTH']	= date('Ymd',strtotime($param['birthyear']."-".$param['birthday']));
		$_SESSION['AUTH_FLG']		= false;
	}

	return $join_result;
}

function getMEMBER($db,$type,$param) {
	$result = null;

	switch ($type) {
		case "KEY" :
			$where = "NAVER_ACCOUNT_KEY = ?";
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