<?php
/*
 +=============================================================================
 | 
 | 공통함수 - 상품 색상/사이즈 정보 조회
 | -------
 |
 | 최초 작성	: 손성환
 | 최초 작성일	: 2022.10.25
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

/* 임시 주문정보 삭제처리 */
function deleteOrder_tmp($db,$country,$member_idx) {
	$db->delete(
		"TMP_ORDER_PRODUCT",
		"ORDER_IDX = (
			SELECT
				S_OI.IDX
			FROM
				TMP_ORDER_INFO S_OI
			WHERE
				S_OI.COUNTRY = ? AND
				S_OI.MEMBER_IDX = ?
		)",
		array($country,$member_idx)
	);
	
	$db->delete(
		"TMP_ORDER_INFO",
		"COUNTRY = ? AND MEMBER_IDX = ?",
		array($country,$_SESSION['MEMBER_IDX'])
	);
}

/* 로그인 한 회원의 등급별 적립/할인율 조회 */
function checkMember_percentage($db) {
	$member_percentage = array();
	
	$select_member_percentage_sql = "
		SELECT
			IFNULL(LV.MILEAGE_PER,0)	AS MILEAGE_PER,
			IFNULL(LV.DISCOUNT_PER,0)	AS DISCOUNT_PER
		FROM
			MEMBER MB
			
			LEFT JOIN MEMBER_LEVEL LV ON
			MB.LEVEL_IDX = LV.IDX
		WHERE
			MB.IDX = ?
	";
	
	$db->query($select_member_percentage_sql,array($_SESSION['MEMBER_IDX']));
	
	foreach($db->fetch() as $data) {
		$member_percentage = array(
			'mileage_per'		=>$data['MILEAGE_PER'],
			'discount_per'		=>$data['DISCOUNT_PER']
		);
	}
	
	return $member_percentage;
}

/* 결제 회원 기본 배송지 및 배송금액 계산 처리 */
function getOrder_to($db,$country,$param_idx) {
	$order_to = null;

	$where = "";

	$param_bind = array();

	if ($param_idx != null) {
		$where .= " OT.IDX = ? ";

		array_push($param_bind,$param_idx);
	} else {
		$where .= "
			OT.COUNTRY = ? AND
			OT.MEMBER_IDX = ? AND
			OT.DEFAULT_FLG = TRUE
		";

		$param_bind = array($country,$_SESSION['MEMBER_IDX']);
	}
	
	$select_order_to_sql = "
		SELECT
			OT.IDX					AS TO_IDX,
			OT.COUNTRY				AS COUNTRY,
			OT.TO_PLACE				AS TO_PLACE,
			OT.TO_NAME				AS TO_NAME,
			OT.TO_MOBILE			AS TO_MOBILE,
			OT.TO_ZIPCODE			AS TO_ZIPCODE,
			OT.TO_ROAD_ADDR			AS TO_ROAD_ADDR,
			IFNULL(
				OT.TO_LOT_ADDR,''
			)						AS TO_LOT_ADDR,
			IFNULL(
				OT.TO_DETAIL_ADDR,''
			)						AS TO_DETAIL_ADDR,
			
			CI.COUNTRY_NAME			AS COUNTRY_NAME,
			PI.PROVINCE_NAME		AS PROVINCE_NAME,
			OT.TO_CITY				AS CITY,
			OT.TO_ADDRESS			AS ADDRESS,
			
			OT.DEFAULT_FLG			AS DEFAULT_FLG,
			IFNULL(
				DZ.COST,0
			)						AS DELIVERY_PRICE
		FROM
			ORDER_TO OT
			
			LEFT JOIN COUNTRY_INFO CI ON
			OT.TO_COUNTRY_CODE = CI.COUNTRY_CODE
			
			LEFT JOIN PROVINCE_INFO PI ON
			OT.TO_PROVINCE_IDX = PI.IDX
			
			LEFT JOIN DHL_ZONES DZ ON
			CI.ZONE_NUM = DZ.ZONE_NUM
		WHERE
			".$where."
	";
	
	$db->query($select_order_to_sql,$param_bind);
	
	foreach($db->fetch() as $data) {
		$delivery_price = 0;
		if ($data['COUNTRY'] == "KR") {
			/* 한국몰 배송금액 설정 */

			/* 배송지역별 추가 배송비 설정 여부 체크처리 */
			$cnt_location = $db->count("DELIVERY_LOCATION","? BETWEEN START_ZIPCODE AND END_ZIPCODE",array($data['TO_ZIPCODE']));
			if ($cnt_location > 0) {
				$delivery_price = checkOrder_location($db,$data['TO_ZIPCODE']);
			} else {
				$delivery_price = 2500;
			}
		} else if ($data['COUNTRY'] == "EN" && $data['DELIVERY_PRICE'] > 0) {
			/* 영문몰 배송금액 설정 */

			$delivery_price = round(currency_EN * $data['DELIVERY_PRICE'],2);
		}

		$to_addr = $data['TO_ROAD_ADDR'];
		if ($data['COUNTRY'] != "KR") {
			$to_addr = $data['COUNTRY_NAME']." ".$data['PROVINCE_NAME']." ".$data['CITY']." ".$data['ADDRESS'];
			$t_delivery_price = number_format($delivery_price,1);
		} else {
			$t_delivery_price = number_format($delivery_price);
		}
		
		$order_to = array(
			'to_idx'			=>$data['TO_IDX'],
			'to_place'			=>$data['TO_PLACE'],
			'to_name'			=>$data['TO_NAME'],
			'to_mobile'			=>$data['TO_MOBILE'],
			'to_zipcode'		=>$data['TO_ZIPCODE'],
			'to_road_addr'		=>$to_addr,
			'to_lot_addr'		=>$data['TO_LOT_ADDR'],
			'to_detail_addr'	=>$data['TO_DETAIL_ADDR'],
			'default_flg'		=>$data['DEFAULT_FLG'],
			'delivery_price'	=>$delivery_price,
			't_delivery_price'	=>$t_delivery_price
		);
	}
	
	return $order_to;
}

