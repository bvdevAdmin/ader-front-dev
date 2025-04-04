<style>

.paying__form .info__wrap{
    padding-bottom:20px;
    border-bottom:1px solid #dcdcdc;
    margin-top:10px;
}
.charging__wrap{
    margin-top:40px;
    width:100%;
}
.charging__tab__btn__container{
    margin: 0 auto;
    width:230px;
    display:grid;
    gap:10px;
    place-items: center;
    grid-template-columns: 70px 70px 70px;
}
.charging__tab__wrap{
    width:710px;
    margin:0 auto;
    margin-top:50px;
}   
.charging__total__wrap{
    width:470px;
    margin:0 auto;
}
.charging__paying__wrap{
    width:470px;
    margin:0 auto;
}
.charging__complete__wrap{
    width:470px;
    margin:0 auto;
}
.charging__complete__title{
    font-size: 13px;
    font-weight: normal;
    font-stretch: normal;
    font-style: normal;
    line-height: 1.15;
    letter-spacing: normal;
    text-align: center;
    color: #343434;
    margin-bottom:30px
}
.charging__notice__wrap{
    width:470px;
    margin:0 auto;
}
.charging__notice__wrap .title{
    margin-bottom:30px;
}
.charging__tab__wrap .contents__table{
    border-top:none;
    border-bottom:none;
    margin-top:40px!important;
}
.contents__table.tab_charging_detail{
    margin-top:20px!important;
}
table.border__bottom td{
    border-bottom: 1px solid #dcdcdc;
}
table.border__bottom__th{
    text-align: left;
}
table.border__bottom__th th{
    border-bottom: 1px solid #dcdcdc;
}
.charging__tab__wrap .footer{
    margin-bottom:100px;
}
.charging__table tr{
    height:55px;
}
.charging__table tbody p{
    margin-bottom:0px;
}
.charging__table thead p{
    margin-bottom:0px;
}
.paying__form{
    margin-top:30px;
}
.description.tab__charging__total{
    margin-top:20px;
    margin-bottom:50px;
}
.description.tab__charging{
    margin-top:10px;
    margin-bottom:50px;
}
.title.tab__charging__complete{
    margin-top:100px;
}
.title.tab__charging__complete p{
    text-align:center;
}
.description.tab__charging__complete{
    margin-top:30px;
}
.description.tab__charging__complete p{
    text-align:center;
}
</style>
<div class="charging__wrap">
    <div class="charging__tab__btn__container">
        <div class="tab__btn__item"  form-id="charging__total__wrap">
            <span>충전하기</span>
        </div>
        <div class="tab__btn__item"  form-id="charging__detail__wrap">
            <span>충전내역</span>
        </div>
        <div class="tab__btn__item"  form-id="charging__notice__wrap">
            <span>유의사항</span>
        </div>
    </div>
    <div class="charging__tab__wrap">
        <div class="charging__tab charging__total__wrap">
            <div class="title">
                <p>충전포인트 현황</p>
            </div>
            <div class="contents__table">
                <table class="border__bottom__th">
                    <colsgroup>
                        <col style="width:130px;">
                        <col style="width:130px;">
                        <col style="width:130px;">
                        <col style="width:110px;">
                    </colsgroup>
                    <thead>
                        <th><p>총 누적포인트</p></th>
                        <th><p>사용된 포인트</p></th>
                        <th><p>잔여 포인트</p></th>
                        <th><p>환불예정 포인트</p></th>
                    </thead>
                    <tbody>
                        <td><p>233,000</p></td>
                        <td><p>- 100,000</p></td>
                        <td><p>133,000</p></td>
                        <td><p>0</p></td>
                    </tbody>
                </table>
            </div>
            <div class="description tab__charging__total">
                <p>·&nbsp;충전 포인트는 현금과 동일하게 사용 가능합니다.</p>
                <p>·&nbsp;충전 포인트 사용 시 적립 포인트 3% 추가 적립이 적용됩니다.</p>
            </div>
            <button class="black__full__width__btn" onclick="movePaymentPage()">충전하기</button>
            <div class="footer"></div>
        </div>

        <div class="charging__tab charging__paying__wrap">
            <div class="title">
                <p>포인트 충전하기</p>
            </div>
            <div class="contents__table">
                <table class="border__bottom__th">
                    <colsgroup>
                        <col style="width:130px;">
                        <col style="width:130px;">
                        <col style="width:130px;">
                        <col style="width:110px;">
                    </colsgroup>
                    <thead>
                        <th><p>총 누적포인트</p></th>
                        <th><p>사용된 포인트</p></th>
                        <th><p>잔여 포인트</p></th>
                        <th><p>환불예정 포인트</p></th>
                    </thead>
                    <tbody>
                        <td><p>233,000</p></td>
                        <td><p>- 100,000</p></td>
                        <td><p>133,000</p></td>
                        <td><p>0</p></td>
                    </tbody>
                </table>
            </div>
            <div class="paying__form">
                <div class="info__wrap">
                    <p>충전 금액 선택</p>
                    <label>
                        <input type="radio" name="charging_amount" value="10" checked />
                        <span>10만원</span>
                    </label>
                    <label>
                        <input type="radio" name="charging_amount" value="25" />
                        <span>25만원</span>
                    </label>
                    <label>
                        <input type="radio" name="charging_amount" value="50" />
                        <span>50만원</span>
                    </label>
                    <label>
                        <input type="radio" name="charging_amount" value="100" />
                        <span>100만원</span>
                    </label>
                    <label>
                        <input type="radio" name="charging_amount" value="200" />
                        <span>200만원</span>
                    </label>
                </div>
                
                <div class="info__wrap">
                    <p>결제 수단 선택</p>
                    <label>
                        <input type="radio" name="payment_method" value="account_transfer" checked />
                        <span>실시간 계좌이체</span>
                    </label>

                    <label>
                        <input type="radio" name="payment_method" value="credit_card" />
                        <span>신용카드</span>
                    </label>
                </div>
                
            </div>
            <div class="description tab__charging">
                <p>사용처 및 용도</p>
                <p>·&nbsp;충전 포인트는 현금과 동일하게 사용 가능합니다.</p>
                <p>·&nbsp;ADER 온라인 공식몰, 오프라인 플래그쉽</p>
                <p>·&nbsp;상품구매, 프리오더, 리오더 예약, 드로우 예약, 익스클루시브 할인</p>
                <div class="height__10px__blank" style="height:10px;"></div>
                <p>&nbsp;충전 포인트 혜택</p>
                <p>·&nbsp;충전 포인트로 결제 시 적립 포인트 3% 추가 지급</p>
                <p>·&nbsp;오픈런 우선구매 혜택</p>
                <div class="height__10px__blank" style="height:10px;"></div>
                <p>환불 방법</p>
                <p>·&nbsp;현금 지급에 한하여 환불 가능하며 5~7일 소요됩니다.</p>
                <div class="height__10px__blank" style="height:10px;"></div>
                <p>이용안내</p>
                <p>·&nbsp;현금영수증 자동발행</p>
            </div>
            <button class="black__full__width__btn" onclick="paymentAction()">결제하기</button>
            <div class="footer"></div>
        </div>

        <div class="charging__tab charging__complete__wrap">
            <div class="title tab__charging__complete">
                <p>포인트 충전이 완료되었습니다.</p>
            </div>
            <div class="description tab__charging__complete">
                <p>·&nbsp;상단의 충전내역 탭에서 충전 내역을 열람하실 수 있습니다.</p>
                <p>·&nbsp;조건없이 현금으로 환불 가능하며 5~7일 소요됩니다.</p>
            </div>
            <div class="footer"></div>
        </div>

        <div class="charging__tab charging__detail__wrap">
            <div class="title">
                <p>충전포인트 내역</p>
            </div>
            <div class="description tab_charging_detail">
                <p>·&nbsp;충전 포인트는 현금과 동일하게 사용 가능합니다.</p>
                <p>·&nbsp;충전 포인트 사용 시 적립 포인트 3% 추가 적립이 적용됩니다.</p>
            </div>
            
            <div class="contents__table tab_charging_detail">
                <table class="border__bottom border__bottom__th mileage__table">
                    <colsgroup>
                        <col style="width:120px;">
                        <col style="width:240px;">
                        <col style="width:120px;">
                        <col style="width:120px;">
                        <col style="width:120px;">
                    </colsgroup>
                    <thead>
                        <th>
                            <p>일자</p>
                        </th>
                        <th>
                            <p>내용</p>
                        </th>
                        <th>
                            <p>적립된 포인트</p>
                        </th>
                        <th>
                            <p>사용된 포인트</p>
                        </th>
                        <th>
                            <p>잔여 포인트</p>
                        </th>
                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <p>2022.12.13</p>
                            </td>
                            <td>
                                <p>구매 적립예정</p>
                            </td>
                            <td>
                                <p>+10,000</p>
                            </td>
                            <td>
                                <p>0</p>
                            </td>
                            <td>
                                <p>31,000</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>2022.12.10</p>
                            </td>
                            <td>
                                <p>주문사용</p>
                            </td>
                            <td>
                                <p>0</p>
                            </td>
                            <td>
                                <p>-5,000</p>
                            </td>
                            <td>
                                <p>21,000</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>2022.08.05</p>
                            </td>
                            <td>
                                <p>충전 즉시적립 이벤트</p>
                            </td>
                            <td>
                                <p>+3,000</p>
                            </td>
                            <td>
                                <p>0</p>
                            </td>
                            <td>
                                <p>26,000</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>2022.07.06</p>
                            </td>
                            <td>
                                <p>포인트 충전 (계좌 간편결제)</p>
                            </td>
                            <td>
                                <p>+10,000</p>
                            </td>
                            <td>
                                <p>0</p>
                            </td>
                            <td>
                                <p>23,000</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>2022.06.15</p>
                            </td>
                            <td>
                                <p>신규회원 적립금</p>
                            </td>
                            <td>
                                <p>+3,000</p>
                            </td>
                            <td>
                                <p>0</p>
                            </td>
                            <td>
                                <p>13,000</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>2022.12.13</p>
                            </td>
                            <td>
                                <p>구매 적립예정</p>
                            </td>
                            <td>
                                <p>+10,000</p>
                            </td>
                            <td>
                                <p>0</p>
                            </td>
                            <td>
                                <p>31,000</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>2022.12.10</p>
                            </td>
                            <td>
                                <p>주문사용</p>
                            </td>
                            <td>
                                <p>0</p>
                            </td>
                            <td>
                                <p>-5,000</p>
                            </td>
                            <td>
                                <p>21,000</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>2022.08.05</p>
                            </td>
                            <td>
                                <p>충전 즉시적립 이벤트</p>
                            </td>
                            <td>
                                <p>+3,000</p>
                            </td>
                            <td>
                                <p>0</p>
                            </td>
                            <td>
                                <p>26,000</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>2022.07.06</p>
                            </td>
                            <td>
                                <p>포인트 충전 (계좌 간편결제)</p>
                            </td>
                            <td>
                                <p>+10,000</p>
                            </td>
                            <td>
                                <p>0</p>
                            </td>
                            <td>
                                <p>23,000</p>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <p>2022.06.15</p>
                            </td>
                            <td>
                                <p>신규회원 적립금</p>
                            </td>
                            <td>
                                <p>+3,000</p>
                            </td>
                            <td>
                                <p>0</p>
                            </td>
                            <td>
                                <p>13,000</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="footer"></div>
        </div>
        <div class="charging__tab charging__notice__wrap">
            <div class='title'><p>충전포인트 유의사항</p></div>
            <div class='description tab__charing__notice'>
                <p>·&nbsp;충전 포인트는 현금과 동일하게 사용 가능합니다.</p>
                <p>·&nbsp;ADER 온라인 공식몰, 오프라인 플래그쉽</p>
                <p>·&nbsp;상품구매, 프리오더, 리오더 예약, 드로우 예약, 익스클루시브 할인</p>
                <p>·&nbsp;충전 포인트로 결제 시 적립 포인트 3% 추가 지급</p>
                <p>·&nbsp;오픈런 우선구매 혜택</p>
                <p>·&nbsp;현금 지급에 한하여 환불 가능하며 5~7일 소요됩니다.</p>
                <p>·&nbsp;현금영수증 자동발행</p>
            </div>
            <div class="footer"></div>
        </div>
    </div>
</div>
<script>
$('.charging__tab').hide();
$('.charging__total__wrap').show();

function movePaymentPage(){
    $('.charging__tab').hide();
    $('.charging__paying__wrap').show();
}
function paymentAction(){
    $('.charging__tab').hide();
    $('.charging__complete__wrap').show();
}
</script>