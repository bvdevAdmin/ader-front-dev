<style>
.form-inline.no-label .textarea {margin-top:40px;}
</style>

<main class="my">
    <?php include $_CONFIG['PATH']['PAGE'].'kr/my/_summary.php'; ?>
	<nav>
		<ul>
			<li><a href="/kr/my">마이페이지</a></li>
			<li><a href="/kr/my/as">A/S</a></li>
			<li><a href="/kr/my/as/apply">서비스 신청</a></li>
		</ul>
	</nav>
	<section class="as">
		<h2 class="no-border">A/S 서비스 신청</h2>
		<article class="apply">
			<div class="tab">
				<div class="tab-container">
					<ul>
						<li>Bluemark 인증내역</li>
						<li>인증 불가 제품</li>
					</ul>
				</div>
				
				<section>
					<ul class="dot">
						<li>인증 내역이 보이지 않는 경우, 마이페이지 - 블루마크 메뉴에서 코드 인증이 필요합니다.</li>
						<li>블루마크는 자동으로 인증 또는 등록되지 않으므로 제품 구매 이후 코드 인증이 필요합니다.</li>
						<li>콜라보레이션 제품 및 일부 제품군의 경우, 블루마크가 포함되어 있지 않아 ‘인증 불가 제품’으로 신청 부탁드립니다.</li>
					</ul>
					<ul class="list" id="list"></ul>
					<div class="paging"></div>
				</section>
				
				<section>
					<article class="submit">
						<ul class="dot">
							<li>정품 여부 확인이 어려운 제품은 A/S가 불가할 수 있습니다.</li>
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
										<option value="<?=$data['CATEGORY_IDX']?>"><?=$data['TXT_CATEGORY_KR']?></option>
										<?php
											}
										?>
									</select>
									<span class="control-label">제품 카테고리</span>
								</div>
								
								<div class="form-inline inline-label no-margin">
									<input type="text" class="barcode" name="barcode" placeholder=" " required>
									<span class="control-label">제품 코드</span>
								</div>
							</div>
							<div class="form-inline no-label">
								<input type="hidden" name="contents">
								<div class="textarea" contentEditable="true"></div>
								<span class="placeholder">내용을 최대한 자세하게 입력해주세요. (최대 1,000자)</span>
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
								<span class="control-label">사진 첨부</span>
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
								<span class="control-label">구매 이력, 증빙 이미지 첨부</span>
							</div>
							
							<ul class="dot">
								<li>제품 전체 및 상세 사진과 파손 부분의 사진을 함께 첨부해주시면 더욱 정확한 확인이 가능합니다.</li>
								<li>파일은 jpg, jpeg, png와 gif 형식만 업로드가 가능하며, 용량은 개당 10MB이하 최대 5개까지만 가능합니다.</li>
							</ul>
							<div class="buttons">
								<button type="button" class="btn_add">A/S 신청</button>
								<button type="button" class="cancel">취소</button>
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
					<span class="control-label">사진 첨부</span>
				</div>
				
				<ul class="dot">
					<li>제품 전체 및 상세 사진과 파손 부분의 사진을 함께 첨부해주시면 더욱 정확한 확인이 가능합니다.</li>
					<li>파일은 jpg, jpeg, png와 gif 형식만 업로드가 가능하며, 용량은 개당 10MB이하 최대 5개까지만 가능합니다.</li>
				</ul>
				<div class="buttons">
					<button type="button" class="btn_add">A/S 신청</button>
					<button type="button" class="cancel">취소</button>
				</div>
			</form>
		</article>
		
		<article class="submit-ok">
			<p>A/S 신청이 완료되었습니다.</p>
			<ul class="dot">
				<li>A/S 내역에서 해당 제품의 A/S 진행과정을 열람하실 수 있습니다.</li>
				<li>제품 회수 후에는 A/S 신청을 취소하실 수 없습니다.</li>
			</ul>
			<a href="/kr/my/as/status" class="btn">A/S 내역</a>
		</article>
	</section>
</main>