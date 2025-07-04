<?php
session_start();
require 'config.php';

// Function to execute SQL queries safely
function executeQuery($conn, $sql, $params = array()) {
    $stmt = sqlsrv_query($conn, $sql, $params);
    if ($stmt === false) {
        die(print_r(sqlsrv_errors(), true));
    }
    return $stmt;
}

// Get all products
$productQuery = "SELECT product_id, name, description, price, image_url, is_available,
                created_at, updated_at FROM Products ORDER BY product_id";
$productsResult = executeQuery($conn, $productQuery);

// Check if form was submitted for add/edit/delete
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $product_id = $_POST['product_id'] ?? null;
        
        try {
            if ($action === 'delete' && $product_id) {
                // Delete product
                $deleteQuery = "DELETE FROM Products WHERE product_id = ?";
                $params = array($product_id);
                executeQuery($conn, $deleteQuery, $params);
                $_SESSION['flash_message'] = "Product deleted successfully.";
                header("Location: adminproduct.php");
                exit();
            } elseif ($action === 'save') {
                // Add or update product
                $name = $_POST['name'];
                $description = $_POST['description'];
                $price = $_POST['price'];
                $image_url = $_POST['image_url'];
                $is_available = isset($_POST['is_available']) ? 1 : 0;
                
                if ($product_id) {
                    // Update existing product
                    $updateQuery = "UPDATE Products SET 
                                  name = ?, description = ?, price = ?, 
                                  image_url = ?, is_available = ?, updated_at = GETDATE()
                                  WHERE product_id = ?";
                    $params = array($name, $description, $price, $image_url, $is_available, $product_id);
                    executeQuery($conn, $updateQuery, $params);
                    $_SESSION['flash_message'] = "Product updated successfully.";
                } else {
                    // Add new product
                    $insertQuery = "INSERT INTO Products 
                                   (name, description, price, image_url, is_available)
                                   VALUES (?, ?, ?, ?, ?)";
                    $params = array($name, $description, $price, $image_url, $is_available);
                    executeQuery($conn, $insertQuery, $params);
                    $_SESSION['flash_message'] = "Product added successfully.";
                }
                header("Location: adminproduct.php");
                exit();
            }
        } catch (Exception $e) {
            die("Error: " . $e->getMessage());
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Crumby Cafe - Product Management</title>
    <link rel="stylesheet" href="admindashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .flash-message {
            background-color: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 4px;
            margin: 10px 0;
            text-align: center;
            border: 1px solid #c3e6cb;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <nav>
        <div class="sidebar">
            <div class="sidebar-header">Admin Dashboard</div>
            <ul class="sidebar-menu">
                <li><a href="admindashboard.php">Dashboard</a></li>
                <li><a href="adminauthorize.php">Staff</a></li>
                <li><a href="adminproduct.php">Product</a></li>
            </ul>
        </div>
    </nav>

    <div class="main-content">
        <div class="header">
            <h1>Product Management</h1>
            <button class="logout-btn" onclick="window.location.href='index.php'">Logout</button>
        </div>

        <!-- Flash Message -->
        <?php if (isset($_SESSION['flash_message'])): ?>
            <div class="flash-message">
                <?php echo htmlspecialchars($_SESSION['flash_message']); ?>
            </div>
            <?php unset($_SESSION['flash_message']); ?>
        <?php endif; ?>

        <div class="action-bar">
            <button id="addProductBtn" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add Product
            </button>
        </div>

        <div class="stats-container">
            <div class="card">
                <h2>Product Information</h2>
                <div class="table-responsive">
                    <table id="productTable">
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Price</th>
                                <th>Image</th>
                                <th>Availability</th>
                                <th>Created</th>
                                <th>Updated</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (sqlsrv_has_rows($productsResult)): ?>
                                <?php while ($product = sqlsrv_fetch_array($productsResult, SQLSRV_FETCH_ASSOC)): ?>
                                    <tr>
                                        <td>#<?php echo htmlspecialchars($product['product_id']); ?></td>
                                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                                        <td><?php echo htmlspecialchars($product['description']); ?></td>
                                        <td>RM<?php echo number_format($product['price'], 2); ?></td>
                                        <td>
                                            <?php if ($product['image_url']): ?>
                                                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" alt="Product Image" class="product-thumbnail">
                                            <?php else: ?>
                                                No Image
                                            <?php endif; ?>
                                        </td>
                                        <td class="status-<?php echo $product['is_available'] ? 'available' : 'unavailable'; ?>">
                                            <?php echo $product['is_available'] ? 'Available' : 'Unavailable'; ?>
                                        </td>
                                        <td><?php echo $product['created_at']->format('Y/m/d H:i'); ?></td>
                                        <td><?php echo $product['updated_at']->format('Y/m/d H:i'); ?></td>
                                        <td class="action-cell">
                                            <button class="btn btn-edit" data-id="<?php echo $product['product_id']; ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-delete" data-id="<?php echo $product['product_id']; ?>">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="9" class="no-products">No products found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<!-- Product Modal -->
<div id="productModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2 id="modalTitle">Add New Product</h2>
        <form id="productForm" method="POST">
            <input type="hidden" name="action" id="formAction" value="save">
            <input type="hidden" name="product_id" id="productId">
            <label for="name">Product Name:</label>
            <input type="text" id="name" name="name" required>

            <label for="description">Description:</label>
            <textarea id="description" name="description" required></textarea>

            <label for="price">Price:</label>
            <input type="number" id="price" name="price" step="0.01" required>

            <label for="image_url">Image URL:</label>
            <input type="text" id="image_url" name="image_url">

            <label><input type="checkbox" id="is_available" name="is_available" checked> Available</label>

            <button type="submit" class="btn btn-primary">Save</button>
            <button type="button" id="cancelBtn" class="btn">Cancel</button>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteModal" class="modal">
    <div class="modal-content">
        <span class="close">&times;</span>
        <h2>Confirm Deletion</h2>
        <p>Are you sure you want to delete this product?</p>
        <form id="deleteForm" method="POST">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="product_id" id="deleteProductId">
            <button type="submit" class="btn btn-danger">Yes, Delete</button>
            <button type="button" id="cancelDeleteBtn" class="btn">Cancel</button>
        </form>
    </div>
</div>


    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addProductBtn = document.getElementById('addProductBtn');
            const productModal = document.getElementById('productModal');
            const deleteModal = document.getElementById('deleteModal');
            const closeButtons = document.querySelectorAll('.close');
            const cancelBtn = document.getElementById('cancelBtn');
            const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');
            const productForm = document.getElementById('productForm');
            const deleteForm = document.getElementById('deleteForm');
            const modalTitle = document.getElementById('modalTitle');

            let currentProductId = null;
            let isEditMode = false;

            // Always reset modals on load
            productModal.style.display = 'none';
            deleteModal.style.display = 'none';

            addProductBtn.addEventListener('click', () => {
                isEditMode = false;
                modalTitle.textContent = 'Add New Product';
                document.getElementById('formAction').value = 'save';
                document.getElementById('productId').value = '';
                productForm.reset();
                document.getElementById('is_available').checked = true;
                productModal.style.display = 'block';
            });

            document.addEventListener('click', function (e) {
                if (e.target.closest('.btn-edit')) {
                    isEditMode = true;
                    modalTitle.textContent = 'Edit Product';
                    currentProductId = e.target.closest('button').getAttribute('data-id');
                    document.getElementById('formAction').value = 'save';
                    document.getElementById('productId').value = currentProductId;

                    const row = e.target.closest('tr');
                    document.getElementById('name').value = row.cells[1].textContent;
                    document.getElementById('description').value = row.cells[2].textContent;
                    document.getElementById('price').value = parseFloat(row.cells[3].textContent.replace('RM', ''));
                    document.getElementById('image_url').value = row.cells[4].querySelector('img')?.src || '';
                    document.getElementById('is_available').checked = row.cells[5].textContent === 'Available';

                    productModal.style.display = 'block';
                }

                if (e.target.closest('.btn-delete')) {
                    currentProductId = e.target.closest('button').getAttribute('data-id');
                    document.getElementById('deleteProductId').value = currentProductId;
                    deleteModal.style.display = 'block';
                }
            });

            function closeModals() {
                productModal.style.display = 'none';
                deleteModal.style.display = 'none';
            }

            closeButtons.forEach(btn => btn.addEventListener('click', closeModals));
            cancelBtn.addEventListener('click', closeModals);
            cancelDeleteBtn.addEventListener('click', closeModals);

            window.addEventListener('click', (e) => {
                if (e.target === productModal || e.target === deleteModal) {
                    closeModals();
                }
            });
        });
    </script>
</body>
</html>
