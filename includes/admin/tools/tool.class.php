<?php

namespace OES\Admin\Tools;

use function OES\Admin\add_oes_notice_after_refresh;


if (!defined('ABSPATH')) exit; // Exit if accessed directly

if (!class_exists('Tool')) :

    /**
     * Class Tool
     *
     * A class to register and display tools.
     */
    class Tool
    {

        /** @var string The tool name. */
        public string $name = '';

        /** @var string The request action for a form on the page. */
        public string $action = '';

        /** @var bool Boolean indicating if tool has added action after form submit */
        public bool $add_action = true;

        /** @var string Action name for the form. */
        public string $form_action = '';

        /** @var string Further form parameters. */
        public string $form_parameters = '';

        /** @var array Tool messages */
        public array $tool_messages = [];

        /** @var array Postbox parameters */
        public array $postbox = [
            'name' => '',
            'screen' => 'oes-tools',
            'context' => 'normal',
            'priority' => 'high'
        ];

        /** @var bool Flag indicating if redirect after tool action is required. */
        public bool $redirect = true;


        /**
         * Tool constructor.
         *
         * @param string $name The tool name.
         * @param array $args Additional parameters.
         */
        function __construct(string $name, array $args = [])
        {

            /* Set tool name */
            $this->name = $name;

            /* Set and validate further parameters */
            $this->initialize_parameters($args);
            $this->validate_parameters();

            /* Add action behaviour */
            if ($this->add_action) add_action("admin_post_$this->action", [$this, 'admin_post']);

            /* Call admin notices */
            add_action('admin_notices', [$this, 'display_messages'], 99);

            /* Initialize postboxes */
            if ($this->postbox['name']) $this->initialize_postbox();
        }


        /**
         * Initialize class parameters
         *
         * @param array $args Additional parameters.
         */
        function initialize_parameters(array $args = [])
        {
        }


        /**
         * Validate class parameters
         */
        function validate_parameters()
        {
            if (!$this->name) $this->add_action = false;
            if (!$this->action) $this->action = $this->name;
        }


        /**
         * Initialize postbox
         */
        function initialize_postbox()
        {
            add_meta_box('oes-tool-' . $this->name,
                empty($this->postbox['name']) ? 'Postbox name missing' : $this->postbox['name'],
                [$this, 'display_tool'],
                $this->postbox['screen'],
                $this->postbox['context'],
                $this->postbox['priority']
            );
        }


        /**
         * Display the tool interface as a form.
         */
        function display_tool()
        {

            /* redirect form to current page */
            $redirect = urlencode($_SERVER['REQUEST_URI']);

            /*
            Create form
               - add action input to link to specific tool,
               - create nonce for security,
               - redirect to current page,
               - call form parameters from specific tool.
            */
            ?>
            <form action="<?php echo $this->form_action; ?>" id="<?php echo $this->name ?>"
                  method="POST"<?php echo $this->form_parameters; ?>>
                <input type="hidden" name="action" value="<?php echo $this->action; ?>">
                <?php wp_nonce_field($this->action, $this->name . '_nonce', FALSE); ?>
                <input type="hidden" name="_wp_http_referer" value="<?php echo $redirect; ?>">
                <?php
                $this->html();
                ?>
            </form>
            <?php
        }


        /**
         * Display the tool messages as admin notice.
         */
        function display_messages()
        {

            /* get the messages */
            if ($this->tool_messages) {

                foreach ($this->tool_messages as $message) {

                    /* validate message parameters  */

                    /* type in array */
                    if (!in_array($message['type'], ['info', 'warning', 'error', 'success'])) $message['type'] = 'info';

                    /* text string ends with punctuation */
                    if (!oes_ends_with($message['text'], '.') && !oes_ends_with($message['text'], '>'))
                        $message['text'] .= '.';

                    /* validate dismissible parameter */
                    if (!is_bool($message['dismissible'])) $message['dismissible'] = true;

                    add_oes_notice_after_refresh($message['text'],
                        $message['type'],
                        $message['dismissible']
                    );
                }
            }
        }


        /**
         * Runs when admin post request for the given action.
         */
        function admin_post()
        {

            /* validate nonce */
            if (!wp_verify_nonce($_POST[$this->name . '_nonce'], $this->action))
                die('Invalid nonce.' . var_export($_POST, true));

            /* get tool action */
            $this->validate_form_input_size();
            $this->admin_post_tool_action();

            /* check if form has redirection */
            if (!isset ($_POST['_wp_http_referer'])) die('Missing target.');

            /* Redirect after saving. */
            if($this->redirect) wp_safe_redirect(urldecode($_POST['_wp_http_referer']));

            exit;
        }


        /**
         * Validate input size.
         *
         * TODO @nextRelease: prevent exceeding of maximum input vars.
         */
        function validate_form_input_size()
        {
            /* check if max input vars has been exceeded */
            if (ini_get('max_input_vars') < $formCount = count($_POST, COUNT_RECURSIVE))
                add_oes_notice_after_refresh(
                    sprintf(__('The amount of variables in this form (%s) exceeds your server configuration for ' .
                        'max_input_vars (%$s) You might not be able to administer the datamodel via this ' .
                        'configuration panel.', 'oes'),
                        $formCount,
                        ini_get('max_input_vars')
                    ),
                    'error');
        }


        /**
         * Tool specific action.
         */
        function admin_post_tool_action()
        {
        }


        /**
         * Display the tools parameters for form.
         */
        function html()
        {
        }
    }
endif;


/* add tools */
add_action('admin_init', '\OES\Admin\Tools\include_tools');

/**
 * Include tools.
 */
function include_tools(){
    oes_include('/includes/admin/tools/tool.class.php');
    oes_include('/includes/admin/tools/config-datamodel.class.php');
    oes_include('/includes/admin/tools/export_datamodel.class.php');
    oes_include('/includes/admin/tools/config-writing.class.php');
    oes_include('/includes/admin/tools/config-pattern.class.php');
    oes_include('/includes/admin/tools/config-reading.class.php');
    oes_include('/includes/admin/tools/config-theme_labels.class.php');
    oes_include('/includes/admin/tools/import.class.php');
    oes_include('/includes/admin/tools/export.class.php');
    if(oes_user_is_oes_admin()) {
        oes_include('/includes/admin/tools/config-admin.class.php');
        oes_include('/includes/admin/tools/update.class.php');
        oes_include('/includes/admin/tools/delete.class.php');
    }
}


/**
 * Register a tool.
 *
 * @param string $class The tool class name.
 * @param string $name The tool name.
 * @return mixed Returns the registered instance of the Tool class.
 */
function register_tool(string $class, string $name = '', array $args = [])
{
    $instance = empty($args) ? new $class($name) : new $class($name, $args);
    return OES()->admin_tools[$instance->name] = $instance;
}


/**
 * Display a tool.
 *
 * @param string $name The tool name.
 */
function display_tool(string $name){
    $oes = OES();
    if (isset($oes->admin_tools[$name])) $oes->admin_tools[$name]->display_tool();
}