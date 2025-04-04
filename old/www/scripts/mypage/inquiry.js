document.addEventListener('DOMContentLoaded', function () {
	makeSelect('inquiry__type');
	makeSelect('edit__inquiry__type');
	getInqFaqCategoryList();

	clickInqImg();

	$('.board__image').on('change', function (e) {
		let frm_obj = null;
		
		let inquiry_edit_wrap = document.querySelector('.inquiry_edit_wrap');
		if (inquiry_edit_wrap.style.display == "block") {
			frm_obj = $('#frm-edit-inquiry');
		} else {
			frm_obj = $('#frm-inquiry');
		}
		
		var files = $(this).val();
		var file = e.target.files;
		var max_size = 10 * 1024 * 1024;

		var file_name_arr = [];
		if (files != null) {
			file_name_arr = files.split('.');
			if (file_name_arr.length > 1) {
				let fileExt = file_name_arr[file_name_arr.length - 1];
				if (fileExt == 'jpg' || fileExt == 'png' || fileExt == 'gif' || fileExt == 'jpeg' || fileExt == 'jpe') {
					var file_size = $(this)[0].files[0].size;
				} else {
					makeMsgNoti(getLanguage(), 'MSG_F_ERR_0017', null);
					// notiModal('업로드 할 수 없는 유형의 사진이 선택되었습니다.<br/>첨부하려는 사진을 다시 선택해주세요.');
				}

				if (file_size > max_size) {
					// notiModal('첨부파일은 10MB 이내로 선택해주세요');
					makeMsgNoti(getLanguage(), 'MSG_F_WRN_0007', null);
					$(this).val('');
					$(this).find('img').eq(0).attr('src', "/images/mypage/mypage_photo_btn.svg");

					return false;
				} else {
					let img_idx = frm_obj.find('.img_index').val();
					
					let reader = new FileReader();
					reader.onload = function (e) {
						frm_obj.find('.inquiry__photo__item').eq(img_idx).find('img').attr('src', e.target.result);
						frm_obj.find('.inquiry__photo__item').eq(img_idx).find('.priview_location').val(``);
					};

					reader.readAsDataURL($(this)[0].files[0]);
				}
			}
		} else {
			// notiModal('첨부하려는 사진을 다시 선택해주세요.');
			makeMsgNoti(getLanguage(), 'MSG_F_WRN_0006', null);
			return false;
		}
	});
	changeSelectLang();
	faqKeywordHandler();
	getInqInfoList();
	inquiryBtnClickEventHandler();

});
function changeSelectLang() {
	let inquiryTitle = document.querySelectorAll('.inquiry__info.inquiry__title');
	inquiryTitle.forEach(elem => {
		let inquiryTypeSelect = document.querySelector('.inquiry_type');
		let selectItem = elem.querySelector('.select-items');
		let optionDivList = selectItem.querySelectorAll('div');
		let firsti18nClass = '';

		optionDivList.forEach(function (el, idx) {
			let typeValue = inquiryTypeSelect.options[idx].value;
			let i18nClass = getI18nClass(typeValue);

			if (idx == 0) {
				firsti18nClass = i18nClass;
			}
			if (typeValue != 'AFS') {
				el.dataset.i18n = i18nClass;
			}

			el.addEventListener("click", function () {
				inquiryTypeSelect.value = typeValue;
			});
		});
		elem.querySelector('.select-selected').dataset.i18n = firsti18nClass;

		changeLanguageR();
	});
}

function getI18nClass(typeValue) {
	switch (typeValue) {
		case 'DAE':
			return 'q_inquiry_subcategory_01';
		case 'CAR':
			return 'q_inquiry_subcategory_02';
		case 'OAP':
			return 'q_inquiry_subcategory_03';
		case 'FAD':
			return 'q_inquiry_subcategory_04';
		case 'RAE':
			return 'q_inquiry_subcategory_05';
		case 'RST':
			return 'q_inquiry_subcategory_06';
		case 'PIQ':
			return 'q_inquiry_subcategory_07';
		case 'BGP':
			return 'q_inquiry_subcategory_08';
		case 'VUC':
			return 'm_voucher';
		case 'OSV':
			return 'q_inquiry_subcategory_09';
		default:
			return '';
	}
}

