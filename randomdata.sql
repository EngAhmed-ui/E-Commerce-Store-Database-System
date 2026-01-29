USE E_Commerce_Store;

-- ######################################################################
-- # 1. SETUP: Clear existing data                                      #
-- ######################################################################
SET FOREIGN_KEY_CHECKS = 0;

TRUNCATE TABLE Payments;
TRUNCATE TABLE Order_Items;
TRUNCATE TABLE Orders;
TRUNCATE TABLE Shopping_Cart;
TRUNCATE TABLE Variant_Attributes;
TRUNCATE TABLE Product_Variants;
TRUNCATE TABLE Products;
TRUNCATE TABLE Categories;
TRUNCATE TABLE Promotions_and_Coupons;
TRUNCATE TABLE User_Addresses;
TRUNCATE TABLE Users;

-- ######################################################################
-- # 2. CORE DATA: Categories (Shifted), Users, Addresses, Coupons      #
-- ######################################################################

-- Categories (Now 9 Records, ID 1 is Uncategorized)
INSERT INTO Categories (category_id, name) VALUES
(1, 'Uncategorized'),
(2, 'Electronics'),
(3, 'Apparel - Men'),
(4, 'Apparel - Women'),
(5, 'Home Decor'),
(6, 'Kitchenware'),
(7, 'Books & Media'),
(8, 'Outdoor Gear'),
(9, 'Health & Beauty');

-- Users (30 Records - No encryption as requested)
INSERT INTO Users (user_id, email, password, full_name, phone_number, created_at, role) VALUES
(1, 'admin@store.com', 'adminpass', 'System Administrator', '1110000000', NOW(), 'admin'),
(2, 'customer1@test.com', 'password123', 'Alan Customer', '2221111111', DATE_SUB(NOW(), INTERVAL 90 DAY), 'customer'),
(3, 'customer2@test.com', 'password123', 'Betty Buyer', '2222222222', DATE_SUB(NOW(), INTERVAL 75 DAY), 'customer'),
(4, 'customer3@test.com', 'password123', 'Charlie Client', '2223333333', DATE_SUB(NOW(), INTERVAL 60 DAY), 'customer'),
(5, 'moderator@store.com', 'modpass', 'Site Moderator', '1119999999', NOW(), 'moderator'),
(6, 'david@test.com', 'password123', 'David Developer', '3330000000', DATE_SUB(NOW(), INTERVAL 50 DAY), 'customer'),
(7, 'elena@test.com', 'password123', 'Elena Explorer', '3331111111', DATE_SUB(NOW(), INTERVAL 40 DAY), 'customer'),
(8, 'frank@test.com', 'password123', 'Frank Firsttime', '3332222222', DATE_SUB(NOW(), INTERVAL 30 DAY), 'customer'),
(9, 'grace@test.com', 'password123', 'Grace Goodbuy', '3333333333', DATE_SUB(NOW(), INTERVAL 20 DAY), 'customer'),
(10, 'henry@test.com', 'password123', 'Henry Happy', '3334444444', DATE_SUB(NOW(), INTERVAL 10 DAY), 'customer'),
(11, 'ida@test.com', 'password123', 'Ida Idea', '3335555555', DATE_SUB(NOW(), INTERVAL 5 DAY), 'customer'),
(12, 'jack@test.com', 'password123', 'Jack Jester', '3336666666', NOW(), 'customer'),
(13, 'karen@test.com', 'password123', 'Karen Kute', '4440000000', NOW(), 'customer'),
(14, 'liam@test.com', 'password123', 'Liam Logictest', '4441111111', NOW(), 'customer'),
(15, 'mia@test.com', 'password123', 'Mia Marvel', '4442222222', NOW(), 'customer'),
(16, 'nathan@test.com', 'password123', 'Nathan Nice', '4443333333', NOW(), 'customer'),
(17, 'olivia@test.com', 'password123', 'Olivia Order', '4444444444', NOW(), 'customer'),
(18, 'peter@test.com', 'password123', 'Peter Placeholder', '4445555555', NOW(), 'customer'),
(19, 'quinn@test.com', 'password123', 'Quinn Quick', '4446666666', NOW(), 'customer'),
(20, 'rachel@test.com', 'password123', 'Rachel Reliable', '4447777777', NOW(), 'customer'),
(21, 'sam@test.com', 'password123', 'Sam Seller', '5550001000', DATE_SUB(NOW(), INTERVAL 150 DAY), 'customer'),
(22, 'tina@test.com', 'password123', 'Tina Tester', '5550001001', DATE_SUB(NOW(), INTERVAL 120 DAY), 'customer'),
(23, 'uma@test.com', 'password123', 'Uma Unique', '5550001002', DATE_SUB(NOW(), INTERVAL 95 DAY), 'customer'),
(24, 'victor@test.com', 'password123', 'Victor Value', '5550001003', DATE_SUB(NOW(), INTERVAL 85 DAY), 'customer'),
(25, 'wendy@test.com', 'password123', 'Wendy Wait', '5550001004', DATE_SUB(NOW(), INTERVAL 70 DAY), 'customer'),
(26, 'xavier@test.com', 'password123', 'Xavier Xpress', '5550001005', DATE_SUB(NOW(), INTERVAL 65 DAY), 'customer'),
(27, 'yara@test.com', 'password123', 'Yara Yellow', '5550001006', DATE_SUB(NOW(), INTERVAL 55 DAY), 'customer'),
(28, 'zane@test.com', 'password123', 'Zane Zone', '5550001007', DATE_SUB(NOW(), INTERVAL 45 DAY), 'customer'),
(29, 'newuser1@test.com', 'password123', 'New User One', '5550001008', DATE_SUB(NOW(), INTERVAL 2 DAY), 'customer'),
(30, 'newuser2@test.com', 'password123', 'New User Two', '5550001009', NOW(), 'customer');

