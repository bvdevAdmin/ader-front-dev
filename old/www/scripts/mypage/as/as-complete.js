document.addEventListener('DOMContentLoaded', function () {
	getAsCompleteInfoList();
});

function getAsCompleteInfoList() {
	$.ajax({
		type: "post",
		url: api_location + "mypage/as/list/get",
		data: {
			"as_status": "ACP"
		},
		dataType: "json",
		error: function (d) {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0079', null);
			//notiModal("AS 내역 불러오기 실패");
		},
		success: function (d) {
			let data = d.data;

			let asWrapContent = document.querySelector(".tab.four .as__complete__container.list_view");
			asWrapContent.innerHTML = "";

			if (data != null && data.length > 0) {
				let div_as_product = writeAsProductListHtml(data, "complete");

				asWrapContent.innerHTML = `
					<table class="as__contents__table">
							<colsgroup>
									<col style="width:15%;">
									<col style="width:55%;">
									<col style="width:30%;">
							</colsgroup>
							<tbody>
									${div_as_product}
							</tbody>
					</table>
				`;
			} else {
				let exception_msg = "";

				switch (getLanguage()) {
					case "KR":
						exception_msg = "A/S 완료 내역이 없습니다.";
						break;

					case "EN":
						exception_msg = "There are no A/S completion records.";
						break;

					case "CN":
						exception_msg = "没有 A/S 完成记录。​";
						break;

				}
				asWrapContent.innerHTML = `
					<div class="no_as_product_msg">${exception_msg}</div>
				`;
			}
			clickAsStatusCompleteBtnEvent();
		}
	});
};

function openAsCompleteDetail(as_idx) {
	let asWrapContent = document.querySelector(".tab.four .as__complete__container.list_view");
	let asDetailContent = document.querySelector(".tab.four .as__complete__container.detail_view");

	asDetailContent.classList.remove("hidden");
	asWrapContent.classList.add("hidden");

	getAsCompleteInfo(as_idx);
}