function getInqFaqCategoryList() {
	$.ajax({
		type: "post",
		url: api_location + "mypage/faq/category/get",
		dataType: "json",
		error: function (d) {
			// notiModal('QnA 카테고리 조회처리중 오류가 발생했습니다.');
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0076', null);
		},
		success: function (d) {
			if (d.code == 200) {
				let data = d.data;

				let div_category = $('.category');
				div_category.html('');

				let div_small_category = $('.category__small');
				div_small_category.html('');

				if (data != null && data.length > 0) {
					let inq_qna_category_html = "";
					let inq_qna_small_category_html = "";

					data.forEach(function (row, index) {
						$('#inq_cate').append(`<option value="${row.no}">${row.title}</option>`);

						inq_qna_category_html += `
							<div class="btn__row">
								<div class="faq__category__btn faq_category" data-category_idx="${row.no}">
									${row.title}
								</div>
							</div>
						`;

						inq_qna_small_category_html += `
							<div class="btn__row">
								<div class="parents__category">
									<div class="faq__category__btn faq_small_category small_category_${row.no}" data-category_idx="${row.no}">
										${row.title}
									</div>
								</div>
							</div>
						`;
					});

					div_category.append(inq_qna_category_html);
					div_small_category.append(inq_qna_small_category_html);
				}

				makeSelect('inquiry__category');
				makeSelect('inquiry__subcategory');

				clickFaqCategory();
				clickFaqSmallCategory();
			}
		}
	});
}

function clickFaqCategory() {
	let faq_category = document.querySelectorAll('.faq_category');
	faq_category.forEach(category => {
		category.addEventListener('click', function (e) {
			$('.inquiry__tab').hide();
			$('.inquiry__faq__detail__wrap').show();

			let el = e.currentTarget;
			let category_idx = el.dataset.category_idx;

			$('.small_category_' + category_idx).click();
		});
	});
}

function clickFaqSmallCategory() {
	let faq_small_category = document.querySelectorAll('.faq_small_category');
	faq_small_category.forEach(category => {
		category.addEventListener('click', function (e) {
			let el = e.currentTarget;
			let category_idx = el.dataset.category_idx;

			if (!el.classList.contains('click__btn')) {
				$('.faq_small_category').removeClass('click__btn');
				el.classList.add('click__btn');

				let category_name = $('#inq_cate option[value=' + category_idx + ']').text();
				$('.inquiry__category .select-items div:contains("' + category_name + '")').click();
				$('.inquiry__category img').attr('src', '/images/mypage/mypage_down_tab_btn.svg');

				getFaqList('click', category_idx);
			} else {
				el.classList.remove('click__btn');

				$('.inquiry__tab').hide();
				$('.inquiry__faq__wrap').show();
			}
		});
	});

	$('.search__keyword').val('');
}

