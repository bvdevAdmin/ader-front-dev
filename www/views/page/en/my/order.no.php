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
				<p>Your order is complete</p>
				<small class="info-text">
					Order number <span id="order-number" data-no=""></span><br>
					Order date <span id="order-date"></span>
				</small>
			</div>

			<dl class="fold">
				<dt><h2 class="border">Order product</h2></dt>
				<dd>
					<ul class="list" id="list-product"></ul>
				</dd>
			</dl>

			<dl class="fold">
				<dt><h2 class="border">Canceled product</h2></dt>
				<dd>
					<ul class="list" id="list-cancel"></ul>
				</dd>
			</dl>

			<dl class="fold">
				<dt><h2 class="border">Exchange product</h2></dt>
				<dd>
					<ul class="list" id="list-exchange"></ul>
				</dd>
			</dl>

			<dl class="fold">
				<dt><h2 class="border">Refund product</h2></dt>
				<dd>
					<ul class="list" id="list-refund"></ul>
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

			<dl class="fold-recent">
				<dt><h2 class="border">Payment status</h2></dt>
				<dd>
					<dl class="price-info">
						<dt>Subtotal</dt>
						<dd id="t_price-total"></dd>
						<dt>Customer total</dt>
						<dd id="t_price-member"></dd>
						<dt>Voucher total</dt>
						<dd id="t_use-voucher"></dd>
						<dt>Mileage total</dt>
						<dd id="t_use-point"></dd>
						<dt>Shipping total</dt>
						<dd id="t_price-delivery"></dd>
						<dt>Return Shipping total</dt>
						<dd id="t_price-delivery"></dd>
					</dl>
					<dl class="price-info">
						<dt class="h27">Cancel total</dt>
						<dd class="h27 total" id="t_price-cancel"></dd>
					</dl>
					<dl class="price-info">
						<dt class="h27">Remain</dt>
						<dd class="h27 total" id="t_remain_price"></dd>
					</dl>
				</dd>
			</dl>

			<dl class="fold fold-cancel">
				<dt><h2 class="border">Cancel payment</h2></dt>
				<dd>
					<dl class="price-info">
						<dt>Subtotal</dt>
						<dd id="c_price-total"></dd>
						<dt>Customer total</dt>
						<dd id="c_price-member"></dd>
						<dt>Voucher total</dt>
						<dd id="c_use-voucher"></dd>
						<dt>Mileage total</dt>
						<dd id="c_use-point"></dd>
						<dt>Extra shipping total</dt>
						<dd id="c_price-delivery"></dd>
						<dt>Return shipping total</dt>
						<dd id="c_delivery-return"></dd>
					</dl>
					<dl class="price-info">
						<dt class="h27">Cancel total</dt>
						<dd class="h27 total" id="c_price-cancel"></dd>
					</dl>
				</dd>
			</dl>

			<dl class="fold fold-refund">
				<dt><h2 class="border">Refund payment</h2></dt>
				<dd>
					<dl class="price-info">
						<dt>Subtotal</dt>
						<dd id="r_price-total"></dd>
						<dt>Customer total</dt>
						<dd id="r_price-member"></dd>
						<dt>Voucher total</dt>
						<dd id="r_use-voucher"></dd>
						<dt>Mileage total</dt>
						<dd id="r_use-point"></dd>
						<dt>Extra shipping total</dt>
						<dd id="r_price-delivery"></dd>
						<dt>Return shipping total</dt>
						<dd id="r_delivery-return"></dd>
					</dl>
					<dl class="price-info">
						<dt class="h27">Cancel total</dt>
						<dd class="h27 total" id="r_price-cancel"></dd>
					</dl>
				</dd>
			</dl>

			<dl class="fold">
				<dt><h2 class="border">Shipping address</h2></dt>
				<dd>
                    <dl class="delivery-info">
						<dt>Address name</dt>
						<dd id="to_place"></dd>
						<dt>Recipient</dt>
						<dd id="to_name"></dd>
						<dt>Recipient tel</dt>
						<dd id="to_mobile"></dd>
						<dt>Zipcode</dt>
						<dd id="to_zipcode"></dd>
                        <dt>Shipping address</dt>
						<dd id="to_addr"></dd>
						<dt>Detail address</dt>
						<dd id="to_detail_addr"></dd>
					</dl>
				</dd>
			</dl>

            <dl class="fold fold-delivery">
				<dt><h2 class="border">Shipping status</h2></dt>
				<dd>
                    <dl class="delivery-info">
						<dt>Shipping status</dt>
						<dd id="delivery-status"></dd>
						<dt>Conpany</dt>
						<dd id="delivery-company"></dd>
						<dt>Tracking number</dt>
						<dd id="delivery-num"></dd>
						<dt>Expected date</dt>
						<dd id="delivery-date"></dd>
                        <dt>Start date</dt>
						<dd id="delivery-start-date"></dd>
                        <dt>End date</dt>
						<dd id="delivery-end-date"></dd>
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
				<a class="btn btn_update">Apply exchange / return</a>
				<a href="/en" class="btn">Keep shopping</a>
				<a href="/en/my/order" class="btn">Order history</a>
			</div>
		</article>
	</section>
</main>