<link rel="stylesheet" href="/css/product/list_tmp.css" type="text/css">

<?php
function getUrlParamter($url, $sch_tag)
{
    $parts = parse_url($url);
    parse_str($parts['query'], $query);
    return $query[$sch_tag];
}

$page_url = $_SERVER['REQUEST_URI'];
$menu_type = getUrlParamter($page_url, 'menu_type');
$menu_idx = getUrlParamter($page_url, 'menu_idx');
$page_idx = getUrlParamter($page_url, 'page_idx');
?>
<main>
    <input id="img_param" type="hidden" value="O">

    <section class="product__list__wrap" data-menu_type="<?=$menu_type?>" data-menu_idx="<?=$menu_idx?>" data-page_idx="<?=$page_idx?>" data-last_idx="0">
        <div class="top__banner"></div>
        <div class="prd__menu">
            <div class="prd__menu__grid"></div>
            <div class="prd__menu__sort">
                <div class="sort-title">22FW 전체보기</div>
                <div class="sort-wrap ">
                    <li class="sort-btn order-btn" id="order-btn-toggle">
                        <img src="/images/svg/sort-bottom.svg" alt="" class="oder-btn-motion">
                        <span data-i18n="pl_sort_filter">정렬</span>
                    </li>
                    <li class="sort-btn filter-btn">
                        <div class="filter-motion-btn">
                            <div class="filter-line">
                                <div class="dot01"></div>
                            </div>
                            <div class="filter-line">
                                <div class="dot02"></div>
                            </div>
                            <div class="filter-line">
                                <div class="dot03"></div>
                            </div>
                        </div>
                        <span data-i18n="pl_filter">필터</span>
                    </li>
                    <li class="sort-btn type-btn" onClick="clickImgTypeBtn();">
                        <div class="d-i-b"><img src="/images/svg/cloth.svg" alt="" style="width:8px;height:17px;">
                        </div>
                        <span id="img_type_text" data-i18n="pl_model_cut">착용컷</span>
                    </li>
                    <div class="sort-btn web rW sort__grid" data-grid="4">
                        <div class="d-i-b"><img src="/images/svg/grid-cols-2.svg" alt=""></div>
                        <span class="layout_change_btn" data-i18n="pl_change_layout_02">2칸 보기</span>
                    </div>
                    <div class="sort-btn mobile rM sort__grid" data-grid="2">
                        <div class="d-i-b"><img src="/images/svg/grid-cols-3.svg" alt=""></div>
                        <span class="layout_change_btn" data-i18n="pl_change_layout_03_m">3칸</span>
                    </div>
                </div>
            </div>
            <div class="line"></div>
            <div class="sort-container">
                <ul class="sort-wrap">
                    <li status="false">
                        <label class="cb__custom self" for="order_param_POP">
                            <input id="order_param_POP" class="sort__cb self__cb" type="checkbox" name="order_param"
                                value="POP" onClick="sortProductList(this);">
                            <div class="cb__mark"></div>
                            <span class="sort-text" data-i18n="pl_trending">인기순</span>
                        </label>
                    </li>
                    <li status="false">
                        <label class="cb__custom self" for="order_param_NEW">
                            <input id="order_param_NEW" class="sort__cb self__cb" type="checkbox" name="order_param"
                                value="NEW" onClick="sortProductList(this);">
                            <div class="cb__mark"></div>
                            <span class="sort-text" data-i18n="pl_latest">신상품순</span>
                        </label>
                    </li>
                    <li status="false">
                        <label class="cb__custom self" for="order_param_MIN">
                            <input id="order_param_MIN" class="sort__cb self__cb" type="checkbox" name="order_param"
                                value="MIN" onClick="sortProductList(this);">
                            <div class="cb__mark"></div>
                            <span class="sort-text" data-i18n="pl_high_price">낮은 가격순</span>
                        </label>
                    </li>
                    <li status="false">
                        <label class="cb__custom self" for="order_param_MAX">
                            <input id="order_param_MAX" class="sort__cb self__cb" type="checkbox" name="order_param"
                                value="MAX" onClick="sortProductList(this);">
                            <div class="cb__mark"></div>
                            <span class="sort-text" data-i18n="pl_low_price">높은 가격순</span>
                        </label>
                    </li>
                </ul>
            </div>
            <div class="filter-container">
                <div class="filter-header">
                    <!-- 모바일 탑 들어갈자리 -->
                </div>
                <div class="filter-body">
                    <div class="filter-wrapper filter-lrg">
                        <div class="filter-content color">
                            <div class="mobile-btn--header">
                                <summary class="filter-lrg-title filter-color">색상</summary>
                                <div class="filter-btn-wrap"><span>[</span>
                                    <div class="mobile-filter-btn"></div><span>]</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="filter-wrapper filter-lrg">
                        <div class="filter-content fit">
                            <div class="mobile-btn--header">
                                <summary class="filter-lrg-title">핏</summary>
                                <div class="filter-btn-wrap"><span>[</span>
                                    <div class="mobile-filter-btn"></div><span>]</span>
                                </div>
                            </div>
                        </div>
                        <div class="filter-content graphic">
                            <div class="mobile-btn--header">
                                <summary class="filter-lrg-title">그래픽</summary>
                                <div class="filter-btn-wrap"><span>[</span>
                                    <div class="mobile-filter-btn"></div><span>]</span>
                                </div>
                            </div>
                        </div>
                        <div class="filter-content line">
                            <div class="mobile-btn--header">
                                <summary class="filter-lrg-title">라인</summary>
                                <div class="filter-btn-wrap"><span>[</span>
                                    <div class="mobile-filter-btn"></div><span>]</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="filter-wrapper filter-lrg">
                        <div class="filter-content size">
                            <div class="mobile-btn--header">
                                <summary class="filter-lrg-title size-margin">사이즈</summary>
                                <div class="filter-btn-wrap"><span>[</span>
                                    <div class="mobile-filter-btn"></div><span>]</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="filter-wrapper filter-lrg">

                    </div>
                </div>
                <div class="filter-footer">
                    <div class="filter-btn-wraaper">
                        <div class="reset-btn" data-i18n="pl_clear">초기화</div>
                        <div class="select-btn"><span data-i18n="pl_view_product_sort_01"></span><span
                                class="select-result">0</span><span data-i18n="pl_view_product_sort_02">개의 제품 선택</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="product__list__body">
        </div>
    </section>
</main>
<script src="/scripts/product/list_tmp.js"></script>