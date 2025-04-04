<style>
	:root {
		--order-header--height: 150px;
		--header--content--gap: 50px;

		--solid-bk: #808080;
	}

	input::-webkit-outer-spin-button,
	input::-webkit-inner-spin-button {
		-webkit-appearance: none;
		margin: 0;
	}

	input {
		padding: 0;
		box-sizing: border-box;
	}

	ul,
	li {
		padding: 0;
		margin: 0;
	}

	input:focus {
		border: 0;
	}

	.content.rigth input:focus {
		caret-color: #343434;
		outline: 0;
	}

	body {
		font-family: var(--ft-no-fu);
		/* background-color: #222; */
		/* color: #ffffff; */
		color: var(--bk);
	}

	.mobile {
		display: none;
	}

	.hidden {
		display: none !important;
	}

	.tui-select-box-highlight {
		background: #f8f8f8 !important;
	}

	.tui-select-box-placeholder {
		font-family: var(--ft-no-fu);
		font-size: 11px;
		font-weight: normal;
		font-stretch: normal;
		font-style: normal;
		letter-spacing: normal;
		color: #dcdcdc;
		padding: 5px 0;
	}

	.tui-selected {
		color: #343434;
	}

	.tui-select-box-input:focus {
		outline: 0px;
	}

	.tui-select-box-input {
		border: 1px solid #808080;
		height: 39px;
	}

	#tmp_order_memo {
		border-top: 0px;
		padding: 10px;
		height: 90px;
		outline: none;
		resize: none;
		border-bottom: 1px solid #808080;
		border-left: 1px solid #808080;
		border-right: 1px solid #808080;
	}

	.tui-select-box-item {
		color: var(--bk);
		font-family: var(--ft-no-fu);
		font-size: 11px;
		padding: 3px 10px;
		border-bottom: 1px solid #eeeeee;
		height: 35px;
	}

	.order-section {
		display: grid;
		grid-template-columns: repeat(16, 1fr);
		margin-bottom: 200px;
	}

	.cn-box p {
		margin: 0;
	}

	.body-wrap {
		font-family: var(--ft-no-fu);
		font-size: 11px;
		font-weight: normal;
		font-stretch: normal;
		font-style: normal;
		line-height: normal;
		letter-spacing: normal;
		text-align: left;
		color: #343434;
	}

	.content.left {
		display: block;
		position: sticky;
		padding-top: var(--order-header--height);
		align-self: start;
		grid-column: 4/9;
	}

	/* 주문상품내역 */
	.order-product .hd-title {
		font-family: var(--ft-no-fu);
		font-size: 13px;
		font-weight: normal;
		font-stretch: normal;
		font-style: normal;
		line-height: normal;
		letter-spacing: normal;
		text-align: left;
		color: #343434;
	}

	.order-product .header-wrap {
		position: sticky;
	}

	.order-product .header-list {
		position: sticky;
		display: grid;
		grid-template-columns: 3fr 1fr 1fr 1fr;
		padding: 10px 0 20px;
	}

	.order-product .header-col {
		font-family: var(--ft-no-fu);
		font-size: 11px;
		font-weight: normal;
		font-stretch: normal;
		font-style: normal;
		line-height: normal;
		letter-spacing: normal;
		text-align: left;
		color: #343434;
	}

	.product .product-info {
		border: 0;
	}

	.product .product-info .info-row .name {
		font-family: var(--ft-no-fu);
		font-size: 11px;
		font-weight: normal;
		font-stretch: normal;
		font-style: normal;
		line-height: 1.55;
		letter-spacing: normal;
		text-align: left;
		color: #343434;
	}

	.product .color-title {
		font-family: var(--ft-no-fu);
		font-size: 11px;
		font-weight: 500;
		font-stretch: normal;
		font-style: normal;
		line-height: normal;
		letter-spacing: normal;
		text-align: left;
		color: #343434;
	}

	.product .list-row {
		font-family: var(--ft-no-fu);
		font-size: 11px;
		font-weight: normal;
		font-stretch: normal;
		font-style: normal;
		line-height: 1.55;
		letter-spacing: normal;
		text-align: left;
		color: #343434;
	}

	.product .size__box li {
		font-family: var(--ft-no-fu);
		font-size: 11px;
		font-weight: 500;
		font-stretch: normal;
		font-style: normal;
		line-height: normal;
		letter-spacing: normal;
		text-align: left;
		color: #343434;
	}

	.order-product .product-wrap {
		max-height: 410px;
		overflow-y: auto;
	}

	.order-product .body-list {
		display: grid;
		grid-template-columns: 3fr 1fr 1fr 1fr;
		width: 100%;
		max-height: 96px;
		border-bottom: 1px solid #dcdcdc;
		padding-bottom: 10px;
		margin-bottom: 10px;
	}

	.order-product .product-info {
		display: flex;
		padding: 0px;
		gap: 10px;
	}

	.order-product .product-info .info-row[data-refund="true"]::after {
		content: "교환 반품 불가";
		font-family: var(--ft-no-fu);
		font-size: 10px;
		font-weight: normal;
		font-stretch: normal;
		font-style: normal;
		line-height: 1.7;
		letter-spacing: normal;
		text-decoration: underline;
		text-align: left;
		color: #808080;
	}

	.order-product .product-info .info-box {
		display: flex;
		flex-direction: column;
		gap: 10px;
		justify-content: flex-start;
	}

	.order-product .product-info .info-row {
		gap: 3px;
		justify-content: flex-start;
	}

	.order-product .product-info .info-row.mobile-saleprice {
		display: none;
	}

	.order-product .web-saleprice {
		display: block;
	}

	.order-product .prd-img {
		/* max-width: 3.65vw; */
		max-width: 70px;
		max-height: 86px;
	}

	.order-product .product-toggle-btn {
		display: none;
	}

	.calculation-box {
		display: flex;
		flex-direction: column;
		gap: 20px;
		padding: 30px 0;
		border-bottom: 1px solid #dcdcdc;
	}

	.calculation-box .point-box {
		display: flex;
		flex-direction: column;
		gap: 20px;
	}

	.calculation-row {
		display: flex;
		justify-content: space-between;
	}

	.price_total {
		text-decoration: underline;
	}

	.total-price-wrap {
		display: flex;
		justify-content: space-between;
		padding-top: 30px;
	}


	.content.rigth {
		/* background-color: brown; */
		background-color: #ffffff;
		padding-top: var(--order-header--height);
		/* margin-top: var(--header--content--gap); */
		grid-column: 10/14;
		display: grid;
	}

	.content.rigth li,
	.content.rigth liul {
		list-style: disc;
	}

	.content.rigth input {
		border: 1px solid #808080;
	}


	.wrapper {
		width: 100%;
	}

	.wrapper[data-group="1"] {
		border-bottom: 1px solid #dcdcdc;
		padding-bottom: 20px;
		margin-bottom: 40px;
		display: none;
	}

	.wrapper[data-group="1"].next {
		display: block;
	}

	.wrapper[data-group="1"] .header-wrap {
		border: 0px;
	}

	.wrapper[data-group="1"] .point-row {
		padding-bottom: 10px;
	}

	.wrapper[data-group="1"] .get-point {
		padding-bottom: 10px;
	}

	.header-wrap {
		display: flex;
		justify-content: space-between;
		align-items: center;
		border-bottom: 1px solid #dcdcdc;
		color: #343434;
		font-size: 13px;
		font-family: var(--ft-no-fu);
		font-weight: normal;
		font-stretch: normal;
		font-style: normal;
		line-height: normal;
		letter-spacing: normal;
	}

	.header-box-btn {
		cursor: pointer;
		display: flex;
		justify-content: center;
		align-items: center;
		gap: 10px;
		height: 40px;
	}

	.header-box-top {
		padding: 20px 0 10px;
	}

	.header-box {
		padding: 10px 0;
		height: 40px;
	}

	.header-btn {
		cursor: pointer;
		display: flex;
		justify-content: center;
		align-items: center;
		color: var(--bk);
		height: 24px;
		padding: 0 15px;
		border-radius: 2px;
		border: solid 1px #dcdcdc;
		font-family: var(--ft-no-fu);
		font-size: 11px;
		font-weight: normal;
		font-stretch: normal;
		font-style: normal;
		line-height: normal;
		letter-spacing: normal;

	}

	.banner-wrap {
		display: grid;
		grid-template-columns: repeat(16, 1fr);
		position: fixed;
		width: 100%;
		height: var(--order-header--height);
		background-color: #ffffff;
		z-index: 10;
	}

	.banner-wrap .banner-box {
		display: flex;
		align-items: center;
		grid-column: 4/17;
		font-family: var(--ft-no-fu);
		font-size: 13px;
		font-weight: normal;
		font-stretch: normal;
		font-style: normal;
		line-height: normal;
		letter-spacing: normal;
		text-align: left;
		color: #343434;

	}

	.cn-box {
		display: flex;
		flex-direction: column;
		gap: 10px;
		padding-top: 10px;
	}

	.check-row {
		display: flex;
		gap: 10px;
		margin-bottom: 10px;
		justify-content: space-between;
	}

	.check-row input[type="checkbox"] {
		width: 10px;
		height: 10px;
		margin-right: 10px;
		cursor: pointer;
	}

	.calculation-wrap {
		font-family: var(--ft-no-fu);
		font-size: 11px;
		font-weight: normal;
		font-stretch: normal;
		font-style: normal;
		line-height: 1.55;
		letter-spacing: normal;
		text-align: left;
		color: #343434;
	}

	/* 바우처 */
	.voucher-info {
		margin-bottom: 40px;
	}

	.voucher-info-list {
		display: flex;
		gap: 9px;
		flex-direction: column;
		font-family: var(--ft-no-fu);
		font-size: 11px;
		font-weight: normal;
		font-stretch: normal;
		font-style: normal;
		line-height: 1.64;
		letter-spacing: normal;
		text-align: left;
		color: #343434;
		margin-top: 10px;
	}

	.voucher-select-box {
		border: 1px solid #808080;
		height: 39px;
	}

	.voucher-select-box-placeholder {
		font-family: var(--ft-no-fu);
		font-size: 11px;
		font-weight: normal;
		font-stretch: normal;
		font-style: normal;
		letter-spacing: normal;
		color: #dcdcdc;
		padding: 5px 0;
	}

	.voucher-select-box-highlight {
		background: #f8f8f8 !important;
	}

	.voucher-selected {
		color: #343434;
	}

	.voucher-select-box-input:focus {
		outline: 0px;
	}

	.voucher-select-box-input {
		border: 1px solid #808080;
		height: 39px;
	}

	/* 포인트 */
	.point-row {
		display: grid;
		grid-template-columns: repeat(4, 1fr);
		gap: 10px;
	}

	.point-row input {
		grid-column: 1/4;
	}

	.point-row input ::placeholder {
		color: #dcdcdc;
	}

	.point-row .mileage_point_btn {
		cursor: pointer;
		grid-column: 4/5;
	}

	.point-row .point-btn {
		cursor: pointer;
		grid-column: 4/5;
	}

	.mileage_point_btn {
		cursor: pointer;
		display: flex;
		justify-content: center;
		align-items: center;
		height: 40px;
		border-radius: 1px;
		background-color: #dcdcdc;
	}

	.charge_point_btn {
		cursor: pointer;
		display: flex;
		justify-content: center;
		align-items: center;
		height: 40px;
		border-radius: 1px;
		background-color: #dcdcdc;
	}

	.reserves-info-list {
		display: flex;
		gap: 10px;
		flex-direction: column;
		font-family: var(--ft-no-fu);
		font-size: 11px;
		font-weight: normal;
		font-stretch: normal;
		font-style: normal;
		line-height: 1.36;
		letter-spacing: normal;
		text-align: left;
		color: #343434;
	}

	.charge-info .body-wrap {
		font-family: var(--ft-no-fu);
		font-size: 11px;
		font-weight: normal;
		font-stretch: normal;
		font-style: normal;
		line-height: normal;
		letter-spacing: normal;
		text-align: left;
		color: #343434;
	}

	.charge-btn {
		cursor: pointer;
		display: flex;
		justify-content: center;
		align-items: center;
		width: 99%;
		max-width: 100%;
		height: 40px;
		margin: 20px 0;
		border-radius: 1px;
		border: solid 1px #dcdcdc;
		font-family: var(--ft-no-fu);
		font-size: 12px;
		font-weight: normal;
		font-stretch: normal;
		font-style: normal;
		line-height: normal;
		letter-spacing: normal;
		text-align: left;
		color: #343434;
	}

	/* 주문자정보 */
	.wrapper.member_info {
		margin-bottom: 60px;
	}

	/* 배송지정보 */
	.address-info {
		padding-bottom: 40px;
	}

	.address-info .message-box {
		display: flex;
		flex-direction: column;
		gap: 10px;
	}

	.address-info .cn-box {
		padding-bottom: 20px;
	}

	.address-info .confirm-text.check::after {
		content: attr(data-content);
		font-family: var(--ft-no-fu);
		font-size: 11px;

		font-weight: normal;
		font-stretch: normal;
		font-style: normal;
		line-height: normal;
		letter-spacing: normal;
		text-align: right;
		position: absolute;
		right: 0;
		bottom: 40px;
		color: #ff0000;
		text-decoration: underline;
	}

	.edit-box .input-row {
		display: flex;
		flex-direction: column;
		gap: 10px;
		margin-bottom: 10px;
		position: relative;
	}

	.edit-box .input-row.addr-search {
		display: grid;
		grid-template-columns: repeat(4, 1fr);
		gap: 10px;
		align-items: end;
	}

	.edit-box .input-row.addr-search .input-text {
		grid-column: 1/4;
		display: flex;
		flex-direction: column;
		gap: 10px;
		margin-bottom: 0px;
	}

	.edit-box .input-row.addr-search .input-text input {
		height: 40px;
	}

	.addr-search-btn {
		cursor: pointer;
		grid-column: 4/5;
		display: flex;
		justify-content: center;
		align-items: center;
		color: var(--bk);
		height: 40px;
		padding: 0 15px;
		border-radius: 2px;
		border: solid 1px #dcdcdc;
	}

	.keyword_label {
		display: none;
	}

	.search_button {
		grid-column: 4/5;
		display: flex;
		justify-content: center;
		align-items: center;
		color: var(--bk);
		height: 40px;
		padding: 0 15px;
		border-radius: 2px;
		border: solid 1px #dcdcdc;
	}

	.edit-box .input-row input {
		height: 40px;
		padding-left: 10px;
		border-radius: 1px;
		border: solid 1px #808080;
		vertical-align: middle;
	}

	.edit-box .check-row {
		display: flex;
		gap: 10px;
		margin-bottom: 10px;
		justify-content: space-between;
	}

	.save-btn {
		cursor: pointer;
		display: flex;
		justify-content: center;
		align-items: center;
		color: var(--bk);
		height: 24px;
		padding: 0 15px;
		border-radius: 2px;
		border: solid 1px #dcdcdc;
	}

	.to-place {
		margin-bottom: 20px;
		text-decoration: underline;
	}

	.to_place {
		text-decoration: underline;
	}

	.tmp_order_memo {
		display: none;
		width: 100%;
	}


	/* 약관동의 */

	.terms-info-list {
		display: flex;
		gap: 10px;
		flex-direction: column;
		padding: 10px 0;
	}

	.terms-service {
		font-family: var(--ft-no-fu);
		font-size: 11px;
		font-weight: normal;
		font-stretch: normal;
		font-style: normal;
		line-height: 1.64;
		letter-spacing: normal;
		text-align: left;
		color: #343434;
		border-top: 1px solid #dcdcdc;
		padding-top: 40px;
	}


	input[type="checkbox"]:checked {
		background: #000000;
	}


	/* 스탭 버튼 */


	.step-btn-wrap {
		display: flex;
		justify-content: space-between;
		gap: 10px;
		margin-top: 10px;
	}

	.step-btn {
		cursor: pointer;
		height: 40px;
		width: 50%;
		display: flex;
		justify-content: center;
		align-items: center;
		font-family: var(--ft-no-fu);
		font-size: 11px;
		font-weight: normal;
		font-stretch: normal;
		font-style: normal;
		line-height: normal;
		letter-spacing: normal;
		text-align: left;

	}

	.step-btn.pre {
		background-color: var(--wh);
		color: var(--bk);
		border: solid 1px var(--solid-bk);
	}

	.step-btn.next {
		background-color: var(--bk-dark);
		color: var(--wh);
		border: solid 1px var(--bk-dark);
	}


	#postcodify {
		margin: 0;
		max-height: 400px;
		overflow: auto;
	}

	#postcodify .keyword {
		grid-column: 1/4;
	}

	#postcodify .search_button {
		grid-column: 4/5;
	}

	.post-change-result {
		width: 74.5%;
		margin: 0 !important;
		background-color: #fff;
		overflow: auto;
		max-height: 300px;
		border: 1px solid #808080;
		border-top: 0px;
		top: -32px;
	}

	.postcodify_search_controls {
		display: grid;
		gap: 10px;
		grid-template-columns: repeat(4, 1fr);
	}

	.postcodify_search_result .code5 {
		color: var(--bk);
	}

	.postcodify_search_result .addr-row {
		color: #dcdcdc;
	}

	.postcodify_search_result .old_addr-row {
		color: #dcdcdc;
	}

	/**배송지 목록---------------------------------- */
	.list-box.hidden {
		display: none;
	}

	.list-box {
		position: relative;
		border: 1px solid #808080;
		background-color: #ffffff;
	}

	.list-box .cn-box {
		position: relative;
		margin-top: 8px;
		display: flex;
		flex-direction: column;
		gap: 8px;
		width: 100%;

	}

	.list-box .addrList-content {
		padding: 30px;
		cursor: pointer;
	}

	.list-box .addrList-content:hover {
		background-color: #f8f8f8;
	}

	.list-box .addrList-header {
		padding: 20px;
		display: flex;
		justify-content: space-between;
		font-size: 11px;
		color: #343434;
	}

	.list-box .addrList-row {
		align-items: center;
		display: flex;
		gap: 3px;
	}

	.order-section [type="checkbox"]:checked {
		background-color: #000000;
	}

	.order-section [type="checkbox"] {
		vertical-align: middle;
		appearance: none;
		border: 1px solid gray;
		width: 10px;
		height: 10px;
		margin: 0px;
		padding: 0px;
	}

	.addr-list-wrap {
		display: flex;
		justify-content: space-between;
	}

	/**배송지 목록---------------------------------- */
	@media (max-width: 1025px) {
		.header-wrap.order-list {
			border-bottom: 0;
		}

		.header-box-top {
			display: flex;
			width: 100%;
			justify-content: space-between;
		}

		.mobile {
			display: inline-block;
		}

		.banner-wrap {
			display: flex;
			height: 37px;
			top: 40px;
			padding-left: 10px;
		}

		.product .product-info {
			height: auto;
		}

		.order-section {
			display: flex;
			flex-direction: column;
			padding: 0 10px;
		}

		.content.left {
			position: static;
			width: 100%;
			display: none;
		}

		.content.rigth {
			padding-top: 47px;
		}

		.content.mobile {
			margin-bottom: 10px;
		}

		.content.mobile li,
		ul {
			list-style: none;
		}

		.terms-service {
			order: 7;
		}

		.step-btn-wrap {
			order: 8;
		}

		.order-product .header-box {
			display: flex;
			justify-content: space-between;
			align-items: center;
			flex-grow: 1;
		}

		.order-product .product-toggle-btn {
			cursor: pointer;
			display: flex;
			justify-content: center;
			align-items: center;
			color: var(--bk);
			height: 24px;
			padding: 0 15px;
			border-radius: 2px;
			border: solid 1px #dcdcdc;
			font-family: var(--ft-no-fu);
			font-size: 11px;
			font-weight: normal;
			font-stretch: normal;
			font-style: normal;
			line-height: normal;
			letter-spacing: normal;
		}

		.order-product .body-list {
			grid-template-columns: 4fr 1fr 1fr;
		}

		.order-product .product-info .info-row.mobile-saleprice {
			display: block;
		}

		.order-product .web-saleprice {
			display: none;
		}

		.order-product .product-wrap {
			/* border-bottom: solid 1px #dcdcdc; */
			margin-bottom: 20px;
		}

		.order-product .product-count {
			display: flex;
			height: 100%;
			align-items: flex-end;
			justify-content: flex-end;
		}

		.order-product .price_total {
			display: flex;
			height: 100%;
			align-items: flex-end;
			justify-content: flex-end;
		}

		.order-product .product-count::before {
			content: "Qty:";
		}

		.calculation-box {
			padding: 0;
			border-bottom: 0px;
		}

		.total-price-wrap {
			padding-top: 10px;
			border-top: solid 1px #dcdcdc;
		}

		.post-change-result {
			width: 100%;
		}

		.price_total {
			text-decoration: underline;
		}
	}
