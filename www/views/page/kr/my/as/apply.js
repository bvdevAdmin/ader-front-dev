let txt_none = {
    KR : "조회 가능한 Bluemark 인증내역이 존재하지 않습니다.",
    EN : "There is no bluemark verification history."
}

$(document).ready(function () {
    getAS_apply_list();

    let tab_bluemark = $('.tab li').eq(0);
    tab_bluemark.click(function () {
        //$('.article.submit').hide();
    });

    let tab_apply = $('.tab li').eq(1);
    tab_apply.click(function () {
        $('.article.submit').hide();
    });

    $('article.submit-ok').hide()

    /* 블루마크 인증 제품 - A/S 신청 버튼 클릭 처리 */
    clickBTN_add_B();

    /* 블루마크 인증 제품 - 취소 버튼 클릭 처리 */
    clickBTN_cancel_B();

    /* 블루마크 미인증 제품 - A/S 신청 버튼 클릭 처리 */
    clickBTN_add();

    /* 블루마크 미인증 제품 - A/S 신청 버튼 클릭 처리 */
    clickBTN_cancel();

	loadImageFile();

    resetFileInput();
});

const data = {
    rows: 10,
    page: 1
}

function getAS_apply_list() {
    $.ajax({
        url: config.api + "bluemark/list/get",
        headers : {
            country : config.language
        },
        data,
        success: function (d) {
            if (d.code == 200) {
                $("#list").html('');

                let data = d.data;
                if (data != null && data.length > 0) {
                    data.forEach(row => {
                        let tmp_class = "";
                        let tmp_txt = {
                            KR : "A/S 신청",
                            EN : "Apply",
                        };

                        let cnt_as = row.cnt_as;
                        if (cnt_as > 0) {
                            tmp_class = "black";
                            if (config.language == "KR") {
                                tmp_txt[config.language] = "A/S 신청 완료";
                            } else if (config.language == "EN") {
                                tmp_txt[config.language] = "Apply complete";
                            }
                        }

                        let dt = {
                            KR : {
                                't_01' : "구매처",
                                't_02' : "Bluemark 시리얼코드",
                                't_03' : "Bluemark 인증날짜",
                                't_04' : tmp_txt[config.language]
                            },
                            EN : {
                                't_01' : "Purchase mall",
                                't_02' : "Bluemark serial",
                                't_03' : "Bluemark date",
                                't_04' : tmp_txt[config.language]
                            }
                        }

                        $("#list").append(`
							<li>
								<div class="image" style="background-image:url('${config.cdn}${row.img_location}')"></div>
								<div class="goods">
									<div class="title">${row.product_name}</div>
									<div class="price">${number_format(row.price)}</div>
									<div class="color">${row.color}</div>
									<div class="size">${row.option_name}</div>
								</div>
								<div class="buy">
									<dl>
										<dt>${dt[config.language]['t_01']}</dt>
                                        <dd>${row.purchase_mall}</dd>
										<dt>${dt[config.language]['t_02']}</dt>
                                        <dd>${row.serial_code}</dd>
										<dt>${dt[config.language]['t_03']}</dt>
                                        <dd>${row.reg_date}</dd>
									</dl>
								</div>
								
								<button type="button" class="btn btn_apply ${tmp_class}" data-serial_code="${row.serial_code}">
                                    ${dt[config.language]['t_04']}
                                </button>
							</li>
						`);
                    });
                } else {
                    $("#list").append(`
                        <div class="list__none">
                            ${txt_none[config.language]}
                        </div>
                    `);
                }

                /* 블루마크 인증 제품 - 블루마크 인증내역 A/S 신청 버튼 클릭 처리 */
                clickBTN_apply();
            }

            /** 페이징 처리 **/
            if ('page' in d) {
                paging({
                    total: d.total,
                    el: $(".paging"),
                    page: d.page,
                    rows: data.rows,
                    show_paging: 10,
                    fn: function (page) {
                        data.page = page
                        getAS_apply_list();
                    }
                });
            }
        }
    });
}

/* 블루마크 인증 제품 - 블루마크 인증내역 A/S 신청 버튼 클릭 처리 */
function clickBTN_apply() {
    let btn_apply = document.querySelectorAll('.btn_apply');
    if (btn_apply != null && btn_apply.length > 0) {
        btn_apply.forEach(btn => {
            btn.addEventListener('click', function (e) {
                let el = e.currentTarget;

                if (!el.classList.contains('black')) {
                    initAS('frm-as-submit');

                    let serial_code = el.dataset.serial_code;

                    $('.tab section').eq(0).removeClass('on');
                    $('.article.submit').show();

                    $('#frm-as-submit .serial_code').val(serial_code);
                }
            });
        });
    }
}

/* 블루마크 인증 제품 - A/S 신청 버튼 클릭 처리 */
function clickBTN_add_B() {
    let btn_add = document.querySelector('#frm-as-submit .btn_add');
    if (btn_add != null) {
        btn_add.addEventListener('click', function (e) {
            let el = e.currentTarget;

            let frm_name = 'frm-as-submit';

            let serial_code = $(`#${frm_name} .serial_code`).val();
            if (serial_code == 0) {
                return false;
            }

            let as_contents = $(`#${frm_name} .textarea`).html();
            if (as_contents != null && as_contents.length > 0) {
                $(`#${frm_name} .as_contents`).val(as_contents);
            } else {
                return false;
            }

            let frm = $(`#${frm_name}`)[0];
            let formData = new FormData(frm);

            addAS(frm_name, formData);
        });
    }
}

