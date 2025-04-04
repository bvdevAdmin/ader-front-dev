if($("section.search > article.result").length) {
	let _category = ['studio','performance','audition','store'];
	[1,2,3,4].forEach(function(category) {
		$.ajax({
			url: config.api + "contents/get",
			data: {
				page : 1,
				category_no : category,
				keyword : $("#keyword").text()
			},
			success: function(d) {
				if(d.code == 200) {

					if($("#ad-platinum").length && d.platinum) {
						$("#ad-platinum").removeClass("hidden");
						d.platinum.forEach(function(row) {set_list_figure($("#list-platinum"),row); });
					}
					else {
						$("#ad-platinum").addClass("hidden");
					}
					if($("#ad-prime").length && d.prime) {
						$("#ad-prime").removeClass("hidden");
						d.prime.forEach(function(row) {set_list_figure($("#list-prime"),row); });
					}
					else {
						$("#ad-prime").addClass("hidden");
					}
					if(d.data) {
						d.data.forEach(function(row) {
							set_list_figure($("#list-"+_category[category-1]),row,_category[category-1]); 
						});
					}
					else {
						$("#list").html(`<li class="empty"><i class="xi-close-thin"></i>검색 결과가 없습니다.</li>`);
					}
					$("ul.list-cont > li button.favorite").click(function() {
						$(this).toggleClass("on");
					});
					$("ul.list-cont > li > a").mouseover(function() {
						if($(this).hasClass("on") == false) {
							$(this).addClass("on");
							let gap = $(this).find("span.scroll").width()-$(this).find("span.info > strong").width(),
								loop = 5,x = 0,interval;
							if(gap > 0) {
								gap += 50;
								interval = setInterval(function(_this) {
									x += loop;
									if(x > gap) x = gap;
									$(_this).find("span.scroll").css("transform",`translateX(-${x}px)`);
									if(x == gap) x = 0;
									if($(_this).hasClass("on") == false) {
										$(_this).find("span.scroll").css("transform",`translateX(0px)`);
										clearInterval(interval);
									}
								},100,$(this));
							}
						}
					});
					$("ul.list-cont > li > a").mouseleave(function() {
						$(this).removeClass("on");
					});

					paging({
						total : d.total,
						el : $("#list-"+_category[category-1]).next(),
						page : d.page,
						row : 20,
						show_paging : 9,
						fn : function(page) {
							get_contents(page);
						}
					});
				}
				else {
					alert(d.msg);
				}
			}
		});
	});
}
