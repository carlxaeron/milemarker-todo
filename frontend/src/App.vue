<template>
  <div id="app">
    <div class="container">
      <h1>Todo App with User Relationships</h1>
      
      <!-- User Management Section -->
      <div class="user-section">
        <h2>Users</h2>
        <div class="user-list">
          <div v-for="user in users" :key="user.id" class="user-item">
            <div class="user-info">
              <span class="user-name">{{ user.name }}</span>
              <span class="user-email">{{ user.email }}</span>
              <span class="user-todos-count">{{ user.todos_count || 0 }} todos</span>
            </div>
            <button @click="loginAsUser(user)" class="login-as-btn">
              <span v-if="loading.loggingIn && currentUser && currentUser.id === user.id">Logging in...</span>
              <span v-else-if="currentUser && currentUser.id === user.id">✓ Logged In</span>
              <span v-else>Login As</span>
            </button>
          </div>
        </div>
        
        <!-- Add User Form -->
        <div class="add-user-form">
          <div class="form-group">
            <input 
              v-model="newUser.name" 
              type="text" 
              placeholder="User name"
              class="user-input"
              :class="{ 'error': errors.user.name }"
              @input="clearUserErrors('name')"
            />
            <span v-if="errors.user.name" class="error-message">{{ errors.user.name }}</span>
          </div>
          
          <div class="form-group">
            <input 
              v-model="newUser.email" 
              type="email" 
              placeholder="User email"
              class="user-input"
              :class="{ 'error': errors.user.email }"
              @input="clearUserErrors('email')"
            />
            <span v-if="errors.user.email" class="error-message">{{ errors.user.email }}</span>
          </div>
          
          <div class="form-group">
            <input 
              v-model="newUser.password" 
              type="password" 
              placeholder="Password"
              class="user-input"
              :class="{ 'error': errors.user.password }"
              @input="clearUserErrors('password')"
            />
            <span v-if="errors.user.password" class="error-message">{{ errors.user.password }}</span>
          </div>
          
          <button @click="addUser" class="add-user-btn" :disabled="loading.addingUser">
            <span v-if="loading.addingUser">Adding...</span>
            <span v-else>Add User</span>
          </button>
        </div>
        
        <!-- General Error Display -->
        <div v-if="errors.general" class="general-error">
          {{ errors.general }}
        </div>
      </div>
      
      <!-- Current User Info -->
      <div v-if="currentUser" class="current-user-info">
        <h3>Currently Logged In: {{ currentUser.name }}</h3>
        <p>Email: {{ currentUser.email }}</p>
        <p>Total Todos: {{ currentUser.todos_count || 0 }}</p>
        <button @click="logoutUser" class="logout-btn">Logout</button>
      </div>
      
      <!-- Login Prompt -->
      <div v-else class="login-prompt">
        <h3>Please Login</h3>
        <p>Select a user from the list above to get started</p>
      </div>
      
      <!-- Add Todo Form -->
      <div class="add-todo-form">
        <div class="form-group">
          <input 
            v-model="newTodo.title" 
            type="text" 
            placeholder="Enter todo title"
            class="todo-input"
            :class="{ 'error': errors.todo.title }"
            @input="clearTodoErrors('title')"
          />
          <span v-if="errors.todo.title" class="error-message">{{ errors.todo.title }}</span>
        </div>
        
        <div class="form-group">
          <textarea 
            v-model="newTodo.description" 
            placeholder="Enter description (optional)"
            class="todo-textarea"
            :class="{ 'error': errors.todo.description }"
            @input="clearTodoErrors('description')"
          ></textarea>
          <span v-if="errors.todo.description" class="error-message">{{ errors.todo.description }}</span>
        </div>
        
        <button @click="addTodo" class="add-btn" :disabled="loading.addingTodo || !currentUser">
          <span v-if="loading.addingTodo">Adding...</span>
          <span v-else-if="!currentUser">Login Required</span>
          <span v-else>Add Todo</span>
        </button>
      </div>

      <!-- Todo List -->
      <div class="todo-list">
        <div v-if="todos.length === 0" class="no-todos">
          No todos yet. Add one above!
        </div>
        
        <div 
          v-for="todo in todos" 
          :key="todo.id" 
          class="todo-item"
          :class="{ completed: todo.completed }"
        >
          <div class="todo-content">
            <input 
              type="checkbox" 
              :checked="todo.completed"
              @change="toggleTodo(todo)"
              class="todo-checkbox"
            />
            <div class="todo-text">
              <h3 class="todo-title">{{ todo.title }}</h3>
              <p v-if="todo.description" class="todo-description">{{ todo.description }}</p>
              
              <!-- User Ownership Info -->
              <div v-if="todo.user" class="todo-owner">
                <span class="owner-label">Owner:</span>
                <span class="owner-name">{{ todo.user.name }}</span>
                <span class="owner-email">({{ todo.user.email }})</span>
              </div>
              
              <!-- Relationship Metadata -->
              <div v-if="todo.user_relationships && todo.user_relationships.length > 0" class="todo-relationships">
                <div v-for="relationship in todo.user_relationships" :key="relationship.id" class="relationship-item">
                  <span class="relationship-type">{{ relationship.relationship_type }}</span>
                  <span v-if="relationship.metadata" class="relationship-metadata">
                    <span v-if="relationship.metadata.priority" class="priority priority-{{ relationship.metadata.priority }}">
                      {{ relationship.metadata.priority }} priority
                    </span>
                    <span v-if="relationship.metadata.category" class="category">
                      {{ relationship.metadata.category }}
                    </span>
                    <span v-if="relationship.metadata.due_date" class="due-date">
                      Due: {{ formatDate(relationship.metadata.due_date) }}
                    </span>
                    <span v-if="relationship.metadata.estimated_hours" class="estimated-hours">
                      {{ relationship.metadata.estimated_hours }}h
                    </span>
                  </span>
                </div>
              </div>
              
              <!-- Relationship Flags -->
              <div class="relationship-flags">
                <span v-if="todo.is_favorite" class="flag favorite-flag">⭐ Favorite</span>
                <span v-if="todo.is_shared" class="flag shared-flag">🔗 Shared</span>
              </div>
            </div>
          </div>
          
          <div class="todo-actions">
            <button @click="editTodo(todo)" class="edit-btn">Edit</button>
            <button @click="deleteTodo(todo.id)" class="delete-btn">Delete</button>
          </div>
        </div>
      </div>

      <!-- Edit Modal -->
      <div v-if="showEditModal" class="modal-overlay" @click="closeEditModal">
        <div class="modal" @click.stop>
          <h3>Edit Todo</h3>
          <input 
            v-model="editingTodo.title" 
            type="text" 
            placeholder="Todo title"
            class="todo-input"
          />
          <textarea 
            v-model="editingTodo.description" 
            placeholder="Description"
            class="todo-textarea"
          ></textarea>
          <div class="modal-actions">
            <button @click="saveEdit" class="save-btn">Save</button>
            <button @click="closeEditModal" class="cancel-btn">Cancel</button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import axios from 'axios'

