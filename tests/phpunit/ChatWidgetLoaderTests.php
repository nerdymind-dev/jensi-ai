<?php

namespace Tests;

use Brain\Monkey\Functions;
use Brain\Monkey\Actions;

defined('ABSPATH') or die();

class ChatWidgetLoaderTests extends PluginTestCase
{
    public function test_construct()
    {
        // Mock the is_admin function to return false (frontend)
        Functions\when('is_admin')->justReturn(false);
        
        // Expect wp_enqueue_scripts action to be added
        Actions\expectAdded('wp_enqueue_scripts');
        
        new \JensiAI\ChatWidgetLoader('test');
        
        $this->assertTrue(true); // If we get here without errors, the test passes
    }

    public function test_should_load_widget_with_no_api_key()
    {
        Functions\when('is_admin')->justReturn(false);
        Functions\when('is_login')->justReturn(false);
        Functions\when('wp_doing_ajax')->justReturn(false);
        Functions\when('wp_doing_cron')->justReturn(false);
        
        // Mock SettingController to return empty settings
        $mockSettingController = $this->getMockBuilder('\JensiAI\Api\SettingController')
            ->getMock();
        $mockSettingController->method('get_settings_raw')
            ->willReturn([]);
        
        $loader = new \JensiAI\ChatWidgetLoader('test');
        
        // Use reflection to test the private method
        $reflection = new \ReflectionClass($loader);
        $method = $reflection->getMethod('should_load_widget');
        $method->setAccessible(true);
        
        $result = $method->invoke($loader);
        
        // Should return false when no API key is configured
        $this->assertFalse($result);
    }

    public function test_widget_config_structure()
    {
        Functions\when('is_admin')->justReturn(false);
        Functions\when('wp_get_environment_type')->justReturn('local');
        Functions\when('rest_url')->returnArg(1);
        Functions\when('wp_create_nonce')->returnArg(1);
        
        // Mock SettingController to return test settings
        $mockSettingController = $this->getMockBuilder('\JensiAI\Api\SettingController')
            ->getMock();
        $mockSettingController->method('get_settings_raw')
            ->willReturn([
                'jensi_ai_api_key' => 'test-key',
                'jensi_ai_agent' => 'test-agent-id',
                'jensi_ai_chat_widget_enabled' => true
            ]);
        
        $loader = new \JensiAI\ChatWidgetLoader('test');
        
        // Use reflection to test the private method
        $reflection = new \ReflectionClass($loader);
        $method = $reflection->getMethod('get_widget_config');
        $method->setAccessible(true);
        
        $config = $method->invoke($loader);
        
        // Verify config structure
        $this->assertArrayHasKey('apiBaseUrl', $config);
        $this->assertArrayHasKey('wsBaseUrl', $config);
        $this->assertArrayHasKey('nonce', $config);
        $this->assertArrayHasKey('defaultAgentId', $config);
        $this->assertArrayHasKey('pluginUrl', $config);
        
        // Verify values
        $this->assertEquals('test-agent-id', $config['defaultAgentId']);
        $this->assertStringContainsString('jensi-ai.test', $config['wsBaseUrl']);
    }
}
