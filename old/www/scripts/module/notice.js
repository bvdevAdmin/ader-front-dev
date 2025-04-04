get_contents = function(page) {
	$("#list").empty();
	$.ajax({
		url: config.api + "board/get",
		data: { bbscode : 'notice', page : page },
		success: function(d) {
			if(d.code == 200) {
				d.data.forEach(function(row) {
					let reg_date = row.reg_date.split(" ");
					$("#list").append(`
						<li>
							<div class="row">
								<div class="regdate">${reg_date[0].replaceAll("-"," . ")}</div>
								<div class="title">${row.title}</div>
							</div>
							<div class="contents">${row.contents}</div>
						</li>
					`);
				});
				$("#list > li > .row > .title").click(function() {
					$(this).parent().parent().toggleClass("on");
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
			}
		}
	});

}
get_contents(1);
