<link rel="stylesheet" href="/css/mypage/orderlist.css">
<main>
	<div class="order-main-title">주문취소</div>
	<section class="order-detail-section noti">
		<p>
			취소 처리가 완료되었습니다.<br>
			취소가 완료된 주문은 상단의 [주문] 및 [취소] 메뉴에서 내역 확인이 가능합니다. <br>
			결제 수단에 따라 환불이 완료되기까지 1-3 영업일 소요될 수 있습니다.
		</p>
	</section>
	<section class="order-detail-section order-cancel-end">
		<div class="order-list-container">
			<div class="order-list-box">
				<div class="order-header">
					<div class="order-info">
						<div class="order-number"><span data-i18n="m_order_number">주문번호</span><a href=""><span class="order-number-value"></span></a></div>
						<div class="order-date"><span data-i18n="o_order_date">주문날짜</span><a href=""><span class="order-date-value"></span></a></div>
					</div>
				</div>
				<div class="order-body"></div>
			</div>
		</div>
	</section>
	<section class="order-detail-section order-cancel-end payment">
		<div class="order-detail-header"><span class="header-tilte">환불 내역</span></div>
		<div class="order-detail-body">
			<div class="order-detail-box payment-info">
				<div class="order-detail-row price_product"><span>제품합계</span><span class="price_product_val"></span></div>
				<div class="order-detail-row price_delivery"><span>배송비</span><span class="price_delivery_val"></span></div>
				<div class="order-detail-row price_discount"><span>바우처</span><span class="price_discount_val"></span></div>
				<div class="order-detail-row price_mileage_point"><span>적립포인트</span><span class="price_mileage_point_val"></span></div>
			</div>
		</div>
		<div class="order-detail-footer price_total"><span>합계</span><span class="price_total_val"></span></div>
		<div class="order-detail-btn bk go-home"><span class="header-tilte">홈으로 돌아가기</span></div>
		<div class="order-detail-btn wh go-cancel-list"><span class="header-tilte">취소 내역 목록 보기</span></div>
	</section>
</main>

<script src="/scripts/mypage/order/order-common.js"></script>
<script src="/scripts/mypage/order/order-cancel-end.js"></script>