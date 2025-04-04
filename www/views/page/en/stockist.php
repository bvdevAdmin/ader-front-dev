<section class="stockist modal">
    <div class="marker-card">
        <div class="swiper-container" id="store-slide">
            <div class="swiper-wrapper"></div>
            <button type="button" class="swiper-button-prev"></button>
            <button type="button" class="swiper-button-next"></button>
        </div>
        <div class="title">
            <span>Title Text</span>
            <a href="#" class="link">
            </a>
        </div>
        <div class="location">
            <span>Additional Info</span>
            <a href="#" class="location-link">
                Location
            </a>
        </div>
        <br>
        <div class="title">
            <span>Title Container</span>
        </div>
        <div class="date">
            <span>Date Info</span>
        </div>
        <a href="#" class="detail">View Details</a>
        <button class="close"></button>
    </div>
</section>

<main class="stockist fix-header">
    <section class="search-tab">
        <form id="frm" class="search">
            <input type="text" name="keyword" placeholder="Enter a keyword">
            <button type="button"></button>
            <input type="hidden" name="lat">
            <input type="hidden" name="lng">
            <button type="button" id="search-by-location" class="location">
                <img src="/images/ico-location.svg"><label>Search by current location</label>
            </button>
        </form>
    </section>
    
    <section class="tab">
        <div class="tab-container mobile">
            <ul>
                <li>Map</li>
                <li id="tab-list">List</li>
            </ul>
        </div>

        <section class="search">
            <div class="google-map">
                <div class="map" id="map"></div>
                <div class="zoom-button">
                    <div class="zoom-in"></div>
                    <div class="zoom-out"></div>
                </div>
            </div>
        </section>

        <section class="store-card">
            <h2>Brand Store</h2>

            <article class="space">
                <h3>Space</h3>
                <dl id="space-info"></dl>
            </article>

            <article class="plug">
                <h3>Plug Shop</h3>
                <dl id="plug-info"></dl>
            </article>

            <h2>Stockist</h2>
            <dl class="stockist" id="stockist-info"></dl>
        </section>
    </section>
</main>
