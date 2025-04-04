<header>
    <section class="notice">
        <button type="button" class="prev"></button>
        <button type="button" class="next"></button>
        <button type="button" class="close EN"></button>
        <article class="noti">
            <p>Notice of CS and Delivery System Update</p>
            <a href="/en/notice/">Learn More</a>
        </article>
    </section>
    <nav>
        <a href="/en"><h1>ADERERROR</h1></a>
        <dl class="gnb" id="gnb"></dl>
        <dl class="tnb" id="tnb">
            <dt><a class="side" data-side="story">Story</a></dt>
            <dd id="tnb-story">
                <dl id="tnb-story-new">
                    <dt>New</dt>
                </dl>
                <dl id="tnb-story-archive">
                    <dt>Archive</dt>
                </dl>
            </dd>
            <dt><a href="/en/stockist">Store</a></dt>
            <dt><a class="side" data-side="my">Login</a><a href="/en/my">Mypage</a></dt>
            <dt><a class="side" data-side="bluemark"><span class="text">Bluemark</span><span class="over"><span class="text">Bluemark</span></span></a></dt>
            <dt><a class="side" data-side="search">Search</a></dt>
            <dt><a class="side" data-side="language">EN</a></dt>
            <dt><a class="side" href="/en/my/wishlist">Wishlist</a></dt>
            <dt><a class="side" data-side="shoppingbag">Shopping bag</a></dt>
        </dl>
        <ul class="tnb-mobile mobile" id="tnb-mobile">
            <li>
                <?php if (isset($_SESSION['MEMBER_IDX'])): ?>
                    <a href="/en/my" class="my">Mypage</a>
                <?php else: ?>
                    <a class="my" data-side="my">Login</a>
                <?php endif; ?>
            </li>
            <li><a class="bluemark" data-side="bluemark">Bluemark</a></li>
            <li><a class="customer" data-side="customer">Customer</a></li>
            <li><a class="language" data-side="language">Language</a></li>
        </ul>
        <button type="button" id="btn-gnb">
            <div class="icon"><span></span><span></span><span></span><span></span></div>
            <span class="text"></span>
        </button>
    </nav>
    <aside>
        <section class="story">
            <button type="button" class="close EN"></button>
            <article>
                <dl class="fold" id="tnb-story-new-m">
                    <dt>New</dt>
                </dl>
                <dl class="fold" id="tnb-story-archive-m">
                    <dt>Archive</dt>
                </dl>
            </article>
        </section>
        <section class="bluemark">
            <button type="button" class="close EN"></button>
            <article>
                <h2>Bluemark</h2>
                <p>
                    Bluemark serves as a genuine product certification<br>
                    to protect our brand awareness<br>
                    and valued customers<br>
                    from imitation products.
                    <br><br>
                    ADER recognized the sale of counterfeit goods<br>
                    to protect the image of consumers and brands.<br>
                    We are actively responding.
                </p>
                <div class="buttons">
                    <a href="/en/my/bluemark?type=regist" class="btn certify no-over">Verify Bluemark</a>
                    <a href="/en/my/bluemark?type=list" class="btn no-over">Verification History</a>
                </div>
            </article>
        </section>
        <section class="language">
            <button type="button" class="close EN"></button>
            <article>
                <h2>Choose Language</h2>
                <p>
                    Please select below.<br>
                    You will be redirected to the website supported with your chosen language.
                </p>
                <div class="buttons">
                    <a href="/kr" class="btn off" data-country="KR">Korean</a>
                    <a href="/en" class="btn" data-country="EN">English</a>
                </div>
            </article>
        </section>
        <section class="search">
            <button type="button" class="close EN"></button>
            <article id="tnb-search">
                <form id="frm-side-search" class="search">
                    <input type="text" name="keyword" placeholder="Enter search keyword">
                    <button type="button">Clear</button>
                </form>
                <section class="intro">
                    <h2>Recommended Search</h2>
                    <ul id="search-recommend-keyword" class="recommend"></ul>
                    <h2>Real-time Popular Products</h2>
                    <ul id="search-recommend-goods" class="hot"></ul>
                </section>
                <section class="result hidden">
                    <h2>Search Results</h2>
                    <ul id="search-result" class="hot"></ul>
                    <a href="/search" class="btn">View All Search Results</a>
                </section>
            </article>
        </section>
        <section class="shoppingbag">
            <article>
                <form id="frm-side-cart">
                    <h2>Shopping Bag</h2>
                    <button type="button" class="close EN"></button>
                    <div class="info-box no-login">
                        <div class="login">
                            <p>You can use it after logging in.</p>
                            <button type="button" class="btn black ready-login">Login</button>
                        </div>
                        <div class="join">
                            <p>Become a Blue member and enjoy our latest updates and events.</p>
                            <a href="/en/join" class="btn">Create Account</a>
                        </div>
                    </div>
                    <div class="info-box empty on">
                        <p>Your shopping bag is empty.</p>
                        <button type="button" id="btn-tnb-cart-continue" class="btn black">Keep Shopping</button>
                    </div>
                    <dl class="cart">
                        <dt>
                            <div class="msg"><span id="side-cart-num"></span> items selected.</div>
                            <div class="right">
                                <button type="button" id="btn-cart-select-delete" class="select-delete">Delete</button>
                                <label><input type="checkbox" name="all_check"><i></i></label>
                            </div>
                        </dt>
                        <dd>
                            <ul id="cart-list"></ul>
                        </dd>
                    </dl>
                    <footer>
                        <dl>
                            <dt>Subtotal</dt>
                            <dd id="frm-side-cart-total-goods">0</dd>
                            <dt>Customer Total</dt>
                            <dd id="frm-side-cart-total-discount">0</dd>
                            <dt>Shipping Total</dt>
                            <dd id="frm-side-cart-delivery">0</dd>
                            <dt>Order Total</dt>
                            <dd id="frm-side-cart-total">0</dd>
                        </dl>
                        <div class="buttons">
                            <button type="submit" class="no-over pay">Checkout</button>
                        </div>
                    </footer>
                </form>
            </article>
        </section>
        <section class="my">
            <article class="login">
                <section>
                    <h2>Login</h2>
                    <button type="button" class="close EN"></button>
                    <form id="frm-side-login">
                        <input type="hidden" name="r_url" value="">
                        <div class="form-inline inline-label">
                            <input type="email" name="member_id" placeholder=" " data-msg1="Please enter your email." data-msg2="Please enter your email properly." required>
                            <span class="control-label">E-mail</span>
                        </div>
                        <div class="form-inline inline-label">
                            <button type="button" class="pw-view-toggle"></button>
                            <input type="password" name="member_pw" placeholder=" " data-msg1="Please enter your password." required>
                            <span class="control-label">Password</span>
                        </div>
                        <span class="result-msg" id="side-login-result"></span>
                        <button type="submit" class="btn login black">Login</button>
                        <div class="rows">
                            <div class="left">
                                <label>
                                    <input type="checkbox" name="save_id" value="y">
                                    <i></i>
                                    Save
                                </label>
                            </div>
                            <div class="right">
                                <a href="/en/find-account?type=id">Find E-mail</a>
                                |
                                <a href="/en/find-account?type=pw">Find Password</a>
                            </div>
                        </div>
                    </form>
                    <div class="sns-login">
                        <p>Login with SNS Account</p>
                        <ul>
                            <li><button type="button" class="login-kakao" id="btn-login-kakao">Kakao Login</button></li>
                            <li><button type="button" class="login-naver" id="btn-login-naver">Naver Login</button></li>
                            <li><button type="button" class="login-google" id="btn-login-google">Google Login</button></li>
                        </ul>
                    </div>
                    <hr />
                    <div class="join">
                        <p>Become a Blue member and enjoy our latest updates and events.</p>
                        <a href="/en/join" class="btn">Create Account</a>
                    </div>
                </section>
                <section class="customer">
                    <h2>Customer</h2>
                    <a href="/en/notice" class="btn">Notice</a>
                    <a href="/en/faq" class="btn">FAQ</a>
                    <a href="/en/my/customer/qna" class="btn">1:1 Inquiry</a>
                </section>
            </article>
        </section>
    </aside>