/* 배송지역별 추가 배송비 설정 여부 체크처리 */
function checkOrder_location($db,$zipcode) {
	$delivery_price = 0;

	$delivery_location = $db->get("DELIVERY_LOCATION","? BETWEEN START_ZIPCODE AND END_ZIPCODE",array($zipcode));
	if (sizeof($delivery_location) > 0) {
		$delivery_price = $delivery_location[0]['DELIVERY_PRICE'];
	}

	return $delivery_price;
}

function setOrder_update($db,$param_status,$order_code) {
	$cnt_update = 0;
	
	$table = array(
		'OCC'		=>"ORDER_CANCEL",
		'OEX'		=>"ORDER_EXCHANGE",
		'ORF'		=>"ORDER_REFUND"
	);
	
	$connect = array(
		'OCC'		=>"C",
		'OEX'		=>"E",
		'ORF'		=>"R"
	);
	
	$cnt_update = $db->count($table[$param_status],"ORDER_CODE = ?",array($order_code));
	$cnt_update++;
	
	$order_update_code = $order_code."-".$connect[$param_status]."-".$cnt_update;
	
	return $order_update_code;
}

function setSize_type($option_name) {
	$size_type = null;
	
	$tmp_len = strlen($option_name);
	switch ($tmp_len) {
		case 2 :
			$size_type = "N";
			
			break;
		
		case 3 :
			$size_type = "O";
			
			break;
		
		default :
			$size_type = "S";
			
			break;
	}
	
	return $size_type;
}

