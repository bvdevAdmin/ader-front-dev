let t_column = {
    KR : {
		t_01 : "주문 상세",
		t_02 : "주문 취소"
    },
    EN : {
		t_01 : "Detail",
		t_02 : "Cancel"
    }
}

let txt_none = {
	KR : "조회 가능한 주문내역이 존재하지 않습니다.",
	EN : "There is no order history.",
}

$(document).ready(function () {
    get_order_list();
    get_order_list('DCP');

    $("form#frm-order-search button.btn").click(function (e) {
        e.stopPropagation()
        e.preventDefault()

        get_order_list();
        get_order_list('DCP');
    })

    $('.input_date').change(function() {
        $(this).attr('placeholder',$(this).val());
    })

    setDateButton();
});

const pageData = {
    rows: 10,
    page: 1
}

function get_order_list(order_status) {
    let date_from = $('#date_from').val();
    let date_to = $('#date_to').val();
    let listId = order_status == 'DCP' ? 'list2' : 'list'

    $.ajax({
        url: config.api + "order/list/get",
        headers : {
            country : config.language
        },
        data: {
            ...pageData,
            date_from, date_to,
            order_status
        },
        error: function () {
            makeMsgNoti(config.language,'MSG_F_ERR_0046', null);
        },
        success: function (d) {
            if (d.code == 200) {
                $(`ul#${listId}`).empty();
				if (d.data.order_info.length > 0) {
					d.data.order_info.forEach(row => {
						let btn_cancel = "";
						if (row.update_flg == true) {
							btn_cancel += `
								<button type="button" class="cancel-btn t_02" data-order_code="${row.order_code}">
                                    ${t_column[config.language]['t_02']}
                                </button>
							`;
						}

                        let delivery = "";
                        if (row.company_name != null && row.delivery_num != null) {
                            delivery = `
                                <div class="delivery">
                                    <div class="company">${row.company_name}</div>
                                    <div class="delivery_num">${row.delivery_num}</div>
                                </div>
                            `;
                        }
						
						$(`ul#${listId}`).append(`
							<li data-no="${row.order_idx}">
                                <span class="image" style="background-image:url('${config.cdn + row.img_location}')" data-no="${row.order_idx}"></span>
								<div class="info">
									<div class="left-info">
										<div class="code">${row.order_code}</div>
										<div class="title">${row.order_title}</div>						                
										<div class="price">${row.price_total}</div>
                                        ${delivery}
									</div>
									<div class="right-info">
										<div class="status">${row.t_order_status}</div>
										<div class="date">${row.create_date}</div>
										<div class="button">
											${btn_cancel}
											<button type="button" class="detail-btn t_01">
                                                ${t_column[config.language]['t_01']}
                                            </button>
										</div>                                    
									</div>
								</div>					
							</li>
						`);
					});

                    div_product = document.querySelectorAll(`ul#${listId} li .image`);
                    if (div_product != null && div_product.length > 0) {
                        div_product.forEach(div => {
                            div.addEventListener('click',function(e) {
                                let el = e.currentTarget;

                                if (el.dataset.no != null) {
                                    location.href = `${config.base_url}/my/order/${el.dataset.no}`;
                                }
                            });
                        });
                    }
				} else {
					$(`ul#${listId}`).append(`
						<div class="list__none">
							${txt_none[config.language]}
						</div>
					`);
				}
				
				$('.cancel-btn').click(function() {
					let order_code = $(this).data('order_code');
					if (order_code != null) {
						location.href = `${config.base_url}/my/order-cancel?order_code=${order_code}`;
					}
				});

                $(`ul#${listId} .detail-btn`).click(function () {
                    let dataNo = $(this).closest('li').data('no');
                    window.location.href = `${config.base_url}/my/order/${dataNo}`
                })

                $(`ul#${listId} .cancel-btn`).click(function () {
                    let dataNo = $(this).closest('li').data('no');
                })

                if ('page' in d) {
                    paging({
                        total: d.total,
                        el: $(`#${listId}-paging`),
                        page: d.page,
                        rows: pageData.rows,
                        show_paging: 10,
                        fn: function (page) {
                            pageData.page = page
                            get_order_list(order_status);
                        }
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
                )
            }
        }
    })
}

function setDateButton() {
    $("ul.term button").click(function () {
        let buttonId = $(this).attr("id");
        let today = new Date();
        let fromDate = new Date();

        switch (buttonId) {
            case "one-week":
                fromDate.setDate(today.getDate() - 7);
                break;
            case "one-month":
                fromDate.setMonth(today.getMonth() - 1);
                break;
            case "three-month":
                fromDate.setMonth(today.getMonth() - 3);
                break;
            case "one-year":
                fromDate.setFullYear(today.getFullYear() - 1);
                break;
        }

        const formatDate = (date) => {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, "0");
            const day = String(date.getDate()).padStart(2, "0");
            return `${year}-${month}-${day}`;
        };

        $("#date_from").val(formatDate(fromDate));
        $("#date_from").attr('placeholder',formatDate(fromDate));

        $("#date_to").val(formatDate(today));
        $("#date_to").attr('placeholder',formatDate(today));
    });
}