<?php
/*
 +=============================================================================
 | 
 | 오더 리스트 조회
 | -------
 |
 | 최초 작성	: 이재민
 | 최초 작성일	: 2024.11.16
 | 최종 수정일	: 
 | 버전		: 1.0
 | 설명		: 
 | 
 +=============================================================================
*/

if (isset($_SERVER['HTTP_COUNTRY']) && isset($_SESSION['MEMBER_IDX'])) {
    $table = "
        ORDER_INFO OI

        LEFT JOIN DELIVERY_COMPANY DC ON
        OI.DELIVERY_IDX = DC.IDX

        LEFT JOIN (
            SELECT
                OP.ORDER_IDX,
                S_PI.IMG_LOCATION
            FROM
                ORDER_PRODUCT OP
                
                LEFT JOIN PRODUCT_IMG S_PI ON
                S_PI.PRODUCT_IDX = OP.PRODUCT_IDX
            WHERE
                S_PI.IMG_TYPE = 'P' AND
                S_PI.IMG_SIZE = 'S' AND
                S_PI.DEL_FLG = FALSE
            GROUP BY
                OP.ORDER_IDX
            ORDER BY
                OP.IDX
        ) IM ON
        IM.ORDER_IDX = OI.IDX

        LEFT JOIN (
            SELECT
                S_OP.ORDER_CODE         AS ORDER_CODE,
                SUM(S_OP.REMAIN_QTY)    AS CNT_REMAIN
            FROM
                ORDER_PRODUCT S_OP
            WHERE
                S_OP.PRODUCT_TYPE NOT REGEXP 'V|D'
            GROUP BY
                S_OP.ORDER_CODE
        ) AS J_OP ON
        OI.ORDER_CODE = J_OP.ORDER_CODE
    ";

    $where = "
        OI.COUNTRY		= ? AND
        OI.MEMBER_IDX   = ?
    ";

    $param_bind = array($_SERVER['HTTP_COUNTRY'],$_SESSION['MEMBER_IDX']);

    if ($date_from != null && $date_to != null) {
        $where .= "
            AND (DATE_FORMAT(OI.CREATE_DATE,'%Y-%m-%d') BETWEEN ? AND ?)
        ";

        $param_bind = array_merge($param_bind,array($date_from, $date_to));
    } else if ($date_from != null && $date_to == null) {
        $where .= "
            AND (DATE_FORMAT(OI.CREATE_DATE,'%Y-%m-%d') >= ?)
        ";
            
        array_push($param_bind,$date_from);
    } else if ($date_from == null && $date_to != null) {
        $where .= "
            AND (DATE_FORMAT(OI.CREATE_DATE,'%Y-%m-%d') <= ?)
        ";

        array_push($param_bind,$date_to);
    }

    if (empty($order_status)) {
        $where .= "
            AND (
                OI.ORDER_STATUS != 'DCP' AND
                CNT_REMAIN > 0
            )
        ";
    } else {
        $where .= "
            AND (
                OI.ORDER_STATUS = 'DCP' OR
                CNT_REMAIN = 0
            )
        ";
    }

    $json_result = array(
        'total'		=>$db->count($table,$where,$param_bind),
        'page'		=>$page
    );

    $select_order_info_sql = "
        SELECT
            OI.IDX                  AS ORDER_IDX,
            OI.ORDER_CODE           AS ORDER_CODE,
            OI.ORDER_TITLE          AS ORDER_TITLE,
            OI.ORDER_STATUS         AS ORDER_STATUS,
            
            DC.COMPANY_NAME         AS COMPANY_NAME,
            OI.DELIVERY_NUM         AS DELIVERY_NUM,
            IM.IMG_LOCATION         AS IMG_LOCATION,
            OI.PRICE_TOTAL          AS PRICE_TOTAL,
            DATE_FORMAT(
                    OI.CREATE_DATE,'%Y.%m.%d'
            )						AS CREATE_DATE,

            IFNULL(
                J_OP.CNT_REMAIN,0
            )                       AS CNT_REMAIN
        FROM
            ".$table."
		WHERE
			".$where."
		ORDER BY
			OI.IDX DESC
		LIMIT
			?,?
	";

    if ($rows != null) {
        $limit_start = (intval($page) - 1) * $rows;
    }

    $db->query($select_order_info_sql,array_merge($param_bind,array($limit_start,$rows)));

    $order_info	= array();
    $param_idx	= array();

    foreach ($db->fetch() as $data) {
        /* 주문교환 배송비 미결제 접수상품 삭제 */
        $cnt_ie = $db->count("ORDER_EXCHANGE OP","ORDER_CODE = ? AND ORDER_STATUS = 'OET'",array($data['ORDER_CODE']));
        $cnt_pe = $db->count("ORDER_PRODUCT_EXCHANGE OP","OP.ORDER_CODE = ? AND OP.ORDER_STATUS LIKE '%T' AND OP.PRODUCT_TYPE NOT REGEXP 'V|D'",array($data['ORDER_CODE']));

        if ($cnt_ie > 0 || $cnt_pe > 0) {
            initOrder_update($db,"OEX",$data['ORDER_CODE']);
        }

        /* 주문반품 배송비 미결제 접수상품 삭제 */
        $cnt_ir = $db->count("ORDER_REFUND OP","ORDER_CODE = ? AND ORDER_STATUS = 'ORT'",array($data['ORDER_CODE']));
        $cnt_pr = $db->count("ORDER_PRODUCT_REFUND OP","OP.ORDER_CODE = ? AND OP.ORDER_STATUS LIKE '%T' AND OP.PRODUCT_TYPE NOT REGEXP 'V|D'",array($data['ORDER_CODE']));

        if ($cnt_ir > 0 || $cnt_pr > 0) {
            initOrder_update($db,"ORF",$data['ORDER_CODE']);
        }
        
        $order_idx = $data['ORDER_IDX'];

        $param_idx[] = $order_idx;
        
        $t_order_status = "";
        if ($data['CNT_REMAIN'] > 0) {
            $t_order_status = setTXT_status($data['ORDER_STATUS']);
        } else {
            if ($data['ORDER_STATUS'] == "DCP") {
                $cnt_OC = $db->count("ORDER_CANCEL",  "ORDER_CODE = ?",array($data['ORDER_CODE']));
                $cnt_OE = $db->count("ORDER_EXCHANGE","ORDER_CODE = ?",array($data['ORDER_CODE']));
                $cnt_OR = $db->count("ORDER_REFUND",  "ORDER_CODE = ?",array($data['ORDER_CODE']));

                if ($cnt_OC > 0 && $cnt_OE == 0 && $cnt_OR == 0) {
                    $t_order_status = setTXT_status("OCC");
                }
            }   
        }
        
        $update_flg = false;
        if ($data['ORDER_STATUS'] == 'PCP' && $data['CNT_REMAIN'] > 0) {
            $update_flg = true;
        }

        $order_info[] = array(
            'order_idx'			=>$data['ORDER_IDX'],
            'order_code'		=>$data['ORDER_CODE'],
            'order_title'		=>$data['ORDER_TITLE'],
            't_order_status'    =>$t_order_status,
            'company_name'		=>$data['COMPANY_NAME'],		
            'delivery_num'		=>$data['DELIVERY_NUM'],
            'img_location'		=>$data['IMG_LOCATION'],
            'price_total'		=>number_format($data['PRICE_TOTAL']),
            'create_date'		=>$data['CREATE_DATE'],
            
            'update_flg'		=>$update_flg
        );
    }

	$json_result['data'] = array(
		'order_info' => $order_info,
	);
} else {
    $json_result['code'] = 401;
    $json_result['msg'] = getMsgToMsgCode($db,$_SERVER['HTTP_COUNTRY'],'MSG_B_ERR_0018',array());

    echo json_encode($json_result);
    exit;
}

