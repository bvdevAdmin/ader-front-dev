<?php
/*=========================== 
	공통
  ===========================*/
$_TABLE['ADMIN']                        = 'ADMINISTRATOR';          // 사이트 관리자 
$_TABLE['ADMIN_PERMIT']                 = 'ADMINISTRATOR_PERMITION'; // 관리자 권한 정보
$_TABLE['POPUP']                        = 'POPUP';                  // 사이트 팝업
$_TABLE['FAQ']                          = 'FAQ';                    // FAQ 내용 정보
$_TABLE['FAQ_CATE']                     = 'FAQ_CATEGORY';           // FAQ 분류
$_TABLE['QNA']                          = 'QNA';                    // 회원 QnA 
$_TABLE['TERMS']                        = 'TERMS';                  // 사이트 약관
$_TABLE['OAUTH']                        = 'OAUTH';                  // API 인증

/** 통계 (MongoDB 이전, 구데이터) **/
$_TABLE['COUNTER']                      = 'COUNTER';                // 카운터 기록
$_TABLE['LOG']                          = 'LOG_202206';             // 방문 상세 기록

/** 회원 정보 **/
$_TABLE['MEMBER']                       = 'MEMBER';                 // 회원 정보
$_TABLE['MEMBER_DROP']                  = 'MEMBER_DROP';            // 탈퇴회원
$_TABLE['SNS']                          = 'MEMBER_SNS';             // SNS 계정 연동 회원
$_TABLE['MEMBER_LV']                    = 'MEMBER_LEVEL';           // 회원 그룹

/** 게시판 **/
$_TABLE['BOARD']                        = 'BOARD';                    // 전체 게시판
$_TABLE['BOARD_CONFIG']                 = 'BOARD_CONFIGURATION';      // 게시판 환경설정
$_TABLE['BOARD_CAT']                    = 'BOARD_CATEGORY';           // 게시판 내 분류 사용시 정보 기록

/** 웹컨텐츠 **/
$_TABLE['CONTENTS']                     = 'PAGE';                     // 컨텐츠 정보
$_TABLE['CONTENTS_DETAIL']              = 'PAGE_CONTENTS';            // 컨텐츠 별 디테일 페이지 기록

/** 이벤트 **/
$_TABLE['EVENT']                        = 'EVENT';                    // 이벤트 참여 기록
$_TABLE['EVENT_INFO']                   = 'EVENT_INFO';               // 이벤트 정보
$_TABLE['EVENT_CODE']                   = 'EVENT_ENTERCODE';          // 회원별 이벤트 참여고유 번호
$_TABLE['EVENT_CODE_ENTER']             = 'EVENT_ENTERCODE_LOGIN';    // 참여고유 번호 접근 기록

/** 블루마크 **/
$_TABLE['BLUEMARK']                     = 'BLUEMARK';                 // 블루마크, 바코드 발행 및 관리
$_TABLE['BLUEMARK_LOG']                 = 'BLUEMARK_HISTORY';         // 블루마크 인증, 이전 이력 관리

/** STOCKIST **/
$_TABLE['STOCKIST']                     = 'STOCKIST';                 // 전세계 매장 위치 정보
$_TABLE['STOCKIST_STORE']               = 'STOCKIST_STORE';           // 전세계 매장별 상세
$_TABLE['STOCKIST_IMAGE']               = 'STOCKIST_IMAGES';          // 매장 이미지 정보

/** 매장 출입 정보 **/
$_TABLE['ENTER']                        = 'OFFLINE_ENTERANCE';        // 오프라인 매장 출입 정보 (성수, 한남, 홍대 ~ 2022) 

/** 쇼핑몰 **/
$_TABLE['SHOP_CONFIG']					        = 'SHOP_CONFIGURATION';				// 환경설정

$_TABLE['SHOP_PRODUCT']					        = 'SHOP_PRODUCT';				// 상품정보

