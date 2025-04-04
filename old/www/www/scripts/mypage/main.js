var swiperMypage = '';
var searchEnterKeyHandler = function (e) {
    if (e.keyCode == '13') {
        $('.search__icon__img').click();
    }
}
$(document).ready(function () {
    let iconItem = document.querySelectorAll(".icon__item");

    iconItem.forEach(item => item.addEventListener("click", function () {
        let btnType = item.getAttribute("btn-type");
        mypageTabBtnClick(btnType, 0);
    }));

    swiperMypage = new Swiper(".swiper.icon", {
        //옵션은 유동적으로 필요한부분만 추가해서 사용가능,
        navigation: {
            nextEl: ".swiper.icon .swiper-button-next",
            prevEl: ".swiper.icon .swiper-button-prev",
        },
        pagination: {
            el: ".swiper.icon .swiper-pagination",
            clickable: true,
        },
        autoHeight: true,
        grabCursor: true,
        slidesPerView: 'auto',
        loop: false,
        loopAdditionalSlides: 1
    });

    swiperTabBtn = new Swiper(".swiper.tab__btn", {
        //옵션은 유동적으로 필요한부분만 추가해서 사용가능,
        navigation: {
            nextEl: ".swiper.tab__btn .swiper-button-next",
            prevEl: ".swiper.tab__btn .swiper-button-prev",
        },
        pagination: {
            el: ".swiper.tab__btn .swiper-pagination",
            clickable: true,
        },
        autoHeight: true,
        grabCursor: true,
        slidesPerView: 'auto',
        loop: false,
        loopAdditionalSlides: 1,
        spaceBetween: 10,
        observer: true,
        obseveParents: true
    });

    $(".tab__btn__item").on('click', function () {
        var ancestorObj = $(this).parents('.menu__tab');
        var btn_parents = ancestorObj.children().eq(1).children().eq(0);
        var swiper_btn_parents = ancestorObj.children().eq(1).children().eq(1);

        var btn_group = btn_parents.find('.tab__btn__item');
        var swiper_btn_group = swiper_btn_parents.find('.tab__btn__item');

        /* new */
        ancestorObj.find('.tab__btn__item').removeClass('selected');
        btn_group.eq($(this).index()).addClass('selected');
        swiper_btn_group.eq($(this).index()).addClass('selected');
        /* old */
        /*
        var btn_length = btn_group.length;
        var old_src = '';
        var sel_old_src = '';

        for(var i = 0; i < btn_length; i++){
            var default_src = '';
            old_src = btn_group.eq(i).children().attr('src');
            default_src = old_src.replace('select','default');
            btn_group.eq(i).children().attr('src', default_src);
            swiper_btn_group.eq(i).children().attr('src', default_src);
        }
        var select_src = '';
        sel_old_src = $(this).children().attr('src');
        select_src = sel_old_src.replace('default','select');

        btn_group.eq($(this).index()).children().attr('src', select_src);
        swiper_btn_group.eq($(this).index()).children().attr('src', select_src);
        */
        /*  */

        var tab_class = $('#btn_type').val() + '__tab';
        var form_id = $(this).attr('form-id');
        if (form_id != '') {
            $('.' + tab_class).hide();
            $('.' + form_id).show();
        }
        ancestorObj.find('input[type="password"]').val('');
        ancestorObj.find('input[type="text"]').val('');
        ancestorObj.find('.select-items.select-hide').hide();
        ancestorObj.find('.toggle__item .question .down__up__icon').attr('src', '/images/mypage/mypage_down_tab_btn.svg');
        $('.mypage__tab__container input[name="page"]').val(1);
        $('.request').hide();
    });

    $('.swiper-slide.icon__item').on('click', function () {
        swiperMypage.slideTo($(this).index() - 1);
    });

    $.ajax({
        type: "post",
        data: {
            "country": getLanguage()
        },
        dataType: "json",
        url: api_location + "mypage/get",
        error: function (d) {
        },
        success: function (d) {
            if (d.code == 302) {
                window.location.replace("/login");
            }
            else {
                var data = d.data[0];
                var balance = data.mileage_balance_total;
                if (balance == null) {
                    balance = 0;
                }

                $('#mileage_value').text(`${balance}P`);
                $('#order_value').text(`${data.order_ing_cnt == null ? 0 : data.order_ing_cnt}`);
                $('#voucher_cnt').text(`${data.voucher_cnt}`);
                $('#mypage_member_name').text(`${data.member_name}`);
                $('#mypage_member_id').text(`${data.member_id}`);
                $('html').scrollTop(0);
            }
        }
    });

    var mypage_type = $('#mypage_type').val();
    if (mypage_type != null && mypage_type.length > 0) {
        switch (mypage_type) {
            case 'bluemark_verify':
                mypageTabBtnClick('bluemark', 0);
                break;

            case 'bluemark_list':
                mypageTabBtnClick('bluemark', 1);
                break;

            case 'orderlist':
                mypageTabBtnClick('orderlist', 0);
                break;

            case 'orderlist_cancel':
                mypageTabBtnClick('orderlist', 1);
                break;

            case 'mileage_first':
                mypageTabBtnClick('mileage', 0);
                break;

            case 'voucher_first':
                mypageTabBtnClick('voucher', 0);
                break;
            case 'stanby_first':
                mypageTabBtnClick('stanby', 0);
                break;
            case 'stanby_second':
                mypageTabBtnClick('stanby', 1);
                break;
            case 'preorder_first':
                mypageTabBtnClick('preorder', 0);
                break;

            case 'reorder_first':
                mypageTabBtnClick('reorder', 0);
                break;

            case 'draw_first':
                mypageTabBtnClick('draw', 0);
                break;

            case 'membership_first':
                mypageTabBtnClick('membership', 0);
                break;

            case 'inquiry_first':
                mypageTabBtnClick('inquiry', 0);
                break;

            case 'inquiry':
                mypageTabBtnClick('inquiry', 1);
                break;

            case 'inquiry_list':   
                mypageTabBtnClick('inquiry', 2);
                break;
            case 'as_first':
                mypageTabBtnClick('as', 0);
                break;

            case 'as_third':
                mypageTabBtnClick('as', 2);
                break;

            case 'as_bluemark':
                mypageTabBtnClick('as', 1);
                break;

            case 'service_first':
                mypageTabBtnClick('service', 0);
                break;

            case 'profile_first':
                mypageTabBtnClick('profile', 0);
                break;
        }
    }
});