export default {
  name: 'App',
        data() {
        return {
          todos: [],
          users: [],
          currentUser: null, // Currently logged in user
          newTodo: {
            title: '',
            description: '',
            completed: false
          },
          newUser: {
            name: '',
            email: '',
            password: ''
          },
          showEditModal: false,
          editingTodo: {
            id: null,
            title: '',
            description: '',
            completed: false
          },
          // Error handling
          errors: {
            user: {},
            todo: {},
            general: ''
          },
          // Loading states
          loading: {
            users: false,
            todos: false,
            addingUser: false,
            addingTodo: false,
            loggingIn: false
          }
        }
      },
  
  mounted() {
    this.fetchUsers()
    this.fetchTodos()
  },
  
  methods: {
    async fetchUsers() {
      this.loading.users = true
      this.errors.general = ''
      
      try {
        const response = await axios.get('http://localhost:8000/api/users')
        this.users = response.data
      } catch (error) {
        console.error('Error fetching users:', error)
        this.errors.general = 'Failed to fetch users. Please try again.'
      } finally {
        this.loading.users = false
      }
    },
    
    async fetchTodos() {
      this.loading.todos = true
      this.errors.general = ''
      
      try {
        const response = await axios.get('http://localhost:8000/api/todos')
        this.todos = response.data
      } catch (error) {
        console.error('Error fetching todos:', error)
        this.errors.general = 'Failed to fetch todos. Please try again.'
      } finally {
        this.loading.todos = false
      }
    },
    
    async addUser() {
      // Clear previous errors
      this.errors.user = {}
      this.errors.general = ''
      
      // Basic frontend validation
      if (!this.newUser.name.trim()) {
        this.errors.user.name = 'Name is required'
        return
      }
      if (!this.newUser.email.trim()) {
        this.errors.user.email = 'Email is required'
        return
      }
      if (!this.newUser.password.trim()) {
        this.errors.user.password = 'Password is required'
        return
      }
      if (this.newUser.password.length < 6) {
        this.errors.user.password = 'Password must be at least 6 characters'
        return
      }
      
      this.loading.addingUser = true
      
      try {
        const response = await axios.post('http://localhost:8000/api/users', this.newUser)
        this.users.push(response.data)
        this.newUser = { name: '', email: '', password: '' }
        this.fetchTodos() // Refresh todos to get updated user counts
      } catch (error) {
        console.error('Error adding user:', error)
        
        if (error.response && error.response.status === 422) {
          // Validation errors from backend
          const validationErrors = error.response.data.errors
          Object.keys(validationErrors).forEach(field => {
            this.errors.user[field] = validationErrors[field][0]
          })
        } else {
          this.errors.general = 'Failed to add user. Please try again.'
        }
      } finally {
        this.loading.addingUser = false
      }
    },
    
    selectUser(user) {
      this.selectedUser = user
      // You could also filter todos by user here if needed
    },
    
    formatDate(dateString) {
      if (!dateString) return ''
      const date = new Date(dateString)
      return date.toLocaleDateString()
    },
    
    clearUserErrors(field) {
      if (this.errors.user[field]) {
        this.errors.user[field] = ''
      }
    },
    
    clearTodoErrors(field) {
      if (this.errors.todo[field]) {
        this.errors.todo[field] = ''
      }
    },
    
    loginAsUser(user) {
      this.loading.loggingIn = true
      this.errors.general = ''
      
      // Simulate login process
      setTimeout(() => {
        this.currentUser = user
        this.loading.loggingIn = false
        
        // Refresh todos to get updated counts
        this.fetchTodos()
        
        // Clear any previous errors
        this.errors.general = ''
      }, 500)
    },
    
    logoutUser() {
      this.currentUser = null
      this.errors.general = ''
      
      // Clear todo form
      this.newTodo = { title: '', description: '', completed: false }
      
      // Clear todo errors
      this.errors.todo = {}
    },
    
    async addTodo() {
      // Check if user is logged in
      if (!this.currentUser) {
        this.errors.general = 'Please login first to add todos'
        return
      }
      
      // Clear previous errors
      this.errors.todo = {}
      this.errors.general = ''
      
      // Basic frontend validation
      if (!this.newTodo.title.trim()) {
        this.errors.todo.title = 'Title is required'
        return
      }
      
      this.loading.addingTodo = true
      
      try {
        // Add current user ID to the todo
        const todoData = {
          ...this.newTodo,
          user_id: this.currentUser.id
        }
        
        const response = await axios.post('http://localhost:8000/api/todos', todoData)
        this.todos.unshift(response.data)
        this.newTodo = { title: '', description: '', completed: false }
        this.fetchUsers() // Refresh users to get updated todo counts
      } catch (error) {
        console.error('Error adding todo:', error)
        
        if (error.response && error.response.status === 422) {
          // Validation errors from backend
          const validationErrors = error.response.data.errors
          Object.keys(validationErrors).forEach(field => {
            this.errors.todo[field] = validationErrors[field][0]
          })
        } else {
          this.errors.general = 'Failed to add todo. Please try again.'
        }
      } finally {
        this.loading.addingTodo = false
      }
    },
    
    async toggleTodo(todo) {
      try {
        const response = await axios.put(`http://localhost:8000/api/todos/${todo.id}`, {
          completed: !todo.completed
        })
        const index = this.todos.findIndex(t => t.id === todo.id)
        if (index !== -1) {
          this.todos[index] = response.data
        }
      } catch (error) {
        console.error('Error updating todo:', error)
      }
    },
    
    editTodo(todo) {
      this.editingTodo = { ...todo }
      this.showEditModal = true
    },
    
    async saveEdit() {
      try {
        const response = await axios.put(`http://localhost:8000/api/todos/${this.editingTodo.id}`, this.editingTodo)
        const index = this.todos.findIndex(t => t.id === this.editingTodo.id)
        if (index !== -1) {
          this.todos[index] = response.data
        }
        this.closeEditModal()
      } catch (error) {
        console.error('Error updating todo:', error)
      }
    },
    
    closeEditModal() {
      this.showEditModal = false
      this.editingTodo = { id: null, title: '', description: '', completed: false }
    },
    
    async deleteTodo(id) {
      if (!confirm('Are you sure you want to delete this todo?')) return
      
      try {
        await axios.delete(`http://localhost:8000/api/todos/${id}`)
        this.todos = this.todos.filter(t => t.id !== id)
      } catch (error) {
        console.error('Error deleting todo:', error)
      }
    }
  }
}
</script>

