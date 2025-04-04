let delivery_address = [];

let tui_voucher = null;

let msg_voucher = {
	KR : "구매하려는 제품 중 선택 한 바우처의 구매제한 제품이 포함되어 있습니다.",
	EN : "Among the products you want to purchase,<br>the limited purchase product of the voucher you chose is included."
}

$(document).ready(function() {
	let toss_code		= get_query_string('code');
	let toss_message	= get_query_string('message');

	if (toss_code != null && toss_message != null) {
		alert(decodeURI(toss_message));
	}

	if(sessionStorage.getItem("cart_no") == null) {
		alert("결제할 상품이 없습니다.");
		history.back();
		return;
	}
		
	$.ajax({
		url: config.api + 'order/pre',
		headers : {
			country : config.language
		},
		data: { basket_idx : JSON.parse(sessionStorage.getItem("cart_no")) },
		success: function(d) {
			if(d.code == 200) {	
				if (d.data.order_product.length == 0) {
					alert(
						"결제할 상품이 없습니다.",
						function() {
							history.back()
						}
					);
				}
				
				let order_to = d.data.order_to;
				if (order_to != null) {
					$('input[name="order_to_idx"]').val(order_to.to_idx);

					let t_column = {
						KR : {
							't_01' : "배송지",
							't_02' : "전화번호",
							't_03' : "우편번호",
							't_04' : "주소",
							't_05' : "상세주소"
						},
						EN : {
							't_01' : "Place name",
							't_02' : "Mobile number",
							't_03' : "Zipcode",
							't_04' : "Address",
							't_05' : "Detail address"
						}
					}

					$('#delivery-info').html(`
						<dl>
							<dt>${t_column[config.language]['t_01']}</dt>
							<dd>${order_to.to_name} (${order_to.to_place})</dd>
							<dt>${t_column[config.language]['t_02']}</dt>
							<dd>${order_to.to_mobile}</dd>
							<dt>${t_column[config.language]['t_03']}</dt>
							<dd>${order_to.to_zipcode}</dd>
							<dt>${t_column[config.language]['t_04']}</dt>
							<dd>${order_to.to_road_addr}</dd>
							<dt>${t_column[config.language]['t_05']}</dt>
							<dd>${order_to.to_detail_addr}</dd>
						</dl>
					`);
				} else {
					let msg_delivery = {
						KR : "배송지를 선택해주세요.",
						EN : "Please choose your address."
					};

					$('#delivery-info').html(`
						${msg_delivery[config.language]}
					`);
				}

				// 주문 메모 직접 입력
				$("#frm select[name='develiry_msg'] > option")
				
				$("#btn-delivery-change").click(function() {
					$.ajax({
						url: config.modal + `pay-${config.language}-delivery`,
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
				
				// 배송 메시지
				d.data.order_memo.forEach(row => {
					$("#frm select[name='order_memo']").append(`
						<option value="${row.memo_idx}" data-direct="${(row.direct_flg == 1) ? 'T' : 'F' }">
							${row.memo_txt}
						</option>
					`);
				});

				let order_memo = $('.order_memo');
				order_memo.on('change',function(e) {
					let direct = $('select[name="order_memo"] option:selected').data('direct');
					if (direct == "T") {
						document.querySelector('.delivery_message').classList.remove('hidden');
					} else {
						document.querySelector('.delivery_message').classList.add('hidden');
						document.querySelector('.delivery_message').value = "";
					}
				});

				// 주문 제품
				let goods_title 	= d.data.order_product[0].product_name;

				let goods_total		= d.data.total_price;
				let goods_discount	= d.data.total_discount;
				let voucher			= 0;
				let mileage			= 0;
				let delivery		= d.data.price_delivery;
				
				document.querySelector("#result-goods-total").dataset.price		= goods_total;
				document.querySelector("#result-goods-discount").dataset.price	= goods_discount;
				document.querySelector("#result-use-voucher").dataset.price		= voucher;
				document.querySelector("#result-use-mileage").dataset.price		= mileage;
				document.querySelector("#result-delivery-fee").dataset.price	= delivery;

				d.data.order_product.forEach(row => {
					option_name = row.option_name
					if (row.product_type === "S" && Array.isArray(row.set_product)) {
						let setOptions = row.set_product.map(product => product.option_name).join(" / ");
						row.option_name += ` ( ${setOptions} )`;
					}
					$("#list").append(`
						<li class="order_product" data-no="${row.product_idx}">
							<span class="image" style="background-image:url('${config.cdn + row.img_location}')" data-no="${row.product_idx}"></span>
							<div class="name">${row.product_name}</div>
							<div class="price">
								<!--<span class="price">${number_format(row.product_price)}</span>-->
								<div class="price${row.discount > 0 ? ' discount' : ''}" data-discount="${row.discount}" data-saleprice="${row.t_sales_price}">
									${row.t_price}
								</div>
								<br>
								<span class="qty">Qty : ${row.product_qty}</span>
							</div>
							<div class="color">
								${row.color}
								<span class="colorchip" style="background-color:${row.color_rgb}"></span>
							</div>
							<div class="size">${row.option_name}</div>
						</li>
					`);
					let $parentLi = $("#list").children("li").last(); // 가장 최근에 추가된 <li> 요소 선택
					let children = [];
					if (row.product_type == 'S') {
						row.set_product_info.forEach(row2 => {
							const $childLi = $(`
								<li class="${row.product_idx}" style="display: none;">
									<span class="image" style="background-image:url('${config.cdn + row2.img_location}')" ></span>
									<div class="name">${row2.product_name}</div>
									<div class="color">${row2.color}
										<span class="colorchip" style="background-color:${row2.color_rgb}"></span>
									</div>
									<div class="size">${row2.option_name}</div>
								</li>
							`);
							children.push($childLi);
						});

						$parentLi.css('cursor', 'pointer');
						$parentLi.on('click', function () {
							$(`.${row.product_idx}`).stop().slideToggle(300); // 300ms 동안 슬라이드 효과
						});

						children.forEach($childLi => {
							$parentLi.after($childLi); // 부모의 바로 뒤에 자식들을 추가
						});
					}
				});

				let div_product = document.querySelectorAll('#list .image');
				if (div_product != null && div_product.length > 0) {
					div_product.forEach(div => {
						div.addEventListener('click',function(e) {
							let el = e.currentTarget;
							
							let no = el.dataset.no;
							if (no != null) {
								location.href = `${config.base_url}/shop/${no}`;
							}
						});
					});
				}
				
				if(d.data.order_product.length > 1) {
					if(config.language == 'KR') {
						goods_title += ` 외 ${d.data.order_product.length-1}건`;
					}
					else {
						goods_title += ` etc ${d.data.order_product.length-1}`;
					}
				}
				
				// 바우처
				$("#voucher-useful").text(d.data.cnt_usable);
				$("#voucher-has").text(d.data.cnt_voucher);

				let t_voucher = {
					KR : {
						't_01' : "선택 안함.",
						't_02' : "[ 사용가능 ] "
					},
					EN : {
						't_01' : "Do not select.",
						't_02' : "[ Usable ] "
					}
				}

				let data_voucher = [];

				let d_data = {
					'voucher_idx'	: 0,
					'usable'		: "T"
				}

				let d_voucher = {
					'label'		:t_voucher[config.language]['t_01'],
					'value'		:JSON.stringify(d_data)
				};

				data_voucher.push(d_voucher);

				if (d.data.order_voucher != null && d.data.order_voucher.length > 0) {
					d.data.order_voucher.forEach(row => {
						let voucher_name = row.voucher_name;
						let t_usable = "F";

						if (row.usable == true) {
							voucher_name = `${t_voucher[config.language]['t_02']}${row.voucher_name}`;
							t_usable = "T";
						}

						let tmp_data = {
							'voucher_idx'	: row.voucher_idx,
							'usable'		: t_usable
						}

						let tmp_voucher = {
							'label'		:voucher_name,
							'value'		:JSON.stringify(tmp_data)
						};
						
						data_voucher.push(tmp_voucher);
					});
				}

				tui_voucher = new tui.SelectBox('#voucher-select', {
					placeholder: t_voucher[config.language]['t_01'],
					data: data_voucher,
					autofocus: false
				});

				let usable_voucher = 0;
				let usable_mileage = 0;

				let member_info = JSON.parse(sessionStorage.getItem("MEMBER"));

				let msg_usable = {
					KR : {
						't_01' : "사용가능",
						't_02' : "보유"
					},
					EN : {
						't_01' : "Usable",
						't_02' : "Reserves"
					}
				}

				let t_usable	= "";
				let t_max		= "";
				if (config.language == "KR") {
					t_usable	= number_format(config.member.mileage);
					t_max		= number_format(config.member.mileage);
				} else if (config.language == "EN") {
					t_usable	= config.member.mileage.toLocaleString('en-US');
					t_max		= config.member.mileage.toLocaleString('en-US');
				}

				$("#mileage-display > small").html(
					`(${msg_usable[config.language]['t_01']} 0 / ${msg_usable[config.language]['t_02']} ${t_max})`
				);

				if ((config.language == "KR" && parseInt(goods_total) >= 80000) || (config.language == "EN" && parseFloat(goods_total) >= 300)) {
					let c_total = 0;
					if (config.language == "KR") {
						c_total = parseInt(d.data.total_price - d.data.total_discount);
					} else if (config.language == "EN") {
						c_total = parseFloat(d.data.total_price - d.data.total_discount).toFixed(2);
					}

					if ((config.language == "KR" && c_total >= 80000) || (config.language == "EN" && c_total >= 300)) {
						usable_voucher = c_total;
						if (c_total > parseInt(member_info.mileage)) {
							usable_mileage = parseInt(member_info.mileage);
						} else {
							usable_mileage = c_total;
						}
	
						if (config.language == "KR") {
							usable_mileage = Math.floor(parseInt(usable_mileage || 0) / 1000) * 1000;
							
							//보유 마일리지가 10000이상 되어야 사용가능
							usable_mileage = usable_mileage >= 10000 ? usable_mileage : 0;
						}

						if (config.language == "KR") {
							t_usable	= number_format(usable_mileage);
						} else if (config.language == "EN") {
							t_usable	= usable_mileage.toLocaleString('en-US');
						}
					}
					
					$("#mileage-point").attr('max',usable_mileage);
					document.querySelector('#mileage-point').addEventListener("keypress", function(e) {
						if (e.key === "Enter") {
						  e.preventDefault();
						}
					});

					if (tui_voucher != null) {
						tui_voucher.on('change',function () {
							let voucher = 0;
							
							let v_selected = tui_voucher.getSelectedItem();
							if (v_selected != null) {
								let v_value = JSON.parse(v_selected.value);
								
								if (v_value.usable != "F") {
									if (v_value.voucher_idx > 0) {
										$("#mileage-point").val(0);
										voucher = calcVoucher(d.data.order_voucher,v_value.voucher_idx,goods_total);

										if (voucher > usable_voucher) {
											voucher = usable_voucher;
										}
		
										document.querySelector("#result-use-voucher").dataset.price = voucher;
										document.querySelector("#result-use-mileage").dataset.price = 0;
										
										/* 사용가능/보유 적립금 표기 */
										$("#mileage-display > small").html(
											`(${msg_usable[config.language]['t_01']} ${t_usable} / ${msg_usable[config.language]['t_02']} ${t_max})`
										);
									}

									setPayTotal(goods_total,goods_discount,voucher,0,delivery);
								} else {
									tui_voucher.select(null,true)
									$('#voucher-select .tui-select-box-placeholder').text(t_voucher[config.language]['t_01']);
									
									alert(msg_voucher[config.language]);
								}
							}
						});
					}

					/* 사용가능/보유 적립금 표기 */
					$("#mileage-display > small").html(`(${msg_usable[config.language]['t_01']} ${t_usable} / ${msg_usable[config.language]['t_02']} ${t_max})`);

					$("#mileage-button").click(function (e) {
						if ((config.language == "KR" && config.member.mileage >= 10000) || (config.language == "EN" && config.member.mileage >= 10)) {
							if (config.language == "KR" && parseInt(usable_mileage) < 1000) {
								return false;
							} else {
								$("#mileage-point").val(parseInt(usable_mileage));
								
								tui_voucher.select(null,true)
								$('#voucher-select .tui-select-box-placeholder').text(t_voucher[config.language]['t_01']);
	
								document.querySelector("#result-use-voucher").dataset.price = 0;
								document.querySelector("#result-use-mileage").dataset.price = parseInt(usable_mileage);
	
								setPayTotal(goods_total,goods_discount,0,parseInt(usable_mileage),delivery);
							}
						} else {
							let msg_minimum = {
								KR : "보유 적립금이 10,000원 이상인 경우만 사용 가능합니다.",
								EN : "You can use mileage only if your reserve is more than 10 USD."
							}

							$(this).val(0);
							alert(msg_minimum[config.language]);
						}
					})

					$("#mileage-point").on("change", function () {
						if ((config.language == "KR" && parseInt(config.member.mileage) >= 10000) || (config.language == "EN" && parseFloat(config.member.mileage) >= 10)) {
							let value	= 0;
							if ($(this).val() != null && $(this).val() != "") {
								value = parseInt($(this).val());
							}
							
							let calc_mileage = 0;
							if (value > usable_mileage) {
								calc_mileage = usable_mileage;
							} else {
								if (config.language == "KR") {
									if (value >= 1000) {
										calc_mileage = Math.floor(parseInt(value || 0) / 1000) * 1000;
										if (value != calc_mileage) {
											alert('주문 결제 시 적립금은 1,000원 단위부터 사용 가능합니다');
										}
									} else {
										alert('주문 결제 시 적립금은 1,000원 단위부터 사용 가능합니다');
									}
								} else if (config.language == "EN") {
									calc_mileage = value;
								}
							}
							
							$(this).val(calc_mileage);

							/* 사용가능/보유 적립금 표기 */
							$("#mileage-display > small").html(`(${msg_usable[config.language]['t_01']} ${t_usable} / ${msg_usable[config.language]['t_02']} ${t_max})`);
							
							tui_voucher.select(null,true)
							$('#voucher-select .tui-select-box-placeholder').text(t_voucher[config.language]['t_01']);
							
							document.querySelector("#result-use-voucher").dataset.price = 0;
							document.querySelector("#result-use-mileage").dataset.price = calc_mileage;

							setPayTotal(goods_total,goods_discount,0,calc_mileage,delivery);
						} else {
							if (config.language == "KR") {
								$(this).val(0);
								alert('보유 적립금이 10,000원 이상인 경우만 사용 가능합니다.');
							} else if (config.language == "EN") {
								$(this).val(0);
								alert('You can use mileage only if your reserve is more than 10 USD');
							}
						}
					});
				} else {
					tui_voucher.on('change',function () {
						let voucher = 0;

						let v_selected = tui_voucher.getSelectedItem();
						if (v_selected != null) {
							let v_value = JSON.parse(v_selected.value);
							
							if (v_value.usable != "F") {
								if (v_value.voucher_idx > 0) {
									$("#mileage-point").val(0);
									voucher = calcVoucher(d.data.order_voucher,v_value.voucher_idx,goods_total);

									if (voucher > usable_voucher) {
										voucher = usable_voucher;
									}
	
									document.querySelector("#result-use-voucher").dataset.price = voucher;
									document.querySelector("#result-use-mileage").dataset.price = 0;
									
									/* 사용가능/보유 적립금 표기 */
									$("#mileage-display > small").html(
										`(${msg_usable[config.language]['t_01']} ${t_usable} / ${msg_usable[config.language]['t_02']} ${t_max})`
									);
								}
								
								setPayTotal(goods_total,goods_discount,voucher,0,delivery);
							} else {
								tui_voucher.select(null,true)
								$('#voucher-select .tui-select-box-placeholder').text(t_voucher[config.language]['t_01']);
								
								alert(msg_voucher[config.language]);
							}
						}
					});

					$("#mileage-point").on("change", function () {
						if ((config.language == "KR" && parseInt(config.member.mileage) >= 10000) || (config.language == "EN" && parseFloat(config.member.mileage) >= 10)) {
							let value	= 0;
							if ($(this).val() != null && $(this).val() != "") {
								value = parseInt($(this).val());
							}
							
							let calc_mileage = 0;
							if (value > usable_mileage) {
								calc_mileage = usable_mileage;
							} else {
								if (config.language == "KR") {
									if (value >= 1000) {
										calc_mileage = Math.floor(parseInt(value || 0) / 1000) * 1000;
										if (value != calc_mileage) {
											alert('주문 결제 시 적립금은 1,000원 단위부터 사용 가능합니다');
										}
									} else {
										alert('주문 결제 시 적립금은 1,000원 단위부터 사용 가능합니다');
									}
								} else if (config.language == "EN") {
									calc_mileage = value;
								}
							}
							
							$(this).val(calc_mileage);

							/* 사용가능/보유 적립금 표기 */
							$("#mileage-display > small").html(`(${msg_usable[config.language]['t_01']} ${t_usable} / ${msg_usable[config.language]['t_02']} ${t_max})`);
							
							tui_voucher.select(null,true)
							$('#voucher-select .tui-select-box-placeholder').text(t_voucher[config.language]['t_01']);
							
							alert(msg_voucher[config.language]);
							
							document.querySelector("#result-use-voucher").dataset.price = 0;
							document.querySelector("#result-use-mileage").dataset.price = calc_mileage;

							setPayTotal(goods_total,goods_discount,0,calc_mileage,delivery);
						} else {
							if (config.language == "KR") {
								$(this).val(0);
								alert('보유 적립금이 10,000원 이상인 경우만 사용 가능합니다.');
							} else if (config.language == "EN") {
								$(this).val(0);
								alert('You can use mileage only if your reserve is more than 10 USD');
							}
						}
					});
				}

				setPayTotal(goods_total,goods_discount,voucher,mileage,delivery);

				// 결제하기
				let toss_payments = TossPayments(config.member.pg.key); //결제위젯용
				const payment_widget = PaymentWidget(config.member.pg.key, config.member.id);  // 결제위젯 초기화

				$("#frm").submit(function(event) {
					let msg_alert = {
						KR : {
							't_01' : "이용약관, 개인정보수집 및 이용 에 동의해주세요.",
							't_02' : "배송지를 선택해주세요.",
							't_03' : "주문 메모를 선택해주세요.",
						},
						EN : {
							't_01' : "Please agree the terms.",
							't_02' : "Please select the address.",
							't_03' : "Please select the memo."
						}
					}
					event.preventDefault();
					if($(this).find("input[name='terms_agree']:checked").length == 0) {
						alert(msg_alert[config.language]['t_01']);
						return false;
					}
					
					let order_to_idx = $('input[name="order_to_idx"]').val();
					if (order_to_idx == 0 || order_to_idx == "") {
						alert(msg_alert[config.language]['t_02']);
						return false;
					}

					let order_memo = $('input[name="order_memo"]').val();
					if (order_memo == 0) {
						alert(msg_alert[config.language]['t_03']);
						return false;
					} else {
						if ($('select[name="order_memo"] option:selected').data('direct') == "T") {
							let delivery_msg = $('input[name="delivery_message"]').val();
							if (delivery_msg == null || delivery_msg == "") {
								alert(msg_alert[config.language]['t_03']);
								return false;
							}
						}
					}
					let data = new FormData($(this).get(0));
					data.append("basket_idx", JSON.parse(sessionStorage.getItem("cart_no")));
					
					let voucher_idx = 0;
					let v_selected = tui_voucher.getSelectedItem();
					if (v_selected != null) {
						let v_value = JSON.parse(v_selected.value);
						voucher_idx = v_value.voucher_idx;
					}

					data.append("voucher_idx", voucher_idx);

					$.ajax({
						url: config.api + "order/set",
						headers : {
							country : config.language
						},
						async: true,
						enctype: "multipart/form-data",
						processData: false,
						contentType: false,
						data,
						error: function () {
							makeMsgNoti("MSG_F_ERR_0023", null);
						},
						success: function (od) {
							if(od.code == 200) {
								if (od.data.price_total > 0) {
									// 결제 정보 설정
									let pay_method = '카드'
										, pay_data = {
											amount		: od.data.price_total,
											orderId		: od.data.order_code,
											orderName	: goods_title,
											customerName: od.data.member_name,
											successUrl	: `${location.origin}${config.base_url}/pay-check`,
											failUrl		: `${location.origin}${config.base_url}/pay`,
										};

									if (config.language != "KR") { // 해외 결제일 경우
										pay_method = '해외간편결제';
										pay_data.useInternationalCardOnly = true;
										pay_data.provider	= "PAYPAL";
										pay_data.currency	= "USD";
										pay_data.country	= "US";
									}

									// 토스 결제모듈 호출
									toss_payments.requestPayment(pay_method,pay_data);
								} else {
									location.href = `${config.base_url}/pay-check?order_code=${od.data.order_code}`;
								}
							} else {
								alert(
									od.msg,
									function() {
										if (od.code == 401) {
											sessionStorage.setItem('r_url',location.href);
											location.href = `${config.base_url}/login`;
										}
									}
								);
							}
						}
					});

					return false;
				});

			} else {
				alert(
					d.msg,
					function() {
						location.href = config.base_url;
					}
				)
			}
		}
	});
});

function setPayTotal(product,discount,voucher,mileage,delivery) {
	if (config.language == "KR") {
		if (product == null) {
			product = parseInt(document.querySelector("#result-goods-total").dataset.price);
		}
	
		if (discount == null) {
			discount = parseInt(document.querySelector("#result-goods-discount").dataset.price);
		}
	
		if (voucher == null) {
			voucher = parseInt(document.querySelector("#result-use-voucher").dataset.price);
		}
	
		if (mileage == null) {
			mileage = parseInt(document.querySelector("#result-use-mileage").dataset.price);
		}
	
		if (delivery == null) {
			delivery = parseInt(document.querySelector("#result-delivery-fee").dataset.price);
		}
	
		// 제품 합계
		$("#result-goods-total").text(number_format(product));
		
		// 회원 할인
		$("#result-goods-discount").text(number_format(discount));
		
		// 바우처 사용</dt>
		$("#result-use-voucher").text(number_format(voucher));
		
		// 적립금 사용</dt>
		$("#result-use-mileage").text(number_format(mileage));
		
		// 배송비</dt>
		$("#result-delivery-fee").text(number_format(delivery));
	} else if (config.language == "EN") {
		if (product == null) {
			product = parseFloat(document.querySelector("#result-goods-total").dataset.price);
		}
	
		if (discount == null) {
			discount = parseFloat(document.querySelector("#result-goods-discount").dataset.price);
		}
	
		if (voucher == null) {
			voucher = parseFloat(document.querySelector("#result-use-voucher").dataset.price);
		}
	
		if (mileage == null) {
			mileage = parseFloat(document.querySelector("#result-use-mileage").dataset.price);
		}
	
		if (delivery == null) {
			delivery = parseFloat(document.querySelector("#result-delivery-fee").dataset.price);
		}
	
		// 제품 합계
		$("#result-goods-total").text(product.toLocaleString('en-US'));
		
		// 회원 할인
		$("#result-goods-discount").text(discount.toLocaleString('en-US'));
		
		// 바우처 사용</dt>
		$("#result-use-voucher").text(voucher.toLocaleString('en-US'));
		
		// 적립금 사용</dt>
		$("#result-use-mileage").text(mileage.toLocaleString('en-US'));
		
		// 배송비</dt>
		$("#result-delivery-fee").text(delivery.toLocaleString('en-US'));
	}
	
	// 최종 결제 금액</dt>
	const total_amount = product - discount - voucher - mileage + delivery;
	if (config.language == "KR") {
		$("#result-total").text(number_format(total_amount));
	} else if (config.language == "EN") {
		$("#result-total").text(total_amount.toLocaleString('en-US'));
	}
}

function calcVoucher(data,voucher_idx,goodsTotal) {
	let voucher = 0;

	let selectedVoucher = data.find(row => row.voucher_idx == voucher_idx);
	if(selectedVoucher.sale_type == 'PER') {
		voucher = Math.round(goodsTotal * (selectedVoucher.sale_price / 100))
	} else if (selectedVoucher.sale_type == 'PRC') {
		voucher = selectedVoucher.sale_price
	}
	
	return voucher
}