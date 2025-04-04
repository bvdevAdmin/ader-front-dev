let progress = {
	'APL'	: 1,	/* 01. 신청 */
	'HOS_F'	: 2,	/* 02. 제품회수 */
	'HOS_T'	: 3,	/* 02. 제품회수 */
	'RPR_F'	: 4,	/* 03. 수선 */
	'RPR_T'	: 5,	/* 03. 수선 */
	'APG_F'	: 6,	/* 04. 결제 */
	'APG_T'	: 7,	/* 04. 결제 */
	'DLV'	: 8,	/* 05. 배송 */
	'ACP'	: 9,	/* 06. 완료 */
	
	'RPA'	: 98,
	'URP'	: 99
}

/* 배송 회수 회사 */
let tui_housing_company = [];

/* 회원 배송지 */
let delivery_address = [];

$(document).ready(function (e) {
	/* 회원 A/S 현황 조회 */
    $.ajax( {
        url : config.api + "as/get",
        headers : {
            country : config.language
        },
		data : {
			as_idx : location.pathname.split("/")[5]
		},
		success : function(d) {
			if (d.code == 200) {
				let data = d.data;
				if (data.as_info != null) {
					let as_info = data.as_info;
					
					console.log(' [ AS PROGRESS ] ',progress[as_info.as_status]);
					
					let progress_val = parseInt(progress[as_info.as_status]);
					let div_progress = document.querySelectorAll('.div_progress');

					if (as_info.as_status == "ACP" && as_info.as_repair_type == "URP") {
						if (div_progress != null && div_progress.length > 0) {
							div_progress.forEach(div => {
								let status = parseInt(div.dataset.status);
								if (status == 1) {
									div.classList.remove('hidden');
								}
							});
						}
					} else {
						if (div_progress != null && div_progress.length > 0) {
							div_progress.forEach(div => {
								let status = parseInt(div.dataset.status);
								if (progress_val >= status) {
									div.classList.remove('hidden');
								}
							});
						}
					}
					
					/* ========== 01. 신청 ========== */
					
					$("#as_code").text(as_info.as_code);					/* A/S 코드 */
					$("#as_date").text(as_info.create_date);				/* A/S 신청일 */
					$('#as_status').append(as_info.t_as_status);			/* A/S 진행현황 */
					
					/* 회원 A/S - 신청제품 */
					if (as_info.product_idx > 0) {
						$(".goods").append(`
							<span class="image" style="background-image:url('${config.cdn}${as_info.img_location}')"></span>
							<div class="name">${as_info.product_name}</div>
							<div class="price">
								<span class="price">
									${as_info.price}
								</span>
							</div>
							<div class="color">
								${as_info.color}
								<span class="colorchip" style="background-color:${as_info.color_rgb}"/>
							</div>
							<div class="size">
								${as_info.option_name}
							</div>
						`)
					} else {
						$('.as_product').hide();
					}
					
					/* 회원 A/S - 블루마크 인증정보 */
					if (as_info.bluemark_flg == true) {
						$("#purchase-mall").text(as_info.purchase_mall);	/* 구매처 */
						$("#serial-code").text(as_info.serial_code);		/* 블루마크 인증번호 */
						$("#reg-date").text(as_info.reg_date);				/* 블루마크 인증일 */
					} else {
						$('.buy').hide();
					}
					
					/* A/S 신청내용 */
					$("#as-contents").append(as_info.as_contents);	/* A/S 신청내용 */
					
					/* ========== 02. 제품회수 ========== */
					
					/* 회원 A/S - 반환 배송지 설정 */
					let t_column = {
						KR : {
							't_01' : "배송지",
							't_02' : "전화번호",
							't_03' : "우편번호",
							't_04' : "주소",
							't_05' : "상세주소",
							't_05' : "메모"
						},
						EN : {
							't_01' : "Place name",
							't_02' : "Mobile number",
							't_03' : "Zipcode",
							't_04' : "Address",
							't_05' : "Detail address",
							't_06' : "Memo"
						}
					}

					if (as_info.to_idx != null) {
						$('input[name="to_idx"]').val(as_info.to_idx);
						
						document.querySelector('.wrap__select').classList.add('hidden');
						document.querySelector('.btn_address').classList.add('hidden');
					}
					
					let order_to = data.order_to;
					if (order_to != null) {
						$('input[name="to_idx"]').val(order_to.to_idx);
						
						$('#delivery-info').html(`
							<dl>
								<dt>${t_column[config.language]['t_01']}</dt>
								<dd>${order_to.to_name} (${order_to.to_place})</dd>
								<dt>${t_column[config.language]['t_02']}</dt>
								<dd>${order_to.to_mobile}</dd>
								<dt>${t_column[config.language]['t_03']}</dt>
								<dd>${order_to.to_zipcode}</dd>
								<dt>${t_column[config.language]['t_04']}</dt>
								<dd>${order_to.txt_addr}</dd>
								<dt>${t_column[config.language]['t_05']}</dt>
								<dd>${order_to.to_detail_addr}</dd>
							</dl>
						`);
					}
					
					data.as_memo.forEach(row => {
						$(".as_memo").append(`
							<option value="${row.memo_idx}" data-direct="${(row.direct_flg == 1) ? 'T' : 'F' }">
								${row.memo_txt}
							</option>
						`);
					});
					
					let as_memo = $('.as_memo');
					as_memo.on('change',function(e) {
						let direct = $('select[name="as_memo"] option:selected').data('direct');
						if (direct == "T") {
							document.querySelector('.as_message').classList.remove('hidden');
						} else {
							document.querySelector('.as_message').classList.add('hidden');
							document.querySelector('.as_message').value = "";
						}
					});
					
					/* (설정) 제품회수 배송업체 */
					let housing_company = data.housing_company;
					if (housing_company != null && housing_company.length > 0) {
						let data_company = [];
						
						$('.deli-company-list').html('');
						
						let t_placeholder = {
							KR : "배송업체를 선택해주세요.",
							EN : "Please select the delivery company."
						}
						
						housing_company.forEach(company => {
							let tmp_data = {
								'value'		:company.housing_idx,
								'label'		:company.housing_company
							};
							
							data_company.push(tmp_data);
						});
						
						tui_housing_company = new tui.SelectBox('.deli-company-list', {
							placeholder		:t_placeholder[config.language],
							data			:data_company,
							autofocus		:false
						});
					}

					if (as_info.d_payment != null) {
						document.querySelector('.housing_F').classList.add('hidden');
					}
					
					/* 회원 A/S - 회수정보 */
					if (as_info.housing_company != null) {
						document.querySelector('.div_progress.housing_F').classList.add('hidden');
						$('.housing_T .housing_company').text(as_info.housing_company);
						$('.housing_T .housing_num').text(as_info.housing_num);
						$('.housing_T .housing_start_date').text(as_info.housing_start_date);
						$('.housing_T .housing_end_date').text(as_info.housing_end_date);
					} else {
						document.querySelector('.div_progress.housing_T').classList.add('hidden');
					}
					
					/* 회원 A/S - 완료 예정일 */
					$("#completion-date").append(as_info.completion_date);	/* A/S 완료예정일 */
					
					/* 회원 A/S - 배송지 목록 모달 표시 */
					clickBTN_delivery();
					
					/* 회원 A/S - A/S 완료 후 제품 배송지 선택 */
					clickBTN_address();
					
					/* 회원 A/S - 제품 회수 방법 */
					clickBTN_type();
					
					/* 회원 A/S - 회수 배송비 결제 */
					clickBTN_D_payment();
					
					/* ========== 03. 수선 ========== */
					
					/* A/S 수선내용 */
					$("#repair-desc").append(as_info.repair_desc);			/* A/S 수선내용 */
					$("#as-price").append(as_info.t_as_price);				/* A/S 수선비용 */
					
					/* ========== 04. 결제 대기 ========== */
					
					/* A/S 결제정보 */
					if (as_info.pg_payment != null) {
						if (as_info.pg_payment != "FREE" && as_info.as_price > 0) {
							let t_payment = {
								KR : {
									't_01' : "결제일",
									't_02' : "결제비용",
									't_03' : "결제수단",
									't_04' : "영수증 보기",
									't_05' : "무상수선"
								},
								EN : {
									't_01' : "Payment date",
									't_02' : "Payment price",
									't_03' : "Payment method",
									't_04' : "Receipt",
									't_05' : "Free to repair"
								}
							}
							$("#payment-info").append(`
								<dl>
									<dt>${t_payment[config.language]['t_01']}</dt>
									<dd><font class="housing_company">${as_info.pg_date}</font></dd>
									
									<dt>${t_payment[config.language]['t_02']}</dt>
									<dd><font class="housing_num">${Number(as_info.pg_price).toLocaleString()}</font></dd>
									
									<dt>${t_payment[config.language]['t_03']}</dt>
									<dd><font class="housing_start_date">${as_info.pg_payment}</font></dd>
								</dl>

								<br><br>
								<button type="button" id="btn-view-receipt" class="btn small">${t_payment[config.language]['t_04']}</button>
							`);
							
							$('#btn-view-receipt').click(function() {
								if (as_info.pg_receipt_url != null) {
									javascript:void(window.open(`${as_info.pg_receipt_url}`,'','width=800, height=1600'));
								}
							});
							
							document.querySelector('.div_payment').classList.add('hidden');
						} else {
							$("#payment-info").append(`${t_payment[config.language]['t_05']}`);
							document.querySelector('.div_payment').classList.add('hidden');
						}
					}
					
					/* A/S 수선비용 결제 버튼 클릭 */
					clickBTN_P_payment();
					
					/* ========== 05. 배송 ========== */
					
					/* A/S - 배송정보 */
					if (as_info.delivery_company != null) {
						$('.delivery_company').text(as_info.delivery_company);
						$('.delivery_num').text(as_info.delivery_num);
						$('.delivery_start_date').text(as_info.delivery_start_date);
						$('.delivery_end_date').text(as_info.delivery_end_date);
					} else {
						document.querySelector('.div_delivery').classList.add('hidden');
					}
					
					/* ========== 06. 완료 ========== */
					
					/* 회원 A/S - 완료정보 */
					if (as_info.as_complete_date != null) {
						$('.complete_date').text(as_info.as_complete_date);
					}
				}
				
				/* 회원 A/S - 제품 이미지 */
				setAS_img("P",data.as_img_P);
				
				/* 회원 A/S - 구매내역 이미지 */
				setAS_img("R",data.as_img_R);
			}
		}
	});
});