</style>

<?php
$member_idx = 0;
if (isset($_SESSION['MEMBER_IDX'])) {
	$member_idx = $_SESSION['MEMBER_IDX'];
}

if ($member_idx == 0 || $basket_idx == null) {
	echo "
			<script>
				location.href='/main';
			</script>
		";
} else {
	$basket_cnt = $db->count("BASKET_INFO", "IDX IN (" . $basket_idx . ") AND MEMBER_IDX = " . $member_idx . " AND DEL_FLG = FALSE");

	$tmp_arr = explode(",", $basket_idx);

	if ($basket_cnt == 0 || count($tmp_arr) != $basket_cnt) {
		echo "
				<script>
					location.href='/main';
				</script>
			";
	}
}
?>

<link rel=stylesheet href='/scripts/static/postcodify-master/api/search.css' type='text/css'>

<main data-basketStr="<?= $basket_idx ?>">
	<div id="payment-method"></div>
	
	<div class="banner-wrap">
		<div class="banner-box">
			<span>결제하기</span>
		</div>
	</div>
	<section class="order-section">
		<div class="content left web">
			<div class="wrapper order-product">
				<div class="header-wrap order-list">
					<div class="header-box-top">
						<span class="hd-title">주문내역</span>
						<div class="product-toggle-btn"><span>자세히보기</span></div>
					</div>
				</div>
				<div class="header-list">
					<div class="header-col prd-col"><span>제품</span></div>
					<div class="header-col price-col"><span>가격</span></div>
					<div class="header-col qty-col"><span>수량</span></div>
					<div class="header-col sum-col"><span>합계</span></div>
				</div>
				<div class="body-wrap">

					<div class="calculation-wrap">
						<div class="calculation-box">
							<div class="price_product_wrap calculation-row">
								<span>제품 합계</span>
								<span class="price_product" data-price_product="0">0</span>
							</div>

							<div class="point-box hidden">
								<div class="calculation-row">
									<span>바우처 사용</span>
									<span class="price_discount" data-price_discount="0">0</span>
								</div>
								<div class="calculation-row">
									<span>적립 포인트 사용</span>
									<span class="price_mileage_point" data-price_mileage_point="0">0</span>
								</div>
								<div class="calculation-row">
									<span>충전 포인트 사용</span>
									<span class="price_charge_point" data-price_charge_point="0">0</span>
								</div>
							</div>

							<div class="calculation-row">
								<span>배송비</span>
								<span class="price_delivery" data-price_delivery="5000">5,000</span>
							</div>
						</div>
						<div class="total-price-wrap">
							<div class="total-box">
								<span>총 합계(Qty:</span>
								<span class="product-qty"></span>)
							</div>
							<span class="price_total">0</span>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="content rigth">
			<div class="wrapper voucher-info" data-group="1">
				<div class="header-wrap">
					<div class="header-box-top">
						<span class="hd-title">바우처</span>
					</div>
				</div>
				<div class="body-wrap">
					<div class="voucher-select-box"></div>
					<div class="voucher-info-list hidden">
						<p>·&nbsp;바우처는 적립포인트와 중복 사용이 불가능합니다.</p>
						<p>·&nbsp;바우처보다 제품 금액이 높을 시, 충전포인트와 중복 사용 또는 선택적 사용이 가능합니다.</p>
						<p>·&nbsp;바우처보다 제품 금액이 낮을 시에는 반환금액이 없습니다.</p>
					</div>
				</div>
			</div>
			<div class="wrapper reserves-info" data-group="1">
				<div class="header-wrap">
					<div class="header-box">
						<span class="hd-title">적립 포인트</span>
					</div>
				</div>
				<div class="body-wrap">
					<div class="point-row">
						<input type="text" id="use_mileage" placeholder="사용하실 보유 적립 포인트를 입력해주세요."
							style="padding-left:10px;">
						<div class="mileage_point_btn" onclick="getTotalMileage(true)"><span
								class="mileage_point_msg">모두적용</span></div>
					</div>
					<div class="get-point reserves">
						<span>보유 적립 포인트</span>
						<span id="txt_total_mileage" style="margin-left:5px;"></span>
					</div>
					<div class="reserves-info-list hidden">
						<p>·&nbsp;주문으로 발생한 적립 포인트는 배송완료 후 7일 부터 실제 사용 가능한 적립 포인트로 전환됩니다. 배송
							완료 시점으로부터 7일 동안은 미가용 적립 포인트로 분류됩니다.</p>
						<p>·&nbsp;미가용 적립 포인트는 반품, 구매취소 등을 대비한 임시 적립 포인트로 사용가능 포인트로 전환되기까지
							상품구매에 사용하실 수 없습니다.</p>
						<p>·&nbsp;사용가능 적립 포인트(총 적립 포인트, 사용된 적립 포인트, 미가용 적립 포인트)는 상품구매 시 바로 사용
							가능합니다.</li>
						<p>·&nbsp;2019.07.01부터 개정 시행되는 특정 금융거래정보법에 따라 계좌 미연결한 고객은 적립 포인트 최대 50
							만원 및 충전 포인트 최대 50만원까지 보유가능합니다.</p>
						<p>·&nbsp;적립 포인트는 예치금과 이니시스 결제 시에만 적립됩니다.</p>
					</div>
				</div>
			</div>
			<div class="wrapper charge-info" data-group="1">
				<div class="header-wrap">
					<div class="header-box">
						<span class="hd-title">충전 포인트</span>
					</div>
				</div>
				<div class="body-wrap">
					<div class="point-row">
						<input type="text" id="use_mileage" placeholder="사용하실 보유 적립 포인트를 입력해주세요."
							style="padding-left:10px;">
						<div class="charge_point_btn"><span>모두적용</span></div>
					</div>
					<div class="get-point charge">
						<span>보유 충전 포인트</span>
						<span id="txt_total_charge" style="margin-left:5px;">0</span>
					</div>
					<div class="charge-btn"><span>충전하기</span></div>
					<div class="reserves-info-list">
						<p>·&nbsp;충전 포인트는 현금과 동일하게 사용 가능합니다.</p>
						<p>·&nbsp;충전 포인트 사용 시 적립 포인트 3% 추가 적립이 적용됩니다.</p>
					</div>

				</div>
			</div>

			<div class="wrapper member_info" data-group="2">
				<div class="header-wrap">
					<div class="header-box-top">
						<span class="hd-title">주문자 정보</span>
					</div>
				</div>
				<div class="body-wrap">
					<div class="cn-box">
						<p class="member_name"></p>
						<p class="member_mobile"></p>
						<p class="member_email"></p>
					</div>
				</div>
			</div>

			<div class="wrapper address-info" data-group="3">
				<div class="header-wrap">
					<div class="header-box">
						<span class="hd-title">배송지정보</span>
					</div>

					<div class="header-box-btn">
						<div class="header-btn edit-btn">수정</div>
						<div class="header-btn list_addr_btn" onclick="listAdd()">배송지목록</div>
					</div>
				</div>
				<div class="body-wrap">
					<div class="edit-box hidden">
						<div class="input-row">
							<span data-content="필수 입력란 입니다." class="confirm-text">배송지명</span>
							<input type="text" class="tmp_to_place" name="tmp_to_place" placeholder="예)집">
						</div>
						<div class="input-row">
							<span data-content="필수 입력란 입니다." class="confirm-text">수령자</span>
							<input type="text" class="tmp_to_name" name="tmp_to_name" placeholder="이름">
						</div>
						<div class="input-row">
							<span data-content="휴대전화 번호를 정확하게 기입해주세요" class="confirm-text">휴대전화</span>
							<input oninput="phoneAutoHyphen(this);" maxlength="13" type="text" class="tmp_to_mobile"
								name="tmp_to_mobile" placeholder="(-)없이 숫자만 입력">
						</div>

						<div class="input-row addr-search">
							<div class="input-text">
								<span>배송지 검색</span>
							</div>
						</div>

						<div id="postcodify" class="input-row"></div>
						<div class="input-row">
							<div class="post-change-result"></div>
							<span>상세주소</span>
							<input class="tmp_to_detail_addr" value="" />
						</div>

						<input type="hidden" class="tmp_to_zipcode" value="">
						<input type="hidden" class="tmp_to_road_addr" value="">
						<input type="hidden" class="tmp_to_lot_addr" value="">


						<div class="check-row">
							<div class="check-text">
								<input type="checkbox" class="add_flg">
								<span>배송지 목록에 추가</span>
							</div>
							<div class="save-btn"><span>저장</span></div>
						</div>
					</div>

					<div class="save-box">
						<!-- <div class="to-place"></div> -->

						<!-- 배송지 정보 표시 -->
						<div class="cn-box">
							<p class="to_place"></p>
							<p class="to_name"></p>
							<p class="to_mobile"></p>
							<p class="to_zipcode"></p>
							<p class="to_addr"></p>
							<p class="to_detail_addr"></p>
						</div>

						<div class="message-box">
							<span class="hd-title">배송메시지</span>
							<div class="edit-message-box">
								<div class="addr-message-select-box"></div>
								<textarea placeholder="내용을 입력해주세요.(최대 50자)" id="tmp_order_memo" class="tmp_order_memo"
									type="text"></textarea>
							</div>
							<div class="save-message-box hidden">
								<p class="message-content"></p>
							</div>
						</div>
					</div>

					<div class="list-box hidden">
						<div class="addrList-header">
							<div>배송지 목록</div>
							<div class="close" onClick="closeBox()"><img src="/images/mypage/tmp_img/X-12.svg"></div>
						</div>
						<div class="addrList-body"></div>
					</div>
				</div>
			</div>

			<div class="terms-service hidden" data-group="4">
				<div class="terms-info-list">
					<p>·&nbsp;최소 결제 가능 금액은 결제금액에서 배송비를 제외한 금액입니다.</p>
					<p>·&nbsp;소액 결제의 경우 PG사 정책에 따라 결제 금액 제한이 있을 수 있습니다.</p>
				</div>
				<div class="check-row">
					<div class="check-text">
						<input onclick="checkboxAll(this)" type="checkbox" class="check-all">
						<label for=""> <span>전체 선택</span></label>
					</div>
				</div>
				<div class="check-row">
					<div class="check-text">
						<input onclick="essentialCheckBox(this)" type="checkbox" class="check-self essential">
						<label for="">
							<span style="text-decoration: underline;"
								onclick="window.open('tice/privacy?notice_type=terms_of_use');">이용약관</span>,&nbsp;
							<span style="text-decoration: underline;"
								onclick="window.open('tice/privacy?notice_type=privacy_policy');">
								개인정보처리방침</span>에 동의합니다. (필수)</span></label>
					</div>
				</div>
				<div class="check-row">
					<div class="check-text">
						<input type="checkbox" class="check-self">
						<label for="">
							<span>뉴스레터 발송, 맞춤 서비스 및 이벤트 제공, 신규 서비스 개발 등 서비스 품질
								향상을 위한 마케팅 정보 수신 및 활용에 동의합니다. (선택)</span></label>
					</div>
				</div>
			</div>
			<div class="content mobile"></div>
			<div class="select-box"></div>
			<div class="step-btn-wrap">
				<div class="step-btn pre" data-step="0"><a><span>이전단계</span></a></div>
				<div class="step-btn next" data-step="1"><span>다음단계</span></div>
			</div>
		</div>
	</section>
