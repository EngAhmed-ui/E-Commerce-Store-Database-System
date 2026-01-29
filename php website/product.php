<?php
session_start();
include "config/db.php";
include "includes/header.php";

$product_id = $_GET['id'] ?? 0;

if ($product_id == 0) {
    header("Location: index.php");
    exit;
}

$sql = "SELECT p.*, c.name AS category_name 
        FROM Products p 
        LEFT JOIN Categories c ON p.category_id = c.category_id 
        WHERE p.product_id = ? AND p.status='active'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$res = $stmt->get_result();
$stmt->close();

if($res->num_rows == 0){
    echo "<div class='alert alert-warning'>Product not found or is inactive.</div>";
    include "includes/footer.php";
    exit;
}

$product = $res->fetch_assoc();

$variants_sql = "SELECT pv.* FROM Product_Variants pv WHERE pv.product_id = ? AND pv.is_available = 1";
$variants_stmt = $conn->prepare($variants_sql);
$variants_stmt->bind_param("i", $product_id);
$variants_stmt->execute();
$variants_res = $variants_stmt->get_result();
$variants_stmt->close();

$image_url = $product['image_url'] ?: 'assets/images/placeholder.png'; 

// Get related products
$related_sql = "SELECT p.* FROM Products p WHERE p.category_id = ? AND p.product_id != ? AND p.status='active' LIMIT 4";
$related_stmt = $conn->prepare($related_sql);
$related_stmt->bind_param("ii", $product['category_id'], $product_id);
$related_stmt->execute();
$related_res = $related_stmt->get_result();
$related_stmt->close();
?>