function getAsCompleteInfo(as_idx) {
	$.ajax({
		type: "post",
		url: api_location + "mypage/as/get",
		data: {
			"as_idx": as_idx
		},
		dataType: "json",
		error: function (d) {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0079', null);
			//notiModal("AS 내역 불러오기에 실패했습니다.");
		},
		success: function (d) {
			let data = d.data;

			let detail_content = document.querySelector(".tab.four .as_complete_product_detail");
			detail_content.innerHTML = "";

			let detail_content_html = "";
			if (data != null) {
				let div_as_product = writeAsProductItemHtml(data, "complete");

				let img_info_product_html = "";
				let img_info_receipt_html = "";

				let img_info = data.img_info;
				if (img_info != null && img_info.length > 0) {
					img_info.forEach(info => {
						if (info.img_type == "P") {
							img_info_product_html += `
								<div class="img_info_content">
									<img src="${cdn_img}${info.img_location}">
								</div>
							`;
						} else if (info.img_type == "R") {
							img_info_receipt_html += `
								<div class="img_info_content">
									<img src="${cdn_img}${info.img_location}">
								</div>
							`;
						}
					});
				}

				detail_content_html += `
						<table class="as__contents__table">
							<colsgroup>
								<col style="width: 15%;">
								<col style="width: 55%;">
								<col style="width: 30%;">
							</colsgroup>
							<tbody>
								${div_as_product}
							</tbody>
						</table>
						
						<div class="as_complete_data_wrap">
							<div class="as_complete_data">
								<div class="as_complete_data_title" data-i18n="as_inquiry_details">문의내용</div>
								<div class="as_complete_data_contents text_area">
									${data.as_contents}
								</div>							
							</div>
							<div class="as_complete_data">
								<div class="as_complete_data_title" data-i18n="as_attached_file">첨부파일</div>
								<div class="img_info_area_wrap">
									<div class="as_complete_data_contents img_info_area img_product">
										${img_info_product_html}
									</div>
				`;

				if (img_info_receipt_html.length > 0) {
					img_info_receipt_html = `
									<div class="as_complete_data_contents img_info_area img_receipt">
										${img_info_receipt_html}
									</div>
					`;
				}
				let company_name_html = "";

				if (data.company_name != null) {
					company_name_html = `
							<div class="as_complete_data">
								<div class="as_complete_data_title" data-i18n="as_delivery_company">배송 업체</div>
								<div class="as_complete_data_contents">
									${data.company_name}
								</div>
							</div>
					`;
				}

				let delivery_num_html = "";

				if (data.delivery_status == null && data.delivery_idx < 1) {
					delivery_num_html = "";
				} else if (data.delivery_idx == 0 || data.delivery_idx == 1) {
					delivery_num_html = `
						<div class="as_complete_data">
								<div class="as_complete_data_title" data-i18n="as_tracking_number">운송장 번호</div>
								<div class="as_complete_data_contents delivery_number">
									<a href="https://trace.cjlogistics.com/web/detail.jsp?slipno=${data.delivery_num}" target='_blank'>${data.delivery_num}</a>
								</div>
						</div>
					`;
				} else {
					delivery_num_html = `
							<div class="as_complete_data">
								<div class="as_complete_data_title" data-i18n="as_tracking_number">운송장 번호</div>
								<div class="as_complete_data_contents delivery_number">
									${data.delivery_num}
								</div>
							</div>
					`;
				}

				let delivery_start_date_html = "";

				if (data.delivery_start_date != null) {
					delivery_start_date_html = `
						<div class="as_complete_data">
							<div class="as_complete_data_title" data-i18n="as_shipping_start_date">배송 시작일</div>
							<div class="as_complete_data_contents">
								${data.delivery_start_date}
							</div>
						</div>`
				}

				detail_content_html += `
									${img_info_receipt_html}
								</div>
							</div>
							<div class="as_complete_data completion_date">
								<div class="as_complete_data_title" data-i18n="as_completion_date">완료일</div>
								<div class="as_complete_data_contents">
									${data.as_complete_date}
								</div>
							</div>
							<div class="as_complete_data">
								<div class="as_complete_data_title" data-i18n="as_repair_details">수선내용</div>
								<div class="as_complete_data_contents text_area">
									${data.repair_desc}
								</div>
							</div>
							${company_name_html}
							${delivery_num_html}
							${delivery_start_date_html}
				`;

				let payment_info = data.payment_info;
				if (payment_info.pg_price != null) {
					detail_content_html += `
							<div class="as_complete_data price_info">
								<div class="as_complete_data_title" data-i18n="as_as_cost">AS 비용</div>
								<div class="as_complete_data_contents">${data.payment_info.pg_price}원</div>							
							</div>
							<div class="as_complete_data price_info">
								<div class="as_complete_data_title" data-i18n="p_payment_method">결제수단</div>
								<div class="as_complete_data_contents">${data.payment_info.pg_date} ${data.payment_info.pg_payment}</div>							
							</div>
							<div class="as_complete_data price_info">
								<div class="as_complete_data_title"></div>
								<div class="as_complete_data_contents">
									<a href="${payment_info.pg_receipt_url}" target="_blank" rel="noopener noreferrer"><div class="show_as_receipt_btn" data-i18n="oc_view_receipts">영수증 보기</div></a>
								</div>
							</div>
					`;
				} else {
					detail_content_html += `
							<div class="as_complete_data price_info">
								<div class="as_complete_data_title" data-i18n="as_as_cost">AS 비용</div>
								<div class="as_complete_data_contents">0원</div>							
							</div>
					`;
				}

				detail_content_html += `
						</div>
					<div class="show_as_complete_list_btn" data-i18n="as_view_list">목록보기</div>
				`;
			} else {
				detail_content_html += `
					<div class="no_as_product_msg" data-i18n="as_no_records">A/S 완료 내역이 없습니다.</div>
				`;
			}

			detail_content.innerHTML = detail_content_html;

			showImgInfoDetail();
			showAsCompleteList();
			changeLanguageR();
		}
	});
}


function showImgInfoDetail() {
	let imgInfo = document.querySelectorAll(".as_complete_data_wrap .img_info_content");

	imgInfo.forEach(img => {
		let imgSrc = img.querySelector("img").getAttribute("src");

		img.addEventListener("click", function () {
			let popupWindow = window.open("", "_blank", "scrollbars=yes");
			popupWindow.document.write('<html><head><title>Image Preview</title></head><body></body></html>');
			popupWindow.document.body.style.background = '#fff';
			popupWindow.document.body.style.display = 'flex';
			popupWindow.document.body.style.justifyContent = 'center';
			popupWindow.document.body.style.alignItems = 'center';
			popupWindow.document.body.style.margin = '0';
			popupWindow.document.body.style.padding = '0';
			popupWindow.document.body.style.height = '100vh';
			popupWindow.document.body.style.cursor = 'pointer';
			popupWindow.document.body.innerHTML = `<img src="${imgSrc}" style="max-width: 100%; max-height: 100%; margin: auto;">`;
			popupWindow.document.body.addEventListener('click', function () {
				popupWindow.close();
			});
		});
	});
}






function showAsCompleteList() {
	let showListBtn = document.querySelector(".detail_view .show_as_complete_list_btn");
	let completeDetailWrap = document.querySelector(".as__complete__container.detail_view");
	let completeDetail = completeDetailWrap.querySelector(".as_complete_product_detail");
	let completeList = document.querySelector(".as__complete__container.list_view");

	showListBtn.addEventListener("click", function () {
		completeDetail.innerHTML = "";
		completeList.classList.remove("hidden");
		completeDetailWrap.classList.add("hidden");
	});
}