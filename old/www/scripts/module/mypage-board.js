get_contents = function(page) {
	$.ajax({
		url: config.api + $("div.tab-cont > ul > li.on").data("code") + "/get",
		data: {
			mode : "mypage",
			page : (isNaN(page) == false)?1:page
		},
		success: function(d) {
			if(d.code == 200) {
				$("#list").empty();

				if(d.data) {
					let num = d.total - ((d.page-1)*20);
					d.data.forEach(function(row) {
						$("#list").append(`
							<tr data-no="${row.no}">
								<td>${num--}</td>
								<td class="no-click">
									<a href="/studio/${row.contents.no}" class="profile">
										<div class="profile-image" style="background-image:url('${row.contents.image}')"></div>
										<span class="id center">${row.contents.name}</span>
									</a>
								</td>
								<td>${row.text}</td>
								<td>${row.reg_date}</td>
								<td>${row.answer ? '답변' : '대기'}</td>
								<td class="no-click"><button type="button" class="delete"><i class="xi-trash"></i></button></td>
							</tr>
						`);
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
					$("#list tr > td:not(.no-click)").click(function() {
						modal('mypage-qna-detail',{ no : $(this).parent().data("no") });
					});
					$("#list button.delete").click(function() {
						let obj = this;
						confirm('해당 글을 삭제할까요?',function() {
							$.ajax({
								url: config.api + $("div.tab-cont > ul > li.on").data("code") + "/delete",
								data: {
									no : $(obj).parent().parent().data("no")
								},
								success: function(d) {
									if(d.code == 200) {
										alert("글이 삭제되었습니다.");
										get_contents(page);
									} else {
										alert(d.msg);
									}
								}
							});
						});
					});
				}
				else {
					$("#list").html(`
						<tr class="nodata">
							<td colspan="6"><i class="xi-close-thin"></i>작성 내역이 없습니다.</td>
						</tr>
					`);
					$("#paging").empty();
				}
			}
			else {
				alert(d.msg);
			}
		}
	});


}
$("div.tab-cont > ul > li").click(function() {
	$(this).addClass("on").siblings().removeClass("on");
	get_contents(1);
}).eq(0).click();
