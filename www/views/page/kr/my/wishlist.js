let t_column = {
    KR : {
		t_01 : "위시리스트 내역이 없습니다.",
    },
    EN : {
		t_01 : "Please regist your wish list.",
    }
}

let is_phased = true
let last_idx = 0
    , page = localStorage.getItem("page")
    , data = {
		menu_idx: get_query_string("menu_idx"),
		menu_type: get_query_string("menu_type"),
		page_idx: get_query_string("page_idx"),
	};
$(window).scroll(function () {
    if (is_phased == false) return;
    if ($(this).scrollTop() + $(this).height() < $("main").height() - $("body > header").height() - ($("body > footer").height() * 2)) return;
    is_phased = false; // 중복 호출 방지

    if (last_idx > 0) {
        data.last_idx = last_idx;
    }

	$.ajax({
        url: config.api + "wishlist/get",
        headers : {
            country : config.language
        },
        data: data,
        success: function (d) {
            if (d.code == 200) {
                if (d.data != null && d.data.length > 0) {
                    d.data.forEach(row => {
                        // 사이즈
                        let size = '';
                        if (row.product_type == "B") {
                            if(row.product_size) {
                                row.product_size.forEach(row2 => {
                                    //if(row2.stock_status=='STSO' || 'option_name' in row2 == false) return;
                                    if('option_name' in row2 == false) return;
                                    size += `
                                        <li 
                                            data-no="${row2.product_idx}" 
                                            data-option_no="${row2.option_idx}" 
                                            data-type="${row2.size_type}" 
                                            class="${(row2.stock_status=='STSO')?'soldout':''}">
                                            <span class="name">${row2.option_name}</span>
                                        </li>
                                    `;
                                });
                            }
                        } else {
                            size = "<li>Set</li>";
                        }
                        
                        // 색상
                        let color = '';
                        if(row.product_color) {
                            row.product_color.forEach(row2 => {
                                //if(row2.stock_status=='STSO') return;
                                if(row2.color == null) return;
                                color += `
                                    <li data-no="${row2.product_idx}" class="${(row2.stock_status=='STSO')?'soldout':''}">
                                        <span class="name">${row2.color}</span>
                                        <span class="colorchip ${(row2.color_rgb=='#ffffff')?'white':''}" style="background-color:${row2.color_rgb}"></span>
                                    </li>
                                `;
                            });
                        }

                        last_idx = row.display_num;

                        $(`#list`).append(`
                            <li class="${(row.stock_status == 'STSO') ? 'soldout' : ''}" style="${(row.background_color != '') ? 'background-color:' + row.background_color : ''}">
                                <a href="${config.base_url}/shop/${row.product_idx}">
                                    <span 
                                        class="image" 
                                        style="background-image:url('${config.cdn + row.img_location}')"
                                    ></span>
                                </a>
                                <div class="info">
                                    <strong>${row.product_name}</strong>
                                    <span class="price ${row.discount > 0 ? ' discount' : ''}" data-discount="${row.discount}" data-saleprice="${row.sales_price}">
                                        <span class="cont">${row.price}</span>
                                    </span>
                                    <span class="color"><ul>${color}</ul></span>
                                    <span class="size"><ul>${size}</ul></span>
                                </div>
                                <button type="button" class="shop favorite on" data-goods_no="${row.product_idx}"></button>
                            </li>
						`);
                    });
                } else {
                    if ($('#list li').length == 0) {
                        $('#list').attr('style', 'height:100vh;display: grid; grid-template-columns: repeat(1, 1fr) !important;');

                        $('#list').append(`
                            <div class="page__none">
                                ${t_column[config.language]['t_01']}
                            </div>
                        `);
    
                        $(window).unbind();
                    }
                }
            } else if (d.code == 401) {
                alert(
                    d.msg,
                    function() {
                        if (d.code == 401) {
                            sessionStorage.setItem('r_url',location.href);
                            location.href = `${config.base_url}/login`;
                        } else {
                            location.href = config.base_url;
                        }
                    }
                );
            }

            setTimeout(() => {
                is_phased = true;
            }, 100);
        }
    });

})