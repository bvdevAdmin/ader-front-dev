<h1>고객 관리</h1>
<div class="navigation">
	<ul>
		<li>Home</li>
		<li>상품관리</li>
		<li>상품목록</li>
	</ul>
</div>

<div class="row">
	<div class="row">
		
	</div>
	
	<div class="portlet" style="margin-top:20px;">
		<div class="title">
			<h1 id="tabTitle">상품 목록</h1>
		</div>
		
		<div class="body">
			<div class="row">
				<label>
					전체 <font style="color:#3971FF;font-weight:800;">5,637</font>건
				</label>
				
				<label>
					판매함 <font style="color:#3971FF;font-weight:800;">1,662</font>건
				</label>
				
				<label>
					판매 안함 <font style="color:#3971FF;font-weight:800;">3,975</font>건
				</label>
				
				<label>
					진열함 <font style="color:#3971FF;font-weight:800;">1,684</font>건
				</label>
				
				<label>
					진열안함 <font style="color:#3971FF;font-weight:800;">3,953</font>건
				</label>
			</div>
			
			<div class="row">
				<TABLE class="list" style="font-size:0.7rem;">
					<colgroup>
						<col width="10%">
						<col width="40%">
						<col width="10%">
						<col width="40%">
					</colgroup>
					<TBODY>
						<TR>
							<TD>검색분류</TD>
							<TD colspan="3">
								<div class="row">
									<select class="fSelect eSearch" name="eField[]" style="width:163px;">
										<option value="product_name">상품명</option>
										<option value="eng_product_name">영문상품명</option>
										<option value="item_name">상품명(관리용)</option>
										<option value="purchase_prd_name">공급사 상품명</option>
										<option value="">--------------------</option>
										<option value="product_no">상품번호</option>
										<option value="product_code">상품코드</option>
										<option value="old_product_code">(구)상품코드</option>
										<option value="ma_product_code">자체 상품코드</option>
										<option value="item_code">품목코드</option>
										<option value="admin_item_code">자체 품목코드</option>
										<option value="">--------------------</option>
										<option value="Manufacturer">제조사</option>
										<option value="Supplier">공급사</option>
										<option value="Brand">브랜드</option>
										<option value="Trend">트렌드</option>
										<option value="Classification">자체분류</option>
										<option value="">--------------------</option>
										<option value="model_name">모델명</option>
										<option value="origin">원산지</option>
										<option value="Condition">상품상태</option>
										<option value="product_tag">상품 검색태그</option>
										<option value="product_weight">상품 전체중량</option>
										<option value="">--------------------</option>
										<option value="pm_memo">메모</option>
										<option value="ins_user">등록아이디</option>
									</select>
									
									<input type="text" value="" style="width:70%;">
									
									<button style="width:30px;height:30px;border:1px solid;background-color:#ffffff;">-</button>
									<button style="width:30px;height:30px;border:1px solid;background-color:#ffffff;">+</button>
								</div>
							</TD>
						</TR>
						
						<TR>
							<TD>상품구분</TD>
							<TD colspan="3">
								<div class="row form-group">
									<label>
										<input type="radio" name="productType" value="" checked>
										<span>전체</span>
									</label>
									
									<label>
										<input type="radio" name="productType" value="">
										<span>기본상품</span>
									</label>
									
									<label>
										<input type="radio" name="productType" value="">
										<span>세트상품</span>
									</label>
								</div>
							</TD>
						</TR>
						
						<TR>
							<TD>상품분류</TD>
							<TD>
								<div class="form-group">
									<label>
										<input type="checkbox" name="productTypeAll" value="">
										<span>전체 상품분류 보기</span>
									</label>
								</div>
								<div class="row form-group">
									<select class="fSelect category eCategory" id="eCategory1" depth="1" name="categorys[]">
										<option value="">- 대분류 선택 -</option>
										<option value="35">개인결제</option>
										<option value="64">NEW(Shop)</option>
										<option value="470">2022 S/S 슈즈 &amp; 가방</option>
										<option value="447">선물제안(전체보기/기프트에디션)</option>
										<option value="78">Collection</option>
										<option value="73">Editorial</option>
										<option value="170">콜라보레이션</option>
										<option value="75">Stockists</option>
										<option value="74">Press</option>
										<option value="108">Significant Home</option>
										<option value="266">Ghost Store</option>
										<option value="410">과거제품</option>
										<option value="524">새 상품분류</option>
									</select>

									<select class="fSelect category eCategory" id="eCategory2" depth="2" name="categorys[]">
										<option value="">- 중분류 선택 -</option>
									</select>

									<select class="fSelect category eCategory" id="eCategory3" depth="3" name="categorys[]">
										<option value="">- 소분류 선택 -</option>
									</select>

									<select class="fSelect category eCategory" id="eCategory4" depth="4" name="categorys[]">
										<option value="">- 상세분류 선택 -</option>
									</select>
									
									<label>
										<input type="checkbox" name="" value="">
										<span>하위분류 포함검색</span>
									</label>
									
									<label>
										<input type="checkbox" name="" value="">
										<span>분류 미등록 상품 검색</span>
									</label>
								</div>
							</TD>
						</TR>
						
						<TR>
							<TD>상품등록일</TD>
							<TD>
								<div class="row">
									<select class="fSelect category" name="date">
										<option value="regist">상품등록일</option>
										<option value="modify">상품최종수정일</option>
									</select>
									
									<button class="search_hidden" style="width:50px;height:30px;border:1px solid;background-color:#ffffff;">오늘</button>
									<button class="search_hidden" style="width:50px;height:30px;border:1px solid;background-color:#ffffff;">3일</button>
									<button class="search_hidden" style="width:50px;height:30px;border:1px solid;background-color:#ffffff;">7일</button>
									<button class="search_hidden" style="width:50px;height:30px;border:1px solid;background-color:#ffffff;">1개월</button>
									<button class="search_hidden" style="width:50px;height:30px;border:1px solid;background-color:#ffffff;">3개월</button>
									<button class="search_hidden" style="width:50px;height:30px;border:1px solid;background-color:#ffffff;">1년</button>
									<button class="search_hidden" style="width:50px;height:30px;border:1px solid;background-color:#ffffff;">전체</button>
								
									<input class="search_hidden" type="date" name="registDate_from" class="margin-bottom-6" placeholder="From" readonly style="width:150px;">
									~
									<input class="search_hidden" type="date" name="registDate_to" placeholder="To" readonly style="width:150px;">
								</div>
							</TD>
						</TR>
						
						<TR>
							<TD>진열상태</TD>
							<TD>
								<div class="row form-group">
									<label>
										<input type="radio" name="displayStatus" value="" checked>
										<span>전체</span>
									</label>
									
									<label>
										<input type="radio" name="displayStatus" value="">
										<span>진열 함</span>
									</label>
									
									<label>
										<input type="radio" name="displayStatus" value="">
										<span>진열 안함</span>
									</label>
								</div>
							</TD>
							<TD>판매상태</TD>
							<TD>
								<div class="row form-group">
									<label>
										<input type="radio" name="displayStatus" value="" checked>
										<span>전체</span>
									</label>
									
									<label>
										<input type="radio" name="displayStatus" value="">
										<span>판매 함</span>
									</label>
									
									<label>
										<input type="radio" name="displayStatus" value="">
										<span>판매 안함</span>
									</label>
								</div>
							</TD>
						</TR>
					</TBODY>
				</TABLE>				
			</div>
			
			<div class="row">
				<button style="width:80px;height:30px;border:1px solid;background-color:#000000;color:#ffffff;border-radius:5px;">검색</button>
			</div>
		</div>
	</div>
</div>

<script>
$(document).ready(function() {
	
});
</script>