/* 회원 A/S - 제품, 구매내역 이미지 */
function setAS_img(img_type,data) {
	if (data != null && data.length > 0) {
		data.forEach(row => {
			$(`#attach-file_${img_type}`).append(`
				<span class="image" style="background-image:url('${config.cdn}${row.img_location}')"></span>
			`);
		});
	} else {
		$(`.div_img_${img_type}`).addClass('hidden');
	}
}

/* 회원 A/S - A/S 완료 후 제품 배송지 */
function clickBTN_address() {
	let btn_address = document.querySelector('.btn_address');
	if (btn_address != null) {
		btn_address.addEventListener('click',function() {
			let msg_alert = {
				KR : {
					't_01' : "제품을 반환받을 배송지를 선택해주세요.",
					't_02' : "주문 메모를 선택해주세요.",
					't_03' : "주문 메모를 입력해주세요."
				},
				EN : {
					't_01' : "Please select the address.",
					't_02' : "Please select the memo.",
					't_03' : "Please enter the message."
				}
			}
			let to_idx = $('input[name="to_idx"]').val();
			if (to_idx == 0 || to_idx == "") {
				alert(msg_alert[config.language]['t_01']);
				return false;
			}
			
			let as_memo	= $('select[name="as_memo"]').val();
			let as_msg	= null;
			
			if (as_memo == 0) {
				alert(msg_alert[config.language]['t_02']);
				return false;
			} else {
				if ($('select[name="as_memo"] option:selected').data('direct') == "T") {
					as_msg = $('input[name="as_message"]').val();
					if (as_msg == null || as_msg == "") {
						alert(msg_alert[config.language]['t_03']);
						return false;
					}
				}
			}
			
			$.ajax( {
				url : config.api + "as/put",
				headers : {
					country : config.language
				},
				data : {
					action_type		: "ADDR",
					as_idx			: location.pathname.split("/")[5],
					to_idx			: to_idx,
					as_memo			: as_memo,
					as_message		: as_msg
				},
				success : function(d) {
					document.querySelector('.wrap__select').classList.add('hidden');
					document.querySelector('.btn_address').classList.add('hidden');
					
					let t_column = {
						KR : {
							't_01' : "배송지",
							't_02' : "전화번호",
							't_03' : "우편번호",
							't_04' : "주소",
							't_05' : "상세주소",
							't_06' : "배송메모"
						},
						EN : {
							't_01' : "Place",
							't_02' : "Mobile number",
							't_03' : "Zipcode",
							't_04' : "Address",
							't_05' : "Detail address",
							't_06' : "Memo"
						}
					}

					$('#delivery-info').html(`
						<dl>
							<dt>${t_column[config.language]['t_01']}</dt>
							<dd>${d.data.to_name} (${d.data.to_place})</dd>
							<dt>${t_column[config.language]['t_02']}</dt>
							<dd>${d.data.to_mobile}</dd>
							<dt>${t_column[config.language]['t_03']}</dt>
							<dd>${d.data.to_zipcode}</dd>
							<dt>${t_column[config.language]['t_04']}</dt>
							<dd>${d.data.txt_addr}</dd>
							<dt>${t_column[config.language]['t_05']}</dt>
							<dd>${d.data.to_detail_addr}</dd>
							
							<dt>${t_column[config.language]['t_06']}</dt>
							<dd>${d.data.as_memo}</dd>
						</dl>
					`);
					
					let msg_confirm = {
						KR : "배송지 정보가 등록되었습니다.",
						EN : "Address has registered."
					}

					alert(msg_confirm[config.language]);
				}
			});
		});
	}
}

