document.addEventListener('DOMContentLoaded', function () {
	getBluemarkInfoList();
	
	getAsCategory();
	
	$('.as__img__item').click(function() {
		$(this).parent().find('.as_img').click();
	});
	
	$('.as_img').on('change', function() {
		ext = $(this).val().split('.').pop().toLowerCase(); //확장자
		
		//배열에 추출한 확장자가 존재하는지 체크
		if ($.inArray(ext, ['gif', 'png', 'jpg', 'jpeg']) == -1) {
			makeMsgNoti(getLanguage(), 'MSG_F_WRN_0014', null);
			//notiModal('이미지 파일이 아닙니다. (gif, png, jpg, jpeg 만 업로드 가능)');
			return false;
		} else {
			file = $(this).prop("files")[0];

			let limitSize = 1024 * 1024 * 10;
			let fileSize = file.size;
			if(fileSize > limitSize) {
				makeMsgNoti(getLanguage(), 'MSG_F_WRN_0015', null);
				//notiModal("이미지 파일의 용량이 큽니다.", "(제한 10MB)");
				return false;
			}
			
			blobURL = window.URL.createObjectURL(file);
			
			let tmp_img = new Image();
			tmp_img.src = blobURL;
			
			let as_img_item = $(this).parent().find('.as__img__item');
			let as_preview = $(this).parent().find('.as_preview');
			
			tmp_img.onload = function() {
				let imgStr = `
					<img class="preview_img" src="${blobURL}" />
				`;
				as_preview.append(imgStr);
				as_img_item.hide();
				as_preview.show();
				initAsImgEvent(as_preview);
			}
			
		}
	});
	
	clickAsApplyTab();
	
	clickAddAsApply();
	clickCancelAsApply();
});
function initAsImgEvent(as_preview){
	as_preview.on('click', function(){
		as_preview.css('display','none');
		as_preview.html('');
		as_preview.prev().css('display','block');
		as_preview.next().val('');
	});
}
let as_category_tui = null;

function getAsCategory() {
	$.ajax({
		type: "post",
		url: api_location + "mypage/as/category/get",
		dataType: "json",
		error: function (d) {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0082', null);
			//notiModal('A/S 제품 카테고리 조회처리중 오류가 발생했습니다.');
		},
		success: function(d) {
			if (d.code == 200) {
				let data = d.data;

				if(data != null && data.length > 0) {
					let asCtgrPlaceholder = "";

					switch(getLanguage()) {
						case "KR":
							asCtgrPlaceholder = "제품 카테고리 선택";
							break;
						case "EN":
							asCtgrPlaceholder = "Select Product Category";
							break;
						case "CN":
							asCtgrPlaceholder = "选择产品类别";
							break;
					}

					let tui_category_data = [];
					
					data.forEach(function (row) {
						tui_category_data.push({
							'label': row.txt_category,
							'value': row.category_idx
						});
					});
					
					as_category_tui = new tui.SelectBox("#frm-as .as_category_select_box",
						{
							placeholder: asCtgrPlaceholder,
							data: tui_category_data,
							autofocus: false
						}
					);
				}
			} else {
				notiModal(d.msg);
			}
		}
	});
}

function openAsApplyTabBluemark(bluemark_idx) {
	$('.as_buying_wrap .as_bluemark_tab').hide();
	$('.as_apply_tab_bluemark').show();
	getBluemarkInfoItem(bluemark_idx);
}

function getBluemarkInfoList() {
	$.ajax({
		type: "post",
		url: api_location + "mypage/as/bluemark/list/get",
		data: {},
		dataType: "json",
		error: function (d) {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0032', null);
			//notiModal('블루마크 인증내역 조회처리중 오류가 발생했습니다.');
		},
		success: function(d) {
			let data = d.data;
			let bluemarkHistoryList = document.querySelector(".as_bluemark_history_list");
			bluemarkHistoryList.innerHTML = "";

			if(data != null && data.length > 0) {
				let div_as_product = writeAsProductListHtml(data, "bluemark");
				
				bluemarkHistoryList.innerHTML = `
						<table class="as_bluemark_list_table">
								<colsgroup>
										<col style="width: 15%;">
										<col style="width: 55%;">
										<col style="width: 30%;">
								</colsgroup>
								<tbody>
									${div_as_product}
								</tbody>
						</table>
				`;
			} else {
				let exception_msg = "";

				switch (getLanguage()) {
						case "KR" :
								exception_msg = "Bluemark 인증 내역이 없습니다.";
								break;
						
						case "EN" :
								exception_msg = "There is no history.";
								break;
						
						case "CN" :
								exception_msg = "没有查询到相关资料。​";
								break;

				}
				bluemarkHistoryList.innerHTML = `
					<div class="no_as_product_msg">${exception_msg}</div>
				`;
			}
			
			clickApplyBluemark();
		}
	});
}

