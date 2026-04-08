const API_BASE = 'http://localhost/backend';
let currentProductId = null;

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
            document.getElementById('authWindow').style.display = 'none';
            document.getElementById('tableWindow').style.display = 'block';
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
    document.getElementById('authWindow').style.display = 'block';
    document.getElementById('tableWindow').style.display = 'none';
    document.getElementById('username').value = '';
    document.getElementById('password').value = '';
    document.getElementById('authError').textContent = '';
}

window.onclick = function (event) {
    const modal = document.getElementById('editModal');
    if (event.target === modal) {
        closeModal();
    }
};