/* A/S 신청제품 반환 배송지 선택 */
function clickBTN_delivery() {
	let btn_delivery = document.querySelector('.btn_delivery');
	if (btn_delivery != null) {
		btn_delivery.addEventListener('click',function() {
			$.ajax({
				url: config.modal + `as-delivery-${config.language}`,
				headers : {
					country : config.language
				},
				data: null,
				dataType: "text",
				error: function(msg) {
					alert("오류가 발생했습니다");
				},
				success: function(d) {
					if(d != "") {
						let id = `_modal_alert_${new Date().getTime()}_${$("body > .modal.alert").length + 1}`;
						$("body").addClass("on-modal").append(`<section class="modal" id="${id}">${d}</section>`);
						if(typeof set_ui == "function") set_ui($(`#${id}`));
						$(`#${id}`).find("button.close,button.cancel").click(function() {
							if(window.location.hash == "") {
								modal_close();
							}
							else {
								window.location.hash = "close";
							}
						});
						setTimeout(function() {
							$(`#${id}`).addClass("on");
						},1);
					}
				}
			});
		});
	}
}

/* 회원 A/S - 제품 회수방법 선택 처리 */
function clickBTN_type() {
	let btn_type = document.querySelectorAll('.btn_type');
	if (btn_type != null && btn_type.length > 0) {
		btn_type.forEach(btn => {
			btn.addEventListener('click',function(e) {
				let el = e.currentTarget;
				
				let housing_type = el.dataset.housing_type;
				if (housing_type != null) {
					let btn_pickup = $('.btn_type.pickup');
					let btn_direct = $('.btn_type.direct');
					
					let div_pickup = document.querySelector('.order-description-pickup');
					let div_direct = document.querySelector('.order-description-direct');
					
					if (housing_type == "APL") {
						/* 반송 - 수거신청 */
						if (!el.classList.contains('bk')) {
							btn_pickup.removeClass('wh');
							btn_pickup.addClass('bk');
							
							btn_direct.addClass('wh');
							btn_direct.removeClass('bk');
							
							div_pickup.classList.remove("hidden");
							div_direct.classList.add("hidden");
						} else {
							btn_pickup.addClass('wh');
							btn_pickup.removeClass('bk');
							
							div_pickup.classList.add("hidden");
							div_direct.classList.add("hidden");
						}
					} else if (housing_type == "DRC") {
						/* 반송 - 직접발송 */
						if (!el.classList.contains('bk')) {
							btn_pickup.addClass('wh');
							btn_pickup.removeClass('bk');
							
							btn_direct.removeClass('wh');
							btn_direct.addClass('bk');
							
							if (div_pickup != null) {
								div_pickup.classList.add("hidden");
							}
							
							div_direct.classList.remove("hidden");
						} else {
							btn_direct.addClass('wh');
							btn_direct.removeClass('bk');
							
							if (div_pickup != null) {
								div_pickup.classList.add("hidden");
							}

							div_direct.classList.add("hidden");
						}
					}
				}
			});
		})
	}
}

