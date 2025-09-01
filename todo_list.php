<?php
session_start();
if (!isset($_SESSION['user'])) {
    echo "Unauthorized";
    exit();
}
?>

<div class="widget-column">
    <h3>To-do list
        <button id="show-input-btn" style="font-size: 10px; cursor: pointer; border: none; background: transparent;">➕</button>
    </h3> 
    <form id="todo-form" style="display: none; margin-top: 10px;">
        <input type="text" id="todo-input" placeholder="Add a task..." style="width: 100%; padding: 5px; border-radius: 4px;">
    </form>
    <ul class="todo-list" id="todo-list">
        <!-- Tasks will appear here -->
    </ul>
</div>

<script>
    const showInputBtn = document.getElementById('show-input-btn');
    const todoForm = document.getElementById('todo-form');
    const todoInput = document.getElementById('todo-input');
    const todoList = document.getElementById('todo-list');

    showInputBtn.addEventListener('click', (e) => {
        e.stopPropagation();
        todoForm.style.display = 'block';
        todoInput.focus();
    });

    document.addEventListener('click', function (event) {
        const isClickInsideForm = todoForm.contains(event.target);
        const isClickOnButton = showInputBtn.contains(event.target);

        if (!isClickInsideForm && !isClickOnButton) {
            todoForm.style.display = 'none';
        }
    });

    function saveTodos(todos) {
        localStorage.setItem('todos_' + <?php echo $_SESSION['user']['id']; ?>, JSON.stringify(todos));
    }

    function loadTodos() {
        return JSON.parse(localStorage.getItem('todos_' + <?php echo $_SESSION['user']['id']; ?>)) || [];
    }

    function renderTodos() {
        todoList.innerHTML = '';
        const todos = loadTodos();
        todos.forEach((todo, index) => {
            const li = document.createElement('li');
            li.innerHTML = `
                <input type="checkbox" ${todo.done ? 'checked' : ''}>
                <span style="margin-right: 10px;">${todo.text}</span>
                <i class="fas fa-trash-alt" style="color:red; cursor:pointer;" onclick="deleteTodo(${index})"></i>
            `;
            const checkbox = li.querySelector('input[type="checkbox"]');
            checkbox.addEventListener('change', () => {
                todos[index].done = checkbox.checked;
                saveTodos(todos);
            });
            todoList.appendChild(li);
        });
    }

    function deleteTodo(index) {
        const todos = loadTodos();
        todos.splice(index, 1);
        saveTodos(todos);
        renderTodos();
    }

    todoForm.addEventListener('submit', (e) => {
        e.preventDefault();
        const value = todoInput.value.trim();
        if (value) {
            const todos = loadTodos();
            todos.push({ text: value, done: false });
            saveTodos(todos);
            renderTodos();
            todoInput.value = '';
            todoForm.style.display = 'none';
        }
    });

    renderTodos();
</script>
