<style>
    .wishlist-wrap {
        margin-top: 50px;
        min-height: 20vh;
    }

    .menu__tab {
        margin: 0;
        width: 100%;
        height: fit-content;
    }

    @media (max-width: 1024px) {
        .wishlist-wrap {
            margin: 40px -10px 0;
        }

        .mypage__tab__container {
            margin-bottom: 0;
        }

    }
</style>

<div class="wishlist-wrap"></div>

<script>
    const wish = new WishlistRender();
</script>