<!-- Enhanced Product Page -->
<div class="product-detail-page">
    <!-- Breadcrumb Navigation -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
            <li class="breadcrumb-item"><a href="customer/products.php">Products</a></li>
            <li class="breadcrumb-item"><a href="customer/products.php?category_id=<?= $product['category_id'] ?>">
                <?= htmlspecialchars($product['category_name']) ?>
            </a></li>
            <li class="breadcrumb-item active" aria-current="page"><?= htmlspecialchars($product['name']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Product Images Section -->
        <div class="col-lg-5">
            <div class="product-image-section sticky-top" style="top: 20px;">
                <!-- Main Image -->
                <div class="main-image-container mb-3">
                    <img src="<?= htmlspecialchars($image_url) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>"
                         class="img-fluid rounded-3 shadow-lg main-product-image"
                         id="mainProductImage"
                         onerror="this.onerror=null;this.src='assets/images/placeholder.png';">
                    
                    <!-- Badges -->
                    <div class="product-badges position-absolute top-0 start-0 m-3">
                        <?php if($variants_res->num_rows > 0): 
                            $first_variant = $variants_res->fetch_assoc();
                            $variants_res->data_seek(0);
                        ?>
                            <?php if($first_variant['stock_quantity'] > 0): ?>
                                <span class="badge bg-success px-3 py-2 mb-2">
                                    <i class="fas fa-check-circle me-1"></i> In Stock
                                </span>
                            <?php else: ?>
                                <span class="badge bg-danger px-3 py-2 mb-2">
                                    <i class="fas fa-times-circle me-1"></i> Out of Stock
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                        
                        <span class="badge bg-info px-3 py-2">
                            <i class="fas fa-tag me-1"></i> <?= htmlspecialchars($product['category_name']) ?>
                        </span>
                    </div>
                    
                    <!-- Image Zoom Overlay -->
                    <div class="image-zoom-overlay" id="zoomOverlay">
                        <div class="zoom-container" id="zoomContainer"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Product Details Section -->
        <div class="col-lg-7">
            <div class="product-details-section">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div>
                        <h1 class="display-5 fw-bold mb-2"><?= htmlspecialchars($product['name']) ?></h1>
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="rating">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= 4 ? 'text-warning' : 'text-muted' ?>"></i>
                                <?php endfor; ?>
                                <small class="text-muted ms-1">(4.2/5 • 128 reviews)</small>
                            </div>
                            <span class="badge bg-light text-dark border">
                                <i class="fas fa-bolt me-1"></i> Fast Delivery
                            </span>
                        </div>
                    </div>
                    
                    <!-- Price -->
                    <div class="text-end">
                        <div class="price-display">
                            <p class="display-4 text-danger fw-bold mb-0">$<?= number_format($product['price'], 2) ?></p>
                            <small class="text-muted">+ Free Shipping</small>
                        </div>
                    </div>
                </div>

                <!-- Product Description -->
                <div class="product-description mb-4">
                    <h5 class="mb-3">Description</h5>
                    <div class="description-content">
                        <p class="lead text-muted"><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                        
                        <!-- Key Features -->
                        <div class="key-features mt-4">
                            <h6 class="mb-3"><i class="fas fa-check-circle text-success me-2"></i>Key Features:</h6>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success me-2"></i> Premium Quality Materials</li>
                                <li><i class="fas fa-check text-success me-2"></i> 1 Year Warranty</li>
                                <li><i class="fas fa-check text-success me-2"></i> Eco-Friendly Packaging</li>
                                <li><i class="fas fa-check text-success me-2"></i> Free Returns within 30 Days</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Variants Selection -->
                <?php if($variants_res->num_rows > 0): ?>
                    <div class="variants-section mb-5">
                        <h5 class="mb-3">Available Options:</h5>
                        <div class="variants-container" id="variantsContainer">
                            <?php while($variant = $variants_res->fetch_assoc()): ?>
                                <?php
                                $attr_sql = "SELECT attribute_name, attribute_value FROM Variant_Attributes WHERE variant_id=?";
                                $attr_stmt = $conn->prepare($attr_sql);
                                $attr_stmt->bind_param("i", $variant['variant_id']);
                                $attr_stmt->execute();
                                $attr_res = $attr_stmt->get_result();
                                $attributes = [];
                                while($attr_row = $attr_res->fetch_assoc()){
                                    $attributes[] = htmlspecialchars($attr_row['attribute_name']).": ".htmlspecialchars($attr_row['attribute_value']);
                                }
                                $attr_stmt->close();
                                ?>
                                
                                <div class="variant-card <?= $variant['stock_quantity'] > 0 ? 'available' : 'unavailable' ?>"
                                     data-variant-id="<?= $variant['variant_id'] ?>"
                                     data-stock="<?= $variant['stock_quantity'] ?>">
                                    <div class="card h-100">
                                        <div class="card-body">
                                            <!-- Variant Attributes -->
                                            <?php if(!empty($attributes)): ?>
                                                <div class="variant-attributes mb-3">
                                                    <?php foreach($attributes as $attr): ?>
                                                        <span class="badge bg-secondary me-1 mb-1"><?= $attr ?></span>
                                                    <?php endforeach; ?>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <!-- Stock and Price -->
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <div>
                                                    <p class="mb-1 text-<?= $variant['stock_quantity'] > 0 ? 'success' : 'danger'; ?>">
                                                        <i class="fas <?= $variant['stock_quantity'] > 0 ? 'fa-check-circle' : 'fa-times-circle' ?> me-1"></i>
                                                        <?= $variant['stock_quantity'] > 0 ? $variant['stock_quantity'] . ' available' : 'Out of Stock'; ?>
                                                    </p>
                                                    <?php if($variant['stock_quantity'] > 0): ?>
                                                        <small class="text-muted">
                                                            <i class="fas fa-shipping-fast me-1"></i>
                                                            Ships in 1-2 business days
                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-end">
                                                    <span class="fs-5 fw-bold">$<?= number_format($product['price'], 2) ?></span>
                                                </div>
                                            </div>
                                            
                                            <!-- Add to Cart -->
                                            <?php if($variant['stock_quantity'] > 0): ?>
                                                <div class="add-to-cart-section">
                                                    <div class="d-flex align-items-center">
                                                        <div class="quantity-selector me-3">
                                                            <div class="input-group input-group-lg">
                                                                <button type="button" class="btn btn-outline-secondary quantity-btn minus">
                                                                    <i class="fas fa-minus"></i>
                                                                </button>
                                                                <input type="number" 
                                                                       class="form-control text-center quantity-input" 
                                                                       value="1" 
                                                                       min="1" 
                                                                       max="<?= $variant['stock_quantity'] ?>"
                                                                       data-variant-id="<?= $variant['variant_id'] ?>"
                                                                       readonly>
                                                                <button type="button" class="btn btn-outline-secondary quantity-btn plus">
                                                                    <i class="fas fa-plus"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        
                                                        <?php if(isset($_SESSION['user_id'])): ?>
                                                            <button type="button" 
                                                                    class="btn btn-danger btn-lg flex-fill add-to-cart-btn"
                                                                    data-variant-id="<?= $variant['variant_id'] ?>">
                                                                <i class="fas fa-cart-plus me-2"></i> Add to Cart
                                                            </button>
                                                        <?php else: ?>
                                                            <button type="button" 
                                                                    class="btn btn-danger btn-lg flex-fill add-to-cart-btn"
                                                                    onclick="showLoginRequired()"
                                                                    title="Please log in to add to cart">
                                                                <i class="fas fa-cart-plus me-2"></i> Login to Add to Cart
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            <?php else: ?>
                                                <button disabled class="btn btn-secondary btn-lg w-100">
                                                    <i class="fas fa-ban me-2"></i> Out of Stock
                                                </button>
                                                
                                                <!-- Notify Me Button -->
                                                <button class="btn btn-outline-primary btn-sm w-100 mt-2 notify-me-btn">
                                                    <i class="fas fa-bell me-1"></i> Notify When Available
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        No variants available for this product.
                    </div>
                <?php endif; ?>

                <!-- Product Specifications -->
                <div class="specifications-section mb-5">
                    <h5 class="mb-3"><i class="fas fa-clipboard-list me-2"></i> Specifications</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Category</th>
                                    <td><?= htmlspecialchars($product['category_name']) ?></td>
                                </tr>
                                <tr>
                                    <th>Product ID</th>
                                    <td>#<?= str_pad($product['product_id'], 6, '0', STR_PAD_LEFT) ?></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td><span class="badge bg-success">Active</span></td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Warranty</th>
                                    <td>1 Year</td>
                                </tr>
                                <tr>
                                    <th>Returns</th>
                                    <td>30 Days Free Returns</td>
                                </tr>
                                <tr>
                                    <th>Support</th>
                                    <td>24/7 Customer Support</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Login Required Message -->
                <?php if(!isset($_SESSION['user_id'])): ?>
                <div class="login-required alert alert-info" role="alert">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-info-circle fa-2x me-3"></i>
                        <div>
                            <h6 class="alert-heading mb-1">Login Required</h6>
                            <p class="mb-0">Please <a href="auth/login.php" class="alert-link">log in</a> to add items to your cart and access exclusive features.</p>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Related Products -->
    <?php if($related_res->num_rows > 0): ?>
    <div class="related-products mt-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0"><i class="fas fa-random me-2"></i> Related Products</h3>
            <a href="customer/products.php?category_id=<?= $product['category_id'] ?>" class="btn btn-outline-danger">
                View All in Category <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
        
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            <?php while($related = $related_res->fetch_assoc()): 
                $related_image = $related['image_url'] ?: 'assets/images/placeholder.png';
            ?>
                <div class="col">
                    <div class="card h-100 related-product-card" 
                         onclick="window.location.href='product.php?id=<?= $related['product_id'] ?>'">
                        <img src="<?= htmlspecialchars($related_image) ?>" 
                             class="card-img-top" 
                             alt="<?= htmlspecialchars($related['name']) ?>"
                             style="height: 150px; object-fit: cover;">
                        <div class="card-body">
                            <h6 class="card-title"><?= htmlspecialchars($related['name']) ?></h6>
                            <p class="card-text text-danger fw-bold mb-0">$<?= number_format($related['price'], 2) ?></p>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Interactive JavaScript -->
<script>
// Image Zoom Functionality
const mainImage = document.getElementById('mainProductImage');
const zoomOverlay = document.getElementById('zoomOverlay');
const zoomContainer = document.getElementById('zoomContainer');

if (mainImage) {
    mainImage.addEventListener('mousemove', function(e) {
        const rect = this.getBoundingClientRect();
        const x = ((e.clientX - rect.left) / this.offsetWidth) * 100;
        const y = ((e.clientY - rect.top) / this.offsetHeight) * 100;
        
        zoomOverlay.style.display = 'block';
        zoomOverlay.style.opacity = '1';
        
        zoomContainer.style.backgroundImage = `url('${this.src}')`;
        zoomContainer.style.backgroundPosition = `${x}% ${y}%`;
        zoomContainer.style.backgroundSize = '200%';
    });
    
    mainImage.addEventListener('mouseleave', function() {
        zoomOverlay.style.opacity = '0';
        setTimeout(() => {
            zoomOverlay.style.display = 'none';
        }, 300);
    });
}

// Quantity Selector
document.querySelectorAll('.quantity-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const isPlus = this.classList.contains('plus');
        const input = this.closest('.quantity-selector').querySelector('.quantity-input');
        
        if (isPlus) {
            if (parseInt(input.value) < parseInt(input.max)) {
                input.value = parseInt(input.value) + 1;
            }
        } else {
            if (parseInt(input.value) > parseInt(input.min)) {
                input.value = parseInt(input.value) - 1;
            }
        }
    });
});

