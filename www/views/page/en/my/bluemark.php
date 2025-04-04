<main class="my">
    <?php include '_summary.php'; ?>
	<nav>
		<ul>
			<li><a href="/en/my">Mypage</a></li>
			<li><a href="/en/my/bluemark">Bluemark</a></li>
		</ul>
	</nav>
	<section class="bluemark wrap-480">
		<h1>Bluemark</h1>
		<article>
			<p>
				Bluemark serves as a genuine product certification<br>
				to protect our brand awareness and valued customers from imitation products.
			</p>
			<p>
				ADER recognized the sale of counterfeit goods to<br>
				protect the image of consumers and brands We are actively responding.
			</p>
			
			<div class="tab">
				<div class="tab-container">
					<ul>
						<li>Verify</li>
						<li>History</li>
					</ul>
				</div>
				<section>
					<form id="frm-bluemark-regist">
						<div class="form-inline height45">
							<select class="store_no" name="store_no">
								<?php
									$select_purchase_mall_sql = "
										SELECT
											IDX				AS MALL_IDX,
											MALL_NAME		AS MALL_NAME
										FROM
											PURCHASE_MALL PM
										WHERE
											PM.COUNTRY = ?
									";
									
									$db->query($select_purchase_mall_sql,array("EN"));
									
									foreach($db->fetch() as $data) {
								?>
								<option value="<?=$data['MALL_IDX']?>"><?=$data['MALL_NAME']?></option>
								<?php
									}
								?>
							</select>
							
							<div class="control-label">Mall</div>
						</div>
						
						<div class="form-inline height45">
							<input class="bluemark" type="text" name="bluemark" placeholder="Enter the serial code in the blue cover" required>
							<div class="control-label">Bluemark serial number</div>
						</div>
						
						<div class="buttons">
							<button type="button" class="blue no-over">VERIFY</button>
						</div>
					</form>
				</section>
				<section>
					<ul class="list" id="list"></ul>
                    <div class="paging"></div>
				</section>
			</div>			
		</article>
	</section>
</main>