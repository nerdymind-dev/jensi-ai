<template>
  <div class="jensi-ai-chat-widget">
    <!-- Floating Button -->
    <button
      v-if="!isOpen"
      @click="toggleWidget"
      class="jensi-ai-chat-button"
      :class="{ 'jensi-ai-chat-button--pulsing': shouldPulse }"
      aria-label="Open chat with AI assistant"
    >
      <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        fill="currentColor"
        class="jensi-ai-chat-icon"
      >
        <path
          d="M20 2H4C2.9 2 2 2.9 2 4V22L6 18H20C21.1 18 22 17.1 22 16V4C22 2.9 21.1 2 20 2ZM20 16H5.17L4 17.17V4H20V16Z"
        />
        <path d="M7 9H17V11H7V9ZM7 12H14V14H7V12Z" />
      </svg>
    </button>

    <!-- Chat Window -->
    <div
      v-if="isOpen"
      class="jensi-ai-chat-window"
      :class="{ 'jensi-ai-chat-window--opening': isOpening }"
    >
      <!-- Header -->
      <div class="jensi-ai-chat-header">
        <div class="jensi-ai-chat-agent-info">
          <div class="jensi-ai-chat-agent-avatar">
            <template v-if="config.avatarUrl && config.avatarUrl.length > 0">
              <img :src="config.avatarUrl" alt="Agent Avatar" />
            </template>
            <template v-else>
              {{ currentAgent?.name?.charAt(0).toUpperCase() || 'AI' }}
            </template>
          </div>
          <div>
            <h3 class="jensi-ai-chat-agent-name">
              {{ currentAgent?.name || 'AI Assistant' }}
            </h3>
            <p class="jensi-ai-chat-agent-status" :class="statusClass">
              {{ statusText }}
            </p>
          </div>
        </div>
        <button
          @click="toggleWidget"
          class="jensi-ai-chat-close"
          aria-label="Close chat"
        >
          <svg
            xmlns="http://www.w3.org/2000/svg"
            viewBox="0 0 24 24"
            fill="currentColor"
          >
            <path
              d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"
            />
          </svg>
        </button>
      </div>

      <!-- Messages -->
      <div ref="messagesContainer" class="jensi-ai-chat-messages">
        <div
          v-for="message in messages"
          :key="message.id"
          class="jensi-ai-chat-message"
          :class="{
            'jensi-ai-chat-message--user': message.type === 'user',
            'jensi-ai-chat-message--assistant': message.type === 'assistant',
          }"
        >
          <div class="jensi-ai-chat-message-content">
            <div v-if="message.type === 'assistant'" class="jensi-ai-chat-message-avatar">
              <template v-if="config.avatarUrl && config.avatarUrl.length > 0">
                <img :src="config.avatarUrl" alt="Agent Avatar" />
              </template>
              <template v-else>
                {{ currentAgent?.name?.charAt(0).toUpperCase() || 'AI' }}
              </template>
            </div>
            <div class="jensi-ai-chat-message-text">
              <span v-html="message.message"></span>
            </div>
          </div>
          <div class="jensi-ai-chat-message-time">
            {{ formatTime(message.created_at) }}
          </div>
        </div>

        <!-- Typing indicator -->
        <div
          v-if="isTyping"
          class="jensi-ai-chat-message jensi-ai-chat-message--assistant"
        >
          <div class="jensi-ai-chat-message-content">
            <div class="jensi-ai-chat-message-avatar">
              <template v-if="config.avatarUrl && config.avatarUrl.length > 0">
                <img :src="config.avatarUrl" alt="Agent Avatar" />
              </template>
              <template v-else>
                {{ currentAgent?.name?.charAt(0).toUpperCase() || 'AI' }}
              </template>
            </div>
            <div class="jensi-ai-chat-typing-indicator">
              <span></span>
              <span></span>
              <span></span>
            </div>
          </div>
        </div>

        <!-- Welcome message -->
        <div
          v-if="messages.length === 0 && !isTyping"
          class="jensi-ai-chat-welcome"
        >
          <div class="jensi-ai-chat-welcome-avatar">
            <template v-if="config.avatarUrl && config.avatarUrl.length > 0">
              <img :src="config.avatarUrl" alt="Agent Avatar" />
            </template>
            <template v-else>
              {{ currentAgent?.name?.charAt(0).toUpperCase() || 'AI' }}
            </template>
          </div>
          <div class="jensi-ai-chat-welcome-content">
            <h4>Welcome!</h4>
            <p>{{ config.welcomeMessage }}</p>
          </div>
        </div>
      </div>

      <!-- Input -->
      <div class="jensi-ai-chat-input-container">
        <form @submit.prevent="sendMessage" class="jensi-ai-chat-input-form">
          <input
            ref="messageInput"
            v-model="currentMessage"
            type="text"
            id="message-input"
            placeholder="Type your message..."
            class="jensi-ai-chat-input"
            :disabled="isLoading"
            maxlength="10000"
          />
          <button
            type="submit"
            class="jensi-ai-chat-send"
            :disabled="!currentMessage.trim() || isLoading"
            aria-label="Send message"
          >
            <svg
              v-if="!isLoading"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 24 24"
              fill="currentColor"
            >
              <path d="M2,21L23,12L2,3V10L17,12L2,14V21Z" />
            </svg>
            <div v-else class="jensi-ai-chat-loading-spinner"></div>
          </button>
        </form>
        <div class="jensi-ai-chat-actions">
          <button
            @click="clearChat"
            class="jensi-ai-chat-action"
            :disabled="isLoading"
          >
            Clear Chat
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, inject, reactive, computed, onMounted, nextTick, watch } from 'vue'
import Echo from 'laravel-echo'
import Pusher from 'pusher-js'

