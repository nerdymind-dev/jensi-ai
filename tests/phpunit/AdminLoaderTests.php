<?php

namespace Tests;

defined('ABSPATH') or die();

class AdminLoaderTests extends PluginTestCase
{
    public function test_construct()
    {
        new \JensiAI\AdminLoader('test');

        $this->assertTrue(has_action('admin_menu', '\JensiAI\AdminLoader->admin_menu()') > 0);
    }
}