</main>

<form id="frm-check" action="/order/check" style="display:none;" method="POST">
	<input id="basket_idx"			type="hidden" name="basket_idx"				value="<?= $basket_idx ?>">

	<input id="to_place"			type="hidden" name="to_place"				value="">
	<input id="to_name"				type="hidden" name="to_name"				value="">
	<input id="to_mobile"			type="hidden" name="to_mobile"				value="">
	<input id="to_zipcode"			type="hidden" name="to_zipcode"				value="">
	<input id="to_lot_addr" 		type="hidden" name="to_lot_addr"			value="">
	<input id="to_road_addr"		type="hidden" name="to_road_addr"			value="">
	<input id="to_detail_addr"		type="hidden" name="to_detail_addr"			value="">
	<input id="order_memo"			type="hidden" name="order_memo"				value="">
	
	<input id="voucher_idx"			type="hidden" name="voucher_idx"			value="0">

	<input id="price_mileage_point" type="hidden" name="price_mileage_point"	value="0">
	<input id="price_charge_point"	type="hidden" name="price_charge_point"		value="0">
</form>

<script src="https://js.tosspayments.com/v1/payment-widget"></script>

<script>
const clientKey = "test_ck_YZ1aOwX7K8meL9vyEe98yQxzvNPG";
//const clientKey = "test_ck_D5GePWvyJnrK0W0k6q8gLzN97Eoq";		//결제위젯용
let tossPayments = TossPayments(clientKey);

