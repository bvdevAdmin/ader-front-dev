<link rel="stylesheet" href="/css/product/best-auto.css" type="text/css">

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
    <section class="best_auto_wrap" data-menu_idx="<?= $menu_idx ?>" data-menu_type="<?= $menu_type ?>"data-page_idx="<?= $page_idx ?>">
        <div class="best_auto_category">
            <div class="category_item"></div>
        </div>
        <div class="best_auto_body"></div>
    </section>
</main>

<script src="/scripts/product/best-auto.js"></script>