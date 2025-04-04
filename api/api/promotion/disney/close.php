<?php
$db2 = new db();
$db->query('TRUNCATE TABLE PROMOTIONS_GAME_RANKING_C');
for($round = 1;$round <= 8 ; $round++) {
	$json_result['round_'.$round] = [];
	$rdata = $db->get('PROMOTIONS_GAME_RANKING_C','ROUND=?',array($round-1));
	$date = date('Y-m-d 00:00:00',strtotime('+'.$round.'day',strtotime('2024-06-17')));
	if($round == 8) {
		$date = '2024-06-24 18:00:00';
	}
	$json_result['round'][] = $date;
	$db->query('
		SELECT
			A.CUSTOMER_NO,
			A.ROUND,
			A.SCORE,
			A.BENEFIT,
			B.NAME AS CUSTOMER_NAME,
			B.ID AS CUSTOMER_ID,
			(
			SELECT
				IDX
			FROM
				'.$_TABLE['GAME_SCORE'].'
			WHERE
				STATUS = "Y" 
				AND CUSTOMER_NO = A.CUSTOMER_NO 
				AND SCORE = 0 
				AND PERIOD > 16041 
				AND UPDATED_DATE < ?
			ORDER BY
				PERIOD 
			LIMIT 1 
			) AS IDX,
			(
			SELECT
				PERIOD 
			FROM
				PROMOTIONS_GAME_RECORD 
			WHERE
				STATUS = "Y" 
				AND CUSTOMER_NO = A.CUSTOMER_NO 
				AND SCORE = 0 
				AND PERIOD > 16041
			ORDER BY
				PERIOD 
				LIMIT 1 
			) AS PERIOD_RESULT
			
		FROM
			'.$_TABLE['GAME_SCORE'].' AS A 
		LEFT JOIN '.$_TABLE['CUSTOMER'].' AS B ON A.CUSTOMER_NO = B.IDX 
		WHERE 
			A.PERIOD > 0
		GROUP BY
			A.CUSTOMER_NO
		ORDER BY 
			PERIOD_RESULT
		',
		array($date)
	);

	$rank_num = 0;
	foreach($db->fetch() as $data) {
		// 일부 거름, 여러 기기로 동작?
		if($data['PERIOD_RESULT'] == null || $data['IDX'] == null || intval($data['PERIOD_RESULT']) == 100000) continue;
		elseif(240620 / intval($data['PERIOD_RESULT']) > 15) continue; // 초당 15회는 거름

		$rank_before = ++$rank_num;
		for($i=0;$i<sizeof($rdata);$i++) {
			if(intval($data['IDX']) == intval($rdata[$i]['RECORD_NO'])) {
				$rank_before = intval($rdata[$i]['RANK']) - $rank_num;
				break;
			}
		}
		$db2->insert('PROMOTIONS_GAME_RANKING_C',array(
			'RECORD_NO' => $data['IDX'],
			'ROUND' => $round,
			'`RANK`' => $rank_num,
			'RANK_BEFORE' => $rank_before,
			'PERIOD' => $data['PERIOD_RESULT'],
			'SCORE' => $data['SCORE'],
			'BENEFIT' => $data['BENEFIT']
		));
		$bonus_count = 0;
		$log_data = $db2->get($_TABLE['GAME_SCORE_LOG'],'RECORD_NO = ? ORDER BY IDX DESC LIMIT 1',array($data['IDX']));
		if(sizeof($log_data) > 0) {
			if(isset($log_data[0]['EXT']) && $log_data[0]['EXT'] != '[]') {
				$ext = @json_decode($log_data[0]['EXT'],true);
				if($ext && is_array($ext) && isset($ext['bonus_count'])) {
					$bonus_count = intval($ext['bonus_count']);
				}
			}
		}
		$json_result['round_'.$round][] = array(
			'customer' => array(
				'id' => $data['CUSTOMER_ID'],
				'name' => $data['CUSTOMER_NAME']
			),
			'round' => intval($data['ROUND']),
			'score' => intval($data['SCORE']),
			'period' => intval($data['PERIOD_RESULT']),
			'benefit' => $data['BENEFIT'],
			'rank' => $rank_num,
			'bonus_count' => $bonus_count
		);
	}
}
