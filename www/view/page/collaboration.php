<main class="collaboration">
    <?php 
    include 'inc/collaboration.nav.php'; 
	include 'collaboration/'.implode('-',array_splice($_CONFIG['M'],1)).'.php';
    ?>
	<button type="button" class="to-top"></button>
</main>