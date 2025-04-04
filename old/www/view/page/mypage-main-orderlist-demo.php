
<link rel="stylesheet" href="/css/mypage/orderlist.css">
<div class="orderlist__wrap">
	<div class="orderlist__tab__btn__container">
		<div class="tab__btn__item" action-type="ALL">
			<span data-i18n="o_order">주문</span>
		</div>

		<div class="tab__btn__item" action-type="OC">
			<span data-i18n="o_cancel">취소</span>
		</div>

		<div class="tab__btn__item" action-type="OE">
			<span data-i18n="o_exchange">교환</span>
		</div>

		<div class="tab__btn__item" action-type="OR">
			<span data-i18n="o_return">반품</span>
		</div>
	</div>

	<input id="param_status" type="hidden" value="ALL">

	<div class="orderlist__tab__wrap tab_ALL">
		<div class="order__list order_list_ALL">
			<input type="hidden" name="rows" value="5">
			<input type="hidden" name="page" value="1">
			<div class="order-list-container">
				<div class="order-list-box">
					<div class="order-header">
						<div class="order-info">
							<div class="order-number"><span>주문번호</span><a href=""><span class="order-number-value">20230324-1679644919</span></a></div>
							<div class="order-date"><span>주문날짜</span><a href=""><span class="order-date-value">2023.03.24</span></a></div>
						</div>
						<div class="order-info-btn"><span>주문 상세 내역</span></div>
					</div>
					<div class="order-body">
						<div class="order-product-box">
							<a href=""><img class="order-product-img" src="https://s3-cloud-bucket-ader.s3.ap-northeast-2.amazonaws.com/s3-cloud-bucket-ader-user/images/product/img_BLAFWKV01BL_05_P_S_202210210000.jpg"></a>
							<ul>
								<div>
									<li class="product-name">Standic logo hoodie zip-up hoodie</li>
									<li class="product-price">329,000</li>
									<li class="product-color">Noir</li>
									<li class="product-size">A1</li>
								</div>
								<div>
									<li class="product-qty">Qty:<span class="qty-cnt">1</span></li>
								</div>
							</ul>
							<div class="order-status-box">
								<div class="order-status">결제완료</div>
								<div class="order-status-msg">제품 준비 중 상태로 변경될 경우 취소가 불가합니다.</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="orderlist__paging"></div>
		</div>
	</div>
</div>
<script src="/scripts/mypage/orderlist.js"></script>