function getFaqList(type, param) {
	var param_json = {};

	if (type == 'click') {
		param_json = {
			'category_no': param
		};
	} else if (type == 'search') {
		param_json = {
			'keyword': param
		};
	}

	$.ajax({
		type: "post",
		data: param_json,
		dataType: "json",
		url: api_location + "mypage/faq/get",
		error: function (d) {
			// notiModal('자주 묻는 질문 조회처리중 오류가 발생했습니다.');
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0015', null);
		},
		success: function (d) {
			if (d.code == 200) {
				let toggle_list_tab = $('.toggle__list__tab.02')
				toggle_list_tab.html('');

				let inq_faq_list_html = "";

				let data = d.data;
				if (data != null && data.length > 0) {
					data.forEach(function (row) {
						inq_faq_list_html +=
							`<div class="toggle__list__tab category">
								<div class="category_title__wrap">
									<p class="title">${row.category_title}</p>
									<img class="faq_category_toggle" data-toggle_type="down" src="/images/mypage/mypage_down_tab_btn.svg">
								</div>
							`;
						if (row.faq_info.length > 0) {
							row.faq_info.forEach(function (faq_row) {
								inq_faq_list_html += `
									<div class="toggle__item">
										<div class="question">
											<span>${faq_row.question}</span>
											<img class="down__up__icon faq_toggle" data-toggle_type="down" src="/images/mypage/mypage_down_tab_btn.svg">
										</div>
										
										<div class="request hidden">
											${xssDecode(faq_row.answer)}
										</div>
									</div>
								`;
							});
						}
						inq_faq_list_html +=
							`</div>`;
					});
				} else {
					switch (getLanguage()) {
						case "KR":
							inq_faq_list_html = '자주 묻는 질문 검색 결과가 존재하지 않습니다.';
							break;
						case "EN":
							inq_faq_list_html = 'There are no search results for Frequently Asked Questions.';
							break;
						case "CN":
							inq_faq_list_html = '常见问题搜索结果不存在。';
							break;
					}
				}

				toggle_list_tab.append(inq_faq_list_html);
				toggleFaqCategoryEvent();
				clickFaqToggle();
			}
		}
	});
}
function toggleFaqCategoryEvent() {
	$('.faq_category_toggle').unbind('click', toggleFaqCategoryEventHandler);
	$('.faq_category_toggle').on('click', toggleFaqCategoryEventHandler);
}
function toggleFaqCategoryEventHandler(e) {
	let selectCategoryTeb = $(e.target).parents('.toggle__list__tab.category');
	let openFlg = selectCategoryTeb.hasClass('active');

	$(e.target).attr('src', '/images/mypage/mypage_down_tab_btn.svg');
	$('.toggle__list__tab.category').removeClass('active');
	$('.down__up__icon.faq_toggle').attr('src', '/images/mypage/mypage_down_tab_btn.svg');
	selectCategoryTeb.find('.request').addClass('hidden');
	if (openFlg == false) {
		$(e.target).attr('src', '/images/mypage/mypage_up_tab_btn.svg');
		selectCategoryTeb.addClass('active');
	}
}
function clickFaqToggle() {
	let faq_toggle = document.querySelectorAll('.faq_toggle');
	faq_toggle.forEach(toggle => {
		toggle.addEventListener('click', function (e) {
			let el = e.currentTarget;
			let toggle_type = el.dataset.toggle_type;

			let faq_wrap = el.parentNode.parentNode;

			let request = faq_wrap.querySelector('.request');
			request.classList.toggle('hidden');

			if (toggle_type == "down") {
				el.dataset.toggle_type = "up";
				el.src = "/images/mypage/mypage_up_tab_btn.svg";
			} else {
				el.dataset.toggle_type = "down";
				el.src = "/images/mypage/mypage_down_tab_btn.svg";
			}
		});
	});
}

function faqKeywordHandler() {
	let search_icon = document.querySelectorAll('.search__icon__img');
	search_icon.forEach(search => {
		search.addEventListener('click', function (e) {
			let el = e.currentTarget;
			let param_keyword = el.parentNode.querySelector('.search__keyword').value;

			if (param_keyword != null && param_keyword != "") {
				searchFaqList(param_keyword);
			} else {
				return false;
			}
		})
	});

	let search_keyword = document.querySelectorAll('.search__keyword');
	search_keyword.forEach(keyword => {
		keyword.addEventListener('keyup', function (e) {
			let el = e.currentTarget;
			let param_keyword = el.value;

			if (param_keyword != null && param_keyword != "") {
				if (window.event.keyCode == 13) {
					searchFaqList(param_keyword);
				}
			} else {
				return false;
			}
		})
	});

	let init_keyword = document.querySelector('.init_keyword');
	init_keyword.addEventListener('click', function () {
		$('.search__keyword').val('');
		init_keyword.classList.add('hidden');
	});
}

