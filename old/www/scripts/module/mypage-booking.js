get_contents = function(page,status) {
	$.ajax({
		url: config.api + "booking/get",
		data: {
			page : (isNaN(page) == false)?1:page,
			status : (typeof status != 'undefined' && $.inArray(status,['','예약신청','결제대기','결제완료','변경신청','취소환불','사용완료']) > -1)?status:''
		},
		success: function(d) {
			if(d.code == 200) {
				$("#list").empty();

				if(d.data) {
					d.data.forEach(function(row) {
						let buttons = '',time_term = row.date[5]/60;
						switch(status) {
							/*
							case '결제대기':
							case '결제완료':
							case '변경신청':
							case '예약신청':
								buttons = `
									<button type="button" class="modify"><i class="xi-scissors"></i></button>
									<button type="button" class="delete"><i class="xi-trash"></i></button>
								`;
							break;
							*/
							case '취소환불':
							case '사용완료':
								buttons = `
									<button type="button" class="delete">내역삭제</button>
								`;
							break;

							default:
								buttons = ``;
								if(row.receipt_number != null && row.pay.price > 0) {
									buttons = `<button type="button" class="modify">예약변경</button>`;
								}
								buttons +=`<button type="button" class="delete">예약취소</button>`;

						}
						$("#list").append(`
							<tr data-no="${row.no}">
								<td>${row.receipt_number ? row.receipt_number : ''}</td>
								<td>
									<a class="profile">
										<div class="profile-image" style="background-image:url('${row.contents.detail.image}')"></div>
										<span class="name"><strong>${row.contents.title}</strong><br>${row.contents.detail.title}</span>
									</a>
								</td>
								<td><i class="xi-calendar"></i> ${row.date[2]}<br><i class="xi-clock-o"></i> ${row.date[3]}:00~${row.date[4]}:59 (${time_term}시간)</td>
								<td></td>
								<td>${number_format(row.contents.detail.amount)} 원</td>
								<td>${buttons}</td>
							</tr>
						`);

						/** 삭제 **/
						$("#list > tr").last().find("button.delete").click(function() {
							let booking_no = $(this).parent().parent().data("no"),
								str = "해당 예약내역을 삭제할까요?";

							if($.inArray(row.status,['취소환불','사용완료']) == -1) {
								str = "해당 예약을 취소할까요?<br><br><strong>환불 규정</strong><br>";
								if(parseInt(row.contents.refund_rule.pay_after) > 0) {
									str += `결제 후 ${row.contents.refund_rule.pay_after}시간 이내에는 100% 환불이 가능합니다.(단, 이용시간 전까지만 가능)`;
								}
								str += '<ul class="refund-rule">';
								if('day' in row.contents.refund_rule) {
									for(key in d.data[0].contents.refund_rule.day) {
										let val = parseInt(d.data[0].contents.refund_rule.day[key]);
										let refund_rule;
										switch(parseInt(key)) {
											case 0:
												refund_rule = `<span class="d-day">이용 당일</span>`;
											break;
											case 1:
												refund_rule = `<span class="d-day">이용 전날</span>`;
											break;
											default:
												refund_rule = `<span class="d-day">이용 ${key}일 전</span>`;
											break;
										}
										refund_rule += (val == 0)?'환불 불가':`총 금액의 ${d.data[0].contents.refund_rule.day[key]}% 환불`;
										str += `<li>${refund_rule}</li>`;
									}
								}
								str += '</ul>';
							}
							confirm(str,function() {
								$.ajax({
									url: config.api + "booking/cancel",
									data: { booking_no : booking_no },
									success: function(d) {
										if(d.code == 200) {
											alert(($.inArray(status,['취소환불','사용완료']) > -1)?"해당 예약내역을 삭제했습니다.":"해당 예약을 취소했습니다.");

											get_contents($("#paging li.now").data("page"),$("div.tab-cont > ul > li.on").data("status"));
											
										}
										else {
											alert(d.msg);
										}
									}
								});
							});
						});

					});
					paging({
						total : d.total,
						el : $("#paging"),
						page : d.page,
						row : 20,
						show_paging : 9,
						fn : function(page) {
							get_contents(page);
						}
					});

					/** 변경 신청 **/
					$("#list button.modify").click(function() {
						modal('booking-modify',{ no : $(this).parent().parent().data("no") });
					});

				}
				else {
					$("#list").html(`<tr class="nodata"><td colspan="6"><i class="xi-close-thin"></i>예약 내역이 없습니다.</td></tr>`);
					$("#paging").empty();
				}
			}
			else {
				alert(d.msg);
			}
		}
	});
};
$("div.tab-cont > ul > li").click(function() {
	$(this).siblings().removeClass("on");
	$(this).addClass("on");
	get_contents(1,$(this).data("status"));
}).eq(0).click();