/* (공통) - 상품 컬러 정보 조회 */
function getProduct_color($db,$country,$member_idx,$product_idx) {
	$product_color = array();
	
	$select_product_color_sql = "
		SELECT
			PR.IDX			AS PRODUCT_IDX,
			PR.COLOR		AS COLOR,
			PR.COLOR_RGB	AS COLOR_RGB,
			
			IFNULL(
				J_ST.LIMIT_QTY,0
			)				AS LIMIT_QTY,
			IFNULL(
				J_RE.CNT_REORDER,0
			)				AS CNT_REORDER
		FROM
			SHOP_PRODUCT PR
			
			LEFT JOIN (
				SELECT
					V_ST.PRODUCT_IDX	AS PRODUCT_IDX,
					SUM(
						V_ST.PURCHASEABLE_QTY
					)					AS LIMIT_QTY
				FROM
					V_STOCK V_ST
				GROUP BY
					V_ST.PRODUCT_IDX
			) AS J_ST ON
			PR.IDX = J_ST.PRODUCT_IDX
			
			LEFT JOIN (
				SELECT
					S_RE.PRODUCT_IDX		AS PRODUCT_IDX,
					COUNT(S_RE.PRODUCT_IDX)	AS CNT_REORDER
				FROM
					REORDER_INFO S_RE
				WHERE
					S_RE.COUNTRY = ? AND
					S_RE.MEMBER_IDX = ?
				GROUP BY
					S_RE.PRODUCT_IDX
			) AS J_RE ON
			PR.IDX = J_RE.PRODUCT_IDX
		WHERE
			PR.SALE_FLG = TRUE AND
			PR.STYLE_CODE IN (
				SELECT
					S_PR.STYLE_CODE
				FROM
					SHOP_PRODUCT S_PR
				WHERE
					S_PR.IDX IN (".implode(',',array_fill(0,count($product_idx),'?')).")
			)
	";
	
	$db->query($select_product_color_sql,array_merge(array($country,$member_idx),$product_idx));
	
	foreach($db->fetch() as $data) {
		$stock_status = "";
		$reorder_flg = false;
		
		if ($data['LIMIT_QTY'] > 0) {
			$stock_status = "STIN";	//재고 있음 (Stock in)
		} else {
			$stock_status = "STSO";	//재고 없음(사선)		→ 증가 예정 재고 없음 (Stock sold out)
		}
		
		$reorder_flg = false;
		if ($data['CNT_REORDER'] > 0) {
			$reorder_flg = true;
		}
		
		$product_color[$data['PRODUCT_IDX']][] = array(
			'product_idx'		=>$data['PRODUCT_IDX'],
			'color'				=>$data['COLOR'],
			'color_rgb'			=>$data['COLOR_RGB'],
			
			'stock_status'		=>$stock_status,
			'reorder_flg'		=>$reorder_flg
		);
	}
	
	return $product_color;
}

/* 일반 상품 사이즈별 재고정보 조회 */
function getProduct_size_B($db,$product_idx) {
	$product_size = array();
	
	$select_product_size_sql = "
		SELECT
			PR.IDX							AS PRODUCT_IDX,
			PR.COLOR						AS COLOR,
			OO.IDX							AS OPTION_IDX,
			OO.OPTION_NAME					AS OPTION_NAME,

			PR.SOLD_OUT_FLG					AS SOLD_OUT_FLG,
			PR.REORDER_FLG					AS REORDER_FLG,

			PR.SOLD_OUT_QTY					AS SOLD_OUT_QTY,
			IFNULL(
				J_PS.CNT_STANDBY,0
			)								AS CNT_STANDBY,
			IFNULL(
				J_ST.LIMIT_QTY,0
			)								AS LIMIT_QTY
		FROM
			SHOP_PRODUCT PR
			
			LEFT JOIN SHOP_OPTION OO ON
			PR.IDX = OO.PRODUCT_IDX
			
			LEFT JOIN (
				SELECT
					S_PS.PRODUCT_IDX		AS PRODUCT_IDX,
					S_PS.OPTION_IDX			AS OPTION_IDX,
					COUNT(S_PS.IDX)			AS CNT_STANDBY
				FROM
					PRODUCT_STOCK S_PS
				WHERE
					S_PS.STOCK_DATE > NOW() AND
					S_PS.DEL_FLG = FALSE
				GROUP BY
					S_PS.OPTION_IDX
			) AS J_PS ON
			PR.IDX = J_PS.PRODUCT_IDX AND
			OO.IDX = J_PS.OPTION_IDX
			
			LEFT JOIN (
				SELECT
					V_ST.PRODUCT_IDX		AS PRODUCT_IDX,
					V_ST.OPTION_IDX			AS OPTION_IDX,
					V_ST.PURCHASEABLE_QTY	AS LIMIT_QTY
				FROM
					V_STOCK V_ST
				GROUP BY
					V_ST.OPTION_IDX
			) AS J_ST ON
			PR.IDX = J_ST.PRODUCT_IDX AND
			OO.IDX = J_ST.OPTION_IDX
		WHERE
			PR.IDX IN (".implode(',',array_fill(0,count($product_idx),'?')).")
		ORDER BY
			OO.IDX ASC
	";
	
	$db->query($select_product_size_sql,$product_idx);
	
	foreach($db->fetch() as $data) {
		if ($data['SOLD_OUT_FLG'] == true) {
			$stock_status = "STSO";
		} else {
			/* 재고 상태 계산처리 */
			$stock_status	= calcQTY_stock($data['LIMIT_QTY'],$data['SOLD_OUT_QTY'],$data['CNT_STANDBY']);
			if ($stock_status == "STSC") {
				if ($data['REORDER_FLG'] != true) {
					$stock_status = "STSO";
				}
			}
		}
		
		$product_size[$data['PRODUCT_IDX']][] = array(
			'product_idx'		=>$data['PRODUCT_IDX'],
			'color'				=>$data['COLOR'],
			'option_idx'		=>$data['OPTION_IDX'],
			'option_name'		=>$data['OPTION_NAME'],
			
			'size_type'			=>setSize_type($data['OPTION_NAME']),
			'stock_status'		=>$stock_status
		);
	}
	
	return $product_size;
}

