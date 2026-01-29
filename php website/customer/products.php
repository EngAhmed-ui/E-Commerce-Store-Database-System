<?php
include "../config/db.php";
if (session_status() === PHP_SESSION_NONE) session_start();

$category_id = intval($_GET['category_id'] ?? 0);

$categories_res = $conn->query("SELECT * FROM Categories ORDER BY name ASC");

$sql = "
    SELECT p.*, c.name AS category_name
    FROM Products p
    LEFT JOIN Categories c ON p.category_id = c.category_id
    WHERE p.status='active'
";

if ($category_id > 0) {
    $stmt = $conn->prepare($sql . " AND p.category_id=? ORDER BY p.name ASC");
    $stmt->bind_param("i", $category_id);
    $stmt->execute();
    $products_res = $stmt->get_result();
    $stmt->close();
} else {
    $products_res = $conn->query($sql . " ORDER BY p.name ASC");
}
?>

<?php include "../includes/header.php"; ?>

<!-- Interactive Category Filter -->
<div class="category-filter-section mb-5">
    <h2 class="mb-4">🛍️ All Products</h2>
    
    <div class="category-filter-container mb-4">
        <div class="d-flex flex-wrap gap-2 mb-3">
            <?php 
                $all_class = $category_id == 0 ? 'btn-danger active-category' : 'btn-outline-secondary';
            ?>
            <a href="products.php" class="btn btn-lg <?= $all_class; ?> px-4 py-2 rounded-pill">
                <i class="fas fa-th-large me-2"></i>All Products
            </a>
            
            <?php while($cat = $categories_res->fetch_assoc()): ?>
                <?php 
                $active_class = $category_id == $cat['category_id'] ? 'btn-danger active-category' : 'btn-outline-secondary'; 
                ?>
                <a href="products.php?category_id=<?php echo $cat['category_id']; ?>" 
                   class="btn btn-lg <?= $active_class ?> px-4 py-2 rounded-pill category-btn">
                    <i class="fas fa-tag me-2"></i><?php echo htmlspecialchars($cat['name']); ?>
                </a>
            <?php endwhile; ?>
        </div>
        
        <?php if($category_id > 0): ?>
            <?php 
                $active_cat = $conn->query("SELECT name FROM Categories WHERE category_id = $category_id")->fetch_assoc();
            ?>
            <div class="alert alert-info alert-dismissible fade show d-flex align-items-center" role="alert">
                <i class="fas fa-filter me-3 fs-4"></i>
                <div>
                    <strong>Filter Applied:</strong> Showing products from 
                    <span class="badge bg-danger ms-1"><?php echo htmlspecialchars($active_cat['name']); ?></span> category
                </div>
                <a href="products.php" class="btn-close ms-auto" aria-label="Clear filter"></a>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Enhanced Product Grid -->
<div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-4 product-grid">
<?php 
if ($products_res && $products_res->num_rows > 0):
    $product_count = 0;
    while($prod = $products_res->fetch_assoc()): 
    $product_count++;

    // FIXED: Correct image path handling for customer folder
    $image_url = '';
    
    if (!empty($prod['image_url'])) {
        $image_url = $prod['image_url'];
        
        // If it's just a filename
        if (!str_contains($image_url, '/') && !str_contains($image_url, 'http')) {
            // Check if file exists in different possible locations
            $possible_paths = [
                $_SERVER['DOCUMENT_ROOT'] . '/ecommerce_project/assets/images/products/' . $image_url,
                $_SERVER['DOCUMENT_ROOT'] . '/ecommerce_project/url/products/' . $image_url,
                $_SERVER['DOCUMENT_ROOT'] . '/assets/images/products/' . $image_url,
                $_SERVER['DOCUMENT_ROOT'] . '/url/products/' . $image_url
            ];
            
            foreach ($possible_paths as $path) {
                if (file_exists($path)) {
                    // Convert to relative URL from customer folder
                    $relative_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $path);
                    $image_url = '..' . $relative_path;
                    break;
                }
            }
            
            // If still not found, try direct paths
            if (strpos($image_url, '/') === false) {
                if (file_exists('../assets/images/products/' . $image_url)) {
                    $image_url = '../assets/images/products/' . $image_url;
                } elseif (file_exists('../url/products/' . $image_url)) {
                    $image_url = '../url/products/' . $image_url;
                }
            }
        }
        // If it's a relative path starting with assets/ or url/
        elseif (strpos($image_url, 'assets/') === 0 || strpos($image_url, 'url/') === 0) {
            $image_url = '../' . $image_url;
        }
    }
    
    // If still empty, use placeholder
    if (empty($image_url)) {
        $image_url = 'https://via.placeholder.com/300x200/cccccc/969696?text=Product+Image';
    }
    
    $variant_sql = "SELECT variant_id, stock_quantity FROM Product_Variants WHERE product_id = ? AND is_available = 1 LIMIT 1";
    $variant_stmt = $conn->prepare($variant_sql);
    $variant_stmt->bind_param("i", $prod['product_id']);
    $variant_stmt->execute();
    $variant_result = $variant_stmt->get_result();
    $variant = $variant_result->fetch_assoc();
    $variant_stmt->close();
    
    $stock_quantity = $variant ? $variant['stock_quantity'] : 0;
    $variant_id = $variant ? $variant['variant_id'] : 0;
    
    // Stock status indicator
    $stock_badge_class = $stock_quantity > 0 ? 'bg-success' : 'bg-danger';
    $stock_badge_text = $stock_quantity > 0 ? 'In Stock' : 'Out of Stock';
