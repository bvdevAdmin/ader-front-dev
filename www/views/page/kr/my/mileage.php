<main class="my">
    <?php include '_summary.php'; ?>
	<nav>
		<ul>
			<li><a href="/my">마이페이지</a></li>
			<li><a href="/my/mileage">적립금</a></li>
		</ul>
	</nav>
	<section class="mileage wrap-720">
		<article class="status">
			<h2>적립금 현황</h2>
			<ul class="table-info">
				<li>
					사용 가능
					<div id="mileage-useful" class="number">0</div>
				</li>
				<li>
					총 적립
					<div id="mileage-stack" class="number">0</div>
				</li>
				<li>
					사용 완료
					<div id="mileage-used" class="number">0</div>
				</li>
				<li>
					적립 예정
					<div id="mileage-scheduled" class="number">0</div>
				</li>
			</ul>
			<ul class="dot">
				<li>온/오프라인 주문/구매 건의 처리 현황에 따라 반영되기까지 시간이 소요될 수 있습니다.</li>
				<li>적립 예정 금액은 온라인 주문 건으로만 발생되며 확정되기 전까지 총 적립에 반영되지 않습니다.</li>
				<li>하단의 <a href="#유의사항">유의사항</a> 내 자세한 <a href="#적립 가이드">사용 및 적립 가이드</a>를 참고해 주세요.
			</ul>
		</article>
		<article class="history">
			<h2>
				적립금 내역
				<a href="/kr/my/mileage/detail">자세히 보기</a>
			</h2>
			<div class="tab">
				<div class="tab-container">
					<ul>
						<li>적립</li>
						<li>사용</li>
					</ul>
				</div>
				<section>
					<ul class="list" id="list-1">
						<li class="empty">적립 내역이 없습니다.</li>
					</ul>
				</section>
				<section>
					<ul class="list" id="list-2">
						<li class="empty">사용 내역이 없습니다.</li>
					</ul>
				</section>
			</div>
			
			<a name="유의사항"></a>
			<h2>유의사항</h2>
			<h3>사용 가이드</h3>
			<ul class="dot">
				<li>최종 주문금액 80,000원 이상 구매시 적립금 사용이 가능합니다.</li>
				<li>적립금은 최소 보유 금액 10,000원 이상부터 1,000원 단위로 사용 가능합니다.</li>
				<li>이전 주문/구매 건의 적립금으로 새로운 주문/구매를 한 경우, 이전 주문/구매 건의 확불 시 현재 보유 적립금이 부족할 수 있습니다. 적립금을 사용한 주문/구매 시 주의 부탁드립니다.</li>
			</ul>
			
			<a name="적립 가이드"></a>
			<h3>적립 가이드</h3>
			<ul class="dot">
				<li>온라인 스토어 주문으로 발생한 적립금은 배송 완료 상태로 변경 된 7일 이후 사용 가능한 적립금으로 전환됩니다.</li>
				<li>오프라인 스토어 구매로 발생한 적립금은 결제 완료 이후 즉시 적립되며 바로 사용 가능합니다.</li>
				<li>멤버십 등급별 구매 시 적립되는 혜택이 다릅니다. 자세한 내용은 <a href="/kr/my/membership">[고객서비스] - [맴버십] 페이지</a>를 참고해주세요.</li>
			</ul>
		</article>
	</section>
</main>