</header>
<aside id="quick">
    <ul>
        <li class="recently-viewed">
            <button type="button" class="quick" id="btn-quick-recently-viewed"></button>
            <section>
                <button type="button" class="close EN"></button>
                <header>Recently Viewed</header>
                <article>
                    <div class="tab">
                        <div class="tab-container">
                            <ul id="quick-tabs">
                                <li class="recently on"></li>
                                <li class="popular"></li>
                                <li class="wishlist"></li>
                            </ul>
                        </div>
                        <section class="recently on">
                            <ul class="list" id="quick-recently-list">
                                <li class="empty">Recently viewed is empty.</li>
                            </ul>
                            <footer>
                                <?php if (isset($_SESSION['MEMBER_IDX'])): ?>
                                    <a href="/en/recently" class="btn">Go to Recently Viewed</a>
                                <?php else: ?>
                                    <a href="/en/login" class="btn">Go to Recently Viewed</a>
                                <?php endif; ?>
                            </footer>
                        </section>
                        <section class="popular">
                            <ul class="list" id="quick-popular-list">
                                <li class="empty">Real-time popular products are empty.</li>
                            </ul>
                            <footer>
                                <a href="/en/best" class="btn">Go to Real-time Popular Products</a>
                            </footer>
                        </section>
                        <section class="wishlist">
                            <ul class="list" id="quick-wishlist-list">
                                <li class="empty">Your wishlist is empty.</li>
                            </ul>
                            <footer>
                                <a href="/en/my/wishlist" class="btn">Go to Wishlist</a>
                            </footer>
                        </section>
                    </div>
                </article>
            </section>
        </li>
        <li class="qna">
            <button type="button" class="quick"></button>
            <section id="quick-qna">            
                <button type="button" class="close EN"></button>
                <header><i></i>QnA</header>
                <article id="quick-qna-category" class="category">
                    <p>Search a word for your inquiry</p>
                    <ul></ul>
                </article>
                <article id="quick-qna-chat" class="chat">
                    <ul></ul>
                </article>
                <footer>
                    <button type="button" class="btn">Create Inquiry</button>
                </footer>
            </section>
        </li>
    </ul>
