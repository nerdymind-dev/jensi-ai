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

    private $widget_config = null;

    private $widget_css = '';

    private $widget_agent = null;

    /**
     * Initialize this class.
     *
     * @param  string  $prefix
     */
    public function __construct($prefix)
    {
        $this->prefix = $prefix;

        // Only load on front-end, not admin
        if (! is_admin()) {
            // Get settings (used in multiple methods)
            $this->settings = (new Api\SettingController)->get_settings_raw();

            add_action('wp_enqueue_scripts', [$this, 'enqueue_chat_widget']);
        }
    }

    /**
     * Enqueue chat widget scripts and styles.
     *
     * @return void
     */
    public function enqueue_chat_widget()
    {
        $agent = $this->get_current_page_agent();

        if (! $agent) {
            return;
        }

        // Build the custom CSS vars (still needed, output inline in footer)
        $primaryHex = $agent['primary_color'] ?? '#667eea';
        $primaryRgb = sscanf($primaryHex, '#%02x%02x%02x');
        $secondaryHex = $agent['secondary_color'] ?? '#764ba2';
        $backgroundHex = $agent['background_color'] ?? '#ffffff';
        $textHex = $agent['text_color'] ?? '#000000';
        $secondaryTextHex = $agent['secondary_text_color'] ?? '#ffffff';
        $bottomOffset = isset($agent['bottom_offset']) ? intval($agent['bottom_offset']) : 20;
        $rightOffset = isset($agent['right_offset']) ? intval($agent['right_offset']) : 20;
        $this->widget_css = '
    :root {
        --jensi-ai-color-primary: ' . $primaryHex . ';
        --jensi-ai-rgb-primary: ' . implode(',', $primaryRgb) . ';
        --jensi-ai-color-secondary: ' . $secondaryHex . ';
        --jensi-ai-bottom-offset: ' . $bottomOffset . 'px;
        --jensi-ai-right-offset: ' . $rightOffset . 'px;
        --jensi-ai-color-background: ' . $backgroundHex . ';
        --jensi-ai-color-text: ' . $textHex . ';
        --jensi-ai-color-text-secondary: ' . $secondaryTextHex . ';
    }';

        // Store agent and config for the footer lazy-loader
        $this->widget_agent = $agent;
        $this->widget_config = $this->get_widget_config($agent);

        // Output the lazy-loader in the footer instead of enqueuing scripts now
        add_action('wp_footer', [$this, 'output_lazy_loader'], 21);
    }

    /**
     * Output the lazy loader for the chat widget.
     *
     * @return void
     */
    public function output_lazy_loader(): void
    {
        if (! $this->widget_config) {
            return;
        }

        global $wp_scripts, $wp_styles;

        // Collect script URLs in dependency order
        $handles = [
            $this->prefix . '-vuejs',
            $this->prefix . '-manifest',
            $this->prefix . '-vendor',
            $this->prefix . '-chat-widget',
        ];
        $srcs = [];
        foreach ($handles as $handle) {
            if (isset($wp_scripts->registered[$handle])) {
                $reg = $wp_scripts->registered[$handle];
                $src = $reg->src;
                if ($reg->ver) {
                    $src = add_query_arg('ver', $reg->ver, $src);
                }
                $srcs[] = esc_url($src);
            }
        }

        $css_url = '';
        if (isset($wp_styles->registered[$this->prefix . '-chat-widget'])) {
            $css_reg = $wp_styles->registered[$this->prefix . '-chat-widget'];
            $css_url = esc_url($css_reg->src . ($css_reg->ver ? '?ver=' . $css_reg->ver : ''));
        }

        if (empty($srcs)) {
            return;
        }

        $srcs_json = wp_json_encode($srcs);
        $css_json  = wp_json_encode($css_url);

        // Placeholder button values from the agent
        $bottom = isset($this->widget_agent['bottom_offset']) ? intval($this->widget_agent['bottom_offset']) : 20;
        $right  = isset($this->widget_agent['right_offset']) ? intval($this->widget_agent['right_offset']) : 20;

        // Inline CSS vars and config — both must be present before the widget JS runs
        echo '<style>' . wp_strip_all_tags($this->widget_css) . '</style>' . "\n";
        echo '<script>window.jensi_ai_chat_widget_config=' . wp_json_encode($this->widget_config) . ';</script>' . "\n";

        // Static placeholder button — matches the real .jensi-ai-chat-button exactly using the same CSS vars
        $button_style = sprintf(
            'position:fixed;bottom:%dpx;right:%dpx;z-index:2147483647;border:none;background:linear-gradient(135deg,var(--jensi-ai-color-primary) 0%%,var(--jensi-ai-color-secondary) 100%%);padding:0;cursor:pointer;border-radius:50%%;width:60px;height:60px;box-shadow:0 4px 12px rgba(0,0,0,0.15);display:flex;align-items:center;justify-content:center;color:var(--jensi-ai-color-text-secondary);transition:all 0.3s ease;',
            $bottom,
            $right
        );
        echo '<button id="jensi-ai-launcher-placeholder" style="' . $button_style . '" aria-label="Open chat with AI assistant">';
        echo '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width:28px;height:28px;"><path d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2ZM20 16H5.17L4 17.17V4H20V16Z"/><path d="M7 9H17V11H7V9ZM7 12H14V14H7V12Z"/></svg>';
        echo '</button>' . "\n";

        // Loader: sequential asset loading triggered by placeholder click or background interaction
        echo '<script>';
        echo '(function(){';
        echo 'var loaded=false,srcs=' . $srcs_json . ',css=' . $css_json . ';';
        echo 'var placeholder=document.getElementById("jensi-ai-launcher-placeholder");';
        // Hide placeholder only once Vue has actually added #jensi-ai-chat-widget to the DOM
        echo 'function onAllLoaded(){';
        echo 'if(!placeholder)return;';
        echo 'if(document.getElementById("jensi-ai-chat-widget")){placeholder.style.display="none";return;}';
        echo 'var obs=new MutationObserver(function(){if(document.getElementById("jensi-ai-chat-widget")){obs.disconnect();placeholder.style.display="none";}});';
        echo 'obs.observe(document.body,{childList:true});';
        echo '}';
        // autoOpen: set flag on config so Vue widget opens immediately on mount
        echo 'function load(autoOpen){';
        echo 'if(autoOpen&&window.jensi_ai_chat_widget_config)window.jensi_ai_chat_widget_config.autoOpen=true;';
        echo 'if(loaded)return;loaded=true;';
        echo 'if(css){var l=document.createElement("link");l.rel="stylesheet";l.href=css;document.head.appendChild(l);}';
        echo 'var i=0;function next(){if(i>=srcs.length){onAllLoaded();return;}var s=document.createElement("script");s.src=srcs[i++];s.onload=next;document.body.appendChild(s);}next();';
        echo '}';
        // Placeholder click: load scripts and open the chat widget immediately
        echo 'if(placeholder)placeholder.addEventListener("click",function(){load(true);});';
        // Preload scripts on other interactions so they are ready before the user clicks
        echo "['scroll','mousemove','touchstart','keydown'].forEach(function(e){document.addEventListener(e,function(){load(false);},{once:true,passive:true});});";
        echo '})();';
        echo '</script>' . "\n";
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
        $cache_key = 'jensi_ai_enabled_agents';
        $cached = wp_cache_get($cache_key, 'jensi_ai');
        if ($cached !== false) {
            return $cached;
        }

        global $wpdb;
        $agents_table = $wpdb->prefix . $this->prefix . '_agents';

        $agents = $wpdb->get_results("SELECT * FROM $agents_table WHERE enabled = 1 ORDER BY created ASC");
        if (! $agents) {
            return [];
        }

        // Convert each agent to array with proper data types
        $result = array_map(function ($agent) {
            return [
                'id' => $agent->id,
                'name' => $agent->name,
                'agent_id' => $agent->agent_id,
                'data_source_id' => $agent->data_source_id ?? null,
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

        wp_cache_set($cache_key, $result, 'jensi_ai', 300);

        return $result;
    }

    /**
     * Check if an agent matches the current page based on filtering rules.
     *
     * @param  array  $agent
     * @param  object|null  $current_post
     * @param  string|false  $current_post_type
     * @return bool
     */
    private function agent_matches_current_page($agent, $current_post, $current_post_type)
    {
        // If agent has no specific filtering rules, it doesn't match
        if (empty($agent['post_type']) && empty($agent['taxonomy']) && empty($agent['terms'])) {
            return false;
        }

        // Check post type matching
        if (! empty($agent['post_type'])) {
            if (! $current_post_type || ! in_array($current_post_type, $agent['post_type'])) {
                return false;
            }
        }

        // Check taxonomy and terms matching
        if (! empty($agent['taxonomy']) && ! empty($agent['terms']) && $current_post) {
            $taxonomy = $agent['taxonomy'];
            $required_terms = $agent['terms'];

            // Get the terms for the current post in the specified taxonomy
            $post_terms = wp_get_post_terms($current_post->ID, $taxonomy, ['fields' => 'slugs']);

            if (is_wp_error($post_terms)) {
                return false;
            }

            // Check if any of the required terms match the post's terms
            $has_matching_term = ! empty(array_intersect($required_terms, $post_terms));
            if (! $has_matching_term) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get widget configuration for the front-end.
     *
     * @param  array  $agent
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
            'pluginUrl' => rtrim(Main::$BASEURL, '/'),
            'avatarUrl' => ! empty($agent['avatar_url']) ? esc_url($agent['avatar_url']) : '',
        ];

        // Allow filtering of configuration
        return apply_filters('jensi_ai_chat_widget_config', $config, $agent);
    }
}
