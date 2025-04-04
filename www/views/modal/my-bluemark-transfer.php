<section class="section__transfer">
	<article>
		<p class="t_01"></p>
		<button type="button" class="close"></button>
		
		<ul class="dot t_02"></ul>
		
		<div class="tab">
			<div class="tab-container">
				<ul>
					<li class="on t_03"></li>
					<li class="t_04"></li>
				</ul>
			</div>
			
			<section class="section__tab on" data-section_type="MAIL">
				<div class="form-inline">
					<select name="country" class="country">
						<option class="t_05" value="KR"></option>
						<option class="t_06" value="EN"></option>
					</select>
					
					<input type="email" name="email" class="input-email">
					<span class="control-label t_07"></span>
				</div>
			</section>
			
			<section class="section__tab" data-section_type="TEL">
				<div class="form-inline">
					<select name="country" class="country">
						<option class="t_05" value="KR"></option>
						<option class="t_06" value="EN"></option>
					</select>
					
					<input type="tel" name="tel" class="input-tel">
					<span class="control-label t_08"></span>
				</div>
			</section>
		</div>
		
		<div class="row_flex__button">
			<button class="t_09 btn_transfer" type="button" class="btn btn_transfer" data-bluemark_idx="<?=$no?>"></button>
		</div>
	</article>
</section>

<script>
let t_column = {
	KR : {
		t_01 : "제품 양도하기",
		t_02 : "<li>하단에 양도받을 이메일을 입력 후 버튼 클릭 시 블루마크 양도신청이 접수됩니다.</li><li>정보는 향후 변경이 불가능하니 신청 전에 반드시 확인해 주시길 바랍니다.</li>",
		t_03 : "이메일",
		t_04 : "휴대전화",
		t_05 : "한국몰",
		t_06 : "영문몰",
		t_07 : "양도 받을 이메일",
		t_08 : "양도 받을 휴대전화",
		t_09 : "신청 완료"
	},
	EN : {
		t_01 : "Hand over",
		t_02 : `
			<li>
				If you enter the e-mail to be transferred at the bottom and click the button,<br>
				the application for the transfer of the blue mark will be received.
			</li>
			<li>
				Information cannot be changed in the future,<br>
				so please check it before applying.
			</li>
		`,
		t_03 : "E-mail",
		t_04 : "Mobile number",
		t_05 : "Korea",
		t_06 : "English",
		t_07 : "Hand over E-mail",
		t_08 : "Hand over mobile",
		t_09 : "Apply"
	}
}

$(document).ready(function() {
	$('.t_01').text(`${t_column[config.language]['t_01']}`);
	$('.t_02').append(`${t_column[config.language]['t_02']}`);
	$('.t_03').text(`${t_column[config.language]['t_03']}`);
	$('.t_04').text(`${t_column[config.language]['t_04']}`);
	$('.t_05').text(`${t_column[config.language]['t_05']}`);
	$('.t_06').text(`${t_column[config.language]['t_06']}`);
	$('.t_07').text(`${t_column[config.language]['t_07']}`);
	$('.t_08').text(`${t_column[config.language]['t_08']}`);
	$('.t_09').text(`${t_column[config.language]['t_09']}`);

	clickBTN_transfer();
});

</script>