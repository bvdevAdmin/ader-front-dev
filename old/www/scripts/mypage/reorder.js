document.addEventListener('DOMContentLoaded', function () {
	$('.reorder__alarm__wrap').hide();
	$('.reorder__cancel__wrap').hide();
	
	clickReorderTab();
	getReorderList('apply');
});

function clickReorderTab() {
	let reorder_tab = document.querySelectorAll('.reorder_tab');
	reorder_tab.forEach(tab => {
		tab.addEventListener('click',function(e) {
			let el = e.currentTarget;
			let list_type = el.dataset.list_type;
			
			if (list_type != null) {
				getReorderList(list_type);
			}
		});
	});	
}

function getReorderList(list_type) {
	let use_form = $('#frm-reorder-list');
	
	var rows = use_form.find('input[name="rows"]').val();
	var page = use_form.find('input[name="page"]').val();
	
	$.ajax({
		type: "post",
		url: api_location + "mypage/reorder/get",
		data: {
			'list_type': list_type,
			'rows': rows,
			'page': page
		},
		dataType: "json",
		error: function (d) {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0012', null);
			//notiModal("리오더", "재주문 목록을 불러오지 못했습니다.");
		},
		success: function (d) {
			if (d.code == 200) {
				let result_table_wb = $('#' + list_type + '_reorder_result_table');
				result_table_wb.html('');
				
				let result_table_mo = $('#' + list_type + '_reorder_result_table_mobile');
				result_table_mo.html('');
				
				let reorder_result_wb_html = "";
				let reorder_result_mo_html = "";
				
				let data = d.data;
				if (data != null && data.length > 0) {
					d.data.forEach(function (row) {
						let product_type = row.product_type;
						
						let set_toggle_html = "";
						if (product_type == "S") {
							set_toggle_html = `
								<div>
									<img class="set_toggle" data-reorder_idx="${row.reorder_idx}" data-action_type="show" src="/images/mypage/mypage_down_tab_btn.svg">
								</div>
							`;
						}						
						
						let reorder_btn_html = '';
						switch (list_type) {
							case 'apply':
								reorder_btn_html = `
									<p>신청완료</p>
									<div class="reorder_reapply_btn reorder_btn" data-reorder_idx="${row.reorder_idx}" data-action_type="cancel" data-i18n="r_cancel">
										신청취소
									</div>
								`;
								break;
							
							case 'alarm':
								reorder_btn_html = `
									<p data-i18n="r_reorder_notified">알림완료</p>
									<p>${row.update_date}</p>
								`;
								
								break;
							
							case 'cancel':
								reorder_btn_html = `
									<p data-i18n="r_reorder_cancelled">취소완료</p>
									<div class="reorder_reapply_btn r_margin reorder_btn" data-reorder_idx="${row.reorder_idx}" data-action_type="re_apply" data-i18n="r_notify_me";">
										재신청
									</div>
								`;

								 break;
						}
						
						let set_product_wb_html = "";
						let set_product_mo_html = "";
						
						let set_product_info = row.set_product_info;
						if (set_product_info != null && set_product_info.length > 0) {
							set_product_info.forEach(set => {
								set_product_wb_html += `
									<tr class="set_product hidden" data-parent_idx="${row.reorder_idx}">
										<td>
											<img src="${cdn_img}${set.img_location}" style="object-fit:contain">
										</td>
										<td class="vertical__top">
											<p style="white-space:nowrap;">
												${set.product_name}
											</p>
											<p></p>
											<div class="color_wrap">
												<p>${set.color}</p>
												<div class="color_chip" style="background-color:${set.color_rgb}"></div>
											</div>
											<p>${set.option_name}</p>
										</td>
										<td>
											<p>Qty: 1</p>
										</td>
										<td></td>
									</tr>
								`;
								
								set_product_mo_html += `
									<tr class="set_product hidden" data-parent_idx="${row.reorder_idx}">
										<td>
											<img src="${cdn_img}${set.img_location}" style="object-fit:contain">
										</td>
										<td class="vertical__top">
											<p style="white-space:nowrap;">
												${set.product_name}
											</p>
											<p></p>
											<div class="color_wrap">
												<p>${set.color}</p>
												<div class="color_chip" style="background-color:${set.color_rgb}"></div>
											</div>
											<p>${set.option_name}</p>
										</td>
										<td>
											<p>Qty: 1</p>
										</td>
										<td></td>
									</tr>
								`;
							});
						}
						
						reorder_result_wb_html += `
							<tr>
								<td>
									<img src="${cdn_img}${row.img_location}">
								</td>
								<td>
									<p class="product_name">
										${row.product_name}
									</p>
								</td>
								<td>
									<div class="color_wrap">
										<p>${row.color}</p>
										<div class="color_chip" style="background-color:${row.color_rgb}"></div>
									</div>
								</td>
								<td>
									<p>${row.option_name}</p>
								</td>
								<td>
									<p>${row.sales_price_kr}</p>
								</td>
								<td>
									<div class="reorder_btn_wrap">
										<div class="text__btn__area">
											${reorder_btn_html}
										</div>
										${set_toggle_html}
									</div>
								</td>
							</tr>
							${set_product_wb_html}
						`;
						
						reorder_result_mo_html += `
							<tr>
								<td>
									<img src="${cdn_img}${row.img_location}" style="object-fit:contain">
								</td>
								<td class="vertical__top">
									<p style="white-space:nowrap;">
										${row.product_name}
									</p>
									<p>${row.sales_price_kr}</p>
									<div class="color_wrap">
										<p>${row.color}</p>
										<div class="color_chip" style="background-color:${row.color_rgb}"></div>
									</div>
									<p>${row.option_name}</p>
								</td>
								<td>
									<p>Qty: 1</p>
								</td>
								<td>
									<div class="reorder_btn_wrap">
										<div class="text__btn__area mobile cancel">
											${reorder_btn_html}
										</div>
										${set_toggle_html}
									</div>
								</td>
							</tr>
							${set_product_mo_html}
						`;
					});
				} else {
					let exception_msg = "";

					switch (getLanguage()) {
							case "KR" :
									exception_msg = "조회 결과가 없습니다.";
									break;
							
							case "EN" :
									exception_msg = "There is no history.";
									break;
							
							case "CN" :
									exception_msg = "没有查询到相关资料。​";
									break;

					}

					reorder_result_wb_html = `
						<tr>
							<td colspan="6" style="text-align:center">
								<p>${exception_msg}</p>
							</td>
						</tr>
					`;
					
					reorder_result_mo_html = `
						<tr>
							<td colspan="4" style="text-align:center">
								<p>${exception_msg}</p>
							</td>
						</tr>
					`;
				}
				
				result_table_wb.append(reorder_result_wb_html);
				result_table_mo.append(reorder_result_mo_html);
				
				clickReorderBtn();
				clickReorderSetToggle();
				
				let showing_page = Math.ceil(d.total / rows);
				mypagePaging(
					{
						total: d.total,
						el: use_form.find(".mypage__paging"),
						page: page,
						row: rows,
						show_paging: showing_page,
						use_form: use_form,
						list_type: list_type
					},
					getReorderList
				);
			} else {
				notiModal(d.msg);
			}
			
			changeLanguageR();
		}
	});
}

