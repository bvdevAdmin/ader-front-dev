<section class="modal-pay-delivery">
	<header>
		배송지 정보
		<button type="button" class="close"></button>
	</header>
	<article>
		<div class="tab">
			<div class="tab-container">
				<ul>
					<li>배송지 목록</li>
					<li>새로 입력</li>
				</ul>
			</div>
			<section>
				<article class="list">
                    <ul class="list" id="list"></ul>
                </article>
			</section>
			<section>
				<article>
					<form id="frm-modal-delivery">
						<div class="form-inline inline-label">
							<input type="text" name="to_place" placeholder=" " required>
							<span class="control-label">배송지명 / 예시)집</span>
						</div>
						<div class="form-inline inline-label">
							<input type="text" name="to_name" placeholder=" " required>
							<span class="control-label">이름</span>
						</div>
						<div class="form-inline inline-label">
							<input type="text" name="to_mobile" placeholder=" " required>
							<span class="control-label">휴대전화</span>
						</div>
						<div class="form-inline inline-label">
							<div id="postcodify" class="input-row"></div>	
							<div class="input-row" style="clear:both;">
								<div class="post-change-result"></div>
							</div>
						</div>
						<div class="form-inline inline-label">
							<input id="to_zipcode" type="number" name="to_zipcode" placeholder=" " readonly required>
							<span class="control-label">우편번호</span>
						</div>
						<div class="form-inline inline-label">
							<input id="to_road_addr" type="text" name="to_road_addr" placeholder=" " readonly required>
							<input id="to_lot_addr" type="hidden" name="to_lot_addr">
							<span class="control-label">주소</span>
						</div>
						<div class="form-inline inline-label">
							<input id="to_detail_addr" type="text" name="to_detail_addr" placeholder=" " required>
							<span class="control-label">상세주소</span>
						</div>
						<div class="form-inline">
							<label><input type="checkbox" name="default_flg" value="T"><i></i>기본 배송지로 저장</label>
						</div>
					</form>
				</article>

				<button type="button" class="btn black btn_M_add">입력 완료</button>
			</section>
		</div>
	</article>
</section>

<script>
$(document).ready(function() {
	$('input[name="to_mobile"]').on('keyup',function(e) {
		let value = $(this).val().replace(/\D/g, "");

		let result = value.replace(/^(\d{3})(\d{4})(\d{4})$/,"$1-$2-$3");
		$(this).val(result);
	});
	
	$("section.modal .tab-container > ul > li").eq(0).click();

    getAddress_list();

	postcodify();

	clickBTN_M_add();
});

function getAddress_list() {
	$.ajax({
		url: config.api + "member/address/get",
		headers : {
			country : config.language
		},
		async:false,
		error: function () {
			makeMsgNoti(config.language,'MSG_F_ERR_0046','KR',null);
		},
		success: function (d) {
			if (d.code == 200) {
				let str_div = "";

				let div_list = $('section.modal-pay-delivery #list');
				div_list.html('');

				let data = d.data;
				delivery_address = data;
				
				if (data != null && data.length > 0) {
					data.forEach(function(row) {
						let msg_default	= "";
						let btn_grid	= 3;
						let btn_default	= `<button type="button" class="default" data-no="${row.order_to_idx}">기본배송지</button>`;

						if (row.default_flg == true) {
							msg_default = `<div class="msg_default">기본배송지</div>`;

							btn_grid = 2
							btn_default = "";
						}

						str_div += `
							<li data-no="146">
								<div class="info">
									<div class="address">
										<dl>
											<dt>배송지</dt>
											<dd>${row.to_name} (${row.to_place})</dd>
											<dt>전화번호</dt>
											<dd>${row.to_mobile}</dd>
											<dt>우편번호</dt>
											<dd>${row.to_zipcode}</dd>
											<dt>주소</dt>
											<dd>${row.txt_addr}</dd>
											<dt>상세주소</dt>
											<dd>${row.to_detail_addr}</dd>
										</dl>

										${msg_default}
									</div>
								</div>
								<div class="buttons grid-${btn_grid}">
									${btn_default}
									<button type="button" class="select" data-no="${row.order_to_idx}">배송지 선택</button>
									<button type="button" class="delete" data-no="${row.order_to_idx}">배송지 삭제</button>
								</div>
							</li>
						`;
					});
				} else {
					str_div += `<li class="list__none">등록된 배송지가 없습니다.</li>`;
				}

				div_list.append(str_div);

                /* 마이페이지 배송지 - 기본 배송지 설정 버튼 클릭 처리 */
				clickBTN_default();
				
				/* 마이페이지 배송지 - 배송지 삭제 버튼 클릭 처리 */
				clickBTN_delete();

                /* 마이페이지 배송지 - 배송지 선택 버튼 클릭 처리 */
				clickBTN_select();
			} else {
				if (d.msg != null) {
					alert(
						d.msg,
						function() {
							if (d.code == 401) {
								sessionStorage.setItem('r_url',location.href);
								location.href = `${config.base_url}/login`;
							}
						}
					);
				} else {
					makeMsgNoti(config.language,'MSG_F_WRN_0002',null);
				}
			}
		}
	});
}