-- Addresses (13 Records)
INSERT INTO User_Addresses (address_id, user_id, label, street_address, city, zip_code) VALUES
(1, 2, 'Primary Home', '101 Main St', 'Metroville', 90210),
(2, 3, 'Office', '500 Tech Lane', 'Silicon Heights', 10001),
(3, 4, 'Apartment', '300 River View Apt 4B', 'Port City', 34567),
(4, 6, 'Warehouse', '999 Industrial Way', 'Gateway', 54321),
(5, 7, 'Parents House', '45 North Road', 'Suburban', 11223),
(6, 8, 'Cabin', '12 Pine Tree Rd', 'Mountain Top', 88888),
(7, 9, 'Shipping', '707 Express Blvd', 'Fast Town', 12345),
(8, 10, 'Billing', '200 Downtown Plz', 'Central City', 00001),
(9, 21, 'Storage Unit', '10 storage road', 'Storage City', 77777),
(10, 22, 'Summer Home', '444 Beach Front', 'Coastal Town', 66666),
(11, 23, 'University Dorm', '900 Campus Drive', 'College Town', 55555),
(12, 24, 'Secondary', '150 River Road', 'Riverside', 44444),
(13, 25, 'Main Home', '200 Forest Way', 'Greenwood', 33333);

-- Coupons (15 Records)
INSERT INTO Promotions_and_Coupons (coupon_id, code, discount_value, valid_from, expires_at, min_order_total) VALUES
(1, 'NEWUSER15', 15.00, '2025-01-01', '2025-12-31', 75.00),
(2, 'DEALOFDAY', 10.00, '2025-12-04', '2025-12-05', 50.00),
(3, 'SAVE25', 25.00, '2025-09-01', '2025-10-30', 150.00),
(4, 'FREESHIP', 0.00, '2025-01-01', '2026-01-01', 50.00),
(5, 'HOLIDAY30', 30.00, '2025-12-15', '2025-12-31', 200.00),
(6, 'BOGO', 5.00, '2025-10-01', '2025-12-31', 25.00),
(7, 'TEST1', 1.00, '2025-01-01', '2025-12-31', 1.00),
(8, 'FALL10', 10.00, '2025-09-01', '2025-11-30', 100.00),
(9, 'VIP75', 75.00, '2025-01-01', '2026-01-01', 500.00),
(10, 'CLEARANCE', 5.00, '2025-11-01', '2025-12-31', 10.00),
(11, 'WINTER20', 20.00, '2025-11-01', '2025-12-31', 120.00),
(12, 'FLASH50', 50.00, '2025-12-04', '2025-12-05', 300.00),
(13, 'GET5OFF', 5.00, '2025-01-01', '2025-12-31', 30.00),
(14, 'SCHOOL10', 10.00, '2025-08-01', '2025-09-30', 50.00),
(15, 'VIPFREE', 0.00, '2025-01-01', '2026-01-01', 0.00);

