<main class="my">
    <?php include '_summary.php'; ?>
	<nav>
		<ul>
			<li><a href="/en/my">Mypage</a></li>
			<li><a href="/en/my/voucher">Voucher</a></li>
		</ul>
	</nav>
	<section class="voucher wrap-720">
		<article>
			<h2 class="border">Voucher register</h2>
			<div class="submit-ok">
				Voucher register has been completed
				<ul class="dot">
					<li>You can check more details in the History menu below.</li>
				</ul>
				<div class="buttons">
					<button type="button" id="btn-voucher-submit-close">Details</button>
				</div>
			</div>
			<div class="submit">
				<form id="frm-voucher">
					<div class="form-inline">
						<button type="submit">Register</button>
						<input id="voucher_issue_code" type="text" name="voucher" placeholder=" " required>
						<div class="control-label inline">Voucher code</div>
					</div>	
				</form>
				<ul class="dot">
					<li>Type case separately.</li>
					<li>Vouchers that have expired cannot be registered.</li>
					<li>Please check the issuance and duration of the voucher.</li>
				</ul>
			</div>
			<h2 class="border">
				Voucher history
				<a href="/en/my/voucher/detail">Details</a>
			</h2>
			<ul class="voucher-list" id="list">
			</ul>
			<h2 class="border">Notice</h2>
			<ul class="dot margin20">
				<li>You can use 1 voucher for 1 order.</li>
				<li>Vouchers will be restored immediately after the entire order is canceled.</li>
				<li>Expired vouchers will not be reissued.</li>
				<li>Purchased products may be restricted depending on the voucher.</li>
			</ul>
		</article>
	</section>
</main>