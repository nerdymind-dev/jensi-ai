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
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/taxonomy',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_taxonomy'],
                    'permission_callback' => [$this, 'get_items_permissions_check'],
                    'args' => [
                        'post_type' => [
                            // 'validate_callback' => function ($param, $request, $key) {
                            //     return is_numeric($param);
                            // },
                            'required' => true
                        ],
                    ]
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
        // example: jensi-ai/v1/configs
        return [
            'all' => esc_url_raw(
                // GET
                rest_url($this->namespace . '/' . $this->rest_base . '/all')
            ),
            'crud' => esc_url_raw(
                // GET/POST/DELETE
                rest_url($this->namespace . '/' . $this->rest_base . '/config')
            ),
            'taxonomy' => esc_url_raw(
                // GET
                rest_url($this->namespace . '/' . $this->rest_base . '/taxonomy')
            ),
        ];
    }

    /**
     * Retrieves taxonomy.
     *
     * @param \WP_REST_Request $request Full details about the request.
     *
     * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_taxonomy($request)
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
            // Get the post type and taxonomy from the request
            $search = $params['search'] ?? null;
            $postType = $params['post_type'] ?? null;
            $taxonomy = $params['taxonomy'] ?? null;

            // Post type is required
            if (!$postType) {
                return rest_ensure_response($response);
            }
            if (!$taxonomy) {
                // Get taxonomies registered for this specific post type
                $taxonomies = get_object_taxonomies($postType, 'names');
                if ($search) {
                    // Filter taxonomies based on search term
                    $taxonomies = array_filter($taxonomies, function ($tax) use ($search) {
                        return str_contains($tax, $search);
                    });
                } else {
                    $response['data'] = $taxonomies;
                }
                $response['data'] = $taxonomies;
                $response['success'] = true;
            } else {
                // Get taxonomies registered for this specific post type
                if ($search) {
                    // Filter terms based on search term
                    $terms = get_terms([
                        'taxonomy' => $taxonomy,
                        'hide_empty' => false,
                        'search' => $search,
                    ]);
                } else {
                    // Get all terms for the specified taxonomy
                    $terms = get_terms([
                        'taxonomy' => $taxonomy,
                        'hide_empty' => false,
                    ]);
                }
                if (!is_wp_error($terms) && !empty($terms)) {
                    $postTerms = array_values($terms);
                    $response['data'] = $postTerms;
                    $response['success'] = true;
                }
            }
        }
        return rest_ensure_response($response);
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
            $tableName = $wpdb->prefix . $this->table_name;

            // Sanitize and prepare data
            $fields = [
                'title' => sanitize_text_field($params['title'] ?? null),
                'post_type' => sanitize_text_field($params['post_type'] ?? null),
                'taxonomy' => sanitize_text_field($params['taxonomy'] ?? null),
                'terms' => json_encode($params['terms'] ?? []),
                'agent_id' => sanitize_text_field($params['agent_id'] ?? null),
                'data_source_id' => sanitize_text_field($params['data_source_id'] ?? null),
                'enabled' => $params['enabled'], // true/false, default true
            ];
            if (!$id) {
                // Create it
                $wpdb->insert($tableName, $fields);
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
                    $updated = $wpdb->update($tableName, $fields, ['id' => $result->id]);
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
                        'validate_callback' => function ($param, $request, $key) {
                            return is_numeric($param);
                        },
                        'required' => true
                    ]
                ];
            case 'update':
                return [
                    'title' => [
                        'validate_callback' => function ($param, $request, $key) {
                            return !empty($param);
                        },
                        'required' => true
                    ],
                    'post_type' => [
                        'validate_callback' => function ($param, $request, $key) {
                            return !empty($param);
                        },
                        'required' => true
                    ],
                    'agent_id' => [
                        'required' => true,
                        'validate_callback' => function ($param, $request, $key) {
                            return !empty($param) && is_string($param);
                        },
                    ],
                    'data_source_id' => [
                        'required' => true,
                        'validate_callback' => function ($param, $request, $key) {
                            return !empty($param) && is_string($param);
                        },
                    ],
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
     * Get config for given post type and terms
     *
     * @param $post_type
     * @param $terms
     * @return false|mixed|\stdClass
     */
    public function get_config_for_terms($post_type, $terms)
    {
        global $wpdb;
        $configs_table = $wpdb->prefix . $this->table_name;
        $results = $wpdb->get_results("SELECT * FROM $configs_table WHERE `enabled` = TRUE AND `post_type` = '$post_type' ORDER BY `created` DESC");
        foreach ($results as $config) {
            if (!$config->taxonomy) {
                // If taxonomy is null, it means all terms are included
                return $config;
            }
            $configTerms = json_decode($config->terms, true);
            if (empty($configTerms)) {
                // If no terms are set, it means all terms are included
                return $config;
            }
            if (!empty(array_intersect($configTerms, $terms))) {
                // If any of the terms match, return this config
                return $config;
            }
        }
        return false;
    }
}
