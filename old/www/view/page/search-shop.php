<link rel=stylesheet href='/css/store/main.css' type='text/css'>
<main>
    <div class="search-shop-wrap">
        <section class="store-section map">
            <div class="seacrh-header-wrap">
                <div class="search-header">
                    <img class="search-svg" src="/images/svg/search.svg" alt="">
                    <input id="store-search-input" type="search" data-i18n-placeholder="ss_search_location">
                    <div class="clear-btn">
                        <img src="/images/svg/reset.svg" alt="">
                        <span>clear</span>
                    </div>
                </div>
                <div class="my-place">
                    <img src="/images/svg/store-addr-bk.svg" alt="">
                    <p data-i18n="ss_current_location"></p>
                </div>
            </div>
            <div class="search-body">
                <div id="map"></div>
                <div id="web-detail-wrap"></div>
            </div>
        </section>
        <section class="store-section brand-store">
            <div class="store-header">
                <div class="store-title" data-i18n="ss_brand_store"></div>
                <div class="store-subtitle" data-i18n="ss_space"></div>
            </div>
            <div class="store-body"></div>
        </section>
        <section class="store-section plug-store">
            <div class="store-header">
                <div class="store-subtitle" data-i18n="ss_plug_shop"></div>
            </div>
            <div class="store-body"></div>
        </section>
        <section class="store-section stockist-store">
            <div class="store-header">
                <div class="store-subtitle" data-i18n="ss_stockist"></div>
            </div>
        </section>
        <div class="wechat_qr hidden"><img src="https://adererror.com/images/wechat-qrcode.png"></div>
        <section id="store-mobile-modal" class="store-section hidden"></section>
    </div>
</main>
<script src="/scripts/store/list.js"></script>
<script
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBvajMZZ6QFCTM5bawJl7Rktj7DdgE4h90&libraries=places&callback=initMap" async defer></script>