<?php

namespace JensiAI\Api;

/**
 * Chat API controller for front-end widget.
 */
class ChatController extends \WP_REST_Controller
{
    /**
     * The application domain.
     *
     * @var string
     */
    private $prefix;

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
        $this->rest_base = 'chat';

        // Get settings so we can set the API token
        $settings = (new SettingController())->get_settings_raw();
        $this->token = $settings['jensi_ai_api_key'] ?? '';

        // Get the base API url
        $this->base_api = wp_get_environment_type() === 'local'
            ? 'https://jensi-ai.test/api'
            : 'https://ai.jensi.com/api';
    }

    /**
     * Register the routes.
     *
     * @return void
     */
    public function register_routes()
    {
        // Send message to chat
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/send-message',
            [
                [
                    'methods' => \WP_REST_Server::CREATABLE,
                    'callback' => [$this, 'send_message'],
                    'permission_callback' => [$this, 'check_public_permissions'],
                    'args' => $this->get_send_message_params(),
                ],
            ]
        );

        // Get chat details
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/(?P<chat_id>[a-f0-9\-]{36})',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_chat'],
                    'permission_callback' => [$this, 'check_public_permissions'],
                    'args' => [
                        'chat_id' => [
                            'required' => true,
                            'validate_callback' => function ($param) {
                                return preg_match('/^[a-f0-9\-]{36}$/', $param);
                            },
                        ],
                    ],
                ],
            ]
        );

        // Create new chat
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/create',
            [
                [
                    'methods' => \WP_REST_Server::CREATABLE,
                    'callback' => [$this, 'create_chat'],
                    'permission_callback' => [$this, 'check_public_permissions'],
                    'args' => $this->get_create_chat_params(),
                ],
            ]
        );
    }

    /**
     * Send a message to the chat.
     *
     * @param \WP_REST_Request $request Full details about the request.
     *
     * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
     */
    public function send_message($request)
    {
        $params = $request->get_params();

        if (!$this->token) {
            return new \WP_Error('no_api_token', __('No JENSi AI API token configured.'), ['status' => 500]);
        }

        $body = [
            'message' => sanitize_textarea_field($params['message']),
        ];

        // Add chat_id or agent_id depending on what's provided
        if (!empty($params['chat_id'])) {
            $body['chat_id'] = sanitize_text_field($params['chat_id']);
        } elseif (!empty($params['agent_id'])) {
            $body['agent_id'] = sanitize_text_field($params['agent_id']);
        } else {
            return new \WP_Error('missing_ids', __('Either chat_id or agent_id must be provided.'), ['status' => 400]);
        }

        if (!empty($params['metadata'])) {
            $body['metadata'] = array_map('sanitize_text_field', $params['metadata']);
        }

        $api_response = wp_remote_post($this->base_api . '/chats/messages', [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token,
            ],
            'body' => wp_json_encode($body),
            'timeout' => 30,
            'sslverify' => wp_get_environment_type() !== 'local',
        ]);

        if (is_wp_error($api_response)) {
            return new \WP_Error('api_error', __('Failed to connect to JENSi AI API.'), ['status' => 500]);
        }

        $code = wp_remote_retrieve_response_code($api_response);
        $response_body = wp_remote_retrieve_body($api_response);
        $data = json_decode($response_body, true);

        if ($code !== 201) {
            $message = $data['message'] ?? __('JENSi AI API returned an error.');
            return new \WP_Error('api_error', $message, ['status' => $code]);
        }

        return rest_ensure_response([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Get chat details and message history.
     *
     * @param \WP_REST_Request $request Full details about the request.
     *
     * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_chat($request)
    {
        $chat_id = $request->get_param('chat_id');

        if (!$this->token) {
            return new \WP_Error('no_api_token', __('No JENSi AI API token configured.'), ['status' => 500]);
        }

        $api_response = wp_remote_get($this->base_api . '/chats/' . $chat_id, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token,
            ],
            'timeout' => 15,
            'sslverify' => wp_get_environment_type() !== 'local',
        ]);

        if (is_wp_error($api_response)) {
            return new \WP_Error('api_error', __('Failed to connect to JENSi AI API.'), ['status' => 500]);
        }

        $code = wp_remote_retrieve_response_code($api_response);
        $response_body = wp_remote_retrieve_body($api_response);
        $data = json_decode($response_body, true);

        if ($code !== 200) {
            $message = $data['message'] ?? __('JENSi AI API returned an error.');
            return new \WP_Error('api_error', $message, ['status' => $code]);
        }

        return rest_ensure_response([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Create a new chat session.
     *
     * @param \WP_REST_Request $request Full details about the request.
     *
     * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
     */
    public function create_chat($request)
    {
        $params = $request->get_params();

        if (!$this->token) {
            return new \WP_Error('no_api_token', __('No JENSi AI API token configured.'), ['status' => 500]);
        }

        $body = [
            'agent_id' => sanitize_text_field($params['agent_id']),
        ];

        if (!empty($params['metadata'])) {
            $body['metadata'] = array_map('sanitize_text_field', $params['metadata']);
        }

        $api_response = wp_remote_post($this->base_api . '/chats', [
            'headers' => [
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token,
            ],
            'body' => wp_json_encode($body),
            'timeout' => 15,
            'sslverify' => wp_get_environment_type() !== 'local',
        ]);

        if (is_wp_error($api_response)) {
            return new \WP_Error('api_error', __('Failed to connect to JENSi AI API.'), ['status' => 500]);
        }

        $code = wp_remote_retrieve_response_code($api_response);
        $response_body = wp_remote_retrieve_body($api_response);
        $data = json_decode($response_body, true);

        if ($code !== 201) {
            $message = $data['message'] ?? __('JENSi AI API returned an error.');
            return new \WP_Error('api_error', $message, ['status' => $code]);
        }

        return rest_ensure_response([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Check permissions for public endpoints.
     *
     * @param \WP_REST_Request $request Full details about the request.
     *
     * @return true|\WP_Error True if the request has access, WP_Error object otherwise.
     */
    public function check_public_permissions($request)
    {
        // Check for CSRF to make sure request is valid
        $nonce = $request->get_header('X-WP-Nonce') ?: $request->get_param('_wpnonce');
        if (!$nonce || !wp_verify_nonce($nonce, 'wp_rest')) {
            return new \WP_Error('rest_forbidden', __('Invalid nonce.'), ['status' => 403]);
        }

        // since success, we respond with next nonce
        header('X-WP-Nonce: ' . wp_create_nonce('wp_rest'));

        return true;
    }

    /**
     * Get parameters for send message endpoint.
     *
     * @return array
     */
    private function get_send_message_params()
    {
        return [
            'message' => [
                'required' => true,
                'type' => 'string',
                'validate_callback' => function ($param) {
                    return !empty($param) && strlen($param) <= 10000;
                },
            ],
            'chat_id' => [
                'required' => false,
                'type' => 'string',
                'validate_callback' => function ($param) {
                    return empty($param) || preg_match('/^[a-f0-9\-]{36}$/', $param);
                },
            ],
            'agent_id' => [
                'required' => false,
                'type' => 'string',
                'validate_callback' => function ($param) {
                    return empty($param) || preg_match('/^[a-f0-9\-]{36}$/', $param);
                },
            ],
            'metadata' => [
                'required' => false,
                'type' => 'array',
            ],
        ];
    }

    /**
     * Get parameters for create chat endpoint.
     *
     * @return array
     */
    private function get_create_chat_params()
    {
        return [
            'agent_id' => [
                'required' => true,
                'type' => 'string',
                'validate_callback' => function ($param) {
                    return preg_match('/^[a-f0-9\-]{36}$/', $param);
                },
            ],
            'metadata' => [
                'required' => false,
                'type' => 'array',
            ],
        ];
    }
}
