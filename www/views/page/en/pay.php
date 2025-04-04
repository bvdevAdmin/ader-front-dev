<main class="my">
	<nav>
		<ul>
			<li>Shopping bag</li>
			<li>Order/Payment</li>
		</ul>
	</nav>
	<button type="button" id="btn-mobile-history-back"></button>
	<section class="pay">
		<form id="frm">
			<article class="delivery">
				<dl class="fold">
					<dt><h2 class="border">Shipping info</h2></dt>
					<dd>
						<button type="button" id="btn-delivery-change" class="btn small">Change</button>
						<div id="delivery-info">
                            
						</div>
						<div class="form-inline height45">
						<select class="order_memo" name="order_memo">
								<option value="0">Please select a request for delivery.</optoin>
							</select>
							<input class="delivery_message hidden" type="text" name="delivery_message" value="">
							<span class="control-label"></span>
						</div>
					</dd>
				</dl>

				<input type="hidden" name="to_road_addr">
				<input type="hidden" name="to_lot_addr">
				<input type="hidden" name="to_detail_addr">
				<input type="hidden" name="price_charge_point">
				<input type="hidden" name="to_place">
				<input type="hidden" name="to_name">
				<input type="hidden" name="to_mobile">
				<input type="hidden" name="to_zipcode">
				<input type="hidden" name="order_to_idx" id="order_to_idx">
			</article>
			<article class="goods">
				<dl class="fold">
					<dt><h2 class="border">Order product</h2></dt>
					<dd>
						<ul class="list" id="list"></ul>
					</dd>
				</dl>
			</article>
			<article class="voucher">
				<dl class="fold">
					<dt><h2 class="border">Voucher/Mileage</h2></dt>
					<dd>
						<div class="form-inline height45">
							<div class="tui_select" id="voucher-select"></div>

							<span class="control-label">Voucher <small>(usable <span id="voucher-useful">0</span> / has <span id="voucher-has">0</span>)</small></span>
						</div>
						<div class="form-inline height45">
							<button type="button" class="black width150" id="mileage-button">Select all</button>
							<input type="number" name="price_mileage_point" id="mileage-point" value="0" step="1">
							<span class="control-label" id="mileage-display">Mileage <small>(usable 0)</small></span>
						</div>
					</dd>
				</dl>
			</article>
			<article class="result">
				<dl class="fold">
					<dt><h2 class="border">Payment</h2></dt>
					<dd>
						<dl>
							<dt>Subtotal</dt>
							<dd id="result-goods-total">0</dd>
							<dt>Customer total</dt>
							<dd id="result-goods-discount">0</dd>
							<dt>Voucher total</dt>
							<dd id="result-use-voucher">0</dd>
							<dt>Mielage total</dt>
							<dd id="result-use-mileage">0</dd>
							<dt>Shipping total</dt>
							<dd id="result-delivery-fee">0</dd>
						</dl>
						<dl class="total">
							<dt>Total</dt>
							<dd id="result-total">0</dd>
						</dl>
					</dd>
				</dl>
				
				<h3>Precautions</h3>
				<ul class="dot">
					<li>After using the voucher, you can restore it when you cancel the entire order in the payment completion state.</li>
					<li>The savings from your order will be earned 7 days after your order changes to delivery status.</li>
					<li>The reserves used will be used for products that have been paid for and will be refunded in case of total cancellation.</li>
					<li> In the case of small payments, there may be a limit on the payment amount depending on PG's policy, and please contact PG for more information.</li>
				</ul>
				
				<div class="agree">
					<label>
						<input type="checkbox" name="terms_agree" value="y">
						<i></i>
						Agree with <a href="/en/terms-of-use" target="_blank">Terms of use</a>, <a href="/en/privacy-policy" target="_blank">Privacy policy</a> (required)
					</label>
				</div>
				
				<div class="buttons">
					<button type="submit" class="black">Checkout</button>
				</div>
			</article>
		</form>
	</section>
</main>