-- ######################################################################
-- # 3. PRODUCTS: category_id updated to reflect Uncategorized shift    #
-- ######################################################################

INSERT INTO Products (product_id, name, description, price, category_id, status, image_url) VALUES
-- Category 2 (Electronics)
(1, 'Laptop Pro X', 'High-performance laptop, 16GB RAM, 1TB SSD.', 1200.00, 2, 'active', 'url/products/laptop_pro.jpg'),
(2, 'E-Reader Tablet', 'Lightweight tablet for reading.', 89.99, 2, 'active', 'url/products/ereader.jpg'),
(3, '4K Monitor 32"', 'Ultra HD monitor for professional use.', 450.00, 2, 'active', 'url/products/4k_monitor.jpg'),
(4, 'Noise Cancelling Headphones', 'Over-ear wireless headphones.', 199.50, 2, 'active', 'url/products/headphones.jpg'),
(5, 'Portable Power Bank 20K', 'High-capacity mobile battery.', 35.00, 2, 'active', 'url/products/powerbank.jpg'),
(6, 'Smart Home Hub', 'Central control for smart home devices.', 99.00, 2, 'active', 'url/products/smarthub.jpg'),
(7, 'Wireless Charging Pad', 'Fast Qi wireless charger.', 18.00, 2, 'active', 'url/products/charger.jpg'),
-- Category 3 (Apparel - Men)
(8, 'Slim Fit Denim Jeans', 'Classic blue denim, stretch material.', 79.99, 3, 'active', 'url/products/mens_jeans.jpg'),
(9, 'Casual Button-Down Shirt', '100% Cotton, various patterns.', 45.00, 3, 'active', 'url/products/mens_shirt.jpg'),
(10, 'Running Shorts', 'Lightweight, quick-dry material.', 30.00, 3, 'active', 'url/products/run_shorts.jpg'),
(11, 'Wool Blazer', 'High-end wool blazer, only L and XL left.', 150.00, 3, 'out_of_stock', 'url/products/blazer.jpg'),
(12, 'Men\'s Graphic Tee', 'Unique graphic print t-shirt.', 25.00, 3, 'active', 'url/products/graphic_tee.jpg'),
(13, 'Technical Running Jacket', 'Lightweight, water-resistant, reflective.', 55.00, 3, 'active', 'url/products/running_jacket.jpg'),
-- Category 4 (Apparel - Women)
(14, 'Summer Dress', 'Light floral summer dress.', 68.00, 4, 'active', 'url/products/summer_dress.jpg'),
(15, 'Cashmere Scarf', 'Soft, luxury cashmere scarf.', 110.00, 4, 'active', 'url/products/scarf.jpg'),
(16, 'Leather Tote Bag', 'Large capacity leather tote.', 120.00, 4, 'inactive', 'url/products/tote.jpg'),
(17, 'Women\'s Rain Jacket', 'Waterproof and windproof jacket.', 85.00, 4, 'active', 'url/products/rain_jacket.jpg'),
-- Category 5 (Home Decor)
(18, 'Scented Candle Set', 'Set of 3 natural soy wax candles.', 28.00, 5, 'active', 'url/products/candles.jpg'),
(19, 'Abstract Wall Art', 'Large canvas, modern abstract design.', 95.00, 5, 'active', 'url/products/wall_art.jpg'),
(20, 'Decorative Throw Blanket', 'Knit throw blanket, various colors.', 40.00, 5, 'active', 'url/products/throw_blanket.jpg'),
(21, 'Ceramic Vase', 'Hand-crafted large ceramic vase.', 60.00, 5, 'active', 'url/products/ceramic_vase.jpg'),
-- Category 6 (Kitchenware)
(22, 'Espresso Machine', 'Semi-automatic espresso maker.', 299.99, 6, 'active', 'url/products/espresso.jpg'),
(23, 'Non-Stick Pan Set', 'Set of 3 frying pans.', 65.00, 6, 'active', 'url/products/pan_set.jpg'),
(24, 'Electric Kettle', 'Fast-boil electric kettle.', 39.99, 6, 'active', 'url/products/kettle.jpg'),
(25, 'Bamboo Cutting Board', 'Large, sustainable bamboo board.', 22.50, 6, 'active', 'url/products/cutting_board.jpg'),
(26, 'Stainless Steel Utensil Set', '24-piece utensil set.', 49.00, 6, 'active', 'url/products/utensils.jpg'),
-- Category 7 (Books & Media)
(27, 'Bestseller Fiction Novel', 'Critically acclaimed modern novel.', 18.00, 7, 'active', 'url/products/novel.jpg'),
(28, 'The Beginner\'s Coding Guide', 'Introduction to Python and SQL.', 45.00, 7, 'active', 'url/products/coding_book.jpg'),
(29, 'History of Rome', 'Illustrated history textbook.', 35.00, 7, 'active', 'url/products/history_book.jpg'),
(30, 'Documentary Film DVD', 'Award-winning documentary.', 15.00, 7, 'active', 'url/products/dvd.jpg'),
-- Category 8 (Outdoor Gear)
(31, '2-Person Camping Tent', 'Lightweight and durable tent.', 115.00, 8, 'active', 'url/products/tent.jpg'),
(32, 'Hiking Backpack 50L', 'Adjustable, large capacity backpack.', 70.00, 8, 'active', 'url/products/backpack.jpg'),
(33, 'Rechargeable Headlamp', 'Bright LED headlamp.', 20.00, 8, 'active', 'url/products/headlamp.jpg'),
(34, 'Insulated Water Bottle', 'Stainless steel, keeps drinks cold.', 25.00, 8, 'active', 'url/products/water_bottle.jpg'),
(35, 'Folding Camping Chair', 'Compact and comfortable chair.', 35.00, 8, 'active', 'url/products/camping_chair.jpg'),
-- Category 9 (Health & Beauty)
(36, 'Organic Shampoo Set', 'Shampoo and conditioner duo.', 32.00, 9, 'active', 'url/products/shampoo.jpg'),
(37, 'Natural Soap Bar Pack', 'Pack of 5 artisan soap bars.', 19.99, 9, 'active', 'url/products/soap_pack.jpg'),
(38, 'Electric Toothbrush', 'Rechargeable sonic toothbrush.', 60.00, 9, 'active', 'url/products/toothbrush.jpg'),
(39, 'Facial Serum Vitamin C', 'Anti-aging vitamin C serum.', 45.00, 9, 'active', 'url/products/serum.jpg'),
(40, 'Sunscreen SPF 50', 'Broad spectrum protection.', 15.00, 9, 'active', 'url/products/sunscreen.jpg');

