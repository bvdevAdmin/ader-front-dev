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
			<div class="image" style="background-image:url('${row.image}')"></div>
			<div class="goods">
				<div class="title">${row.title}</div>
				<div class="price">${number_format(row.price)}</div>
				<div class="color">${row.color}</div>
				<div class="size">${row.size}</div>
			</div>
			<div class="buy">
				<dl>
					<dt>구매처</dt><dd>${row.store}</dd>
					<dt>Bluemark 시리얼코드</dt><dd>${row.bluemark}</dd>
					<dt>Bluemark 인증 날짜</dt><dd>${row.reg_date}</dd>
				</dl>
			</div>
			<button type="button" class="btn">A/S 신청</button>
		</li>
	`);
});