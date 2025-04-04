$(document).ready(function () {
	$('.tab.two').hide();
	$('.tab.three').hide();
	$('.tab.four').hide();

	$('.as_buying_wrap').hide();
	$('.as_buying_wrap_apply').hide();
	$('.as_apply_tab_bluemark').hide();
	$('.as_buying_wrap.one_one').show();

	clickAsTab();
});

function clickAsTab() {
	let as_tab = document.querySelectorAll('.as_tab');
	as_tab.forEach(tab => {
		tab.addEventListener('click', function (e) {
			let el = e.currentTarget;
			let tab_num = el.dataset.tab_num;

			$('.tab').hide();
			$('.as_current_list').show();
			$(`.tab.${tab_num}`).show();

			$('.as_tab_btn li').removeClass('on');
			el.classList.add('on');
		});
	});
}

function clickAsApplyTab() {
	let as_apply_tab = document.querySelectorAll('.as_apply_tab');

	as_apply_tab.forEach(tab => {
		tab.addEventListener('click', function (e) {
			let el = e.currentTarget;
			let tab_num = el.dataset.apply_tab_num;

			$('.as_apply_tab_bluemark').hide();
			$('.as_buying_wrap').hide();

			$('.as_bluemark_tab').show();
			$(`.as_buying_wrap.${tab_num}`).show();

			$('.as__service__btn li').removeClass('selected');
			el.classList.add('selected');

			$('.add_as_apply').attr('disabled', false);
		});
	});
}

function shippingForm() {
	let asShippingForm = document.querySelector(".as_shipping_form");
	asShippingForm.classList.remove("hidden");
}

function closeShipping() {
	let asShippingForm = document.querySelector(".as_shipping_form");
	asShippingForm.classList.add("hidden");
}