/* 마이페이지 배송지 - 기본 배송지 설정 버튼 클릭 처리 */
function clickBTN_default() {
	let btn_default = document.querySelectorAll('.default');
	btn_default.forEach(btn => {
		btn.addEventListener('click',function(e) {
			let el = e.currentTarget;

			let address_idx = el.dataset.no;
			if (address_idx != null) {
				$.ajax({
					url: config.api + "member/address/put",
					headers : {
						country : config.language
					},
					data :{
						'action_type'		:"DEFAULT",
						'address_idx'		:address_idx
					},
					error: function () {
						makeMsgNoti(config.language,'MSG_F_ERR_0046',null);
					},
					success: function (d) {
						if (d.code == 200) {
							$('input[name="order_to_idx"]').val(address_idx);

							delivery_address.forEach(function(row) {
								if (parseInt(row.order_to_idx) == parseInt(address_idx)) {
									$('#delivery-info').html(`
										<dl>
											<dt>배송지</dt>
											<dd>${row.to_name} (${row.to_place})</dd>
											<dt>전화번호</dt>
											<dd>${row.to_mobile}</dd>
											<dt>우편번호</dt>
											<dd>${row.to_zipcode}</dd>
											<dt>주소</dt>
											<dd>${row.to_road_addr}</dd>
											<dt>상세주소</dt>
											<dd>${row.to_detail_addr}</dd>
										</dl>
									`);
								}
								
								caclDelivery_price(row.delivery_price);
							});

							modal_close();
						} else {
							if (d.msg != null) {
								alert(
									d.msg,
									function() {
										if (d.code == 401) {
											sessionStorage.setItem('r_url',location.href);
											location.href = `${config.base_url}/login`;
										}
									}
								);
							}
						}
					}
				});
			}
		});
	});
}

function clickBTN_delete() {
	let btn_delete = document.querySelectorAll('.delete');
	btn_delete.forEach(btn => {
		btn.addEventListener('click',function(e) {
			let el = e.currentTarget;

			let address_idx = el.dataset.no;
			if (address_idx != null) {
				$.ajax({
					url: config.api + "member/address/put",
					headers : {
						country : config.language
					},
					data :{
						'action_type'		:"DELETE",
						'address_idx'		:address_idx
					},
					error: function () {
						makeMsgNoti('MSG_F_ERR_0046',null);
					},
					success: function (d) {
						if (d.code == 200) {
							if (parseInt($('input[name="order_to_idx"]').val()) == parseInt(address_idx)) {
								$('input[name="order_to_idx"]').val(0);

								$('#delivery-info').html(`
									배송지를 선택해주세요.
								`);
							}
							
							/* 마이페이지 배송지 - 회원 배송지 목록 조회 */
							getAddress_list();
						} else {
							if (d.msg != null) {
								alert(
									d.msg,
									function() {
										if (d.code == 401) {
											sessionStorage.setItem('r_url',location.href);
											location.href = `${config.base_url}/login`;
										}
									}
								);
							}
						}
					}
				});
			}
		});
	});
}

function clickBTN_select() {
    let btn_select = document.querySelectorAll(".select");
	btn_select.forEach(btn => {
		btn.addEventListener('click',function(e) {
			let el = e.currentTarget;

			let to_idx = el.dataset.no;
			if (to_idx != null) {
				$('input[name="order_to_idx"]').val(to_idx);

				delivery_address.forEach(function(row) {
					if (parseInt(row.order_to_idx) == parseInt(to_idx)) {
						$('#delivery-info').html(`
							<dl>
								<dt>배송지</dt>
								<dd>${row.to_name} (${row.to_place})</dd>
								<dt>전화번호</dt>
								<dd>${row.to_mobile}</dd>
								<dt>우편번호</dt>
								<dd>${row.to_zipcode}</dd>
								<dt>주소</dt>
								<dd>${row.to_road_addr}</dd>
								<dt>상세주소</dt>
								<dd>${row.to_detail_addr}</dd>
							</dl>
						`);

						caclDelivery_price(row.delivery_price);
					}
				});

				modal_close();
			}
		});
	});
}

