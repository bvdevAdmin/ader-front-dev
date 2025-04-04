$(document).ready(function() {
	const replace_keyword_underline = str => {
		let keyword = $("#frm input[name='keyword']").val().trim();
		if(keyword == '') return str;
		
		return str.replaceAll(keyword,`<u>${keyword}</u>`);
	}

	new Swiper(".swiper-container", {
		slidesPerView : 'auto',
		direction: 'horizontal',
		speed: 200,
		loop: false,
		touchStartPreventDefault: false,
		allowTouchMove: true,
		simulateTouch: true
	});

	/** 분류 불러오기 **/
	$.ajax({
		url : config.api + "faq/category",
		headers : {
			country : config.language
		},
		success: function(d) {
			if(d.code == 200) {
				d.data.forEach(row => {
					$("#faq-category").append(`
						<li class="swiper-slide" data-no="${row.no}">${row.title}</li>
					`);
				});

				$("#frm").submit(function() {
					let data = { keyword : $(this).find("input[name='keyword']").val().trim()};
					if(data.keyword == '') {
						data = {
							category_no : $("#faq-category > li.on").data("no")
						};
					}
					else {
						$("#faq-category > li.on").removeClass("on");
					}

					/** 내용 불러오기 **/
					$.ajax({
						url : config.api + "faq/get",
						headers : {
							country : config.language
						},
						data : data,
						success: function(d2) {
							if(d2.code == 200) {
								$("#faq-contents").empty();
								
								if (d2.data != null && d2.data.length > 0) {
									d2.data.forEach(row => {
										$("#faq-contents").append(`
											<h2>${replace_keyword_underline(row.title)}</h2>
											<dl></dl>
										`);
	
										$("#faq-contents > dl").last().append(`
											<dt>${replace_keyword_underline(decodeHTMLEntities(row.question))}</dt>
											<dd>
												<h3>${row.subcategory}</h3>
												${replace_keyword_underline(decodeHTMLEntities(row.answer))}
											</dd>
										`);
	
									});
	
									$("#faq-contents > dl > dt").click(function() {
										$(this).siblings("dt").removeClass("on");
										$(this).siblings("dd").slideUp("fast");
										$(this).toggleClass("on");
										if($(this).hasClass("on")) {
											$(this).next().slideDown("fast");
										}
										else {
											$(this).next().slideUp("fast");
										}
									});
								}
							}
							else {
								alert(d2.msg);
							}
						}
					});
					return false;
				});

				$("#faq-category > li").click(function() {
					$(this).addClass("on")
						.siblings().removeClass("on");
					$("#frm input[name='keyword']").val("");
					$("#frm").submit();
				}).eq(0).click();


			}
		}
	});
});