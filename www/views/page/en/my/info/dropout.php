<main class="my">
	<nav>
		<ul>
			<li><a href="/en/my">Mypage</a></li>
			<li><a href="/en/my/info">Account</a></li>
			<li>Dropout</li>
		</ul>
	</nav>
	<section class="info drop">
		<h1>Dropout</h1>
		<article class="dropout">
			<h2>Extinction list</h2>
			<ul class="table-info">
				<li>
					Mileage<div class="number"><span id="my-point">0</span></div>
				</li>
				<li>
					Voucher<div class="number text-underline"><span id="my-voucher"></span></div>
				</li>
				<li>
					Wishlist<div class="number text-underline"><span class="wishlist" id="my-wishlist"></span></div>
				</li>
			</ul>
			
			<h2 class="border">Dropout reason</h2>
			<form id="frm">
				<div class="reason">
					<label class="check"><input type="checkbox" name="reason" value="배송불만족"><i></i>Delivery</label>
					<label class="check"><input type="checkbox" name="reason" value="교환 / 반품 불만족"><i></i>Exchange / Refund</label>
					<label class="check"><input type="checkbox" name="reason" value="제품 / 가격 / 품질 불만족"><i></i>Product / price / quality</label>
					<label class="check"><input type="checkbox" name="reason" value="회원 혜택 부족"><i></i>Membership</label>
					<label class="check"><input type="checkbox" name="reason" value="개인정보 유출 우려"><i></i>Privacy leakage</label>
					<label class="check"><input type="checkbox" name="reason" value="이용 빈도 낮음"><i></i>Low frequency of use</label>
					<label class="check"><input type="checkbox" name="reason" value="사이트 이용 불편"><i></i>Inconvenience site</label>
					<label class="check"><input type="checkbox" name="reason" value="기타"><i></i>Etc</label>
				</div>
				<div class="form-inline no-label">
					<div class="textarea" contentEditable="true"></div>
					<span class="placeholder">Please enter the reason.</span>
				</div>
				<ul class="agree">
					<li>
						<label class="check"><input type="checkbox" name="agree_1" value="y"><i></i>Checked all dropout notices and agree to dropout. (required)</label>
					</li>
					<li>
						<label class="check"><input type="checkbox" name="agree_2" value="y"><i></i>Agree to extinguishment of mileage, voucher (required)</label>
					</li>
				</ul>
				<ul class="dot">
					<li>You can rejoin at any time after withdrawing from the membership.</li>
					<li>As of the time of withdrawal of membership, withdrawal processing is possible only when no items are being shipped or returned or exchanged.</li>
					<li>If you leave the membership, your vouchers and reserves will automatically expire and will not be restored.</li>
					<li>
						Personal information is stored for a certain period of time<br>
						according to the relevant laws such as the Consumer Protection Act on Electronic Commerce, etc.<br>
						and the Communication Secret Protection Act.
					<ul>
					<li>Record of contract or withdrawal of subscription: 5 years</li>
					<li>Record of payment and supply of goods, etc.: 5 years </li>
					<li>Record of consumer complaints or dispute settlement: 3 years</li>
					<li> Website visit record: 3 months</li>
					</ul>
					</li>
				</ul>
				<div class="buttons">
					<button type="submit" class="black">Dropout</button>
					<button type="button">Cancel</button>
				</div>
			</form>
		</article>
		<article class="dropout-ok">
			<p>Dropout has been completed<br><br>Thank you for using out site.</p>
			<a href="/en" class="btn">Return to first screen</a>
		</article>
	</section>
</main>