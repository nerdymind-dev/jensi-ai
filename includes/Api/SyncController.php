<?php

namespace JensiAI\Api;

/**
 * Backend configs controller.
 */
class SyncController extends \WP_REST_Controller
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
     * @var ?string
     */
    private $table_name;

    /**
     * Initialize this class.
     */
    public function __construct()
    {
        $this->prefix = \JensiAI\Main::PREFIX;
        $this->namespace = $this->prefix . '/v1';
        $this->rest_base = 'sync';
        $this->table_name = null;
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
            '/' . $this->rest_base,
            [
                [
                    'methods' => \WP_REST_Server::CREATABLE,
                    'callback' => [$this, 'sync'],
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
        // example: jensi-ai/v1/sync
        return esc_url_raw(
            // GET/POST
            rest_url($this->namespace . '/' . $this->rest_base)
        );
    }

    /**
     * Queue up all valid posts for syncing with the AI service.
     *
     * @param \WP_REST_Request $request Full details about the request.
     *
     * @return \WP_REST_Response|\WP_Error Response object on success, or WP_Error object on failure.
     */
    public function sync(\WP_REST_Request $request)
    {
        // attempt to parse the json parameter
        $params = $request->get_params();

        // @TODO...

        dump('SyncController::sync() called with params:');
        dump($params);

        $nonce = wp_create_nonce('wp_rest');
        $response = rest_ensure_response([
            'data' => [],
            'success' => true,
            'nonce' => $nonce,
        ]);
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
            case 'create':
                return [
                    // Any variables required for this one?
                    // 'id' => [
                    //     'validate_callback' => function ($param, $request, $key) {
                    //         return is_numeric($param);
                    //     },
                    //     'required' => true
                    // ]
                ];
        }
        return [];
    }
}
