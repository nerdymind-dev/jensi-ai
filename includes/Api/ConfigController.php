<?php

namespace JensiAI\Api;

/**
 * Backend configs controller.
 */
class ConfigController extends \WP_REST_Controller
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
        $this->rest_base = 'configs';
        $this->table_name = $this->prefix . '_configs';
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
                    'callback' => [$this, 'get_configs'],
                    'permission_callback' => [$this, 'get_items_permissions_check'],
                    'args' => $this->get_collection_params(),
                ]
            ]
        );
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/config',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_config'],
                    'permission_callback' => [$this, 'get_items_permissions_check'],
                    'args' => $this->get_collection_params('get'),
                ],
                [
                    'methods' => \WP_REST_Server::CREATABLE,
                    'callback' => [$this, 'create_update_config'],
                    'permission_callback' => [$this, 'get_items_permissions_check'],
                    'args' => $this->get_collection_params('update'),
                ],
                [
                    'methods' => \WP_REST_Server::DELETABLE,
                    'callback' => [$this, 'destroy_config'],
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
        // example: vwr-live-catalog/v1/configs
        return [
            'all' => esc_url_raw(
            // GET
                rest_url($this->namespace . '/' . $this->rest_base . '/all')
            ),
            'crud' => esc_url_raw(
            // GET/POST/DELETE
                rest_url($this->namespace . '/' . $this->rest_base . '/config')
            ),
        ];
    }

    /**
     * Retrieves all configs.
     *
     * @param \WP_REST_Request $request Full details about the request.
     *
     * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_configs($request)
    {
        $nonce = wp_create_nonce('wp_rest');
        $response = [
            'data' => $this->get_all_configs(),
            'success' => true,
            'nonce' => $nonce,
        ];
        return rest_ensure_response($response);
    }

    /**
     * Retrieves config.
     *
     * @param \WP_REST_Request $request Full details about the request.
     *
     * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_config($request)
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
        if (isset($params)) {
            $id = $params['id'] ?? null;
            $result = $id ? $this->get_config_object($id) : null;
            if ($result) {
                $response['data'] = $result;
                $response['success'] = true;
            }
        }
        return rest_ensure_response($response);
    }

    /**
     * Update config.
     *
     * @param \WP_REST_Request $request Full details about the request.
     *
     * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
     */
    public function create_update_config(\WP_REST_Request $request)
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
            if (!$id) {
                // Create it
                $wpdb->insert($wpdb->prefix . $this->table_name, [
                    'title' => $params['title'] ?? null,
                    'post_type' => $params['post_type'] ?? null,
                    'sections' => json_encode($params['sections'] ?? []),
                    'terms' => json_encode($params['terms'] ?? []),
                    'enabled' => $params['enabled'], // true/false, default true
                ]);
                $result = $this->get_config_object(); // get the latest item
                if ($result) {
                    // Errors will be an array, else TRUE if stored successfully
                    $response['data'] = $result;
                    $response['success'] = true;
                }
            } else {
                // Update it
                $result = $this->get_config_object($id);
                if ($result) {
                    $updated = $wpdb->update($wpdb->prefix . $this->table_name, [
                        'title' => $params['title'] ?? null,
                        'post_type' => $params['post_type'] ?? null,
                        'sections' => json_encode($params['sections'] ?? []),
                        'terms' => json_encode($params['terms'] ?? []),
                        'enabled' => $params['enabled'], // true/false, default true
                    ], ['id' => $result->id]);
                    if ($updated !== false) {
                        // Get the updated item
                        $result = $this->get_config_object($id);
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
     * Destroy config.
     *
     * @param \WP_REST_Request $request Full details about the request.
     *
     * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
     */
    public function destroy_config(\WP_REST_Request $request)
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
            $id = $params['id'] ?? null;
            $result = $id ? $this->get_config_object($id) : null;
            if ($result) {
                global $wpdb;
                // Destroy the object
                $result = $wpdb->delete($wpdb->prefix . $this->table_name, ['id' => $result->id]);
                $response['success'] = $result !== false;
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
            case 'destroy':
                return [
                    'id' => [
                        'validate_callback' => function($param, $request, $key) {
                            return is_numeric($param);
                        },
                        'required' => true
                    ]
                ];
            case 'update':
                return [
                    'title' => [
                        'validate_callback' => function($param, $request, $key) {
                            return !empty($param);
                        },
                        'required' => true
                    ],
                    'post_type' => [
                        'validate_callback' => function($param, $request, $key) {
                            return !empty($param);
                        },
                        'required' => true
                    ],
                    'sections' => [
                        'validate_callback' => function($param, $request, $key) {
                            return is_array($param) && !empty($param);
                        },
                        'required' => true
                    ],
                    // Should term be required?
                    //'term_id' => [
                    //    'validate_callback' => function($param, $request, $key) {
                    //        return is_numeric($param);
                    //    },
                    //    'required' => true
                    //],
                ];
        }
        return [];
    }

    /**
     * Get all configs
     *
     * @return array|object|\stdClass[]|null
     */
    public function get_all_configs()
    {
        global $wpdb;
        $configs_table = $wpdb->prefix . $this->table_name;
        return $wpdb->get_results("SELECT * FROM $configs_table ORDER BY `created` DESC");
    }

    /**
     * Fetch row from the configs table
     *
     * @param object|null $id
     * @return array|object|\stdClass|null
     */
    public function get_config_object($id = null)
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
     * @param $terms
     * @return false|mixed|\stdClass
     */
    public function get_config_for_terms($terms)
    {
        global $wpdb;
        $configs_table = $wpdb->prefix . $this->table_name;
        $results = $wpdb->get_results("SELECT * FROM $configs_table WHERE `enabled` = TRUE ORDER BY `created` DESC");
        foreach ($results as $config) {
            $configTerms = json_decode($config->terms, true);
            if (!empty(array_intersect($configTerms, $terms))) {
                return $config;
            }
        }
        return false;
    }
}
