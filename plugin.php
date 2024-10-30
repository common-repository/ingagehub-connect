<?php
/*
Plugin Name: INgageHub Connect
Plugin URI: http://www.ingagehub.com/features/wordpress-integration/
Description: INgageHub
Author: INgageHub
Version: 2.1.0
Author URI: http://www.ingagehub.com
*/

if ( !function_exists( 'add_action' ) ) {
    echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
    exit;
}

function com_ingagehub_plugin_dir_path()
{
    return plugin_dir_path (__FILE__);
}

function com_ingagehub_plugin_dir_url()
{
    return plugin_dir_url (__FILE__);
}

function com_ingagehub_clear_cached_objects() {
    if (!session_id())
        session_start();

    unset($_SESSION['ih_connect_cached_base_questions']);
    unset($_SESSION['ih_connect_cached_campaign']);
    unset($_SESSION['ih_connect_cached_campaigns']);
    unset($_SESSION['ih_connect_cached_question_layouts']);
    unset($_SESSION['ih_connect_campaign_table_filter']);
    unset($_SESSION['ih_connect_campaign_table_sort']);
    unset($_SESSION['ih_connect_message']);
    unset($_SESSION['ih_connect_message_class']);
}

function com_ingagehub_clear_cached_loadables() {
    if (!session_id())
        session_start();

    unset($_SESSION['ih_connect_cached_base_questions']);
    unset($_SESSION['ih_connect_cached_campaign']);
    unset($_SESSION['ih_connect_cached_campaigns']);
    unset($_SESSION['ih_connect_cached_question_layouts']);
}

include com_ingagehub_plugin_dir_path() . 'classes/auth.php';
include com_ingagehub_plugin_dir_path() . 'classes/base_question.php';
include com_ingagehub_plugin_dir_path() . 'classes/campaign.php';
include com_ingagehub_plugin_dir_path() . 'classes/messages.php';
include com_ingagehub_plugin_dir_path() . 'classes/question_layout.php';

include com_ingagehub_plugin_dir_path() . 'components/services.php';
include com_ingagehub_plugin_dir_path() . 'components/shortcodes.php';

class INgageHubConnect {
    public function __construct() {
        add_action( 'admin_head', array( $this, 'com_ingagehub_global_script_admin' ) );
        add_action( 'admin_init', array( $this, 'com_ingagehub_mce_setup' ) );
        add_action( 'admin_menu', array( $this, 'com_ingagehub_add_menu_pages' ) );
        add_action( 'wp_head', array( $this, 'com_ingagehub_global_script' ) );
        add_action( 'wp_logout', array( $this, 'com_ingagehub_logout' ) );
    }

    public function com_ingagehub_mce_setup() {
        if ( current_user_can( 'edit_posts' ) && current_user_can( 'edit_pages' ) ) {
            add_filter( 'mce_buttons', array( $this, 'com_ingagehub_mce_buttons' ) );
            add_filter( 'mce_external_plugins', array( $this, 'com_ingagehub_mce_plugin' ) );
        }
    }

    public function com_ingagehub_mce_buttons( $buttons ) {
        array_push( $buttons, "button_ih_connect" );
        return $buttons;
    }

    public function com_ingagehub_mce_plugin( $plugin_array ) {
        $plugin_array['com_ingagehub_mce_plugin'] = com_ingagehub_plugin_dir_url() . 'js/mce_plugin.min.js';
        return $plugin_array;
    }

    public function com_ingagehub_global_script() {
        ?>
        <script type="text/javascript">
            window.INgageHub = window.INgageHub || {};
            window.INgageHub.pluginUrl = '<?php echo com_ingagehub_plugin_dir_url(); ?>';
            window.INgageHub.ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
        </script>
        <?php
    }

    public function com_ingagehub_global_script_admin() {
        ?>
        <script type="text/javascript">
            window.INgageHub = window.INgageHub || {};
            window.INgageHub.pluginUrl = '<?php echo com_ingagehub_plugin_dir_url(); ?>';
            window.INgageHub.ajaxUrl = '<?php echo admin_url('admin-ajax.php'); ?>';
            window.INgageHub.optionsUrl = '<?php menu_page_url('ingagehub_menu'); ?>';
            window.INgageHub.campaignsUrl = '<?php menu_page_url('ingagehub_menu_campaigns'); ?>';
        </script>
        <?php
    }

    public function com_ingagehub_add_menu_pages() {
        add_menu_page(
            'INgageHub Connect'
            , 'INgageHub'
            , 'edit_posts'
            , 'ingagehub_menu'
            , ''
            , com_ingagehub_plugin_dir_url() . 'images/ih_button_20x20.png'
        );

        $page = add_submenu_page(
            'ingagehub_menu'
            , 'INgageHub Connect Options'
            , 'Options'
            , 'edit_posts'
            , 'ingagehub_menu'
            , array( $this, 'com_ingagehub_menu_page_options' )
        );

        add_action('load-' . $page, array( $this, 'com_ingagehub_menu_page_options_load') );

        $page = add_submenu_page(
            'ingagehub_menu'
            , 'INgageHub Connect Campaigns'
            , 'Campaigns'
            , 'edit_posts'
            , 'ingagehub_menu_campaigns'
            , array( $this, 'com_ingagehub_menu_page_campaigns' )
        );

        add_action('load-' . $page, array( $this, 'com_ingagehub_menu_page_campaigns_load') );
    }

    public function com_ingagehub_menu_page_options() {
        include com_ingagehub_plugin_dir_path() . 'menu_pages/options.php';
    }

    public function com_ingagehub_menu_page_options_load() {
        if (!session_id())
            session_start();

        include com_ingagehub_plugin_dir_path() . 'menu_pages/options_load.php';
    }

    public function com_ingagehub_menu_page_campaigns_load() {
        if (!session_id())
            session_start();

        include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns_load.php';
    }

    public function com_ingagehub_menu_page_campaigns() {
        include com_ingagehub_plugin_dir_path() . 'menu_pages/campaigns.php';
    }

    public function com_ingagehub_logout() {
        $a = new INgageHubConnectAuth();
        $a->logout();

        com_ingagehub_clear_cached_objects();
    }
}

new INgageHubConnect();
?>
