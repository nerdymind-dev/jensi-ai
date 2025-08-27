<?php

// don't call the file directly
if (!defined('ABSPATH')) {
    exit;
}

// this allows for using WordPress server-side translation
return [
    'sections' => [
        'general' => __('General', \JensiAI\Main::PREFIX),
        // 'extra' => __('Extra', \JensiAI\Main::PREFIX),
        // 'advanced' => __('Advanced', \JensiAI\Main::PREFIX),
        'debug' => __('Debugging', \JensiAI\Main::PREFIX),
    ],
    'options' => [
        // General
        'jensi_ai_api_key' => [
            'name' => __('API key', \JensiAI\Main::PREFIX),
            'description' => __('Your JENSi AI API key', \JensiAI\Main::PREFIX),
            'section' => 'general',
            'type' => 'password',
            'default' => '',
        ],
        'jensi_ai_data_source' => [
            'name' => __('Data Source', \JensiAI\Main::PREFIX),
            'description' => __('Your JENSi AI data source', \JensiAI\Main::PREFIX),
            'section' => 'general',
            'type' => 'config',
            'default' => '',
        ],
    
        // Extra
        // ...

        // Advanced
        // ...

        // Debugging
        'cleanup_db_on_plugin_uninstall' => [
            'name' => __('Cleanup database upon plugin uninstall', \JensiAI\Main::PREFIX),
            'description' => __('When enabled the plugin will remove any database data upon plugin uninstall.', \JensiAI\Main::PREFIX),
            'section' => 'debug',
            'type' => 'toggle',
            'default' => false,
        ],
        'enable_debug_messages' => [
            'name' => __('Enable Debug Messages', \JensiAI\Main::PREFIX),
            'description' => __('When enabled the plugin will output debug messages in the JavaScript console.', \JensiAI\Main::PREFIX),
            'section' => 'debug',
            'type' => 'toggle',
            'default' => false,
        ],
    ]
];


// Example settings
/*
     'sections' => [
        'general'   => __('General', \JensiAI\Main::PREFIX),
        'advanced'  => __('Advanced', \JensiAI\Main::PREFIX),
        'debugging' => __('Debugging', \JensiAI\Main::PREFIX),
    ],
    'options'  => [
        'input'                          => [
            'name'        => __('Input', \JensiAI\Main::PREFIX),
            'description' => __('Simple text input', \JensiAI\Main::PREFIX),
            'section'     => 'general',
            'type'        => 'text',
            'default'     => '',
        ],
        'email'                          => [
            'name'        => __('Email', \JensiAI\Main::PREFIX),
            'description' => __('Email type input', \JensiAI\Main::PREFIX),
            'section'     => 'general',
            'type'        => 'email',
            'default'     => '',
        ],
        'url'                            => [
            'name'        => __('URL', \JensiAI\Main::PREFIX),
            'description' => __('URL input', \JensiAI\Main::PREFIX),
            'section'     => 'general',
            'type'        => 'url',
            'default'     => '',
        ],
        'color'                          => [
            'name'        => __('Color', \JensiAI\Main::PREFIX),
            'description' => __('Color picker', \JensiAI\Main::PREFIX),
            'section'     => 'general',
            'type'        => 'color',
            'default'     => '#000', // empty text means #000 by default anyway so might as well set it
        ],
        'textarea'                       => [
            'name'        => __('Textarea', \JensiAI\Main::PREFIX),
            'description' => __('Simple textarea', \JensiAI\Main::PREFIX),
            'section'     => 'general',
            'type'        => 'textarea',
            'default'     => '',
        ],
        'dropdown'                       => [
            'name'        => __('Select one', \JensiAI\Main::PREFIX),
            'description' => __('Single select dropdown', \JensiAI\Main::PREFIX),
            'section'     => 'general',
            'type'        => 'dropdown',
            'default'     => 'option1',
            'options'     => ['option1', 'option2', 'option3'],
        ],
        'additional_css'                 => [
            'name'        => __('Additional CSS', \JensiAI\Main::PREFIX),
            'description' => __('Add additional CSS to page', \JensiAI\Main::PREFIX),
            'section'     => 'advanced',
            'type'        => 'code',
            'default'     => '',
        ],
        'enable_debug_messages'          => [
            'name'        => __('Enable Debug Messages', \JensiAI\Main::PREFIX),
            'description' => __('When enabled the plugin will output debug messages in the JavaScript console.', \JensiAI\Main::PREFIX),
            'section'     => 'debugging',
            'type'        => 'toggle',
            'default'     => false,
        ],
        'cleanup_db_on_plugin_uninstall' => [
            'name'        => __('Cleanup database upon plugin uninstall', \JensiAI\Main::PREFIX),
            'description' => __('When enabled the plugin will remove any database data upon plugin uninstall.', \JensiAI\Main::PREFIX),
            'section'     => 'advanced',
            'type'        => 'toggle',
            'default'     => false,
        ],
        'include_post_types'             => [
            'name'            => __('Post Types', \JensiAI\Main::PREFIX),
            'description'     => __('Demo multi-select dropdown', \JensiAI\Main::PREFIX),
            'section'         => 'general',
            'type'            => 'dropdownMultiselect',
            'optionsCallback' => function () {
                return get_post_types('', 'names');
            },
            'default'         => ['post', 'page'],
        ],
    ],
    // ...

 */
