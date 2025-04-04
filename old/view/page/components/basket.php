<link rel="stylesheet" href="/css/common/basket.css">
<link rel="stylesheet" href="/css/module/foryou.css">
<main>
    <section class="basket__wrap">
        <div class="list__box">
            <div class="list__header">
                <div class="icon__box">
                    <img src="/images/svg/basket.svg" alt="">
                    <div data-i18n="s_shoppingbag">쇼핑백</div>
                </div>
                <div class="checkbox__box">
                    <label class="cb__custom all" for="">
                        <input class="prd__cb all__cb" type="checkbox" name="stock">
                        <div class="cb__mark"></div>
                    </label>
                    <div class="flex gap-10">
                        <u class="ufont st__checked__btn" btn="stock" data-i18n="s_remove_selected">선택 삭제</u>
                        <u class="ufont st__all__btn" btn="stock" data-i18n="s_remove_all">모두 삭제</u>
                    </div>            
                </div>
            </div>
            <div class="list__body"></div>
        </div>
        <div class="pay__box">
            <div class="pay__row">
                <div data-i18n="s_subtotal">제품합계</div>
                <div class="product__total__price">0</div>
            </div>
            <div class="pay__row">
                <div data-i18n="s_shipping_total">배송비</div>
                <div class="deli__price" data-price_delivery="5000">0</div>
            </div>
            <div class="pay__row">
                <div data-i18n="s_order_total">총 합계</div>
                <div class="pay__total__price">0</div>
            </div>
            <div class="pay__btn"><span data-i18n="s_checkout">결제하기</span></div>
            <p class="pay__notiy"></p> 
        </div>
    </section>
    <section class="recommend-wrap"></section>
</main>