-- ######################################################################
-- # 4. VARIANTS & ATTRIBUTES (100+ Records aligned to shifted Products)#
-- ######################################################################

-- Product_Variants (100 Records)
INSERT INTO Product_Variants (variant_id, product_id, stock_quantity, is_available) VALUES
(1, 1, 10, 1), (2, 1, 5, 1), (3, 2, 50, 1), (4, 2, 40, 1), (5, 3, 20, 1),
(6, 4, 30, 1), (7, 4, 0, 0), (8, 5, 80, 1), (9, 5, 60, 1), (29, 6, 40, 1), 
(30, 6, 35, 1), (31, 7, 50, 1), (32, 7, 60, 1), (10, 8, 30, 1), (11, 8, 25, 1), 
(12, 8, 20, 1), (13, 8, 15, 1), (33, 9, 25, 1), (34, 9, 20, 1), (35, 10, 30, 1), 
(36, 10, 25, 1), (37, 11, 5, 0), (38, 12, 50, 1), (39, 12, 45, 1), (14, 13, 40, 1), 
(15, 13, 50, 1), (16, 13, 60, 1), (17, 14, 20, 1), (18, 14, 15, 1), (19, 14, 10, 1), 
(40, 15, 15, 1), (41, 15, 10, 1), (42, 16, 10, 0), (43, 17, 20, 1), (44, 17, 15, 1), 
(20, 18, 100, 1), (21, 18, 80, 1), (45, 19, 30, 1), (46, 19, 25, 1), (47, 20, 50, 1), 
(48, 20, 45, 1), (49, 20, 40, 1), (50, 21, 20, 1), (51, 21, 15, 1), (22, 22, 15, 1), 
(23, 22, 10, 1), (52, 23, 70, 1), (53, 23, 60, 1), (54, 24, 80, 1), (55, 24, 75, 1), 
(56, 25, 90, 1), (57, 25, 85, 1), (58, 26, 60, 1), (59, 26, 55, 1), (24, 27, 70, 1), 
(25, 27, 150, 1), (60, 28, 110, 1), (61, 28, 100, 1), (62, 29, 120, 1), (63, 29, 100, 1), 
(64, 30, 80, 1), (65, 30, 70, 1), (26, 31, 25, 1), (27, 31, 20, 1), (28, 31, 15, 1), 
(66, 32, 40, 1), (67, 32, 35, 1), (68, 33, 60, 1), (69, 33, 55, 1), (70, 34, 70, 1), 
(71, 34, 65, 1), (72, 35, 45, 1), (73, 35, 40, 1), (74, 36, 75, 1), (75, 36, 70, 1), 
(76, 37, 85, 1), (77, 37, 80, 1), (78, 38, 55, 1), (79, 38, 50, 1), (80, 39, 65, 1), 
(81, 39, 60, 1), (82, 40, 90, 1), (83, 40, 85, 1), (84, 1, 8, 1), (85, 2, 40, 1), 
(86, 3, 15, 1), (87, 4, 20, 1), (88, 5, 50, 1), (89, 8, 10, 1), (90, 13, 30, 1), 
(91, 14, 5, 1), (92, 17, 10, 1), (93, 20, 30, 1), (94, 22, 5, 1), (95, 27, 50, 1), 
(96, 31, 10, 1), (97, 34, 50, 1), (98, 37, 70, 1), (99, 38, 40, 1), (100, 39, 50, 1);

