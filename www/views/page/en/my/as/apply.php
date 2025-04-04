<style>
.form-inline.no-label .textarea {margin-top:40px;}
</style>

<main class="my">
    <?php include $_CONFIG['PATH']['PAGE'].'en/my/_summary.php'; ?>
	<nav>
		<ul>
			<li><a href="/en/my">Mypage</a></li>
			<li><a href="/en/my/as">A/S</a></li>
			<li><a href="/en/my/as/apply">Apply</a></li>
		</ul>
	</nav>
	<section class="as">
		<h2 class="no-border">Apply</h2>
		<article class="apply">
			<div class="tab">
				<div class="tab-container">
					<ul>
						<li>Bluemark verified</li>
						<li>Bluemark unverified</li>
					</ul>
				</div>
				
				<section>
					<ul class="dot">
						<li>If verified bluemark is not visible,<br>you need verify from the Bluemark menu.</li>
						<li>Bluemarks are not automatically verified or registered,<br>so verify code is required after purchasing product.</li>
						<li>In the case of collaboration products and some product lines,<br>please apply for 'Bluemark unverified'</li>
					</ul>
					<ul class="list" id="list"></ul>
					<div class="paging"></div>
				</section>
				
				<section>
					<article class="submit">
						<ul class="dot">
							<li>A/S service may not be available for Bluemark unverified product</li>
						</ul>
						<form id="frm-as-submit-nocerty">
							<input class="as_contents" type="hidden" name="as_contents">
							
							<div class="grid col-2 gap-20">
								<div class="form-inline inline-label">
									<select class="as_category" name="as_category">
										<option value=""></option>
										<?php
											$select_as_category_sql = "
												SELECT
													AC.IDX					AS CATEGORY_IDX,
													AC.TXT_CATEGORY_KR		AS TXT_CATEGORY_KR,
													AC.TXT_CATEGORY_EN		AS TXT_CATEGORY_EN
												FROM
													AS_CATEGORY AC
											";
											
											$db->query($select_as_category_sql);
											
											foreach($db->fetch() as $data) {
										?>
										<option value="<?=$data['CATEGORY_IDX']?>">
											<?=$data['TXT_CATEGORY_EN']?>
										</option>
										<?php
											}
										?>
									</select>
									<span class="control-label">Product category</span>
								</div>
								
								<div class="form-inline inline-label no-margin">
									<input type="text" class="barcode" name="barcode" placeholder=" " required>
									<span class="control-label">Product code</span>
								</div>
							</div>
							<div class="form-inline no-label">
								<input type="hidden" name="contents">
								<div class="textarea" contentEditable="true"></div>
								<span class="placeholder">Please enter the contents as detailed as possible. (Maximum 1,000 characters)</span>
							</div>
							<div class="form-inline image">
								<label class="image">
									<input type="file" name="product_img[]" accept=".jpg, .jpeg, .png, .gif">
									<img>
								</label>
								<label class="image">
									<input type="file" name="product_img[]" accept=".jpg, .jpeg, .png, .gif">
									<img>
								</label>
								<label class="image">
									<input type="file" name="product_img[]" accept=".jpg, .jpeg, .png, .gif">
									<img>
								</label>
								<label class="image">
									<input type="file" name="product_img[]" accept=".jpg, .jpeg, .png, .gif">
									<img>
								</label>
								<label class="image">
									<input type="file" name="product_img[]" accept=".jpg, .jpeg, .png, .gif">
									<img>
								</label>
								<span class="control-label">Attach file product</span>
							</div>
							
							<div class="form-inline image">
								<label class="image">
									<input type="file" name="receipt_img[]" accept=".jpg, .jpeg, .png, .gif">
									<img>
								</label>
								<label class="image">
									<input type="file" name="receipt_img[]" accept=".jpg, .jpeg, .png, .gif">
									<img>
								</label>
								<label class="image">
									<input type="file" name="receipt_img[]" accept=".jpg, .jpeg, .png, .gif">
									<img>
								</label>
								<label class="image">
									<input type="file" name="receipt_img[]" accept=".jpg, .jpeg, .png, .gif">
									<img>
								</label>
								<label class="image">
									<input type="file" name="receipt_img[]" accept=".jpg, .jpeg, .png, .gif">
									<img>
								</label>
								<span class="control-label">Attach file evidence</span>
							</div>
							
							<ul class="dot">
								<li>If you attach the entire, detailed picture of the damaged part,<br>we can check more accurately.</li>
								<li>Attacg files can only be uploaded in jpg, jpeg, png, gif formats<br>capacity can be up to 5 in a maximum of 10MB or less per file.</li>
							</ul>
							<div class="buttons">
								<button type="button" class="btn_add">Apply</button>
								<button type="button" class="cancel">Cancel</button>
							</div>
						</form>
					</article>

				</section>
		</article>
		
		<article class="article submit">
			<form id="frm-as-submit">
				<input class="serial_code" type="hidden" name="serial_code" value="0">
				<input class="as_contents" type="hidden" name="as_contents">
				
				<div class="form-inline no-label">
					<div class="textarea" contentEditable="true"></div>					
				</div>
				
				<div class="form-inline image">
					<label class="image">
						<input type="file" name="product_img[]" accept=".jpg, .jpeg, .png, .gif">
						<img>
					</label>
					<label class="image">
						<input type="file" name="product_img[]" accept=".jpg, .jpeg, .png, .gif">
						<img>
					</label>
					<label class="image">
						<input type="file" name="product_img[]" accept=".jpg, .jpeg, .png, .gif">
						<img>
					</label>
					<label class="image">
						<input type="file" name="product_img[]" accept=".jpg, .jpeg, .png, .gif">
						<img>
					</label>
					<label class="image">
						<input type="file" name="product_img[]" accept=".jpg, .jpeg, .png, .gif">
						<img>
					</label>
					<span class="control-label">Attach</span>
				</div>
				
				<ul class="dot">
					<li>If you attach the entire, detailed picture of the damaged part,<br>we can check more accurately.</li>
					<li>Attacg files can only be uploaded in jpg, jpeg, png, gif formats<br>capacity can be up to 5 in a maximum of 10MB or less per file.</li>
				</ul>
				<div class="buttons">
					<button type="button" class="btn_add">Apply</button>
					<button type="button" class="cancel">Cancel</button>
				</div>
			</form>
		</article>
		
		<article class="submit-ok">
			<p>A/S application has been completed.</p>
			<ul class="dot">
				<li>You can view the A/S progress and details of A/S.</li>
				<li>After collecting the product, you cannot cancel the warrantyapplication.</li>
			</ul>
			<a href="/en/my/as/status" class="btn">A/S history</a>
		</article>
	</section>
</main>