?>

    <div class="col">
        <div class="card h-100 shadow-lg product-card interactive-card" 
             data-product-id="<?= $prod['product_id'] ?>"
             onclick="window.location.href='../product.php?id=<?= $prod['product_id'] ?>'">
            
            <!-- Product Image with Overlay -->
            <div class="product-image-container position-relative">
                <img src="<?php echo $image_url; ?>" 
                     class="card-img-top product-image" 
                     alt="<?php echo htmlspecialchars($prod['name']); ?>" 
                     loading="lazy"
                     onerror="this.onerror=null;this.src='https://via.placeholder.com/300x200/cccccc/969696?text=Product+Image';">
                
                <!-- Stock Badge -->
                <div class="position-absolute top-0 start-0 m-2">
                    <span class="badge <?= $stock_badge_class ?> px-3 py-2 stock-badge">
                        <i class="fas <?= $stock_quantity > 0 ? 'fa-check-circle' : 'fa-times-circle' ?> me-1"></i>
                        <?= $stock_badge_text ?>
                    </span>
                </div>
                
                <!-- Quick View Button -->
                <div class="image-overlay">
                    <button class="btn btn-light quick-view-btn" 
                            onclick="event.stopPropagation(); window.location.href='../product.php?id=<?= $prod['product_id'] ?>'">
                        <i class="fas fa-eye me-1"></i> Quick View
                    </button>
                </div>
                
                <!-- Category Badge -->
                <div class="position-absolute top-0 end-0 m-2">
                    <span class="badge bg-secondary category-badge">
                        <?php echo htmlspecialchars($prod['category_name']); ?>
                    </span>
                </div>
            </div>
            
            <!-- Card Body -->
            <div class="card-body d-flex flex-column">
                <h5 class="card-title text-truncate-2 product-name"><?php echo htmlspecialchars($prod['name']); ?></h5>
                
                <!-- Product Description Preview -->
                <p class="card-text text-muted small mb-2 product-description-preview">
                    <?php 
                        $short_desc = substr($prod['description'] ?? '', 0, 80);
                        echo htmlspecialchars($short_desc . (strlen($prod['description'] ?? '') > 80 ? '...' : ''));
                    ?>
                </p>
                
                <!-- Price and Stock Info -->
                <div class="price-section mt-auto">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <p class="fs-4 text-danger fw-bold mb-0 product-price">
                            $<?php echo number_format($prod['price'],2); ?>
                        </p>
                        
                        <?php if($stock_quantity > 0): ?>
                            <small class="text-muted stock-indicator">
                                <i class="fas fa-box me-1"></i><?php echo $stock_quantity; ?> left
                            </small>
                        <?php endif; ?>
                    </div>

                    <!-- Add to Cart Form -->
                    <?php if($stock_quantity > 0 && $variant_id > 0): ?>
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <!-- User is logged in - show add to cart button -->
                            <form method="POST" action="../cart/add_to_cart.php" onclick="event.stopPropagation();" 
                                  class="add-to-cart-form mt-2">
                                <input type="hidden" name="variant_id" value="<?php echo $variant_id; ?>">
                                
                                <div class="d-flex align-items-center">
                                    <div class="input-group input-group-sm quantity-selector me-2" style="width: 100px;">
                                        <button type="button" class="btn btn-outline-secondary btn-sm quantity-minus">
                                            <i class="fas fa-minus"></i>
                                        </button>
                                        <input type="number" name="quantity" value="1" min="1" max="<?php echo $stock_quantity; ?>" 
                                               class="form-control form-control-sm text-center quantity-input" readonly>
                                        <button type="button" class="btn btn-outline-secondary btn-sm quantity-plus">
                                            <i class="fas fa-plus"></i>
                                        </button>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-danger btn-sm flex-grow-1 add-to-cart-btn">
                                        <i class="fas fa-shopping-cart me-1"></i>Add to Cart
                                    </button>
                                </div>
                            </form>
                        <?php else: ?>
                            <!-- User is not logged in - show login prompt -->
                            <div class="mt-2" onclick="event.stopPropagation();">
                                <a href="../auth/login.php?redirect=<?php echo urlencode($_SERVER['REQUEST_URI']); ?>" 
                                   class="btn btn-danger btn-sm w-100">
                                    <i class="fas fa-sign-in-alt me-1"></i>Sign In to Purchase
                                </a>
                                <small class="text-muted d-block mt-1 text-center">
                                    <i class="fas fa-info-circle me-1"></i>You must be signed in to add items to cart
                                </small>
                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <button disabled class="btn btn-outline-secondary btn-sm w-100 mt-2 disabled-btn">
                            <i class="fas fa-ban me-1"></i>Out of Stock
                        </button>
                    <?php endif; ?>
                    
                    <!-- Quick Actions -->
                    <div class="quick-actions d-flex justify-content-between mt-3">
                        <a href="../product.php?id=<?= $prod['product_id'] ?>" 
                           class="text-decoration-none small text-primary">
                            <i class="fas fa-info-circle me-1"></i>Details
                        </a>
                        <!-- Removed Save/Wishlist button as requested -->
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endwhile; 
else: ?>
    <div class="col-12">
        <div class="empty-state text-center py-5">
            <div class="empty-icon mb-4">
                <i class="fas fa-box-open fa-4x text-muted"></i>
            </div>
            <h3 class="text-muted mb-3">No products found</h3>
            <p class="text-muted mb-4">
                <?php if($category_id > 0): ?>
                    No products available in this category. Try another category!
                <?php else: ?>
                    No products available at the moment. Please check back later!
                <?php endif; ?>
            </p>
            <a href="products.php" class="btn btn-danger">
                <i class="fas fa-redo me-2"></i>View All Products
            </a>
        </div>
    </div>
