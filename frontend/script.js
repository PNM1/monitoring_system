const API_BASE = 'http://localhost/backend';
let currentProductId = null;
let isAdmin = false;

async function login() {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    const errorDiv = document.getElementById('authError');

    if (!username || !password) {
        errorDiv.textContent = 'Заполните все поля';
        return;
    }

    try {
        const response = await fetch(`${API_BASE}/api_auth.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password }),
        });

        const data = await response.json();

        if (data.success) {
            isAdmin = data.is_admin === true;
            
            document.getElementById('authWindow').style.display = 'none';
            document.getElementById('tableWindow').style.display = 'block';
            
            const adminBtn = document.getElementById('adminBtn');
            if (isAdmin) {
                adminBtn.style.display = 'inline-block';
            } else {
                adminBtn.style.display = 'none';
            }            
            loadProducts();
        } else {
            errorDiv.textContent = data.message || 'Ошибка авторизации';
        }
    } catch {
        errorDiv.textContent = 'Ошибка соединения с сервером';
    }
}

async function loadProducts() {
    const container = document.getElementById('productsTable');
    container.innerHTML = '<div class="loading">Загрузка...</div>';

    try {
        const response = await fetch(`${API_BASE}/api_ui.php/products`);
        const data = await response.json();

        if (data.success && data.products) {
            renderTable(data.products);
        } else {
            container.innerHTML = '<div class="loading">Ошибка загрузки данных</div>';
        }
    } catch {
        container.innerHTML = '<div class="loading">Ошибка соединения с сервером</div>';
    }
}

function parseLocationString(locationString) {
    if (!locationString || locationString === '-') {
        return '-';
    }
    
    const parts = locationString.split(';');
    
    if (parts.length === 3) {
        const department = parts[0];
        const row = parts[1];
        const shelf = parts[2];
        
        if (!isNaN(department) && !isNaN(row) && !isNaN(shelf)) {
            return `Отдел ${department}, Стеллаж ${row}, Место ${shelf}`;
        }
    }
    
    return locationString;
}

function renderTable(products) {
    const container = document.getElementById('productsTable');
    
    if (!products.length) {
        container.innerHTML = '<div class="loading">Нет товаров</div>';
        return;
    }

    let html = `
        <table>
            <thead>
                <tr>
                    <th>Название товара</th>
                    <th>Категория</th>
                    <th>Цвет</th>
                    <th>Размер</th>
                    <th>Цена, ₽</th>
                    <th>Текущее местоположение</th>
                    <th>Действие</th>
                </tr>
            </thead>
            <tbody>
    `;

    products.forEach(product => {
        const parsedLocation = parseLocationString(product.location);
        html += `
            <tr>
                <td>${escapeHtml(product.name)}</td>
                <td>${escapeHtml(product.category || '-')}</td>
                <td>${escapeHtml(product.color || '-')}</td>
                <td>${escapeHtml(product.size || '-')}</td>
                <td>${product.price ? product.price.toLocaleString() + ' ₽' : '-'}</td>
                <td>${escapeHtml(parsedLocation)}</td>
                <td><button class="edit-btn" onclick="openEditModal(${product.id})">Редактировать</button></td>
            </tr>
        `;
    });

    html += '</tbody></table>';
    container.innerHTML = html;
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/[&<>]/g, function (m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

function openEditModal(productId) {
    currentProductId = productId;
    document.getElementById('deptInput').value = '';
    document.getElementById('rowInput').value = '';
    document.getElementById('shelfInput').value = '';
    document.getElementById('editModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('editModal').style.display = 'none';
    currentProductId = null;
}

async function saveLocation() {
    const department = document.getElementById('deptInput').value;
    const row = document.getElementById('rowInput').value;
    const shelf = document.getElementById('shelfInput').value;

    if (!department || !row || !shelf) {
        alert('Заполните все поля');
        return;
    }

    try {
        const response = await fetch(
            `${API_BASE}/api_ui.php/products/${currentProductId}/location`,
            {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    department: parseInt(department),
                    row: parseInt(row),
                    shelf: parseInt(shelf),
                }),
            },
        );

        const data = await response.json();

        if (data.success) {
            closeModal();
            loadProducts();
        } else {
            alert(data.message || 'Ошибка при сохранении');
        }
    } catch {
        alert('Ошибка соединения с сервером');
    }
}

function logout() {
    isAdmin = false;
    document.getElementById('authWindow').style.display = 'block';
    document.getElementById('tableWindow').style.display = 'none';
    document.getElementById('username').value = '';
    document.getElementById('password').value = '';
    document.getElementById('authError').textContent = '';
}

function openAdminPanel() {
    if (!isAdmin) return;
    document.getElementById('adminModal').style.display = 'flex';
    loadUsersList();
}

function closeAdminPanel() {
    document.getElementById('adminModal').style.display = 'none';
    document.getElementById('createUserMsg').innerHTML = '';
}

async function loadUsersList() {
    const container = document.getElementById('usersList');
    container.innerHTML = '<div class="loading">Загрузка...</div>';
    
    try {
        const response = await fetch(`${API_BASE}/api_users.php`, {
            method: 'GET',
            headers: { 'Content-Type': 'application/json' }
        });
        
        const data = await response.json();
        
        if (data.success && data.users) {
            renderUsersList(data.users);
        } else {
            container.innerHTML = '<div class="error">Ошибка загрузки пользователей</div>';
        }
    } catch {
        container.innerHTML = '<div class="error">Ошибка соединения с сервером</div>';
    }
}

function renderUsersList(users) {
    const container = document.getElementById('usersList');
    
    if (!users.length) {
        container.innerHTML = '<div class="loading">Нет пользователей</div>';
        return;
    }
    
    let html = '<table class="users-table"><thead><tr><th>Логин</th><th>Админ</th><th>Действие</th></tr></thead><tbody>';
    
    users.forEach(user => {
        const isSelf = user.username === 'admin';
        html += `
            <tr>
                <td>${escapeHtml(user.username)}</td>
                <td>${user.is_admin ? 'Да' : 'Нет'}</td>
                <td>
                    ${!isSelf ? `<button class="delete-user-btn" onclick="deleteUser(${user.id}, '${escapeHtml(user.username)}')">Удалить</button>` : 'Главный админ'}
                </td>
            </tr>
        `;
    });
    
    html += '</tbody></table>';
    container.innerHTML = html;
}

async function createUser() {
    const username = document.getElementById('newUsername').value.trim();
    const password = document.getElementById('newPassword').value;
    const msgDiv = document.getElementById('createUserMsg');
    
    if (!username || !password) {
        msgDiv.innerHTML = '<span class="error">Заполните оба поля</span>';
        return;
    }
    
    if (username.length < 3) {
        msgDiv.innerHTML = '<span class="error">Логин должен быть не менее 3 символов</span>';
        return;
    }
    
    if (password.length < 3) {
        msgDiv.innerHTML = '<span class="error">Пароль должен быть не менее 3 символов</span>';
        return;
    }
    
    msgDiv.innerHTML = '<span class="loading-small">Создание...</span>';
    
    try {
        const response = await fetch(`${API_BASE}/api_users.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ username, password })
        });
        
        const data = await response.json();
        
        if (data.success) {
            msgDiv.innerHTML = '<span class="success">Пользователь создан</span>';
            document.getElementById('newUsername').value = '';
            document.getElementById('newPassword').value = '';
            loadUsersList();
            setTimeout(() => {
                msgDiv.innerHTML = '';
            }, 3000);
        } else {
            msgDiv.innerHTML = `<span class="error">${data.message || 'Ошибка создания'}</span>`;
        }
    } catch {
        msgDiv.innerHTML = '<span class="error">Ошибка соединения с сервером</span>';
    }
}

async function deleteUser(userId, username) {
    if (!confirm(`Вы уверены, что хотите удалить пользователя "${username}"?`)) {
        return;
    }
    
    try {
        const response = await fetch(`${API_BASE}/api_users.php?id=${userId}`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' }
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert('Пользователь удален');
            loadUsersList();
        } else {
            alert(data.message || 'Ошибка при удалении');
        }
    } catch {
        alert('Ошибка соединения с сервером');
    }
}

window.onclick = function (event) {
    const editModal = document.getElementById('editModal');
    const adminModal = document.getElementById('adminModal');
    if (event.target === editModal) {
        closeModal();
    }
    if (event.target === adminModal) {
        closeAdminPanel();
    }
};