<main class="my">
    <?php include '_summary.php'; ?>
	<nav>
		<ul>
			<li><a href="/en/my">Mypage</a></li>
			<li><a href="/en/my/mileage">Mileage</a></li>
		</ul>
	</nav>
	<section class="mileage wrap-720">
		<article class="status">
			<h2>Mileage status</h2>
			<ul class="table-info">
				<li>
					Usable
					<div id="mileage-useful" class="number">0</div>
				</li>
				<li>
					Total
					<div id="mileage-stack" class="number">0</div>
				</li>
				<li>
					Used
					<div id="mileage-used" class="number">0</div>
				</li>
				<li>
					Accumulate
					<div id="mileage-scheduled" class="number">0</div>
				</li>
			</ul>
			<ul class="dot">
				<li>It may take some time to reflect depending on the processing status of online/offline orders/purchases.</li>
				<li>
					The estimated accumulated amount will only be incurred for online orders<br>
					and will not be reflected in the total accumulated amount until confirmed.
				</li>
				<li>
					Please refer to <a href="#유의사항">Note</a>, <a href="#적립 가이드">Guide</a> in detail at the bottom of
				</li>.
			</ul>
		</article>
		<article class="history">
			<h2>
				Mileage history
				<a href="/en/my/mileage/detail">Details</a>
			</h2>
			<div class="tab">
				<div class="tab-container">
					<ul>
						<li>Accumulate</li>
						<li>Used</li>
					</ul>
				</div>
				<section>
					<ul class="list" id="list-1">
						<li class="empty"></li>
					</ul>
                     <div class="paging" id="list-1-paging"></div>
				</section>
				<section>
					<ul class="list" id="list-2">
						<li class="empty"></li>
					</ul>
                     <div class="paging" id="list-2-paging"></div>
				</section>
			</div>
			
			<a name="유의사항"></a>
			<h2>Note</h2>
			<h3>Precautions</h3>
			<ul class="dot">
				<li>You can use the reserve if you purchase the final order amount of 300 USD or more.</li>
				<li> The reserve can be used in units of 10 USD from the minimum holding amount of 10 USD or more.</li>
				<li>
					If a new order/purchase is made with the reserve from the previous order/purchase,<br>
					the current reserve may be insufficient when confirming the previous order/purchase.<br>
					Please be careful when ordering/purchasing using the reserve.
				</li>
			</ul>
			
			<a name="적립 가이드"></a>
			<h3>Accumulation Guide</h3>
			<ul class="dot">
				<li>Reserves generated from online store orders will be converted to usable reserves after 7 days of change to delivery status.</li>
				<li>Reserves incurred from offline store purchases will be accumulated immediately after payment is completed and can be used immediately.</li>
				<li>
					The benefits accumulated when purchasing by membership level are different.<br>
					For more information, please refer to <a href="/en/my/membership">[Account] -[Membership] page</a>
				</li>
			</ul>
		</article>
	</section>
</main>