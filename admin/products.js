$(document).ready(function() {
    loadProducts();

    $('#productForm').on('submit', function(e) {
        e.preventDefault();
        if (!$('#name').val().trim() || !$('#author').val().trim() || !$('#description').val().trim()) {
            showMessage('Required fields missing', 'error');
            return;
        }

        const formData = new FormData(this);
        const $submitBtn = $('#productForm button[type="submit"]').prop('disabled', true).text('Saving...');

        $.ajax({
            url: 'products.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                try {
                    const res = JSON.parse(response);
                    showMessage(res.message, res.success ? 'success' : 'error');
                    if (res.success) {
                        $('#productForm')[0].reset();
                        $('#productId, #file_size, #existing_filepath, #existing_thumbs').val('');
                        hideForm();
                        loadProducts();
                    }
                } catch (e) {
                    showMessage('Error processing response: ' + e.message, 'error');
                    console.error('Parse error:', e, 'Response:', response);
                }
                $submitBtn.prop('disabled', false).text('Save');
            },
            error: function(xhr, status, error) {
                showMessage('Server error: ' + (xhr.responseText || error), 'error');
                console.error('AJAX error:', status, error, 'Response:', xhr.responseText);
                $submitBtn.prop('disabled', false).text('Save');
            }
        });
    });

    $('#search').on('keyup', debounce(function() {
        const query = $(this).val().toLowerCase();
        if (query.length === 0 || query.length > 2) {
            $.ajax({
                url: 'products.php',
                type: 'GET',
                data: { action: 'search', query },
                success: function(response) {
                    try {
                        renderTable(JSON.parse(response));
                    } catch (e) {
                        showMessage('Error loading products: ' + e.message, 'error');
                        console.error('Parse error:', e, 'Response:', response);
                    }
                },
                error: function(xhr, status, error) {
                    showMessage('Search error: ' + (xhr.responseText || error), 'error');
                    console.error('AJAX error:', status, error, 'Response:', xhr.responseText);
                }
            });
        }
    }, 300));

    $(document).on('click', '.edit-btn', function() {
        $.ajax({
            url: 'products.php',
            type: 'GET',
            data: { action: 'get', id: $(this).data('id') },
            success: function(response) {
                try {
                    const product = JSON.parse(response);
                    if (product.success === false) {
                        showMessage(product.message, 'error');
                        return;
                    }
                    $('#productId').val(product.id);
                    $('#type').val(product.type);
                    $('#sku').val(product.sku);
                    $('#name').val(product.name);
                    $('#author').val(product.author);
                    $('#price').val(product.price);
                    $('#stock_quantity').val(product.stock_quantity);
                    $('#description').val(product.description);
                    $('#file_size').val(product.file_size);
                    $('#existing_filepath').val(product.filepath);
                    $('#existing_thumbs').val(product.thumbs);
                    showForm();
                } catch (e) {
                    showMessage('Error loading product: ' + e.message, 'error');
                    console.error('Parse error:', e, 'Response:', response);
                }
            },
            error: function(xhr, status, error) {
                showMessage('Error loading product: ' + (xhr.responseText || error), 'error');
                console.error('AJAX error:', status, error, 'Response:', xhr.responseText);
            }
        });
    });

    $(document).on('click', '.delete-btn', function() {
        if (confirm('Delete this product?')) {
            $.ajax({
                url: 'products.php',
                type: 'POST',
                data: {
                    action: 'delete',
                    id: $(this).data('id'),
                    csrf_token: $('#csrf_token').val()
                },
                success: function(response) {
                    try {
                        const res = JSON.parse(response);
                        showMessage(res.message, res.success ? 'success' : 'error');
                        if (res.success) loadProducts();
                    } catch (e) {
                        showMessage('Error deleting product: ' + e.message, 'error');
                        console.error('Parse error:', e, 'Response:', response);
                    }
                },
                error: function(xhr, status, error) {
                    showMessage('Error deleting product: ' + (xhr.responseText || error), 'error');
                    console.error('AJAX error:', status, error, 'Response:', xhr.responseText);
                }
            });
        }
    });

    function debounce(func, wait) {
        let timeout;
        return function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, arguments), wait);
        };
    }

    function loadProducts() {
        $.ajax({
            url: 'products.php',
            type: 'GET',
            data: { action: 'list' },
            success: function(response) {
                try {
                    renderTable(JSON.parse(response));
                } catch (e) {
                    showMessage('Error loading products: ' + e.message, 'error');
                    console.error('Parse error:', e, 'Response:', response);
                }
            },
            error: function(xhr, status, error) {
                showMessage('Error loading products: ' + (xhr.responseText || error), 'error');
                console.error('AJAX error:', status, error, 'Response:', xhr.responseText);
            }
        });
    }

    function renderTable(products) {
        $('#productTable tbody').html(products.length === 0
            ? '<tr><td colspan="8" style="text-align:center;">No products found</td></tr>'
            : products.map(product => `
                <tr>
                    <td>${product.id}</td>
                    <td>${product.type}</td>
                    <td>${product.sku || '-'}</td>
                    <td>${product.name || '-'}</td>
                    <td>${product.author}</td>
                    <td>${product.price ? '$' + parseFloat(product.price).toFixed(2) : '-'}</td>
                    <td>${product.stock_quantity || '-'}</td>
                    <td class="action-buttons">
                        <button class="edit-btn" data-id="${product.id}">Edit</button>
                        <button class="delete-btn" data-id="${product.id}">Delete</button>
                    </td>
                </tr>
            `).join(''));
    }
});

function showForm() {
    $('#formContainer').addClass('active');
    $('.action-bar, .table-container, .search-container').hide();
    window.scrollTo(0, 0);
}

function hideForm() {
    $('#formContainer').removeClass('active');
    setTimeout(() => {
        $('.action-bar, .table-container, .search-container').show();
        $('#productForm')[0].reset();
        $('#productId, #file_size, #existing_filepath, #existing_thumbs').val('');
    }, 300);
}