// Extend window interface for Pusher
declare global {
  interface Window {
    Pusher: typeof Pusher
  }
}

// Make Pusher available globally for Laravel Echo
window.Pusher = Pusher

const axios = inject('axios') as any
const win = inject('win') as any

// Types
interface Message {
  id: string
  chat_id: string
  type: 'user' | 'assistant'
  message: string
  metadata?: any[]
  created_at: string
  updated_at: string
}

interface Agent {
  id: string
  name: string
  description?: string
}

interface WebSocketConfig {
  channel: string
  app_id: string
  app_key: string
}

interface Chat {
  id: string
  agent_id: string
  status: string
  started_at: string
  ended_at?: string
  metadata?: any[]
  created_at: string
  updated_at: string
  agent: Agent
  messages: Message[]
  websocket: WebSocketConfig
}

// State
const isOpen = ref(false)
const isOpening = ref(false)
const isLoading = ref(false)
const isTyping = ref(false)
const shouldPulse = ref(true)
const currentMessage = ref('')
const currentChat = ref<Chat | null>(null)
const currentAgent = ref<Agent | null>(null)
const messages = ref<Message[]>([])
const echo = ref<any>(null)
const isConnected = ref(false)
const messagesContainer = ref<HTMLElement>()
const messageInput = ref<HTMLInputElement>()

// Configuration (will be populated by WordPress)
const config = reactive({
  apiBaseUrl: '',
  wsBaseUrl: '',
  defaultAgentId: '',
  welcomeMessage: 'How can I help you today?',
  avatarUrl: '',
  nonce: '',
})

// Computed
const statusClass = computed(() => {
  if (isConnected.value) {
    return 'jensi-ai-chat-status--online'
  }
  if (messages.value.length === 0 && !isTyping.value) {
    return 'jensi-ai-chat-status--waiting'
  }
  return 'jensi-ai-chat-status--offline'
})

const statusText = computed(() => {
  if (isConnected.value) {
    return 'Connected'
  }
  if (isLoading.value) {
    return 'Connecting...'
  }
  if (messages.value.length === 0 && !isTyping.value) {
    return 'Ready...'
  }
  return 'Offline'
})

// Methods
const toggleWidget = async () => {
  if (isOpen.value) {
    closeWidget()
  } else {
    await openWidget()
  }
}

const openWidget = async () => {
  isOpening.value = true
  isOpen.value = true
  shouldPulse.value = false
  
  // Load saved chat or initialize new one
  await initializeChat()
  
  // Focus input after opening
  await nextTick()
  if (messageInput.value) {
    messageInput.value.focus()
  }
  
  // Animation cleanup
  setTimeout(() => {
    isOpening.value = false
  }, 300)
}

