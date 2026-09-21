<?php
/**
 * @var array    $attributes
 * @var string   $content
 * @var WP_Block $block
 */

$wrapper_attributes = get_block_wrapper_attributes();
?>
<div <?php echo $wrapper_attributes; ?>>
    <?php echo $content; ?>
</div>