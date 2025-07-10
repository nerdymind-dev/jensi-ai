<?php

namespace JensiAI\Api;

use Exception;
use JensiAI\QueueLoader;

/**
 * Backend configs controller.
 */
class QueueController extends \WP_REST_Controller
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
        $this->rest_base = 'jobs';
        $this->table_name = $this->prefix . '_jobs';
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
            '/' . $this->rest_base . '/table',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_queue_table'],
                    'permission_callback' => [$this, 'get_items_permissions_check'],
                    'args' => $this->get_collection_params(),
                ],
            ]
        );
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/table/vpage=(?P<vpage>\d+)',
            [
                [
                    'methods' => \WP_REST_Server::READABLE,
                    'callback' => [$this, 'get_queue_table'],
                    'permission_callback' => [$this, 'get_items_permissions_check'],
                    'args' => $this->get_collection_params('table'),
                ],
            ]
        );
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/job',
            [
                [
                    'methods' => \WP_REST_Server::DELETABLE,
                    'callback' => [$this, 'destroy_job'],
                    'permission_callback' => [$this, 'get_items_permissions_check'],
                    'args' => $this->get_collection_params('destroy'),
                ],
            ]
        );
        register_rest_route(
            $this->namespace,
            '/' . $this->rest_base . '/process',
            [
                [
                    'methods' => \WP_REST_Server::CREATABLE,
                    'callback' => [$this, 'process_job'],
                    'permission_callback' => [$this, 'get_items_permissions_check'],
                    'args' => $this->get_collection_params(),
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
        // example: vwr-live-catalog/v1/settings
        return [
            'table' => esc_url_raw(
            // GET
                rest_url($this->namespace . '/' . $this->rest_base . '/table')
            ),
            'job' => esc_url_raw(
            // DELETE
                rest_url($this->namespace . '/' . $this->rest_base . '/job')
            ),
            'process' => esc_url_raw(
            // POST
                rest_url($this->namespace . '/' . $this->rest_base . '/process')
            )
        ];
    }

    /**
     * Queue table.
     *
     * @param \WP_REST_Request $request Full details about the request.
     *
     * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_queue_table(\WP_REST_Request $request)
    {
        // attempt to parse the json parameter
        $params = $request->get_params();
        $nonce = wp_create_nonce('wp_rest');
        $page = $params['vpage'] ?? 1;
        $perPage = $params['vpagesize'] ?? 15;
        $search = $params['q'] ?? null;
        $order = $params['vsort'] ?? null;
        $sort = $params['vorder'] ?? null;
        $response = rest_ensure_response([
            'data' => [
                'queueTable' => $this->queue_table($page, $perPage, $search, $order, $sort ?? 'DESC')
            ],
            'success' => true,
            'nonce' => $nonce,
        ]);
        return rest_ensure_response($response);
    }

    /**
     * Process first queued item.
     *
     * @param \WP_REST_Request $request Full details about the request.
     *
     * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
     */
    public function process_job(\WP_REST_Request $request)
    {
        // attempt to parse the json parameter
        $params = $request->get_params();
        $nonce = wp_create_nonce('wp_rest');
        $id = $params['id'] ?? null;
        $response = rest_ensure_response([
            'data' => [
                'processed' => (new QueueLoader())->process_job($id)
            ],
            'success' => true,
            'nonce' => $nonce,
        ]);
        return rest_ensure_response($response);
    }

    /**
     * Get WordPress table html from our data
     *
     * @param int $page
     * @param int $perPage
     * @param string|null $search
     * @param string|null $order
     * @param string $sort
     * @return array
     */
    public function queue_table(
        int $page = 1,
        int $perPage = 15,
        string $search = null,
        string $order = null,
        string $sort = 'DESC'
    ) {
        global $wpdb;
        try {
            $queue_table = $wpdb->prefix . $this->table_name;
            $query = "SELECT * FROM $queue_table";
            $total_query = "SELECT COUNT(1) FROM ($query) AS combined_table";
            $total = $wpdb->get_var($total_query);
            $items_per_page = $perPage;
            $p = $page;

            $orderBy = " ORDER BY created DESC";
            if ($order) {
                $orderBy = sprintf(' ORDER BY %s %s', $order, $sort);
            }
            if ($search) {
                $query .= sprintf(' WHERE name LIKE "%%%s%%"', $search);
            }
            $offset = ($p * $items_per_page) - $items_per_page;
            $result = $wpdb->get_results($query . $orderBy . " LIMIT $offset, $items_per_page");

            $total_page = ceil($total / $items_per_page);

            $next_page = $total_page >= ($p + 1) ? $p + 1 : null;
            $prev_page = $p <= 1 ? null : $p - 1;

            $ends_count = 2;  // how many items at the ends (before and after [...])
            $middle_count = 2;  // how many items before and after current page
            $dots = false;
            $links = [];

            for ($i = 1; $i <= $total_page; $i++) {
                if ($i === $p) {
                    $links[] = [
                        'label' => $i,
                        'url' => null,
                        'active' => true
                    ];
                    $dots = true;
                } else if ($i <= $ends_count || ($p && $i >= $p - $middle_count && $i <= $p + $middle_count) || $i > $total - $ends_count) {
                    $links[] = [
                        'label' => $i,
                        'url' => rest_url($this->namespace . '/' . $this->rest_base . "/table/vpage=$i"),
                        'active' => false
                    ];
                    $dots = true;
                } elseif ($dots) {
                    $links[] = [
                        'label' => "...",
                        'url' => null,
                        'active' => false
                    ];
                    $dots = false;
                }
            }

            $total = (int)$total;
            $to = $offset + $items_per_page;
            if ($to > $total) {
                $to = $total;
            }

            return [
                'total' => $total,
                'per_page' => $items_per_page,
                'current_page' => $p,
                'last_page' => $total_page,
                'first_page_url' => rest_url($this->namespace . '/' . $this->rest_base . '/table'),
                'last_page_url' => rest_url($this->namespace . '/' . $this->rest_base . "/table/vpage=$total_page"),
                'next_page_url' => $next_page !== null ? rest_url($this->namespace . '/' . $this->rest_base . "/table/vpage=$next_page") : null,
                'prev_page_url' => $prev_page !== null ? rest_url($this->namespace . '/' . $this->rest_base . "/table/vpage=$prev_page") : null,
                'path' => rest_url($this->namespace . '/' . $this->rest_base . '/table'),
                'from' => $offset + 1,
                'to' => $to,
                'links' => $links,
                'rows' => $result
            ];
        } catch (Exception $e) {
            // Return generic/empty structure on error
            return [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => $page,
                'first_page_url' => rest_url($this->namespace . '/' . $this->rest_base . '/table'),
                'last_page_url' => rest_url($this->namespace . '/' . $this->rest_base . "/table/vpage=1"),
                'next_page_url' => null,
                'prev_page_url' => null,
                'path' => rest_url($this->namespace . '/' . $this->rest_base . '/table'),
                'from' => 0,
                'to' => 0,
                'links' => [],
                'rows' => []
            ];
        }
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
     * Destroy job.
     *
     * @param \WP_REST_Request $request Full details about the request.
     *
     * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
     */
    public function destroy_job(\WP_REST_Request $request)
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
            $result = $id ? $this->get_job_object($id) : null;
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
     * @param $persona_id
     * @return void
     */
    public function destroy_jobs_for_persona($persona_id)
    {
        global $wpdb;
        $jobs_table = $wpdb->prefix . $this->table_name;
        $wpdb->query("DELETE FROM $jobs_table WHERE `persona_id` = $persona_id");
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
            case 'table':
                return [
                    'vpage' => [
                        'validate_callback' => function($param, $request, $key) {
                            return is_numeric($param);
                        },
                        'required' => true
                    ],
                ];
        }
        return [];
    }

    /**
     * Fetch row from the jobs table
     *
     * @param object|null $id
     * @return array|object|\stdClass|null
     */
    public function get_job_object($id = null)
    {
        global $wpdb;
        $jobs_table = $wpdb->prefix . $this->table_name;
        if ($id !== null) {
            // Get specified item from the database
            $result = $wpdb->get_row("SELECT * FROM $jobs_table WHERE `id` = $id");
        } else {
            // Get first item (latest entry) if no ID passed
            $result = $wpdb->get_row("SELECT * FROM $jobs_table ORDER BY `modified` DESC");
        }
        return $result;
    }
}