$_TABLE['SHOP_BANK']					= 'SHOP_BANK_INFORMATION';		// 은행 계좌 정보
$_TABLE['SHOP_CART']					= 'SHOP_CART';					// 장바구니
$_TABLE['SHOP_CART_OPTION']				= 'SHOP_CART_OPTION';			// 장바구니 선택한 옵션
$_TABLE['SHOP_CATEGORY']				= 'SHOP_CATEGORY';				// 상품 진열 정보 분류
$_TABLE['SHOP_COUPON']					= 'SHOP_COUPON';				// 쿠폰
$_TABLE['SHOP_SALECOUPON']				= 'SHOP_COUPON_PUBLISH';		// 쿠폰 발행
$_TABLE['SHOP_SHIPPING_ADDRESS']		= 'SHOP_CUSTOMER_SHIPPING_ADDRESS';	// 배송지 관리
$_TABLE['SHOP_DELIVERY_BOX']			= 'SHOP_DELIVERY_BOX';			// 택배 박스 설정
$_TABLE['SHOP_DELIVERY_COMPANY']		= 'SHOP_DELIVERY_COMPANY';		// 택배사 코드
$_TABLE['SHOP_DELIVERY_COST']			= 'SHOP_DELIVERY_COST';			// 택배 비용 설정
$_TABLE['SHOP_DELIVERY_EMS']			= 'SHOP_DELIVERY_COST_EMS';		// EMS 택배 비용 프리셋
$_TABLE['SHOP_DELIVERY_EMS_COUNTRY']	= 'SHOP_DELIVERY_COST_EMS_COUNTRY';		// EMS 택배 비용 국가설정
$_TABLE['SHOP_DELIVERY_MT']				= 'SHOP_DELIVERY_REMOTEAREA';	// 도서산간지역
$_TABLE['SHOP_GOODS']					= 'SHOP_GOODS';					// 상품 진열 정보
$_TABLE['SHOP_CUSTOMER_FAVORITE']		= 'SHOP_GOODS_FAVORITE';		// 즐겨찾기
$_TABLE['SHOP_CUSTOMER_RECENTLY']		= 'SHOP_GOODS_RECENTLYVIEW';	// 최근 본 상품
$_TABLE['SHOP_GOODS_RELATION']			= 'SHOP_GOODS_RELATION';		// 관련상품
$_TABLE['SHOP_ORDER']					= 'ORDERS';						// 주문 정보
$_TABLE['SHOP_ORDER_GOOD']				= 'ORDERS_GOODS';				// 주문 정보 (상품별)
$_TABLE['SHOP_ORDER_REJECT']			= 'ORDERS_REJECT';				// 반품 정보 관리
$_TABLE['SHOP_ORDER_REJECT_REASON']		= 'ORDERS_REJECT_REASON';		// 반품 사유 분류
$_TABLE['SHOP_ORDER_REVIEW']			= 'ORDERS_REVIEW';				// 주문상품 후기 
$_TABLE['SHOP_PG_RESULT']				= 'PG_PAYMENT_RESULT_LOG';		// 주문 정보 <- 몽고DB로 이전 예정
$_TABLE['SHOP_WARE']					= 'WAREHOUSE';					// 상품창고
$_TABLE['SHOP_WARE_CATEGORY']			= 'WAREHOUSE_CATEGORY';			// 상품창고 분류
$_TABLE['SHOP_WARE_DETAIL']				= 'WAREHOUSE_DETAIL';			// 상품 상세 내용
$_TABLE['SHOP_WARE_IMAGE']				= 'WAREHOUSE_IMAGES';			// 상품이미지
$_TABLE['SHOP_WARE_OPTION']				= 'WAREHOUSE_OPTION';			// 상품 옵션
$_TABLE['SHOP_WARE_OPTION_VAL']			= 'WAREHOUSE_OPTION_VALUES';	// 상품 옵션 선택 정보
$_TABLE['SHOP_WARE_PRICE']				= 'WAREHOUSE_PRICE';			// 상품 가격 정보

//NEW TABLE : 2022-07-06
$_TABLE['STORE']                        = 'STORE';                    //상점테이블
//NEW TABLE : 2022-07-07
$_TABLE['SHOP_PRODUCT']                 = 'SHOP_PRODUCT';             //상품테이블
$_TABLE['INNER_CATEGORY']               = 'INNER_CATEGORY';           //내부용 상품카테고리 테이블
$_TABLE['SHOP_PROD_CATE']               =  'SHOP_PROD_CATE';          //상점카테고리-상품 매핑 테이블
$_TABLE['STOCK_LIMIT']                  = 'STOCK_LIMIT';              //예약재고 테이블
$_TABLE['PRODUCT_IMAGE']                = 'PRODUCT_IMAGE';            //상품이미지 테이블
?>