const customerKey = "<?=$_SESSION['MEMBER_ID']?>";
const paymentWidget = PaymentWidget(clientKey,customerKey);  // 결제위젯 초기화

$(document).ready(function() {
	//paymentWidget.renderPaymentMethods('#payment-method', 15000);
})

let total_qty = 0;
window.addEventListener("resize", function () {
	resizeEvent();
});

function resizeEvent() {
	const chageOrderWrap = document.querySelector(".content .order-product");
	const webContent = document.querySelector(".content.web");
	const mobileContent = document.querySelector(".content.mobile");
	const bodyWidth = document.querySelector("body").offsetWidth;
	if (1024 <= bodyWidth) {
		webContent.appendChild(chageOrderWrap);
		prdToggleBtn.classList.add("hidden");
		document.querySelector(".order-product").querySelector(".header-list").classList.remove("hidden");
	} else if (1024 >= bodyWidth) {
		document.querySelector(".order-product").querySelector(".header-list").classList.add("hidden");
		prdToggleBtn.classList.remove("hidden");
		mobileContent.appendChild(chageOrderWrap);
	}
}

document.addEventListener("DOMContentLoaded", function () {
	const urlParams = new URL(location.href).searchParams;
	const param_value = urlParams.get('basket_idx');
	let basket_idx = param_value.split(",");

	const postResult = document.createElement("div")
	postResult.classList.add("post-result");
	document.getElementById("postcodify").appendChild(postResult);

	$.ajax({
		type: "post",
		url: "pi/order/pg/get",
		data: {
			"basket_idx": basket_idx,
		},
		dataType: "json",
		error: function () {
			console.log("결제하기에 화면정보 조회처리에 실패했습니다.");
		},
		success: function (d) {
			let code = d.code;
			if (code == 200) {
				let data = d.data[0];
				if (data.order_to_info.length == 0) {
					let put_addr = document.querySelector(".edit-box");
					put_addr.classList.remove("hidden");
				} else {
					getAddrInfo(data.order_to_info);
				}
				getOrderPgInfoList(data.product_info);
				getMemberInfo(data.member_info);
				
				let voucher_cnt = data.voucher_cnt;
				let voucher_info = data.voucher_info;
				setVoucherInfoList(data.voucher_cnt, data.voucher_info);

				getTotalMileage(false);
			} else {
				exceptionHandling("디자인 필요", d.msg);
			}
		}
	});
	orderMemoChangeEvent();
	resizeEvent();
});