/* 블루마크 인증 제품 - 취소 버튼 클릭 처리 */
function clickBTN_cancel_B() {
    let btn_cancel = document.querySelector('#frm-as-submit .cancel');
    if (btn_cancel != null) {
        btn_cancel.addEventListener('click', function () {
            $('.tab section').eq(0).addClass('on');
            $('.article.submit').hide();
        });
    }
}

/* 블루마크 미인증 제품 - A/S 신청 버튼 클릭 처리 */
function clickBTN_add() {
    let btn_add = document.querySelector('#frm-as-submit-nocerty .btn_add');
    if (btn_add != null) {
        btn_add.addEventListener('click', function () {
            let frm_name = 'frm-as-submit-nocerty';

            let as_category = $(`#${frm_name} .as_category`).val();
            if (as_category == null || as_category == 0) {
                console.log('as_category');
                return false;
            }

            let barcode = $(`#${frm_name} .barcode`).val();
            if (barcode == null || barcode.length == 0) {
                console.log('barcode')
                return false;
            }

            let as_contents = $(`#${frm_name} .textarea`).html();
            if (as_contents != null && as_contents.length > 0) {
                $(`#${frm_name} .as_contents`).val(as_contents);
            } else {
                console.log('as_contents');
                return false;
            }

            if( !checkImages( 'product_img' ) ) return false
            if( !checkImages( 'receipt_img' ) ) return false

            let frm = $(`#${frm_name}`)[0];
            let formData = new FormData(frm);

            addAS(frm_name, formData);
        });
    }
}

/* 블루마크 미인증 제품 - A/S 신청 버튼 클릭 처리 */
function clickBTN_cancel() {
    let btn_cancel = document.querySelector('#frm-as-submit-nocerty .cancel');
    if (btn_cancel != null) {
        btn_cancel.addEventListener('click', function () {
            initAS('frm-as-submit-nocerty');
        });
    }
}

function addAS(frm_name, formData) {
    $.ajax({
        url: config.api + "as/add",
        headers : {
            country : config.language
        },
        data: formData,
        dataType: "json",
        cache: false,
        contentType: false,
        processData: false,
        data: formData,
        beforeSend: function() {
			loadingMASK();
		},
        success: function (d) {
            closeMASK();
            
            if (d.code == 200) {
                initAS(frm_name);

                getAS_apply_list();

                let msg_alert = {
                    KR : "A/S 신청이 완료되었습니다.",
                    EN : "A/S apply has completed"
                }
                alert(
                    msg_alert[config.language],
                    function() {
                        location.href = `${config.base_url}/my/as`;
                    }
                );
            } else {
                alert(d.msg);
            }
        }
    });
}

function initAS(frm_name) {
    let frm = $(`#${frm_name}`);

    let serial_code = frm.find('.serial_code');
    if (serial_code != null) {
        serial_code.val(0);
    }

    let as_category = frm.find('.as_category');
    if (as_category != null) {
        as_category.val('');
    }

    let barcode = frm.find('.barcode');
    if (barcode != null) {
        barcode.val('');
    }

    let as_contents = frm.find('.as_contents');
    if (as_contents != null) {
        as_contents.html('');
    }

    let textarea = frm.find('.textarea');
    if (textarea != null) {
        textarea.html('');
    }

    let fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        clearFileInput(input)
    })
}

function loadImageFile() {
	let fileInputs = document.querySelectorAll('input[type="file"]');
	fileInputs.forEach(input => {
		input.addEventListener('change', function (event) {
			const file = event.target.files[0];
			if (file && file.type.startsWith('image/')) {
				const reader = new FileReader();
				reader.onload = function (e) {
					const img = input.nextElementSibling;
					img.src = e.target.result;
					img.style = 'width: 100%;height: 100%'
				};
				reader.readAsDataURL(file);
			}
		});
	});
}

function resetFileInput() {
    let fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(input => {
        input.addEventListener('click', function ( event ) {
            clearFileInput(input, event)
        })
    })
}

function clearFileInput(input, event) {
    if (input.files.length > 0) {
        input.value = '';
        const imgPreview = input.nextElementSibling;
        imgPreview.style.display = 'none';

        event && event.preventDefault();
    }
}

function checkImages( name ) {
    const inputs = document.querySelectorAll(`input[name="${name}[]"]`);

    let hasImage = false;

    inputs.forEach(input => {
        if (input.files.length > 0) {
            hasImage = true;
        }
    });

    let msg_img = {
        KR : {
            'product_img' : "하나 이상의 제품 이미지를 첨부해 주세요.",
            'receipt_img' : "하나 이상의 구매 이력, 증빙이미지를 첨부해 주세요."
        },
        EN : {
            'product_img' : "Please attach at least one product image.",
            'receipt_img' : "Please attach at least one purchase evidence image."
        }
    }
    
    if (!hasImage) {
        alert(msg_img[config.language][name]);
    }

    return hasImage
}