/* 주문교환/반품 배송비 미결제 접수상품 삭제 */
function initOrder_update($db,$param_status,$order_code) {
	$table_I = array(
		'OEX'		=>"ORDER_EXCHANGE",
		'ORF'		=>"ORDER_REFUND",
	);
	
	$table_P = array(
		'OEX'		=>"ORDER_PRODUCT_EXCHANGE",
		'ORF'		=>"ORDER_PRODUCT_REFUND",
	);
	
	$status_D = array(
		'OEX'		=>"OET",
		'ORF'		=>"ORT"
	);
	
	$select_order_update_sql = "
		SELECT
			OP.IDX					AS OP_IDX,
			OP.ORDER_PRODUCT_CODE	AS ORDER_PRODUCT_CODE,
			OP.PRODUCT_QTY			AS PRODUCT_QTY
		FROM
			".$table_P[$param_status]." OP
		WHERE
			OP.ORDER_CODE = ? AND
			OP.ORDER_STATUS LIKE '%T' AND
			OP.PRODUCT_TYPE NOT REGEXP 'V|D'
	";
	
	$db->query($select_order_update_sql,array($order_code));
	
	foreach($db->fetch() as $data) {
		$op_idx				= $data['OP_IDX'];
		$order_product_code = $data['ORDER_PRODUCT_CODE'];
		$product_qty		= $data['PRODUCT_QTY'];
		
		$init_product_B_sql = "
			UPDATE
				ORDER_PRODUCT
			SET
				REMAIN_PRICE	= (PRODUCT_PRICE / PRODUCT_QTY) * (REMAIN_QTY + ?),
				REMAIN_QTY		= REMAIN_QTY + ?
			WHERE
				ORDER_PRODUCT_CODE = ?
		";
		
		$db->query($init_product_B_sql,array($product_qty,$product_qty,$order_product_code));
		
		$cnt_set = $db->count($table_P[$param_status],"PARENT_IDX = ?",array($op_idx));
		if ($cnt_set > 0) {
			$init_product_S_sql = "
				UPDATE
					ORDER_PRODUCT
				SET
					REMAIN_PRICE	= (PRODUCT_PRICE / PRODUCT_QTY) * (REMAIN_QTY + ?),
					REMAIN_QTY		= REMAIN_QTY + ?
				WHERE
					PARENT_IDX = ?
			";
			
			$db->query($init_product_B_sql,array($product_qty,$product_qty,$op_idx));
		}
	}
	
	$db->delete("ORDER_PRODUCT","ORDER_CODE = ? AND PRODUCT_TYPE = 'D' AND ORDER_STATUS = 'PWT'",array($order_code));
	$db->delete($table_I[$param_status],"ORDER_CODE = ? AND ORDER_STATUS = ?",array($order_code,$status_D[$param_status]));
	$db->delete($table_P[$param_status],"ORDER_CODE = ? AND ORDER_STATUS = ?",array($order_code,$status_D[$param_status]));
}

