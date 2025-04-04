<?php

$db->begin_transaction();

try {
	if (isset($_GET['code']) && $_GET['code'] != null) {
		$code = $_GET['code'];
		
		/* 1. KAKAO - 사용자 토큰 발급 */
		$token_kakao = getKAKAO_token($code);
		if ($token_kakao['access_token']) {
			/* 2. KAKAO - 사용자 정보 조회 */
			$data_user = getUSER_kakao($token_kakao['access_token']);
			if (sizeof($data_user) > 0 && sizeof($data_user['kakao_account']) > 0) {
				$account_key = md5($data_user['id']);

				$cnt_key = $db->count("MEMBER","KAKAO_ACCOUNT_KEY = ?",array($account_key));
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
					$cnt_mail	= $db->count("MEMBER","MEMBER_ID = ?",array($data_user['kakao_account']['email']));
					$cnt_tel	= 0;
					
					$phone_number = null;
					if (isset($data_user['kakao_account']['phone_number'])) {
						if (strpos($data_user['kakao_account']['phone_number'],'+82') === 0) {
							$phone_number = preg_replace('/^\+82\s?10/','010',$data_user['kakao_account']['phone_number']);
						} else {
							$phone_number = $data_user['kakao_account']['phone_number'];
						}

						if ($phone_number != null) {
							$cnt_tel	= $db->count("MEMBER","TEL_MOBILE = ?",array($phone_number));
						}
					}
					
					if ($cnt_mail > 0) {
						$member = getMEMBER($db,"MID",$data_user['kakao_account']['email']);

						$db->update(
							"MEMBER",
							array(
								'KAKAO_ACCOUNT_KEY'		=>$account_key
							),
							"MEMBER_ID = ?",
							array($data_user['kakao_account']['email'])
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
							/* 카카오 - 간편 로그인 실패 예외처리 */
							echo "
								<script>
									alert(
										'카카오 로그인처리중 오류가 발생했습니다.',
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
								'KAKAO_ACCOUNT_KEY'		=>$account_key
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
							/* 카카오 - 간편 로그인 실패 예외처리 */
							echo "
								<script>
									alert(
										'카카오 로그인처리중 오류가 발생했습니다.',
										function() {
											localStorage.setItem('lang','".$member['COUNTRY']."');
											location.href = '/".strtolower($member['COUNTRY'])."';
										}
									);
								</script>
							";
						}
					} else {
						$join_result = setMember_join($db,$account_key,$data_user['kakao_account']);
						if ($join_result != false) {
							echo "
								<script>
									localStorage.setItem('lang','EN');
									location.href = '/".strtolower($member['COUNTRY'])."/login-auth';
								</script>
							";
						} else {
							/* KAKAO - 간편가입 실패 예외처리 */
							echo "
								<script>
									alert(
										'카카오 가입처리중 오류가 발생했습니다.',
										function() {
											localStorage.setItem('lang','EN');
											location.href = '/".strtolower($member['COUNTRY'])."';
										}
									);
								</script>
							";
						}
					}
				}
			} else {
				/* KAKAO - 사용자 정보 조회 실패 예외처리 */
				echo "
					<script>
						alert(
							'카카오 계정정보 조회처리중 오류가 발생했습니다.',
							function() {
								localStorage.setItem('lang','EN');
								location.href = '/".strtolower($member['COUNTRY'])."';
							}
						);
					</script>
				";
			}

			$db->commit();
		} else {
			/* KAKAO - 사용자 토큰 발급 예외처리 */
			echo "
				<script>
					alert(
						'카카오 토큰 발급처리중 오류가 발생했습니다.',
						function() {
							localStorage.setItem('lang','EN');
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
					localStorage.setItem('lang','EN');
					location.href = '/".strtolower($member['COUNTRY'])."';
				}
			)
		</script>
	";
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
			'redirect_url'	=>"https://stg.adererror.com/en/kakao-login",
			'code'			=>$code
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

function getUSER_kakao($token) {
	$data_user = null;
	
	$curl = curl_init();
	
	curl_setopt_array($curl, array(
		CURLOPT_URL				=> 'https://kapi.kakao.com/v2/user/me',
		CURLOPT_RETURNTRANSFER	=> true,
		CURLOPT_TIMEOUT			=> 30,
		CURLOPT_HTTPHEADER		=> [
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
			MB.KAKAO_ACCOUNT_KEY = ?
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
			KAKAO_ACCOUNT_KEY = ?
	";

	$db->query($update_member_sql,$_SERVER['REMOTE_ADDR'],$account_key);
	
	$db->insert(
		"MEMBER_LOGIN_HISTORY",
		array(
			'COUNTRY'		=>"EN",
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

	$phone_number = null;
	if (isset($param['phone_number']) && strpos($param['phone_number'], '+82') === 0) {
        $phone_number = preg_replace('/^\+82\s?10/', '010', $param['phone_number']);
    }

	$db->insert(
		"MEMBER",
		array(
			'COUNTRY'				=>"EN",
			'MEMBER_STATUS'			=>"NML",
			'MEMBER_ID'				=>$param['email'],
			'MEMBER_NAME'			=>$param['name'],
			'TEL_MOBILE'			=>$phone_number,

			'KAKAO_ACCOUNT_KEY'		=>$account_key,
			'AUTH_FLG'				=>1
		)
	);
	
	$member_idx = $db->last_id();
	if (isset($member_idx) && $member_idx > 0) {
		$join_result = true;

		if (isset($param['gender'])) {
			$gender = "F";
			if ($param['gender'] == "male") {
				$gender = "M";
			}

			$db->insert(
				"MEMBER_CUSTOM",
				array(
					'COUNTRY'			=>"EN",
					'MEMBER_IDX'		=>$member_idx,
					'MEMBER_GENDER'		=>$gender
				)
			);
		}

        $db->insert(
            "MILEAGE_INFO",
            array(
                'COUNTRY'				=>"EN",
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
		$_SESSION['AUTH_FLG']		= false;
	}

	return $join_result;
}

function getMEMBER($db,$type,$param) {
	$result = null;

	switch ($type) {
		case "KEY" :
			$where = "KAKAO_ACCOUNT_KEY = ?";
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