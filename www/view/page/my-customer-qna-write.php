<main class="my">
	<?php include 'inc/my.summary.php'; ?>
	<nav>
		<ul>
			<li><a href="/my">마이페이지</a></li>
			<li><a href="/my/customer">고객센터</a></li>
			<li><a href="/my/customer/qna">문의하기</a></li>
			<li><a>1:1 문의하기</a></li>
		</ul>
	</nav>
	<section class="qna wrap-720">
		<article class="write">
			<h2>1:1 문의하기</h2>
			<form id="frm-qna-write">
				<div class="form-inline inline-label">
					<select name="category" required>
						<option value=""></option>
						<option value="제품 / 사이즈">제품 / 사이즈</option>
						<option value="취소 / 환불">취소 / 환불</option>
						<option value="출고 / 배송">출고 / 배송</option>
						<option value="반품 / 교환">반품 / 교환</option>
					</select>
					<span class="control-label">문의 유형</span>
				</div>
				<div class="form-inline inline-label no-margin">
					<input type="text" name="title" placeholder=" " required>
					<span class="control-label">제목을 입력하세요.</span>
				</div>
				<div class="form-inline inline-label">
					<div class="textarea" contentEditable="true"></div>
					<span class="control-label">내용을 입력해주세요. (최대 1,000자)</span>
				</div>
				<div class="form-inline file">
					<label class="cont">
						<input type="file" name="file[]">
					</label>
					<label class="cont">
						<input type="file" name="file[]">
					</label>
					<label class="cont">
						<input type="file" name="file[]">
					</label>
					<label class="cont">
						<input type="file" name="file[]">
					</label>
					<label class="cont">
						<input type="file" name="file[]">
					</label>
					<span class="control-label">사진 첨부</span>
				</div>
				<ul class="dot">
					<li>제품 불량 및 오배송의 경우, 수령하신 제품의 상태와 배송 패키지 사진을 등록해 주시면 더욱 빠른 확인이 가능합니다.</li>
					<li>파일은 JPG, JPEG, GIF 및 PNG 형식만 첨부가 가능하며, 용량은 개당 10MB 이하 최대 5개까지만 가능합니다.</li>
				</ul>
				<div class="buttons">
					<button type="submit">등록</button>
					<button type="button" class="cancel">취소</button>
				</div>
			</form>
		</article>
	</section>
</main>