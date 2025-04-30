<?php
require_once 'includes/config.php';

// Get total products count
$result = $conn->query("SELECT COUNT(*) as total FROM products");
$total_products = $result->fetch_assoc()['total'];

// Get low stock items count
$result = $conn->query("SELECT COUNT(*) as total FROM products WHERE status = 'Low Stock'");
$low_stock = $result->fetch_assoc()['total'];

// Get out of stock items count
$result = $conn->query("SELECT COUNT(*) as total FROM products WHERE status = 'Out of Stock'");
$out_of_stock = $result->fetch_assoc()['total'];

// Get all products
$result = $conn->query("SELECT * FROM products ORDER BY created_at DESC");
$products = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Pro</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.2/gsap.min.js"></script>
</head>
<body>
    <!-- Loading Screen -->
    <div class="loading-overlay">
        <div class="loading-spinner"></div>
    </div>

    <div class="container">
        <header class="header">
            <div class="logo">
                <svg width="32" height="32" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="3" y="3" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/>
                    <path d="M3 9H21" stroke="currentColor" stroke-width="2"/>
                    <path d="M9 21V9" stroke="currentColor" stroke-width="2"/>
                </svg>
                Inventory Pro
            </div>
            <div class="header-right">
                <div class="notification-wrapper">
                    <svg class="notification-icon" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                        <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                        <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                    </svg>
                    <div class="notification-badge">0</div>
                    <div class="notifications-dropdown">
                        <div class="notifications-header">
                            <h3>Notifications</h3>
                            <button class="btn-icon mark-all-read">Mark all as read</button>
                        </div>
                        <div class="notifications-list">
                            <!-- Notifications will be loaded here -->
                        </div>
                    </div>
                </div>
                <div class="profile-icon">
                    <svg width="40" height="40" viewBox="0 0 40 40" fill="none">
                        <circle cx="20" cy="20" r="20" fill="#00A76F"/>
                        <text x="20" y="25" text-anchor="middle" fill="white" font-size="16" font-family="Plus Jakarta Sans">A</text>
                    </svg>
                </div>
            </div>
        </header>

        <div class="stats-container">
            <div class="stat-card">
                <h3>Total Products</h3>
                <div class="number" data-value="<?php echo $total_products; ?>">0</div>
                <div class="trend up">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M10 15.8333V4.16666M10 4.16666L4.16669 10M10 4.16666L15.8334 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    12% from last month
                </div>
            </div>
            <div class="stat-card">
                <h3>Low Stock Items</h3>
                <div class="number" data-value="<?php echo $low_stock; ?>">0</div>
                <div class="trend up">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M10 15.8333V4.16666M10 4.16666L4.16669 10M10 4.16666L15.8334 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    3 more than last week
                </div>
            </div>
            <div class="stat-card">
                <h3>Out of Stock</h3>
                <div class="number" data-value="<?php echo $out_of_stock; ?>">0</div>
                <div class="trend down">
                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                        <path d="M10 4.16666V15.8333M10 15.8333L4.16669 10M10 15.8333L15.8334 10" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                    2 fewer than last week
                </div>
            </div>
        </div>

        <div class="products-header">
            <h2>Products</h2>
            <button id="addProductBtn" class="btn btn-primary">
                <svg width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M10 4.16666V15.8333M4.16669 10H15.8334" stroke="white" stroke-width="2" stroke-linecap="round"/>
                </svg>
                Add Product
            </button>
        </div>

        <input type="text" id="searchProducts" class="search-bar" placeholder="Search products...">

        <div class="products-table">
            <table>
                <thead>
                    <tr>
                        <th data-sort="string">Product Name</th>
                        <th data-sort="string">Category</th>
                        <th data-sort="number">Price</th>
                        <th data-sort="number">Stock</th>
                        <th data-sort="string">Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($products as $product): ?>
                        <tr class="product-row">
                            <td data-label="Product Name"><?php echo htmlspecialchars($product['product_name']); ?></td>
                            <td data-label="Category"><?php echo htmlspecialchars($product['category']); ?></td>
                            <td data-label="Price">$<?php echo number_format($product['price'], 2); ?></td>
                            <td data-label="Stock"><?php echo $product['stock']; ?></td>
                            <td data-label="Status">
                                <span class="status-badge status-<?php echo strtolower(str_replace(' ', '-', $product['status'])); ?>">
                                    <?php echo $product['status']; ?>
                                </span>
                            </td>
                            <td data-label="Actions" class="actions">
                                <button class="btn-icon edit-product" data-id="<?php echo $product['id']; ?>" 
                                    data-name="<?php echo htmlspecialchars($product['product_name']); ?>"
                                    data-category="<?php echo htmlspecialchars($product['category']); ?>"
                                    data-price="<?php echo $product['price']; ?>"
                                    data-stock="<?php echo $product['stock']; ?>">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                                        <path d="M9.16669 3.33334H3.33335C2.89133 3.33334 2.46739 3.50893 2.15483 3.82149C1.84227 4.13405 1.66669 4.55798 1.66669 5.00001V16.6667C1.66669 17.1087 1.84227 17.5326 2.15483 17.8452C2.46739 18.1577 2.89133 18.3333 3.33335 18.3333H15C15.442 18.3333 15.866 18.1577 16.1785 17.8452C16.4911 17.5326 16.6667 17.1087 16.6667 16.6667V10.8333" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M15.4167 2.08333C15.7483 1.75181 16.1979 1.56555 16.6667 1.56555C17.1354 1.56555 17.585 1.75181 17.9167 2.08333C18.2482 2.41485 18.4345 2.86449 18.4345 3.33333C18.4345 3.80217 18.2482 4.25181 17.9167 4.58333L10 12.5L6.66669 13.3333L7.50002 10L15.4167 2.08333Z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                                <button class="btn-icon delete-product" data-id="<?php echo $product['id']; ?>">
                                    <svg width="20" height="20" viewBox="0 0 20 20" fill="none" stroke="currentColor">
                                        <path d="M2.5 5H17.5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M15.8334 5V16.6667C15.8334 17.5 15 18.3333 14.1667 18.3333H5.83335C5.00002 18.3333 4.16669 17.5 4.16669 16.6667V5" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                        <path d="M6.66669 4.99999V3.33333C6.66669 2.49999 7.50002 1.66666 8.33335 1.66666H11.6667C12.5 1.66666 13.3334 2.49999 13.3334 3.33333V4.99999" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <h2>Add New Product</h2>
            <form id="productForm">
                <input type="hidden" id="product_id" name="product_id">
                <div class="form-group">
                    <label for="product_name">Product Name</label>
                    <input type="text" id="product_name" name="product_name" required>
                </div>
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category" required>
                        <option value="">Select Category</option>
                        <option value="Electronics">Electronics</option>
                        <option value="Furniture">Furniture</option>
                        <option value="Clothing">Clothing</option>
                        <option value="Books">Books</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="price">Price</label>
                    <input type="number" id="price" name="price" step="0.01" required>
                </div>
                <div class="form-group">
                    <label for="stock">Stock</label>
                    <input type="number" id="stock" name="stock" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close-modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Product</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/main.js"></script>
</body>
</html>
<?php $conn->close(); ?>
