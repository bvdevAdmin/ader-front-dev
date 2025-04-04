<link rel=stylesheet href='/css/module/quickview.css' type='text/css'>
<div id="quickview" class="hidden">
    <div class="quickview__box">
        <input id="quickview_observer" type="hidden" />
        <div class="quickview__btn__wrap open">
            <div class="btn__box param_btn recent__btn" data-quick="recent">
                <div class="btn_icon_wrap recent_view">
                    <img src="/images/svg/wish-recent.svg" alt="">
                    <p>Recently<br>viewed</p>
                </div>
            </div>
            <div class="btn__box param_btn real__btn" data-quick="top">
                <div class="btn_icon_wrap">
                    <img src="/images/svg/wish-real.svg" alt="">
                    <p>Top</p>
                </div>
            </div>
            <div class="btn__box param_btn list__btn" data-quick="wish">
                <div class="btn_icon_wrap">
                    <img src="/images/svg/wish-list.svg" alt="">
                    <p>Wishlist</p>
                </div>
            </div>
            <div class="btn__box param_btn faq__btn" data-quick="qna">
                <div class="btn_icon_wrap">
                    <img src="/images/svg/wish-faq.svg" alt="">
                    <p>QnA</p>
                </div>
            </div>
        </div>
        <div class="quickview__content__wrap">
            <input type="hidden" id="sel_category_no" value="0">
            <input type="hidden" id="sel_category_title" value="">

            <div class="content-header">
                <div class="title__box">
                    <img src="" alt="">
                    <span></span>
                </div>
                <div class="title__box--btn">
                    <div class="all-btn show_all_mo" data-i18n="lm_view_all">+ 전체 보기</div>
                    <div id="quickview-close-btn" class="remove-btn close_contents">
                        <img src="/images/svg/sold-line.svg">
                        <img src="/images/svg/sold-line.svg">
                    </div>
                </div>
            </div>

            <div class="common-contents-container hidden"></div>
            <form id="frm-inquiry" method="post" action="_api/mypage/inquiry/add">
                <div class="hidden-input hidden">
                    <input type="hidden" id="country" name="country">
                    <input type="file" id="inquiry_img" name="inq_img[]">
                </div>
                <div class="contents-footer hidden">
                    <input type="hidden" id="inquiry_type" name="inquiry_type">
                    <input type="hidden" id="inquiry_title" name="inquiry_title">

                    <div class="file-upload-btn">
                        <img src="/images/svg/file_clip_btn.svg"
                            style="width:22px;height:22px;margin:5px auto;margin-left:11px">
                    </div>
                    <input type="text" id="inquiryTextBox" name="inquiryTextBox">

                    <div class="submit_btn add_qna_inq"><span data-i18n="q_confirm">확인</span></div>
                </div>
            </form>

            <div class="swiper-quick-container">
                <div class="quickview-wish-swiper"></div>
            </div>

            <div class="all-btn show_all_wb" data-i18n="lm_view_all"></div>
        </div>
    </div>
</div>
<script src="/scripts/module/quickview.js"></script>