</aside>
<section class="cookie-agree">
    <article class="banner">
        <button type="button" class="close EN"></button>
        <p>We use cookies and similar technologies to enhance site navigation, analyze usage, and assist in marketing efforts. By continuing to use ADERERROR's online store, you consent to these terms.</p>
        <div class="buttons">
            <button type="button" class="config">Accept Cookie</button>
            <button type="button" class="black accept-all">Accept All</button>
        </div>
    </article>
    <article class="accept">
        <section class="cont">
            <header>
                Cookie Settings
                <button type="button" class="close EN"></button>
            </header>
            <article>
                <form id="frm-cookie-accept">
                    <ul>
                        <li>
                            <label class="check"><input type="checkbox" name="necessary" value="y" checked><i></i>Essential Cookies</label>
                            <p>
                                Technical cookies are essential for the website to function correctly or to use requested services and content. You can set your browser to block or notify you about these cookies, but some parts of the site may not work properly as a result.
                            </p>
                        </li>
                        <li>
                            <label class="check"><input type="checkbox" name="general" value="y"><i></i>Preferences</label>
                            <p>
                                Functional cookies allow the website to provide enhanced features and personalized services based on your preferences (e.g., language or selected products). Without these cookies, certain or all services may not function properly.
                            </p>
                        </li>
                        <li>
                            <label class="check"><input type="checkbox" name="stat" value="y"><i></i>Statistics</label>
                            <p>
                                Statistical cookies collect information about how visitors interact with the website, helping improve user experience. Without these, we lose insights to enhance functionality.
                            </p>
                        </li>
                        <li>
                            <label class="check"><input type="checkbox" name="marketing" value="y"><i></i>Marketing</label>
                            <p>
                                Marketing cookies are used to create user profiles for targeted promotions. They rely on unique identification of your browser and internet device. Without these, the relevance of advertisements may decrease.
                            </p>
                        </li>
                    </ul>
                    <div class="buttons">
                        <button type="button">Accept</button>
                        <button type="submit" class="accept-all black">Accept All</button>
                    </div>
                </form>
            </article>
        </section>
    </article>
</section>
<footer>
    <section>
        <article class="about">
            <h2>About ADERERROR</h2>
            <p>
                ADER was founded in 2014 and is a brand based on fashion and expresses cultural communication.<br>
                The brand slogan 'but near missed things' implies our philosophy that we focus on the expression of any things that we missed in everyday.<br>
                We re-edit pictures, videos, space, design, art, and objects in our way to suggest a new cultural experience.<br>
                <br>
                ADER pursues designing communication between all areas as our essential brand value.
            </p>
        </article>
		<article class="rules">
			<h2>Regulations</h2>
			<ul>
				<li><a href="/en/online-store-guide">Online store guide</a></li>
				<li><a href="/en/terms-of-use">Terms and Conditions</a></li>
				<li><a href="/en/privacy-policy">Privacy Policy</a></li>
				<li><a href="/en/cookie-policy">Cookie Policy</a></li>
			</ul>
		</article>
		<article class="social">
			<h2>Social media</h2>
			<ul>
				<li><a href="http://pf.kakao.com/_mQzRxl" target="_blank"><img src="/images/sns/kakao.svg"></a></li>
				<li><a href="https://www.facebook.com/adererror" target="_blank"><img src="/images/sns/facebook.svg"></a></li>
				<li><a href="http://instagram.com/ader_error" target="_blank"><img src="/images/sns/instagram.svg"></a></li>
				<li><a href="https://goo.gl/s1b2mN" target="_blank"><img src="/images/sns/youtube.svg"></a></li>
				<li><a href="http://weibo.com/aderofficial" target="_blank"><img src="/images/sns/weibo.svg"></a></li>
				<li><a href="https://pinterest.com/adererror/" target="_blank"><img src="/images/sns/pinterest.svg"></a></li>
				<li><a href="https://goo.gl/MUkFap" target="_blank"><img src="/images/sns/vimeo.svg"></a></li>
				<li><a href="/"><img src="/images/sns/wechat.svg"></a></li>
			</ul>
		</article>
		<article class="customer-center">
			<h2>Customer</h2>
			<address>ADER 3F 53, Yeonmujang-gil, <br>Seongdong-gu, Seoul, Korea</address>
			<p class="tel">TEL. 02-792-2232</p>
			<p class="hour">Office hour Mon - Fri AM 10:00 - PM 5:00</p>
		</article>
		<article class="company">
			<h2>Company</h2>
			<ul>
				<li>Company | ADER</li>
				<li>Business Name | FIVE SPACE CO.,LTD</li>
				<li>Business License | 760-87-01757</li>
				<li>Mail-order License No. | 제 2021-서울성동-01588호</li>
				<li>CEO | HANN</li>
				<li>OFFICE | ADER 3F 53, Yeonmujang-gil, Seongdong-gu, Seoul, Korea</li>
			</ul>
		</article>
	</section>
	<p class="copyright">© ADERERROR 2024</p>
</footer>

</body>
</html>