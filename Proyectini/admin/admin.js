const apiUrl = '../api/products.php';
let products = [];

const form = document.querySelector('#product-form');
const rowsContainer = document.querySelector('#product-rows');
const idField = document.querySelector('#product-id');
const nameField = document.querySelector('#product-name');
const priceField = document.querySelector('#product-price');
const descriptionField = document.querySelector('#product-description');
const stockField = document.querySelector('#product-stock');
const imageField = document.querySelector('#product-image');

function formatCurrency(value) {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value);
}

async function loadProducts() {
    const response = await fetch(apiUrl);
    if (!response.ok) {
        throw new Error('No se pudo obtener el catálogo.');
    }

    const payload = await response.json();
    products = payload.data || [];
    renderRows();
}

function renderRows() {
    if (products.length === 0) {
        rowsContainer.innerHTML = '<tr><td colspan="6">No hay productos registrados.</td></tr>';
        return;
    }

    rowsContainer.innerHTML = products.map((product) => {
        const imageCell = product.image
            ? `<img src="../uploads/${product.image}" alt="${product.name}">`
            : '<span class="hint">Sin imagen</span>';

        return `
            <tr data-id="${product.id}">
                <td><strong>${product.name}</strong></td>
                <td>${formatCurrency(product.price)}</td>
                <td>${imageCell}</td>
                <td>${product.description}</td>
                <td>${product.stock}</td>
                <td>
                    <div class="table-actions">
                        <button type="button" data-action="edit">Editar</button>
                        <button type="button" data-action="delete">Eliminar</button>
                    </div>
                </td>
            </tr>
        `;
    }).join('');
}

function resetForm() {
    form.reset();
    idField.value = '';
}

function fillForm(product) {
    idField.value = product.id;
    nameField.value = product.name;
    priceField.value = product.price;
    descriptionField.value = product.description;
    stockField.value = product.stock;
    imageField.value = '';
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function saveProduct(event) {
    event.preventDefault();
    const formData = new FormData(form);
    const id = idField.value.trim();

    if (id) {
        formData.append('_method', 'PUT');
        formData.append('id', id);
    }

    const response = await fetch(apiUrl, {
        method: 'POST',
        body: formData,
    });

    const payload = await response.json();
    if (!response.ok) {
        throw new Error(payload.error || 'No se pudo guardar el producto.');
    }

    await loadProducts();
    resetForm();
    alert(id ? 'Producto actualizado.' : 'Producto creado.');
}

async function deleteProduct(id) {
    if (!confirm('¿Seguro que deseas eliminar este producto?')) {
        return;
    }

    const response = await fetch(`${apiUrl}?id=${encodeURIComponent(id)}`, {
        method: 'DELETE',
    });

    const payload = await response.json();
    if (!response.ok) {
        throw new Error(payload.error || 'No se pudo eliminar el producto.');
    }

    await loadProducts();
    alert('Producto eliminado.');
}

rowsContainer?.addEventListener('click', async (event) => {
    const target = event.target;
    if (!(target instanceof HTMLElement)) {
        return;
    }

    const action = target.dataset.action;
    if (!action) {
        return;
    }

    const row = target.closest('tr');
    const id = row?.dataset.id;
    if (!id) {
        return;
    }

    const product = products.find((item) => item.id === id);
    if (!product) {
        return;
    }

    try {
        if (action === 'edit') {
            fillForm(product);
        } else if (action === 'delete') {
            await deleteProduct(id);
        }
    } catch (error) {
        alert(error.message);
    }
});

form?.addEventListener('submit', async (event) => {
    try {
        await saveProduct(event);
    } catch (error) {
        alert(error.message);
    }
});

form?.addEventListener('reset', () => {
    idField.value = '';
});

loadProducts().catch((error) => {
    console.error(error);
    rowsContainer.innerHTML = '<tr><td colspan="6">No se pudo cargar el catálogo.</td></tr>';
});