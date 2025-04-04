<link rel="stylesheet" href="/css/standby/main.css" type="text/css">
<?php
function getUrlParamter($url, $sch_tag)
{
    $parts = parse_url($url);
    parse_str($parts['query'], $query);
    return $query[$sch_tag];
}

$page_url = $_SERVER['REQUEST_URI'];
$standby_idx = getUrlParamter($page_url, 'standby_idx');
?>
<main data-standby_idx="<?= $standby_idx ?>">
    <section class="standby-banner-wrap">
        <div class="banner-img"><img src="" alt=""></div>
        <div class="banner-countdown">STANDBY OPENS IN &nbsp;<span id="countdown"></span></div>
    </section>
    <section class="standby-list-wrap">
        <div class="standby_web_product_list product-wrap">

        </div>
        <div class="info-wrap">
            <div class="info info-product">
                <div class="info__box">
                    <div class="info-standby-title">STANDBY</div>
                    <div class="info-product-name">ADER Callio Tote Bag</div>
                    <p class="info-product-description standby_description"></p>
                    <div class="info-product-description">
                        <p data-i18n="sb_entry_info_01"></p>
                        <p data-i18n="sb_entry_info_02"></p>
                        <p data-i18n="sb_entry_info_03"></p>
                    </div>
                    <ul class="info-standby-date">
                    </ul>
                </div>
            </div>
            <div class="standby_mobile_product_list product-wrap">

            </div>
            <div class="info info-agreement">
                <div class="info__box agree">
                    <div class="agreement">
                        <div class="agreement_box">

                        </div>
                        <div class="control-group">
                            <input id="terms_TRUE" class="cb-radio" type="radio" name="terms" value="TRUE">
                            <label for="terms_TRUE" data-i18n="sb_agree">동의</label>

                            <input id="terms_FALSE" class="cb-radio" type="radio" name="terms" value="FALSE" checked>
                            <label for="terms_FALSE" data-i18n="sb_disagree">비동의</label>

                        </div>
                    </div>
                    <ul class="standby-notice">
                        <li data-i18n="sb_entry_notice_01"></li>
                        <li data-i18n="sb_entry_notice_02"></li>
                        <li data-i18n="sb_entry_notice_03"></li>
                        <li data-i18n="sb_entry_notice_04"></li>
                        <li data-i18n="sb_entry_notice_05"></li>
                        <li data-i18n="sb_entry_notice_06"></li>
                        <li data-i18n="sb_entry_notice_07"></li>
                        <li data-i18n="sb_entry_notice_08"></li>
                        <li data-i18n="sb_entry_notice_09"></li>
                    </ul>
                    <div class="standby-joinus-btn"><span data-i18n="sb_stand">참여하기</span></div>
                </div>
            </div>
        </div>
    </section>
</main>
<script src="/scripts/standby/main.js"></script>