function inquiryBtnClickEventHandler() {
	let inquiryTabWrap = document.querySelector('.inquiry__tab__wrap');
	let btn_inquiry = inquiryTabWrap.querySelector('.black__full__width__btn.inquiry');
	let inquiryCancelBtn = inquiryTabWrap.querySelector('.white__full__width__btn');

	btn_inquiry.addEventListener('click', function () {
		if (!btn_inquiry.classList.contains('disabled')) {
			addInqInfo();
		}
		
		//changeLanguageR();
	});

	inquiryCancelBtn.addEventListener('click', function () {
		initInquiryForm('frm-inquiry');
		$('.inquiry__wrap').find('.tab__btn__item').eq(2).click();
		changeLanguageR();
	});
}

function editInquiryEventHandler() {
	let inquiryEditWrap = document.querySelector('.inquiry_edit_wrap');
	let editBtn = inquiryEditWrap.querySelector('.black__full__width__btn');
	let closeBtn = inquiryEditWrap.querySelector('.close.close_inq_edit');

	editBtn.removeEventListener('click', editInquiry);
	closeBtn.removeEventListener('click', initForm);

	editBtn.addEventListener('click', editInquiry);
	closeBtn.addEventListener('click', initForm);

	function initForm() {
		initInquiryForm('frm-edit-inquiry');
	}
}
function initInquiryForm(form_id) {
	let inquiryForm = document.querySelector(`.inquiry__tab #${form_id}`);

	let previewObj = inquiryForm.querySelectorAll('.inquiry__photo__item');
	let fileObj = inquiryForm.querySelectorAll('input[type=file]');
	let textBoxObj = inquiryForm.querySelector('.inquiryTextBox');
	let titleObj = inquiryForm.querySelector('.inquiry_title');
	let typeObj = inquiryForm.querySelector('.inquiry_type');
	let selectDiv = inquiryForm.querySelector('.select-selected');

	previewObj.forEach(function (el) {
		el.querySelector('img').src = "/images/mypage/mypage_photo_btn.svg";
	})
	fileObj.forEach(function (el) {
		el.value = '';
	})
	textBoxObj.value = '';
	titleObj.value = '';
	typeObj[0].selected = true;
	let defaultTypeText = typeObj[0].text;
	selectDiv.innerHTML = defaultTypeText;

	if (form_id == 'frm-edit-inquiry') {
		let prev_location = inquiryForm.querySelectorAll('.priview_location');

		prev_location.forEach(function (el) {
			el.value = '';
		})
		inquiryForm.querySelector('.board_idx').value = '';
		document.querySelector('.inquiry_edit_wrap').style.display = 'none';
	}
}

function searchFaqList(param_keyword) {
	$('.inquiry__tab').hide();
	$('.inquiry__faq__detail__wrap').show();

	$('.category__small .click__btn').removeClass('click__btn');

	$('.search__keyword').val(param_keyword);

	getFaqList('search', param_keyword);
	document.querySelector('.init_keyword').classList.remove('hidden');
}

