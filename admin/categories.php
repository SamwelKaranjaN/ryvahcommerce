<?php
require_once 'header.php';
require_once 'php/db_connect.php';

// Handle category operations
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_category'])) {
        $name = $_POST['name'];
        $description = $_POST['description'];
        $stmt = $conn->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->bind_param("ss", $name, $description);
        $stmt->execute();
    } elseif (isset($_POST['delete_category'])) {
        $category_id = $_POST['category_id'];
        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
    }
}

// Fetch categories with product count
$query = "SELECT c.*, COUNT(p.id) as product_count 
          FROM categories c 
          LEFT JOIN products p ON c.id = p.category_id 
          GROUP BY c.id 
          ORDER BY c.name ASC";
$result = $conn->query($query);
?>

<div class="main-content">
    <div class="page-header">
        <h2>Categories Management</h2>
        <div class="header-actions">
            <button class="btn btn-primary" onclick="showAddCategoryModal()">
                <i class="fas fa-plus"></i> Add Category
            </button>
        </div>
    </div>

    <div class="content-card">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Products</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($category = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $category['id']; ?></td>
                        <td><?php echo htmlspecialchars($category['name']); ?></td>
                        <td><?php echo htmlspecialchars($category['description']); ?></td>
                        <td><?php echo $category['product_count']; ?></td>
                        <td>
                            <button class="btn btn-sm btn-warning" onclick="editCategory(<?php echo $category['id']; ?>)">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-sm btn-danger" onclick="deleteCategory(<?php echo $category['id']; ?>)">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div id="addCategoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Add New Category</h3>
            <span class="close" onclick="closeAddCategoryModal()">&times;</span>
        </div>
        <form method="POST" class="modal-body">
            <div class="form-group">
                <label for="name">Category Name</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="3"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" name="add_category" class="btn btn-primary">Add Category</button>
                <button type="button" class="btn btn-secondary" onclick="closeAddCategoryModal()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<style>
.modal {
    display: none;
    position: fixed;
    z-index: 1000;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
}

.modal-content {
    background-color: #fff;
    margin: 10% auto;
    padding: 20px;
    border-radius: 8px;
    width: 50%;
    max-width: 500px;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.close {
    font-size: 24px;
    cursor: pointer;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
}

.form-group input,
.form-group textarea {
    width: 100%;
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
}

.btn-sm {
    padding: 4px 8px;
    font-size: 0.875rem;
    margin: 0 2px;
}
</style>

<script>
function showAddCategoryModal() {
    document.getElementById('addCategoryModal').style.display = 'block';
}

function closeAddCategoryModal() {
    document.getElementById('addCategoryModal').style.display = 'none';
}

function editCategory(categoryId) {
    window.location.href = `edit-category.php?id=${categoryId}`;
}

function deleteCategory(categoryId) {
    if (confirm('Are you sure you want to delete this category? This will also remove it from all associated products.')) {
        fetch('php/delete_category.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ category_id: categoryId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error deleting category: ' + data.message);
            }
        });
    }
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target == document.getElementById('addCategoryModal')) {
        closeAddCategoryModal();
    }
}
</script>

<?php require_once 'footer.php'; ?> 