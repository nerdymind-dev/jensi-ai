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
        // Find the agent that should be displayed for the current page
        $agent = $this->get_current_page_agent();

        // If no agent should be displayed, don't load the widget
        if (!$agent) {
            return;
        }

        // Enqueue styles and scripts
        wp_enqueue_style($this->prefix . '-chat-widget');
        wp_enqueue_script($this->prefix . '-chat-widget');

        // Output custom styles based on agent settings
        $primaryHex = $agent['primary_color'] ?? '#667eea';
        $primaryRgb = sscanf($primaryHex, "#%02x%02x%02x");
        $secondaryHex = $agent['secondary_color'] ?? '#764ba2';
        $backgroundHex = $agent['background_color'] ?? '#ffffff';
        $textHex = $agent['text_color'] ?? '#000000';
        $secondaryTextHex = $agent['secondary_text_color'] ?? '#ffffff';
        $bottomOffset = isset($agent['bottom_offset']) ? intval($agent['bottom_offset']) : 20;
        $rightOffset = isset($agent['right_offset']) ? intval($agent['right_offset']) : 20;
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
        $config = $this->get_widget_config($agent);

        // Localize script with configuration
        wp_localize_script($this->prefix . '-chat-widget', 'jensi_ai_chat_widget_config', $config);
    }

    /**
     * Get the agent that should be displayed for the current page.
     *
     * @return array|null
     */
    private function get_current_page_agent()
    {
        // Check if API key is configured
        if (empty($this->settings['jensi_ai_api_key'])) {
            return null;
        }

        // Don't load on admin pages, login pages, etc.
        if (is_admin() || is_login() || wp_doing_ajax() || wp_doing_cron()) {
            return null;
        }

        // Get all enabled agents
        $agents = $this->get_enabled_agents();
        if (empty($agents)) {
            return null;
        }

        // Get current post/page information
        $current_post = get_queried_object();
        $current_post_type = get_post_type();

        // First, check for agents with specific filtering rules
        // This will allow more specific agents to take precedence
        // over those that display everywhere
        foreach ($agents as $agent) {
            if ($this->agent_matches_current_page($agent, $current_post, $current_post_type)) {
                return $agent;
            }
        }

        // Then check for agents that display everywhere
        foreach ($agents as $agent) {
            if ($agent['display_everywhere']) {
                return $agent;
            }
        }

        // Allow filtering of the selected agent
        $selected_agent = apply_filters('jensi_ai_selected_agent', null, $agents);
        if ($selected_agent) {
            return $selected_agent;
        }

        return null;
    }

    /**
     * Get all enabled agents from the database.
     *
     * @return array
     */
    private function get_enabled_agents()
    {
        global $wpdb;
        $agents_table = $wpdb->prefix . $this->prefix . '_agents';

        $agents = $wpdb->get_results("SELECT * FROM $agents_table WHERE enabled = 1 ORDER BY created ASC");
        if (!$agents) {
            return [];
        }

        // Convert each agent to array with proper data types
        return array_map(function ($agent) {
            return [
                'id' => $agent->id,
                'name' => $agent->name,
                'agent_id' => $agent->agent_id,
                'data_source_id' => $agent->data_source_id,
                'enabled' => (bool) $agent->enabled,
                'avatar_url' => $agent->avatar_url,
                'welcome_message' => $agent->welcome_message,
                'bottom_offset' => (int) $agent->bottom_offset,
                'right_offset' => (int) $agent->right_offset,
                'primary_color' => $agent->primary_color,
                'secondary_color' => $agent->secondary_color,
                'background_color' => $agent->background_color,
                'text_color' => $agent->text_color,
                'secondary_text_color' => $agent->secondary_text_color,
                'post_type' => $agent->post_type ? explode(',', $agent->post_type) : [],
                'taxonomy' => $agent->taxonomy,
                'terms' => $agent->terms ? explode(',', $agent->terms) : [],
                'display_everywhere' => (bool) $agent->display_everywhere,
            ];
        }, $agents);
    }

    /**
     * Check if an agent matches the current page based on filtering rules.
     *
     * @param array $agent
     * @param object|null $current_post
     * @param string|false $current_post_type
     * @return bool
     */
    private function agent_matches_current_page($agent, $current_post, $current_post_type)
    {
        // If agent has no specific filtering rules, it doesn't match
        if (empty($agent['post_type']) && empty($agent['taxonomy']) && empty($agent['terms'])) {
            return false;
        }

        // Check post type matching
        if (!empty($agent['post_type'])) {
            if (!$current_post_type || !in_array($current_post_type, $agent['post_type'])) {
                return false;
            }
        }

        // Check taxonomy and terms matching
        if (!empty($agent['taxonomy']) && !empty($agent['terms']) && $current_post) {
            $taxonomy = $agent['taxonomy'];
            $required_terms = $agent['terms'];

            // Get the terms for the current post in the specified taxonomy
            $post_terms = wp_get_post_terms($current_post->ID, $taxonomy, ['fields' => 'slugs']);

            if (is_wp_error($post_terms)) {
                return false;
            }

            // Check if any of the required terms match the post's terms
            $has_matching_term = !empty(array_intersect($required_terms, $post_terms));
            if (!$has_matching_term) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get widget configuration for the front-end.
     *
     * @param array $agent
     * @return array
     */
    private function get_widget_config($agent)
    {
        // Get API URLs based on environment
        $env = wp_get_environment_type();
        $ws_base_url = $env === 'local'
            ? 'jensi-ai.test:8090'
            : 'ai.jensi.com:443'; // Use port 443 for secure WebSocket (wss) in production

        $config = [
            'id' => $agent['id'],
            'nonce' => wp_create_nonce('wp_rest'),
            'apiBaseUrl' => rest_url($this->prefix . '/v1'),
            'wsBaseUrl' => $ws_base_url,
            'defaultAgentId' => $agent['agent_id'] ?? '',
            'dataSourceId' => $agent['data_source_id'] ?? '',
            'welcomeMessage' => $agent['welcome_message'] ?? 'Hello! How can I assist you today?',
            'pluginUrl' => rtrim(\JensiAI\Main::$BASEURL, '/'),
            'avatarUrl' => !empty($agent['avatar_url']) ? esc_url($agent['avatar_url']) : '',
        ];

        // Allow filtering of configuration
        return apply_filters('jensi_ai_chat_widget_config', $config, $agent);
    }
}