function getInqInfoList() {
	$.ajax({
		type: "post",
		url: api_location + "mypage/inquiry/get",
		data : {
			'data_type':"ENC"
		},
		dataType: "json",
		error: function (d) {
			// notiModal('문의내역 조회처리중 오류가 발생했습니다.');
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0055', null);
		},
		success: function (d) {
			if (d.code == 200) {
				let toggle_list_tab = document.querySelector('.toggle__list.inquiry__list .toggle__list__tab');

				let write_inquiry_html = "";

				let data = d.data;
				if (data != null && data.length > 0) {
					data.forEach(function (row) {
						let txt_request_status = "";
						let inquiry_request_html = "";

						let board_reply_info = row.board_reply_info;
						let board_img_info = row.board_img_info;

						let editable = "true";
						if (row.request_flg == true) {
							editable = "false";
							switch (getLanguage()) {
								case "KR":
									txt_request_status = '답변 완료';
									break;
								case "EN":
									txt_request_status = 'Inquiry completed';
									break;
								case "CN":
									txt_request_status = '回答完成';
									break;
							}
							inquiry_request_html += `
									Q. ${row.board_contents} 
									<div class="inquiry__photo__container">
							`;

							let board_img_info = row.board_img_info;
							if (board_img_info != null && board_img_info.length > 0) {
								board_img_info.forEach(function (img_row) {
									inquiry_request_html += `
										<div class="inquiry__photo__item">
											<div class="inquiry__photo__item">
												<img src="${cdn_img}${img_row.img_location}">
											</div>
										</div>
									`;
								});
							}

							inquiry_request_html += `
									</div>
							`;
							inquiry_request_html += `
								<p>A.</p>
								${board_reply_info[0].contents}
							`;
						} else if (row.request_flg == false) {
							switch (getLanguage()) {
								case "KR":
									txt_request_status = '답변 대기';
									break;
								case "EN":
									txt_request_status = 'Waiting for reply';
									break;
								case "CN":
									txt_request_status = '等待答复';
									break;
							}

							inquiry_request_html += `
									Q. ${row.board_contents} 
									<div class="inquiry__photo__container">
							`;

							let board_img_info = row.board_img_info;
							if (board_img_info != null && board_img_info.length > 0) {
								board_img_info.forEach(function (img_row) {
									inquiry_request_html += `
										<div class="inquiry__photo__item">
											<div class="inquiry__photo__item">
												<img src="${cdn_img}${img_row.img_location}">
											</div>
										</div>
									`;
								});
							}

							inquiry_request_html += `
									</div>
							`;
						}

						let i18nClass = "";

						switch (row.board_category) {
							case 'DAE':
								i18nClass = 'q_inquiry_subcategory_01';
								break;
							case 'CAR':
								i18nClass = 'q_inquiry_subcategory_02';
								break;
							case 'OAP':
								i18nClass = 'q_inquiry_subcategory_03';
								break;
							case 'FAD':
								i18nClass = 'q_inquiry_subcategory_04';
								break;
							case 'RAE':
								i18nClass = 'q_inquiry_subcategory_05';
								break;
							case 'RST':
								i18nClass = 'q_inquiry_subcategory_06';
								break;
							case 'AFS':
								i18nClass = 'q_inquiry_subcategory_10';
								break;
							case 'PIQ':
								i18nClass = 'q_inquiry_subcategory_07';
								break;
							case 'BGP':
								i18nClass = 'q_inquiry_subcategory_08';
								break;
							case 'VUC':
								i18nClass = 'm_voucher';
								break;
							case 'OSV':
								i18nClass = 'q_inquiry_subcategory_09';
								break;
						}

						write_inquiry_html += `
							<div class="toggle__item">
								<div class="pc_inquiry_list">
									<div class="description">
										<p>${row.create_date}</p>
										<p data-i18n="${i18nClass}">${row.txt_board_category}</p>
									</div>
									
									<div class="inquiry_question_wrap">
										<div class="inquiry_question">
											${row.board_title}
										</div>
										<div class="inquiry_request hidden">
											${inquiry_request_html}
										</div>
									</div>
									
									<div class="inquiry_question_wrap">
										<p class ="gray_text">${txt_request_status}</p>
									</div>
									
									<div class ="inquiry_question_wrap">
										<img class="down__up__icon inq_toggle" data-toggle_type="down" src="/images/mypage/mypage_down_tab_btn.svg">
									</div>
									
									<div class= "inquiry_btn_wrap">
										<div class="inquiry_history_btn">
											<span class="edit_inq" data-board_idx="${row.board_idx}" data-editable="${editable}" data-i18n="p_edit">수정</span>
										</div>
										
										<div class="inquiry_history_btn">
											<span class="delete_inq" data-board_idx="${row.board_idx}" data-i18n="p_delete">삭제</span>
										</div>
									</div>
								</div>
								
								<div class="mobile_inquiry_list">
									<div class="inquiry_list_flex">
										<div class="flex_box">
											<p class = "margin-right">
												${row.create_date}
											</p>
											<p class ="gray_text">
												${txt_request_status}
											</p>
										</div>
										
										<div class= "inquiry_btn_wrap">
											<div class="inquiry_history_btn">
												<span class="edit_inq" data-board_idx="${row.board_idx}" data-editable="${editable}" data-i18n="p_edit">수정</span>
											</div>
											<div class="inquiry_history_btn">
												<span class="delete_inq" data-board_idx="${row.board_idx}" data-i18n="p_delete">삭제</span>
											</div>
										</div>
									</div>
									
									<div class="description">
										<p data-i18n="${i18nClass}">${row.txt_board_category}</p>
									</div>
									
									<div class="inquiry_list_flex btn_toggle_inquiry">
										<div>
											<div class="display_box" >
												<div class="inquiry_question description" class="cursor_pointer">
													<p>${row.board_title}</p>
												</div>
												<div class="inquiry_request hidden">
													${inquiry_request_html}
												</div>
											</div>
										</div>
										
										<img class="down__up__icon inq_toggle" data-toggle_type="down" src="/images/mypage/mypage_down_tab_btn.svg">
									</div>
								</div>
							</div>
						`;
					});
				} else {
					let exception_msg = "";

					switch (getLanguage()) {
						case "KR":
							exception_msg = "등록된 문의 내역이 없습니다.";
							break;

						case "EN":
							exception_msg = "There is no registered inquiry history.";
							break;

						case "CN":
							exception_msg = "没有注册查询历史。";
							break;

					}
					write_inquiry_html = `
						<div class="no_inquiry_history">${exception_msg}</div>
					`;
				}
				
				toggle_list_tab.innerHTML = write_inquiry_html;

				toggleInqInfo();
				deleteInqInfo();
				editInqInfo();
				changeLanguageR();
				
				clickBtnToggleInquiry();
			}
		}
	});
}

