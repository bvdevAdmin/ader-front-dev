<style>
.mt_40 {margin-top:40px!important;}
.post-change-result {padding:10px!important;z-index:99;}
@media (min-width: 1024px) {
.post-change-result {width: 100%;margin: 0 !important;background-color: #fff;overflow-y: auto;overflow-x: hidden;max-height: 285px;border: 1px solid #808080;border-top: 0px;top: -32px;}
.post-change-result {width: 100%;margin: 0 !important;background-color: #fff;overflow-y: auto;overflow-x: hidden;max-height: 285px;border: 1px solid #808080;border-top: 0px;top: -32px;}
}
</style>

<main class="my">
	<nav>
		<ul>
			<li><a href="/en/my">Mypage</a></li>
			<li><a href="/en/my/info">Account</a></li>
			<li><a href="/en/my/info/address">Address</a></li>
			<li><a>Edit address</a></li>
		</ul>
	</nav>
	<button type="button" id="btn-mobile-history-back"></button>
	<section class="address wrap-480">
		<article class="add">
			<h1>Edit address</h1>
			<form id="frm">
				<input id="no" type="hidden" name="no" value="">
				<input type="hidden" name="action_type" value="UPDATE">
				
				<div class="form-inline inline-label">
					<input id="to_place" type="text" name="to_place" placeholder=" " required>
					<span class="control-label">Place name</span>
				</div>
				<div class="form-inline inline-label">
					<input id="to_name" type="text" name="to_name" placeholder=" " required>
					<span class="control-label">Receipt</span>
				</div>
				<div class="form-inline inline-label">
					<input id="to_mobile" type="text" name="to_mobile" placeholder=" " required>
					<span class="control-label">Mobile number</span>
				</div>

				<div class="form-inline inline-label">
					<input id="to_zipcode" type="number" name="to_zipcode" placeholder=" " required>
					<span class="control-label">Zipcode</span>
				</div>

				<div class="form-inline inline-label">
					<div class="foreign">
						<select class="country" name="to_country_code"></select>
						
						<select class="province" name="to_province_idx"></select>
					</div>
					<span class="control-label">Country / Province</span>
				</div>
				<div class="form-inline inline-label">
					<input id="to_city" type="text" name="to_city" required>
					<span class="control-label">City</span>
				</div>
				<div class="form-inline inline-label">
					<input id="to_address" type="text" name="to_address" required>
					<span class="control-label">Address</span>
				</div>
				<div class="form-inline inline-label">
					<input id="to_detail_addr" type="text" name="to_detail_addr" placeholder=" " required>
					<span class="control-label">Detail address</span>
				</div>
				<div class="form-inline">
					<label><input type="checkbox" name="default_flg" value="T"><i></i>Set default address</label>
				</div>
				<div class="buttons mt_40">
					<button type="submit" class="black">Edit</button>
				</div>
			</form>
		</article>
	</section>
</main>