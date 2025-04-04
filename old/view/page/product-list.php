<link rel="stylesheet" href="/css/product/list.css" type="text/css">

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
    <input id="menu_type" type="hidden" value="<?= $menu_type ?>">
    <input id="menu_idx" type="hidden" value="<?= $menu_idx ?>">
    <input id="page_idx" type="hidden" value="<?= $page_idx ?>">

    <input id="img_param" type="hidden" value="O">
    <input id="more_flg" type="hidden" value="false">

    <section class="product__list__wrap" data-menu_type="<?=$menu_type?>" data-menu_idx="<?=$menu_idx?>" data-page_idx="<?=$page_idx?>" data-last_idx="0">
        <div class="top__banner"></div>
        <div class="prd__menu">
            <div class="prd__menu__grid"></div>
            <div class="prd__menu__sort">
                <div class="sort-title"></div>
                <div class="sort-wrap ">
                    <li class="sort-btn order-btn" id="order-btn-toggle">
                        <img src="/images/svg/sort-bottom.svg" alt="" class="oder-btn-motion">
                        <span data-i18n="pl_sort_filter"></span>
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
                        <span data-i18n="pl_filter"></span>
                    </li>
					
                    <li class="sort-btn type-btn">
                        <div class="d-i-b"><img src="/images/svg/cloth.svg" alt="" style="height:17px;">
                        </div>
                        <span id="img_type_text" data-i18n="pl_model_cut"></span>
                    </li>
					
                    <div class="sort-btn web rW sort__grid" data-grid="4">
                        <div class="d-i-b"><img src="/images/svg/grid-cols-2.svg" alt=""></div>
                        <span class="layout_change_btn" data-i18n="pl_change_layout_02"></span>
                    </div>
					
                    <div class="sort-btn mobile rM sort__grid" data-grid="3">
                        <div class="d-i-b"><img src="/images/svg/grid-cols-1.svg" alt=""></div>
                        <span class="layout_change_btn" data-i18n="pl_change_layout_01_m"></span>
                    </div>
                </div>
            </div>
            <div class="line"></div>
            <div class="sort-container">
                <ul class="sort-wrap hidden">
                    <li status="false">
                        <label class="cb__custom self" for="order_param_POP">
                            <input id="order_param_POP" class="sort__cb self__cb btn_product_sort" type="checkbox" name="order_param" value="POP">
                            <div class="cb__mark"></div>
                            <span class="sort-text" data-i18n="pl_trending"></span>
                        </label>
                    </li>
					
                    <li status="false">
                        <label class="cb__custom self" for="order_param_NEW">
                            <input id="order_param_NEW" class="sort__cb self__cb btn_product_sort" type="checkbox" name="order_param" value="NEW">
                            <div class="cb__mark"></div>
                            <span class="sort-text" data-i18n="pl_latest"></span>
                        </label>
                    </li>
					
                    <li status="false">
                        <label class="cb__custom self" for="order_param_MIN">
                            <input id="order_param_MIN" class="sort__cb self__cb btn_product_sort" type="checkbox" name="order_param" value="MIN">
                            <div class="cb__mark"></div>
                            <span class="sort-text" data-i18n="pl_high_price"></span>
                        </label>
                    </li>
					
                    <li status="false">
                        <label class="cb__custom self" for="order_param_MAX">
                            <input id="order_param_MAX" class="sort__cb self__cb btn_product_sort" type="checkbox" name="order_param" value="MAX">
                            <div class="cb__mark"></div>
                            <span class="sort-text" data-i18n="pl_low_price"></span>
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
                                <summary class="filter-lrg-title filter-color" data-i18n="pl_filter_color"></summary>
                                <div class="filter-btn-wrap"><span>[</span>
                                    <div class="mobile-filter-btn"></div><span>]</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="filter-wrapper filter-lrg">
                        <div class="filter-content fit">
                            <div class="mobile-btn--header">
                                <summary class="filter-lrg-title" data-i18n="pl_filter_fit"></summary>
                                <div class="filter-btn-wrap"><span>[</span>
                                    <div class="mobile-filter-btn"></div><span>]</span>
                                </div>
                            </div>
                        </div>
                        <div class="filter-content graphic">
                            <div class="mobile-btn--header">
                                <summary class="filter-lrg-title" data-i18n="pl_filter_graphic"></summary>
                                <div class="filter-btn-wrap"><span>[</span>
                                    <div class="mobile-filter-btn"></div><span>]</span>
                                </div>
                            </div>
                        </div>
                        <div class="filter-content line">
                            <div class="mobile-btn--header">
                                <summary class="filter-lrg-title" data-i18n="pl_filter_line"></summary>
                                <div class="filter-btn-wrap"><span>[</span>
                                    <div class="mobile-filter-btn"></div><span>]</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="filter-wrapper filter-lrg">
                        <div class="filter-content size">
                            <div class="mobile-btn--header">
                                <summary class="filter-lrg-title size-margin" data-i18n="pl_filter_size"></summary>
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
                        <div class="reset-btn" data-i18n="pl_clear"></div>
                        <div class="select-btn"><span data-i18n="pl_view_product_sort_01"></span><span
                                class="select-result">0</span><span data-i18n="pl_view_product_sort_02"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="product__list__body">
        </div>
    </section>
</main>

<script src="/scripts/product/list.js"></script>
<script src="/scripts/product/filter.js"></script>