/* 컬러 세트 상품 사이즈별 재고정보 조회 */
function getProduct_size_S($db,$product_idx) {
	$product_size = array();
	
	$param_set = getSET_option($db,$product_idx);
	
	$select_product_size_SZ_sql = "
		SELECT
			SP.SET_PRODUCT_IDX			AS SET_IDX,
			SP.PRODUCT_IDX				AS PRODUCT_IDX,
			SP.OPTION_IDX				AS OPTION_IDX
		FROM
			SET_PRODUCT SP
		WHERE
			SP.SET_PRODUCT_IDX IN (".implode(',',array_fill(0,count($product_idx),'?')).")
	";
	
	$db->query($select_product_size_SZ_sql,$product_idx);
	
	foreach($db->fetch() as $data) {
		$set_option = array();
		
		$set_option_idx = $data['OPTION_IDX'];
		if ($set_option_idx != null && strlen($set_option_idx) > 0) {
			$set_option_idx = explode(",",$set_option_idx);	
			if (count($set_option_idx) > 0) {
				foreach($set_option_idx as $option) {
					$param = $param_set[$option];
					
					$set_option[] = array(
						'product_idx'		=>$param['product_idx'],
						'product_name'		=>$param['product_name'],
						'img_location'		=>$param['img_location'],
						'color'				=>$param['color'],
						'color_rgb'			=>$param['color_rgb'],
						'option_idx'		=>$param['option_idx'],
						'option_name'		=>$param['option_name'],
						
						/* 재고 상태 계산처리 */
						'stock_status'		=>calcQTY_stock($param['limit_qty'],0,$param['cnt_standby'])
					);
				}
			}
		}
		
		$product_size[$data['SET_IDX']][] = array(
			'set_option'		=>$set_option
		);
	}
	
	return $product_size;
}

/* 재고 상태 계산처리 */
function calcQTY_stock($limit_qty,$sold_out_qty,$cnt_standby) {
	$stock_status = null;
	
	/* 구매 가능 수량 */
	if ($limit_qty > 0) {
		if ($limit_qty >= $sold_out_qty) {
			$stock_status = "STIN";	//재고 있음 (Stock in)
		} else {
			$stock_status = "STCL";	//품절 임박 (Stock sold out close)
		}
	} else {
		if ($cnt_standby > 0) {
			$stock_status = "STSC";	//재고 없음(그레이아웃)	→ 재고 증가 예정 (Stock in schedule)
		} else {
			$stock_status = "STSO";	//재고 없음(사선)		→ 증가 예정 재고 없음 (Stock sold out)
		}
	}
	
	return $stock_status;
}