// 상품 리스트 함수
function writeAsProductListHtml(data, status) {
	let div_as_product = "";

	let as_apply_complete_txt = "";
	let as_apply_txt = "";
	let as_purchase_mall_txt = "";
	let as_bluemark_serial_txt = "";
	let as_bluemark_verify_date_txt = "";
	let as_number_txt = "";
	let as_apply_date_txt = "";
	let as_view_detail_txt = "";
	let as_complete_txt = "";
	let as_product_category_txt = "";
	let as_product_code_txt = "";
	let as_bluemark_verify_unable_txt = "";

	switch (getLanguage()) {
		case "KR":
			as_apply_complete_txt = "A/S 신청 완료";
			as_apply_txt = "A/S 신청";
			as_purchase_mall_txt = "구매처";
			as_bluemark_serial_txt = "Bluemark 시리얼코드";
			as_bluemark_verify_date_txt = "Bluemark 인증 날짜";
			as_number_txt = "A/S 번호";
			as_apply_date_txt = "신청 날짜";
			as_view_detail_txt = "자세히 보기";
			as_complete_txt = "완료";
			as_product_category_txt = "제품 카테고리";
			as_product_code_txt = "제품 코드";
			as_bluemark_verify_unable_txt = "Bluemark 인증 불가";
			break;

		case "EN":
			as_apply_complete_txt = "A/S application completed";
			as_apply_txt = "A/S application";
			as_purchase_mall_txt = "Purchase mall";
			as_bluemark_serial_txt = "Bluemark serial code";
			as_bluemark_verify_date_txt = "Bluemark certification date";
			as_number_txt = "A/S number";
			as_apply_date_txt = "Application date";
			as_view_detail_txt = "View details";
			as_complete_txt = "Completion";
			as_product_category_txt = "Product category";
			as_product_code_txt = "Product code";
			as_bluemark_verify_unable_txt = "Bluemark unable to authenticate";
			break;

		case "CN":
			as_apply_complete_txt = "A/S申请完成";
			as_apply_txt = "A/S申请";
			as_purchase_mall_txt = "购买处";
			as_bluemark_serial_txt = "Bluemark串行码";
			as_bluemark_verify_date_txt = "Bluemark认证日期";
			as_number_txt = "A/S 号码";
			as_apply_date_txt = "申请日期";
			as_view_detail_txt = "查看详情";
			as_complete_txt = "完成";
			as_product_category_txt = "产品类别";
			as_product_code_txt = "产品代码";
			as_bluemark_verify_unable_txt = "无法验证Bluemark";
			break;
	}

	data.forEach(row => {
		let color_html = asProductColorHtml(row.color, row.color_rgb);
		let header_html = "";
		let body_html = "";
		let verify_html = "";
		let reg_date_html = "";
		let create_date_html = "";

		if (row.reg_date == "0000.00.00") {
			reg_date_html = "-";
		} else {
			reg_date_html = row.reg_date;
			create_date_html = row.create_date;
		}

		if (status == "bluemark") {
			let button_html = "";
			if (row.as_flg == true) {
				button_html = `
					<div class="as_order_status_box complete">
						${as_apply_complete_txt}
					</div>
				`;
			} else {
				button_html = `
					<div class="as_order_status_box apply_bluemark" data-bluemark_idx="${row.bluemark_idx}">
						${as_apply_txt}
					</div>
				`;
			}

			body_html = `
				<td>
					<a href="/product/detail?product_idx=${row.product_idx}">
						<img class="as_img_fixed" src="${cdn_img}${row.img_location}">
					</a>
				</td>
				<td class="as_table_colspan_td">
					<span>${row.product_name}</span>
					<p>${row.sales_price}</p>
					<div>${color_html}</div>
					<p>${row.option_name}</p>
					
					<input class="bluemark_barcode" type="hidden" value="${row.barcode}">
					<input class="bluemark_serial_code" type="hidden" value="${row.serial_code}">
				</td>
				<td>
					${button_html}
				</td>
			`;

			verify_html = `
				<div>
					<span>${as_purchase_mall_txt} </span>
					<span class="verify_info_text">${row.purchase_mall}</span>
				</div>
				<div>
					<span>${as_bluemark_serial_txt} </span>
					<span class="verify_info_text">${row.serial_code}</span>
				</div>
				<div>
					<span>${as_bluemark_verify_date_txt} </span>
					<span class="verify_info_text">${reg_date_html}</span>
				</div>
			`;
		} else {
			if (status == "current") {
				header_html = `
					<tr class="as_list_header_tr">
						<td colspan="3">
							<div class="as_list_header_wrap">
								<div class="as_list_header_info">
									<span>${as_number_txt}&nbsp&nbsp${row.as_code}</span>
									<span>${as_apply_date_txt}&nbsp&nbsp${create_date_html}</span>
								</div>
								<div class="as_order_status_box detail_view as_status" data-as_type="current" data-as_idx="${row.as_idx}">
									${as_view_detail_txt}
								</div>
							</div>
						</td>
					</tr>
				`;

				button_html = `
					<div class="as_status_text">${row.as_status}</div>
				`
			} else {
				header_html = `
					<tr class="as_list_header_tr">
						<td colspan="3">
							<div class="as_list_header_wrap">
								<div class="as_list_header_info">
									<span>${as_number_txt}&nbsp&nbsp${row.as_code}</span>
									<span>${as_apply_date_txt}&nbsp&nbsp${create_date_html}</span>
								</div>
								<div class="as_order_status_box detail_view as_status" data-as_type="complete" data-as_idx="${row.as_idx}">
									${as_view_detail_txt}
								</div>
							</div>
						</td>
					</tr>
				`;

				button_html = `
					<div class="as_status_text">${as_complete_txt}</div>
				`
			}

			if (row.bluemark_flg == true) {
				body_html = `
					<td>
						<a href="/product/detail?product_idx=${row.product_idx}">
							<img class="as_img_fixed" src="${cdn_img}${row.img_location}">
						</a>
					</td>
					<td class="as_table_colspan_td">
						<span>${row.product_name}</span>
						<p>${row.sales_price}</p>
						<div>${color_html}</div>
						<p>${row.option_name}</p>
					</td>
					<td>
						${button_html}
					</td>
				`;

				verify_html = `
					<div>
						<span>${as_purchase_mall_txt} </span>
						<span class="verify_info_text">
							${row.purchase_mall}
						</span>
					</div>
					<div>
						<span>${as_bluemark_serial_txt} </span>
						<span class="verify_info_text">
							${row.serial_code}
						</span>
					</div>
					<div>
						<span>${as_bluemark_verify_date_txt} </span>
						<span class="verify_info_text">
							${reg_date_html}
						</span>
					</div>
				`
			} else {
				body_html = `
					<td colspan="3">
						<div class="as_list_header_wrap">
							<div class="as_list_header_info">
								<span>${as_product_category_txt}&nbsp&nbsp${row.txt_category}</span>
								<span>${as_product_code_txt}&nbsp&nbsp${row.barcode}</span>
							</div>
							<div>${row.as_status}</div>
						</div>
					</td>
				`;

				verify_html = `
					<span class="verify_info_text">${as_bluemark_verify_unable_txt}</span>
				`;
			}
		}

		div_as_product += `
			${header_html}
			<tr>
				${body_html}
			</tr>
			<tr class="bluemark_verify_info_tr">
				<td colspan="3">
					<div class="bluemark_verify_info_wrap">
						${verify_html}
					</div>
				</td>
			</tr>
		`;
	});

	return div_as_product;
}

