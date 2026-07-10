import { createApp } from 'vue'
import ChatWidget from './ChatWidget.vue'
import installShared from '~src/shared'
import { Vue3ProgressPlugin } from '@marcoschulte/vue3-progress';

/**
 * Chat Widget Entry Point
 *
 * This creates a floating chat widget that can be embedded on any page
 */

const init = () => {
  // Create the chat widget container
  const widgetContainer = document.createElement('div')
  widgetContainer.id = 'jensi-ai-chat-widget'
  document.body.appendChild(widgetContainer)

  // Create and mount the Vue app
  const app = createApp(ChatWidget)

  // Install shared utilities
  installShared(app, 'jensi_ai_chat_widget_config')

  app.use(Vue3ProgressPlugin, {
    // ...
  })

  // Mount the app
  app.mount('#jensi-ai-chat-widget')
}

// Guard against DOMContentLoaded having already fired (e.g. when scripts are lazy-loaded)
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', init)
} else {
  init()
}
