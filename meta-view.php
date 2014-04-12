<div>
	<dl>
		<dt>価格</dt>
		<dd>
			<?php $mb->the_field('price'); ?>
			<input type="text" name="<?php $mb->the_name(); ?>"
				value="<?php $mb->the_value(); ?>" style="text-align: right" />
		</dd>
		<dt>価格表示ショートコード</dt>
		<dd>
			<input type="text" value="[<?php echo sm::SHORTCODE_PRICE; ?>]" readonly/>
		</dd>
	</dl>
</div>
