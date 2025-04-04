let update_code = get_query_string('update_code');

$(document).ready(function() {
	if (update_code != null || update_code != undefined) {
		$.ajax({
			url: config.api + "order/cancel/complete",
			headers : {
				country : config.language
			},
			data: {
				'update_code'		:update_code
			},
			error: function () {
				makeMsgNoti('MSG_F_ERR_0046', null);
			},
			success: function (d) {
				if (d.code == 200) {
					let data = d.data;
					
					let cancel_price = data.cancel_price;
					$(".order-number-value").text(cancel_price.order_code);
					$('.order-date-value').text(cancel_price.create_date)
					
					document.querySelector('.o_product').textContent	= cancel_price.t_product;
					document.querySelector('.o_product').dataset.price	= cancel_price.product;
					
					document.querySelector('.o_member').textContent		= cancel_price.t_member;
					document.querySelector('.o_member').dataset.price	= cancel_price.member;

					document.querySelector('.o_discount').textContent	= cancel_price.t_discount;
					document.querySelector('.o_discount').dataset.price	= cancel_price.discount;
					
					document.querySelector('.o_mileage').textContent	= cancel_price.t_mileage;
					document.querySelector('.o_mileage').dataset.price	= cancel_price.mileage;
					
					document.querySelector('.o_delivery').textContent	= cancel_price.t_delivery;
					document.querySelector('.o_delivery').dataset.price	= cancel_price.delivery;

					document.querySelector('.o_return').textContent		= cancel_price.t_return;
					document.querySelector('.o_return').dataset.price	= cancel_price.return;

					document.querySelector('.o_cancel').textContent		= cancel_price.t_cancel;
					document.querySelector('.o_cancel').dataset.price	= cancel_price.cancel;
					
					let order_body	= document.querySelector(".order-body");
					
					let cancel_product = data.cancel_product;
					if (cancel_product != null && cancel_product.length > 0) {
						cancel_product.forEach(function(row) {
							let div_product = document.createElement("div");
							div_product.classList.add("order-product-box");
							
							div_product.innerHTML = `
								<a href="javascript:void(0);">
									<img class="order-product-img" src="${config.cdn}${row.img_location}">
								</a>
								<ul>
									<div>
										<li class="product-name">${row.product_name}</li>
										<li class="product-price">${row.product_price}</li>
										<li>
											<span class="name">${row.color}</span>
											<span class="colorchip ${(row.color_rgb == '#ffffff')?'white':''}" style="background-color:${row.color_rgb}"></span>
										</li>
										<li class="product-size">${row.option_name}</li>
									</div>
									<div>
										<li class="product-qty">
											Qty:<span class="qty-cnt">${row.product_qty}</span>
										</li>
									</div>
								</ul>
							`;
							
							order_body.appendChild(div_product);
						});
					}
					
				} else {
					alert(
						d.msg,
						function() {
							if (d.code == 401) {
								sessionStorage.setItem('r_url',location.href);
								location.href = `${config.base_url}/login`;
							}
						}
					);
				}
			}
		});
	} else {
		alert(
			'조회 가능한 주문 취소 내역이 존재하지 않습니다.',
			function() {
				location.href = `${config.base_url}`;
			}
		);
	}
	
	document.querySelector('.go-home').addEventListener('click',function() {
		location.href = `${config.base_url}`;
	});
	
	document.querySelector('.go-cancel-list').addEventListener('click',function() {
		location.href = `${config.base_url}/my/order`;
	});
});