function mypageTabBtnClick(type, tab_idx) {
    $('#btn_type').val(type);

    $('.menu__tab').addClass('non__display__tab');
    $('#mypage_tab_' + type).removeClass('non__display__tab');
    $('.click__icon__item').removeClass('click__icon__item');

    $('.icon__item[btn-type=' + type + ']').addClass('click__icon__item');
    swiperMypage.slideTo($(`.icon__item[btn-type='${type}']`).index() - 1);
    $(`.${type}__wrap`).find('.tab__btn__item').eq(tab_idx).click();
    if (type == "as") {
        if (tab_idx == 2) {
            $('.as_tab').eq(2).addClass('on');
        } else {
            $('.as_tab').eq(0).addClass('on');
        }
    }

    if (type == "bluemark") {
        $(".web.bluemark__btn.side-bar").addClass("open");
    }
    else {
        $(".web.bluemark__btn.side-bar").removeClass("open");
    }

    if (type == "orderlist") {
        getOrderInfoList('ALL');
        getOrderInfoList('OCC');
        getOrderInfoList('OEX');
        getOrderInfoList('ORF');
        addShowDetailEvent();
        clickSetToggle();
    }
    let country = getLanguage();
    if(type == "profile"){
        if(country !== 'KR'){
            $('#mypage_tab_profile .tab__btn__item').eq(1).css('display','none');
        }
    }
    changeLanguageR();
}

function makeSelect(divId) {
    changeLanguageR();

    var selectDiv = $('.' + divId);
    selectDiv.css('position', 'relative');
    var SelLen = selectDiv.find('select option').length;

    var selectedDiv = ` <div class="select-selected">${selectDiv.find('select option:selected').text()}</div><img src="/images/mypage/mypage_down_tab_btn.svg" style="width:10px;height:5px;position: absolute;right:10px;top:18px;">`;
    selectDiv.append(selectedDiv);

    var selectHideDiv = `<div class="select-items select-hide">`;
    for (var i = 0; i < SelLen; i++) {
        selectHideDiv += `  
                        <div>${selectDiv.find(`select option:eq(${i})`).text()}</div>
                    `;
    }
    selectHideDiv += `  </div>`;
    selectDiv.append(selectHideDiv);

    selectDiv.find('.select-items').find('div').on('click', function () {
        var clickCountryText = $(this).text();

        var sameCountryOption = selectDiv.find(`select option:contains("${clickCountryText}")`);
        sameCountryOption.prop('selected', true);

        selectDiv.find('.select-selected').text(clickCountryText);

        selectDiv.find('.select-items').toggle();

        if ($(this).parent().parent().attr('class') == 'inquiry__category') {
            var category_no = $('#inq_cate').val();
            getFaqList('click', category_no);
            $('.category__small').find('.faq__category__btn[category-no=' + category_no + ']').addClass('click__btn');
        }
    });

    selectDiv.find('.select-selected').on('click', function () {
        selectDiv.find('.select-items').toggle();
    });
}

const foryou = new ForyouRender();

