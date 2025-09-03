## JENSi AI 🤖

Companion plugin for the **JENSi AI** SaaS API. Connect your WordPress to **JENSi AI** for results using RAG.

### Requirements

1. `PHP >= 8`
2. `Node >= 18`

### Getting started

1. `composer install`
2. `npm install`
3. `npm run dev` or `npm run build`

Make sure to edit the `.env` file with the correct variables.
You may also see **HTTPS** errors in the console if using **HTTPS**, in this case you will need to access the HMR server URL directly in your browser and accept the SSL certificate to continue.

If you see an empty admin section, this is most likely the issue.

## Features

- **Floating Button**: A responsive circular button that appears on all front-end pages
- **Expandable Chat Window**: Clean, modern chat interface with conversation history
- **Real-time Streaming**: Uses WebSocket connections for streaming responses
- **Persistent Conversations**: Chat sessions are saved in localStorage and persist across page loads
- **Responsive Design**: Works on desktop and mobile devices
- **Configurable**: Can be enabled/disabled and customized through WordPress admin

## Setup Instructions

### 1. Configure the Plugin

1. Go to **WordPress Admin > JENSi AI > Settings**
2. Enter your **JENSi AI API Key**
3. Select an **Agent** for the chat widget to use
4. Make sure **Enable Chat Widget** is checked
5. Save your settings

### 2. API Endpoints

The chat widget uses these REST API endpoints:

- `POST /wp-json/jensi_ai/v1/chat/send-message` - Send a message to start or continue a chat
- `GET /wp-json/jensi_ai/v1/chat/{chat_id}` - Get chat history and details
- `POST /wp-json/jensi_ai/v1/chat/create` - Create a new chat session (optional)

### 3. WebSocket Configuration

The widget connects to the JENSi AI WebSocket server for real-time message streaming:

- **Local Development**: `wss://jensi-ai.test:8090`
- **Production**: `wss://ai.jensi.com:8090`

WebSocket events:
- `message.streaming` - Partial message content during streaming
- `message.complete` - Final complete message

## How It Works

### Chat Flow

1. **First Visit**: User clicks the floating button and sees a welcome message
2. **Start Chat**: User types a message, which creates a new chat session
3. **Streaming Response**: The AI agent responds with streaming text via WebSocket
4. **Persistent Session**: Chat ID is saved in localStorage for session continuity
5. **Continued Conversation**: Subsequent messages use the existing chat session

### Technical Details

#### Frontend Components

- **ChatWidget.vue**: Main Vue component handling the entire chat interface
- **chat-widget.ts**: Entry point that mounts the widget to the DOM
- **chat-widget.css**: Styled with modern CSS and smooth animations

#### Backend Components

- **ChatController.php**: REST API controller for chat operations
- **ChatWidgetLoader.php**: Handles enqueueing assets and configuration
- **Main.php**: Initializes the chat widget loader

#### Data Storage

- **Chat Sessions**: Stored in JENSi AI cloud (not locally)
- **Chat ID**: Stored in browser localStorage for session persistence
- **Settings**: Stored in WordPress database

### Customization

#### Disable on Specific Pages

You can control where the chat widget appears using the filter:

```php
add_filter('jensi_ai_chat_widget_should_load', function($should_load) {
    // Don't show on checkout pages
    if (is_page('checkout')) {
        return false;
    }
    return $should_load;
});
```

#### Customize Widget Configuration

```php
add_filter('jensi_ai_chat_widget_config', function($config) {
    // Add custom metadata
    $config['customData'] = [
        'user_id' => get_current_user_id(),
        'page_title' => get_the_title()
    ];
    return $config;
});
```

## Troubleshooting

### Chat Widget Not Appearing

1. Check that **Enable Chat Widget** is enabled in settings
2. Verify you have a valid **API Key** configured
3. Make sure an **Agent** is selected
4. Check browser console for JavaScript errors

### WebSocket Connection Issues

1. Verify the WebSocket URL in browser network tab
2. Check for SSL certificate issues in development
3. Ensure firewall/proxy allows WebSocket connections

### API Errors

1. Check WordPress REST API is working: `/wp-json/jensi_ai/v1/chat/`
2. Verify API key has proper permissions
3. Check agent ID is valid and accessible

## Development

### Building Assets

```bash
# Development with hot reload
npm run dev

# Production build
npm run build
```

### File Structure

```
src/chat-widget/
├── chat-widget.ts          # Entry point
├── ChatWidget.vue          # Main component
assets/
├── chat-widget.css         # Styles
includes/
├── Api/ChatController.php  # API endpoints
├── ChatWidgetLoader.php    # Asset loader
```

## API Documentation

### Send Message

```http
POST /wp-json/jensi_ai/v1/chat/send-message
Content-Type: application/json
X-WP-Nonce: {nonce}

{
  "message": "Hello, how can you help me?",
  "chat_id": "optional-existing-chat-id",
  "agent_id": "agent-uuid-if-starting-new-chat"
}
```

### Get Chat Details

```http
GET /wp-json/jensi_ai/v1/chat/{chat_id}
X-WP-Nonce: {nonce}
```

## Security Considerations

- All API calls are nonce-protected
- Chat sessions are scoped to the browser (localStorage)
- No sensitive data is stored locally
- WebSocket connections use secure WSS protocol in production
