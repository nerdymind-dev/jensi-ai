import { mount } from '@vue/test-utils'
import { nextTick } from 'vue'

// Mock Laravel Echo first
jest.mock('laravel-echo', () => {
  return jest.fn(() => ({
    connector: {
      pusher: {
        connection: {
          state: 'disconnected',
          bind: jest.fn(),
        }
      }
    },
    channel: jest.fn(() => ({
      listen: jest.fn()
    })),
    disconnect: jest.fn()
  }))
})

jest.mock('pusher-js', () => jest.fn())

// Import after mocking
import ChatWidget from '../../src/chat-widget/ChatWidget.vue'

// Mock window config
;(window as any).jensi_ai_chat_widget_config = {
  apiBaseUrl: 'https://test.com/api',
  wsBaseUrl: 'test.com:8090',
  defaultAgentId: '123',
  nonce: 'test-nonce'
}

describe('ChatWidget with Laravel Echo', () => {
  let wrapper: any

  beforeEach(() => {
    jest.clearAllMocks()
  })

  afterEach(() => {
    if (wrapper) {
      wrapper.unmount()
    }
  })

  test('should render chat button when closed', () => {
    wrapper = mount(ChatWidget)
    
    const button = wrapper.find('.jensi-ai-chat-button')
    expect(button.exists()).toBe(true)
    
    const chatWindow = wrapper.find('.jensi-ai-chat-window')
    expect(chatWindow.exists()).toBe(false)
  })

  test('should show chat window when opened', async () => {
    wrapper = mount(ChatWidget)
    
    const button = wrapper.find('.jensi-ai-chat-button')
    await button.trigger('click')
    await nextTick()

    const chatWindow = wrapper.find('.jensi-ai-chat-window')
    expect(chatWindow.exists()).toBe(true)
    
    const chatButton = wrapper.find('.jensi-ai-chat-button')
    expect(chatButton.exists()).toBe(false)
  })

  test('should import successfully with Laravel Echo', () => {
    wrapper = mount(ChatWidget)
    expect(wrapper.exists()).toBe(true)
  })
})
