let board_idx = get_query_string('qna_idx');

let param_template = [];

$(document).ready(function () {
    $('#board_idx').val(board_idx);

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
						$('#frm-qna-update .textarea').html(template);
					}
					
				});
            } else {
                alert(d.msg);
            }
        }
    });
	
    $.ajax({
		url : config.api + "member/qna/get",
		headers : {
			country : config.language
		},
		data : {
			board_idx: board_idx
		},
		success : function(d) {
			if (d.code == 200) {
				if (d.data != null) {
					$('.category_idx').val(d.data.category_idx).trigger('change');
                    $('input[name="qna_title"]').val(d.data.question_title);
					$('.textarea').html(d.data.question_contents);
                    $('.textarea').focus();
                    
					let board_img = d.data.board_img;
					if (board_img != null && board_img.length > 0) {
						board_img.forEach(img => {
							$('.div_question_img').append(`
								<div class="question_img" style="background-image:url('${config.cdn}${img.img_location}')">
                                    <input type="hidden" name="img_idx[]" value="${img.img_idx}">
                                </div>
							`)
						});
					} else {
                        let msg_img = {
							KR : "등록된 문의 이미지가 존재하지 않습니다.",
							EN : "Inquiry images does not exist."
						}

                        $('.div_question_img').append(`
							${msg_img[config.language]}
						`);
                    }
                    
					let question_img = document.querySelectorAll('.question_img');
					if (question_img != null && question_img.length > 0) {
						question_img.forEach(img => {
							img.addEventListener('click',function(e) {
								let el = e.currentTarget;

                                let msg_confirm = {
                                    KR : {
                                        't_01' : "문의 이미 삭제",
                                        't_02' : "<p>삭제 한 문의 이미지 복구할 수 없습니다.</p><p>문의 이미지 삭제하시겠습니까?</p>",
                                    },
                                    EN : {
                                        't_01' : "Delete inquiry image",
                                        't_02' : "<p>Deleted inquiry images cannot be recovered.</p><p>Are you sure you want to delete your inquiry image?</p>",
                                    }
                                }

								confirm({
                                    title : msg_confirm[config.language]['t_01'],
                                    body : msg_confirm[config.language]['t_02'],
                                    ok : no => {
                                        $(el).remove();
                                    },
                                });
							});
						});
					}
				}
			} else {
				alert(
					d.msg,
					function() {
						if (d.code == 401) {
							location.href = `${config.base_url}/login`
						} else if (d.code == 300) {
							location.href = `${config.base_url}/my/customer/qna`
						}
					}
				)
			}
		}
	});

    let btn_update = document.querySelector('#frm-qna-update .btn_update');
    if (btn_update != null) {
        btn_update.addEventListener('click', function () {
            let frm_name = "frm-qna-update";

            let category_idx = $(`#${frm_name} .category_idx`).val()
            if (category_idx == null || category_idx.length == 0) {
                return false;
            }

            let qna_title = $(`#${frm_name} input[name=qna_title]`).val();
            if (qna_title == null || qna_title.length == 0) {
                return false;
            }

            let qna_contents = $(`#${frm_name} .textarea`).html();
            if (qna_contents != null && qna_contents.length > 0) {
                $(`#${frm_name} .qna_contents`).val(qna_contents);
            } else {
                return false;
            }

            let frm = $(`#${frm_name}`)[0];
            let formData = new FormData(frm);

            let check_img = checkImages();
            if (check_img != false) {
                putQnA(frm_name, formData);
            }
        });
    }

    let btn_cancel = document.querySelector('#frm-qna-update .cancel');
    if (btn_cancel != null) {
        btn_cancel.addEventListener('click',function() {
            location.href = `${config.base_url}/my/customer/qna`;
        });
    }

    loadImageFile();

    resetFileInput();
});

function putQnA(frm_name, formData) {
    $.ajax({
        url: config.api + "member/qna/put",
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
                    KR : "1:1 문의 수정이 완료되었습니다.",
                    EN : "1:1 inquiry has updated.",
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

    let cnt_upload = 0;
    inputs.forEach(input => {
        if (input.files.length > 0) {
            cnt_upload++;
        }
    });

    let message = "";
    let msg_img = {
        KR : "문의 이미지는 최대 5개 까지 등록 가능합니다.",
        EN : "You can upload 5 inquiry images."
    };

    let cnt_total = cnt_upload + parseInt($('.question_img').length);
    
    if (cnt_total > 5) {
        message = msg_img[config.language];
    } else {
        hasImage = true;
    }

    if (hasImage != true) {
        alert(message);
    }

    return hasImage;
}