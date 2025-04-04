get_contents = function(page) {
	$.ajax({
		url: config.api + "favorite/get",
		data: {
			page : (isNaN(page) == false)?1:page
		},
		success: function(d) {
			if(d.code == 200) {
				$("ul.list-cont").empty();

				if(d.data) {
					d.data.forEach(function(row) {
						set_list_figure($("#list"),row,row.category_keyword); 
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
				else {
					$("#list").html(`<li class="empty"><i class="xi-close-thin"></i>찜한 연습실이 없습니다.</li>`);
					$("#paging").empty();
				}
				$("ul.list-cont > li button.favorite").click(function() {
					let obj = this;
					confirm('찜한 연습실을 삭제할까요?',function() {
						$.ajax({
							url: config.api + "favorite/delete",
							data: {
								no : $(obj).data("no")
							},
							success: function(d) {
								if(d.code == 200) {
									get_contents(page);
								} else {
									alert(d.msg);
								}
							}
						});
					});
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

			}
			else {
				alert(d.msg);
			}
		}
	});
};
get_contents();
