<?php

namespace JensiAI;

use Exception;
use Carbon\Carbon;
use JensiAI\Api\ConfigController;
use JensiAI\Api\SettingController;

/**
 * Main class.
 *
 * @class Main The class that holds initialize this plugin
 */
final class Main
{
    /**
     * A unique plugin prefix/token to use throughout your plugin.
     * This is also your application domain.
     *
     * @var string
     */
    public const PREFIX = 'jensi_ai';

    /**
     * Holds various class instances.
     *
     * @var array
     * @since   1.0.0
     */
    private $container = [];

    /**
     * The single instance of Main.
     *
     * @var     object
     * @since   1.0.0
     */
    private static $_instance = null; //phpcs:ignore

    /**
     * The version number.
     *
     * @var     string
     * @since   1.0.0
     */
    public $VERSION;

    /**
     * The plugin filename.
     *
     * @var string
     */
    public static $PLUGINFILE = '';

    /**
     * The base url, default '.'.
     *
     * @var string
     */
    public static $BASEURL = '.';

    /**
     * The plugin dir, default to empty string.
     *
     * @var string
     */
    public static $PLUGINDIR = '';

    /**
     * Constructor for the Main class.
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @param string $filepath the plugin file path
     * @param string $version Plugin version.
     */
    private function __construct($filepath, $version = '1.0.0')
    {
        self::$PLUGINFILE = $filepath;
        self::$PLUGINDIR = dirname($filepath);
        $this->VERSION = $version;
    }

    /**
     * Get a singleton instance of this plugin.
     *
     * Usage: Main::get_instance()
     *
     * @param string $filepath the plugin file path
     * @param string $version Plugin version.
     *
     * @return Main the singleton instance
     */
    public static function get_instance($filepath, $version = '1.0.0')
    {
        if (is_null(self::$_instance)) {
            self::$_instance = new self($filepath, $version);
        }

        return self::$_instance;
    }

    /**
     * Do stuff during plugin uninstall.
     */
    public static function uninstall_plugin()
    {
        flush_rewrite_rules();

        $settings = (new SettingController())->get_settings_raw();
        (new \JensiAI\Migrations())->cleanUp(self::PREFIX, $settings);
    }

    /**
     * Activate and initialize the plugin.
     */
    public function run()
    {
        // set base url from plugin file name
        self::$BASEURL = plugins_url('', self::$PLUGINFILE);

        register_activation_hook(self::$PLUGINFILE, [$this, 'activate_plugin']);
        register_deactivation_hook(self::$PLUGINFILE, [$this, 'deactivate_plugin']);
        register_uninstall_hook(self::$PLUGINFILE, [__CLASS__, 'uninstall_plugin']);

        // Set up the CRON job
        // add_filter('cron_schedules', [$this, 'jensi_ai_cron_schedules']);
        // add_action('jensi_ai_queue', [$this, 'process_jensi_ai_queue']);

        add_action('plugins_loaded', [$this, 'plugins_loaded']);

        // setup cli
        if (defined('WP_CLI') && \WP_CLI) {
            $this->container['cli'] = new \JensiAI\CliLoader(self::PREFIX);
        }

        // this is to register an action link from the Plugin manager page to our settings page
        $plugin = plugin_basename(self::$PLUGINFILE);
        add_filter("plugin_action_links_$plugin", [$this, 'register_settings_link']);

        // Add admin notices
        add_action('admin_notices', [$this, 'jensi_ai_admin_notices']);

        // EXAMPLE: On post save check if we need to generate additional content
        add_action('save_post', [$this, 'jensi_ai_save_post']);

        // EXAMPLE: Add custom metabox to product pages
        // add_action('add_meta_boxes', [$this, 'jensi_ai_add_product_meta_boxes']);

        // Additional thing you can do: register post type, taxonomy, etc...
        return $this;
    }

    /**
     * Register admin notices.
     */
    public function jensi_ai_admin_notices()
    {
        $transient = get_transient('jensi_ai_generating');
        if ($transient !== false) {
?>
            <div class="notice notice-<?php echo $transient['status']; ?> is-dismissible">
                <p><?php echo $transient['message']; ?></p>
            </div>
        <?php
            delete_transient('jensi_ai_generating');
        }
    }

    /**
     * Register custom CRON interval
     *
     * @param $schedules
     * @return mixed
     */
    function jensi_ai_cron_schedules($schedules)
    {
        // NOTE: need to update CRON timeout interval to be less than/equal to the shortest interval
        // E.g.: `define('WP_CRON_LOCK_TIMEOUT', 30);`
        if (!isset($schedules["every_half_minute"])) {
            $schedules["every_half_minute"] = [
                'interval' => 30,
                'display' => __('Every 30 seconds')
            ];
        }
        return $schedules;
    }

    /**
     * @return void
     */
    public function process_jensi_ai_queue()
    {
        (new QueueLoader())->run();
    }

    /**
     * If product, product image URL
     *
     * @param $post_id
     * @return void
     */
    public function jensi_ai_save_post($post_id): void
    {
        if (get_post_type($post_id) !== 'post') {
            return;
        }
        $this->submit_post_for_ai($post_id);
    }

