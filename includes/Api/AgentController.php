<?php

namespace JensiAI\Api;

/**
 * Backend configs controller.
 */
class AgentController extends \WP_REST_Controller
{
    /**
     * The application domain.
     *
     * @var string
     */
    private $prefix;

    /**
     * The model storage.
     *
     * @var string
     */
    private $table_name;

    /**
     * The JENSi AI API base URL.
     *
     * @var string
     */
    private $base_api;

    /**
     * The JENSi AI API access token.
     *
     * @var string
     */
    private $token;

    /**
     * Initialize this class.
     */
    public function __construct()
    {
        $this->prefix = \JensiAI\Main::PREFIX;
        $this->namespace = $this->prefix . '/v1';
        $this->rest_base = 'agents';
        $this->table_name = null;

        // Get settings so we can set the API token
        $settings = (new SettingController())->get_settings_raw();
        $this->token = $settings['jensi_ai_api_key'] ?? '';

        // Get the base API url
        $this->base_api = wp_get_environment_type() === 'local'
            ? 'https://jensi-ai.test/api'
            : 'https://ai.jensi.com/api';
    }

    /**
     * Get the primary table for this controllers data
     *
     * @return string|null
     */
    public function getTableName(): ?string
    {
        // No tables associated with this controller
        return null;
    }

    /**
     * Register the routes.
     *
     * @return void
     */
    public function register_routes()
    {
        // Register the /wp-json/ + get_endpoint() route
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/get',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_agents'],
                    'permission_callback' => [$this, 'get_items_permissions_check'],
                    'args' => $this->get_collection_params('get'),
                ],
            ]
        );
    }

    /**
     * get the endpoint.
     *
     * @return array|string the full endpoint
     */
    public function get_endpoints()
    {
        // example: /wp-json/jensi_ai/v1/agents/get
        return [
            'get' => esc_url_raw(
                // GET
                rest_url($this->namespace . '/' . $this->rest_base . '/get')
            ),
        ];
    }

    /**
     * Retrieve data sources.
     *
     * @param \WP_REST_Request $request Full details about the request.
     *
     * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_agents($request)
    {
        // attempt to parse the json parameter
        $params = $request->get_params();
        $nonce = wp_create_nonce('wp_rest');

        // update correct response
        $response = [
            'data' => null,
            'success' => false,
            'nonce' => $nonce,
        ];
        if (!$this->token) {
            $response['data'] = 'No JENSi AI API token configured.';
        } else {
            // get search param if provided
            $search = isset($params['search']) ? $params['search'] : null;

            // call the remote API to get agents
            $url = $this->base_api . '/agents';
            if ($search) {
                $url = add_query_arg('search', urlencode($search), $url);
            }
            $api_response = wp_remote_get($url, [
                'headers' => [
                    'Accept' => 'application/json',
                    'Authorization' => 'Bearer ' . $this->token,
                ],
                'timeout' => 15,
                'sslverify' => wp_get_environment_type() !== 'local',
            ]);
            if (is_wp_error($api_response)) {
                return new \WP_Error('rest_api_error', __('Failed to connect to JENSi AI API.'), ['status' => 500]);
            }
            $code = wp_remote_retrieve_response_code($api_response);
            $body = wp_remote_retrieve_body($api_response);
            if ($code !== 200) {
                $data = json_decode($body, true);
                $message = $data['message'] ?? __('JENSi AI API returned an error.');
                return new \WP_Error($code, $message, $data);
            } else {
                $data = json_decode($body, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    $response['data'] = 'Failed to parse JENSi AI API response: ' . json_last_error_msg();
                    return new \WP_Error('rest_api_error', __('Failed to parse JENSi AI API response.'), ['status' => 500]);
                }
                $response['data'] = $data['data'];
                $response['success'] = true;
            }
        }
        return rest_ensure_response($response);
    }

    /**
     * Checks if a given request has access to read the items.
     *
     * @param \WP_REST_Request $request Full details about the request.
     *
     * @return true|\WP_Error True if the request has read access, WP_Error object otherwise.
     */
    public function get_items_permissions_check($request)
    {
        // optional: check nonce
        // https://via.studio/journal/wordpress-rest-api-secure-ajax-calls-custom-endpoints
        // example: /wp-json/me/v1/endpoint/?_wpnonce=${nonce}
        // check_ajax_referer('wp_rest', '_wpnonce', true)
        // 3rd parameter (die=true) to kill rest of execution
        if (!current_user_can('manage_options')) {
            return new \WP_Error('rest_forbidden', __('Sorry, you cannot update settings.'), ['status' => 403]);
        }

        // since success, we respond with next nonce
        header('X-WP-Nonce: ' . wp_create_nonce('wp_rest'));

        return true;
    }

    /**
     * Retrieves the query params for the items collection.
     *
     * @return array Collection parameters.
     */
    public function get_collection_params($route = null)
    {
        switch ($route) {
            case 'get':
                return [
                    'search' => [
                        'required' => false,
                        'validate_callback' => function ($param, $request, $key) {
                            return !empty($param);
                        },
                    ],
                ];
        }
        return [];
    }
}
