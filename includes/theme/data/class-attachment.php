<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('OES_Attachment', false)) {

    /**
     * Class OES_Attachment
     *
     * This class prepares an attachment for display in the frontend theme.
     */
    class OES_Attachment extends OES_Object
    {

        /** @var string $attachment_type The attachment type */
        public string $attachment_type = 'file';

        /** @inheritdoc */
        public $post_type = 'media';

        /** @inheritdoc */
        public function set_parameters(): void
        {
            $this->set_title();

            global $post;
            foreach (['image', 'video', 'audio'] as $type) {
                if (wp_attachment_is($type, $post ? get_post($this->object_ID) : $post)) {
                    $this->attachment_type = $type;
                }
            }

            $this->language = $this->get_language();
        }

        /** @inheritdoc */
        public function set_title(): void
        {
            $titleOption = OES()->media_groups['title'] ?? 'title';
            $this->title = match ($titleOption) {
                'title' => get_the_title($this->object_ID),
                'caption' => wp_get_attachment_caption($this->object_ID),
                'alt' => get_post_meta($this->object_ID, '_wp_attachment_image_alt', TRUE),
                default => oes_get_field($titleOption, $this->object_ID),
            };
        }

        /** @inheritdoc */
        public function get_index_connected_posts(string $consideredPostType, string $postRelationship = ''): array
        {
            if (!$consideredPostType) {
                return [];
            }

            global $wpdb;

            $relativePath = esc_url($wpdb->esc_like(wp_get_attachment_url($this->object_ID)));
            $position = strpos($relativePath, '/wp-content/uploads/');

            if ($position) {
                $offset = $position + strlen('/wp-content/uploads/') + 1;
                $relativePath = substr($relativePath, $offset);
            }

            $collectPosts = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT DISTINCT post_id FROM {$wpdb->prefix}postmeta 
                                WHERE post_id <> %d and 
                                    meta_value LIKE '%%%s%%'
                                OR post_id IN (
                                    SELECT ID FROM {$wpdb->prefix}posts
                                    WHERE (post_content LIKE '%%%s%%' or post_content like '%%\"figure\":%d%%')                                    
                                    AND post_type = '{$consideredPostType}'
                                )",
                    $this->object_ID,
                    $relativePath,
                    $relativePath,
                    $this->object_ID,
                )
            );

            $connectedPosts = [];

            foreach ($collectPosts as $singlePost) {
                $connectedPosts[$consideredPostType][] = get_post($singlePost->post_id);
            }

            return $connectedPosts;
        }

        /** @inheritdoc */
        public function prepare_html_main_block(array $args = []): array
        {
            return $this->prepare_html_main_attachment();
        }

        /** @inheritdoc */
        public function prepare_html_main_classic(array $args = []): array
        {
            return $this->prepare_html_main_attachment();
        }

        /**
         * Prepare data according to type.
         *
         * @return array Prepared data.
         */
        function prepare_html_main_attachment(): array
        {
            return match ($this->attachment_type) {
                'image' => $this->prepare_html_main_image(),
                'audio' => $this->prepare_html_main_audio(),
                'video' => $this->prepare_html_main_video(),
                'file' => $this->prepare_html_main_file(),
                default => $this->prepare_html_main_other(),
            };
        }

        /**
         * Prepare data for image.
         *
         * @return array Prepared data.
         */
        function prepare_html_main_image(): array
        {
            return ['200_content' => oes_get_image_panel_content(acf_get_attachment($this->object_ID))];
        }

        /**
         * Prepare data for audio.
         *
         * @return array Prepared data.
         */
        function prepare_html_main_audio(): array
        {
            return ['200_content' => $this->prepare_audio_html()];
        }

        /**
         * Prepare data for video.
         *
         * @return array Prepared data.
         */
        function prepare_html_main_video(): array
        {
            return ['200_content' => $this->prepare_video_html()];
        }

        /**
         * Prepare data for file.
         *
         * @return array Prepared data.
         */
        function prepare_html_main_file(): array
        {
            return ['200_content' => $this->prepare_file_html()];
        }

        /**
         * Prepare data for other attachment types.
         *
         * @return array Prepared data.
         */
        function prepare_html_main_other(): array
        {
            return ['200_content' => $this->prepare_file_html()];
        }

        /**
         * Prepare html display of a video.
         * @oesDevelopment
         * @return string
         */
        function prepare_video_html(): string
        {
            return '
<!-- wp:group {"align":"full","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull">

    <!-- wp:video {"align":"wide"} -->
    <figure class="wp-block-video alignwide">
        <video controls muted src="' . esc_url(wp_get_attachment_url($this->object_ID)) . '"></video>
    </figure>
    <!-- /wp:video -->

</div>
<!-- /wp:group -->';
        }

        /**
         * Prepare html display of audio.
         * @oesDevelopment
         * @return string
         */
        function prepare_audio_html(): string
        {
            return '
<!-- wp:group {"align":"full","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull">

    <!-- wp:audio {"id":' . absint($this->object_ID) . '} -->
    <figure class="wp-block-audio">
        <audio controls src="' . esc_url(wp_get_attachment_url($this->object_ID)) . '"></audio>
    </figure>
    <!-- /wp:audio -->

</div>
<!-- /wp:group -->';
        }

        /**
         * Prepare html display of file.
         * @oesDevelopment
         * @return string
         */
        function prepare_file_html(): string
        {
            $url = wp_get_attachment_url($this->object_ID);
            return '
<!-- wp:group {"align":"full","layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull">

    <!-- wp:file {"id":' . absint($this->object_ID) . ',"href":"' . esc_url($url) . '"} -->
    <div class="wp-block-file">
        <a href="' . esc_url($url) . '">' . $this->title . '</a>
        <a href="' . esc_url($url) . '" class="wp-block-file__button wp-element-button" download>' .
                esc_html__('Download', 'x3p0-ideas') . '</a>
    </div>
    <!-- /wp:file -->

</div>
<!-- /wp:group -->';
        }
    }
}