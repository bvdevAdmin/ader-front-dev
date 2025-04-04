let txt_none = {
	KR : "등록된 문의내역이 없습니다.",
	EN : "There is no QnA."
}

$(document).ready(function() {
	getQuestion_list();

	$(window).resize(function() {
		if (window.is_mobile) {
			$('.qna.wrap-720 .contents-list .category').addClass('hidden');
			$('.qna.wrap-720 .contents-list .date').addClass('hidden');
		} else {
			$('.qna.wrap-720 .contents-list .category').removeClass('hidden');
			$('.qna.wrap-720 .contents-list .date').removeClass('hidden');
		}
	}).resize();
});

const pageData = {
	rows: 10,
	page: 1
}

function getQuestion_list() {
	$.ajax({
		url: config.api + "member/qna/list/get",
		headers : {
			country : config.language
		},
		data: {
            ...pageData
        },
		error: function () {
			makeMsgNoti(config.language,'MSG_F_ERR_0046', null);
		},
		success: function (d) {
			if (d.code == 200) {
				$('.qna.wrap-720 .contents-list').empty();
				if (d.data != null && d.data.length > 0) {
					d.data.forEach(function(row) {
						let t_answer = {
							KR : {
								't_01' : "답변대기",
								't_02' : "답변완료"
							},
							EN : {
								't_01' : "Waiting",
								't_02' : "Complete"
							}
						}

						let answer_status = t_answer[config.language]['t_01'];
						if (row.answer_flg == true) {
							answer_status = t_answer[config.language]['t_02'];
						}

						$('.qna.wrap-720 .contents-list').append(`
							<li>
								<div class="category">${row.question_category}</div>
								<div class="title" data-no="${row.board_idx}">${row.board_title}</div>
								<div class="answer">${answer_status}</div>
								<div class="date">${row.create_date}</div>
							</li>
						`);

						if (window.is_mobile) {
							$('.qna.wrap-720 .contents-list .category').addClass('hidden');
							$('.qna.wrap-720 .contents-list .date').addClass('hidden');
						} else {
							$('.qna.wrap-720 .contents-list .category').removeClass('hidden');
							$('.qna.wrap-720 .contents-list .date').removeClass('hidden');
						}
					});
				} else {
					$('.qna.wrap-720 .contents-list').append(`
						<div class="list__none">
							${txt_none[config.language]}
						</li>
					`);
				}
				
				if('page' in d) {
					paging({
						total : d.total,
						el : $(".paging"),
						page : d.page,
						rows : pageData.rows,
						show_paging : 10,
						fn : function(page) {
							pageData.page = page;
							getQuestion_list();
						}
					});
				}

				let board_title = document.querySelectorAll('.qna.wrap-720 .contents-list .title');
				if (board_title != null && board_title.length > 0) {
					board_title.forEach(title => {
						title.addEventListener('click',function(e) {
							let el = e.currentTarget;
							
							if (el.dataset.no != null) {
								location.href = `${config.base_url}/my/customer/qna/${el.dataset.no}`;
							}
						});
					});
				}
			} else {
				alert(
					d.msg,
					function() {
						if (d.code == 401) {
							sessionStorage.setItem('r_url',location.href);
							location.href = `${config.base_url}/login`;
						}
					}
				)
			}
		}
	});
}
