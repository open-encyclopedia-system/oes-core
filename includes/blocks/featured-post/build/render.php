<div <?php echo get_block_wrapper_attributes(); ?>><?php
	echo oes_get_featured_post_html(
			(!empty($attributes['oes_post']) ? get_post($attributes['oes_post']) : false), [
			'post_type' => $attributes['post_type'] ?? 'page'
	]); ?>
</div>