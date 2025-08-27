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
     * The application settings.
     *
     * @var array
     */
    private $settings;

    /**
     * Initialize this class.
     */
    public function __construct()
    {
        $this->prefix = \JensiAI\Main::PREFIX;
        $this->table_name = $this->prefix . '_jobs';

        // Fetch the settings
        $this->settings = (new SettingController())->get_settings_raw();
    }

    /**
     * @param $post
     * @param $config
     * @param null|string $type
     * @return array|true
     */
    public function store_job($post, $config, $type = null)
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
        $where = $id
            ? " WHERE `processed` = 0 AND `id` = $id"
            : " WHERE `processed` = 0";
        $orderBy = " ORDER BY `created` ASC";
        $result = $wpdb->get_row($statement . $where . $orderBy);

        if ($result) {
            // Flag item as processed (so it doesn't get run again)
            $wpdb->update($queue_table, ['processed' => true], ['id' => $result->id]);
            try {
                

                // @TODO: Implement the logic to process the job.
                dump([
                    'message' => 'TEST QUEUE WORKING: not yet implemented...',
                    'job' => $result
                ]);

                // If here, assume the queue job didn't complete successfully
                $wpdb->update($queue_table, [
                    'failed' => true,
                    'errors' => json_encode([
                        'message' => 'TEST QUEUE WORKING: not yet implemented...',
                        'trace' => '',
                        'file' => __CLASS__,
                        'line' => null
                    ])
                ], ['id' => $result->id]);
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
        return false;
    }
}
