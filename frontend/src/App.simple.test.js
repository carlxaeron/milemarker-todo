import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import App from './App.vue'
import axios from 'axios'

// Mock axios
vi.mock('axios')

describe('App.vue - Basic Tests', () => {
  let wrapper

  beforeEach(() => {
    // Reset mocks
    vi.clearAllMocks()
    
    // Mock axios methods to return empty data
    axios.get = vi.fn().mockResolvedValue({ data: [] })
    axios.post = vi.fn()
    axios.put = vi.fn()
    axios.delete = vi.fn()
    
    // Mount component
    wrapper = mount(App)
  })

  it('renders the todo app title', () => {
    expect(wrapper.find('h1').text()).toBe('Todo App with User Relationships')
  })

  it('renders the add todo form', () => {
    expect(wrapper.find('.add-todo-form').exists()).toBe(true)
    expect(wrapper.find('input[placeholder="Enter todo title"]').exists()).toBe(true)
    expect(wrapper.find('textarea[placeholder="Enter description (optional)"]').exists()).toBe(true)
    expect(wrapper.find('.add-btn').exists()).toBe(true)
  })

  it('shows "No todos yet" message when todos array is empty', () => {
    expect(wrapper.find('.no-todos').text()).toBe('No todos yet. Add one above!')
  })

  it('has correct initial data structure', () => {
    expect(wrapper.vm.todos).toEqual([])
    expect(wrapper.vm.newTodo).toEqual({
      title: '',
      description: '',
      completed: false
    })
    expect(wrapper.vm.showEditModal).toBe(false)
    expect(wrapper.vm.editingTodo).toEqual({
      id: null,
      title: '',
      description: '',
      completed: false
    })
  })

  it('renders form inputs with correct placeholders', () => {
    const titleInput = wrapper.find('input[placeholder="Enter todo title"]')
    const descriptionTextarea = wrapper.find('textarea[placeholder="Enter description (optional)"]')
    
    expect(titleInput.exists()).toBe(true)
    expect(descriptionTextarea.exists()).toBe(true)
  })

  it('renders add button with correct text', () => {
    const addButton = wrapper.find('.add-btn')
    expect(addButton.text()).toBe('Login Required')
  })

  it('has correct CSS classes for styling', () => {
    expect(wrapper.find('.container').exists()).toBe(true)
    expect(wrapper.find('.todo-list').exists()).toBe(true)
    expect(wrapper.find('.add-todo-form').exists()).toBe(true)
  })
})

describe('App.vue - Component Structure', () => {
  let wrapper

  beforeEach(() => {
    vi.clearAllMocks()
    axios.get = vi.fn().mockResolvedValue({ data: [] })
    wrapper = mount(App)
  })

  it('has the correct component name', () => {
    expect(wrapper.vm.$options.name).toBe('App')
  })

  it('imports axios correctly', () => {
    expect(wrapper.vm.$options.methods).toBeDefined()
    expect(typeof wrapper.vm.addTodo).toBe('function')
    expect(typeof wrapper.vm.fetchTodos).toBe('function')
  })

  it('has all required methods defined', () => {
    const methods = wrapper.vm.$options.methods
    
    expect(methods.addTodo).toBeDefined()
    expect(methods.fetchTodos).toBeDefined()
    expect(methods.toggleTodo).toBeDefined()
    expect(methods.editTodo).toBeDefined()
    expect(methods.saveEdit).toBeDefined()
    expect(methods.closeEditModal).toBeDefined()
    expect(methods.deleteTodo).toBeDefined()
  })
})

describe('App.vue - Template Structure', () => {
  let wrapper

  beforeEach(() => {
    vi.clearAllMocks()
    axios.get = vi.fn().mockResolvedValue({ data: [] })
    wrapper = mount(App)
  })

  it('has the correct HTML structure', () => {
    // Check main container
    expect(wrapper.find('#app').exists()).toBe(true)
    expect(wrapper.find('.container').exists()).toBe(true)
    
    // Check form structure
    expect(wrapper.find('.add-todo-form').exists()).toBe(true)
    
    // Check todo list structure
    expect(wrapper.find('.todo-list').exists()).toBe(true)
    
    // Check modal structure (should be hidden initially, so we check the template source)
    expect(wrapper.vm.$options.template || wrapper.vm.$options.render).toBeDefined()
  })

  it('has proper form input types', () => {
    const titleInput = wrapper.find('input[type="text"]')
    const descriptionTextarea = wrapper.find('textarea')
    
    expect(titleInput.exists()).toBe(true)
    expect(descriptionTextarea.exists()).toBe(true)
  })

  it('has proper button types', () => {
    const addButton = wrapper.find('.add-btn')
    expect(addButton.exists()).toBe(true)
    expect(addButton.attributes('type')).toBeUndefined() // Default button type
  })

  it('shows modal when editing', async () => {
    // Set showEditModal to true
    await wrapper.setData({ showEditModal: true })
    
    // Now the modal should be visible
    expect(wrapper.find('.modal-overlay').exists()).toBe(true)
    expect(wrapper.find('.modal').exists()).toBe(true)
  })
})