<style>
* {
  margin: 0;
  padding: 0;
  box-sizing: border-box;
}

body {
  font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
  background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
  min-height: 100vh;
}

.container {
  max-width: 800px;
  margin: 0 auto;
  padding: 20px;
}

h1 {
  text-align: center;
  color: white;
  margin-bottom: 30px;
  font-size: 2.5rem;
  text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.add-todo-form {
  background: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  margin-bottom: 20px;
}

.add-todo-form .form-group {
  margin-bottom: 15px;
}

.todo-assignment-info {
  background: #e8f5e8;
  padding: 10px 15px;
  border-radius: 5px;
  margin-bottom: 15px;
  border-left: 3px solid #4caf50;
}

.todo-assignment-info p {
  color: #2e7d32;
  margin: 0;
  font-size: 14px;
}

.todo-assignment-warning {
  background: #fff3e0;
  padding: 10px 15px;
  border-radius: 5px;
  margin-bottom: 15px;
  border-left: 3px solid #ff9800;
}

.todo-assignment-warning p {
  color: #e65100;
  margin: 0;
  font-size: 14px;
}

.todo-input, .todo-textarea {
  width: 100%;
  padding: 12px;
  margin-bottom: 15px;
  border: 2px solid #e1e5e9;
  border-radius: 5px;
  font-size: 16px;
  transition: border-color 0.3s ease;
}

.todo-input:focus, .todo-textarea:focus {
  outline: none;
  border-color: #667eea;
}

.todo-input:disabled, .todo-textarea:disabled {
  background-color: #f8f9fa;
  color: #6c757d;
  cursor: not-allowed;
}

.todo-textarea {
  resize: vertical;
  min-height: 80px;
}

.add-btn {
  background: #667eea;
  color: white;
  border: none;
  padding: 12px 24px;
  border-radius: 5px;
  font-size: 16px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

.add-btn:hover:not(:disabled) {
  background: #5a6fd8;
}

.add-btn:disabled {
  background: #6c757d;
  cursor: not-allowed;
  opacity: 0.6;
}

/* User Management Styles */
.user-section {
  background: white;
  padding: 20px;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  margin-bottom: 20px;
}

.user-section h2 {
  color: #333;
  margin-bottom: 15px;
  font-size: 1.5rem;
}

.user-list {
  margin-bottom: 20px;
}

.user-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 15px;
  border: 1px solid #e1e5e9;
  border-radius: 5px;
  margin-bottom: 10px;
  background: #f8f9fa;
}

.user-info {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.user-name {
  font-weight: bold;
  color: #333;
  font-size: 16px;
}

.user-email {
  color: #666;
  font-size: 14px;
}

.user-todos-count {
  color: #28a745;
  font-size: 12px;
  font-weight: bold;
}

.login-as-btn {
  background: #007bff;
  color: white;
  border: none;
  padding: 8px 16px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.3s ease;
  min-width: 100px;
}

.login-as-btn:hover:not(:disabled) {
  background: #0056b3;
}

.login-as-btn:disabled {
  background: #6c757d;
  cursor: not-allowed;
  opacity: 0.6;
}

.add-user-form {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr auto;
  gap: 15px;
  align-items: start;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 5px;
}

.error-message {
  color: #dc3545;
  font-size: 12px;
  font-weight: 500;
  margin-top: 2px;
}

.general-error {
  background: #f8d7da;
  color: #721c24;
  padding: 10px;
  border-radius: 5px;
  margin-top: 10px;
  border: 1px solid #f5c6cb;
  font-size: 14px;
}

.user-input.error,
.todo-input.error,
.todo-textarea.error,
.user-select.error {
  border-color: #dc3545;
  background-color: #fff5f5;
}

.user-input.error:focus,
.todo-input.error:focus,
.todo-textarea.error:focus,
.user-select.error:focus {
  border-color: #dc3545;
  box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
}

.user-input {
  padding: 10px;
  border: 2px solid #e1e5e9;
  border-radius: 5px;
  font-size: 14px;
  transition: border-color 0.3s ease;
}

.user-input:focus {
  outline: none;
  border-color: #007bff;
}

.add-user-btn {
  background: #28a745;
  color: white;
  border: none;
  padding: 10px 20px;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.3s ease;
  min-height: 42px;
}

.add-user-btn:hover:not(:disabled) {
  background: #218838;
}

.add-user-btn:disabled {
  background: #6c757d;
  cursor: not-allowed;
  opacity: 0.6;
}

.current-user-info {
  background: #e8f5e8;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 20px;
  border-left: 4px solid #4caf50;
  position: relative;
}

.current-user-info h3 {
  color: #2e7d32;
  margin-bottom: 10px;
}

.current-user-info p {
  color: #424242;
  margin-bottom: 5px;
}

.logout-btn {
  position: absolute;
  top: 15px;
  right: 15px;
  background: #f44336;
  color: white;
  border: none;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
  font-size: 12px;
  transition: background-color 0.3s ease;
}

.logout-btn:hover {
  background: #d32f2f;
}

.login-prompt {
  background: #fff3e0;
  padding: 15px;
  border-radius: 8px;
  margin-bottom: 20px;
  border-left: 4px solid #ff9800;
  text-align: center;
}

.login-prompt h3 {
  color: #e65100;
  margin-bottom: 10px;
}

.login-prompt p {
  color: #424242;
  margin-bottom: 0;
}

.user-select {
  padding: 12px;
  border: 2px solid #e1e5e9;
  border-radius: 5px;
  font-size: 16px;
  margin-bottom: 15px;
  transition: border-color 0.3s ease;
}

.user-select:focus {
  outline: none;
  border-color: #667eea;
}

.todo-list {
  background: white;
  border-radius: 10px;
  box-shadow: 0 4px 6px rgba(0,0,0,0.1);
  overflow: hidden;
}

.no-todos {
  padding: 40px;
  text-align: center;
  color: #666;
  font-style: italic;
}

.todo-item {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e1e5e9;
  transition: background-color 0.3s ease;
}

.todo-item:hover {
  background-color: #f8f9fa;
}

.todo-item:last-child {
  border-bottom: none;
}

.todo-item.completed {
  background-color: #f8f9fa;
  opacity: 0.7;
}

.todo-item.completed .todo-title {
  text-decoration: line-through;
  color: #666;
}

.todo-content {
  display: flex;
  align-items: flex-start;
  flex: 1;
}

.todo-checkbox {
  margin-right: 15px;
  margin-top: 5px;
  transform: scale(1.2);
}

.todo-text {
  flex: 1;
}

.todo-title {
  margin-bottom: 5px;
  color: #333;
  font-size: 18px;
}

.todo-description {
  color: #666;
  line-height: 1.4;
}

.todo-owner {
  margin-top: 8px;
  padding: 8px;
  background: #f8f9fa;
  border-radius: 5px;
  border-left: 3px solid #007bff;
}

.owner-label {
  font-weight: bold;
  color: #007bff;
  margin-right: 8px;
}

.owner-name {
  color: #333;
  font-weight: 500;
}

.owner-email {
  color: #666;
  font-size: 12px;
  margin-left: 5px;
}

.todo-relationships {
  margin-top: 10px;
}

.relationship-item {
  display: inline-block;
  margin: 2px 5px 2px 0;
  padding: 4px 8px;
  background: #e9ecef;
  border-radius: 12px;
  font-size: 12px;
}

.relationship-type {
  color: #495057;
  font-weight: 500;
  margin-right: 5px;
}

.relationship-metadata {
  display: inline-flex;
  gap: 5px;
  flex-wrap: wrap;
}

.priority {
  padding: 2px 6px;
  border-radius: 8px;
  font-size: 11px;
  font-weight: bold;
  text-transform: uppercase;
}

.priority-high {
  background: #ffebee;
  color: #c62828;
}

.priority-medium {
  background: #fff3e0;
  color: #ef6c00;
}

.priority-low {
  background: #e8f5e8;
  color: #2e7d32;
}

.category {
  background: #e3f2fd;
  color: #1565c0;
  padding: 2px 6px;
  border-radius: 8px;
  font-size: 11px;
}

.due-date {
  background: #f3e5f5;
  color: #7b1fa2;
  padding: 2px 6px;
  border-radius: 8px;
  font-size: 11px;
}

.estimated-hours {
  background: #e0f2f1;
  color: #00695c;
  padding: 2px 6px;
  border-radius: 8px;
  font-size: 11px;
  font-weight: bold;
}

.relationship-flags {
  margin-top: 8px;
  display: flex;
  gap: 8px;
}

.flag {
  padding: 4px 8px;
  border-radius: 12px;
  font-size: 12px;
  font-weight: 500;
}

.favorite-flag {
  background: #fff3e0;
  color: #ef6c00;
}

.shared-flag {
  background: #e8f5e8;
  color: #2e7d32;
}

.todo-actions {
  display: flex;
  gap: 10px;
}

.edit-btn, .delete-btn {
  padding: 8px 16px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
  transition: all 0.3s ease;
}

.edit-btn {
  background: #28a745;
  color: white;
}

.edit-btn:hover {
  background: #218838;
}

.delete-btn {
  background: #dc3545;
  color: white;
}

.delete-btn:hover {
  background: #c82333;
}

.modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0,0,0,0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 1000;
}

.modal {
  background: white;
  padding: 30px;
  border-radius: 10px;
  width: 90%;
  max-width: 500px;
  box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.modal h3 {
  margin-bottom: 20px;
  color: #333;
}

.modal-actions {
  display: flex;
  gap: 10px;
  justify-content: flex-end;
  margin-top: 20px;
}

.save-btn, .cancel-btn {
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 14px;
  transition: background-color 0.3s ease;
}

.save-btn {
  background: #28a745;
  color: white;
}

.save-btn:hover {
  background: #218838;
}

.cancel-btn {
  background: #6c757d;
  color: white;
}

.cancel-btn:hover {
  background: #5a6268;
}
</style>