function getOrderPgInfoList(product) {
	let domFrag = document.createDocumentFragment();
	const orderWrapBody = document.querySelector(".order-product .body-wrap");
	let width = document.querySelector("body").offsetWidth;
	let wrap = document.createElement("div");
	wrap.classList.add("product-wrap");
	if (width <= 1024) {
		wrap.classList.add("hidden");
	}

	let listHtml = "";
	product.forEach(el => {
		let product_qty = parseInt(el.product_qty);
		total_qty += product_qty;

		let sales_price = el.sales_price.toLocaleString('ko-KR')
		let total_price = el.total_price.toLocaleString('ko-KR')

		listHtml += "";
		listHtml += '<div class="body-list product">';
		listHtml += '    <div class="product-info">';
		listHtml += '        <a href="" class="docs-creator"><img class="prd-img" cnt="1" src="' + img_root + el.img_location + '" alt=""></a>';
		listHtml += '        <div class="info-box">';
		listHtml += '            <div class="info-row" data-refund="' + el.refund_flg + '">';
		listHtml += '                <div class="name" data-soldout=""><span>' + el.product_name + '</span></div>';
		listHtml += '            </div>';
		listHtml += '            <div class="info-row mobile-saleprice">';
		listHtml += '                <div class="product-price">' + sales_price + '</div>';
		listHtml += '            </div>';
		listHtml += '            <div class="info-row">';
		listHtml += '                <div class="color-title"><span>' + el.color + '</span></div>';
		listHtml += '                <div class="color__box" data-maxcount="" data-colorcount="1">';
		listHtml += '                    <div class="color" data-color="' + el.color_rgb + '" data-productidx="1" data-soldout="STIN" style="background-color:' + el.color_rgb + '"></div>';
		listHtml += '                </div>';
		listHtml += '            </div>';
		listHtml += '            <div class="info-row">';
		listHtml += '                <div class="size__box">';
		listHtml += '                    <li class="size" data-sizetype="" data-productidx="1" data-optionidx="1" data-soldout="STIN">' + el.option_name + '</li>';
		listHtml += '                </div>';
		listHtml += '            </div>';
		listHtml += '        </div>';
		listHtml += '    </div>';

		listHtml += '    <div class="list-row web-saleprice"><span class="product-price">' + sales_price + '</span></div>';
		listHtml += '    <div class="list-row"><span class="product-count">' + el.product_qty + '</span></div>';
		listHtml += '    <div class="list-row"><span class="price_total">' + total_price + '</span></div>';
		listHtml += '</div>';
	});

	wrap.innerHTML = listHtml;
	domFrag.appendChild(wrap);
	orderWrapBody.prepend(domFrag);
	calcPriceProduct();
}

function getMemberInfo(member_info) {
	let member = member_info[0];

	document.querySelector(".member_info .member_name").innerHTML = member.member_name;
	document.querySelector(".member_info .member_mobile").innerHTML = member.member_mobile;
	document.querySelector(".member_info .member_email").innerHTML = member.member_email;

	$('#member_name').val(member.member_name);
	$('#member_mobile').val(member.member_mobile);
	$('#member_email').val(member.member_email);
}

function getAddrInfo(order_to_info) {
	let order_to = order_to_info[0];

	document.querySelector(".address-info .save-box .to_place").innerHTML = order_to.to_place;
	document.querySelector(".address-info .save-box .to_name").innerHTML = order_to.to_name;
	document.querySelector(".address-info .save-box .to_mobile").innerHTML = order_to.to_mobile;
	document.querySelector(".address-info .save-box .to_zipcode").innerHTML = order_to.to_zipcode;

	let to_addr = null;
	if (order_to.to_road_addr == "" || order_to.to_road_addr == "" == null) {
		to_addr = order_to.to_lot_addr;
	} else {
		to_addr = order_to.to_road_addr;
	}

	document.querySelector(".address-info .save-box .to_addr").innerHTML = to_addr;
	document.querySelector(".address-info .save-box .to_detail_addr").innerHTML = order_to.to_detail_addr;

	$('#to_place').val(order_to.to_place);
	$('#to_name').val(order_to.to_name);
	$('#to_mobile').val(order_to.to_mobile);
	$('#to_zipcode').val(order_to.to_zipcode);
	$('#to_road_addr').val(order_to.to_road_addr);
	$('#to_lot_addr').val(order_to.to_lot_addr);
	$('#to_detail_addr').val(order_to.to_detail_addr);
}

const put_addr_wrap = document.querySelector(".edit-box");
const get_addr_wrap = document.querySelector(".save-box");
const list_addr_wrap = document.querySelector(".list-box");

const list_addr_btn = document.querySelector(".list_addr_btn");

function listAdd() {
	$.ajax({
		type: "post",
		dataType: "json",
		url: "pi/order/pg/to/get",
		error: function () {
			console.log("결제하기에 화면정보 조회처리에 실패했습니다.");
		},
		success: function (d) {
			let code = d.code;
			if (code == 200) {
				let data = d.data;

				if (data != null) {
					let addrListBody = document.querySelector(".addrList-body");
					addrListBody.innerHTML = "";

					data.forEach(function (row) {
						let addrListContent = document.createElement("div");
						addrListContent.className = "addrList-content";

						let strDiv = "";
						strDiv += '<div class="to-place">' + row.to_place + '</div>';
						strDiv += '<div class="addr-list-wrap">';
						strDiv += '	<div class="cn-box" onClick="getOrderToInfo(' + row.order_to_idx + ')">';
						strDiv += '    	<div class="addrList-row">';
						strDiv += '       	 <span class="to-name">' + row.to_name + '</span>/<span class="to-phone">' + row.to_mobile + '</span>';
						strDiv += '   	 </div>';
						strDiv += '  	  <div class="addrList-row">';
						strDiv += '   	     (<span class="to-zipcode">' + row.to_zipcode + '</span>)<span class="to-addr">' + row.to_road_addr + '</span><span class="to-detail">' + row.to_detail_addr + '</span>';
						strDiv += '   	 </div>';
						strDiv += '	</div>';
						strDiv += '	<div id="delete-addr" style ="padding-top: 40px; width:50px;" onClick="deleteAdrr(' + row.order_to_idx + ')"><u>삭제하기</u></div>';
						strDiv += '</div>';

						addrListContent.innerHTML = strDiv;
						addrListBody.appendChild(addrListContent)
					});

					get_addr_wrap.classList.add("hidden");
					put_addr_wrap.classList.add("hidden");
					list_addr_wrap.classList.remove("hidden");
				}
				else{
					document.querySelector(".list-box").classList.add("hidden");
				}
			} else {
				exceptionHandling("디자인 필요", d.msg);
			}
		}
	});
}