function setTXT_status($param_status) {
	$txt_status = "";

	$status = array(
		'KR'		=>array(
			'PCP'		=>"결제완료",
			'PPR'		=>"상품준비",
			'NDP'		=>"배송대기",
			'DPR'		=>"배송준비",
			'DPG'		=>"배송중",
			'DCP'		=>"배송완료",
			'OCC'		=>"주문취소",
			'OEX'		=>"교환접수",
			'OEH'		=>"수거완료",
			'OEP'		=>"교환완료",
			'OEE'		=>"교환철회",
			'ORF'		=>"반품접수",
			'ORH'		=>"수거완료",
			'ORP'		=>"반품완료",
			'ORE'		=>"반품철회",
		),
		'EN'			=>array(
			'PCP'		=>"Payment completed",
			'PPR'		=>"Product preparation",
			'NDP'		=>"Wating for shipping",
			'DPR'		=>"Shipping preparation",
			'DPG'		=>"Shipping in progress",
			'DCP'		=>"Shipping completed",
			'OCC'		=>"Order canceled",
			'OEX'		=>"Exchange received",
			'OEH'		=>"Product collected",
			'OEP'		=>"Exchange completed",
			'OEE'		=>"Exchange rejected",
			'ORF'		=>"Refund received",
			'ORH'		=>"Product collected",
			'ORP'		=>"Refund completed",
			'ORE'		=>"Refund rejected",
		)
	);
		
	if (isset($status[$_SERVER['HTTP_COUNTRY']][$param_status])) {
		$txt_status = $status[$_SERVER['HTTP_COUNTRY']][$param_status];
	}

		return $txt_status;
}

?>