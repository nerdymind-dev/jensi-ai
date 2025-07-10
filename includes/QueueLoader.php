<?php

namespace JensiAI;

use JensiAI\Api\PersonasController;
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
     * Initialize this class.
     */
    public function __construct()
    {
        $this->prefix = \JensiAI\Main::PREFIX;
        $this->table_name = $this->prefix . '_jobs';
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

        // One for each enabled social?
        $sections = json_decode($config->sections);
        $errors = [];
        foreach ($sections as $section) {
            if ($type && $section->type !== $type) {
                continue;
            }
            $result = $wpdb->insert($wpdb->prefix . $this->table_name, [
                'name' => $post->post_title,
                'post_id' => $post->ID,
                'content' => $post->post_content,
                'type' => $section->type,
                // 'persona_id' => (int)$section->persona_id, // not using personas, will be something else, leaving as example
                // 'processed' => false // populated by default...
            ]);
            if ($result === false) {
                $errors[] = $section;
            }
        }
        return count($errors) === 0 ? true : $errors;
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
                /**
                 * Here is where you would implement the logic to process the job.
                 */

                // @TODO: Implement the logic to process the job.

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