function getOrderToInfo(order_to_idx) {
	$.ajax({
		type: "post",
		data: {
			'order_to_idx': order_to_idx
		},
		dataType: "json",
		url: "pi/order/pg/to/get",
		error: function () {
			console.log("결제하기에 화면정보 조회처리에 실패했습니다.");
		},
		success: function (d) {
			let code = d.code;
			if (code == 200) {
				let data = d.data;

				if (data != null) {
					data.forEach(function (row) {
						get_addr_wrap.querySelector(".to_place").innerHTML = row.to_place;
						get_addr_wrap.querySelector(".to_name").innerHTML = row.to_name;
						get_addr_wrap.querySelector(".to_mobile").innerHTML = row.to_mobile;
						get_addr_wrap.querySelector(".to_zipcode").innerHTML = row.to_zipcode;

						let to_addr = null;
						if (row.to_road_addr == "" || row.to_road_addr == null) {
							to_addr = row.to_lot_addr;
						} else {
							to_addr = row.to_lot_addr;
						}

						get_addr_wrap.querySelector(".to_addr").innerHTML = to_addr;
						get_addr_wrap.querySelector(".to_detail_addr").innerHTML = row.to_detail_addr;

						$('#to_place').val(row.to_place);
						$('#to_name').val(row.to_name);
						$('#to_mobile').val(row.to_mobile);
						$('#to_zipcode').val(row.to_zipcode);
						$('#to_road_addr').val(row.to_road_addr);
						$('#to_lot_addr').val(row.to_lot_addr);
						$('#to_detail_addr').val(row.to_detail_addr);

						get_addr_wrap.classList.remove("hidden");
						put_addr_wrap.classList.add("hidden");
						list_addr_wrap.classList.add("hidden");
					});
				}
			} else {
				exceptionHandling("디자인 필요", d.msg);
			}
		}
	});
}

$(function () {
	$("#postcodify").postcodify({
		insertPostcode: ".tmp_to_zipcode",
		insertAddress: ".tmp_to_road_addr",
		insertExtraInfo: ".tmp_to_lot_addr",
		hideOldAddresses: false,
		results: ".post-change-result",
		hideSummary: true,
		useFullJibeon: true,
		onReady: function () {
			document.querySelector(".post-change-result").style.display = "none";
			$(".postcodify_search_controls .keyword").attr("placeholder", "예) 성동구 연무장길 53, 성수동2가 315-57");
			// $(".post-change-result").hide();
		},
		onSuccess: function () {
			document.querySelector(".post-change-result").style.display = "block";
			$("#postcodify div.postcode_search_status.too_many").hide();
			// $(".post-change-result").hide();
		},
		afterSelect: function (selectedEntry) {
			$("#postcodify div.postcode_search_result").remove();
			$("#postcodify div.postcode_search_status.too_many").hide();
			$("#postcodify div.postcode_search_status.summary").hide();

			document.querySelector(".post-change-result").style.display = "none";
			$("#entry_box").show();
			$("#entry_details").focus();
			$(".postcodify_search_controls .keyword").val($(".tmp_to_road_addr").val());
		}
	});
});

const update_addr_btn = document.querySelector(".address-info .edit-btn");
update_addr_btn.addEventListener("click", function () {
	get_addr_wrap.classList.toggle("hidden");
	put_addr_wrap.classList.toggle("hidden");
	list_addr_wrap.classList.add("hidden");
	$(".edit_box").removeClass("hidden");
});
const add_addr_btn = document.querySelector(".address-info .save-btn");
add_addr_btn.addEventListener("click", function() {
	let edditBox = document.querySelector(".edit-box");
	listAddr();
});

function listAddr() {
	let to_place = document.querySelector(".tmp_to_place");
	let to_name = document.querySelector(".tmp_to_name");
	let to_mobile = document.querySelector(".tmp_to_mobile");

	let addrSearch = document.querySelector(".postcodify_search_controls .keyword");

	if (to_place.value === "" || to_place.value == null) {
		to_place.previousElementSibling.classList.add("check");
		return false;
	} else {
		to_place.previousElementSibling.classList.remove("check")
	}

	if (to_name.value === "" || to_name.value == null) {
		to_name.previousElementSibling.classList.add("check");
		return false;
	} else {
		to_name.previousElementSibling.classList.remove("check");
	}

	if (to_mobile.value === "" || to_mobile == null) {
		to_mobile.previousElementSibling.classList.add("check");
		return false;
	} else {
		to_mobile.previousElementSibling.classList.remove("check");
	}

	if (addrSearch.value.length == 0) {
		if (document.querySelector(".tmp_to_zipcode").value === document.querySelector(".postcodify_search_controls .keyword").value) {
			exceptionHandling("디자인 필요", "배송지를 선택해주세요");
		}
		return false;
	}

	addOrderToInfo();
	document.querySelector("#to_place").value = to_place.value;
	document.querySelector("#to_name").value = to_name.value;
	document.querySelector("#to_mobile").value = to_mobile.value;
	document.querySelector("#to_zipcode").value = put_addr_wrap.querySelector(".tmp_to_zipcode").value;
	document.querySelector("#to_road_addr").value = put_addr_wrap.querySelector(".tmp_to_road_addr").value;
	document.querySelector("#to_lot_addr").value = put_addr_wrap.querySelector(".tmp_to_lot_addr").value;
	document.querySelector("#to_detail_addr").value = put_addr_wrap.querySelector(".tmp_to_detail_addr").value;

	let resetInput = document.querySelectorAll(".edit-box .input-row input");
	resetInput.forEach((el) => {
		el.value = "";
	});
	$('.add_flg').prop('checked', false);

	get_addr_wrap.classList.remove("hidden");
	put_addr_wrap.classList.add("hidden");
	list_addr_wrap.classList.add("hidden");
}

function addOrderToInfo() {
	let to_place = put_addr_wrap.querySelector(".tmp_to_place").value;
	let to_name = put_addr_wrap.querySelector(".tmp_to_name").value;
	let to_mobile = put_addr_wrap.querySelector(".tmp_to_mobile").value;
	let to_zipcode = put_addr_wrap.querySelector(".tmp_to_zipcode").value;
	let to_road_addr = put_addr_wrap.querySelector(".tmp_to_road_addr").value;
	let to_lot_addr = put_addr_wrap.querySelector(".tmp_to_lot_addr").value;
	let to_detail_addr = put_addr_wrap.querySelector(".tmp_to_detail_addr").value;

	//변경된 주소 박스 
	let to_place_text = document.querySelector(".save-box .to_place");
	let to_name_text = document.querySelector(".save-box .to_name");
	let to_mobile_text = document.querySelector(".save-box .to_mobile");
	let to_zipcode_text = document.querySelector(".save-box .to_zipcode");
	let to_road_addr_text = document.querySelector(".save-box .to_addr");
	let to_detail_addr_text = document.querySelector(".save-box .to_detail_addr");

	to_place_text.innerHTML = to_place;
	to_name_text.innerHTML = to_name;
	to_mobile_text.innerHTML = to_mobile;
	to_zipcode_text.innerHTML = to_zipcode;
	to_road_addr_text.innerHTML = to_road_addr;
	to_detail_addr_text.innerHTML = to_detail_addr;

	if ($('.add_flg').prop('checked') == true) {
		$.ajax({
			type: "POST",
			data: {
				'to_place': to_place,
				'to_name': to_name,
				'to_mobile': to_mobile,
				'to_zipcode': to_zipcode,
				'to_road_addr': to_road_addr,
				'to_lot_addr': to_lot_addr,
				'to_detail_addr': to_detail_addr,
			},
			dataType: "json",
			url: "pi/order/pg/to/add",
			error: function () {
				alert('배송지 저장처리중 오류가 발생했습니다.');
			},
			success: function (d) {

			}
		});
	}
}

// 배송메모 셀렉트 박스 설정
let orderMemoSelectBox = new tui.SelectBox('.addr-message-select-box', {
	placeholder: '배송시 요청사항을 선택해주세요',
	data: [
		{
			label: '부재시 문앞에 놓아주세요.',
			value: '1'
		},
		{
			label: '택배함에 넣어 주세요.',
			value: '2'
		},
		{
			label: '파손위험상품입니다. 배송시 주의해주세요.',
			value: '3'
		},
		{
			label: '배송전 연락주세요.',
			value: '4'
		},
		{
			label: '(최근)문 앞에 두고가주에요~',
			value: '5'
		},
		{
			label: '직접입력',
			value: 'direct'
		}
	],
	autofocus: false
});


function orderMemoChangeEvent() {
	orderMemoSelectBox.on('change', ev => {
		let tmp_order_memo = document.querySelector("#tmp_order_memo");
		let massageValue = document.querySelector(".message-content");

		let select_label = ev.curr.getLabel();
		let select_value = ev.curr.getValue();

		$('.edit-message-box').find('.tui-select-box-placeholder').addClass('tui-selected');

		document.querySelector(".save-message-box .message-content").innerHTML = ev.curr.getLabel();

		if (ev.curr.getValue() === "direct") {
			tmp_order_memo.style.display = "block";
		} else {
			tmp_order_memo.style.display = "none";
		}
	});
}

