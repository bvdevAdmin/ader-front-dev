$(document).ready(function() {
	let swiper = [];
	let goods = [
		["/upload/goods-1.png","Standic airpods leather case"],
		["/upload/goods-2.png","Twin heart logo cardigan"],
		["/upload/goods-3.png","Wide shopper bag"],
		["/upload/goods-4.png","Shopper bag"],
	];
	goods.forEach(function(row) {
		$("#swiper-goods .swiper-wrapper").append(`
			<a href="" class="swiper-slide"style="background-image:url('${row[0]}')"><span>${row[1]}</span></a>
		`);
	});
	if($(window).width() > 720) {
		swiper.push(new Swiper("#swiper-goods", {
			slidesPerView: 'auto',
			spaceBetween: 0,
			loop: true,
			navigation: {
				nextEl: "#swiper-goods .swiper-button-next",
				prevEl: "#swiper-goods .swiper-button-prev",
			},
		}));
	}


	let styling = [
		["/upload/styling-1.jpg","Metal line"],
		["/upload/styling-2.jpg","Exclusive t-shirts edition"],
		["/upload/styling-3.jpg","Summer Accessories"],
		["/upload/styling-4.jpg","Summer Accessories"],
	];
	styling.forEach(function(row) {
		$("#swiper-styling .swiper-wrapper").append(`
			<div class="swiper-slide">
				<div class="image" style="background-image:url('${row[0]}')"></div>
				<div class="title">
					${row[1]}
					<div class="links">
						<a href="">자세히보기</a>
					</div>
				</div>
			</div>
		`);
	});
	swiper.push(new Swiper("#swiper-styling", {
		slidesPerView: 'auto',
		spaceBetween: 0,
		loop: true,
		navigation: {
			nextEl: "#swiper-styling .swiper-button-next",
			prevEl: "#swiper-styling .swiper-button-prev",
		},
	}));


	let foryou = [
		["/upload/foryou-1.png","Tnnn blazer"],
		["/upload/foryou-2.png","Berengo coat"],
		["/upload/foryou-3.png","Curve; MU01"],
		["/upload/foryou-4.png","Wide shopper bag"],
		["/upload/foryou-5.png","Trace roll bag"],
		["/upload/foryou-6.png","Curve; MU01"],
	];
	foryou.forEach(function(row) {
		$("#swiper-foryou .swiper-wrapper").append(`
			<div class="swiper-slide">
				<button type="button" class="favorite"></button>
				<div class="image" style="background-image:url('${row[0]}')"></div>
				<a href="">${row[1]}</a>
			</div>
		`);
	});
	swiper.push(new Swiper("#swiper-foryou", {
		slidesPerView: 'auto',
		spaceBetween: 0,
		loop: true,
		navigation: {
			nextEl: "#swiper-foryou .swiper-button-next",
			prevEl: "#swiper-foryou .swiper-button-prev",
		},
	}));
});