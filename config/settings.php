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
        'widget' => __('Chat Widget', \JensiAI\Main::PREFIX),
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
        'jensi_ai_agent' => [
            'name' => __('Agent', \JensiAI\Main::PREFIX),
            'description' => __('Your JENSi AI agent', \JensiAI\Main::PREFIX),
            'section' => 'general',
            'type' => 'config',
            'default' => '',
        ],
        'jensi_ai_data_source' => [
            'name' => __('Data Source', \JensiAI\Main::PREFIX),
            'description' => __('Your JENSi AI data source', \JensiAI\Main::PREFIX),
            'section' => 'general',
            'type' => 'config',
            'default' => '',
        ],

        // Widget
        'jensi_ai_chat_widget_enabled' => [
            'name' => __('Enable Chat Widget', \JensiAI\Main::PREFIX),
            'description' => __('Enable the floating chat widget on the front-end of your website', \JensiAI\Main::PREFIX),
            'section' => 'widget',
            'type' => 'toggle',
            'default' => true,
        ],
        'welcome_message' => [
            'name'        => __('Welcome message', \JensiAI\Main::PREFIX),
            'description' => __('The welcome message shown when the chat widget is opened', \JensiAI\Main::PREFIX),
            'section'     => 'widget',
            'type'        => 'textarea',
            'default'     => 'Hello! How can I assist you today?',
        ],
        'primary_color'   => [
            'name'        => __('Primary color', \JensiAI\Main::PREFIX),
            'description' => __('The primary color for the widget', \JensiAI\Main::PREFIX),
            'section'     => 'widget',
            'type'        => 'color',
            'format'      => 'hex',
            'default'     => '#667eea',
        ],
        'secondary_color' => [
            'name'        => __('Secondary color', \JensiAI\Main::PREFIX),
            'description' => __('The secondary color for the widget', \JensiAI\Main::PREFIX),
            'section'     => 'widget',
            'type'        => 'color',
            'format'      => 'hex',
            'default'     => '#764ba2',
        ],
        'background_color' => [
            'name'        => __('Background color', \JensiAI\Main::PREFIX),
            'description' => __('The background color for the widget', \JensiAI\Main::PREFIX),
            'section'     => 'widget',
            'type'        => 'color',
            'format'      => 'hex',
            'default'     => '#ffffff',
        ],
        'text_color'      => [
            'name'        => __('Text color', \JensiAI\Main::PREFIX),
            'description' => __('The text color for the widget', \JensiAI\Main::PREFIX),
            'section'     => 'widget',
            'type'        => 'color',
            'format'      => 'hex',
            'default'     => '#000000',
        ],
        'avatar_url'    => [
            'name'        => __('Avatar URL', \JensiAI\Main::PREFIX),
            'description' => __('The avatar image URL for the chat bot (leave empty for default)', \JensiAI\Main::PREFIX),
            'section'     => 'widget',
            'type'        => 'url',
            'default'     => '',
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