const closeWidget = () => {
  isOpen.value = false
  isOpening.value = false
  if (echo.value) {
    echo.value.disconnect()
    echo.value = null
    isConnected.value = false
  }
}

const initializeChat = async () => {
  try {
    // Check for saved chat ID
    const savedChatId = localStorage.getItem('jensi_ai_chat_id')
    if (savedChatId && savedChatId !== undefined) {
      // Try to load existing chat
      await loadChat(savedChatId)
    } else {
      // Start fresh - we'll create chat when first message is sent
      currentAgent.value = await getDefaultAgent()
    }
  } catch (error) {
    console.error('Failed to initialize chat:', error)
    // Fallback to default agent
    currentAgent.value = await getDefaultAgent()
  }
}

const getDefaultAgent = async (): Promise<Agent> => {
  // If we have a default agent ID from settings, try to fetch its details
  if (config.defaultAgentId) {
    try {
      const response = await axios.get(`${config.apiBaseUrl}/chat/agent/${config.defaultAgentId}`)
      if (response.data.success && response.data.data) {
        const agent = response.data.data || null
        if (agent) {
          return agent
        }
      }
    } catch (error) {
      console.error('Failed to fetch default agent:', error)
    }
  }
  
  // Fallback to a default agent
  return {
    id: config.defaultAgentId || 'default',
    name: 'AI Assistant',
    description: 'I\'m here to help answer your questions!'
  }
}

const loadChat = async (chatId: string) => {
  isLoading.value = true
  
  try {
    const response = await axios.get(`${config.apiBaseUrl}/chat/${chatId}`)
  
    if (!response.data.success) {
      throw new Error('Failed to load chat')
    }    

    currentChat.value = response.data.data
    currentAgent.value = response.data.data.agent
    messages.value = response.data.data.messages || []
    
    // Connect to websocket
    connectWebSocket(response.data.data.websocket)
    
    // Scroll to bottom
    await nextTick()
    scrollToBottom()
  } catch (error) {
    console.error('Error loading chat:', error)
    // Clear invalid chat ID and start fresh
    localStorage.removeItem('jensi_ai_chat_id')
    currentAgent.value = await getDefaultAgent()
  } finally {
    isLoading.value = false
  }
}

const sendMessage = async () => {
  if (!currentMessage.value.trim() || isLoading.value) {
    return
  }
  
  const messageText = currentMessage.value.trim()
  currentMessage.value = ''
  isLoading.value = true
  
  // Add user message immediately
  const userMessage: Message = {
    id: `temp-${Date.now()}`,
    chat_id: currentChat.value?.id || '',
    type: 'user',
    message: messageText,
    created_at: new Date().toISOString(),
    updated_at: new Date().toISOString(),
  }
  
  messages.value.push(userMessage)
  await nextTick()
  scrollToBottom()
  
  try {
    const payload: any = {
      message: messageText,
    }
    
    if (currentChat.value) {
      payload.chat_id = currentChat.value.id
    } else if (config.defaultAgentId) {
      payload.agent_id = config.defaultAgentId
    } else {
      throw new Error('No agent or chat configured')
    }
    
    const response = await axios.post(`${config.apiBaseUrl}/chat/send-message`, payload)

    if (!response.data.success) {
      throw new Error('Failed to send message')
    }
    
    // Update user message with real ID
    const sentMessage = response.data.data
    userMessage.id = sentMessage.id
    userMessage.chat_id = sentMessage.chat_id
    
    // Save chat info
    if (!currentChat.value) {
      currentChat.value = sentMessage.chat
      localStorage.setItem('jensi_ai_chat_id', sentMessage.chat_id)
      
      // Connect to websocket for streaming response
      connectWebSocket(sentMessage.chat.websocket)
    }
    
    // Show typing indicator
    isTyping.value = true
    await nextTick()
    scrollToBottom()
  } catch (error) {
    console.error('Error sending message:', error)
    // Remove the failed message
    messages.value = messages.value.filter(m => m.id !== userMessage.id)
    
    // Show error message
    const errorMessage: Message = {
      id: `error-${Date.now()}`,
      chat_id: currentChat.value?.id || '',
      type: 'assistant',
      message: 'Sorry, I encountered an error. Please try again.',
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString(),
    }
    messages.value.push(errorMessage)
    
    await nextTick()
    scrollToBottom()
  } finally {
    isLoading.value = false
  }
}