// 바우처 정보 셀렉트 박스 설정
function setVoucherInfoList(voucher_cnt,voucher_info) {
	let toast_data = [
		{
			'label': '선택안함',
			'value': false
		}
	];
	
	let voucherInfoSelectBox = null;
	if (voucher_cnt > 0 && voucher_info.length > 0 && voucher_info != null) {
		voucher_info.forEach(function (voucher) {
			let tmp_val = {
				'voucher_idx'	: voucher.voucher_idx,
				'sale_type'		: voucher.sale_type,
				'sale_price'	: voucher.sale_price,
				'mileage_flg'	: voucher.mileage_flg
			};

			let tmp_arr =
			{
				'label': voucher.voucher_name,
				'value': JSON.stringify(tmp_val)
			};

			toast_data.push(tmp_arr);
		});

		let usable_cnt = voucher_info.length;
		voucherInfoSelectBox = new tui.SelectBox('.voucher-select-box',
			{
				placeholder: '사용가능 쿠폰 ' + voucher_cnt + '장 / 보유 ' + usable_cnt + '장',
				data: toast_data
			}
		);
	} else {
		// 배송메모 셀렉트 박스 기본값 설정
		voucherInfoSelectBox = new tui.SelectBox('.voucher-select-box',
			{
				placeholder: '사용가능 가능한 쿠폰이 없습니다',
				data: [{
					'label': '선택안함',
					'value': false
				}]
			}
		);
	}

	voucherInfoSelectBox.on('change', ev => {
		$('.voucher-select-box').find('.tui-select-box-placeholder').addClass('tui-selected');

		let select_label = ev.curr.getLabel();
		let select_value = ev.curr.getValue();
		
		let voucher_info = null

		if (select_value != "false") {
			document.querySelector('.voucher-info-list').classList.remove("hidden");
			voucher_info = JSON.parse(select_value);

			$('#voucher_idx').val(voucher_info['voucher_idx']);

			let sale_type = voucher_info['sale_type'];
			let sale_price = voucher_info['sale_price'];
			
			console.log(sale_type);
			console.log(sale_price);
			
			let tmp_discount = 0;
			if (sale_type == "PRC") {
				tmp_discount = voucher_info['sale_prices'];
			} else if (sale_type == "PER") {
				let price_product = document.querySelector(".calculation-wrap .price_product_wrap .price_product").dataset.price_product;
				tmp_discount = (price_product * (parseInt(sale_price) / 100));
			}
			
			let price_discount = document.querySelector(".calculation-wrap .price_discount");
			price_discount.innerHTML = tmp_discount.toLocaleString('ko-KR');
			price_discount.dataset.price_discount = tmp_discount;
			
			let mileage_flg = voucher_info['mileage_flg'];
			if (mileage_flg == false) {
				$('#use_mileage').attr('disabled', false);
				$('.mileage_point_btn').attr('onClick', 'getTotalMileage(true);');
			} else {

				$('#use_mileage').val(0);
				$('#use_mileage').attr('disabled', true);
				$('.mileage_point_btn').attr('onClick', 'return false;');

				let price_mileage_point = document.querySelector(".price_mileage_point");
				price_mileage_point.dataset.price_mileage_point = 0;
				price_mileage_point.innerHTML = 0;
			}
		} else {
			document.querySelector('.voucher-info-list').classList.add("hidden");
			$('#voucher_idx').val(0);
			$('#use_mileage').attr('disabled', false);
			$('.mileage_point_btn').attr('onClick', 'return false;');

			let price_discount = document.querySelector(".price_discount");
			price_discount.dataset.price_discount = 0;
			price_discount.innerHTML = 0;

			let price_mileage_point = document.querySelector(".price_mileage_point");
			price_mileage_point.dataset.price_mileage_point = 0;
			price_mileage_point.innerHTML = 0;

			$('#use_mileage').attr('disabled', false);
			$('.mileage_point_btn').attr('onClick', 'getTotalMileage(true);');
		}

		calcPriceTotal();
	});
}

function getTotalMileage(calc_flg) {
	$.ajax({
		type: "post",
		dataType: "json",
		url: "pi/mileage/get",
		error: function () {
			console.log("적립 포인트 불러오기에 실패했습니다.");
		},
		success: function (d) {
			let code = d.code;
			if (code == 200) {
				let mileage_point = d.data;
				$('#txt_total_mileage').text(mileage_point.toLocaleString('ko-KR'));
				$('#use_mileage').val(mileage_point.toLocaleString('ko-KR'));

				let price_mileage_point = document.querySelector(".price_mileage_point");
				price_mileage_point.dataset.price_mileage_point = mileage_point;
				price_mileage_point.innerHTML = mileage_point.toLocaleString('ko-KR');

				if (calc_flg == true) {
					$('.mileage_point_msg').text('적용취소');
					$('.mileage_point_btn').attr('onClick', 'getTotalMileage(false);');
					$('.reserves-info-list.hidden').removeClass('hidden');
					calcPriceTotal();
				} else {
					price_mileage_point.dataset.price_mileage_point = '0';
					price_mileage_point.innerHTML = 0;
					$('#use_mileage').val('0');
					$('.mileage_point_btn').attr('onClick', 'getTotalMileage(true);');
					$('.mileage_point_msg').text('모두적용');
					$('.reserves-info-list').addClass('hidden');
					calcPriceTotal();
				}
			} else {
				exceptionHandling("디자인 필요", d.msg);
			}
		}
	});
}

$('#use_mileage').keyup(function () {
	var write_mileage = parseInt($('#use_mileage').val().replace(',', ''));
	var temp_meileage = "";

	if (isNaN(write_mileage)) {
		// temp_meileage = "";
		temp_meileage = 0;
	} else {
		temp_meileage = write_mileage;
	}

	$.ajax({
		type: "post",
		url: "pi/mileage/check",
		data: {
			'input_mileage': temp_meileage
		},
		dataType: "json",
		error: function () {
			console.log("적립 포인트 불러오기에 실패했습니다.");
		},
		success: function (d) {
			let code = d.code;

			if (code == 200 || code == 301) {
				let mileage_point = d.data;
				if (code == 301) {
					mileage_point = 0;
				}

				$('#use_mileage').val(mileage_point.toLocaleString('ko-KR'));

				let price_mileage_point = document.querySelector(".price_mileage_point");
				price_mileage_point.dataset.price_mileage_point = mileage_point;
				price_mileage_point.innerHTML = mileage_point.toLocaleString('ko-KR');
				calcPriceTotal();
			} else if (code == 403) {
				exceptionHandling("디자인 필요", d.msg);
			}
		}
	});
});

/* 다음단계, 이전단계 */
//버튼
const calculationWrap = document.querySelector(".calculation-wrap");
const prdToggleBtn = document.querySelector(".product-toggle-btn");
const next_step_btn = document.querySelector(".step-btn.next");
const prev_step_btn = document.querySelector(".step-btn.pre");

const $$wrapper = document.querySelectorAll(".wrapper");
const $$group1 = document.querySelectorAll(".wrapper[data-group='1']");
const $group2 = document.querySelector(".wrapper[data-group='2']");
const $group3 = document.querySelector(".wrapper[data-group='3']");
const $group4 = document.querySelector(".terms-service[data-group='4']");
const calPointBox = document.querySelector(".calculation-box .point-box");

prev_step_btn.addEventListener("click", function () {
	if (prev_step_btn.dataset.step == "0") {
		window.location.href = "/order/basket/list";
	}
	let width = document.querySelector("body").offsetWidth;
	if (width <= 1024) {
		let productWrap = document.querySelector(".product-wrap");
		let toggleBtn = document.querySelector(".product-toggle-btn");
		if (productWrap) {
			productWrap.classList.add("hidden");
		}
		toggleBtn.classList.remove("hidden");
	}
	let next_step_level = next_step_btn.dataset.step;

	next_step_btn.dataset.step = "1";
	prev_step_btn.dataset.step = "0";
	calculationWrap.dataset.step = "1";
	next_step_btn.querySelector("span").innerHTML = "다음 단계";

	header_box_btn = document.querySelector('.header-box-btn');
	header_box_btn.classList.remove("hidden");
	update_addr_btn.classList.remove("hidden");
	list_addr_btn.classList.remove("hidden");

	$$group1.forEach(el => {
		el.classList.remove("next");
	});
	//terms-service
	$group4.classList.add("hidden");

	if (next_step_level > 2) {
		document.querySelector(".address-info.next .header-box-btn").classList.remove("hidden");
	}

	//배송메시지 박스 
	document.querySelector(".edit-message-box").classList.remove("hidden");
	document.querySelector(".save-message-box").classList.add("hidden");

	calPointBox.classList.add("hidden");
	calcPriceTotal();
});

