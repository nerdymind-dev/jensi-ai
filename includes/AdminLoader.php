<?php

namespace JensiAI;

/**
 * Admin pages loader.
 */
class AdminLoader
{
    /**
     * The application domain.
     *
     * @var string
     */
    protected $prefix;

    /**
     * Initialize this class.
     */
    public function __construct($prefix)
    {
        $this->prefix = $prefix;
        add_action('admin_menu', [$this, 'admin_menu']);
    }

    /**
     * Register our menu page.
     *
     * @return void
     */
    public function admin_menu()
    {
        global $submenu;

        $capability = 'manage_options';
        $slug = $this->prefix;

        $hook = add_menu_page(
            esc_html(__('JENSi AI', $this->prefix)),
            esc_html(__('JENSi AI', $this->prefix)),
            $capability,
            $slug,
            [$this, 'plugin_page'],
            'data:image/svg+xml;base64,' . base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-6 h-6"><path fill-rule="evenodd" d="M4.804 21.644A6.707 6.707 0 006 21.75a6.721 6.721 0 003.583-1.029c.774.182 1.584.279 2.417.279 5.322 0 9.75-3.97 9.75-9 0-5.03-4.428-9-9.75-9s-9.75 3.97-9.75 9c0 2.409 1.025 4.587 2.674 6.192.232.226.277.428.254.543a3.73 3.73 0 01-.814 1.686.75.75 0 00.44 1.223zM8.25 10.875a1.125 1.125 0 100 2.25 1.125 1.125 0 000-2.25zM10.875 12a1.125 1.125 0 112.25 0 1.125 1.125 0 01-2.25 0zm4.875-1.125a1.125 1.125 0 100 2.25 1.125 1.125 0 000-2.25z" clip-rule="evenodd" /></svg>')
            //'dashicons-superhero' // tip: https://developer.wordpress.org/resource/dashicons
        );

        if (current_user_can($capability)) {
            add_submenu_page(
                $slug,
                esc_html(__('Queue', $this->prefix)),
                esc_html(__('Queue', $this->prefix)),
                $capability,
                "admin.php?page={$slug}#/queue"
            );
            add_submenu_page(
                $slug,
                esc_html(__('Settings', $this->prefix)),
                esc_html(__('Settings', $this->prefix)),
                $capability,
                "admin.php?page={$slug}#/settings"
            );
            add_submenu_page(
                $slug,
                esc_html(__('Help', $this->prefix)),
                esc_html(__('Help', $this->prefix)),
                $capability,
                "admin.php?page={$slug}#/help"
            );
        }
    }

    /**
     * Load scripts and styles for the app.
     *
     * @return void
     */
    public function enqueue_scripts()
    {
        wp_enqueue_style($this->prefix . '-admin');
        wp_enqueue_script($this->prefix . '-admin');
    }

    /**
     * Render our admin page.
     *
     * @return void
     */
    public function plugin_page()
    {
        // register scripts
        $this->enqueue_scripts();

        // instantiate controllers
        $settingController = new Api\SettingController();
        $queueController = new Api\QueueController();

        // output data for use on client-side
        // https://wordpress.stackexchange.com/questions/344537/authenticating-with-rest-api
        $appVars = apply_filters('jensi_ai/admin_app_vars', [
            'rest' => [
                'endpoints' => [
                    'settings' => $settingController->get_endpoints(),
                    'queue' => $queueController->get_endpoints(),
                ],
                'nonce' => wp_create_nonce('wp_rest'),
            ],
            'nonce' => wp_create_nonce('wp_rest'),
            // 'contentTypes' => [],
            // 'endpoints' => [],
            'settings' => $settingController->get_settings_raw(),
            'settingStructure' => $settingController->get_settings_structure(true),
            'prefix' => $this->prefix,
            'queueTable' => $queueController->queue_table(),
            'postTypes' => get_post_types([], 'names'),
            'postTerms' => get_terms(['taxonomy' => 'category']),
            'adminUrl' => admin_url('/'),
            'pluginUrl' => rtrim(\JensiAI\Main::$BASEURL, '/'),
            'pluginVersion' => JENSI_AI_VERSION
        ]);

        wp_localize_script($this->prefix . '-admin', 'vue_wp_plugin_config_admin', $appVars);

        $content = '<div class="admin-app-wrapper"><div id="vue-admin-app"></div></div>';
        echo $content;
    }
}
