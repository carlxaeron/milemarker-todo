import { mount } from '@vue/test-utils'
import { vi } from 'vitest'

/**
 * Create a mock todo item for testing
 */
export const createMockTodo = (overrides = {}) => ({
  id: 1,
  title: 'Test Todo',
  description: 'Test Description',
  completed: false,
  created_at: '2025-08-15T00:00:00Z',
  updated_at: '2025-08-15T00:00:00Z',
  owner: {
    id: 1,
    name: 'Test User',
    email: 'test@example.com',
    email_verified_at: null,
    created_at: '2025-08-15T00:00:00Z',
    updated_at: '2025-08-15T00:00:00Z'
  },
  general_maps: [
    {
      id: 1,
      mappable_type: 'App\\Models\\User',
      mappable_id: 1,
      related_type: 'App\\Models\\Todo',
      related_id: 1,
      relationship_type: 'todo_owner',
      relationship_key: null,
      metadata: {
        assigned_at: '2025-08-15T00:00:00Z',
        assigned_by: 'api'
      },
      sort_order: 0,
      is_active: true,
      created_at: '2025-08-15T00:00:00Z',
      updated_at: '2025-08-15T00:00:00Z'
    }
  ],
  ...overrides
})

/**
 * Create a mock todos array for testing
 */
export const createMockTodos = (count = 1, overrides = {}) => {
  return Array.from({ length: count }, (_, index) => 
    createMockTodo({
      id: index + 1,
      title: `Test Todo ${index + 1}`,
      ...overrides
    })
  )
}

/**
 * Create a mock todo without owner (for testing edge cases)
 */
export const createMockTodoWithoutOwner = (overrides = {}) => ({
  id: 1,
  title: 'Test Todo',
  description: 'Test Description',
  completed: false,
  created_at: '2025-08-15T00:00:00Z',
  updated_at: '2025-08-15T00:00:00Z',
  owner: null,
  general_maps: [],
  ...overrides
})

/**
 * Mock axios responses for testing
 */
export const mockAxiosResponses = {
  getTodos: (todos = []) => ({
    data: todos
  }),
  
  createTodo: (todo) => ({
    data: { ...todo, id: Date.now(), created_at: new Date().toISOString(), updated_at: new Date().toISOString() }
  }),
  
  updateTodo: (todo) => ({
    data: { ...todo, updated_at: new Date().toISOString() }
  }),
  
  deleteTodo: () => ({
    status: 204
  })
}

/**
 * Mock console methods for testing
 */
export const mockConsole = {
  error: vi.fn(),
  warn: vi.fn(),
  log: vi.fn()
}

/**
 * Mock browser APIs for testing
 */
export const mockBrowserAPIs = {
  confirm: vi.fn(() => true),
  alert: vi.fn(),
  localStorage: {
    getItem: vi.fn(),
    setItem: vi.fn(),
    removeItem: vi.fn(),
    clear: vi.fn()
  }
}

/**
 * Setup global mocks for testing
 */
export const setupGlobalMocks = () => {
  // Mock console
  global.console = { ...console, ...mockConsole }
  
  // Mock browser APIs
  global.confirm = mockBrowserAPIs.confirm
  global.alert = mockBrowserAPIs.alert
  global.localStorage = mockBrowserAPIs.localStorage
  
  // Mock fetch if needed
  global.fetch = vi.fn()
}

/**
 * Clean up global mocks after testing
 */
export const cleanupGlobalMocks = () => {
  vi.clearAllMocks()
  vi.restoreAllMocks()
}

/**
 * Wait for next tick and DOM updates
 */
export const waitForUpdate = async (wrapper) => {
  await wrapper.vm.$nextTick()
  await new Promise(resolve => setTimeout(resolve, 0))
}

/**
 * Simulate user input on form elements
 */
export const simulateUserInput = async (wrapper, selector, value) => {
  const element = wrapper.find(selector)
  await element.setValue(value)
  await element.trigger('input')
  await wrapper.vm.$nextTick()
}

/**
 * Simulate user click on buttons
 */
export const simulateUserClick = async (wrapper, selector) => {
  const element = wrapper.find(selector)
  await element.trigger('click')
  await wrapper.vm.$nextTick()
}

/**
 * Check if element has specific CSS classes
 */
export const hasClasses = (element, classes) => {
  const elementClasses = element.classes()
  return classes.every(cls => elementClasses.includes(cls))
}

/**
 * Check if element has specific attributes
 */
export const hasAttributes = (element, attributes) => {
  return Object.entries(attributes).every(([key, value]) => 
    element.attributes(key) === value
  )
}