next_step_btn.addEventListener("click", function () {
	let next_step_level = next_step_btn.dataset.step;
	let checkBoxEssential = document.querySelectorAll("essential");
	let orderSection = document.querySelector(".order-section");
	let nameData = document.querySelector("#to_name").value;
	let mobileData = document.querySelector("#to_mobile").value;
	let zipcodeData = document.querySelector("#to_zipcode").value;

	let width = document.querySelector("body").offsetWidth;

	if (width <= 1024) {
		let productWrap = document.querySelector(".product-wrap.hidden");
		let toggleBtn = document.querySelector(".product-toggle-btn");
		if (productWrap) {
			productWrap.classList.remove("hidden");
		}
		toggleBtn.classList.add("hidden");
	}
	if (nameData == '' || mobileData == '' || zipcodeData == '') {
		exceptionHandling("","주소지를 적어주세요.");
		return false;
	}
	if (next_step_level == 2) {
		if (orderSection.dataset.status === "F") {
			exceptionHandling("디자인 필요", "이용약관에 동의가 필요합니다.");
		} else if (orderSection.dataset.status === "T") {
			addTmpOrderInfo();
		}
	} else {
		next_step_btn.dataset.step = "2";
		prev_step_btn.dataset.step = "1";
		calculationWrap.dataset.step = "2";
		orderSection.dataset.status = "F";
		if (next_step_btn.dataset.step === "2") {
			next_step_btn.querySelector("span").innerHTML = "결제하기";
		}

		$$group1.forEach(el => {
			el.classList.add("next");
		});

		$group2.classList.add("next");

		$group3.classList.add("next");
		if ($group3.classList.contains("next")) {
			document.querySelector(".address-info.next .header-box-btn").classList.add("hidden");
		}

		//terms-service
		$group4.classList.remove("hidden");

		//배송메시지 박스 
		document.querySelector(".edit-message-box").classList.add("hidden");
		document.querySelector(".save-message-box").classList.remove("hidden");
		calPointBox.classList.remove("hidden");

		get_addr_wrap.classList.remove("hidden");
		put_addr_wrap.classList.add("hidden");

		// 직접입력 배송메시지
		let tmp_order_memo = document.querySelector("#tmp_order_memo").value;
		if (tmp_order_memo.length > 0) {
			document.querySelector(".save-message-box .message-content").innerHTML = tmp_order_memo;
			$('#order_memo').val(tmp_order_memo);
		}

		calcPriceTotal();
	}
});

function checkboxAll(allCheck) {
	let selfChecks = document.querySelectorAll(".terms-service .check-self");
	let essentialCheckBox = document.querySelector(".terms-service .check-self.essential");
	let orderSection = document.querySelector(".order-section");
	selfChecks.forEach(el => {
		el.checked = allCheck.checked;
	});
	if (essentialCheckBox.checked) {
		orderSection.dataset.status = "T";
	} else {
		orderSection.dataset.status = "F";
	}
}
function essentialCheckBox(check) {
	let orderSection = document.querySelector(".order-section");
	if (check.checked) {
		orderSection.dataset.status = "T";
	} else {
		orderSection.dataset.status = "F";
	}
}

function calcPriceProduct() {
	let price_total = document.querySelectorAll(".product .price_total");
	let price_product = document.querySelector(".calculation-wrap .price_product_wrap .price_product");
	let price_delivery = document.querySelector(".calculation-wrap .price_delivery");

	let product_qty = document.querySelector(".calculation-wrap .product-qty");
	let product_len = price_total.length;

	let product_sum = [...price_total].map((el) => {
		let sum = + el.innerHTML.replace(/,/g, '');
		return sum;
	});
	//합산
	const sum = product_sum.reduce(function add(sum, currenValue) {
		return sum + currenValue;
	})

	price_product.dataset.price_product = sum;
	price_product.innerHTML = sum.toLocaleString("ko-KR");
	//product_qty.innerHTML = product_len;
	product_qty.innerHTML = total_qty;

	//배송비 처리
	if (product_sum < 50000) {
		let set = 5000;
		price_delivery.dataset.price_delivery = set;
		price_delivery.innerHTML = set.toLocaleString("ko-KR");;
	} else {
		price_delivery.dataset.price_delivery = 0;
		price_delivery.innerHTML = 0;
	}

	calcPriceTotal();
}

function calcPriceTotal() {
	let step = calculationWrap.dataset.step;

	let price_product = document.querySelector(".calculation-wrap .price_product_wrap .price_product").dataset.price_product;
	let price_discount = document.querySelector(".calculation-wrap .price_discount").dataset.price_discount;
	let price_mileage_point = document.querySelector(".calculation-wrap .price_mileage_point").dataset.price_mileage_point;
	let price_charge_point = document.querySelector(".calculation-wrap .price_charge_point").dataset.price_charge_point;
	let price_delivery = document.querySelector(".calculation-wrap .price_delivery").dataset.price_delivery;

	let price_total = document.querySelector(".calculation-wrap .price_total");

	let result = 0;
	//상품, 배송, 바우처, 적립, 충전 객체
	let calWrap = [
		{
			"title": "price_product",
			"price": price_product
		},
		{
			"title": "price_discount",
			"price": price_discount
		},
		{
			"title": "price_mileage_point",
			"price": price_mileage_point
		},
		{
			"title": "price_charge_point",
			"price": price_charge_point
		},
		/*{
			"title": "price_delivery",
			"price": price_delivery
		}*/
	]

	/*if (step === "1") {
	} else if (step === "2") {
	}*/

	result = calWrap.map(item => item.price).reduce(
		(prev, curr) => parseInt(prev) - parseInt(curr)
	);
	result = result + parseInt(price_delivery);
	price_total.innerHTML = result.toLocaleString("ko-KR");
}

(function () {
	let orderWrap = document.querySelector(".product-toggle-btn").offsetParent;
	prdToggleBtn.addEventListener("click", function () {

		document.querySelector(".order-product").querySelector(".product-wrap").classList.toggle("hidden");
	});
})();

//전화번호 하이푼 자동 입렵
const phoneAutoHyphen = (target) => {
	target.value = target.value
		.replace(/[^0-9]/g, '')
		.replace(/^(\d{0,3})(\d{0,4})(\d{0,4})$/g, "$1-$2-$3").replace(/(\-{1,2})$/g, "");
}

//창 닫기
function closeBox() {
	$('.close').on('click', function () {
		document.querySelector(".list-box").classList.add("hidden");
		document.querySelector(".save-box").classList.remove("hidden");
	});
}
//배송지 정보 삭제

function deleteAdrr(to_idx) {
	$.ajax({
		type: "post",
		data: {
			"order_to_idx": to_idx
		},
		dataType: "json",
		url: "/_api/order/pg/to/delete",
		error: function () {
			alert("배송지 정보 삭제 처리에 실패했습니다.");
		},
		success: function (d) {
			let code = d.code;
			if (code == 200) {
				console.log("성공");
				listAdd();
			}
			else {
				alert(d.msg);
			}
		}
	});
}

function addTmpOrderInfo() {
	let frm = $("#frm-check")[0];
	let formData = new FormData(frm);
	
	$.ajax({
		type: "post",
		url: "/_api/order/pg/tmp",
		data: formData,
		dataType: "json",
		async: true,
		enctype: "multipart/form-data",
		processData: false,
		contentType: false,
		error: function () {
			alert("주문정보 등록처리에 실패했습니다.");
		},
		success: function (d) {
			let code = d.code;
			if (code == 200) {
				let order_info = d.data;
				setTossPayment(order_info);
			}
		}
	});
}

function setTossPayment(order_info) {
	/*
	tossPayments.requestPayment('카드', {
		amount: order_info.price_total,
		orderId: order_info.order_code,
		orderName: order_info.order_title,
		customerName: order_info.member_name,
		successUrl: '/order/check',
		failUrl: '/order/check',
	});
	*/
	
	tossPayments.requestPayment('가상계좌', { // 결제 수단 파라미터
		// 결제 정보 파라미터
		amount: order_info.price_total,
		orderId: order_info.order_code,
		orderName: order_info.order_title,
		customerName: order_info.member_name,
		successUrl: '/order/check',
		failUrl: '/order/check',
		validHours: 24,
		cashReceipt: {
			type: '소득공제',
		},
	});
}
</script>