function clickApplyBluemark() {
	let apply_bluemark = document.querySelectorAll('.apply_bluemark');
	apply_bluemark.forEach(apply => {
		apply.addEventListener('click', function (e) {
			let el = e.currentTarget;
			let bluemark_idx = el.dataset.bluemark_idx;
			let asApplyBtn = document.querySelector(".add_as_apply.bluemark");

			if (bluemark_idx > 0) {
				openAsApplyTabBluemark(bluemark_idx);

				$(".add_as_apply.bluemark").attr("disabled", false);
			}
		});
	});

}

function clickAsStatusCurrentBtnEvent() {
	let currentDetailBtn = $('.as_status[data-as_type=current]');
	currentDetailBtn.unbind('click');
	currentDetailBtn.on('click', function () {
		let as_type = $(this).attr('data-as_type');
		let as_idx = $(this).attr('data-as_idx');
		if (as_type != null && as_idx != null) {
			openAsStatusTab(as_idx, 'current');
		}
	});
}
function clickAsStatusCompleteBtnEvent() {
	let complateDetailBtn = $('.as_status[data-as_type=complete]')
	complateDetailBtn.unbind('click');
	complateDetailBtn.on('click', function () {
		let as_type = $(this).attr('data-as_type');
		let as_idx = $(this).attr('data-as_idx');
		if (as_type != null && as_idx != null) {
			openAsCompleteDetail(as_idx);
		}
	})
}
// 상품 단품 함수
function writeAsProductItemHtml(data, status) {
	let div_as_product = "";

	let colorHtml = asProductColorHtml(data.color, data.color_rgb);
	let headerHtml = "";
	let bodyHtml = "";
	let msgHtml = "";
	let hiddenHtml = "";
	let verifyHtml = "";

	let as_purchase_mall_txt = "";
	let as_bluemark_serial_txt = "";
	let as_bluemark_verify_date_txt = "";
	let as_number_txt = "";
	let as_apply_date_txt = "";
	let as_complete_txt = "";
	let as_product_category_txt = "";
	let as_product_code_txt = "";
	let as_bluemark_verify_unable_txt = "";
	let reg_date_html = "";
	let create_date_html = "";

	if (data.reg_date == "0000.00.00") {
		reg_date_html = "-";
	} else {
		reg_date_html = data.reg_date;
		create_date_html = data.create_date;
	}

	switch (getLanguage()) {
		case "KR":
			as_purchase_mall_txt = "구매처";
			as_bluemark_serial_txt = "Bluemark 시리얼코드";
			as_bluemark_verify_date_txt = "Bluemark 인증 날짜";
			as_number_txt = "A/S 번호";
			as_apply_date_txt = "신청 날짜";
			as_complete_txt = "완료";
			as_product_category_txt = "제품 카테고리";
			as_product_code_txt = "제품 코드";
			as_bluemark_verify_unable_txt = "Bluemark 인증 불가";
			break;

		case "EN":
			as_purchase_mall_txt = "Purchase mall";
			as_bluemark_serial_txt = "Bluemark serial code";
			as_bluemark_verify_date_txt = "Bluemark certification date";
			as_number_txt = "A/S number";
			as_apply_date_txt = "Application date";
			as_complete_txt = "Completion";
			as_product_category_txt = "Product category";
			as_product_code_txt = "Product code";
			as_bluemark_verify_unable_txt = "Bluemark unable to authenticate";
			break;

		case "CN":
			as_purchase_mall_txt = "购买处";
			as_bluemark_serial_txt = "Bluemark串行码";
			as_bluemark_verify_date_txt = "Bluemark认证日期";
			as_number_txt = "A/S 号码";
			as_apply_date_txt = "申请日期";
			as_complete_txt = "完成";
			as_product_category_txt = "产品类别";
			as_product_code_txt = "产品代码";
			as_bluemark_verify_unable_txt = "无法验证Bluemark";
			break;
	}

	if (status == "bluemark") {
		hiddenHtml = `
			<input class="bluemark_barcode" type="hidden" value="${data.barcode}">
			<input class="bluemark_serial_code" type="hidden" value="${data.serial_code}">
		`;
		bodyHtml = `
			<td>
				<img class="as_img_fixed" src="${cdn_img}${data.img_location}">
			</td>
			<td class="as_table_colspan_td">
				<p>${data.product_name}</p>
				<p>${data.sales_price}</p>
				<div>${colorHtml}</div>
				<p>${data.option_name}</p>
				${hiddenHtml}
			</td>
			<td>
				${msgHtml}
			</td>
		`;
		verifyHtml = `
				<div>
					<span>${as_purchase_mall_txt} </span>
					<span class="verify_info_text">${data.purchase_mall}</span>
				</div>
				<div>
					<span>${as_bluemark_serial_txt} </span>
					<span class="verify_info_text">${data.serial_code}</span>
				</div>
				<div>
					<span>${as_bluemark_verify_date_txt} </span>
					<span class="verify_info_text">${reg_date_html}</span>
				</div>
			`;
	} else {
		headerHtml = `
				<tr class="as_list_header_tr">
					<td colspan="3">
						<div class="as_list_header_wrap">
							<div class="as_list_header_info">
								<span>${as_number_txt}&nbsp&nbsp${data.as_code}</span>
								<span>${as_apply_date_txt}&nbsp&nbsp${create_date_html}</span>
							</div>
						</div>
					</td>
				</tr>
		`;

		msgHtml = status == "current" ?
			`
			<div class="as_order_status_msg as_status_text">
				${data.txt_as_status}
			</div>
		` :
			`
			<div class="as_status_text">${as_complete_txt}</div>
		`;

		data.bluemark_flg == true ?
			(bodyHtml = `
			<td>
				<img class="as_img_fixed" src="${cdn_img}${data.img_location}">
			</td>
			<td class="as_table_colspan_td">
				<p>${data.product_name}</p>
				<p>${data.sales_price}</p>
				<div>${colorHtml}</div>
				<p>${data.option_name}</p>
				${hiddenHtml}
			</td>
			<td>
				${msgHtml}
			</td>
		`,
				verifyHtml = `
			<div>
				<span>${as_purchase_mall_txt} </span>
				<span class="verify_info_text">${data.purchase_mall}</span>
			</div>
			<div>
				<span>${as_bluemark_serial_txt} </span>
				<span class="verify_info_text">${data.serial_code}</span>
			</div>
			<div>
				<span>${as_bluemark_verify_date_txt} </span>
				<span class="verify_info_text">${reg_date_html}</span>
			</div>
		`) :
			(bodyHtml = `
			<td colspan="3">
				<div class="as_list_header_wrap">
					<div class="as_list_header_info">
						<span>${as_product_category_txt}&nbsp&nbsp${data.txt_category}</span>
						<span>${as_product_code_txt}&nbsp&nbsp${data.barcode}</span>
					</div>
					<div>${msgHtml}</div>
				</div>
			</td>
		`,
				verifyHtml = `
			<span class="verify_info_text">${as_bluemark_verify_unable_txt}</span>
		`)
	}

	div_as_product += `
		${headerHtml}
		<tr>
			${bodyHtml}
		</tr>
		<tr class="bluemark_verify_info_tr">
			<td colspan="3">
				<div class="bluemark_verify_info_wrap">
					${verifyHtml}
				</div>
			</td>
		</tr>
	`;

	return div_as_product;
}

//상품 컬러칩 생성
const asProductColorHtml = (color, color_rgb) => {
	let productColorHtml = "";
	if (!color_rgb) {
		return null;
	} else {
		let multi = color_rgb.split(";");
		if (multi.length === 2) {
			productColorHtml += `
				<div class="color-line"	style="--background:linear-gradient(90deg, ${multi[0]} 50%, ${multi[1]} 50%);">
					<p class="color-name">${color}</p>
					<div class="color multi" data-title="${color}"></div>
				</div>
			`;
		} else {
			productColorHtml += `
				<div class="color-line"	data-title="${color}" style="--background:${multi[0]}">
					<p class="color-name">${color}</p>
					<div class="color" data-title="${color}"></div>
				</div>
			`;
		}
	}

	return productColorHtml;
};