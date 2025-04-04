<main class="my order">
    <?php include '_summary.php'; ?>
	<nav>
		<ul>
			<li><a href="/en/my">Mypage</a></li>
			<li><a href="/en/my/order">Order list</a></li>
		</ul>
	</nav>
	<section class="order">
		<h1>Order list</h1>
		<article>
			<div class="date-search">
				<form id="frm-order-search">
					<ul class="term">
						<li><button type="button" id="one-week">1 Week</button></li>
						<li><button type="button" id="one-month">1 Month</button></li>
						<li><button type="button" id="three-month">3 Month</button></li>
						<li><button type="button" id="one-year">1 Year</button></li>
					</ul>
					<div class="inp">
						<div class="item-date">
							<input type="date" id="date_from" class="input_date" name="date_from" placeholder="YYYY-MM-DD">
						</div>
						<span class="t">-</span>
						<div class="item-date">
							<input type="date" id="date_to" class="input_date" name="date_to" placeholder="YYYY-MM-DD">
						</div>
					</div>
					<button type="submit" class="btn">Search</button>
				</form>
			</div>
			<div class="tab">
				<div class="tab-container">
					<ul>
						<li>on-going</li>
						<li>complete</li>
					</ul>
				</div>
				<section>
					<ul class="list" id="list"></ul>
                    <div class="paging" id="list-paging"></div>
				</section>
				<section>
                    <ul class="list" id="list2"></ul>
                    <div class="paging" id="list2-paging"></div>
				</section>
			</div>
		</article>
	</section>
</main>