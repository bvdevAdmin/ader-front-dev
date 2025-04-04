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
    <section class="join-result-sction">
        <div class="join-wrap">
        </div>

    </section>
</main>
<script src="/scripts/standby/complete.js"></script>