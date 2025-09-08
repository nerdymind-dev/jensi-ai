<?php

namespace JensiAI;

use JensiAI\Api\SettingController;

/**
 * Frontend pages loader.
 */
class QueueLoader
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
     * Initialize this class.
     */
    public function __construct()
    {
        $this->prefix = \JensiAI\Main::PREFIX;
        $this->table_name = $this->prefix . '_jobs';

        // Get the base API url
        $this->base_api = wp_get_environment_type() === 'local'
            ? 'https://jensi-ai.test/api'
            : 'https://ai.jensi.com/api';
    }

    /**
     * @param $post
     * @param null|string $type
     * @return array|true
     */
    public function store_job($post, $type = null)
    {
        global $wpdb;

        $error = null;

        // Check if post is already in queue
        $existing = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}{$this->table_name} WHERE post_id = %d AND processed = 0", $post->ID));
        if ($existing) {
            // Delete the existing unprocessed job for this post
            $wpdb->delete($wpdb->prefix . $this->table_name, ['id' => $existing->id]);
        }

        // Insert new job
        $result = $wpdb->insert($wpdb->prefix . $this->table_name, [
            'name' => $post->post_title,
            'post_id' => $post->ID,
            'content' => strip_tags($post->post_content),
            'type' => $type,
            // 'processed' => false // populated by default...
        ]);
        if ($result === false) {
            $error = 'Failed to insert job into queue table: ' . $wpdb->last_error;
        }
        return $error === null ? true : $error;
    }

    /**
     * Process any queued up process items
     *
     * @return void
     */
    public function run()
    {
        $this->process_job();
    }

    /**
     * @param $id
     * @return bool
     */
    public function process_job($id = null)
    {
        global $wpdb;
        $queue_table = $wpdb->prefix . $this->table_name;
        $statement = "SELECT * FROM $queue_table";
        if (is_numeric($id)) {
            $id = intval($id);
            $id = "= $id";
        } elseif (is_array($id)) {
            $id = array_map('intval', $id);
            $id = implode(',', $id);
            $id = "IN ($id)";
        } else {
            $id = null;
        }
        $where = $id
            ? " WHERE `processed` = 0 AND `id` $id"
            : " WHERE `processed` = 0";
        $orderBy = " ORDER BY `created` ASC";
        $results = $wpdb->get_results($statement . $where . $orderBy);
        if ($results) {
            foreach ($results as $result) {
                // Flag item as processed (so it doesn't get run again)
                $wpdb->update($queue_table, ['processed' => true], ['id' => $result->id]);
                try {
                    // Load settings
                    $settings = (new SettingController())->get_settings_raw();

                    // Make sure we have the required minimum for connecting to the API
                    if (!$settings['jensi_ai_api_key']) {
                        $wpdb->update($queue_table, [
                            'failed' => true,
                            'errors' => json_encode([
                                'message' => 'No API key configured',
                                'trace' => '',
                                'file' => __CLASS__,
                                'line' => null
                            ])
                        ], ['id' => $result->id]);
                    }
                    if (!$settings['jensi_ai_data_source']) {
                        $wpdb->update($queue_table, [
                            'failed' => true,
                            'errors' => json_encode([
                                'message' => 'No data source configured',
                                'trace' => '',
                                'file' => __CLASS__,
                                'line' => null
                            ])
                        ], ['id' => $result->id]);
                    }

                    // Process the job
                    $url = $this->base_api . '/data-sources/data';
                    $body = [
                        'agent_id' => $settings['jensi_ai_agent'] ?? null,
                        'source_id' => $settings['jensi_ai_data_source'] ?? null,
                        'url' => get_permalink($result->post_id),
                        'data' => $result->content,
                        'metadata' => [
                            // Any custom data...
                            'post_id' => $result->post_id,
                            'type' => $result->type,
                            'name' => $result->name,
                        ],
                        'attachments' => [], // Future use, include post attachments?
                    ];
                    $api_response = wp_remote_post($url, [
                        'headers' => [
                            'Accept' => 'application/json',
                            'Content-Type' => 'application/json',
                            'Authorization' => 'Bearer ' . $settings['jensi_ai_api_key'],
                        ],
                        'timeout' => 60, // give it a bit longer to process
                        'body' => json_encode($body),
                        'sslverify' => wp_get_environment_type() !== 'local',
                    ]);
                    if (is_wp_error($api_response)) {
                        $wpdb->update($queue_table, [
                            'failed' => true,
                            'errors' => json_encode([
                                'message' => $api_response->get_error_message(),
                                'trace' => '',
                                'file' => __CLASS__,
                                'line' => null
                            ])
                        ], ['id' => $result->id]);
                    }
                    $code = wp_remote_retrieve_response_code($api_response);
                    $body = wp_remote_retrieve_body($api_response);
                    $data = json_decode($body, true);
                    if ($code !== 200) {
                        $wpdb->update($queue_table, [
                            'failed' => true,
                            'errors' => json_encode([
                                'message' => $data['message'] ?? __('JENSi AI API returned an error.'),
                                'trace' => '',
                                'file' => __CLASS__,
                                'line' => null
                            ])
                        ], ['id' => $result->id]);
                        return false;
                    } else {
                        if (json_last_error() !== JSON_ERROR_NONE) {
                            $wpdb->update($queue_table, [
                                'failed' => true,
                                'errors' => json_encode([
                                    'message' => 'Failed to parse JENSi AI API response: ' . json_last_error_msg(),
                                    'trace' => '',
                                    'file' => __CLASS__,
                                    'line' => null
                                ])
                            ], ['id' => $result->id]);
                            return false;
                        }

                        // Mark job as successful
                        $wpdb->update($queue_table, [
                            'failed' => false,
                            'meta' => [
                                'message' => $data['message'] ?? 'N/A'
                            ],
                            'errors' => null
                        ], ['id' => $result->id]);
                        return true;
                    }
                } catch (\Exception $e) {
                    $wpdb->update($queue_table, [
                        'failed' => true,
                        'errors' => json_encode([
                            'message' => $e->getMessage(),
                            'trace' => $e->getTraceAsString(),
                            'file' => $e->getFile(),
                            'line' => $e->getLine()
                        ])
                    ], ['id' => $result->id]);
                }
            }
        }
        return false;
    }
}
