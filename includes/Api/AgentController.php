<?php

namespace JensiAI\Api;

/**
 * Backend Agent CRUD controller.
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
     * Initialize this class.
     */
    public function __construct()
    {
        $this->prefix = \JensiAI\Main::PREFIX;
        $this->namespace = $this->prefix . '/v1';
        $this->rest_base = 'agent-crud';
        $this->table_name = $this->prefix . '_agents';
    }

    /**
     * Get the primary table for this controllers data
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->table_name;
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
            '/' . $this->rest_base . '/all',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_agents'],
                    'permission_callback' => [$this, 'get_items_permissions_check'],
                    'args' => $this->get_collection_params(),
                ]
            ]
        );
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/agent',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_agent'],
                    'permission_callback' => [$this, 'get_items_permissions_check'],
                    'args' => $this->get_collection_params('get'),
                ],
                [
                    'methods' => \WP_REST_Server::CREATABLE,
                    'callback' => [$this, 'create_update_agent'],
                    'permission_callback' => [$this, 'get_items_permissions_check'],
                    'args' => $this->get_collection_params('update'),
                ],
                [
                    'methods' => \WP_REST_Server::DELETABLE,
                    'callback' => [$this, 'destroy_agent'],
                    'permission_callback' => [$this, 'get_items_permissions_check'],
                    'args' => $this->get_collection_params('destroy'),
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
        // example: jensi-ai/v1/agent-crud
        return [
            'all' => esc_url_raw(
                // GET
                rest_url($this->namespace . '/' . $this->rest_base . '/all')
            ),
            'crud' => esc_url_raw(
                // GET/POST/DELETE
                rest_url($this->namespace . '/' . $this->rest_base . '/agent')
            ),
        ];
    }

    /**
     * Retrieves agents.
     *
     * @param \WP_REST_Request $request Full details about the request.
     *
     * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_agents($request)
    {
        $nonce = wp_create_nonce('wp_rest');
        $response = [
            'data' => $this->get_all_agents(),
            'success' => true,
            'nonce' => $nonce,
        ];
        return rest_ensure_response($response);
    }

    /**
     * Retrieves a single agent.
     *
     * @param \WP_REST_Request $request Full details about the request.
     *
     * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_agent($request)
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

        if (isset($params['id'])) {
            global $wpdb;
            $agents_table = $wpdb->prefix . $this->table_name;
            $id = intval($params['id']);

            $agent = $wpdb->get_row($wpdb->prepare("SELECT * FROM $agents_table WHERE id = %d", $id));

            if ($agent) {
                $response['data'] = [
                    'id' => $agent->id,
                    'name' => $agent->name,
                    'agent_id' => $agent->agent_id,
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
                    'created' => $agent->created,
                    'modified' => $agent->modified,
                ];
                $response['success'] = true;
            } else {
                $response['data'] = 'Agent not found.';
            }
        } else {
            $response['data'] = 'ID parameter is required.';
        }

        return rest_ensure_response($response);
    }

    /**
     * Create or update agent.
     *
     * @param \WP_REST_Request $request Full details about the request.
     *
     * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
     */
    public function create_update_agent($request)
    {
        // attempt to parse the json parameter
        $params = $request->get_json_params();
        $nonce = wp_create_nonce('wp_rest');

        // update correct response
        $response = [
            'data' => $params,
            'success' => false,
            'nonce' => $nonce,
        ];

        if (isset($params)) {
            global $wpdb;
            $id = $params['id'] ?? null;
            $tableName = $wpdb->prefix . $this->table_name;

            // Sanitize and prepare data
            $fields = [
                'name' => sanitize_text_field($params['name'] ?? ''),
                'agent_id' => sanitize_text_field($params['agent_id'] ?? ''),
                'enabled' => isset($params['enabled']) ? (bool) $params['enabled'] : true,
                'avatar_url' => esc_url_raw($params['avatar_url'] ?? ''),
                'welcome_message' => sanitize_textarea_field($params['welcome_message'] ?? 'Hello! How can I assist you today?'),
                'bottom_offset' => isset($params['bottom_offset']) ? intval($params['bottom_offset']) : 20,
                'right_offset' => isset($params['right_offset']) ? intval($params['right_offset']) : 20,
                'primary_color' => sanitize_hex_color($params['primary_color'] ?? '#667eea'),
                'secondary_color' => sanitize_hex_color($params['secondary_color'] ?? '#764ba2'),
                'background_color' => sanitize_hex_color($params['background_color'] ?? '#ffffff'),
                'text_color' => sanitize_hex_color($params['text_color'] ?? '#000000'),
                'secondary_text_color' => sanitize_hex_color($params['secondary_text_color'] ?? '#ffffff'),
                'post_type' => sanitize_text_field($params['post_type'] ?? null),
                'taxonomy' => sanitize_text_field($params['taxonomy'] ?? null),
                'terms' => json_encode($params['terms'] ?? []),
                'display_everywhere' => isset($params['display_everywhere']) ? (bool) $params['display_everywhere'] : false,
            ];

            if (!$id) {
                // Create it
                $wpdb->insert($tableName, $fields);
                $result = $this->get_agent_object(); // get the latest item
                if ($result) {
                    // Errors will be an array, else TRUE if stored successfully
                    $response['data'] = $result;
                    $response['success'] = true;
                }
            } else {
                // Update it
                $result = $this->get_agent_object($id);
                if ($result) {
                    $updated = $wpdb->update($tableName, $fields, ['id' => $result->id]);
                    if ($updated !== false) {
                        // Get the updated item
                        $result = $this->get_agent_object($id);
                        // Errors will be an array, else TRUE if stored successfully
                        $response['data'] = $result;
                        $response['success'] = true;
                    }
                }
            }
        }

        return rest_ensure_response($response);
    }

    /**
     * Delete agent.
     *
     * @param \WP_REST_Request $request Full details about the request.
     *
     * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
     */
    public function destroy_agent($request)
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

        if (isset($params['id'])) {
            global $wpdb;
            $agents_table = $wpdb->prefix . $this->table_name;
            $id = intval($params['id']);

            $result = $wpdb->delete($agents_table, ['id' => $id], ['%d']);

            if ($result !== false && $result > 0) {
                $response['data'] = 'Agent deleted successfully.';
                $response['success'] = true;
            } else {
                $response['data'] = 'Failed to delete agent or agent not found.';
            }
        } else {
            $response['data'] = 'ID parameter is required.';
        }

        return rest_ensure_response($response);
    }

    /**
     * Get all configs
     *
     * @return array|object|\stdClass[]|null
     */
    public function get_all_agents()
    {
        global $wpdb;
        $agents_table = $wpdb->prefix . $this->table_name;
        return $wpdb->get_results("SELECT * FROM $agents_table ORDER BY `created` DESC");
    }

    /**
     * Fetch row from the agents table
     *
     * @param object|null $id
     * @return array|object|\stdClass|null
     */
    public function get_agent_object($id = null)
    {
        global $wpdb;
        $configs_table = $wpdb->prefix . $this->table_name;
        if ($id !== null) {
            // Get specified item from the database
            $result = $wpdb->get_row("SELECT * FROM $configs_table WHERE `id` = $id");
        } else {
            // Get first item (latest entry) if no ID passed
            $result = $wpdb->get_row("SELECT * FROM $configs_table ORDER BY `modified` DESC");
        }
        return $result;
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
        if (!current_user_can('manage_options')) {
            return new \WP_Error('rest_forbidden', __('Sorry, you cannot manage agents.'), ['status' => 403]);
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
    public function get_collection_params($context = '')
    {
        if ($context === 'get' || $context === 'destroy') {
            return [
                'id' => [
                    'required' => true,
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    },
                ],
            ];
        } elseif ($context === 'update') {
            return [
                'id' => [
                    'required' => false,
                    'validate_callback' => function ($param, $request, $key) {
                        return is_numeric($param);
                    },
                ],
                'name' => [
                    'required' => true,
                    'validate_callback' => function ($param, $request, $key) {
                        return !empty($param) && is_string($param);
                    },
                ],
                'agent_id' => [
                    'required' => true,
                    'validate_callback' => function ($param, $request, $key) {
                        return !empty($param) && is_string($param);
                    },
                ],
            ];
        }
        return [];
    }
}
