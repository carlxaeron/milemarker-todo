import { describe, it, expect, beforeEach, vi } from 'vitest'
import { mount } from '@vue/test-utils'
import { render, screen, fireEvent, waitFor } from '@testing-library/vue'
import App from './App.vue'
import axios from 'axios'

// Mock axios
vi.mock('axios')

describe('App.vue', () => {
  let wrapper
  let mockAxios

  beforeEach(() => {
    // Reset mocks
    vi.clearAllMocks()
    
    // Create mock axios instance
    mockAxios = {
      get: vi.fn().mockResolvedValue({ data: [] }),
      post: vi.fn(),
      put: vi.fn(),
      delete: vi.fn()
    }
    
    // Mock axios methods
    axios.get = mockAxios.get
    axios.post = mockAxios.post
    axios.put = mockAxios.put
    axios.delete = mockAxios.delete
  })

  describe('Component Rendering', () => {
    it('renders the todo app title', () => {
      wrapper = mount(App)
      expect(wrapper.find('h1').text()).toBe('Todo App')
    })

    it('renders the add todo form', () => {
      wrapper = mount(App)
      expect(wrapper.find('.add-todo-form').exists()).toBe(true)
      expect(wrapper.find('input[placeholder="Enter todo title"]').exists()).toBe(true)
      expect(wrapper.find('textarea[placeholder="Enter description (optional)"]').exists()).toBe(true)
      expect(wrapper.find('.add-btn').exists()).toBe(true)
    })

    it('shows "No todos yet" message when todos array is empty', () => {
      wrapper = mount(App)
      expect(wrapper.find('.no-todos').text()).toBe('No todos yet. Add one above!')
    })
  })

  describe('Fetching Todos', () => {
    it('fetches todos on component mount', async () => {
      const mockTodos = [
        { id: 1, title: 'Test Todo', description: 'Test Description', completed: false }
      ]
      
      mockAxios.get.mockResolvedValue({ data: mockTodos })
      
      wrapper = mount(App)
      
      await wrapper.vm.$nextTick()
      
      expect(mockAxios.get).toHaveBeenCalledWith('http://localhost:8000/api/todos')
      expect(wrapper.vm.todos).toEqual(mockTodos)
    })

    it('handles error when fetching todos fails', async () => {
      const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
      mockAxios.get.mockRejectedValue(new Error('Network error'))
      
      wrapper = mount(App)
      
      await wrapper.vm.$nextTick()
      
      expect(consoleSpy).toHaveBeenCalledWith('Error fetching todos:', expect.any(Error))
      consoleSpy.mockRestore()
    })
  })

  describe('Adding Todos', () => {
    it('adds a new todo successfully', async () => {
      const newTodo = {
        title: 'New Todo',
        description: 'New Description',
        completed: false
      }
      
      const createdTodo = { ...newTodo, id: 1, created_at: '2025-08-15T00:00:00Z', updated_at: '2025-08-15T00:00:00Z' }
      
      mockAxios.post.mockResolvedValue({ data: createdTodo })
      
      wrapper = mount(App)
      
      // Wait for component to mount and fetchTodos to complete
      await wrapper.vm.$nextTick()
      
      // Set form data
      await wrapper.setData({
        newTodo: { ...newTodo }
      })
      
      // Trigger add todo
      await wrapper.vm.addTodo()
      
      // Wait for the async operation to complete
      await wrapper.vm.$nextTick()
      
      expect(mockAxios.post).toHaveBeenCalledWith('http://localhost:8000/api/todos', newTodo)
      
      // Check if the todo was added to the array
      const todosArray = wrapper.vm.todos
      expect(todosArray).toHaveLength(1)
      expect(todosArray[0]).toEqual(createdTodo)
      
      expect(wrapper.vm.newTodo).toEqual({ title: '', description: '', completed: false })
    })

    it('does not add todo when title is empty', async () => {
      wrapper = mount(App)
      
      // Wait for component to mount and fetchTodos to complete
      await wrapper.vm.$nextTick()
      
      const initialTodos = wrapper.vm.todos
      
      await wrapper.setData({
        newTodo: { title: '', description: 'Some description', completed: false }
      })
      
      await wrapper.vm.addTodo()
      
      expect(wrapper.vm.todos).toEqual(initialTodos)
      expect(mockAxios.post).not.toHaveBeenCalled()
    })

    it('handles error when adding todo fails', async () => {
      const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
      mockAxios.post.mockRejectedValue(new Error('Network error'))
      
      wrapper = mount(App)
      
      // Wait for component to mount and fetchTodos to complete
      await wrapper.vm.$nextTick()
      
      await wrapper.setData({
        newTodo: { title: 'Test Todo', description: '', completed: false }
      })
      
      await wrapper.vm.addTodo()
      
      expect(consoleSpy).toHaveBeenCalledWith('Error adding todo:', expect.any(Error))
      consoleSpy.mockRestore()
    })
  })

  describe('Toggling Todo Status', () => {
    it('toggles todo completion status', async () => {
      const todo = { id: 1, title: 'Test Todo', description: '', completed: false }
      const updatedTodo = { ...todo, completed: true }
      
      mockAxios.put.mockResolvedValue({ data: updatedTodo })
      
      wrapper = mount(App)
      
      // Wait for component to mount and fetchTodos to complete
      await wrapper.vm.$nextTick()
      
      // Set todos directly to avoid fetchTodos interference
      await wrapper.setData({
        todos: [todo]
      })
      
      await wrapper.vm.toggleTodo(todo)
      
      expect(mockAxios.put).toHaveBeenCalledWith('http://localhost:8000/api/todos/1', { completed: true })
      expect(wrapper.vm.todos[0].completed).toBe(true)
    })

    it('handles error when toggling todo fails', async () => {
      const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
      mockAxios.put.mockRejectedValue(new Error('Network error'))
      
      const todo = { id: 1, title: 'Test Todo', description: '', completed: false }
      
      wrapper = mount(App)
      
      // Wait for component to mount and fetchTodos to complete
      await wrapper.vm.$nextTick()
      
      await wrapper.setData({
        todos: [todo]
      })
      
      await wrapper.vm.toggleTodo(todo)
      
      expect(consoleSpy).toHaveBeenCalledWith('Error updating todo:', expect.any(Error))
      consoleSpy.mockRestore()
    })
  })

  describe('Editing Todos', () => {
    it('opens edit modal when edit button is clicked', async () => {
      const todo = { id: 1, title: 'Test Todo', description: 'Test Description', completed: false }
      
      wrapper = mount(App)
      
      // Wait for component to mount and fetchTodos to complete
      await wrapper.vm.$nextTick()
      
      await wrapper.setData({
        todos: [todo]
      })
      
      await wrapper.vm.editTodo(todo)
      
      expect(wrapper.vm.showEditModal).toBe(true)
      expect(wrapper.vm.editingTodo).toEqual(todo)
    })

    it('saves edited todo successfully', async () => {
      const todo = { id: 1, title: 'Original Title', description: 'Original Description', completed: false }
      const editedTodo = { ...todo, title: 'Updated Title', description: 'Updated Description' }
      const savedTodo = { ...editedTodo, updated_at: '2025-08-15T00:00:00Z' }
      
      mockAxios.put.mockResolvedValue({ data: savedTodo })
      
      wrapper = mount(App)
      
      // Wait for component to mount and fetchTodos to complete
      await wrapper.vm.$nextTick()
      
      await wrapper.setData({
        todos: [todo],
        editingTodo: editedTodo,
        showEditModal: true
      })
      
      await wrapper.vm.saveEdit()
      
      expect(mockAxios.put).toHaveBeenCalledWith('http://localhost:8000/api/todos/1', editedTodo)
      expect(wrapper.vm.todos[0]).toEqual(savedTodo)
      expect(wrapper.vm.showEditModal).toBe(false)
    })

    it('closes edit modal', async () => {
      wrapper = mount(App)
      
      // Wait for component to mount and fetchTodos to complete
      await wrapper.vm.$nextTick()
      
      await wrapper.setData({
        showEditModal: true,
        editingTodo: { id: 1, title: 'Test', description: '', completed: false }
      })
      
      await wrapper.vm.closeEditModal()
      
      expect(wrapper.vm.showEditModal).toBe(false)
      expect(wrapper.vm.editingTodo).toEqual({ id: null, title: '', description: '', completed: false })
    })
  })

  describe('Deleting Todos', () => {
    it('deletes todo successfully', async () => {
      const todo = { id: 1, title: 'Test Todo', description: '', completed: false }
      
      mockAxios.delete.mockResolvedValue({})
      
      // Mock confirm dialog
      global.confirm = vi.fn(() => true)
      
      wrapper = mount(App)
      
      // Wait for component to mount and fetchTodos to complete
      await wrapper.vm.$nextTick()
      
      await wrapper.setData({
        todos: [todo]
      })
      
      await wrapper.vm.deleteTodo(1)
      
      expect(mockAxios.delete).toHaveBeenCalledWith('http://localhost:8000/api/todos/1')
      expect(wrapper.vm.todos).toHaveLength(0)
    })

    it('does not delete todo when user cancels', async () => {
      const todo = { id: 1, title: 'Test Todo', description: '', completed: false }
      
      // Mock confirm dialog to return false
      global.confirm = vi.fn(() => false)
      
      wrapper = mount(App)
      
      // Wait for component to mount and fetchTodos to complete
      await wrapper.vm.$nextTick()
      
      await wrapper.setData({
        todos: [todo]
      })
      
      await wrapper.vm.deleteTodo(1)
      
      expect(mockAxios.delete).not.toHaveBeenCalled()
      expect(wrapper.vm.todos).toHaveLength(1)
    })

    it('handles error when deleting todo fails', async () => {
      const consoleSpy = vi.spyOn(console, 'error').mockImplementation(() => {})
      mockAxios.delete.mockRejectedValue(new Error('Network error'))
      
      // Mock confirm dialog
      global.confirm = vi.fn(() => true)
      
      const todo = { id: 1, title: 'Test Todo', description: '', completed: false }
      
      wrapper = mount(App)
      
      // Wait for component to mount and fetchTodos to complete
      await wrapper.vm.$nextTick()
      
      await wrapper.setData({
        todos: [todo]
      })
      
      await wrapper.vm.deleteTodo(1)
      
      expect(consoleSpy).toHaveBeenCalledWith('Error deleting todo:', expect.any(Error))
      consoleSpy.mockRestore()
    })
  })

  describe('User Interactions', () => {
    it('updates form data when typing', async () => {
      wrapper = mount(App)
      
      // Wait for component to mount and fetchTodos to complete
      await wrapper.vm.$nextTick()
      
      const titleInput = wrapper.find('input[placeholder="Enter todo title"]')
      const descriptionTextarea = wrapper.find('textarea[placeholder="Enter description (optional)"]')
      
      await titleInput.setValue('New Todo Title')
      await descriptionTextarea.setValue('New Todo Description')
      
      expect(wrapper.vm.newTodo.title).toBe('New Todo Title')
      expect(wrapper.vm.newTodo.description).toBe('New Todo Description')
    })

    it('shows completed todo styling', async () => {
      const todo = { id: 1, title: 'Completed Todo', description: '', completed: true }
      
      wrapper = mount(App)
      
      // Wait for component to mount and fetchTodos to complete
      await wrapper.vm.$nextTick()
      
      await wrapper.setData({
        todos: [todo]
      })
      
      const todoItem = wrapper.find('.todo-item')
      expect(todoItem.classes()).toContain('completed')
    })
  })
})
