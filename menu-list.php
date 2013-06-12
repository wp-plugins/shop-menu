<?php foreach($info->items as $item): ?>
<div class="shop-menu-item">
	<a href="<?php echo $item->url; ?>">
	<?php echo $item->img_tag; ?>
		<p class="shop-menu-name"><?php echo $item->title; ?></p>
		 <?php if (!empty($info->show_price)): ?>
		<p class="shop-menu-price">
			<?php echo $item->price;?>
		</p> <?php endif; ?>
	</a>
</div>
<?php endforeach; ?>
