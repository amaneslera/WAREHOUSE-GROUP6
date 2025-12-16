// inventory-search.js
// Connects the search bar and warehouse filter to the backend API for live inventory search

document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.querySelector('input[placeholder="Search inventory..."]');
    const warehouseDropdown = document.querySelector('.dropdown-toggle');
    const tableBody = document.querySelector('table tbody');
    let selectedWarehouse = '';

    // Helper to fetch and render inventory
    function fetchInventory(query = '', warehouse = '') {
        let url = '/api/inventory?';
        if (query) url += `search=${encodeURIComponent(query)}&`;
        if (warehouse && warehouse !== 'All warehouses') url += `warehouse_name=${encodeURIComponent(warehouse)}&`;
        fetch(url)
            .then(res => res.json())
            .then(data => {
                tableBody.innerHTML = '';
                if (data.data && data.data.length) {
                    data.data.forEach(item => {
                        let statusClass = 'bg-success', statusText = 'Good Stock';
                        if (item.current_stock == 0) { statusClass = 'bg-danger'; statusText = 'Out of Stock'; }
                        else if (item.current_stock <= item.minimum_stock) { statusClass = 'bg-warning'; statusText = 'Low Stock'; }
                        tableBody.innerHTML += `
                        <tr>
                            <td>${item.item_id}</td>
                            <td>${item.item_name}</td>
                            <td>${item.category_name}</td>
                            <td>${item.warehouse_name}</td>
                            <td>${item.current_stock}</td>
                            <td>$${parseFloat(item.unit_price).toFixed(2)}</td>
                            <td><span class="badge ${statusClass}">${statusText}</span></td>
                            <td>${item.updated_at ? item.updated_at.split(' ')[0] : ''}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="/inventory/view/${item.id}" class="btn btn-sm btn-primary" title="View"><i class="fas fa-eye"></i> View</a>
                                    <a href="/inventory/edit/${item.id}" class="btn btn-sm btn-secondary" title="Edit"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="/inventory/delete/${item.id}" class="btn btn-sm btn-outline-secondary" title="Delete" onclick="return confirm('Are you sure you want to delete this item?')"><i class="fas fa-trash"></i> Delete</a>
                                </div>
                            </td>
                        </tr>`;
                    });
                } else {
                    tableBody.innerHTML = '<tr><td colspan="9" class="text-center text-muted">No inventory items found.</td></tr>';
                }
            });
    }

    // Search input event
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            fetchInventory(searchInput.value, selectedWarehouse);
        });
    }

    // Warehouse dropdown event
    document.querySelectorAll('.dropdown-menu .dropdown-item').forEach(item => {
        item.addEventListener('click', function (e) {
            e.preventDefault();
            selectedWarehouse = this.textContent.trim();
            warehouseDropdown.textContent = selectedWarehouse;
            fetchInventory(searchInput.value, selectedWarehouse);
        });
    });

    // Initial load
    fetchInventory();
});
