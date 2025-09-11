<?php

namespace JensiAI;

/**
 * Chat widget loader for front-end.
 */
class ChatWidgetLoader
{
    /**
     * The application domain.
     *
     * @var string
     */
    private $prefix;

    private $settings;

    /**
     * Initialize this class.
     *
     * @param string $prefix
     */
    public function __construct($prefix)
    {
        $this->prefix = $prefix;

        // Only load on front-end, not admin
        if (!is_admin()) {
            add_action('wp_enqueue_scripts', [$this, 'enqueue_chat_widget']);
        }

        // Get settings (used in multiple methods)
        $this->settings = (new Api\SettingController())->get_settings_raw();
    }

    /**
     * Enqueue chat widget scripts and styles.
     *
     * @return void
     */
    public function enqueue_chat_widget()
    {
        $settings = $this->settings;

        // Check if chat widget should be enabled
        if (!$this->should_load_widget()) {
            return;
        }

        // Enqueue styles and scripts
        wp_enqueue_style($this->prefix . '-chat-widget');
        wp_enqueue_script($this->prefix . '-chat-widget');

        // Output custom styles based on settings
        $primaryHex = $settings['primary_color'] ?? '#667eea';
        $primaryRgb = sscanf($primaryHex, "#%02x%02x%02x");
        $secondaryHex = $settings['secondary_color'] ?? '#764ba2';
        $backgroundHex = $settings['background_color'] ?? '#ffffff';
        $textHex = $settings['text_color'] ?? '#000000';
        $secondaryTextHex = $settings['secondary_text_color'] ?? '#ffffff';
        $bottomOffset = isset($settings['bottom_offset']) ? intval($settings['bottom_offset']) : 20;
        $rightOffset = isset($settings['right_offset']) ? intval($settings['right_offset']) : 20;
        $custom_css = "
        :root {
            --jensi-ai-color-primary: " . $primaryHex . ";
            --jensi-ai-rgb-primary: " . implode(',', $primaryRgb) . ";
            --jensi-ai-color-secondary: " . $secondaryHex . ";
            --jensi-ai-bottom-offset: " . $bottomOffset . "px;
            --jensi-ai-right-offset: " . $rightOffset . "px;
            --jensi-ai-color-background: " . $backgroundHex . ";
            --jensi-ai-color-text: " . $textHex . ";
            --jensi-ai-color-text-secondary: " . $secondaryTextHex . ";
        }";
        wp_add_inline_style($this->prefix . '-chat-widget', $custom_css);

        // Get widget configuration
        $config = $this->get_widget_config();

        // Localize script with configuration
        wp_localize_script($this->prefix . '-chat-widget', 'jensi_ai_chat_widget_config', $config);
    }

    /**
     * Check if the widget should be loaded on the current page.
     *
     * @return bool
     */
    private function should_load_widget()
    {
        $settings = $this->settings;

        // Check if API key is configured
        if (empty($settings['jensi_ai_api_key'])) {
            return false;
        }

        // Check if chat widget is enabled (you might want to add this setting)
        $widget_enabled = $settings['jensi_ai_chat_widget_enabled'] ?? true;
        if (!$widget_enabled) {
            return false;
        }

        // Don't load on admin pages, login pages, etc.
        if (is_admin() || is_login() || wp_doing_ajax() || wp_doing_cron()) {
            return false;
        }

        // Allow filtering of where the widget should appear
        return apply_filters('jensi_ai_chat_widget_should_load', true);
    }

    /**
     * Get widget configuration for the front-end.
     *
     * @return array
     */
    private function get_widget_config()
    {
        $settings = $this->settings;

        // Get API URLs based on environment
        $env = wp_get_environment_type();
        $ws_base_url = $env === 'local'
            ? 'jensi-ai.test:8090'
            : 'ai.jensi.com:443'; // Use port 443 for secure WebSocket (wss) in production

        $config = [
            'nonce' => wp_create_nonce('wp_rest'),
            'apiBaseUrl' => rest_url($this->prefix . '/v1'),
            'wsBaseUrl' => $ws_base_url,
            'defaultAgentId' => $settings['jensi_ai_agent'] ?? '',
            'welcomeMessage' => $settings['welcome_message'] ?? 'Hello! How can I assist you today?',
            'pluginUrl' => rtrim(\JensiAI\Main::$BASEURL, '/'),
            'avatarUrl' => !empty($settings['avatar_url']) ? esc_url($settings['avatar_url']) : '',
        ];

        // Allow filtering of configuration
        return apply_filters('jensi_ai_chat_widget_config', $config);
    }
}
