const detail_wrap = document.querySelector(`.editorial-detail-wrap`);
const page_idx = getUrlParamValue('page_idx');
const size_type = getUrlParamValue('size_type');

window.addEventListener('DOMContentLoaded', function () {
    console.log('editorialDetail Loaded');
    editorialClickEvent(page_idx, size_type);
    appendSlide();
    divFadeIn(detail_wrap, 0.01, 16, 150);
    scrollTop();
    detailBackBtnClickEvent();
})
function appendSlide() {
    $('.editorial-detail-wrap').addClass('open');
}

function editorialClickEvent(page_idx, size_type) {
    let styleWrap = document.querySelector('.styling-with-wrap');
    
    $.ajax({
        type: "post",
        data: {
            'page_idx': page_idx,
            'size_type': size_type
        },
        dataType: "json",
        url: api_location + "posting/editorial/get",
        error: function () {
            // alert("에디토리얼 리스트 불러오기 실패했습니다.");
            makeMsgNoti(getLanguage(), "MSG_F_ERR_0105", null);
        },
        success: function (d) {
            if (d != null) {
                if (d.code == 200) {
                    let editorial_info = d.data.editorial_info;
                    let editorial_product = d.data.product_info;
                    if (editorial_info.length > 0) {
                        editorial_info.forEach(function (editorial_row) {
                            let contents_info = editorial_row.contents_info[0];
                            let thumbs_img_tag = `
                                <div class="swiper-slide">
                                    <img src="${cdn_img + editorial_row.img_location}" alt="">
                                </div>
                            `;
                            $('.editorial-controller-swiper.swiper .swiper-wrapper').append(thumbs_img_tag);

                            let contents_tag = `
                                <div class="swiper-slide">
                            `;
                            if (contents_info.contents_type == 'VID') {
                                contents_tag += `
                                    <figure class="vplayer">
                                        <video controls="" autoplay="" muted="" loop="" >
                                            <source src="${cdn_vid + contents_info.contents_url}" type="video/mp4">
                                        </video>
                                    </figure>
                                `;
                            }
                            else {
                                contents_tag += `
                                    <img src="${cdn_img + contents_info.contents_url}" alt="">
                                `;
                            }
                            contents_tag += `
                                </div>
                            `;
                            $('.editorial-preview-swiper.swiper .swiper-wrapper').append(contents_tag);
                        })

                    }
                    if (editorial_product.length > 0) {
                        const styling = new StylingRender(editorial_product);
                        styleProductFadeIn(styleWrap, 500);
                    }
                    editorialDetailSwiper();
                    videoFomating();
                    let vctrbox = new Vctrbox(".vplayer");
                }
                else {
                    alert(d.msg);
                }
            }
            else {
                // alert("에디토리얼 데이터가 존재하지 않습니다.");
                makeMsgNoti(getLanguage(), "MSG_F_ERR_0106", null);
            }
        }
    });
}
function detailBackBtnClickEvent(){
    $('.editorial-detail-wrap .back-btn').on('click', function(){
        if(document.referrer == '/posting/editorial'){
            history.back();
        }
        else{
            location.href = '/posting/editorial';
        }
    })
}
function editorialDetailSwiper() {
    var editorial_ControllerSwiper = new Swiper(".editorial-controller-swiper", {
        spaceBetween: 10,
        slidesPerView: 16.5,
        freeMode: true,
        watchSlidesProgress: true,
        autoHeight: true,
        breakpoints: {
            320: {
                slidesPerView: 8.2,
            },
            480: {
                slidesPerView: 9.2
            },
            640: {
                slidesPerView: 10.2
            },
            740: {
                slidesPerView: 11.2
            },
            840: {
                slidesPerView: 12.2
            },
            940: {
                slidesPerView: 14.2
            }
        },
        pagination: {
            el: '.controller-swiper-wrap .swiper-pagination',
            type: 'fraction'
        },
    });
    var editorial_PreviewSwiper = new Swiper(".editorial-preview-swiper", {
        slidesPerView: 1,
        thumbs: {
            swiper: editorial_ControllerSwiper,
        },
        navigation: {
            nextEl: ".preview-swiper-wrap .swiper-button-next",
            prevEl: ".preview-swiper-wrap .swiper-button-prev",
        },
        pagination: {
            el: '.preview-swiper-wrap .swiper-pagination',
            type: 'fraction'
        },
        on: {
            afterInit: function () {
            },
            imagesReady: function () {
            }
        }
    });
}

function styleProductFadeIn(styleWrap, timeoutInterval){
    setTimeout(function(){
        elementFadeIn(styleWrap, 0.01, 16);
    }, timeoutInterval);
}