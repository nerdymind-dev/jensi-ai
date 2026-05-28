<?php

namespace Tests;

use JensiAI\AdminLoader;

defined('ABSPATH') or exit();

class AdminLoaderTests extends PluginTestCase
{
    public function test_construct()
    {
        new AdminLoader('test');

        $this->assertTrue(has_action('admin_menu', '\JensiAI\AdminLoader->admin_menu()') > 0);
    }
}
