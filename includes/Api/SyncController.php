<?php

namespace JensiAI\Api;

use JensiAI\QueueLoader;

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

        // See if we're running all, or just for a specific config
        $config_id = $params['id'] ?? null;

        // Init the config controller
        $controller = new ConfigController();
        $configs = $config_id
            ? [$controller->get_config_object($config_id)]
            : $controller->get_all_configs();

        // Get all matching entries for each config
        $post_ids = [];
        $posts_to_sync = [];
        foreach ($configs as $config) {
            if (empty($config) || empty($config->id)) {
                continue;
            }

            // Get posts matching this config
            $post_type = $config->post_type;
            $taxonomy = $config->taxonomy; // or null/empty for all
            $terms = $config->terms; // array of term IDs, or null/empty for all

            // Build the query args
            $args = [
                'post_type' => $post_type,
                'post_status' => 'publish', // only published posts
                'numberposts' => -1,
                'fields' => 'ID,post_title,post_date,post_content,post_type,post_status',
            ];

            // Add taxonomy query if needed
            if (!empty($taxonomy)) {
                $args['tax_query'] = [
                    [
                        'taxonomy' => $taxonomy,
                        'field' => 'term_id',
                    ],
                ];
                if (!empty($terms) && is_array($terms)) {
                    $args['tax_query'][0]['terms'] = $terms;
                    $args['tax_query'][0]['operator'] = 'IN';
                } else {
                    $args['tax_query'][0]['operator'] = 'EXISTS';
                }
            }

            // Don't include existing posts that have already been fetched
            if (!empty($post_ids)) {
                $args['post__not_in'] = $post_ids;
            }

            $posts = get_posts($args);
            foreach ($posts as $post) {
                // Keep track of processed posts
                $post_ids[] = $post->ID;

                // Add to sync list as a simple object
                $posts_to_sync[] = (object)[
                    'ID' => $post->ID,
                    'post_title' => $post->post_title,
                    'post_date' => $post->post_date,
                    'post_content' => $post->post_content,
                    'post_type' => $post->post_type,
                    'post_status' => $post->post_status,
                ];
            }
        }

        // Setup the queue for each post
        $loader = new QueueLoader();
        $errors = [];
        foreach ($posts_to_sync as $post) {
            // Queue each post for syncing
            $result = $loader->store_job($post, $post->post_type);
            if ($result !== true) {
                $errors[] = $result;
            }
        }

        $nonce = wp_create_nonce('wp_rest');
        $response = rest_ensure_response([
            'data' => [],
            'errors' => $errors,
            'success' => empty($errors),
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
