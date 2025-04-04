document.addEventListener('DOMContentLoaded', function () {
    $('.service__tab').hide();
    $('.service__notice__wrap').show();
	
	getNoticeInfoList();
	getPolicyInfoList();
});

function getNoticeInfoList() {
	$.ajax({
		type: "post",
		url: api_location + "mypage/notice/get",
		dataType: "json",
		error: function (d) {
		//   notiModal("공지사항", "공지사항을 조회처리중 오류가 발생했습니다.");
		makeMsgNoti(getLanguage(), 'MSG_F_ERR_0070', null);
		},
		success: function (d) {
			if (d.code == 200) {
				if (d.data != null && d.data.length > 0) {
					$('.toggle__list__tab.01').html('');
				
					d.data.forEach(function (row) {
						let fix_btn_html = "";
						if (row.fix_flg == true) {
							fix_btn_html = `<img src="/images/mypage/mypage_fixed_icon.svg" style="float:left;margin-right:5px;">`;
						}
				
						// Use a div to temporarily hold the HTML content
						let tempDiv = document.createElement('div');
						tempDiv.innerHTML = row.contents;
				
						// Convert align attributes to text-align styles
						$(tempDiv).find('p[align]').each(function() {
							let align = $(this).attr('align');
							$(this).css('text-align', align);
							$(this).removeAttr('align');
						});
				
						// Get the modified HTML content from tempDiv
						let modifiedHtml = tempDiv.innerHTML;

						console.log(modifiedHtml);

						let strDiv = `
							<div class="toggle__item">
								<div class="question">
									${fix_btn_html}
									<span>${row.title}</span>
									<img src="/images/mypage/mypage_down_tab_btn.svg" class="down__up__icon" style="float:right;margin-top:10px;">
								</div>
								<div class="request" style="display:none">` + modifiedHtml + `</div>
							</div>
						`;
				
						let $strDiv = $(strDiv);
				
						// Append modified HTML to the .request element
						// $strDiv.find('.request').html(modifiedHtml);

						$('.toggle__list__tab.01').append($strDiv);
					});
				} else {
					let exception_msg = "";
				
					switch (getLanguage()) {
						case "KR":
							exception_msg = "공지사항이 없습니다.";
							break;
						case "EN":
							exception_msg = "There is no history.";
							break;
						case "CN":
							exception_msg = "没有查询到相关资料。​";
							break;
					}
					let strDiv = `
						<div class="no_service_notice_msg">
							${exception_msg}
						</div>
					`;
				
					$('.toggle__list__tab.01').append(strDiv);
				}
				
				$('.service__tab__wrap .request p').css('text-align', 'left');
				$('.service__tab__wrap .request img').css('width', '670px');
			} else {
				if (d.code = 401) {
					$('#notimodal-modal .close-btn').attr('onclick', 'location.href="/login"');
				}
				
				notiModal("공지사항", d.msg);
			}
			
			$('.question').on('click', function () {
				$('.request').not($(this).next()).hide();
				$('.question').find('img.down__up__icon').attr('src', '/images/mypage/mypage_down_tab_btn.svg');

				if ($(this).next().css('display') == 'none') {
					$(this).find('img.down__up__icon').attr('src', '/images/mypage/mypage_up_tab_btn.svg');
				} else {
					$(this).find('img.down__up__icon').attr('src', '/images/mypage/mypage_down_tab_btn.svg');
				}
				$(this).next().toggle();
			})

		}
	});
}

function getPolicyInfoList() {
	$.ajax({
		type:"post",
		url: api_location + "policy/page/get",
		dataType: "json",
		error: function() {
		//   notiModal("법적 고지사항 조회에 실패했습니다.");
		  makeMsgNoti(getLanguage(), 'MSG_F_ERR_0037', null);
		},
		success: function(d) {
			if (d.code == 200) {
				let data = d.data;
				if(data != null) {
					data.forEach(el => {
						let policy_wrap = null;
						switch (el.policy_type) {
							case "GUD" :
								policy_wrap = document.querySelector(".service__tab__wrap .service__guide__wrap");
								break;
							
							case "PNL" :
								policy_wrap = document.querySelector(".service__tab__wrap .service__policy__wrap");
								break;
							
							case "TRM" :
								policy_wrap = document.querySelector(".service__tab__wrap .service__terms__wrap");
								break;
						}
						
						policy_wrap.innerHTML = el.policy_txt;
					});
				} else {
				//   notiModal("법적 고지사항 정보가 존재하지 않습니다.");
				makeMsgNoti(getLanguage(), 'MSG_F_ERR_0038', null);
				}
			} else {
				notiModal(d.msg);
			}
		}
	});
}