import { createApp } from 'vue'
import ChatWidget from './ChatWidget.vue'
import installShared from '~src/shared'

/**
 * Chat Widget Entry Point
 * 
 * This creates a floating chat widget that can be embedded on any page
 */

// Wait for DOM to be ready
document.addEventListener('DOMContentLoaded', () => {
  // Create the chat widget container
  const widgetContainer = document.createElement('div')
  widgetContainer.id = 'jensi-ai-chat-widget'
  document.body.appendChild(widgetContainer)

  // Create and mount the Vue app
  const app = createApp(ChatWidget)
  
  // Install shared utilities
  installShared(app, 'jensi_ai_chat_widget_config')
  
  // Mount the app
  app.mount('#jensi-ai-chat-widget')
})
