<section class="modal-pay-delivery">
	<header>
		Address info
		<button type="button" class="close"></button>
	</header>
	<article>
		<div class="tab">
			<div class="tab-container">
				<ul>
					<li>Address list</li>
					<li>New</li>
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
							<input type="text" name="to_place" required>
							<span class="control-label">Address name</span>
						</div>
						<div class="form-inline inline-label">
							<input type="text" name="to_name" required>
							<span class="control-label">Recipient</span>
						</div>
						<div class="form-inline inline-label">
							<input type="text" name="to_mobile" required>
							<span class="control-label">Mobile number</span>
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
							<input id="to_zipcode" type="text" name="to_zipcode" required>
							<span class="control-label">Zipcode</span>
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
							<label><input type="checkbox" name="default_flg" value="T"><i></i>Set default</label>
						</div>
					</form>
				</article>

				<button type="button" class="btn black btn_M_add">Complete</button>
			</section>
		</div>
	</article>
</section>

<script>

let country_foreign = [];

$(document).ready(function() {
	$('input[name="to_mobile"]').on('keyup',function(e) {
		let value = $(this).val().replace(/\D/g, "");

		let result = value.replace(/^(\d{3})(\d{4})(\d{4})$/,"$1-$2-$3");
		$(this).val(result);
	});
	
	$("section.modal .tab-container > ul > li").eq(0).click();

    getAddress_list();

	clickBTN_M_add();

	getAddress_foreign();
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
						let btn_default	= `<button type="button" class="default" data-no="${row.order_to_idx}">Default address</button>`;

						if (row.default_flg == true) {
							msg_default = `<div class="msg_default">Default address</div>`;

							btn_grid = 2
							btn_default = "";
						}

						str_div += `
							<li data-no="146">
								<div class="info">
									<div class="address">
										<dl>
											<dt>Address name</dt>
											<dd>${row.to_name} (${row.to_place})</dd>
											<dt>Mobile number</dt>
											<dd>${row.to_mobile}</dd>
											<dt>Zipcode</dt>
											<dd>${row.to_zipcode}</dd>
											<dt>Address</dt>
											<dd>${row.txt_addr}</dd>
										</dl>

										${msg_default}
									</div>
								</div>
								<div class="buttons grid-${btn_grid}">
									${btn_default}
									<button type="button" class="select" data-no="${row.order_to_idx}">Select</button>
									<button type="button" class="delete" data-no="${row.order_to_idx}">Delete</button>
								</div>
							</li>
						`;
					});
				} else {
					str_div += `<li class="list__none">There is no address.</li>`;
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
							$('input[name="to_idx"]').val(address_idx);

							delivery_address.forEach(function(row) {
								if (parseInt(row.order_to_idx) == parseInt(address_idx)) {
									$('#delivery-info').html(`
										<dl>
											<dt>Address name</dt>
											<dd>${row.to_name} (${row.to_place})</dd>
											<dt>Mobile number</dt>
											<dd>${row.to_mobile}</dd>
											<dt>Zipcode</dt>
											<dd>${row.to_zipcode}</dd>
											<dt>Address</dt>
											<dd>${row.txt_addr}</dd>
											<dt>Detail address</dt>
											<dd>${row.to_detail_addr}</dd>
										</dl>
									`);
								}
							});

							modal_close();
						} else {
							if (d.msg != null) {
								alert(
									d.msg,
									function() {
										if (d.code == 401) {
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
									Please select the address
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
				$('input[name="to_idx"]').val(to_idx);

				delivery_address.forEach(function(row) {
					if (parseInt(row.order_to_idx) == parseInt(to_idx)) {
						$('#delivery-info').html(`
							<dl>
								<dt>Address name</dt>
								<dd>${row.to_name} (${row.to_place})</dd>
								<dt>Mobile number</dt>
								<dd>${row.to_mobile}</dd>
								<dt>Zipcode</dt>
								<dd>${row.to_zipcode}</dd>
								<dt>Address</dt>
								<dd>${row.txt_addr}</dd>
								<dt>Detail address</dt>
								<dd>${row.to_detail_addr}</dd>
							</dl>
						`);
					}
				});

				modal_close();
			}
		});
	});
}

function clickBTN_M_add() {
	let btn_add = document.querySelector('.btn_M_add');
	if (btn_add != null) {
		btn_add.addEventListener('click',function(e) {
			let frm_name = "#frm-modal-delivery";
			if ($(`${frm_name} select[name="to_country_code"]`).val() == null || $(`${frm_name} select[name="to_country_code"]`).val() == "") {
				alert('Please select the country.');
				return false;
			}

			if ($(`${frm_name} select[name="to_province_idx"]`).val() == null || $(`${frm_name} select[name="to_province_idx"]`).val() == "") {
				alert('Please select the province.');
				return false;
			}

			if ($(`${frm_name} input[name="to_place"]`).val() == null || $(`${frm_name} input[name="to_place"]`).val() == "") {
				alert('Please enter the address name.');
				return false;
			}

			if ($(`${frm_name} input[name="to_name"]`).val() == null || $(`${frm_name} input[name="to_name"]`).val() == "") {
				alert('Please enter the receipt.');
				return false;
			}

			if ($(`${frm_name} input[name="to_mobile"]`).val() == null || $(`${frm_name} input[name="to_mobile"]`).val() == "") {
				alert('Please enter the mobile number.');
				return false;
			}

			if ($(`${frm_name} input[name="to_address"]`).val() == null || $(`${frm_name} input[name="to_address"]`).val() == "") {
				alert('Please enter the address.');
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
											<dt>Place name</dt>
											<dd>${row.to_name} (${row.to_place})</dd>
											<dt>Mobile number</dt>
											<dd>${row.to_mobile}</dd>
											<dt>Zipcode</dt>
											<dd>${row.to_zipcode}</dd>
											<dt>Address</dt>
											<dd>${row.txt_addr}</dd>
											<dt>Detail address</dt>
											<dd>${row.to_detail_addr}</dd>
										</dl>
									`);
								}

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

function getAddress_foreign() {
	$.ajax({
		url: config.api + "member/address/foreign",
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
				let data = d.data;
				if (data != null && data.length > 0) {
					$('#frm-modal-delivery .country').html('');
					$('#frm-modal-delivery .province').html('');
					
					$('#frm-modal-delivery .country').append(`<option value="">Please select the country</option>`);
					data.forEach(function(row) {
						country_foreign[row.country_code] = row;
						$('#frm-modal-delivery .country').append(`<option value="${row.country_code}">${row.country_name}</option>`);
					});

					$('#frm-modal-delivery .province').append(`<option value="">Please select the province</option>`);

					changeCountry_foreign();
				}
			}
		}
	});
}

function changeCountry_foreign() {
	let country = $('#frm-modal-delivery .country');
	if (country != null) {
		country.on('change',function() {
			let country_code = $(this).val();

			let country		= country_foreign[country_code];
			let province	= country.province;

			$('#frm-modal-delivery .province').html('<option value="0">-</option>');
				if (province != null && province.length > 0) {
					$('#frm-modal-delivery .province').html('');
					$('#frm-modal-delivery .province').append(`<option value="">Please select the province</option>`);
					province.forEach(function(row2) {
						$('#frm-modal-delivery .province').append(`<option value="${row2.province_idx}">${row2.province_name}</option>`);
					});
				}
		});
	}
}

</script>