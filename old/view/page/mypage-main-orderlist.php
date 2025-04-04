<link rel="stylesheet" href="/css/mypage/orderlist.css">
<div class="orderlist__wrap">
	<div class="tab__btn__container">
		<div class="tab__btn__item order_status_btn" action-type="ALL">
			<span data-i18n="o_order"></span>
		</div>

		<div class="tab__btn__item order_status_btn" action-type="OCC">
			<span data-i18n="o_cancel"></span>
		</div>

		<div class="tab__btn__item order_status_btn" action-type="OEX">
			<span data-i18n="o_exchange"></span>
		</div>

		<div class="tab__btn__item order_status_btn" action-type="ORF">
			<span data-i18n="o_return"></span>
		</div>
	</div>

	<input id="param_status" type="hidden" value="ALL">

	<div class="orderlist-calendar-wrap">
		<div class="orderlist-calendar">
			<div class="calendar-dropdown-warp">
				<div class="calendar-orderlist-start purchase-wrap dropdown">
					<div class="date-choice-btn start">
						<img class="orderlist-calendar-img" src="/images/mypage/mypage_calendar_icon.png">
						<span data-i18n="o_calendar_select"></span>
					</div>
					<input class="selected-date start" type="hidden">
					<div class="calendar">
						<div class="calendar-header">
							<button class="prev-month-btn">&lt;</button>
							<h2 class="current-month"></h2>
							<button class="next-month-btn">&gt;</button>
						</div>
						<div class="calendar-weekdays"></div>
						<div class="calendar-days"></div>
					</div>
				</div>
				<span class="calendar-hyphen"></span>
				<div class="calendar-orderlist-end purchase-wrap dropdown">
					<div class="date-choice-btn end">
						<img class="orderlist-calendar-img" src="/images/mypage/mypage_calendar_icon.png">
						<span data-i18n="o_calendar_select"></span>
					</div>
					<input class="selected-date end" type="hidden">
					<div class="calendar">
						<div class="calendar-header">
							<button class="prev-month-btn">&lt;</button>
							<h2 class="current-month"></h2>
							<button class="next-month-btn">&gt;</button>
						</div>
						<div class="calendar-weekdays"></div>
						<div class="calendar-days"></div>
					</div>
				</div>
			</div>
			<div class="date-search-btn" data-i18n="o_calendar_inquiry"></div>
		</div>
		<div class="orderlist-calendar-date-btn-wrap">
			<div class="calendar-date-btn" data-search_date="1W" data-i18n="o_one_week">1주일</div>
			<div class="calendar-date-btn" data-search_date="1M" data-i18n="o_one_month">1개월</div>
			<div class="calendar-date-btn" data-search_date="3M" data-i18n="o_three_months">3개월</div>
			<div class="calendar-date-btn" data-search_date="1Y" data-i18n="o_last_year">최근 1년</div>
		</div>
	</div>

	<div class="orderlist__tab__wrap tab_ALL">
		<div class="order__list order_list_ALL">
			<input type="hidden" name="rows" value="5">
			<input type="hidden" name="page" value="1">
			<div class="order-list-container">

			</div>
			<div class="orderlist__paging"></div>
		</div>
	</div>

	<div class="orderlist__tab__wrap tab_OCC hidden">
		<div class="order__list order_list_OCC hidden">
			<input type="hidden" name="rows" value="5">
			<input type="hidden" name="page" value="1">
			<div class="order-list-container">

			</div>
			<div class="orderlist__paging"></div>
		</div>
	</div>

	<div class="orderlist__tab__wrap tab_OEX hidden">
		<div class="order__list order_list_OEX hidden">
			<input type="hidden" name="rows" value="5">
			<input type="hidden" name="page" value="1">
			<div class="order-list-container">

			</div>
			<div class="orderlist__paging"></div>
		</div>
	</div>

	<div class="orderlist__tab__wrap tab_ORF hidden">
		<div class="order__list order_list_ORF hidden">
			<input type="hidden" name="rows" value="5">
			<input type="hidden" name="page" value="1">
			<div class="order-list-container">

			</div>
			<div class="orderlist__paging"></div>
		</div>
	</div>
	<div id="mypage_order_detail" class="order__detail hidden"></div>
</div>

<script src="/scripts/mypage/order/order-common.js"></script>
<script src="/scripts/mypage/order/order-list.js"></script>
<script src="/scripts/mypage/order/order-detail.js"></script>