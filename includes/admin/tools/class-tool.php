<?php

/**
 * @file
 * @reviewed 2.4.0
 */

namespace OES\Admin\Tools;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

use function OES\Admin\add_oes_notice_after_refresh;
use function OES\Admin\get_admin_note_html;

if (!class_exists('Tool')) :

    /**
     * Class Tool
     *
     * Base class for building admin tools in WordPress.
     * Provides a framework for form handling, postboxes, notices, and AJAX support.
     */
    class Tool
    {
        /**
         * Tool name (slug/identifier).
         *
         * @var string
         */
        public string $name = '';

        /**
         * Action name used in form submission.
         *
         * @var string
         */
        public string $action = '';

        /**
         * Whether to add an admin_post hook automatically.
         *
         * @var bool
         */
        public bool $add_action = true;

        /**
         * AJAX action name (optional).
         *
         * @var string
         */
        protected string $ajax_action = '';

        /**
         * Form action URL.
         *
         * @var string
         */
        public string $form_action = '';

        /**
         * Additional HTML parameters for the form element.
         *
         * @var string
         */
        public string $form_parameters = '';

        /**
         * Admin notice messages (to be displayed after redirect).
         *
         * Each message should be an array with keys: type, text, dismissible.
         *
         * @var array
         */
        public array $tool_messages = [];

        /**
         * Postbox configuration parameters.
         *
         * @var array
         */
        public array $postbox = [
            'name'     => '',
            'screen'   => 'oes-tools',
            'context'  => 'normal',
            'priority' => 'high',
        ];

        /**
         * Whether to redirect back after form submission.
         *
         * @var bool
         */
        public bool $redirect = true;

        /**
         * Admin notices to be shown immediately (not after refresh).
         *
         * @var array
         */
        public array $admin_notices = [];

        /**
         * Hidden form inputs.
         *
         * @var array
         */
        public array $hidden_inputs = [];

        /**
         * Tool constructor.
         *
         * @param string $name Tool name (identifier).
         * @param array  $args Optional parameters to configure the tool.
         */
        public function __construct(string $name, array $args = [])
        {
            $this->name = $name;

            $this->initialize_parameters($args);
            $this->additional_parameters($args);
            $this->validate_parameters();
            $this->register_hooks();
        }

        /**
         * Registers WordPress hooks (actions) for the tool.
         *
         * @return void
         */
        protected function register_hooks(): void
        {
            if (!empty($this->ajax_action)) {
                add_action('wp_ajax_' . $this->ajax_action, [$this, 'handle_ajax']);
            } elseif ($this->add_action) {
                add_action("admin_post_{$this->action}", [$this, 'admin_post']);
            }

            add_action('admin_notices', [$this, 'display_messages'], 10);
            add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);

            if (!empty($this->postbox['name'])) {
                $this->initialize_postbox();
            }
        }

        /**
         * Initialize base parameters (intended to be overridden).
         *
         * @param array $args Tool parameters.
         * @return void
         */
        protected function initialize_parameters(array $args = []): void
        {
        }

        /**
         * Initialize additional parameters (optional).
         *
         * @param array $args Additional tool parameters.
         * @return void
         */
        protected function additional_parameters(array $args = []): void
        {
        }

        /**
         * Validates and sets fallback parameters.
         *
         * @return void
         */
        protected function validate_parameters(): void
        {
            if (empty($this->name)) {
                $this->add_action = false;
            }

            if (empty($this->action)) {
                $this->action = $this->name;
            }

            if (empty($this->form_action)) {
                $this->form_action = esc_url(admin_url('admin-post.php'));
            }
        }

        /**
         * Registers a postbox for this tool in the admin screen.
         *
         * @return void
         */
        protected function initialize_postbox(): void
        {
            add_meta_box(
                'oes-tool-' . $this->name,
                esc_html($this->postbox['name'] ?: 'Postbox name missing'),
                [$this, 'render_form'],
                $this->postbox['screen'],
                $this->postbox['context'],
                $this->postbox['priority']
            );
        }

        /**
         * Renders the full tool form inside the postbox.
         *
         * @return void
         */
        public function display(): void
        {
            $redirect = urlencode($_SERVER['REQUEST_URI']);
            ?>
            <form action="<?php echo esc_url($this->form_action); ?>"
                  id="<?php echo esc_attr($this->name); ?>"
                  method="POST" <?php echo $this->form_parameters; ?>>
                <input type="hidden" name="action" value="<?php echo esc_attr($this->action); ?>">
                <?php wp_nonce_field($this->action, $this->name . '_nonce', false); ?>
                <input type="hidden" name="_wp_http_referer" value="<?php echo esc_attr($redirect); ?>">
                <?php
                $this->html();
                $this->render_hidden_inputs();
                ?>
            </form>
            <?php
        }

        /**
         * Displays admin messages stored in the tool.
         *
         * @return void
         */
        public function display_messages(): void
        {
            foreach ($this->tool_messages as $message) {
                $type = $message['type'] ?? 'info';
                $text = $message['text'] ?? '';
                $dismissible = $message['dismissible'] ?? true;

                if (!in_array($type, ['info', 'warning', 'error', 'success'], true)) {
                    $type = 'info';
                }

                if (!str_ends_with($text, '.') && !str_ends_with($text, '>')) {
                    $text .= '.';
                }

                add_oes_notice_after_refresh($text, $type, $dismissible);
            }
        }

        /**
         * Display the tool messages as admin notice.
         */
        function display_admin_notices(): void
        {
            foreach ($this->admin_notices as $notice) {
                get_admin_note_html($notice);
            }
        }

        /**
         * Handles form submission for admin_post_[action].
         *
         * @return void
         */
        public function admin_post(): void
        {
            if (
                empty($_POST[$this->name . '_nonce']) ||
                !wp_verify_nonce($_POST[$this->name . '_nonce'], $this->action)
            ) {
                wp_die(__('Security check failed.', 'oes'));
            }

            $this->validate_form_input_size();
            $this->admin_post_tool_action();

            if (empty($_POST['_wp_http_referer'])) {
                wp_die(__('Missing referer.', 'oes'));
            }

            if ($this->redirect) {
                wp_safe_redirect(urldecode($_POST['_wp_http_referer']));
            }

            exit;
        }

        /**
         * Validates that the number of submitted form fields does not exceed PHP's max_input_vars.
         *
         * @return void
         */
        protected function validate_form_input_size(): void
        {
            $max_input_vars = (int) ini_get('max_input_vars');
            $form_count = count($_POST, COUNT_RECURSIVE);

            if ($form_count > $max_input_vars) {
                add_oes_notice_after_refresh(
                    sprintf(
                        __('The number of submitted form variables (%1$s) exceeds your server limit (%2$s). This may cause incomplete data saving.', 'oes'),
                        $form_count,
                        $max_input_vars
                    ),
                    'error'
                );
            }
        }

        /**
         * Outputs hidden input fields as HTML.
         *
         * @return void
         */
        protected function render_hidden_inputs(): void
        {
            foreach ($this->hidden_inputs as $name => $value) {
                printf(
                    '<input type="hidden" name="%s" value="%s">',
                    esc_attr($name),
                    esc_attr($value)
                );
            }
        }

        /**
         * Outputs the custom form fields (main content).
         * Override in child classes.
         *
         * @return void
         */
        protected function html(): void
        {
        }

        /**
         * Enqueues admin scripts.
         * Override in child classes to add specific assets.
         *
         * @param string $hook Current admin page hook suffix.
         * @return void
         */
        public function enqueue_scripts(string $hook): void
        {
        }

        /**
         * Executes the tool’s specific logic after form submit.
         * Override in child classes.
         *
         * @return void
         */
        protected function admin_post_tool_action(): void
        {
        }

        /**
         * Handles AJAX requests.
         * Override in child classes.
         *
         * @return void
         */
        public function handle_ajax(): void
        {
        }
    }

endif;