function getBluemarkInfoItem(bluemark_idx) {
	$.ajax({
		type: "post",
		url: api_location + "mypage/as/bluemark/get",
		data: {
			"bluemark_idx": bluemark_idx
		},
		dataType: "json",
		error: function (d) {
		},
		success: function(d) {
			let data = d.data;
			
			if(data != null) {
				let asApplyItem = document.querySelector(".as_apply_tab_bluemark .as_apply_item");
				asApplyItem.innerHTML = "";
				
				let div_as_product = writeAsProductItemHtml(data, "bluemark");

				asApplyItem.innerHTML = `
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
				`;
			} else {
				makeMsgNoti(getLanguage(), 'MSG_F_WRN_0035', null);
				//notiModal("블루마크 인증품목이 없습니다.");
			}
		}
	});
}

function clickAddAsApply() {
	let add_as_apply = document.querySelectorAll('.add_as_apply');
	add_as_apply.forEach(apply => {
		apply.addEventListener('click',function(e) {
			let el = e.currentTarget;
			let apply_type = el.dataset.apply_type;
			
			if (apply_type != null) {
				addAsApply(apply_type);
			}
		});
	});
}

function clickCancelAsApply() {
	let cancel_as_apply = document.querySelectorAll('.cancel_as_apply');
	cancel_as_apply.forEach(cancel => {
		cancel.addEventListener('click',function(e) {
			let el = e.currentTarget;
			let cancel_type = el.dataset.cancel_type;
			
			if (cancel_type != null) {
				cancelAsApply(cancel_type);
			}
		});
	});
}