function clickReorderBtn() {
	let reorder_btn = document.querySelectorAll('.reorder_btn');
	reorder_btn.forEach(btn => {
		btn.addEventListener('click',function(e) {
			let el = e.currentTarget;
			let reorder_idx = el.dataset.reorder_idx;
			let action_type = el.dataset.action_type;
			
			if (reorder_idx != null && action_type != null) {
				$.ajax({
					type: "post",
					data: {
						'no': reorder_idx,
						'action_type': action_type
					},
					dataType: "json",
					url: api_location + "mypage/reorder/put",
					error: function (d) {
						makeMsgNoti(getLanguage(), 'MSG_F_ERR_0013', null);
						//notiModal('재입고 알림 취소/재신청 처리중 오류가 발생했습니다.');
					},
					success: function (d) {
						let seq = 0;
						
						switch (action_type) {
							case 'cancel':
								seq = 0;
								break;
							case 're_apply':
								seq = 2;
								break;
						}
						
						$('.reorder__wrap').find('.tab__btn__item').eq(seq).click();
					}
				});
			}
		});
	});
}

function clickReorderSetToggle() {
	let reorder_wrap = document.querySelector('.reorder__wrap');
	let set_toggle = reorder_wrap.querySelectorAll('.set_toggle');
	
	set_toggle.forEach(toggle => {
		toggle.addEventListener('click', function (e) {
			let toggle_btn = e.currentTarget;
			
			let reorder_idx = toggle_btn.dataset.reorder_idx;
			let action_type = toggle_btn.dataset.action_type;
			
			let set_product = reorder_wrap.querySelectorAll('.set_product');
			set_product.forEach(set => {
				console.log(set.dataset.parent_idx);
				if (set.dataset.parent_idx == reorder_idx) {
					set.classList.toggle('hidden');
				}
			});
			
			if (action_type == "show") {
				toggle_btn.dataset.action_type = "hide";
				toggle_btn.src = "/images/mypage/mypage_up_tab_btn.svg";
			} else if (action_type == "hide") {
				toggle_btn.dataset.action_type = "show";
				toggle_btn.src = "/images/mypage/mypage_down_tab_btn.svg";
			}
		});
	});
}