const connectWebSocket = (websocketConfig: WebSocketConfig) => {
  // Disconnect existing Echo instance
  if (echo.value) {
    echo.value.disconnect()
    isConnected.value = false
  }
  
  try {
    // Get host from config - remove protocol if present
    const host = config.wsBaseUrl.replace(/^wss?:\/\//, '')
    
    // Create new Echo instance
    echo.value = new Echo({
      broadcaster: 'reverb',
      key: websocketConfig.app_key,
      wsHost: host.split(':')[0],
      wsPort: host.includes(':') ? parseInt(host.split(':')[1]) : 80,
      wssPort: host.includes(':') ? parseInt(host.split(':')[1]) : 443,
      forceTLS: config.wsBaseUrl.startsWith('wss'),
      enabledTransports: ['ws', 'wss'],
    })
    
    // Listen for connection events
    echo.value.connector.pusher.connection.bind('connected', () => {
      isConnected.value = true
    })
    
    echo.value.connector.pusher.connection.bind('disconnected', () => {
      isConnected.value = false
    })
    
    echo.value.connector.pusher.connection.bind('error', (error: any) => {
      console.error('Echo connection error:', error)
      isConnected.value = false
    })
    
    // Subscribe to the chat channel
    const channel = echo.value.channel(websocketConfig.channel)
    
    // Listen for message streaming events
    channel.listen('.message.chunk', (data: any) => {
        if (data.is_complete) {
            handleCompleteMessage(data)
        } else {
            handleStreamingMessage(data)
        }
    })
  } catch (error) {
    console.error('Failed to connect to Laravel Echo:', error)
  }
}

const handleStreamingMessage = (data: any) => {
  // Find or create assistant message
  let assistantMessage = messages.value.find(
    m => m.type === 'assistant' && m.id === data.message_id
  )
  
  if (!assistantMessage) {
    assistantMessage = {
      id: data.message_id,
      chat_id: data.chat_id,
      type: 'assistant',
      message: '',
      created_at: new Date().toISOString(),
      updated_at: new Date().toISOString(),
    }
    
    // Remove typing indicator
    isTyping.value = false
    
    messages.value.push(assistantMessage)
  }
  
  // Append streamed content
  assistantMessage.message += data.chunk || ''

  // Scroll to bottom
  nextTick(() => scrollToBottom())
}

const handleCompleteMessage = (data: any) => {
  // Find assistant message and mark as complete
  const assistantMessage = messages.value.find(
    m => m.type === 'assistant' && m.id === data.message_id
  )
  
  if (assistantMessage) {
    assistantMessage.message = data.full_message
    assistantMessage.updated_at = data.timestamp
  }
  
  isTyping.value = false
  
  // Scroll to bottom
  nextTick(() => scrollToBottom())
}

const clearChat = () => {
  if (isLoading.value) return
  
  if (confirm('Are you sure you want to clear this chat? This action cannot be undone.')) {
    localStorage.removeItem('jensi_ai_chat_id')
    messages.value = []
    currentChat.value = null
    
    if (echo.value) {
      echo.value.disconnect()
      echo.value = null
      isConnected.value = false
    }
    
    initializeChat()
  }
}

const scrollToBottom = () => {
  if (messagesContainer.value) {
    messagesContainer.value.scrollTop = messagesContainer.value.scrollHeight
  }
}

const formatTime = (timestamp: string) => {
  const date = new Date(timestamp)
  return date.toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' })
}

// Lifecycle
onMounted(() => {
  // Get config from WordPress
  const wpConfig = (window as any).jensi_ai_chat_widget_config
  if (wpConfig) {
    Object.assign(config, wpConfig)
  }
  
  if (!win.$appConfig.nonce) {
    win.$appConfig.nonce = config.nonce
  }

  // Set pulse animation to stop after a few seconds
  setTimeout(() => {
    shouldPulse.value = false
  }, 5000)
})

// Watch for window resize to adjust widget position
watch(isOpen, (newVal) => {
  if (newVal) {
    // Could add window resize handlers here if needed
  }
})
</script>
