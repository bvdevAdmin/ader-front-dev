$(document).ready(function() {
	let board_idx = location.pathname.split("/")[5];

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
					document.querySelector('.question_date').textContent		= d.data.question_date;
					
					document.querySelector('.question_category').textContent	= d.data.question_category;
					document.querySelector('.question_title').textContent		= d.data.question_title;
					document.querySelector('.question_contents').innerHTML		= d.data.question_contents;

					let board_img = d.data.board_img;
					if (board_img != null && board_img.length > 0) {
						board_img.forEach(img => {
							$('.div_question_img').append(`
								<input type="hidden" name="img_idx" value="${img.img_idx}">
								<div class="question_img" style="background-image:url('${config.cdn}${img.img_location}')"></div>
							`);
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

					let board_answer = d.data.board_answer;
					if (board_answer != null) {
						document.querySelector('.div_answer .answer_date').textContent		= board_answer.answer_date;
						document.querySelector('.div_answer .answer_contents').innerHTML	= board_answer.answer_contents;
					} else {
						let msg_wait = {
							KR : {
								't_01' : "답변대기",
								't_02' : "빠른 시간 내에 답변드리겠습니다.<br>잠시만 기다려 주세요."
							},
							EN : {
								't_01' : "Wait",
								't_02' : "We will respond to you as soon as possible,<br>please wait a moment."
							}
						}

						document.querySelector('.div_answer .answer_date').textContent		= msg_wait[config.language]['t_01'];
						document.querySelector('.div_answer .answer_contents').innerHTML	= `
							<div class="list_none">
								${msg_wait[config.language]['t_02']}
							</div>
						`;
					}

					let question_img = document.querySelectorAll('.question_img');
					if (question_img != null && question_img.length > 0) {
						question_img.forEach(img => {
							img.addEventListener('click',function(e) {
								let el = e.currentTarget;

								let src = $(el).get(0).style.backgroundImage;
								src = src.replace(/url\(["']?(.*?)["']?\)/, '$1');
								
								window.open(`${src}`,"_blank","scrollbars=yes");
							});
						});
					}

					if (d.data.answer_flg == true) {
						$('.modify').remove();
						$('.delete').remove();
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

	let btn_modify = document.querySelector('.modify');
	if (btn_modify != null) {
		btn_modify.addEventListener('click',function() {
			location.href = `${config.base_url}/my/customer/qna/update?qna_idx=${board_idx}`;
		});
	}

	let btn_delete = document.querySelector('.delete');
	if (btn_delete != null) {
		btn_delete.addEventListener('click',function(e) {
			let el = e.currentTarget;
			
			let msg_confirm = {
				KR : {
					't_01' : "문의 삭제",
					't_02' : "<p>삭제 한 문의내용은 복구할 수 없습니다.</p><p>문의 내용을 삭제하시겠습니까?</p>",
					't_03' : "문의내용이 삭제되었습니다."
				},
				EN : {
					't_01' : "Delete inquiry",
					't_02' : "<p>Deleted inquiries cannot be recovered.</p><p>Are you sure you want to delete your inquiry?</p>",
					't_03' : "Inquiry has deleted."
				}
			}

			confirm({
				title : msg_confirm[config.language]['t_01'],
				body : msg_confirm[config.language]['t_02'],
				ok : no => {
					$.ajax({
						url : config.api + "member/qna/put",
						headers : {
							country : config.language
						},
						data : {
							'action_type'	:"DELETE",
							'board_idx'		:board_idx
						},
						success : function(d) {
							if (d.code == 200) {
								alert(
									msg_confirm[config.language]['t_03'],
									function () {
										location.href = `${config.base_url}/my/customer/qna`;
									}
								)
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
				},
			});
		});
	}
});