<?php endif; ?>
</div>

<!-- Results Count -->
<?php if(isset($product_count) && $product_count > 0): ?>
<div class="row mt-4">
    <div class="col-12">
        <div class="alert alert-light border d-flex justify-content-between align-items-center">
            <span class="text-muted">
                <i class="fas fa-chart-bar me-2"></i>
                Showing <strong><?php echo $product_count; ?></strong> product<?php echo $product_count > 1 ? 's' : ''; ?>
                <?php if($category_id > 0): ?>
                    in <span class="badge bg-danger"><?php echo htmlspecialchars($active_cat['name']); ?></span> category
                <?php endif; ?>
            </span>
            <a href="../index.php" class="btn btn-outline-danger btn-sm">
                <i class="fas fa-home me-1"></i>Back to Home
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Interactive JavaScript -->
<script>
// Quantity selector functionality
document.querySelectorAll('.quantity-minus').forEach(button => {
    button.addEventListener('click', function(e) {
        e.stopPropagation();
        const input = this.parentElement.querySelector('.quantity-input');
        if (parseInt(input.value) > parseInt(input.min)) {
            input.value = parseInt(input.value) - 1;
        }
    });
});

document.querySelectorAll('.quantity-plus').forEach(button => {
    button.addEventListener('click', function(e) {
        e.stopPropagation();
        const input = this.parentElement.querySelector('.quantity-input');
        if (parseInt(input.value) < parseInt(input.max)) {
            input.value = parseInt(input.value) + 1;
        }
    });
});

// Product card hover effects
document.querySelectorAll('.interactive-card').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px)';
        this.style.boxShadow = '0 10px 20px rgba(0,0,0,0.15)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
        this.style.boxShadow = '0 5px 15px rgba(0,0,0,0.1)';
    });
});

