<main class="my">
    <?php include '_summary.php'; ?>
	<nav>
		<ul>
			<li><a href="/en/my">Mypage</a></li>
			<li><a href="/en/my/membership">Membership</a></li>
		</ul>
	</nav>
	<section class="customer membership">
		<article>
			<h1>Membership</h1>
			<div class="--describe">
				<p>
					<big data-field="member_name"></big>
					<small>
						<font class="f_member_name"></font>
					</small><br>
					
					<big data-field="member_membership"></big>
					<small>
						<font class="f_member_level"></font>
						level.
					</small>
				</p>
				<p>
					Purchase price :
					<span data-field="member_buytotal">
						<font class="f_buy_total">0</font>
					</span> USD<br>
					<font class="f_next_price"></font>
				</p>
				<p>
					Applicate date : <span data-field="member_level_accept_date">2025.01.01</span><br>
					Applicate period : <span data-field="member_level_accept_date_from">2024.01.01</span> - <span data-field="member_level_accept_date_to">2024.12.31</span>
				</p>
			</div>
			<h2>Benefits</h2>
			<ul class="benefits">
				<li>
					<div class="level">
						<big>BLUE</big>
						Buy more than once a year
					</div>
					
					<ul class="dot">
						<li>Purchase amount accumulated 1%</li>
						<li>10% off birthday voucher provided</li>
					</ul>
				</li>

				<li>
					<div class="level">
						<big>BRONZE</big>
						Buy more than 300 USD, once a year
					</div>
					<ul class="dot">
						<li>Purchase amount accumulated 2%</li>
						<li>10% off birthday voucher provided</li>
						<li>Free return once a year</li>
					</ul>
				</li>

				<li>
					<div class="level">
						<big>SILVER</big>
						Buy more than 3,000 USD, once a year
					</div>
					<ul class="dot">
						<li>Purchase amount accumulated 3%</li>
						<li>15% off birthday voucher provided</li>
						<li>Free return once a year</li>
					</ul>
				</li>
				<li>
					<div class="level">
						<big>GOLD</big>
						Buy more than 5,000 USD, once a year
					</div>
					<ul class="dot">
						<li>Purchase amount accumulated 4%</li>
						<li>15% off birthday voucher provided</li>
						<li>Free return twice a year</li>
					</ul>
				</li>

				<li>
					<div class="level">
						<big>BLACK</big>
						Buy more than 10,000 USD, once a year
					</div>
					<ul class="dot">
						<li>Purchase amount accumulated 7%</li>
						<li>20% off birthday voucher provided</li>
						<li>Free return once a year</li>
					</ul>
				</li>
			</ul>

			<ul class="dot">
				<li>Cumulative purchase amount is based on the actual payment amount of the order.</li>
				<li>Cumulative purchase amount will be reflected 7 days after the change to delivery completed.</li>
				<li>The reserve will be available 7 days after delivery completion date.</li>
				<li>
					If you use this service for a fraudulent purpose and method,<br>
					or if it negatively affects the shopping experience of other customers,<br>
					you may experience restrictions on the use of the service, etc.
				</li>
			</ul>

		</article>
	</section>
</main>