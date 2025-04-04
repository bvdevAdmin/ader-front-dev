<?php

if(!isset($_SESSION['MEMBER_IDX'])) {
	$code = 360;
} else {
	$next_level = array(
		'KR'	=>array(
			1		=>1,
			2		=>500000,
			3		=>3000000,
			4		=>5000000,
			5		=>10000000
		),
		'EN'	=>array(
			1		=>1,
			2		=>500,
			3		=>3000,
			4		=>5000,
			5		=>10000
		)
	);
	
	$select_member_sql = "
		SELECT
			MB.MEMBER_ID			AS MEMBER_ID,
			MB.MEMBER_NAME			AS MEMBER_NAME,
			LV.TITLE				AS MEMBER_LEVEL,
			IFNULL(
				MB.TEL_MOBILE,''
			)						AS TEL_MOBILE,
			IFNULL(
				MB.MEMBER_BIRTH,''
			)						AS MEMBER_BIRTH,
			IFNULL(
				(
					SELECT
						S_MI.MILEAGE_BALANCE
					FROM
						MILEAGE_INFO S_MI
					WHERE
						S_MI.COUNTRY	= ? AND
						S_MI.MEMBER_IDX	= MB.IDX
					ORDER BY
						S_MI.IDX DESC
					LIMIT
						0,1
				),0
			)						AS MILEAGE_BALANCE,
			IFNULL(
				J_VI.CNT_VOUCHER,0
			)						AS CNT_VOUCHER,

			IFNULL(
				J_OI.PRICE_TOTAL,0
			)						AS PRICE_I,
			IFNULL(
				J_OC.PRICE_TOTAL,0
			)						AS PRICE_C,
			IFNULL(
				J_OF.PRICE_TOTAL,0
			)						AS PRICE_R,
			
			MB.RECEIVE_EMAIL_FLG	AS RECEIVE_EMAIL_FLG,
			MB.RECEIVE_TEL_FLG		AS RECEIVE_TEL_FLG,
			MB.RECEIVE_SMS_FLG		AS RECEIVE_SMS_FLG,
			MB.AUTH_FLG				AS AUTH_FLG
		FROM
			MEMBER MB
			
			LEFT JOIN MEMBER_LEVEL LV ON
			MB.LEVEL_IDX = LV.IDX
			
			LEFT JOIN (
				SELECT
					S_VI.MEMBER_IDX			AS MEMBER_IDX,
					COUNT(S_VI.MEMBER_IDX)	AS CNT_VOUCHER
				FROM
					VOUCHER_ISSUE S_VI
				WHERE
					S_VI.COUNTRY	= ? AND
					S_VI.USED_FLG	= FALSE AND
					S_VI.DEL_FLG	= FALSE AND
					NOW() BETWEEN S_VI.USABLE_START_DATE AND S_VI.USABLE_END_DATE
				GROUP BY
					S_VI.MEMBER_IDX
			) AS J_VI ON
			MB.IDX = J_VI.MEMBER_IDX

			LEFT JOIN (
				SELECT
					S_OI.MEMBER_IDX			AS MEMBER_IDX,
					SUM(S_OI.PRICE_PRODUCT)	AS PRICE_TOTAL
				FROM
					ORDER_INFO S_OI
				GROUP BY
					S_OI.MEMBER_IDX
			) AS J_OI ON
			MB.IDX = J_OI.MEMBER_IDX

			LEFT JOIN (
				SELECT
					S_OI.MEMBER_IDX			AS MEMBER_IDX,
					SUM(S_OI.PRICE_REFUND)	AS PRICE_TOTAL
				FROM
					ORDER_CANCEL S_OI
				GROUP BY
					S_OI.MEMBER_IDX
			) AS J_OC ON
			MB.IDX = J_OC.MEMBER_IDX

			LEFT JOIN (
				SELECT
					S_OF.MEMBER_IDX			AS MEMBER_IDX,
					SUM(S_OF.PRICE_REFUND)	AS PRICE_TOTAL
				FROM
					ORDER_REFUND S_OF
				WHERE
					S_OF.ORDER_STATUS = 'ORP'
				GROUP BY
					S_OF.MEMBER_IDX
			) AS J_OF ON
			MB.IDX = J_OF.MEMBER_IDX
		WHERE
			MB.IDX = ?
	";
	
	$db->query($select_member_sql,array($_SERVER['HTTP_COUNTRY'],$_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']));
	
	foreach($db->fetch() as $data) {
		$buy_total = $data['PRICE_I'] - $data['PRICE_C'] - $data['PRICE_R'];
		
		$next_price = " - ";
		if (isset($next_level[$_SERVER['HTTP_COUNTRY']][$_SESSION['LEVEL_IDX']])) {
			$next_price = $next_level[$_SERVER['HTTP_COUNTRY']][$_SESSION['LEVEL_IDX']];
			$next_price -= $buy_total;
			if ($next_price <= 0) {
				$next_price = " - ";
			} else {
				if ($_SERVER['HTTP_COUNTRY'] != "KR") {
					$next_price = number_format($next_price,1);
				} else {
					$next_price = number_format($next_price);
				}
			}
		}
		
		if ($_SERVER['HTTP_COUNTRY'] == "KR") {
			$buy_total = number_format($buy_total);
		} else if ($_SERVER['HTTP_COUNTRY'] == "EN") {
			$buy_total = number_format($buy_total,1);
		}

		$t_mileage = number_format($data['MILEAGE_BALANCE']);
		if ($_SERVER['HTTP_COUNTRY'] != "KR") {
			$t_mileage = number_format($data['MILEAGE_BALANCE'],1);
		}

		$auth_flg = "F";
		if ($data['AUTH_FLG'] == true) {
			$auth_flg = "T";
		}
		
		$json_result = array(
			'id'			=>$data['MEMBER_ID'],
			'email'			=>$data['MEMBER_ID'],
			'name'			=>$data['MEMBER_NAME'],
			'tel'			=>$data['TEL_MOBILE'],
			'birthday'		=>$data['MEMBER_BIRTH'],
			'mileage'		=>$data['MILEAGE_BALANCE'],
			't_mileage'		=>$t_mileage,
			'voucher'		=>$data['CNT_VOUCHER'],
			'membership'	=>$data['MEMBER_LEVEL'],
			
			'buy_total'		=>$buy_total,
			'next_price'	=>$next_price,
			
			'r_mail_flg'	=>$data['RECEIVE_EMAIL_FLG'],
			'r_sms_flg'		=>$data['RECEIVE_SMS_FLG'],
			'r_tel_flg'		=>$data['RECEIVE_TEL_FLG'],

			'auth_flg'		=>$data['AUTH_FLG'],
			
			'cnt_wish'		=>$db->count("WHISH_LIST","COUNTRY = ? AND MEMBER_IDX = ?",array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX'])),
			
			'pg' => array(
				'key' => PG['KEY']
			)
		);
	}
}