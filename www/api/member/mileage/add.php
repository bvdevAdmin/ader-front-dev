<?php
/*
 +=============================================================================
 | 
 | Batch - 마이페이지 마일리지 적립
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2023.01.11
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

$db->begin_transaction();

try {
	$select_mileage_info_sql = "
		SELECT
			MI.IDX					AS MILEAGE_IDX,
			MI.MILEAGE_UNUSABLE		AS MILEAGE_UNUSABLE
		FROM
			MILEAGE_INFO MI
		WHERE
			MI.MILEAGE_CODE = 'PIN' AND
			MI.MILEAGE_USABLE_DATE IS NOT NULL AND
			MI.INC_FLG = FALSE AND
			DATE_FORMAT(
				MI.MILEAGE_USABLE_DATE,
				'%Y-%m-%d'
			) <= CURDATE()
	";

	$db->query($select_mileage_info_sql);

	foreach($db->fetch() as $data) {
		$insert_mileage_info_sql = "
			INSERT INTO
				MILEAGE_INFO
			(
				COUNTRY,
				MEMBER_IDX,
				ID,
				MEMBER_LEVEL,
				MILEAGE_CODE,
				MILEAGE_UNUSABLE,
				MILEAGE_USABLE_INC,
				MILEAGE_BALANCE,
				ORDER_CODE,
				ORDER_PRODUCT_CODE,
				ORDER_UPDATE_CODE,
				DATE_CODE,
				CREATER,
				UPDATER
			)
			SELECT
				MI.COUNTRY				AS COUNTRY,
				MI.MEMBER_IDX			AS MEMBER_IDX,
				MI.ID					AS ID,
				MB.LEVEL_IDX			AS MEMBER_LEVEL,
				'APM'					AS MILEAGE_CODE,
				0						AS MILEAGE_UNUSABLE,
				?						AS MILEAGE_USABLE_INC,
				MILEAGE_BALANCE + ?		AS MILEAGE_BALANCE,
				MI.ORDER_CODE			AS ORDER_CODE,
				MI.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
				MI.ORDER_UPDATE_CODE	AS ORDER_UPDATE_CODE,
				'TODAY'					AS DATE_CODE,
				'system'				AS CREATER,
				'system'				AS UPDATER
			FROM
				MILEAGE_INFO MI

				LEFT JOIN MEMBER MB ON
				MI.MEMBER_IDX = MB.IDX
			WHERE
				MI.IDX = ?
		";

		$db->query($insert_mileage_info_sql,array($data['MILEAGE_UNUSABLE'],$data['MILEAGE_UNUSABLE'],$data['MILEAGE_IDX']));

		$mileage_idx = $db->last_id();
		if (!empty($mileage_idx)) {
			$db->update(
				"MILEAGE_INFO",
				array(
					'INC_FLG'		=>1,
					'UPDATER'		=>'system',
					'UPDATE_DATE'	=>NOW()
				),
				"IDX = ?",
				array($data['MILEAGE_IDX'])
			);

			$db->commit();
		}
	}
} catch (mysqli_sql_exception $e) {
	$db->rollback();
	
	print_r($e);
	
	$json_result['code'] = 301;
	$json_result['msg'] = '적립금 자동적립처리중 오류가 발생했습니다.';
	
	echo json_encode($json_result);
	exit;
}

?>