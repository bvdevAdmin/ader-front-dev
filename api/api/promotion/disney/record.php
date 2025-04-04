<?php

if(!isset($id) || $id == '') {
    $code = 999;
    $msg = '회원 아이디가 넘어오지 않았습니다.';
}
elseif(!isset($promotion_no) || !is_numeric($promotion_no)) {
    $code = 999;
    $msg = 'promotion_no가 넘어오지 않았습니다.';
}
elseif(!isset($score) || !is_numeric($score)) {
    $code = 999;
    $msg = 'score가 넘어오지 않았습니다.';
}
else {
    if($db->count($_TABLE['CUSTOMER'],'ID=?',array($id)) == 0) {
        $db->insert(
            $_TABLE['CUSTOMER'],
            array(
                'ID' => $id,
                'NAME' => (isset($name)) ? $name : '임시 회원',
                'PW_HISTORY' => '[]'
            )
        );
        $customer_no = $db->last_id();
    }
    else {
        $customer_no = $db->get($_TABLE['CUSTOMER'],'ID=?',array($id))[0]['IDX'];
    }

    try {
        $round = $db->count($_TABLE['GAME_SCORE'],'CUSTOMER_NO=? AND SCORE=0',array($customer_no))+1;

        $db->begin();

        $where = 'CUSTOMER_NO=? AND ROUND=?';
        $where_values = array($customer_no,$round);
        if($db->count($_TABLE['GAME_SCORE'],$where,$where_values) == 0) {
            $values = array(
                'PROMOTION_NO' => $promotion_no,
                'CUSTOMER_NO' => $customer_no,
                'BENEFIT' => (isset($benefit)) ? $benefit : '',
                'ROUND' => $round,
                'SCORE' => $score
            );
            $db->insert($_TABLE['GAME_SCORE'],$values);
        }
        $data = $db->get($_TABLE['GAME_SCORE'],$where.' ORDER BY IDX DESC LIMIT 1',$where_values)[0];
        $record_no = $data['IDX'];

        // 게임 종료
        if($flag == 'fin' || $flag == 'save') {
            $log_data = $db->get($_TABLE['GAME_SCORE_LOG'],'RECORD_NO = ? AND FLAG = "진행" ORDER BY IDX DESC LIMIT 1',array($record_no))[0];
            $period = time() - strtotime($log_data['CREATED_DATE']); // 해당 라운드 진행시간 구함
            if($period > 100000) $period = 100000; // 보정 

            // 로그 기록 완료 처리
			/*
            switch($flag) {
                case 'fin':
                    $values = array(
                        'PERIOD' => $period, // 해당 라운드 진행시간
                        'FLAG' => '종료' // 종료 처리
                    );
                break;
                case 'save':
                    $values = array(
						'SCORE' => intval($score),
                        'PERIOD' => $period, // 해당 라운드 진행시간
                        'FLAG' => '종료' // 종료 처리
                    );
                break;
            }
			*/
			$values = array(
				'PERIOD' => $period, // 해당 라운드 진행시간
				'FLAG' => '종료' // 종료 처리
			);
			if($flag == 'save') {
				$values['SCORE'] = intval($score);
			}
			if(isset($bonus_count)) {
				$values['EXT'] = json_encode(array('bonus_count'=>intval($bonus_count)));
			}
            $db->update(
                $_TABLE['GAME_SCORE_LOG'],
                $values,
                'IDX=?',
                array($log_data['IDX'])
            );

			/** 기록 **/
			$values = array(
                'SCORE' => $score, // 최종 점수
                'PERIOD' => $period + intval($data['PERIOD'])
            );
            if(isset($benefit) && $benefit != '') {
                $values['BENEFIT'] = $benefit;
            }

            $db->update(
                $_TABLE['GAME_SCORE'],
                $values,
                'IDX=?',
                array($data['IDX'])
            ); // 총 진행 시간 기록
        }

        // 게임 시작
        if($flag == 'start' || $flag == 'save') {
			$values = array(
				'RECORD_NO' => $record_no,
				'START' => $score, // 시작 점수
				'SCORE' => $score, // 시작 점수
				'IP' => $_SERVER['REMOTE_ADDR'],
				'FLAG' => '진행',
				'CREATED_DATE' => now()
			);
			if(isset($bonus_count)) {
				$values['EXT'] = json_encode(array('bonus_count'=>intval($bonus_count)));
			}

            $db->insert($_TABLE['GAME_SCORE_LOG'],$values);
        }

        $json_result = array(
            'customer' => array(
                'id' => $id
            ),
            'score' => intval($score),
            'timestamp' => time()
        );
        $db->commit();

    }
    catch(Exceiption $e) {
        $code = 500;
        $msg = $e->getMessage();
    }
}