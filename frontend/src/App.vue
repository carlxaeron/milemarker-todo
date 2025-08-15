<script setup></script>

<template>
  <div id="app">
    <div class="container">
      <h1>Todo App</h1>
      
      <!-- Add Todo Form -->
      <div class="add-todo-form">
        <input 
          v-model="newTodo.title" 
          type="text" 
          placeholder="Enter todo title"
          class="todo-input"
        />
        <textarea 
          v-model="newTodo.description" 
          placeholder="Enter description (optional)"
          class="todo-textarea"
        ></textarea>
        <button @click="addTodo" class="add-btn">Add Todo</button>
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
      newTodo: {
        title: '',
        description: '',
        completed: false
      },
      showEditModal: false,
      editingTodo: {
        id: null,
        title: '',
        description: '',
        completed: false
      }
    }
  },
  
  mounted() {
    this.fetchTodos()
  },
  
  methods: {
    async fetchTodos() {
      try {
        const response = await axios.get('http://localhost:8000/todos')
        this.todos = response.data
      } catch (error) {
        console.error('Error fetching todos:', error)
      }
    },
    
    async addTodo() {
      if (!this.newTodo.title.trim()) return
      
      try {
        const response = await axios.post('http://localhost:8000/todos', this.newTodo)
        this.todos.unshift(response.data)
        this.newTodo = { title: '', description: '', completed: false }
      } catch (error) {
        console.error('Error adding todo:', error)
      }
    },
    
    async toggleTodo(todo) {
      try {
        const response = await axios.put(`http://localhost:8000/todos/${todo.id}`, {
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
        const response = await axios.put(`http://localhost:8000/todos/${this.editingTodo.id}`, this.editingTodo)
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
        await axios.delete(`http://localhost:8000/todos/${id}`)
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

.add-btn:hover {
  background: #5a6fd8;
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