function toggleInqInfo() {
	let toggle_list_tab = document.querySelector('.toggle__list.inquiry__list .toggle__list__tab');

	let inq_toggle = toggle_list_tab.querySelectorAll('.inq_toggle');
	inq_toggle.forEach(toggle => {
		toggle.addEventListener('click', function (e) {
			let el = e.currentTarget;
			let toggle_type = el.dataset.toggle_type;

			let inq_list = el.parentNode.parentNode;

			let inq_request = inq_list.querySelector('.inquiry_request');
			inq_request.classList.toggle('hidden');

			if (toggle_type == "down") {
				el.dataset.toggle_type = "up";
				el.src = "/images/mypage/mypage_up_tab_btn.svg";
			} else {
				el.dataset.toggle_type = "down";
				el.src = "/images/mypage/mypage_down_tab_btn.svg";
			}
		});
	});
}
function editInqInfo() {
	let toggle_list_tab = document.querySelector('.toggle__list.inquiry__list .toggle__list__tab');
	let inq_edit_wrap = document.querySelector('.inquiry_edit_wrap');
	let edit_inq = toggle_list_tab.querySelectorAll('.edit_inq');

	let previewObj = inq_edit_wrap.querySelectorAll('.inquiry__photo__item');
	let fileObj = inq_edit_wrap.querySelectorAll('input[type=file]');
	let pageIdxObj = inq_edit_wrap.querySelector('.board_idx');
	let textBoxObj = inq_edit_wrap.querySelector('.inquiryTextBox');
	let titleObj = inq_edit_wrap.querySelector('.inquiry_title');
	let typeObj = inq_edit_wrap.querySelector('.inquiry_type');
	let selectDiv = inq_edit_wrap.querySelector('.select-selected');

	edit_inq.forEach(edit_btn => {
		edit_btn.addEventListener('click', function (e) {
			let el = e.currentTarget;
			let board_idx = el.dataset.board_idx;
			if (board_idx != null) {
				$.ajax({
					type: "post",
					url: api_location + "mypage/inquiry/get",
					data: {
						'board_idx': board_idx,
						'data_type':"DEC"
					},
					dataType: "json",
					error: function (d) {
						// notiModal('문의내역 삭제처리중 오류가 발생했습니다.');
						makeMsgNoti(getLanguage(), 'MSG_F_ERR_0058', null);
					},
					success: function (d) {
						if (d.code == 200) {
							if (d.data != null) {
								let data = d.data[0];
								
								pageIdxObj.value = data.board_idx;
								titleObj.value = data.board_title;
								textBoxObj.value = data.board_contents;
								//selectDiv.innerHTML = data.txt_board_category;
								typeObj.value = data.board_category;
								
								//let i18nCategory = getI18nClass(data.txt_board_category);
								
								let i18nClass = "";

								switch (data.board_category) {
									case 'DAE':
										i18nClass = 'q_inquiry_subcategory_01';
										break;
									case 'CAR':
										i18nClass = 'q_inquiry_subcategory_02';
										break;
									case 'OAP':
										i18nClass = 'q_inquiry_subcategory_03';
										break;
									case 'FAD':
										i18nClass = 'q_inquiry_subcategory_04';
										break;
									case 'RAE':
										i18nClass = 'q_inquiry_subcategory_05';
										break;
									case 'RST':
										i18nClass = 'q_inquiry_subcategory_06';
										break;
									case 'AFS':
										i18nClass = 'q_inquiry_subcategory_10';
										break;
									case 'PIQ':
										i18nClass = 'q_inquiry_subcategory_07';
										break;
									case 'BGP':
										i18nClass = 'q_inquiry_subcategory_08';
										break;
									case 'VUC':
										i18nClass = 'm_voucher';
										break;
									case 'OSV':
										i18nClass = 'q_inquiry_subcategory_09';
										break;
								}
								
								selectDiv.dataset.i18n = i18nClass;
								
								let img_index = document.querySelector('.inquiry_edit_wrap .img_index');
								if (data.board_img_info != null && data.board_img_info.length > 0) {
									img_index.value = data.board_img_info.length;
									data.board_img_info.forEach(function (img_info, idx) {
										$('.priview_location').eq(idx).val(img_info.img_location);
										$('.priview_location').eq(idx).next().attr('src', cdn_img + img_info.img_location);
									})
								} else {
									img_index.value = 0;
								}
								
								changeLanguageR();
							}
							else {
								// notiModal('문의내역 정보를 찾을 수 없습니다.');
								makeMsgNoti(getLanguage(), 'MSG_F_ERR_0056', null);
							}
						}
						else {
							notiModal(d.msg);
						}
					}
				});
			}
			inq_edit_wrap.style.display = 'block';
		})
	});
	editInquiryEventHandler();
}

