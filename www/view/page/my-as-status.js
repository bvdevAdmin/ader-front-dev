let data = [
	{
		image : "",
		title : "Standic logo hoodie zip-up hoodie",
		price : 329000,
		color : "Beige",
		size : "A1",
		store : "ADER Sinsa Space",
		bluemark : "BLAFWBZ06BR2-ERO4T02A",
		reg_date : "2022.12.14",
	},
	{
		image : "",
		title : "Standic logo hoodie zip-up hoodie",
		price : 329000,
		color : "Beige",
		size : "A1",
		store : "ADER Sinsa Space",
		bluemark : "BLAFWBZ06BR2-ERO4T02A",
		reg_date : "2022.12.14",
	},
	{
		image : "",
		title : "Standic logo hoodie zip-up hoodie",
		price : 329000,
		color : "Beige",
		size : "A1",
		store : "ADER Sinsa Space",
		bluemark : "BLAFWBZ06BR2-ERO4T02A",
		reg_date : "2022.12.14",
	},
	{
		image : "",
		title : "Standic logo hoodie zip-up hoodie",
		price : 329000,
		color : "Beige",
		size : "A1",
		store : "ADER Sinsa Space",
		bluemark : "BLAFWBZ06BR2-ERO4T02A",
		reg_date : "2022.12.14",
	},
	{
		image : "",
		title : "Standic logo hoodie zip-up hoodie",
		price : 329000,
		color : "Beige",
		size : "A1",
		store : "ADER Sinsa Space",
		bluemark : "BLAFWBZ06BR2-ERO4T02A",
		reg_date : "2022.12.14",
	},
	{
		image : "",
		title : "Standic logo hoodie zip-up hoodie",
		price : 329000,
		color : "Beige",
		size : "A1",
		store : "ADER Sinsa Space",
		bluemark : "BLAFWBZ06BR2-ERO4T02A",
		reg_date : "2022.12.14",
	},
];

data.forEach(row => {
	$("#list").append(`
		<li>
			<header>
				<dl>
					<dt>A/S번호</dt><dd>KR-20230401-168747609</dd>
					<dt>신청 날짜</dt><dd>${row.reg_date}</dd>
				</dl>
				<a href="/my/as/status/detail" class="btn">자세히보기</a>
			</header>
			<div class="image" style="background-image:url('${row.image}')"></div>
			<div class="goods">
				<div class="title">${row.title}</div>
				<div class="price">${number_format(row.price)}</div>
				<div class="color">${row.color}</div>
				<div class="size">${row.size}</div>
			</div>
			<dl class="buy">
				<dt>구매처</dt><dd>${row.store}</dd>
				<dt>Bluemark 시리얼코드</dt><dd>${row.bluemark}</dd>
				<dt>Bluemark 인증 날짜</dt><dd>${row.reg_date}</dd>
			</dl>
		</li>
	`);
});