function getSET_option($db,$product_idx) {
	$set_option = array();
	
	$select_ordersheet_option_sql = "
		SELECT
			PR.IDX							AS PRODUCT_IDX,
			
			PR.PRODUCT_NAME					AS PRODUCT_NAME,
			J_PI.IMG_LOCATION				AS IMG_LOCATION,
			PR.COLOR						AS COLOR,
			PR.COLOR_RGB					AS COLOR_RGB,
			
			OO.IDX							AS OPTION_IDX,
			OO.OPTION_NAME					AS OPTION_NAME,
			
			IFNULL(
				J_PS.CNT_STANDBY,0
			)								AS CNT_STANDBY,
			IFNULL(
				V_ST.PURCHASEABLE_QTY,0
			)								AS LIMIT_QTY
		FROM
			SET_PRODUCT SP
			
			LEFT JOIN SHOP_PRODUCT PR ON
			SP.PRODUCT_IDX = PR.IDX
			
			LEFT JOIN SHOP_OPTION OO ON
			PR.IDX = OO.PRODUCT_IDX
			
			LEFT JOIN (
				SELECT
					S_PS.OPTION_IDX			AS OPTION_IDX,
					COUNT(S_PS.IDX)			AS CNT_STANDBY
				FROM
					PRODUCT_STOCK S_PS
				WHERE
					S_PS.STOCK_DATE > NOW() AND
					S_PS.DEL_FLG = FALSE
				GROUP BY
					S_PS.OPTION_IDX
			) AS J_PS ON
			OO.IDX = J_PS.OPTION_IDX
			
			LEFT JOIN (
				SELECT
					S_PI.PRODUCT_IDX	AS PRODUCT_IDX,
					S_PI.IMG_LOCATION	AS IMG_LOCATION
				FROM
					PRODUCT_IMG S_PI
				WHERE
					S_PI.IMG_TYPE = 'P' AND
					S_PI.IMG_SIZE = 'S' AND
					S_PI.DEL_FLG = FALSE
				GROUP BY
					S_PI.PRODUCT_IDX
			) AS J_PI ON
			PR.IDX = J_PI.PRODUCT_IDX
				
			LEFT JOIN V_STOCK V_ST ON
			PR.IDX = V_ST.PRODUCT_IDX AND
			OO.IDX = V_ST.OPTION_IDX
		WHERE
			SP.SET_PRODUCT_IDX IN (".implode(',',array_fill(0,count($product_idx),'?')).")
	";
	
	$db->query($select_ordersheet_option_sql,$product_idx);
	
	foreach($db->fetch() as $data) {
		$set_option[$data['OPTION_IDX']] = array(
			'product_idx'		=>$data['PRODUCT_IDX'],
			'product_name'		=>$data['PRODUCT_NAME'],
			'img_location'		=>$data['IMG_LOCATION'],
			'color'				=>$data['COLOR'],
			'color_rgb'			=>$data['COLOR_RGB'],
			
			'option_idx'		=>$data['OPTION_IDX'],
			'option_name'		=>$data['OPTION_NAME'],
			
			'cnt_standby'		=>$data['CNT_STANDBY'],
			'limit_qty'			=>$data['LIMIT_QTY']
		);
	}
	
	return $set_option;
}

function getMsgToMsgCode($db,$country,$msg_code,$mapping_arr){
	$msg_text = "";

	$msg_mst = $db->get("MSG_MST","MSG_CODE = ?",array($msg_code));
	if (sizeof($msg_mst) > 0) {
		if (isset($msg_mst[0]['MSG_TEXT_'.$country])) {
			$msg_text = $msg_mst[0]['MSG_TEXT_'.$country];
		}

		foreach($mapping_arr as $mapping_info) {
			$msg_text = str_replace($mapping_info['key'],$mapping_info['value'],$msg_text);
		}
	}
	
	return $msg_text;
}