// Add to Cart Functionality
document.querySelectorAll('.add-to-cart-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        // Check if user is logged in
        <?php if(!isset($_SESSION['user_id'])): ?>
        showToast('Please log in to add items to cart', 'info');
        return;
        <?php endif; ?>
        
        const variantId = this.dataset.variantId;
        const quantity = document.querySelector(`.quantity-input[data-variant-id="${variantId}"]`).value;
        
        // Show loading state
        const originalText = this.innerHTML;
        this.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Adding...';
        this.disabled = true;
        
        // Simulate API call (replace with actual fetch)
        setTimeout(() => {
            const formData = new FormData();
            formData.append('variant_id', variantId);
            formData.append('quantity', quantity);
            
            fetch('cart/add_to_cart.php', {
                method: 'POST',
                body: formData
            })
            .then(response => {
                // Success animation
                this.innerHTML = '<i class="fas fa-check me-2"></i> Added!';
                this.classList.remove('btn-danger');
                this.classList.add('btn-success');
                
                // Show success message
                showToast('Product added to cart successfully!', 'success');
                
                // Reset button after 2 seconds
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.classList.remove('btn-success');
                    this.classList.add('btn-danger');
                    this.disabled = false;
                }, 2000);
            })
            .catch(error => {
                this.innerHTML = '<i class="fas fa-times me-2"></i> Error';
                showToast('Failed to add product to cart', 'error');
                
                setTimeout(() => {
                    this.innerHTML = originalText;
                    this.disabled = false;
                }, 2000);
            });
        }, 500);
    });
});