-- Variant Attributes (Aligned to variants)
INSERT INTO Variant_Attributes (variant_id, attribute_name, attribute_value) VALUES
(1, 'RAM', '16GB'), (1, 'Storage', '1TB'), (2, 'RAM', '8GB'), (2, 'Storage', '512GB'),
(3, 'Color', 'Black'), (4, 'Color', 'Blue'), (5, 'Size', '32"'), (6, 'Color', 'Black'),
(10, 'Waist', '30'), (11, 'Waist', '32'), (12, 'Waist', '34'), (13, 'Waist', '36'),
(14, 'Size', 'S'), (15, 'Size', 'M'), (16, 'Size', 'L'), (17, 'Pattern', 'Floral'),
(20, 'Scent', 'Lavender'), (22, 'Finish', 'Silver'), (24, 'Format', 'Hardcover'),
(26, 'Color', 'Green'), (70, 'Size', '20oz'), (80, 'Volume', '30ml');

-- ######################################################################
-- # 5. TRANSACTIONS: Orders, Order Items, Carts, Payments              #
-- ######################################################################

-- Orders (25 Records)
INSERT INTO Orders (order_id, user_id, order_date, status, coupon_id, discount_applied, address_id, payment_method, final_total, shipping_cost) VALUES
(1, 2, DATE_SUB(NOW(), INTERVAL 80 DAY), 'delivered', 1, 15.00, 1, 'Credit Card', 1933.50, 5.00),
(2, 3, DATE_SUB(NOW(), INTERVAL 60 DAY), 'shipped', 3, 25.00, 2, 'PayPal', 422.50, 5.00),
(3, 4, DATE_SUB(NOW(), INTERVAL 40 DAY), 'processing', NULL, 0.00, 3, 'Credit Card', 252.99, 5.00),
(4, 6, DATE_SUB(NOW(), INTERVAL 20 DAY), 'shipped', 4, 0.00, 4, 'Debit Card', 230.00, 5.00),
(5, 7, DATE_SUB(NOW(), INTERVAL 10 DAY), 'delivered', 6, 5.00, 5, 'Credit Card', 192.99, 5.00),
(6, 8, DATE_SUB(NOW(), INTERVAL 5 DAY), 'pending', NULL, 0.00, 6, 'PayPal', 147.99, 5.00),
(7, 9, DATE_SUB(NOW(), INTERVAL 3 DAY), 'cancelled', 7, 1.00, 7, 'N/A', 19.00, 5.00),
(8, 10, DATE_SUB(NOW(), INTERVAL 2 DAY), 'processing', NULL, 0.00, 8, 'Credit Card', 461.98, 5.00),
(9, 2, DATE_SUB(NOW(), INTERVAL 1 DAY), 'pending', 2, 10.00, 1, 'Debit Card', 80.00, 5.00),
(10, 3, DATE_SUB(NOW(), INTERVAL 1 HOUR), 'pending', NULL, 0.00, 2, 'Credit Card', 105.00, 5.00),
(11, 11, DATE_SUB(NOW(), INTERVAL 4 DAY), 'delivered', 7, 1.00, 1, 'PayPal', 88.99, 5.00),
(12, 12, DATE_SUB(NOW(), INTERVAL 1 DAY), 'shipped', 6, 5.00, 1, 'Credit Card', 45.00, 5.00),
(13, 13, DATE_SUB(NOW(), INTERVAL 2 HOUR), 'processing', NULL, 0.00, 2, 'Credit Card', 25.00, 5.00),
(14, 14, DATE_SUB(NOW(), INTERVAL 3 HOUR), 'pending', 10, 5.00, 3, 'Credit Card', 40.00, 5.00),
(15, 15, DATE_SUB(NOW(), INTERVAL 5 HOUR), 'cancelled', NULL, 0.00, 4, 'N/A', 304.99, 5.00),
(16, 21, DATE_SUB(NOW(), INTERVAL 140 DAY), 'delivered', 1, 15.00, 9, 'Credit Card', 357.49, 5.00),
(17, 22, DATE_SUB(NOW(), INTERVAL 110 DAY), 'delivered', 8, 10.00, 10, 'PayPal', 243.00, 5.00),
(18, 23, DATE_SUB(NOW(), INTERVAL 90 DAY), 'shipped', NULL, 0.00, 11, 'Credit Card', 345.00, 5.00),
(19, 24, DATE_SUB(NOW(), INTERVAL 80 DAY), 'processing', 11, 20.00, 12, 'Debit Card', 690.00, 5.00),
(20, 25, DATE_SUB(NOW(), INTERVAL 60 DAY), 'cancelled', 13, 5.00, 13, 'N/A', 294.99, 5.00),
(21, 26, DATE_SUB(NOW(), INTERVAL 50 DAY), 'delivered', 14, 10.00, 1, 'Credit Card', 154.00, 5.00),
(22, 27, DATE_SUB(NOW(), INTERVAL 40 DAY), 'shipped', 1, 15.00, 2, 'PayPal', 224.99, 5.00),
(23, 28, DATE_SUB(NOW(), INTERVAL 30 DAY), 'delivered', 13, 5.00, 3, 'Credit Card', 132.00, 5.00),
(24, 29, DATE_SUB(NOW(), INTERVAL 1 DAY), 'pending', 12, 50.00, 4, 'Debit Card', 1229.99, 5.00),
(25, 30, NOW(), 'pending', 15, 0.00, 5, 'Credit Card', 30.00, 5.00);