function addMember_log($db,$member_idx,$member_id) {
	$where = "";
	$param_bind = array();

	if ($member_idx != null) {
		$where .= " IDX = ? ";

		array_push($param_bind,$member_idx);
	} else if ($member_id != null) {
		$where .= " MEMBER_ID = ? ";

		array_push($param_bind,$member_id);
	}

	$db->query('
		INSERT INTO
			MEMBER_LOG
		(
			MEMBER_IDX,
			COUNTRY,
			MEMBER_STATUS,
			MEMBER_ID,
			NAVER_ACCOUNT_KEY,
			KAKAO_ACCOUNT_KEY,
			GOOGLE_ACCOUNT_KEY,
			LEVEL_IDX,
			MEMBER_NAME,
			MEMBER_PW,
			PW_DATE,
			MEMBER_BIRTH,
			TEL_MOBILE,
			RECEIVE_EMAIL_FLG,
			RECEIVE_EMAIL_DATE,
			RECEIVE_SMS_FLG,
			RECEIVE_SMS_DATE,
			RECEIVE_TEL_FLG,
			RECEIVE_TEL_DATE,
			LOGIN_IP,
			JOIN_DATE,
			LOGIN_DATE,
			SLEEP_DATE,
			SLEEP_OFF_DATE,
			DROP_TYPE,
			DROP_DATE,
			DROP_OFF_DATE,
			DROP_REASON,
			LOGIN_CNT,
			SUSPICION_FLG,
			SUSPICION_MEMO,
			IMPROPPER_FLG,
			IMPROPPER_MEMO,
			MIG_ID,
			AUTH_NO,
			AUTH_DATE,
			UPDATER
		)
		SELECT
			IDX,
			COUNTRY,
			MEMBER_STATUS,
			MEMBER_ID,
			NAVER_ACCOUNT_KEY,
			KAKAO_ACCOUNT_KEY,
			GOOGLE_ACCOUNT_KEY,
			LEVEL_IDX,
			MEMBER_NAME,
			MEMBER_PW,
			PW_DATE,
			MEMBER_BIRTH,
			TEL_MOBILE,
			RECEIVE_EMAIL_FLG,
			RECEIVE_EMAIL_DATE,
			RECEIVE_SMS_FLG,
			RECEIVE_SMS_DATE,
			RECEIVE_TEL_FLG,
			RECEIVE_TEL_DATE,
			LOGIN_IP,
			JOIN_DATE,
			LOGIN_DATE,
			SLEEP_DATE,
			SLEEP_OFF_DATE,
			DROP_TYPE,
			DROP_DATE,
			DROP_OFF_DATE,
			DROP_REASON,
			LOGIN_CNT,
			SUSPICION_FLG,
			SUSPICION_MEMO,
			IMPROPPER_FLG,
			IMPROPPER_MEMO,
			MIG_ID,
			AUTH_NO,
			AUTH_DATE,
			MEMBER_ID
		FROM
			MEMBER
		WHERE
			'.$where.'
	',$param_bind);
}

/* 1Day 토큰 체크 */
function checkToken($db) {
	$cnt_token = $db->count("DELIVERY_TOKEN","DATE_FORMAT(NOW(),'%Y-%m-%d') <= DATE_FORMAT(TOKEN_DATE,'%Y-%m-%d')");
	
	$token_num = null;
	if ($cnt_token > 0) {
		$delivery_token = $db->get("DELIVERY_TOKEN","DATE_FORMAT(TOKEN_DATE,'%Y-%m-%d %H:%i:%s') < ?",array("DATE_FORMAT('%Y-%m-%d %H:%i:%s')"))[0];
		$token_num = $delivery_token['TOKEN_NUM'];
	} else {
		$token = generateToken();
		$token_num = $token['token_num'];
		
		$cnt = $db->count("DELIVERY_TOKEN");
		if ($cnt > 0) {
			$db->update(
				"DELIVERY_TOKEN",
				array(
					'TOKEN_NUM'		=>$token_num,
					'TOKEN_DATE'	=>$token['token_date']
				)
			);
		} else {
			$db->insert(
				"DELIVERY_TOKEN",
				array(
					'TOKEN_NUM'		=>$token_num,
					'TOKEN_DATE'	=>$token['token_date']
				)
			);
		}
	}
	
	return $token_num;
}

/* 1Day 토큰 발행 */
function generateToken() {
	$curl = curl_init();

	curl_setopt_array($curl, [
		CURLOPT_URL				=>"https://dxapi-dev.cjlogistics.com:5054/ReqOneDayToken",
		CURLOPT_RETURNTRANSFER	=>true,
		CURLOPT_ENCODING		=>"",
		CURLOPT_MAXREDIRS		=>10,
		CURLOPT_TIMEOUT			=>30,
		CURLOPT_HTTP_VERSION	=>CURL_HTTP_VERSION_1_1,
		CURLOPT_CUSTOMREQUEST	=>"POST",
		CURLOPT_POSTFIELDS		=>'
			{
				"DATA" : {
					"CUST_ID"		:30426467,
					"BIZ_REG_NUM"	:7608701757
				}
			}
		',
		CURLOPT_HTTPHEADER => [
			"Content-type:application/json",
			"Accept:application/json"
		],
	]);

	$response = curl_exec($curl);
	$err = curl_error($curl);

	if (!$err) {
		$result = json_decode($response,true);
		
		$result_cd = $result['RESULT_CD'];
		
		if ($result_cd == "S") {
			$token_num = $result['DATA']['TOKEN_NUM'];
			$token_date = date("Y-m-d H:i:s", strtotime($result['DATA']['TOKEN_EXPRTN_DTM']));
			
			$token = array(
				"token_num"		=>$token_num,
				"token_date"	=>$token_date,
			);
			
			return $token;
		}
	}
}

?>