// Variant Selection
document.querySelectorAll('.variant-card').forEach(card => {
    card.addEventListener('click', function(e) {
        if (!e.target.closest('.add-to-cart-btn') && !e.target.closest('.quantity-btn')) {
            this.classList.add('selected');
            
            // Remove selection from other variants
            document.querySelectorAll('.variant-card').forEach(otherCard => {
                if (otherCard !== this) {
                    otherCard.classList.remove('selected');
                }
            });
        }
    });
});

// Function to show login required message
function showLoginRequired() {
    showToast('Please log in to add items to cart', 'info');
}

// Toast Notification Function
function showToast(message, type = 'info') {
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'times-circle' : 'info-circle'} me-2"></i>
            <span>${message}</span>
        </div>
        <button class="btn-close btn-close-white ms-auto" onclick="this.parentElement.remove()"></button>
    `;
    
    document.body.appendChild(toast);
    
    setTimeout(() => {
        toast.classList.add('show');
    }, 10);
    
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 3000);
}

// Smooth scrolling for page sections
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Initialize tooltips
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
});
</script>

<!-- Custom CSS -->
<style>
.product-detail-page {
    padding-bottom: 3rem;
}

.breadcrumb {
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    padding: 1rem 1.5rem;
    border-radius: 10px;
    border: 1px solid #e9ecef;
}

.breadcrumb-item a {
    text-decoration: none;
    color: #6c757d;
    transition: color 0.3s ease;
}

.breadcrumb-item a:hover {
    color: #dc3545;
}

.breadcrumb-item.active {
    color: #495057;
    font-weight: 500;
}

.main-image-container {
    position: relative;
    overflow: hidden;
    border-radius: 15px;
    background: #f8f9fa;
    height: 400px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.main-product-image {
    max-height: 100%;
    width: auto;
    max-width: 100%;
    object-fit: contain;
    transition: transform 0.3s ease;
}

.product-badges {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.product-badges .badge {
    font-size: 0.85rem;
    padding: 0.5rem 1rem;
    border-radius: 25px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.image-zoom-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    display: none;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 1000;
    border-radius: 15px;
}

.zoom-container {
    width: 100%;
    height: 100%;
    background-repeat: no-repeat;
    background-position: center;
}

.product-details-section {
    padding: 1.5rem;
    background: #fff;
    border-radius: 15px;
    box-shadow: 0 5px 15px rgba(0,0,0,0.05);
}

.price-display {
    padding: 1rem;
    background: linear-gradient(135deg, #fff5f5 0%, #ffeaea 100%);
    border-radius: 10px;
    border: 2px solid #ffc9c9;
}

.description-content {
    line-height: 1.8;
}

.key-features ul {
    padding-left: 0;
}

.key-features li {
    padding: 0.5rem 0;
    border-bottom: 1px dashed #e9ecef;
}

.key-features li:last-child {
    border-bottom: none;
}

.variants-container {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
}

.variant-card {
    transition: all 0.3s ease;
}

.variant-card .card {
    border: 2px solid transparent;
    transition: all 0.3s ease;
}

.variant-card:hover .card {
    border-color: #dc3545;
    transform: translateY(-5px);
}

.variant-card.selected .card {
    border-color: #dc3545;
    background: linear-gradient(135deg, #fff5f5 0%, #ffeaea 100%);
}

.variant-card.unavailable .card {
    opacity: 0.7;
    filter: grayscale(0.3);
}

.variant-attributes .badge {
    font-size: 0.8rem;
    padding: 0.35rem 0.75rem;
}

.quantity-selector .input-group {
    border-radius: 10px;
    overflow: hidden;
    width: 150px;
}

.quantity-selector .input-group .btn {
    border: none;
    background: #f8f9fa;
    width: 50px;
}

.quantity-selector .input-group .btn:hover {
    background: #e9ecef;
}

.quantity-selector .form-control {
    border: none;
    background: #fff;
    font-weight: bold;
    font-size: 1.1rem;
}

.add-to-cart-btn {
    border-radius: 10px;
    padding: 0.75rem 1.5rem;
    transition: all 0.3s ease;
}

.add-to-cart-btn:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 53, 69, 0.3);
}

.add-to-cart-btn:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

.notify-me-btn {
    border-radius: 25px;
}

.specifications-section .table {
    background: #f8f9fa;
    border-radius: 10px;
    overflow: hidden;
}

.specifications-section th {
    font-weight: 600;
    color: #495057;
    padding: 1rem;
    background: #e9ecef;
}

.specifications-section td {
    padding: 1rem;
    background: #fff;
}

.login-required {
    border-left: 4px solid #0dcaf0;
    border-radius: 10px;
    padding: 1.5rem;
}

.related-products {
    padding: 2rem;
    background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
    border-radius: 15px;
    margin-top: 3rem;
}

.related-product-card {
    cursor: pointer;
    transition: all 0.3s ease;
    border: none;
    border-radius: 10px;
    overflow: hidden;
}

.related-product-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.rating .fa-star {
    font-size: 1.1rem;
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
    justify-content: space-between;
    z-index: 9999;
    min-width: 300px;
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

.toast-error {
    background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
}

.toast-info {
    background: linear-gradient(135deg, #0dcaf0 0%, #17a2b8 100%);
}

.toast-content {
    display: flex;
    align-items: center;
}

@media (max-width: 768px) {
    .main-image-container {
        height: 300px;
    }
    
    .variants-container {
        grid-template-columns: 1fr;
    }
    
    .quantity-selector .input-group {
        width: 120px;
    }
    
    .display-4 {
        font-size: 2.5rem;
    }
    
    .display-5 {
        font-size: 2rem;
    }
}

@media (max-width: 576px) {
    .main-image-container {
        height: 250px;
    }
    
    .add-to-cart-section .d-flex {
        flex-direction: column;
        gap: 1rem;
    }
    
    .quantity-selector {
        width: 100% !important;
    }
    
    .quantity-selector .input-group {
        width: 100% !important;
    }
}
</style>

<?php include "includes/footer.php"; ?>