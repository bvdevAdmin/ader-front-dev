$.ajax({
	url: config.api + "faq/category",
	success: function(d) {
		if(d.code == 200) {
			d.data.forEach(function(row) {
				$("div.tab-cont > ul").append(`<li data-no="${row.no}"><span>${row.title}</span></li>`);
			});
			get_contents = function(cat) {
				$("#list").empty();
				$.ajax({
					url: config.api + "faq/get",
					data: { category_no : cat },
					success: function(d) {
						if(d.code == 200) {
							d.data.forEach(function(row) {
								$("#list").append(`
									<li>
										<div class="row">
											<div class="category">${row.category}</div>
											<div class="title">${row.title}</div>
										</div>
										<div class="contents">${row.contents}</div>
									</li>
								`);
							});
							$("#list > li > .row > .title").click(function() {
								$(this).parent().parent().toggleClass("on");
							});
						}
					}
				});

			}
			$("div.tab-cont > ul > li").click(function() {
				$(this).addClass("on").siblings().removeClass("on");
				get_contents($(this).data("no"));
			}).eq(0).click();
		}
	}
});