/* 회원 A/S - 배송비 결제 처리 */
function clickBTN_D_payment() {
	let btn_housing = document.querySelector('.btn_housing');
	if (btn_housing != null) {
		btn_housing.addEventListener('click',function() {
			let as_idx = location.pathname.split("/")[5];
			
			let housing_type = "";
			let housing_idx = 0;
			let housing_num = null;

			let msg_alert = {
				KR : {
					't_01' : "배송 방법을 선택해주세요.",
					't_02' : "배송 업체를 선택해주세요.",
					't_03' : "운송장 번호를 입력해주세요.",
					't_04' : "A/S 신청제품의 반송정보가 등록되었습니다."
				},
				EN : {
					't_01' : "Please select the delivery method.",
					't_02' : "Please select the delivery company.",
					't_03' : "Please enter the shipping number.",
					't_04' : "Return information has been registered."

				}
			}

			let btn_type = document.querySelector('.btn_type.bk');
			if (btn_type != null) {
				housing_type = btn_type.dataset.housing_type;
			} else {
				alert(msg_alert[config.language]['t_01']);
				return false;
			}

			if (housing_type == "DRC") {
				let selected_company = tui_housing_company.getSelectedItem();
				if (selected_company != null) {
					housing_idx = selected_company.value;
					if (isNaN(housing_idx)) {
						alert(msg_alert[config.language]['t_02']);
						return false;
					}
				} else {
					alert(msg_alert[config.language]['t_02']);
					return false;
				}

				housing_num = document.querySelector('.housing_num').value;
				if (!housing_num || housing_num.length == 0) {
					alert(msg_alert[config.language]['t_03']);
					return false;
				}
			}
			
			$.ajax({
				url : config.api + "as/put",
				headers : {
					country : config.language
				},
				data : {
					'action_type'	: "HOS",
					'as_idx'		: as_idx,
					'housing_type'  : housing_type,
					'housing_idx'	: housing_idx,
					'housing_num'	: housing_num
				},
				success : function(d) {
					if (d.code == 200) {
						if (d.data != null) {
							setToss_payment(d.data.as_price,d.data.payment_code);
						} else {
							alert(
								msg_alert[config.language]['t_04'],
								function() {
									location.href = `${config.base_url}/my/as/status`;
								}
							);
						}
					} else {
						alert(
							d.msg,
							function () {
								if (d.code == 401) {
									sessionStorage.setItem('r_url',location.href);
									location.href = `${config.base_url}/login`;
								}
							}
						);
					}
				}
			});
		});
	}
}

