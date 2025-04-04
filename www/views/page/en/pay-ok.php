<?php

$order_idx = 0;
if (isset($_GET['order_idx'])) {
	$order_idx = $_GET['order_idx'];
}

?>
<main class="my">
	<nav>
		<ul>
			<li>Shopping bag</li>
			<li>Order/Payment</li>
		</ul>
	</nav>
	<section class="pay ok wrap-720">
		<article>
			<h2>Order/Payment</h2>
			<div class="infobox">
				<p>Payment has completed</p>
				<small class="info-text">
					Order number <span id="order-number" data-no="<?= $order_idx ?>"></span><br>
					Order date <span id="order-date"></span>
				</small>
			</div>

			<dl class="fold">
				<dt><h2 class="border">Order product</h2></dt>
				<dd>
					<ul class="list" id="list"></ul>
				</dd>
			</dl>

			<dl class="fold">
				<dt><h2 class="border">Order payment</h2></dt>
				<dd>
					<dl class="price-info">
						<dt>Subtotal</dt>
						<dd id="price-total"></dd>
						<dt>Customer total</dt>
						<dd id="price-member"></dd>
						<dt>Voucher total</dt>
						<dd id="use-voucher"></dd>
						<dt>Mileage total</dt>
						<dd id="use-point"></dd>
						<dt>Shipping total</dt>
						<dd id="price-delivery"></dd>
					</dl>
					<dl class="price-info">
						<dt class="h27">Total</dt>
						<dd class="h27 total" id="price-pay-total"></dd>
					</dl>
					<dl class="price-info pay-method">
						<dt>Payment method</dt>
						<dd id="pay-method"></dd>
						<dt>Payment date</dt>
						<dd id="pay-date"></dd>
						<dt></dt>
						<dd><button type="button" id="btn-view-receipt" class="btn small">View Receipts</button></dd>
					</dl>
				</dd>
			</dl>
			
            <dl>
                <dd>
                    <h3>Order cancellation notice</h3>
					<ul class="dot">
						<li>Orders can be cancelled by customers until 'preparing product' phase.</li>
						<li>After the 'preparing product', from 'processing' phase orders cannot be cancelled and only can be returned or exchanged after the delivery.</li>
					</ul>
					<h3>Return & Exchanges</h3>
					<ul class="dot">
						<li>Return and exchange request must be reached us within 7 business days after the delivery date.</li>
						<li>Requests can be made by customers within order history menu. Please contact us if you need any help.</li>
						<li>Learn more about return and exchange</li>
					</ul>
                </dd>
            </dl>
			
			<div class="buttons">
				<a href="/en" class="btn">Keep shopping</a>
				<a href="/en/my/order" class="btn">Order history</a>
			</div>
		</article>
	</section>
</main>