<link rel="stylesheet" href="/css/mypage/inquiry.css">

<div class="inquiry__wrap">
    <div class="tab__btn__container">
        <div class="tab__btn__item" form-id="inquiry__faq__wrap">
            <span data-i18n="lm_faq">자주 찾는 질문</span>
        </div>
        <div class="tab__btn__item" form-id="inquiry__action__wrap">
            <span data-i18n="lm_inquiry">문의하기</span>
        </div>
        <div class="tab__btn__item" form-id="inquiry__list__wrap">
            <span data-i18n="inquiry_history">문의내역</span>
        </div>
    </div>

    <div class="inquiry__tab__wrap">
        <div class="inquiry__tab inquiry__faq__wrap">
            <div class="search">
                <input class="search__keyword" type="text" placeholder="무엇을 도와드릴까요?"
                    data-i18n-placeholder="inquiry_search">
                <img class="search__icon__img" src="/images/mypage/mypage_search_icon.svg">
            </div>
            <div class="category"></div>
            <div class="footer"></div>
        </div>

        <div class="inquiry__tab inquiry__faq__detail__wrap">
            <div class="inquiry__faq__detail__container">
                <div class="inquiry__faq__detail__area">
                    <div class="search__small">
                        <input class="search__keyword" type="text" data-i18n-placeholder="inquiry_search">

                        <img class="search__icon__img" src="/images/mypage/mypage_search_icon.svg">

                        <div class="close init_keyword hidden">
                            <img src="/images/mypage/tmp_img/X-12.svg" />
                        </div>
                    </div>

                    <div class="pc__view">
                        <div class="category__small"></div>
                    </div>

                    <div class="mobile__view">
                        <div class="category__small__mobile">
                            <div class="inquiry__category" style="width:100%;position:relative">
                                <select id="inq_cate"></select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="inquiry__faq__detail__area">
                    <div class="toggle__list">
                        <div class="toggle__list__tab 02"></div>
                    </div>
                </div>
            </div>
            <div class="footer"></div>
        </div>

        <div class="inquiry__tab inquiry__action__wrap">
            <form id="frm-inquiry" method="post" action="_api/mypage/inquiry/add">
                <input id="country" name="country" type="hidden">
                <div class="hidden board__image__wrap">
                    <input type="hidden" class="img_index">
                    <input type="file" class="board__image" name="inq_img[]">
                    <input type="file" class="board__image" name="inq_img[]">
                    <input type="file" class="board__image" name="inq_img[]">
                    <input type="file" class="board__image" name="inq_img[]">
                    <input type="file" class="board__image" name="inq_img[]">
                </div>

                <div class="title">
                    <p data-i18n="inquiry_1">1:1 문의</p>
                </div>
                <div class="inquiry__info inquiry__title">
                    <span>
                        <p class="title" data-i18n="inquiry_type">문의유형</p>
                        <div class="inquiry__type">
                            <select class="inquiry_type" name="inquiry_type">
                                <option name="inquiry_type" value="CAR">취소/환불</option>
                                <option name="inquiry_type" value="OAP">주문/결제</option>
                                <option name="inquiry_type" value="FAD">출고/배송</option>
                                <option name="inquiry_type" value="RAE">반품/교환</option>
                                <option name="inquiry_type" value="AFS">A/S</option>
                                <option name="inquiry_type" value="DAE">배송/기타문의</option>
                                <option name="inquiry_type" value="RST">재입고</option>
                                <option name="inquiry_type" value="PIQ">제품문의</option>
                                <option name="inquiry_type" value="BGP">블루마크/정가품</option>
                                <option name="inquiry_type" value="VUC">바우처</option>
                                <option name="inquiry_type" value="OSV">기타서비스</option>
                            </select>
                        </div>
                    </span>
                    <span style="width:100%;">
                        <p class="title" data-i18n="inquiry_title">제목</p>
                        <input class="inquiry_title" name="inquiry_title" data-i18n-placeholder="inquiry_type_title">
                    </span>
                </div>

                <div class="inquiry__info inquiry__contents">
                    <textarea data-i18n-placeholder="inquiry_type_inquiry" class="inquiryTextBox" name="inquiryTextBox"
                        type="text"></textarea>
                </div>
                <div class="inquiry__info inquiry__photo">
                    <p class="description" data-i18n="inquiry_attachment_photo">사진 첨부</p>
                    <div class="inquiry__photo__container">
                        <div class="inquiry__photo__item" data-img_idx="0">
                            <img src="/images/mypage/mypage_photo_btn.svg">
                        </div>

                        <div class="inquiry__photo__item" data-img_idx="1">
                            <img src="/images/mypage/mypage_photo_btn.svg">
                        </div>

                        <div class="inquiry__photo__item" data-img_idx="2">
                            <img src="/images/mypage/mypage_photo_btn.svg">
                        </div>

                        <div class="inquiry__photo__item" data-img_idx="3">
                            <img src="/images/mypage/mypage_photo_btn.svg">
                        </div>

                        <div class="inquiry__photo__item" data-img_idx="4">
                            <img src="/images/mypage/mypage_photo_btn.svg">
                        </div>
                    </div>

                    <div class="description">
                        <p data-i18n="inquiry_attchment_info_01">·&nbsp;제품 불량 및 오 배송의 경우, 수령하신 제품의 상태와 배송 패키지 사진을 등록
                            부탁드립니다.</p>
                        <p data-i18n="inquiry_attchment_info_02" style="margin-top: 10px;">·&nbsp;파일은 jpg, jpeg, png와
                            gif 형식만 업로드가 가능하며, 용량은 개당 10MB이하 최대
                            5개까지만 가능합니다.</p>
                    </div>
                </div>

                <div style="border-top:1px solid #dcdcdc;padding-top:20px;"></div>

                <button class="black__full__width__btn inquiry" data-i18n="inquiry_save">등록</button>
                <button class="white__full__width__btn" data-i18n="o_cancel">취소</button>
            </form>

            <iframe name="registIfr" style="display:none;"></iframe>
            <div class="footer"></div>
        </div>

        <div class="inquiry__tab inquiry__list__wrap">
            <div class="title">
                <p data-i18n="inquiry_my_history">나의 문의내역</p>
            </div>
            <div class="description">
                <p data-i18n="inquiry_cs_time">
                    고객센터 운영시간 월-금 / AM 9:30 - PM 1:00, PM 2:00 - PM 5:00
                </p>

                <p class="oneline_text" data-i18n="inquiry_cs_info_01">
                    매월 15일 (공휴일인 경우 직전 영업일)은 당사의 CS 및 배송 시스템 점검일입니다.
                </p>

                <p class="text_margin_left oneline_text" data-i18n="inquiry_cs_info_02">
                    보다 나은 서비스를 제공하기 위하여 위 점검일에는 CS 및
                    배송 업무가 중단됩니다.
                </p>

                <p class="text_margin_left" data-i18n="inquiry_cs_info_03">
                    고객 여러분들의 양해를 부탁드립니다. 오프라인 스토어는 정상 운영됩니다.
                </p>

                <p data-i18n="inquiry_cs_answered">
                    답변이 완료된nquery 문의내역은 수정이 불가능합니다.
                </p>
            </div>

            <div class="toggle__list inquiry__list">
                <div class="toggle__list__tab"></div>
            </div>



            <div class="inquiry_edit_wrap">
                <form id="frm-edit-inquiry" method="post" action="_api/mypage/inquiry/put">
                    <input type="hidden" name="country">
                    <input type="hidden" class="board_idx" name="board_idx">
                    <div class="hidden board__image__wrap">
                        <input type="hidden" class="img_index">
                        <input type="file" class="board__image" name="inq_img[]">
                        <input type="file" class="board__image" name="inq_img[]">
                        <input type="file" class="board__image" name="inq_img[]">
                        <input type="file" class="board__image" name="inq_img[]">
                        <input type="file" class="board__image" name="inq_img[]">
                    </div>
                    <div class="modal_title">
                        <p data-i18n="inquiry_edit">1:1 문의 수정</p>
                        <div class="close close_inq_edit">
                            <img src="/images/mypage/tmp_img/X-12.svg">
                        </div>
                    </div>
                    <div class="inquiry__info inquiry__title">
                        <span>
                            <p class="title" data-i18n="inquiry_type">문의유형</p>
                            <div class="edit__inquiry__type">
                                <select class="inquiry_type" name="inquiry_type">
                                    <option value="CAR" selected>취소/환불</option>
                                    <option value="OAP">주문/결제</option>
                                    <option value="FAD">출고/배송</option>
                                    <option value="RAE">반품/교환</option>
                                    <option value="AFS">A/S</option>
                                    <option value="DAE">배송/기타문의</option>
                                    <option value="RST">재입고</option>
                                    <option value="PIQ">제품문의</option>
                                    <option value="BGP">블루마크/정가품</option>
                                    <option value="VUC">바우처</option>
                                    <option value="OSV">기타서비스</option>
                                </select>
                            </div>
                        </span>
                        <span style="width:100%;">
                            <p class="title" data-i18n="inquiry_title">제목</p>
                            <input class="inquiry_title" name="inquiry_title"
                                data-i18n-placeholder="inquiry_type_title">
                        </span>
                    </div>

                    <div class="inquiry__info inquiry__contents">
                        <textarea data-i18n-placeholder="inquiry_type_inquiry" name="inquiryTextBox"
                            class="inquiryTextBox" type="text"></textarea>
                    </div>
                    <div class="inquiry__info inquiry__photo">
                        <p class="description" data-i18n="inquiry_attachment_photo">사진 첨부</p>
                        <div class="inquiry__photo__container">
                            <div class="inquiry__photo__item" data-img_idx="0">
                                <input type="hidden" class="priview_location" name="priview_location[]">
                                <img src="/images/mypage/mypage_photo_btn.svg">
                            </div>

                            <div class="inquiry__photo__item" data-img_idx="1">
                                <input type="hidden" class="priview_location" name="priview_location[]">
                                <img src="/images/mypage/mypage_photo_btn.svg">
                            </div>

                            <div class="inquiry__photo__item" data-img_idx="2">
                                <input type="hidden" class="priview_location" name="priview_location[]">
                                <img src="/images/mypage/mypage_photo_btn.svg">
                            </div>

                            <div class="inquiry__photo__item" data-img_idx="3">
                                <input type="hidden" class="priview_location" name="priview_location[]">
                                <img src="/images/mypage/mypage_photo_btn.svg">
                            </div>

                            <div class="inquiry__photo__item" data-img_idx="4">
                                <input type="hidden" class="priview_location" name="priview_location[]">
                                <img src="/images/mypage/mypage_photo_btn.svg">
                            </div>
                        </div>

                        <div class="description">
                            <p data-i18n="inquiry_attchment_info_01">·&nbsp;제품 불량 및 오 배송의 경우, 수령하신 제품의 상태와 배송 패키지 사진을 등록
                                부탁드립니다.</p>
                            <p data-i18n="inquiry_attchment_info_02" style="margin-top: 10px;">·&nbsp;파일은 jpg, jpeg,
                                png와 gif 형식만 업로드가 가능하며, 용량은 개당 10MB이하 최대
                                5개까지만 가능합니다.</p>
                        </div>
                    </div>

                    <div style="border-top:1px solid #dcdcdc;padding-top:20px;"></div>

                    <button class="black__full__width__btn inquiry" data-i18n="p_edit">수정</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="/scripts/mypage/inquiry.js"></script>