function editInquiry() {
	let form = $('#frm-edit-inquiry')[0];
	let formData = new FormData(form);

	$.ajax({
		type: "post",
		url: api_location + "mypage/inquiry/put",
		data: formData,
		dataType: "json",
		cache: false,
		contentType: false,
		processData: false,
		error: function (d) {
			// notiModal("문의수정 도중 오류가 발생했습니다.");
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0057', null);
		},
		success: function (d) {
			if (d.code == 200) {
				getInqInfoList();
				initInquiryForm('frm-edit-inquiry')
				changeSelectLang();
			} else {
				notiModal(d.msg);
			}
			let scroll_height = $(".common-contents-container").prop('scrollHeight');
			$(".quickview__content__wrap.faq.open .common-contents-container").animate({
				scrollTop: scroll_height
			}, 400);
			$(".quickview__content__wrap.faq.open").animate({
				scrollTop: scroll_height
			}, 400);
		}
	});
}

function deleteInqInfo() {
	let toggle_list_tab = document.querySelector('.toggle__list.inquiry__list .toggle__list__tab');

	let delete_inq = toggle_list_tab.querySelectorAll('.delete_inq');
	delete_inq.forEach(delete_btn => {
		delete_btn.addEventListener('click', function (e) {
			let el = e.currentTarget;
			let board_idx = el.dataset.board_idx;

			if (board_idx != null) {
				$.ajax({
					type: "post",
					url: api_location + "mypage/inquiry/delete",
					data: {
						'board_idx': board_idx
					},
					dataType: "json",
					error: function (d) {
						// notiModal('문의내역 삭제처리중 오류가 발생했습니다.');
						makeMsgNoti(getLanguage(), 'MSG_F_ERR_0049', null);
					},
					success: function (d) {
						getInqInfoList();
					}
				});
			}
		});
	});
}