/* A/S 수선비용 결제 버튼 클릭 */
function clickBTN_P_payment() {
	let btn_payment = document.querySelector('.btn_payment');
	if (btn_payment != null) {
		btn_payment.addEventListener('click',function() {
			let msg_alert = {
				KR : {
					't_01' : "제품을 반환받을 배송지를 선택해주세요.",
					't_02' : "A/S 신청제품의 반송정보가 등록되었습니다."
				},
				EN : {
					't_01' : "Please select the address to return.",
					't_02' : "Return information has been registered."

				}
			}

			let to_idx = $('input[name="to_idx"]').val();
			if (to_idx == 0 || to_idx == "") {
				alert(msg_alert[config.language]['t_01']);
				return false;
			}
			
			$.ajax({
				url : config.api + "as/put",
				headers : {
					country : config.language
				},
				data : {
					'action_type'	: "APG",
					'as_idx'		: location.pathname.split("/")[5],
					'to_idx'		:to_idx
				},
				success : function(d) {
					if (d.code == 200) {
						if (d.data != null) {
							setToss_payment(d.data.as_price,d.data.payment_code);
						} else {
							alert(
								msg_alert[config.language]['t_02'],
								function() {
									location.href = `${config.base_url}/my/as`;
								}
							);
						}
					} else {
						alert(
							d.msg,
							function () {
								if (d.code == 401) {
									sessionStorage.setItem('r_url',location.href);
									location.href = `${config.base_url}/login`;
								}
							}
						);
					}
				}
			});
		});
	}
}

function setToss_payment(as_price,payment_code) {
	let toss_payments = TossPayments(config.member.pg.key); //결제위젯용
	
	// 결제 정보 설정
	let pay_method = '카드'
		,pay_data = {
			amount			: as_price,
			orderId			: payment_code,
			orderName		: payment_code,
			customerName	: config.member.id,
			successUrl		: `${location.origin}${config.base_url}/my/as/payment`,
			failUrl			: `${location.origin}${config.base_url}/my/as/status`,
		};

	if (config.language != "KR") { // 해외 결제일 경우
		pay_method = '해외간편결제';
		pay_data.useInternationalCardOnly = true;
		pay_data.provider	= "PAYPAL";
		pay_data.currency	= "USD";
		pay_data.country	= "US";
	}

	/* 토스 결제모듈 호출 */
	toss_payments.requestPayment(pay_method,pay_data);
}