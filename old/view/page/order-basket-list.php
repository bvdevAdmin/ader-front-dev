<?php
	if (!isset($_SESSION['MEMBER_IDX'])) {
		echo "
			<script>
				location.href='/login';
			</script>
		";
	}
	
	include $_CONFIG['PATH']['PAGE'] . '/components/basket.php';	
?>
<style>main{overflow-x: initial;}</style>
<script>
	const basket = new BasketPage("basket-page");
	const foryou = new ForyouRender();
</script>