function clickInqImg() {
	let inq_photo_item = document.querySelectorAll('.inquiry__photo__item');
	inq_photo_item.forEach(photo => {
		photo.addEventListener('click', function (e) {
			let el = e.currentTarget;
			let current_idx = el.dataset.img_idx;
			
			let frm = null;
			
			let inquiry_edit_wrap = document.querySelector('.inquiry_edit_wrap');
			if (inquiry_edit_wrap.style.display == "block") {
				frm = document.querySelector('#frm-edit-inquiry');
			} else {
				frm = document.querySelector('#frm-inquiry');
			}
			
			let board_img = frm.querySelectorAll('.board__image');
			
			let current_board_img = board_img[current_idx];
			
			if (current_board_img.value) {
				current_board_img.value = null;
				el.querySelector('img').src = "/images/mypage/mypage_photo_btn.svg";
			} else {
				let img_idx = 0;
				
				if (inquiry_edit_wrap.style.display == "block") {
					img_idx = document.querySelector('#frm-edit-inquiry .img_index').value;
				} else {
					board_img.forEach(img => {
						if (img.value) {
							img_idx++;
						}
					});
				}
				
				frm.querySelector('.img_index').value = img_idx;
				board_img[img_idx].click();
			}
		});
	});
}

function addInqInfo() {
	let form = $('#frm-inquiry')[0];
	let formData = new FormData(form);

	let title = $(".inquiry_title").val();
	let inquiry_text = $(".inquiryTextBox").val();

	if (title == null || title.length < 1) {
		// notiModal("문의 제목을 입력해주세요.");
		makeMsgNoti(getLanguage(), 'MSG_F_WRN_0044', null);
		return false;
	}
	if (inquiry_text == null || inquiry_text.length < 1) {
		// notiModal("문의 내용을 입력해주세요.");
		makeMsgNoti(getLanguage(), 'MSG_F_WRN_0061', null);
		return false;
	}
	
	let btn_inquiry = document.querySelector('.inquiry__tab__wrap .black__full__width__btn.inquiry');	
	btn_inquiry.classList.add('disabled');
	
	$.ajax({
		type: "post",
		url: api_location + "mypage/inquiry/add",
		data: formData,
		dataType: "json",
		cache: false,
		contentType: false,
		processData: false,
		error: function (d) {
			// notiModal("문의신청 도중 오류가 발생했습니다.");
			makeMsgNoti(getLanguage(), 'MSG_F_ERR_0054', null);
		},
		success: function (d) {
			if (d.code == 200) {
				initInquiryForm('frm-inquiry');
				getInqInfoList();
				$('.inquiry__wrap').find('.tab__btn__item').eq(2).click();
			} else {
				notiModal(d.msg);
			}

			//contents_container.append(contents_container_html);

			let scroll_height = $(".common-contents-container").prop('scrollHeight');
			$(".quickview__content__wrap.faq.open .common-contents-container").animate({
				scrollTop: scroll_height
			}, 400);
			$(".quickview__content__wrap.faq.open").animate({
				scrollTop: scroll_height
			}, 400);
		}
	});
}

function clickBtnToggleInquiry() {
	let btn_toggle_inquiry = document.querySelectorAll('.btn_toggle_inquiry');
	btn_toggle_inquiry.forEach(btn => {
		btn.addEventListener('click',function(e) {
			let el = e.currentTarget;
			toggleInquiryBtn(el);
		});
	});
}