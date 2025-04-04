let param_template = [];

$(document).ready(function () {
	getCategory();

    loadImageFile();

    resetFileInput();

    let btn_write = document.querySelector('#frm-qna-write .btn_write');
    if (btn_write != null) {
        btn_write.addEventListener('click', function () {
            let frm_name = "frm-qna-write";

            let msg_alert = {
                KR : {
                    't_01' : "문의 유형을 선택해주세요.",
                    't_02' : "문의 제목을 입력해주세요.",
                    't_03' : "문의 내용을 입력해주세요."
                },
                EN : {
                    't_01' : "Please select the inquiry category.",
                    't_02' : "Please enter the inquiry title.",
                    't_03' : "Please enter the inquiry contents"
                }
            }

            let category_idx = $(`#${frm_name} .category_idx`).val()
            if (category_idx == null || category_idx.length == 0) {
                alert(msg_alert[config.language]['t_01']);
                return false;
            }

            let qna_title = $(`#${frm_name} input[name=qna_title]`).val();
            if (qna_title == null || qna_title.length == 0) {
                alert(msg_alert[config.language]['t_02']);
                return false;
            }

            let qna_contents = $(`#${frm_name} .textarea`).html();
            if (qna_contents != null && qna_contents.length > 0 && qna_contents != "<p><br></p>") {
                $(`#${frm_name} .qna_contents`).val(qna_contents);
            } else {
                alert(msg_alert[config.language]['t_03']);
                return false;
            }

            let frm = $(`#${frm_name}`)[0];
            let formData = new FormData(frm);

            addQnA(frm_name, formData);
        });
    }

    let btn_cancel = document.querySelector('#frm-qna-write .cancel');
    if (btn_cancel != null) {
        btn_cancel.addEventListener('click',function() {
            location.href = `${config.base_url}/my/customer/qna`;
        });
    }
});

function getCategory() {
	$.ajax({
        url: config.api + "member/qna/category",
        headers : {
            country : config.language
        },
        dataType: "json",
		async:false,
        success: function (d) {
            if (d.code == 200) {
                let data = d.data;
				
				let div_category = $('.category_idx');
				div_category.html('');
				
				let str_div = "<option></option>";
				
				if (data != null && data.length > 0) {
					data.forEach(function(row) {
						str_div += `
							<option value="${row.category_idx}">
								${row.category_name}
							</option>
						`;
						
						param_template[row.category_idx] = row.template;
					});
				}
				
				div_category.append(str_div);
				
				div_category.on('change',function() {
					if ($(this).val() != null && $(this).val().length > 0) {
						let template = param_template[$(this).val()];
						$('#frm-qna-write .textarea').html(template);
                        $('#frm-qna-write .textarea').focus();
					}
				});
            } else {
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
    });
}

function addQnA(frm_name, formData) {
    $.ajax({
        url: config.api + "member/qna/add",
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
                let msg_cmp = {
                    KR : "1:1 문의 등록이 완료되었습니다.",
                    EN : "1:1 inquiry has registered.",
                }
                alert(
                    msg_cmp[config.language],
                    function() {
                        location.href = `${config.base_url}/my/customer/qna`;
                    }
                );
            } else {
                alert(d.msg);
            }
        }
    });
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
        input.addEventListener('click', function (event) {
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

function checkImages() {
    const inputs = document.querySelectorAll(`input[name="qna_img[]"]`);

    let hasImage = false;

    inputs.forEach(input => {
        if (input.files.length > 0) {
            hasImage = true;
        }
    });

    let msg_img = {
        KR : "하나 이상의 제품 이미지를 첨부해 주세요",
        EN : "Please upload more than 1 image."
    };

    if (!hasImage) {
        alert(msg_img[config.language]);
    }

    return hasImage
}