<?php

namespace Tests;

use Brain\Monkey\Functions;

defined('ABSPATH') or die();

class ChatControllerTests extends PluginTestCase
{
    public function test_construct()
    {
        $controller = new \JensiAI\Api\ChatController();

        $actual = $this->accessNonPublicProperty($controller, 'namespace');
        $expected = \JensiAI\Main::PREFIX.'/v1';
        $this->assertEquals($expected, $actual);

        $actual = $this->accessNonPublicProperty($controller, 'rest_base');
        $expected = 'chat';
        $this->assertEquals($expected, $actual);
    }

    public function test_chat_endpoints_structure()
    {
        $controller = new \JensiAI\Api\ChatController();
        
        // Mock the register_rest_route function to capture the routes
        $routes = [];
        Functions\when('register_rest_route')
            ->alias(function($namespace, $route, $options) use (&$routes) {
                $routes[] = [
                    'namespace' => $namespace,
                    'route' => $route,
                    'options' => $options
                ];
            });

        // Mock the rest_url function
        Functions\when('rest_url')
            ->returnArg(1);

        $controller->register_routes();

        // Verify that the expected routes were registered
        $this->assertCount(3, $routes);
        
        // Check for send-message route
        $sendMessageRoute = array_filter($routes, function($route) {
            return $route['route'] === '/chat/send-message';
        });
        $this->assertCount(1, $sendMessageRoute);
        
        // Check for get chat route  
        $getChatRoute = array_filter($routes, function($route) {
            return strpos($route['route'], '/chat/(?P<chat_id>') === 0;
        });
        $this->assertCount(1, $getChatRoute);
        
        // Check for create chat route
        $createChatRoute = array_filter($routes, function($route) {
            return $route['route'] === '/chat/create';
        });
        $this->assertCount(1, $createChatRoute);
    }

    public function test_public_permissions_check()
    {
        $controller = new \JensiAI\Api\ChatController();
        
        // Mock WP_REST_Request
        $request = $this->createMock('\WP_REST_Request');
        
        $result = $controller->check_public_permissions($request);
        
        // Should return true for public access
        $this->assertTrue($result);
    }
}