-- Order Items (Linked to variants)
INSERT INTO Order_Items (order_item_id, order_id, quantity, unit_price, variant_id) VALUES
(1, 1, 1, 1200.00, 1), (2, 1, 1, 199.50, 6), (3, 1, 1, 450.00, 5), (4, 1, 1, 99.00, 29),
(5, 2, 2, 55.00, 14), (6, 2, 1, 199.50, 7), (25, 8, 1, 299.99, 22), (68, 24, 1, 1200.00, 1);

-- Shopping Cart (25 Records)
INSERT INTO Shopping_Cart (cart_item_id, quantity, user_id, variant_id) VALUES
(1, 1, 2, 1), (2, 2, 2, 6), (3, 1, 3, 14), (4, 3, 4, 17), (5, 1, 7, 24),
(11, 1, 10, 22), (12, 1, 12, 31), (25, 1, 30, 28);

-- Payments (25 Records)
INSERT INTO Payments (payment_id, order_id, payment_date, amount) VALUES
(1, 1, DATE_SUB(NOW(), INTERVAL 80 DAY), 1933.50),
(2, 2, DATE_SUB(NOW(), INTERVAL 60 DAY), 422.50),
(3, 3, DATE_SUB(NOW(), INTERVAL 40 DAY), 252.99),
(4, 4, DATE_SUB(NOW(), INTERVAL 20 DAY), 230.00),
(5, 5, DATE_SUB(NOW(), INTERVAL 10 DAY), 192.99),
(6, 6, DATE_SUB(NOW(), INTERVAL 5 DAY), 147.99),
(7, 7, DATE_SUB(NOW(), INTERVAL 3 DAY), 19.00),
(8, 8, DATE_SUB(NOW(), INTERVAL 2 DAY), 461.98),
(9, 9, DATE_SUB(NOW(), INTERVAL 1 DAY), 80.00),
(10, 10, DATE_SUB(NOW(), INTERVAL 1 HOUR), 105.00),
(11, 11, DATE_SUB(NOW(), INTERVAL 4 DAY), 88.99),
(12, 12, DATE_SUB(NOW(), INTERVAL 1 DAY), 45.00),
(13, 13, DATE_SUB(NOW(), INTERVAL 2 HOUR), 25.00),
(14, 14, DATE_SUB(NOW(), INTERVAL 3 HOUR), 40.00),
(15, 15, DATE_SUB(NOW(), INTERVAL 5 HOUR), 304.99),
(16, 16, DATE_SUB(NOW(), INTERVAL 140 DAY), 357.49),
(17, 17, DATE_SUB(NOW(), INTERVAL 110 DAY), 243.00),
(18, 18, DATE_SUB(NOW(), INTERVAL 90 DAY), 345.00),
(19, 19, DATE_SUB(NOW(), INTERVAL 80 DAY), 690.00),
(20, 20, DATE_SUB(NOW(), INTERVAL 60 DAY), 294.99),
(21, 21, DATE_SUB(NOW(), INTERVAL 50 DAY), 154.00),
(22, 22, DATE_SUB(NOW(), INTERVAL 40 DAY), 224.99),
(23, 23, DATE_SUB(NOW(), INTERVAL 30 DAY), 132.00),
(24, 24, DATE_SUB(NOW(), INTERVAL 1 DAY), 1229.99),
(25, 25, NOW(), 30.00);

SET FOREIGN_KEY_CHECKS = 1;