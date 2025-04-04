<main class="my">
	<nav>
	<ul>
			<li><a href="/en/my">Mypage</a></li>
			<li><a href="/en/my/customer">Customer</a></li>
			<li><a href="/en/my/customer/qna">1:1 inquiry</a></li>
			<li><a>Create inquiry</a></li>
		</ul>
	</nav>
	<section class="qna wrap-720">
		<article class="write">
			<h2>Create inquiry</h2>
			<form id="frm-qna-write">
				<div class="form-inline inline-label">
					<select class="category_idx" name="category_idx" required>
						
					</select>
					<span class="control-label">Inquiry category</span>
				</div>
				
				<div class="form-inline inline-label no-margin">
					<input type="text" name="qna_title" placeholder="" required>
					<span class="control-label">Type your title here.</span>
				</div>
				
				<div class="form-inline inline-label">
					<input class="qna_contents" type="hidden" name="qna_contents" value="">
					<div class="textarea" contentEditable="true"></div>
					<span class="control-label">Type your inquiry here. (Up to 1,000 letters)</span>
				</div>
				
				<div class="form-inline file">
					<label class="image">
						<input type="file" name="qna_img[]" accept=".jpg, .jpeg, .png, .gif">
						<img>
					</label>
					<label class="image">
						<input type="file" name="qna_img[]" accept=".jpg, .jpeg, .png, .gif">
						<img>
					</label>
					<label class="image">
						<input type="file" name="qna_img[]" accept=".jpg, .jpeg, .png, .gif">
						<img>
					</label>
					<label class="image">
						<input type="file" name="qna_img[]" accept=".jpg, .jpeg, .png, .gif">
						<img>
					</label>
					<label class="image">
						<input type="file" name="qna_img[]" accept=".jpg, .jpeg, .png, .gif">
						<img>
					</label>
					<span class="control-label">Attachment</span>
				</div>
				
				<ul class="dot">
					<li>In case of a defective product or wrong delivery, please upload the product and shipment photographs.</li>
					<li>File configuration with jpg, jpeg, gif and png are supported up to 10MB, maximum of 5 files.</li>
				</ul>
				<div class="buttons">
					<button type="button" class="btn_write">Create</button>
					<button type="button" class="cancel">Cancel</button>
				</div>
			</form>
		</article>
	</section>
</main>