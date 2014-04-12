<div id='shop-menu'>
	<div id='shop-menu-list'>
		<?php foreach($info->items as $item): ?>
		<div class="shop-menu-item">
			<a href="<?php echo $item->url; ?>"<?php if ( $info->window_open ){ echo ' target="_blank"'; } ?>>
			<?php echo $item->img_tag; ?>
				<p class="shop-menu-name"><?php echo $item->title; ?></p>
				 <?php if (!empty($info->show_price)): ?>
				<p class="shop-menu-price">
					<?php echo $item->price;?>
				</p> <?php endif; ?>
			</a>
		</div>
		<?php endforeach; ?>
	</div>
	<?php if ( $info->has_next ): ?>
	<div id='next-menu-btn'>続きを見る</div>
	<div id='loader'><img src='<?php echo( plugins_url( "image/loader.gif", __FILE__ ));?>' /></div>
	<input type="hidden" id="shop-category" value="<?php echo $category ?>">
	<?php endif; ?>
</div>
