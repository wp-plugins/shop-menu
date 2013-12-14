<div id='shop-menu'>
	<div id='shop-menu-list'>
		<?php include( dirname(__FILE__) . '/menu-list.php' ); ?>
	</div>
	<?php if ( $info->has_next ): ?>
	<div id='next-menu-btn'>続きを見る</div>
	<?php endif; ?>
</div>