// Add to cart form submission feedback
document.querySelectorAll('.add-to-cart-form').forEach(form => {
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const submitBtn = this.querySelector('.add-to-cart-btn');
        const originalText = submitBtn.innerHTML;
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Adding...';
        submitBtn.disabled = true;
        
        // Simulate API call (replace with actual fetch)
        setTimeout(() => {
            fetch(this.action, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                submitBtn.innerHTML = '<i class="fas fa-check me-1"></i>Added!';
                submitBtn.classList.remove('btn-danger');
                submitBtn.classList.add('btn-success');
                
                // Reset after 2 seconds
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.classList.remove('btn-success');
                    submitBtn.classList.add('btn-danger');
                    submitBtn.disabled = false;
                }, 2000);
            })
            .catch(error => {
                submitBtn.innerHTML = '<i class="fas fa-times me-1"></i>Error';
                setTimeout(() => {
                    submitBtn.innerHTML = originalText;
                    submitBtn.disabled = false;
                }, 2000);
            });
        }, 500);
    });
});

// Toast notification function (for login messages)
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
        ${message}
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => toast.classList.add('show'), 10);
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}
</script>

<!-- Custom CSS for enhanced design -->
<style>
.product-grid {
    margin-bottom: 3rem;
}

.product-card {
    transition: all 0.3s ease;
    border: none;
    border-radius: 15px;
    overflow: hidden;
    position: relative;
}

.product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.15) !important;
}

.product-image-container {
    height: 250px; /* Increased image height */
    overflow: hidden;
    position: relative;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.product-image {
    height: 100%;
    width: 100%;
    object-fit: cover;
    transition: transform 0.5s ease;
}

.product-card:hover .product-image {
    transform: scale(1.05);
}

.image-overlay {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.7));
    padding: 20px;
    transform: translateY(100%);
    transition: transform 0.3s ease;
}

.product-card:hover .image-overlay {
    transform: translateY(0);
}

.quick-view-btn {
    width: 100%;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.product-card:hover .quick-view-btn {
    opacity: 1;
}

.stock-badge {
    font-size: 0.75rem;
    border-radius: 20px;
    backdrop-filter: blur(10px);
    background-color: rgba(25, 135, 84, 0.9) !important;
}

.category-badge {
    font-size: 0.7rem;
    padding: 0.35rem 0.75rem;
    border-radius: 20px;
}

.text-truncate-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    min-height: 3em;
}

.product-description-preview {
    line-height: 1.4;
    min-height: 2.8em;
}

.quantity-selector {
    border-radius: 25px;
    overflow: hidden;
}

.quantity-selector .btn {
    border: none;
    background: #f8f9fa;
}

.quantity-selector .btn:hover {
    background: #e9ecef;
}

.quantity-input {
    border: none;
    background: #fff;
    font-weight: bold;
}

.add-to-cart-btn {
    border-radius: 25px;
    padding: 0.5rem 1rem;
    transition: all 0.3s ease;
    border: none;
}

.add-to-cart-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(220, 53, 69, 0.3);
}

.disabled-btn {
    border-radius: 25px;
    padding: 0.5rem 1rem;
    opacity: 0.7;
}

.quick-actions {
    border-top: 1px solid #f1f1f1;
    padding-top: 0.75rem;
}

.category-btn {
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
}

.category-btn:hover {
    transform: translateY(-2px);
}

.category-btn::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    width: 0;
    height: 2px;
    background: #dc3545;
    transition: all 0.3s ease;
    transform: translateX(-50%);
}

.category-btn:hover::after {
    width: 80%;
}

.active-category {
    position: relative;
    padding-right: 2.5rem !important;
}

.active-category::before {
    content: '✓';
    position: absolute;
    right: 1rem;
    font-weight: bold;
}

.empty-state {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 15px;
    border: 2px dashed #dee2e6;
}

.empty-icon {
    color: #6c757d;
    opacity: 0.5;
}

.toast-notification {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #333;
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 10px;
    display: flex;
    align-items: center;
    z-index: 9999;
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.3s ease;
}

.toast-notification.show {
    opacity: 1;
    transform: translateY(0);
}

.toast-success {
    background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
}

.toast-info {
    background: linear-gradient(135deg, #17a2b8 0%, #0dcaf0 100%);
}

@media (max-width: 768px) {
    .product-image-container {
        height: 200px;
    }
    
    .category-btn {
        font-size: 0.9rem;
        padding: 0.5rem 1rem;
    }
}

@media (max-width: 576px) {
    .product-image-container {
        height: 180px;
    }
}
</style>

<?php include "../includes/footer.php"; ?>