    /**
     * On post save, check if we need to generate new content
     *
     * @param $post_id
     * @return void
     */
    public function submit_post_for_ai($post_id)
    {
        $controller = new ConfigController();
        $post_type = get_post_type($post_id);
        $terms = wp_get_post_categories($post_id, ['fields' => 'ids']);
        $config = $controller->get_config_for_terms($post_type, $terms);

        // If no config found, return
        if (!$config) {
            return;
        }

        // Queue up post for submission to JENSi AI
        $this->generate_content_for_post($post_id, $post_type, $config);
    }

    /**
     * Generate content for post
     *
     * @param $post_id
     * @param $type
     * @param $config
     * @return void
     */
    private function generate_content_for_post($post_id, $type, $config): void
    {
        // Get the post Object, so we can grab the current content
        $post = get_post($post_id);

        // If post and config set, queue it up!
        if ($post && $config) {
            (new QueueLoader())->store_job($post, $config, $type);
            set_transient('jensi_ai_generating', [
                'message' => 'Process has queued for this post! It will be imported into JENSi AI and added to your AI knowledge library.',
                'status' => 'success'
            ], 30);
        }
    }

    /**
     * Add product page custom metabox
     *
     * @return void
     */
    public function jensi_ai_add_product_meta_boxes()
    {
        add_meta_box(
            'jensi_ai_generate',
            'JENSi AI Generation',
            [$this, 'jensi_ai_echo_meta_box'],
            'post',
            'normal',
            'high'
        );
    }

    /**
     * Output product AI metabox
     *
     * @param $post
     * @return void
     */
    public function jensi_ai_echo_meta_box($post)
    {
        wp_nonce_field('jensi_ai_metabox_nonce', 'jensi_ai_nonce');
        $content = get_post_meta($post->ID, '_jensi_ai_generated_content', true);
        $liContent = get_post_meta($post->ID, '_jensi_ai_li_generated_content', true);
        $igContent = get_post_meta($post->ID, '_jensi_ai_ig_generated_content', true);
        $fbContent = get_post_meta($post->ID, '_jensi_ai_fb_generated_content', true);
        $twContent = get_post_meta($post->ID, '_jensi_ai_tw_generated_content', true);
        ?>
        <style>
            .jensi_ai_input_group_heading {
                font-size: 16px;
                margin-bottom: 35px;
            }

            .jensi_ai_input_group_info {
                font-size: 12px;
                color: #969696;
            }

            .jensi_ai_input_group {
                margin-bottom: 20px;
            }

            .jensi_ai_meta_details {
                font-size: 18px;
            }

            .jensi_ai_input_group .check {
                display: block;
                margin: 5px 0;
                font-size: 12px;
            }
        </style>
        <div class="jensi_ai_input_group_heading">
            <p class="jensi_ai_meta_details">
                Use the below options to generate (or re-generate) AI content on
                post save.
                <strong>This content will be automatically generated on post
                    create if <a href="/wp-admin/admin.php?page=jensi_ai" target="_blank">configured to do so</a>
                    .</strong>
                This override will allow you to generate content on-demand.
            </p>
            <label class="selectit">
                <input value="jensi_ai_generate_on_save" type="checkbox" name="jensi_ai_generate_on_save" id="jensi_ai_generate_on_save">
                Generate new content for all configured types on save?
            </label>
            <hr />
        </div>

        <div class="jensi_ai_input_group">
            <label class="">
                Post content
            </label>
            <label class="selectit check">
                <input value="jensi_ai_generate_general_on_save" type="checkbox" name="jensi_ai_generate_general_on_save" id="jensi_ai_generate_general_on_save">
                Generate new Post content on save?
            </label>
            <textarea class="large-text" name="jensi_ai_generated_content" id="jensi_ai_generated_content" rows="10" cols="4" autocomplete="off">
                <?php echo trim($content); ?>
            </textarea>
            <small class="jensi_ai_input_group_info">
                Meta field: _jensi_ai_generated_content
            </small>
        </div>

        <div class="jensi_ai_input_group">
            <label class="">
                LinkedIn content
            </label>
            <label class="selectit check">
                <input value="jensi_ai_generate_li_on_save" type="checkbox" name="jensi_ai_generate_li_on_save" id="jensi_ai_generate_li_on_save">
                Generate new LinkedIn content on save?
            </label>
            <textarea class="large-text" name="jensi_ai_li_generated_content" id="jensi_ai_li_generated_content" rows="4" cols="4" autocomplete="off">
                <?php echo trim($liContent); ?>
            </textarea>
            <small class="jensi_ai_input_group_info">
                Meta field: _jensi_ai_li_generated_content
            </small>
        </div>

        <div class="jensi_ai_input_group">
            <label class="">
                Instagram content
            </label>
            <label class="selectit check">
                <input value="jensi_ai_generate_ig_on_save" type="checkbox" name="jensi_ai_generate_ig_on_save" id="jensi_ai_generate_ig_on_save">
                Generate new Instagram content on save?
            </label>
            <textarea class="large-text" name="jensi_ai_ig_generated_content" id="jensi_ai_ig_generated_content" rows="4" cols="4" autocomplete="off">
                <?php echo trim($igContent); ?>
            </textarea>
            <small class="jensi_ai_input_group_info">
                Meta field: _jensi_ai_ig_generated_content
            </small>
        </div>

        <div class="jensi_ai_input_group">
            <label class="">
                Facebook content
            </label>
            <label class="selectit check">
                <input value="jensi_ai_generate_fb_on_save" type="checkbox" name="jensi_ai_generate_fb_on_save" id="jensi_ai_generate_fb_on_save">
                Generate new Facebook content on save?
            </label>
            <textarea class="large-text" name="jensi_ai_fb_generated_content" id="jensi_ai_fb_generated_content" rows="4" cols="4" autocomplete="off">
                <?php echo trim($fbContent); ?>
            </textarea>
            <small class="jensi_ai_input_group_info">
                Meta field: _jensi_ai_fb_generated_content
            </small>
        </div>

        <div class="jensi_ai_input_group">
            <label class="">
                Twitter content
            </label>
            <label class="selectit check">
                <input value="jensi_ai_generate_tw_on_save" type="checkbox" name="jensi_ai_generate_tw_on_save" id="jensi_ai_generate_tw_on_save">
                Generate new Twitter content on save?
            </label>
            <textarea class="large-text" name="jensi_ai_tw_generated_content" id="jensi_ai_tw_generated_content" rows="4" cols="4" autocomplete="off">
                <?php echo trim($twContent); ?>
            </textarea>
            <small class="jensi_ai_input_group_info">
                Meta field: _jensi_ai_tw_generated_content
            </small>
        </div>
<?php
    }

