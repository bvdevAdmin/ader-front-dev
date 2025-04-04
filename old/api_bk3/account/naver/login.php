<?php
/*
 +=============================================================================
 | 
 | 회원 로그인
 | -------
 |
 | 최초 작성	: 박성혁
 | 최초 작성일	: 2022.11.30
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 |            
 | 
 +=============================================================================
*/
include_once("/var/www/www/api/common.php");
include_once("/var/www/www/api/account/common.php");

$country = null;
if (isset($_SERVER['HTTP_COUNTRY'])) {
	$country = $_SERVER['HTTP_COUNTRY'];
}

$member_ip = '0.0.0.0';
if (isset($_SERVER['REMOTE_ADDR'])) {
	$member_ip = $_SERVER['REMOTE_ADDR'];
}

if (!isset($id)) {
	$json_result['code'] = 401;
	$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_WRN_0004', array());
	
	echo json_encode($json_result);
	exit;
}

$cnt_member = $db->count("MEMBER_".$country, "NAVER_ACCOUNT_KEY = '".$id."'");

if ($cnt_member == 0) {
	$user_exist_cnt = $db->count("MEMBER_".$country,"TEL_MOBILE = '".$mobile."' ");
	if ($user_exist_cnt == 0) {
		$json_result['code'] = 301;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0013', array());
		
		echo json_encode($json_result);
		exit;
	} else {
		$db->update(
			"MEMBER_".$country,
			array(
				'NAVER_ACCOUNT_KEY'		=>$id
			),
			"TEL_MOBILE = '".$mobile."'"
		);
	}
}

$select_member_sql = "
	SELECT 
		MB.IDX					AS MEMBER_IDX,
		MB.COUNTRY				AS COUNTRY,
		MB.MEMBER_ID			AS MEMBER_ID,
		MB.MEMBER_PW			AS MEMBER_PW,
		MB.LEVEL_IDX			AS MEMBER_LEVEL,
		MB.MEMBER_NAME			AS MEMBER_NAME,
		MB.TEL_MOBILE			AS TEL_MOBILE,
		MB.MEMBER_STATUS		AS MEMBER_STATUS,
		MB.LOGIN_CNT			AS LOGIN_CNT,
		DATE_FORMAT(
			MB.MEMBER_BIRTH,
			'%Y%m%d'
		)						AS MEMBER_BIRTH_NUM,
		DATE_FORMAT(
			MB.MEMBER_BIRTH - INTERVAL VM.DATE_AGO_PARAM DAY,
			'%Y'
		)						AS AGO_YEAR_PARAM,
		DATE_FORMAT(
			MB.MEMBER_BIRTH - INTERVAL VM.DATE_AGO_PARAM DAY,
			'-%m-%d'
		)						AS AGO_DATE_PARAM,
		DATE_FORMAT(
			MB.MEMBER_BIRTH + INTERVAL VM.DATE_LATER_PARAM DAY,
			'%Y'
		)						AS LATER_YEAR_PARAM,
		DATE_FORMAT(
			MB.MEMBER_BIRTH + INTERVAL VM.DATE_LATER_PARAM DAY,
			'-%m-%d'
		)						AS LATER_DATE_PARAM,
		DATE_FORMAT(
			MB.MEMBER_BIRTH,
			'-%m-%d'
		)						AS MEMBER_BIRTH,
		DATE_FORMAT(
			NOW(),
			'%Y'
		)						AS NOW_YEAR,
		DATE_FORMAT(
			NOW(),
			'%m'
		)						AS NOW_MONTH,
		DATE_FORMAT(
			NOW(),
			'%Y-%m-%d'
		)						AS NOW,
		VM.DATE_AGO_PARAM		AS DATE_AGO_PARAM,
		VM.DATE_LATER_PARAM		AS DATE_LATER_PARAM
	FROM 
		MEMBER_" . $country . " MB
		LEFT JOIN
		(
			SELECT
				DATE_AGO_PARAM,
				DATE_LATER_PARAM
			FROM
				VOUCHER_MST
			WHERE
				COUNTRY = '" . $country . "' AND VOUCHER_TYPE = 'BR'
			LIMIT 1
		) AS VM ON
		1=1
	WHERE
		NAVER_ACCOUNT_KEY = '" . $id . "'
";

$db->query($select_member_sql);

foreach ($db->fetch() as $data) {
	$member_birth = $data['MEMBER_BIRTH'];
	$status = $data['MEMBER_STATUS'];

	if ($status == 'DRP') {
		$json_result['code'] = 305;
		$json_result['msg'] = getMsgToMsgCode($db, $country, 'MSG_B_ERR_0086', array());

		echo json_encode($json_result);
		exit;
	} else if ($status == 'SLP') {
		$db->update(
			"MEMBER_".$country,
			array(
				'SLEEP_OFF_DATE'	=>NOW(),
				'MEMBER_STATUS'		=>'NML'
			),
			'IDX = '.$data['MEMBER_IDX']
		)
	}

	//회원 상태 = '일반'
	$_SESSION['MEMBER_IDX'] = $data['MEMBER_IDX'];
	$_SESSION['COUNTRY'] = $data['COUNTRY'];
	$_SESSION['MEMBER_ID'] = $data['MEMBER_ID'];
	$_SESSION['LEVEL_IDX'] = $data['MEMBER_LEVEL'];
	$_SESSION['MEMBER_NAME'] = $data['MEMBER_NAME'];
	$_SESSION['TEL_MOBILE'] = $data['TEL_MOBILE'];
	$_SESSION['MEMBER_EMAIL'] = $data['MEMBER_ID'];
	$_SESSION['MEMBER_BIRTH'] = $data['MEMBER_BIRTH_NUM'];
	
	$db->update(
		"MEMBER_".$country,
		array(
			'IP'			=>$member_ip,
			'LOGIN_CNT'		=>intval($data['LOGIN_CNT'] + 1),
			'LOGIN_DATE'	=>NOW()
		),
		'IDX = '.$data['MEMBER_IDX']
	)

	if ($data['AGO_YEAR_PARAM'] != $data['LATER_YEAR_PARAM']) {
		if ($data['NOW_MONTH'] == '01') {
			$ago_year = intval($data['NOW_YEAR']) - 1;
			$later_year = $data['NOW_YEAR'];
		} else if ($data['NOW_MONTH'] == '12') {
			$ago_year = $data['NOW_YEAR'];
			$later_year = intval($data['NOW_YEAR']) + 1;
		} else {
			$ago_year = $data['NOW_YEAR'];
			$later_year = $data['NOW_YEAR'];
		}
	} else {
		$ago_year = $data['NOW_YEAR'];
		$later_year = $data['NOW_YEAR'];
	}

	$start_date_param = $ago_year . $data['AGO_DATE_PARAM'];
	$end_date_param = $later_year . $data['LATER_DATE_PARAM'];
	$now_param = strtotime(date("Y-m-d"));

	if (strtotime($start_date_param) < $now_param && strtotime($end_date_param) > $now_param) {
		brithVoucherIssue($db, $country, $data['MEMBER_IDX'], $start_date_param, $end_date_param);
	}

	if ($r_url != null) {
		$json_result['data'] = $r_url;
	}
}

?>