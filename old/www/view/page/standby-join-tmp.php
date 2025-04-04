<!-- <link rel=stylesheet href='/css/standby/join.css' type='text/css'> -->

<style>
    .join-result-sction {
        width: 100%;
        position: relative;
        height: 85vh;
    }

    .join-result-sction .join-wrap {
        display: flex;
        gap: 40px;
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        flex-direction: column;
    }

    .standby-join-title {
        font-size: 13px;
        font-weight: normal;
        font-stretch: normal;
        font-style: normal;
        line-height: normal;
        letter-spacing: normal;
        text-align: left;
        color: #343434;
        margin-bottom: 10px;
    }

    .standby-join-subtitle {
        font-size: 20px;
        font-weight: 500;
        font-stretch: normal;
        font-style: normal;
        line-height: normal;
        letter-spacing: 0.6px;
        text-align: left;
        color: #343434;
        margin-bottom: 30px;
    }

    .standby-join-noti1 {
        font-size: 11px;
        font-weight: normal;
        font-stretch: normal;
        font-style: normal;
        line-height: normal;
        letter-spacing: normal;
        text-align: left;
        color: #343434;
        margin-bottom: 20px;
    }

    .standby-join-noti2 {
        font-size: 11px;
        font-weight: normal;
        font-stretch: normal;
        font-style: normal;
        line-height: normal;
        letter-spacing: normal;
        text-align: left;
        color: #343434;

    }

    .join-btn-wrap {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .join-btn-wrap .join--btn {
        border-radius: 2px;
        cursor: pointer;
        width: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        height: 40px;
        border-radius: 1px;
        border: solid 1px #808080;
    }

    @media (max-width: 1025px) {
        .join-result-sction .join-wrap{
            width: 90%;
        }
        .standby-join-title {
            font-size: 12px;
           
            margin-bottom: 10px;
        }

        .standby-join-subtitle {
            font-size: 16px;
            
            margin-bottom: 30px;
        }

        .standby-join-noti1 {
            font-size: 11px;
            
            margin-bottom: 20px;
        }

        .standby-join-noti2 {
            font-size: 11px;
           
            color: #343434;

        }
    }
</style>
<main>
    <section class="join-result-sction">
        <div class="join-wrap">
            <div class="join-title-wrap">
                <div class="standby-join-title">STANDBY</div>
                <div class="standby-join-subtitle">ADER Callio Tote Bag</div>
                <div class="standby-join-noti1">참여가 완료되었습니다.</div>
                <div class="standby-join-noti2"> 구매 링크는 당첨자에 한 해 LMS로 발송되며 기간 내 링크를 통해 구매 가능합니다.</div>
            </div>
            <div class="join-btn-wrap">
                <div class="join--btn my">
                    <span>나의 STANDBY 참여 내역 보러가기</span>
                </div>
                <a href="/standby/list">
                    <div class="join--btn">
                        <span>HOME으로 돌아가기</span>
                    </div>
                </a>
            </div>
        </div>

    </section>
</main>
<!-- <script src="/scripts/standby/join.js"></script> -->