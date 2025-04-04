<?php
$round = date_diff(date_create('2024-06-17'),date_create(date('Y-m-d')))->days;
//if(time() > strtotime('2024-06-24 18:00:00')) $round = 8;
$round = 8;
$where = ' A.STATUS="Y" AND A.PERIOD > 15000 AND A.PERIOD != 100000 AND A.ROUND = ?';
$where_values = array($round);
$rank = 1;
$db2 = new db();
$orderby = 'A.`RANK`';
$tables = '
	'.$_TABLE['GAME_RANK'].' AS A
	LEFT JOIN '.$_TABLE['GAME_SCORE'].' AS A2 ON A.RECORD_NO = A2.IDX
	LEFT JOIN '.$_TABLE['CUSTOMER'].' AS B ON A2.CUSTOMER_NO = B.IDX
';

if(isset($id) && $id != '') {
    $where = ' B.ID = ? ';
    $where_values = array($id);
    $rank = 0;
	$orderby = 'SCORE, PERIOD';
	$tables = '
		'.$_TABLE['GAME_SCORE'].' AS A
        LEFT JOIN '.$_TABLE['CUSTOMER'].' AS B ON A.CUSTOMER_NO = B.IDX
	';
}

else {
	/** 랭킹 **/
	if($db->count($_TABLE['GAME_RANK'],'ROUND=?',array($round)) == 0) {
		$rdata = $db->get($_TABLE['GAME_RANK'],'ROUND=?',array(intval($round)-1));		
		//$db->delete($_TABLE['GAME_RANK'],'ROUND=?',array($round));
		/*
		$db->query('
			SELECT
					A.*
				FROM '.$_TABLE['GAME_SCORE'].' AS A
			WHERE
				A.STATUS = "Y"
				AND A.SCORE = 0
			GROUP BY
				A.CUSTOMER_NO
			ORDER BY
				A.PERIOD
		');
		*/
		$db->query('
			SELECT
				A.IDX,
				A.CUSTOMER_NO,
				A.ROUND,
				A.SCORE,
				A.BENEFIT,
				(
				SELECT
					PERIOD 
				FROM
					'.$_TABLE['GAME_SCORE'].'
				WHERE
					STATUS = "Y" 
					AND CUSTOMER_NO = A.CUSTOMER_NO 
					AND SCORE = 0 
					AND PERIOD > 0 
				ORDER BY
					PERIOD 
					LIMIT 1 
				) AS PERIOD_RESULT 
			FROM
				'.$_TABLE['GAME_SCORE'].' AS A 
			WHERE 
				A.PERIOD > 0
			GROUP BY
				A.CUSTOMER_NO
			ORDER BY 
				PERIOD_RESULT
		');

		$rank_num = 1;
		foreach($db->fetch() as $data) {
			// 일부 거름, 여러 기기로 동작?
			if($data['PERIOD_RESULT'] == null) continue;
			//elseif(strtotime($data['UPDATED_DATE'])-strtotime($data['CREATED_DATE']) < intval($data['PERIOD'])) continue;
			elseif(240620 / intval($data['PERIOD_RESULT']) > 15) continue; // 초당 15회는 거름

			$rank_before = 0;
			for($i=0;$i<sizeof($rdata);$i++) {
				if(intval($data['IDX']) == intval($rdata[$i]['RECORD_NO'])) {
					$rank_before = intval($rdata[$i]['RANK']) - $rank_num;
					break;
				}
			}
			
			$db2->insert($_TABLE['GAME_RANK'],array(
				'RECORD_NO' => $data['IDX'],
				'ROUND' => $round,
				'`RANK`' => $rank_num++,
				'RANK_BEFORE' => $rank_before,
				'PERIOD' => $data['PERIOD_RESULT'],
				'SCORE' => $data['SCORE'],
				'BENEFIT' => $data['BENEFIT']
			));
		}
		/*
		if($rank_num < 30) {
			$orderby = 'A.SCORE, A.PERIOD';
			$tables = '
				'.$_TABLE['GAME_SCORE'].' AS A
				LEFT JOIN '.$_TABLE['CUSTOMER'].' AS B ON A.CUSTOMER_NO = B.IDX
			';

		}
		*/
	}
}

$db->query('
    SELECT
            A.*,
            B.ID AS CUSTOMER_ID, B.NAME AS CUSTOMER_NAME
        FROM '.$tables.'
    WHERE
        '.$where.'
	GROUP BY
		B.ID
	ORDER BY
        '.$orderby.'
    LIMIT 
        30
',$where_values);

foreach($db->fetch() as $data) {
	$bonus_count = 0;
    if(!isset($id) || $id == '') {
        $data['CUSTOMER_ID'] = mb_substr($data['CUSTOMER_ID'], 0, -4).'***'.mb_substr($data['CUSTOMER_ID'], -1);
    }
	else {
		$log_data = $db2->get($_TABLE['GAME_SCORE_LOG'],'RECORD_NO = ? ORDER BY IDX DESC LIMIT 1',array($data['IDX']));
		if(sizeof($log_data) > 0) {
			if(isset($log_data[0]['EXT']) && $log_data[0]['EXT'] != '[]') {
				$ext = @json_decode($log_data[0]['EXT'],true);
				if($ext && is_array($ext) && isset($ext['bonus_count'])) {
					$bonus_count = intval($ext['bonus_count']);
				}
			}
		}
	}
    $json_result['data'][] = array(
        'customer' => array(
            'id' => $data['CUSTOMER_ID'],
			'name' => '***'
            //'name' => $data['CUSTOMER_NAME']
        ),
        'round' => intval($data['ROUND']),
        'score' => intval($data['SCORE']),
        'period' => intval($data['PERIOD']),
        'benefit' => $data['BENEFIT'],
        'rank' => array(((isset($data['RANK'])) ? intval($data['RANK']) : $rank++),isset($data['RANK_BEFORE']) ? intval($data['RANK_BEFORE']) : 0),
		'bonus_count' => $bonus_count
	);
}