function addAsApply(status) {
	let applyBtn = null;
	if(status == "bluemark") {
		applyBtn = $(".as__black__btn.bluemark");
	} else {
		applyBtn = $(".as__black__btn.no_verify");
	}

	applyBtn.attr("disabled", true);

	let frm = null;
	let apply_tab = null;
	
	let serial_code = "";
	let as_category_idx = "";
	let as_contents = "";
	let barcode = "";

	let img_proof_cnt = 0;

	if(status == "bluemark") {
		frm = $('#frm-as-bluemark');
		
		apply_tab = document.querySelector(".as_apply_tab_bluemark");
		
		serial_code = apply_tab.querySelector(".bluemark_serial_code").value;
		as_category_idx = 0;
		as_contents = apply_tab.querySelector(".as__contents__text").value;
		barcode = apply_tab.querySelector(".bluemark_barcode").value;
	} else {
		frm = $('#frm-as');
		
		apply_tab = document.querySelector(".as_apply_tab_no_verify");
		
		serial_code = "";
		
		let selected = apply_tab.querySelector(".tui-select-box-selected");
		as_category_idx = selected ? selected.dataset.value : "";
		
		as_contents = apply_tab.querySelector(".as__contents__text").value;
		barcode = apply_tab.querySelector(".as_barcode_input").value;
	}
	
	frm.find('.serial_code').val(serial_code);
	frm.find('.as_category_idx').val(as_category_idx);
	frm.find('.as_contents').val(as_contents);
	frm.find('.barcode').val(barcode);
	
	let product_img = apply_tab.querySelectorAll('.product_img');

	let product_img_cnt = 0;
	product_img.forEach(img => {
		if (img.value) {
			product_img_cnt++;
		}
	});
	
	if (!product_img_cnt > 0) {
		makeMsgNoti(getLanguage(), 'MSG_F_WRN_0059', null);
		//notiModal("A/S 신청하려는 제품의 이미지를 첨부해주세요.");
		applyBtn.attr("disabled", false);
		return false;
	}
	
	if (status != "bluemark") {
		let receipt_img = apply_tab.querySelectorAll('.receipt_img');
		
		let receipt_img_cnt = 0;
		receipt_img.forEach(img => {
			if (img.value) {
				receipt_img_cnt++;
			}
		});
		
		if (!receipt_img_cnt > 0) {
			makeMsgNoti(getLanguage(), 'MSG_F_WRN_0060', null);
			//notiModal("A/S 신청하려는 제품의 구매 이력, 증빙 이미지를 첨부해주세요.");
			applyBtn.attr("disabled", false);
			return false;
		}
	}
	
	if(as_category_idx == null || as_category_idx.length == 0) {
		makeMsgNoti(getLanguage(), 'MSG_F_WRN_0057', null);
		//notiModal("A/S 신청하려는 제품의 카테고리를 선택해주세요.");
		applyBtn.attr("disabled", false);
		return false;
	}
	
	if(as_contents == null || as_contents.length == 0) {
		makeMsgNoti(getLanguage(), 'MSG_F_WRN_0056', null);
		//notiModal("A/S 요청사항을 입력해주세요.");
		applyBtn.attr("disabled", false);
		return false;
	}
	
	if(barcode == null || barcode.length == 0) {
		makeMsgNoti(getLanguage(), 'MSG_F_WRN_0058', null);
		//notiModal("A/S 신청하려는 제품의 제품코드를 입력해주세요.");
		applyBtn.attr("disabled", false);
		return false;
	}
	
	let form = frm[0];
	let formData = new FormData(form);
	
	$.ajax({
		type: "post",
		url: api_location + "mypage/as/add",
		data: formData,
		dataType: "json",
		cache: false,
		contentType: false,
		processData: false,
		error: function(d) {
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0085', null);
			//notiModal("A/S 신청처리중 오류가 발생했습니다.");
			applyBtn.attr("disabled", false);
		},
		success: function(d) {
			if (d.code == 200) {
				if(status == "bluemark") {
					$(".as_apply_tab_bluemark").hide();
					initAsApply("as-bluemark");
				} else {
					$(".as_buying_wrap.one_two").hide();
					initAsApply("as");
				}
			
				getBluemarkInfoList();
				getMemberAsInfoList();
				getAsCompleteInfoList();
			
				$(".one_one").show();
				$(".as_bluemark_tab").show();
				
				document.querySelector('.as_apply_tab_btn_bluemark').classList.add('selected');
				document.querySelector('.as_apply_tab_btn_no_verify').classList.remove('selected');
				
				makeMsgNoti(getLanguage(), 'MSG_F_INF_0017', null);
				//notiModal("A/S 신청이 완료되었습니다.<br>A/S 현황 및 내역 메뉴에서 자세한 확인이 가능합니다.");
				applyBtn.attr("disabled", true);
			} else {
				notiModal(d.msg);
				applyBtn.attr("disabled", false);
			}
		}
	});
}

function initAsApply(status) {
	let asForm = document.querySelector("#frm-" + status);
	let asContentsText = asForm.querySelector(".as__contents__text");
	let asImgInput = asForm.querySelectorAll(".as_img");
	let asImgPreview = asForm.querySelectorAll(".as_preview");
	let asImgItem = asForm.querySelectorAll(".as__img__item");
	let asApplyBtn = asForm.querySelector(".as__black__btn.add_as_apply");
	
	asApplyBtn.disabled = false;
	asContentsText.value = "";
	asImgInput.forEach(input => input.value = "");
	asImgPreview.forEach(preview => {
		preview.removeAttribute("style");
		preview.innerHTML = "";
	});
	asImgItem.forEach(item => item.removeAttribute("style"));

	if(status == "as") {
		let asBarcodeInput = asForm.querySelector(".as_barcode_input");
		let asSelectBoxItem = asForm.querySelectorAll(".tui-select-box-item");
		let asSelectBoxPlaceholder = asForm.querySelector(".tui-select-box-placeholder");

		asSelectBoxItem.forEach(item => {
			if(item.classList.contains("tui-select-box-selected")) {
				item.classList.remove("tui-select-box-selected");
			}
		});

		as_category_tui.deselect();
		// asSelectBoxPlaceholder.innerText = "제품 카테고리 선택";
		asBarcodeInput.value = "";
	} else {
		let asApplyItem = document.querySelector(".as_apply_tab_bluemark .as_apply_item");
		asApplyItem.innerHTML = "";
	}
}

function cancelAsApply(status) {
	$(".as_buying_wrap").hide();
	$(".as_apply_tab_bluemark").hide();
	$(".as_bluemark_tab").show();
	$(".as_buying_wrap.one_one").show();
	$(".as_apply_tab_btn_no_verify").removeClass("selected");
	$(".as_apply_tab_btn_bluemark").addClass("selected");

	initAsApply(status);
}