function postcodify() {
	if ($('#postcodify').find('postcodify_search_controls').length == 0) {
		$("#postcodify").postcodify({
			insertPostcode5: "#to_zipcode",
			insertAddress: "#to_road_addr",
			insertDetails: "#to_detail_ddr",
			insertJibeonAddress: "#to_lot_addr",
			hideOldAddresses: false,
			results: ".post-change-result",
			hideSummary: true,
			useFullJibeon: true,
			useCors: false,
			onReady: function () {
				document.querySelector(".post-change-result").style.display = "none";
				$(".postcodify_search_controls .keyword_label").text('우편번호 검색');
				$(".postcodify_search_controls .keyword").attr("placeholder", "3글자 이상 입력해주세요.");
				$('.postcodify_search_controls .keyword').attr('chk-flg', 'false');
				// $(".post-change-result").hide();
			},
			onSuccess: function () {
				document.querySelector(".post-change-result").style.display = "block";
				$("#postcodify div.postcode_search_status.too_many").hide();
				// $(".post-change-result").hide();

				$('.input-row').css('position', 'relative');
				$('.post-change-result').css('position', 'absolute');
				$('.post-change-result').css('top', '-12px');
			},
			afterSelect: function (selectedEntry) {

				$("#postcodify div.postcode_search_result").remove();
				$("#postcodify div.postcode_search_status.too_many").hide();
				$("#postcodify div.postcode_search_status.summary").hide();
				document.querySelector(".post-change-result").style.display = "none";
				$("#entry_box").show();
				$("#entry_details").focus();
				$(".postcodify_search_controls .keyword").val($("#road_addr").val());

				$('.input-row').css('position', 'relative');
				$('.post-change-result').css('position', 'absolute');
				$('.post-change-result').css('top', '-12px');
			}
		});

		$(".postcodify_search_controls .keyword").css({
			"display": "grid",
			"width": "75%"
		});

		$(".postcodify_search_controls .search_button").css({
			"border": "1px solid #bfbfbf"
		});

		$('.postcodify_search_controls .keyword').keyup(function (e) {
			$('.postcodify_search_controls .keyword').attr('chk-flg', 'false');
		});

		$('.post-change-result.postcodify_search_form.postcode_search_form').on('click', function () {
			$('.postcodify_search_controls .keyword').attr('chk-flg', 'true');
		});
	}
}

function clickBTN_M_add() {
	let btn_add = document.querySelector('.btn_M_add');
	if (btn_add != null) {
		btn_add.addEventListener('click',function(e) {
			let frm_name = "#frm-modal-delivery";
			if ($(`${frm_name} input[name="to_place"]`).val() == null || $(`${frm_name} input[name="to_place"]`).val() == "") {
				alert('배송지명을 입력해주세요.');
				return false;
			}

			if ($(`${frm_name} input[name="to_name"]`).val() == null || $(`${frm_name} input[name="to_name"]`).val() == "") {
				alert('이름을 입력해주세요.');
				return false;
			}

			if ($(`${frm_name} input[name="to_mobile"]`).val() == null || $(`${frm_name} input[name="to_mobile"]`).val() == "") {
				alert('휴대전화를 입력해주세요.');
				return false;
			}

			if ($(`${frm_name} input[name="to_road_addr"]`).val() == null || $(`${frm_name} input[name="to_road_addr"]`).val() == "") {
				alert('주소를 입력해주세요.');
				return false;
			}

			$.ajax({
				url: config.api + "member/address/add",
				headers : {
					country : config.language
				},
				data: $("#frm-modal-delivery").serialize(),
				async: false,
				error: function () {
					makeMsgNoti(config.base_url,'MSG_F_ERR_0039', null);
				},
				success: function (d) {
					if (d.code == 200) {
						getAddress_list();
						
						console.log($('input[name="default_flg"]').prop('checked'));
						console.log(d.data);

						if ($('input[name="default_flg"]').prop('checked')) {
							$('input[name="order_to_idx"]').val(d.data);

							delivery_address.forEach(function(row) {
								if (parseInt(row.order_to_idx) == parseInt(d.data)) {
									$('#delivery-info').html(`
										<dl>
											<dt>배송지</dt>
											<dd>${row.to_name} (${row.to_place})</dd>
											<dt>전화번호</dt>
											<dd>${row.to_mobile}</dd>
											<dt>우편번호</dt>
											<dd>${row.to_zipcode}</dd>
											<dt>주소</dt>
											<dd>${row.to_road_addr}</dd>
											<dt>상세주소</dt>
											<dd>${row.to_detail_addr}</dd>
										</dl>
									`);
								}

								let product = parseInt(document.querySelector("#result-goods-total").dataset.price);

								let delivery_price = 0;
								if (config.language == "KR" && product < 80000) {
									delivery_price = 2500;
								} else if (config.language == "EN" && product < 300) {
									delivery_price = parseInt(row.delivery_price);
								}
								
								setPayTotal(null,null,null,null,delivery_price);

								modal_close();
							});
						} else {
							$('.modal-pay-delivery .tab-container li').eq(0).click();

							$('#frm-modal-delivery input[type="text"]').val('');
							$('#frm-modal-delivery input[type="number"]').val('');
						}
					} else {
						alert(
							d.msg,
							function() {
								if (d.code == 401) {
									sessionStorage.setItem('r_url',location.href);
									location.href = `${config.base_url}/login`;
								}
							}
						)
					}
				}
			});
		});
	}
}

function caclDelivery_price(delivery_price) {
	let product		= document.querySelector("#result-goods-total").dataset.price;
	let member		= document.querySelector("#result-goods-discount").dataset.price;
	let voucher		= document.querySelector('#result-use-voucher').dataset.price;
	let mileage		= document.querySelector('#result-use-mileage').dataset.price;

	let delivery	= 0;
	if (product < 80000) {
		delivery = delivery_price;
	}
	
	setPayTotal(product,member,voucher,mileage,delivery);
}

</script>