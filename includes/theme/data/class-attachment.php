<?php

if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('OES_Attachment')) {

    /**
     * Class OES_Attachment
     *
     * This class prepares an attachment for display in the frontend theme.
     */
    class OES_Attachment extends OES_Object
    {

        /** @var string $attachment_type The attachment type */
        public string $attachment_type = 'file';

        //Overwrite parent
        public $post_type = 'media';


        //Overwrite parent
        public function set_parameters(): void
        {
            $this->set_title();

            /* get attachment type */
            global $post;
            foreach (['image', 'video', 'audio'] as $type)
                if (wp_attachment_is($type, $post ? get_post($this->object_ID) : $post))
                    $this->attachment_type = $type;
        }


        //Overwrite parent
        public function set_title(): void
        {
            $titleOption = OES()->media_groups['title'] ?? 'title';
            switch ($titleOption) {

                case 'title':
                    $this->title = get_the_title($this->object_ID);
                    break;

                case 'caption':
                    $this->title = wp_get_attachment_caption($this->object_ID);
                    break;

                case 'alt':
                    $this->title = get_post_meta($this->object_ID, '_wp_attachment_image_alt', TRUE);
                    break;

                default:
                    $this->title = oes_get_field($titleOption, $this->object_ID);
                    break;
            }
        }


        //Overwrite parent
        public function get_index_connected_posts(string $consideredPostType, string $postRelationship = ''): array
        {
            /* prepare data */
            $connectedPosts = [];

            /* get considered post type */
            if ($consideredPostType) {

                /* find all posts that contain the attachment URL in their content or the attachment ID in a comment */
                global $wpdb;
                
                /* get relative path of attachment */
                $relativePath = esc_url($wpdb->esc_like(wp_get_attachment_url($this->object_ID)));
                if (strpos($relativePath, '/wp-content/uploads/'))
                    $relativePath = substr($relativePath,
                        strpos($relativePath, '/wp-content/uploads/') + strlen('/wp-content/uploads/') + 1);

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

                /* prepare posts for display */
                foreach($collectPosts as $singlePost)
                    $connectedPosts[$consideredPostType][] = get_post($singlePost->post_id);
            }

            return $connectedPosts;
        }


        //Overwrite parent
        public function prepare_html_main_block(array $args = []): array
        {
            return $this->prepare_html_main_attachment();
        }


        //Overwrite parent
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
            switch ($this->attachment_type) {

                case 'image':
                    return $this->prepare_html_main_image();

                case 'audio':
                    return $this->prepare_html_main_audio();

                case 'video':
                    return $this->prepare_html_main_video();

                case 'file':
                    return $this->prepare_html_main_file();

                default:
                    return $this->prepare_html_main_other();
            }
        }


        /**
         * Prepare data for image.
         *
         * @return array Prepared data.
         */
        function prepare_html_main_image(): array {
            return ['200_content' => oes_get_image_panel_content(acf_get_attachment($this->object_ID))];
        }


        /**
         * Prepare data for audio.
         *
         * @return array Prepared data.
         */
        function prepare_html_main_audio(): array {
            return ['200_content' => $this->prepare_audio_html()];
        }


        /**
         * Prepare data for video.
         *
         * @return array Prepared data.
         */
        function prepare_html_main_video(): array {
            return ['200_content' => $this->prepare_video_html()];
        }


        /**
         * Prepare data for file.
         *
         * @return array Prepared data.
         */
        function prepare_html_main_file(): array {
            return ['200_content' => $this->prepare_file_html()];
        }


        /**
         * Prepare data for other attachment types.
         *
         * @return array Prepared data.
         */
        function prepare_html_main_other(): array {
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