    /**
     * Magic getter to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __get($prop)
    {
        if (array_key_exists($prop, $this->container)) {
            return $this->container[$prop];
        }

        return $this->{$prop};
    }

    /**
     * Magic isset to bypass referencing plugin.
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __isset($prop)
    {
        return isset($this->{$prop}) || isset($this->container[$prop]);
    }

    /**
     * Register hooks after all plugins are loaded.
     *
     * @return void
     */
    public function plugins_loaded()
    {
        add_action('init', [$this, 'init_hook_handler']);
    }

    /**
     * Plugin activation function.
     */
    public function activate_plugin()
    {
        (new \JensiAI\Migrations())
            ->run(self::PREFIX, $this->VERSION);
    }

    /**
     * Do stuff during plugin deactivation.
     */
    public function deactivate_plugin()
    {
        flush_rewrite_rules();

        // clear saved options...
        delete_option('jensi_ai_models');

        // do stuff such as: shut off cron tasks, etc...
        wp_clear_scheduled_hook('jensi_ai_queue');
    }

    /**
     * Register settings link that display on the plugins listing page.
     *
     * @param array $links
     * @return array
     */
    public function register_settings_link($links)
    {
        $settings_link = '<a href="admin.php?page=' . self::PREFIX . '#/settings">';
        $settings_link .= esc_html(__('Settings', self::PREFIX)) . '</a>';
        array_unshift($links, $settings_link);

        return $links;
    }

    /**
     * Handler for init_hook.
     *
     * @return void
     */
    public function init_hook_handler()
    {
        // initialize assets
        $this->container['assets'] = new \JensiAI\Assets(self::PREFIX);

        // initialize the various loader classes
        if ($this->is_request('admin')) {
            $this->container['admin'] = new \JensiAI\AdminLoader(self::PREFIX);
        }

        if ($this->is_request('frontend')) {
            $this->container['frontend'] = new \JensiAI\FrontendLoader(self::PREFIX);
        }

        // finally load api routes
        $this->container['api'] = new \JensiAI\ApiRoutes(self::PREFIX);

        // Ensure CRON is scheduled
        if (!wp_next_scheduled('jensi_ai_queue')) {
            wp_schedule_event(time(), 'every_half_minute', 'jensi_ai_queue');
        }
    }

    /**
     * Initialize plugin for localization.
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup()
    {
        load_plugin_textdomain(
            self::PREFIX,
            false,
            dirname(plugin_basename(self::PREFIX)) . '/languages/'
        );
    }

    /**
     * What type of request is this?
     *
     * @param string $type admin, ajax, cron or frontend.
     *
     * @return bool
     */
    private function is_request($type)
    {
        switch ($type) {
            case 'admin':
                return is_admin();

            case 'ajax':
                return defined('DOING_AJAX');

            case 'rest':
                return defined('REST_REQUEST');

            case 'cron':
                return defined('DOING_CRON');

            case 'frontend':
                return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON');
        }
    }

    /**
     * Cloning is forbidden.
     *
     * @since 1.0.0
     */
    public function __clone()
    {
        _doing_it_wrong(__FUNCTION__, esc_html(__('Cloning of Main is forbidden')), esc_attr($this->VERSION));
    } // End __clone ()

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 1.0.0
     */
    public function __wakeup()
    {
        _doing_it_wrong(__FUNCTION__, esc_html(__('Unserializing instances of Main is forbidden')), esc_attr($this->VERSION));
    } // End __wakeup ()
}
