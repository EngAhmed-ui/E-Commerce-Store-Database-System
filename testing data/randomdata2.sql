USE E_Commerce_Store;

-- ######################################################################
-- # 1. TEMPORARILY DISABLE FOREIGN KEYS                                #
-- ######################################################################
SET FOREIGN_KEY_CHECKS = 0;

-- ######################################################################
-- # 2. ADD MORE CATEGORIES (Extending from existing 9 categories)      #
-- ######################################################################
-- Note: Main data has categories 1-9, so continue from 10
INSERT INTO Categories (category_id, name) VALUES
(10, 'Sports Equipment'),
(11, 'Toys & Games'),
(12, 'Automotive'),
(13, 'Pet Supplies'),
(14, 'Office Supplies'),
(15, 'Jewelry & Watches'),
(16, 'Baby & Kids'),
(17, 'Groceries'),
(18, 'Fitness & Gym'),
(19, 'Musical Instruments'),
(20, 'Travel & Luggage'),
(21, 'Art & Crafts');

-- ######################################################################
-- # 3. ADD MORE USERS (Extending from existing 30 users)               #
-- ######################################################################
-- Main data has users 1-30, continue from 31
INSERT INTO Users (user_id, email, password, full_name, phone_number, created_at, role) VALUES
(31, 'premium@customer.com', 'premium123', 'Premium Buyer', '5551112222', DATE_SUB(NOW(), INTERVAL 180 DAY), 'customer'),
(32, 'business@client.com', 'business456', 'Business Solutions LLC', '5552223333', DATE_SUB(NOW(), INTERVAL 160 DAY), 'customer'),
(33, 'frequent@shopper.com', 'shopper789', 'Frequent Shopper', '5553334444', DATE_SUB(NOW(), INTERVAL 140 DAY), 'customer'),
(34, 'wholesale@buyer.com', 'wholesale123', 'Wholesale Distributor', '5554445555', DATE_SUB(NOW(), INTERVAL 120 DAY), 'customer'),
(35, 'gift@buyer.com', 'giftpass123', 'Gift Shopper', '5555556666', DATE_SUB(NOW(), INTERVAL 100 DAY), 'customer'),
(36, 'seasonal@customer.com', 'seasonal123', 'Seasonal Buyer', '5556667777', DATE_SUB(NOW(), INTERVAL 80 DAY), 'customer'),
(37, 'tech@enthusiast.com', 'techpass456', 'Tech Enthusiast', '5557778888', DATE_SUB(NOW(), INTERVAL 60 DAY), 'customer'),
(38, 'fashion@lover.com', 'fashionpass', 'Fashion Lover', '5558889999', DATE_SUB(NOW(), INTERVAL 40 DAY), 'customer'),
(39, 'home@decorator.com', 'homepass123', 'Home Decorator', '5559990000', DATE_SUB(NOW(), INTERVAL 30 DAY), 'customer'),
(40, 'book@worm.com', 'bookpass456', 'Book Worm', '5550001111', DATE_SUB(NOW(), INTERVAL 15 DAY), 'customer'),
(41, 'outdoor@adventurer.com', 'outdoorpass', 'Outdoor Adventurer', '5551110000', DATE_SUB(NOW(), INTERVAL 10 DAY), 'customer'),
(42, 'beauty@expert.com', 'beautypass', 'Beauty Expert', '5552220000', DATE_SUB(NOW(), INTERVAL 7 DAY), 'customer'),
(43, 'student@discount.com', 'student123', 'College Student', '5553330000', DATE_SUB(NOW(), INTERVAL 5 DAY), 'customer'),
(44, 'senior@shopper.com', 'seniorpass', 'Senior Shopper', '5554440000', DATE_SUB(NOW(), INTERVAL 3 DAY), 'customer'),
(45, 'international@buyer.com', 'international', 'International Buyer', '5555550000', NOW(), 'customer'),
(46, 'reviewer@influencer.com', 'reviewpass', 'Product Reviewer', '5556660000', NOW(), 'customer'),
(47, 'returning@customer.com', 'returnpass', 'Returning Customer', '5557770000', NOW(), 'customer'),
(48, 'firsttime@buyer.com', 'firstpass', 'First Time Buyer', '5558880000', NOW(), 'customer'),
(49, 'holiday@shopper.com', 'holidaypass', 'Holiday Shopper', '5559990001', NOW(), 'customer'),
(50, 'corporate@gift.com', 'corppass123', 'Corporate Gifting', '5550002222', NOW(), 'customer');

-- ######################################################################
-- # 4. ADD MORE USER ADDRESSES                                         #
-- ######################################################################
-- Main data has addresses 1-13, continue from 14
INSERT INTO User_Addresses (address_id, user_id, label, street_address, city, zip_code) VALUES
(14, 31, 'Primary Residence', '888 Luxury Lane', 'Beverly Hills', 90212),
(15, 32, 'Corporate Office', '1000 Business Blvd', 'Financial District', 10005),
(16, 33, 'Main Home', '333 Shopper Street', 'Retail City', 11225),
(17, 34, 'Warehouse', '500 Industrial Ave', 'Distribution Center', 54322),
(18, 35, 'Gift Shipping', '777 Gift Road', 'Gifting Town', 12346),
(19, 36, 'Seasonal Home', '222 Seasonal Way', 'Ski Resort', 88889),
(20, 37, 'Tech Hub', '444 Tech Street', 'Silicon Valley', 94043),
(21, 38, 'Fashion District', '555 Style Avenue', 'Fashion City', 10018),
(22, 39, 'Home Decor Studio', '666 Design Road', 'Creative Town', 11226),
(23, 40, 'Library Address', '777 Book Lane', 'Academic City', 11227),
(24, 41, 'Camping Address', '888 Adventure Trail', 'Wilderness', 99999),
(25, 42, 'Beauty Studio', '999 Glamour Blvd', 'Beauty City', 10019),
(26, 43, 'University Housing', '111 Campus Drive', 'College Town', 55556),
(27, 44, 'Retirement Community', '222 Senior Living', 'Retirement City', 11228),
(28, 45, 'International Office', '333 Global Plaza', 'International City', 10020),
(29, 46, 'Influencer Studio', '444 Review Street', 'Media City', 10021),
(30, 47, 'Return Center', '555 Return Lane', 'Returns City', 11229),
(31, 48, 'New Customer', '666 First Time Rd', 'Beginner Town', 11230),
(32, 49, 'Holiday Home', '777 Christmas Way', 'Holiday City', 11231),
(33, 50, 'Corporate Headquarters', '888 Corporate Circle', 'Business Park', 10022);

-- ######################################################################
-- # 5. ADD MORE PROMOTIONS & COUPONS                                   #
-- ######################################################################
-- Main data has coupons 1-15, continue from 16
INSERT INTO Promotions_and_Coupons (coupon_id, code, discount_value, valid_from, expires_at, min_order_total) VALUES
(16, 'SPRING25', 25.00, '2025-03-01', '2025-05-31', 125.00),
(17, 'SUMMER30', 30.00, '2025-06-01', '2025-08-31', 150.00),
(18, 'BLACKFRIDAY', 100.00, '2025-11-25', '2025-11-27', 400.00),
(19, 'CYBERMONDAY', 75.00, '2025-11-28', '2025-12-01', 300.00),
(20, 'MEMORIALDAY', 20.00, '2025-05-25', '2025-05-31', 100.00),
(21, 'LABORDAY', 15.00, '2025-09-01', '2025-09-07', 75.00),
(22, 'CHRISTMAS50', 50.00, '2025-12-20', '2025-12-26', 200.00),
(23, 'NEWYEAR20', 20.00, '2025-12-30', '2026-01-05', 80.00),
(24, 'VALENTINE15', 15.00, '2025-02-10', '2025-02-15', 60.00),
(25, 'EASTER10', 10.00, '2025-04-01', '2025-04-10', 40.00),
(26, 'STUDENT20', 20.00, '2025-01-01', '2026-01-01', 50.00),
(27, 'SENIOR15', 15.00, '2025-01-01', '2026-01-01', 40.00),
(28, 'MILITARY25', 25.00, '2025-01-01', '2026-01-01', 100.00),
(29, 'FIRSTORDER10', 10.00, '2025-01-01', '2026-01-01', 25.00),
(30, 'BIRTHDAY5', 5.00, '2025-01-01', '2026-01-01', 20.00),
(31, 'REFERRAL15', 15.00, '2025-01-01', '2026-01-01', 50.00),
(32, 'LOYALTY10', 10.00, '2025-01-01', '2026-01-01', 40.00),
(33, 'FLASHSALE30', 30.00, '2025-12-05', '2025-12-06', 120.00),
(34, 'CLEARANCE20', 20.00, '2025-12-01', '2025-12-31', 50.00),
(35, 'VIP100', 100.00, '2025-01-01', '2026-01-01', 1000.00);

-- ######################################################################
-- # 6. ADD MORE PRODUCTS (Extending from existing 40 products)         #
-- ######################################################################
-- Main data has products 1-40, continue from 41
INSERT INTO Products (product_id, name, description, price, category_id, status, image_url) VALUES
-- Sports Equipment (Category 10)
(41, 'Basketball Premium', 'Official size and weight, indoor/outdoor use', 29.99, 10, 'active', 'url/products/basketball.jpg'),
(42, 'Yoga Mat Premium', 'Non-slip, eco-friendly material, 6mm thickness', 39.99, 10, 'active', 'url/products/yogamat.jpg'),
(43, 'Dumbbell Set 40lb', 'Adjustable dumbbell set with case', 89.99, 10, 'active', 'url/products/dumbbells.jpg'),
(44, 'Tennis Racket Pro', 'Graphite tennis racket with premium strings', 129.99, 10, 'active', 'url/products/tennisracket.jpg'),
(45, 'Football Official', 'NFL official size and weight', 34.99, 10, 'active', 'url/products/football.jpg'),

-- Toys & Games (Category 11)
(46, 'LEGO City Set', '500-piece city building set', 59.99, 11, 'active', 'url/products/lego.jpg'),
(47, 'Board Game Collection', 'Family board game collection (5 games)', 49.99, 11, 'active', 'url/products/boardgames.jpg'),
(48, 'Remote Control Car', '2.4GHz remote control, 30mph speed', 79.99, 11, 'active', 'url/products/rccar.jpg'),
(49, 'Educational STEM Kit', 'Science experiment kit for kids 8+', 34.99, 11, 'active', 'url/products/stemkit.jpg'),
(50, 'Dollhouse with Furniture', '3-story dollhouse with 20+ furniture pieces', 129.99, 11, 'active', 'url/products/dollhouse.jpg'),

-- Automotive (Category 12)
(51, 'Car Phone Mount', 'Magnetic car phone mount with wireless charging', 24.99, 12, 'active', 'url/products/phone_mount.jpg'),
(52, 'Jump Starter Power Bank', 'Portable car jump starter with USB ports', 99.99, 12, 'active', 'url/products/jumpstarter.jpg'),
(53, 'Car Vacuum Cleaner', 'Cordless car vacuum with attachments', 49.99, 12, 'active', 'url/products/carvacuum.jpg'),
(54, 'Car Seat Covers', 'Universal fit neoprene seat covers (2 pieces)', 69.99, 12, 'active', 'url/products/seatcovers.jpg'),
(55, 'Dash Cam HD', '1080p dash cam with night vision', 89.99, 12, 'active', 'url/products/dashcam.jpg'),

-- Pet Supplies (Category 13)
(56, 'Dog Bed Orthopedic', 'Memory foam dog bed for large breeds', 79.99, 13, 'active', 'url/products/dogbed.jpg'),
(57, 'Automatic Cat Feeder', 'Programmable automatic cat feeder', 64.99, 13, 'active', 'url/products/catfeeder.jpg'),
(58, 'Pet Carrier Airline Approved', 'Soft-sided pet carrier for air travel', 54.99, 13, 'active', 'url/products/petcarrier.jpg'),
(59, 'Dog Toy Bundle', 'Assorted durable dog toys (10 pieces)', 29.99, 13, 'active', 'url/products/dogtoys.jpg'),
(60, 'Cat Tree with Scratching Posts', 'Multi-level cat tree with condo', 119.99, 13, 'active', 'url/products/cattree.jpg'),

-- Office Supplies (Category 14)
(61, 'Wireless Mouse & Keyboard', 'Ergonomic wireless mouse and keyboard combo', 49.99, 14, 'active', 'url/products/mousekeyboard.jpg'),
(62, 'Desk Organizer Set', 'Premium desk organizer with pen holders', 34.99, 14, 'active', 'url/products/deskorganizer.jpg'),
(63, 'Executive Desk Chair', 'Ergonomic office chair with lumbar support', 199.99, 14, 'active', 'url/products/deskchair.jpg'),
(64, 'Label Maker Pro', 'Professional label maker with tape', 44.99, 14, 'active', 'url/products/labelmaker.jpg'),
(65, 'Paper Shredder Cross-Cut', '12-sheet cross-cut paper shredder', 89.99, 14, 'active', 'url/products/shredder.jpg'),

-- Jewelry & Watches (Category 15)
(66, 'Gold Plated Necklace', '18k gold plated chain with pendant', 89.99, 15, 'active', 'url/products/necklace.jpg'),
(67, 'Silver Bracelet Set', 'Sterling silver bracelet set (3 pieces)', 129.99, 15, 'active', 'url/products/braceletset.jpg'),
(68, 'Smart Watch Fitness', 'Fitness smartwatch with heart rate monitor', 199.99, 15, 'active', 'url/products/smartwatch.jpg'),
(69, 'Diamond Stud Earrings', 'Sterling silver with genuine diamonds', 299.99, 15, 'active', 'url/products/earrings.jpg'),
(70, 'Men\'s Leather Watch', 'Genuine leather strap with mineral glass', 149.99, 15, 'active', 'url/products/leatherwatch.jpg'),

-- Baby & Kids (Category 16)
(71, 'Baby Stroller Travel System', 'Convertible stroller with car seat', 299.99, 16, 'active', 'url/products/stroller.jpg'),
(72, 'Baby Monitor Camera', 'HD video baby monitor with night vision', 89.99, 16, 'active', 'url/products/babymonitor.jpg'),
(73, 'Kids Tablet Educational', 'Kid-safe tablet with educational apps', 129.99, 16, 'active', 'url/products/kidstablet.jpg'),
(74, 'Nursing Pillow with Cover', 'Ergonomic nursing pillow', 39.99, 16, 'active', 'url/products/nursingpillow.jpg'),
(75, 'Baby Clothes Bundle', 'Newborn clothes bundle (10 pieces)', 49.99, 16, 'active', 'url/products/babyclothes.jpg'),

-- Groceries (Category 17)
(76, 'Organic Coffee Beans', 'Premium organic arabica coffee beans, 1lb', 16.99, 17, 'active', 'url/products/coffeebeans.jpg'),
(77, 'Artisan Chocolate Box', 'Assorted artisan chocolates, 24 pieces', 34.99, 17, 'active', 'url/products/chocolatebox.jpg'),
(78, 'Extra Virgin Olive Oil', 'Cold pressed extra virgin olive oil, 1L', 24.99, 17, 'active', 'url/products/oliveoil.jpg'),
(79, 'Gourmet Tea Collection', 'Assorted gourmet teas, 20 bags each', 29.99, 17, 'active', 'url/products/teacollection.jpg'),
(80, 'Organic Snack Box', 'Assorted organic snacks, monthly subscription', 39.99, 17, 'active', 'url/products/snackbox.jpg'),

-- Fitness & Gym (Category 18)
(81, 'Resistance Band Set', '5-piece resistance band set with handles', 29.99, 18, 'active', 'url/products/resistancebands.jpg'),
(82, 'Foam Roller Set', '3-piece foam roller set for muscle recovery', 44.99, 18, 'active', 'url/products/foamroller.jpg'),
(83, 'Adjustable Kettlebell', 'Adjustable kettlebell 5-25lbs', 79.99, 18, 'active', 'url/products/kettlebell.jpg'),
(84, 'Fitness Tracker Band', 'Waterproof fitness tracker with app', 59.99, 18, 'active', 'url/products/fitnesstracker.jpg'),
(85, 'Yoga Block Set', '2 premium yoga blocks with strap', 24.99, 18, 'active', 'url/products/yogablocks.jpg'),

-- Musical Instruments (Category 19)
(86, 'Acoustic Guitar Beginner', 'Full-size acoustic guitar with case', 199.99, 19, 'active', 'url/products/guitar.jpg'),
(87, 'Digital Piano 88 Keys', 'Weighted key digital piano with stand', 499.99, 19, 'active', 'url/products/digitalpiano.jpg'),
(88, 'Violin Student Set', 'Complete violin set for students', 149.99, 19, 'active', 'url/products/violin.jpg'),
(89, 'Drum Practice Pad', 'Quiet drum practice pad with stand', 39.99, 19, 'active', 'url/products/drumpad.jpg'),
(90, 'Ukulele Soprano', 'Soprano ukulele with gig bag', 59.99, 19, 'active', 'url/products/ukulele.jpg'),

-- Travel & Luggage (Category 20)
(91, 'Carry-On Spinner Suitcase', 'Hardshell carry-on with spinner wheels', 129.99, 20, 'active', 'url/products/carryon.jpg'),
(92, 'Travel Backpack 40L', 'Waterproof travel backpack with laptop sleeve', 89.99, 20, 'active', 'url/products/travelbackpack.jpg'),
(93, 'Packing Cube Set', '6-piece packing cube set', 29.99, 20, 'active', 'url/products/packingcubes.jpg'),
(94, 'Travel Pillow Memory Foam', 'Memory foam travel pillow with case', 34.99, 20, 'active', 'url/products/travelpillow.jpg'),
(95, 'Passport Holder RFID', 'Leather passport holder with RFID blocking', 24.99, 20, 'active', 'url/products/passportholder.jpg'),

-- Art & Crafts (Category 21)
(96, 'Acrylic Paint Set', '24-color acrylic paint set with brushes', 34.99, 21, 'active', 'url/products/acrylicpaint.jpg'),
(97, 'Sketchbook Premium', 'Hardcover sketchbook, 100 pages', 19.99, 21, 'active', 'url/products/sketchbook.jpg'),
(98, 'Pottery Wheel Electric', 'Electric pottery wheel for beginners', 199.99, 21, 'active', 'url/products/potterywheel.jpg'),
(99, 'Bead Jewelry Making Kit', 'Complete bead jewelry making kit', 49.99, 21, 'active', 'url/products/beadkit.jpg'),
(100, 'Calligraphy Pen Set', 'Professional calligraphy pen set', 29.99, 21, 'active', 'url/products/calligraphypen.jpg'),

-- Additional popular items across categories
-- Note: These now reference the updated category IDs
(101, 'Bluetooth Speaker Waterproof', 'Portable Bluetooth speaker IPX7 waterproof', 79.99, 2, 'active', 'url/products/bluetoothspeaker.jpg'),
(102, 'Smart Light Bulbs Set', '4-pack smart LED bulbs with app control', 49.99, 2, 'active', 'url/products/smartbulbs.jpg'),
(103, 'Gaming Mouse RGB', 'RGB gaming mouse with programmable buttons', 59.99, 2, 'active', 'url/products/gamingmouse.jpg'),
(104, 'Winter Parka Heavy Duty', 'Heavy duty winter parka with fur hood', 199.99, 3, 'active', 'url/products/winterparka.jpg'),
(105, 'Designer Handbag Leather', 'Genuine leather designer handbag', 299.99, 4, 'active', 'url/products/designerhandbag.jpg'),
(106, 'Air Purifier HEPA', 'HEPA air purifier for large rooms', 179.99, 5, 'active', 'url/products/airpurifier.jpg'),
(107, 'Stand Mixer Professional', 'Professional stand mixer with attachments', 399.99, 6, 'active', 'url/products/standmixer.jpg'),
(108, 'Cookbook Collection', '5-book cookbook collection by famous chefs', 89.99, 7, 'active', 'url/products/cookbooks.jpg'),
(109, 'Fishing Rod Combo', 'Complete fishing rod and reel combo', 89.99, 8, 'active', 'url/products/fishingrod.jpg'),
(110, 'Electric Massage Gun', 'Deep tissue electric massage gun', 129.99, 9, 'active', 'url/products/massagegun.jpg');

-- ######################################################################
-- # 7. ADD MORE PRODUCT VARIANTS (Extending from existing 100 variants)#
-- ######################################################################
-- Main data has variants 1-100, continue from 101
INSERT INTO Product_Variants (variant_id, product_id, stock_quantity, is_available) VALUES
-- Sports Equipment variants (101-120)
(101, 41, 50, 1), (102, 41, 45, 1),  -- Basketball
(103, 42, 60, 1), (104, 42, 55, 1),  -- Yoga Mat
(105, 43, 40, 1), (106, 43, 35, 1),  -- Dumbbell Set
(107, 44, 25, 1), (108, 44, 20, 1),  -- Tennis Racket
(109, 45, 55, 1), (110, 45, 50, 1),  -- Football

-- Toys & Games variants (121-140)
(121, 46, 40, 1), (122, 46, 35, 1),  -- LEGO
(123, 47, 65, 1), (124, 47, 60, 1),  -- Board Games
(125, 48, 30, 1), (126, 48, 25, 1),  -- RC Car
(127, 49, 70, 1), (128, 49, 65, 1),  -- STEM Kit
(129, 50, 20, 1), (130, 50, 15, 1),  -- Dollhouse

-- Automotive variants (141-160)
(141, 51, 80, 1), (142, 51, 75, 1),  -- Phone Mount
(143, 52, 35, 1), (144, 52, 30, 1),  -- Jump Starter
(145, 53, 55, 1), (146, 53, 50, 1),  -- Car Vacuum
(147, 54, 45, 1), (148, 54, 40, 1),  -- Seat Covers
(149, 55, 40, 1), (150, 55, 35, 1),  -- Dash Cam

-- Pet Supplies variants (161-180)
(161, 56, 25, 1), (162, 56, 20, 1),  -- Dog Bed
(163, 57, 40, 1), (164, 57, 35, 1),  -- Cat Feeder
(165, 58, 50, 1), (166, 58, 45, 1),  -- Pet Carrier
(167, 59, 75, 1), (168, 59, 70, 1),  -- Dog Toys
(169, 60, 20, 1), (170, 60, 15, 1),  -- Cat Tree

-- Office Supplies variants (181-200)
(181, 61, 65, 1), (182, 61, 60, 1),  -- Mouse & Keyboard
(183, 62, 85, 1), (184, 62, 80, 1),  -- Desk Organizer
(185, 63, 15, 1), (186, 63, 10, 1),  -- Desk Chair
(187, 64, 70, 1), (188, 64, 65, 1),  -- Label Maker
(189, 65, 40, 1), (190, 65, 35, 1),  -- Paper Shredder

-- Jewelry & Watches variants (201-220)
(201, 66, 55, 1), (202, 66, 50, 1),  -- Necklace
(203, 67, 40, 1), (204, 67, 35, 1),  -- Bracelet Set
(205, 68, 30, 1), (206, 68, 25, 1),  -- Smart Watch
(207, 69, 20, 1), (208, 69, 15, 1),  -- Earrings
(209, 70, 45, 1), (210, 70, 40, 1),  -- Leather Watch

-- Baby & Kids variants (221-240)
(221, 71, 10, 1), (222, 71, 8, 1),   -- Stroller
(223, 72, 50, 1), (224, 72, 45, 1),  -- Baby Monitor
(225, 73, 35, 1), (226, 73, 30, 1),  -- Kids Tablet
(227, 74, 60, 1), (228, 74, 55, 1),  -- Nursing Pillow
(229, 75, 80, 1), (230, 75, 75, 1),  -- Baby Clothes

-- Groceries variants (241-260)
(241, 76, 100, 1), (242, 76, 95, 1),  -- Coffee Beans
(243, 77, 45, 1), (244, 77, 40, 1),   -- Chocolate Box
(245, 78, 70, 1), (246, 78, 65, 1),   -- Olive Oil
(247, 79, 85, 1), (248, 79, 80, 1),   -- Tea Collection
(249, 80, 60, 1), (250, 80, 55, 1),   -- Snack Box

-- Fitness & Gym variants (261-280)
(261, 81, 90, 1), (262, 81, 85, 1),   -- Resistance Bands
(263, 82, 70, 1), (264, 82, 65, 1),   -- Foam Roller
(265, 83, 45, 1), (266, 83, 40, 1),   -- Kettlebell
(267, 84, 75, 1), (268, 84, 70, 1),   -- Fitness Tracker
(269, 85, 95, 1), (270, 85, 90, 1),   -- Yoga Blocks

-- Musical Instruments variants (281-300)
(281, 86, 20, 1), (282, 86, 15, 1),   -- Guitar
(283, 87, 10, 1), (284, 87, 8, 1),    -- Digital Piano
(285, 88, 25, 1), (286, 88, 20, 1),   -- Violin
(287, 89, 55, 1), (288, 89, 50, 1),   -- Drum Pad
(289, 90, 65, 1), (290, 90, 60, 1),   -- Ukulele

-- Travel & Luggage variants (301-320)
(301, 91, 40, 1), (302, 91, 35, 1),   -- Suitcase
(303, 92, 60, 1), (304, 92, 55, 1),   -- Travel Backpack
(305, 93, 85, 1), (306, 93, 80, 1),   -- Packing Cubes
(307, 94, 75, 1), (308, 94, 70, 1),   -- Travel Pillow
(309, 95, 90, 1), (310, 95, 85, 1),   -- Passport Holder

-- Art & Crafts variants (321-340)
(321, 96, 75, 1), (322, 96, 70, 1),   -- Acrylic Paint
(323, 97, 100, 1), (324, 97, 95, 1),  -- Sketchbook
(325, 98, 15, 1), (326, 98, 10, 1),   -- Pottery Wheel
(327, 99, 50, 1), (328, 99, 45, 1),   -- Bead Kit
(329, 100, 65, 1), (330, 100, 60, 1), -- Calligraphy Pen

-- Additional products variants (341-400)
(341, 101, 45, 1), (342, 101, 40, 1),  -- Bluetooth Speaker
(343, 102, 70, 1), (344, 102, 65, 1),  -- Smart Bulbs
(345, 103, 55, 1), (346, 103, 50, 1),  -- Gaming Mouse
(347, 104, 30, 1), (348, 104, 25, 1),  -- Winter Parka
(349, 105, 20, 1), (350, 105, 15, 1),  -- Designer Handbag
(351, 106, 35, 1), (352, 106, 30, 1),  -- Air Purifier
(353, 107, 12, 1), (354, 107, 10, 1),  -- Stand Mixer
(355, 108, 40, 1), (356, 108, 35, 1),  -- Cookbooks
(357, 109, 30, 1), (358, 109, 25, 1),  -- Fishing Rod
(359, 110, 40, 1), (360, 110, 35, 1),  -- Massage Gun

-- More variants for existing popular products (401-450)
(401, 1, 12, 1), (402, 1, 8, 1),      -- Laptop Pro X
(403, 22, 8, 1), (404, 22, 5, 1),     -- Espresso Machine
(405, 31, 12, 1), (406, 31, 10, 1),   -- Camping Tent
(407, 68, 35, 1), (408, 68, 30, 1),   -- Smart Watch
(409, 86, 25, 1), (410, 86, 20, 1),   -- Guitar
(411, 91, 25, 1), (412, 91, 20, 1),   -- Suitcase
(413, 101, 30, 1), (414, 101, 25, 1), -- Bluetooth Speaker
(415, 107, 15, 1), (416, 107, 12, 1), -- Stand Mixer
(417, 110, 30, 1), (418, 110, 25, 1), -- Massage Gun
(419, 46, 30, 1), (420, 46, 25, 1),   -- LEGO
(421, 61, 50, 1), (422, 61, 45, 1),   -- Mouse & Keyboard
(423, 71, 12, 1), (424, 71, 10, 1),   -- Stroller
(425, 81, 70, 1), (426, 81, 65, 1),   -- Resistance Bands
(427, 96, 60, 1), (428, 96, 55, 1),   -- Acrylic Paint
(429, 102, 55, 1), (430, 102, 50, 1), -- Smart Bulbs
(431, 103, 45, 1), (432, 103, 40, 1), -- Gaming Mouse
(433, 104, 20, 1), (434, 104, 15, 1), -- Winter Parka
(435, 105, 18, 1), (436, 105, 12, 1), -- Designer Handbag
(437, 106, 25, 1), (438, 106, 20, 1), -- Air Purifier
(439, 107, 8, 1), (440, 107, 6, 1);   -- Stand Mixer

-- ######################################################################
-- # 8. ADD MORE VARIANT ATTRIBUTES                                     #
-- ######################################################################
INSERT INTO Variant_Attributes (variant_id, attribute_name, attribute_value) VALUES
-- Sports Equipment attributes
(101, 'Color', 'Orange'), (101, 'Size', 'Official'),
(102, 'Color', 'Brown'), (102, 'Size', 'Official'),
(103, 'Color', 'Purple'), (103, 'Thickness', '6mm'),
(104, 'Color', 'Blue'), (104, 'Thickness', '8mm'),
(105, 'Weight', '40lb'), (105, 'Type', 'Adjustable'),
(106, 'Weight', '20lb'), (106, 'Type', 'Fixed'),
(107, 'Grip Size', '4 1/4'), (107, 'String Pattern', '16x19'),
(108, 'Grip Size', '4 3/8'), (108, 'String Pattern', '18x20'),
(109, 'Color', 'Brown'), (109, 'Size', 'Official'),
(110, 'Color', 'White'), (110, 'Size', 'Youth'),

-- Toys & Games attributes
(121, 'Pieces', '500'), (121, 'Theme', 'City'),
(122, 'Pieces', '1000'), (122, 'Theme', 'Castle'),
(123, 'Games', '5'), (123, 'Age', '8+'),
(124, 'Games', '3'), (124, 'Age', '12+'),
(125, 'Scale', '1:10'), (125, 'Battery', 'LiPo'),
(126, 'Scale', '1:14'), (126, 'Battery', 'NiMH'),
(127, 'Experiments', '20'), (127, 'Age', '8-12'),
(128, 'Experiments', '50'), (128, 'Age', '12+'),
(129, 'Levels', '3'), (129, 'Furniture Pieces', '20'),
(130, 'Levels', '2'), (130, 'Furniture Pieces', '15'),

-- Automotive attributes
(141, 'Mount Type', 'Magnetic'), (141, 'Charging', 'Wireless'),
(142, 'Mount Type', 'Clip'), (142, 'Charging', 'None'),
(143, 'Capacity', '15000mAh'), (143, 'Jump Power', '600A'),
(144, 'Capacity', '10000mAh'), (144, 'Jump Power', '400A'),
(145, 'Power Source', 'Cordless'), (145, 'Runtime', '30min'),
(146, 'Power Source', '12V Car'), (146, 'Cord Length', '16ft'),
(147, 'Material', 'Neoprene'), (147, 'Fit', 'Universal'),
(148, 'Material', 'Leatherette'), (148, 'Fit', 'Bucket Seats'),
(149, 'Resolution', '1080p'), (149, 'Night Vision', 'Yes'),
(150, 'Resolution', '720p'), (150, 'Night Vision', 'No'),

-- Pet Supplies attributes
(161, 'Size', 'Large'), (161, 'Material', 'Memory Foam'),
(162, 'Size', 'Medium'), (162, 'Material', 'Orthopedic'),
(163, 'Capacity', '5 meals'), (163, 'Timer', 'Digital'),
(164, 'Capacity', '3 meals'), (164, 'Timer', 'Mechanical'),
(165, 'Size', 'Medium'), (165, 'Airline Approved', 'Yes'),
(166, 'Size', 'Small'), (166, 'Airline Approved', 'Yes'),
(167, 'Pieces', '10'), (167, 'Durability', 'High'),
(168, 'Pieces', '5'), (168, 'Durability', 'Medium'),
(169, 'Height', '60 inches'), (169, 'Platforms', '5'),
(170, 'Height', '48 inches'), (170, 'Platforms', '3'),

-- Office Supplies attributes
(181, 'Connection', 'Wireless'), (181, 'Battery', 'Rechargeable'),
(182, 'Connection', 'Bluetooth'), (182, 'Battery', 'AA'),
(183, 'Material', 'Wood'), (183, 'Compartments', '6'),
(184, 'Material', 'Metal'), (184, 'Compartments', '4'),
(185, 'Color', 'Black'), (185, 'Adjustability', 'Full'),
(186, 'Color', 'Gray'), (186, 'Adjustability', 'Partial'),
(187, 'Tape Width', '12mm'), (187, 'Memory', '26 labels'),
(188, 'Tape Width', '9mm'), (188, 'Memory', '9 labels'),
(189, 'Shred Type', 'Cross-cut'), (189, 'Sheet Capacity', '12'),
(190, 'Shred Type', 'Strip-cut'), (190, 'Sheet Capacity', '8'),

-- Jewelry & Watches attributes
(201, 'Material', 'Gold Plated'), (201, 'Chain Length', '18 inches'),
(202, 'Material', 'Sterling Silver'), (202, 'Chain Length', '20 inches'),
(203, 'Pieces', '3'), (203, 'Clasp Type', 'Lobster'),
(204, 'Pieces', '2'), (204, 'Clasp Type', 'Toggle'),
(205, 'Display', 'Touchscreen'), (205, 'Battery Life', '7 days'),
(206, 'Display', 'E-ink'), (206, 'Battery Life', '30 days'),
(207, 'Diamond Size', '0.5ct'), (207, 'Setting', 'Prong'),
(208, 'Diamond Size', '0.25ct'), (208, 'Setting', 'Bezel'),
(209, 'Strap Material', 'Genuine Leather'), (209, 'Water Resistance', '50m'),
(210, 'Strap Material', 'Stainless Steel'), (210, 'Water Resistance', '100m'),

-- Baby & Kids attributes
(221, 'Color', 'Black'), (221, 'Included', 'Car Seat'),
(222, 'Color', 'Gray'), (222, 'Included', 'Stroller Only'),
(223, 'Camera Type', 'HD Video'), (223, 'Range', '900ft'),
(224, 'Camera Type', 'Audio Only'), (224, 'Range', '600ft'),
(225, 'Storage', '32GB'), (225, 'Parental Controls', 'Yes'),
(226, 'Storage', '16GB'), (226, 'Parental Controls', 'Basic'),
(227, 'Shape', 'C-Shaped'), (227, 'Cover Type', 'Removable'),
(228, 'Shape', 'U-Shaped'), (228, 'Cover Type', 'Fixed'),
(229, 'Size', 'Newborn'), (229, 'Pieces', '10'),
(230, 'Size', '0-3 months'), (230, 'Pieces', '8'),

-- Groceries attributes
(241, 'Roast', 'Medium'), (241, 'Origin', 'Colombia'),
(242, 'Roast', 'Dark'), (242, 'Origin', 'Ethiopia'),
(243, 'Chocolate Type', 'Dark'), (243, 'Pieces', '24'),
(244, 'Chocolate Type', 'Milk'), (244, 'Pieces', '12'),
(245, 'Type', 'Extra Virgin'), (245, 'Volume', '1L'),
(246, 'Type', 'Virgin'), (246, 'Volume', '500ml'),
(247, 'Varieties', '5'), (247, 'Bags per Variety', '20'),
(248, 'Varieties', '3'), (248, 'Bags per Variety', '15'),
(249, 'Snack Types', '8'), (249, 'Subscription', 'Monthly'),
(250, 'Snack Types', '4'), (250, 'Subscription', 'One-time'),

-- Additional variant attributes for the rest
(261, 'Resistance Levels', '5'), (261, 'Material', 'Latex'),
(263, 'Diameter', '6 inches'), (263, 'Material', 'EVA Foam'),
(265, 'Weight Range', '5-25lbs'), (265, 'Adjustment', 'Dial'),
(267, 'Display', 'OLED'), (267, 'Water Resistance', 'IP68'),
(269, 'Material', 'EVA Foam'), (269, 'Dimensions', '9x6x3'),
(281, 'Body Material', 'Spruce'), (281, 'Strings', 'Nylon'),
(283, 'Keys', '88 Weighted'), (283, 'Sounds', '128'),
(285, 'Size', '4/4'), (285, 'Wood', 'Maple'),
(287, 'Surface', 'Rubber'), (287, 'Stand', 'Included'),
(289, 'Size', 'Soprano'), (289, 'Strings', 'Nylon'),
(301, 'Material', 'Polycarbonate'), (301, 'Wheels', 'Spinner'),
(303, 'Capacity', '40L'), (303, 'Laptop Sleeve', '17 inch'),
(305, 'Set Pieces', '6'), (305, 'Material', 'Nylon'),
(307, 'Material', 'Memory Foam'), (307, 'Cover', 'Removable'),
(309, 'Material', 'Genuine Leather'), (309, 'RFID Protection', 'Yes'),
(321, 'Colors', '24'), (321, 'Brush Types', '5'),
(323, 'Paper Weight', '140gsm'), (323, 'Pages', '100'),
(325, 'Speed', 'Variable'), (325, 'Power', 'Electric'),
(327, 'Bead Types', '100+'), (327, 'Tools Included', 'Yes'),
(329, 'Nib Types', '3'), (329, 'Ink Colors', '5'),
(341, 'Waterproof', 'IPX7'), (341, 'Battery', '12h'),
(343, 'Bulbs', '4'), (343, 'Smart Home', 'WiFi'),
(345, 'DPI', '16000'), (345, 'Buttons', 'Programmable'),
(347, 'Insulation', '600 Fill'), (347, 'Temperature', '-20°C'),
(349, 'Material', 'Genuine Leather'), (349, 'Interior', 'Lined'),
(351, 'Room Size', '500 sq ft'), (351, 'Filter Type', 'HEPA'),
(353, 'Power', '1000W'), (353, 'Attachments', '3'),
(355, 'Books', '5'), (355, 'Recipes', '300+'),
(357, 'Length', '6ft'), (357, 'Reel Type', 'Spinning'),
(359, 'Speeds', '5'), (359, 'Attachments', '6');

-- ######################################################################
-- # 9. ADD MORE ORDERS (Extending from existing 25 orders)             #
-- ######################################################################
-- Main data has orders 1-25, continue from 26
INSERT INTO Orders (order_id, user_id, order_date, status, coupon_id, discount_applied, address_id, payment_method, final_total, shipping_cost) VALUES
-- More realistic orders with various statuses and dates
(26, 31, DATE_SUB(NOW(), INTERVAL 170 DAY), 'delivered', 16, 25.00, 14, 'Credit Card', 874.95, 10.00),
(27, 32, DATE_SUB(NOW(), INTERVAL 150 DAY), 'shipped', 17, 30.00, 15, 'Bank Transfer', 1569.95, 15.00),
(28, 33, DATE_SUB(NOW(), INTERVAL 130 DAY), 'processing', 18, 100.00, 16, 'Credit Card', 1299.98, 12.00),
(29, 34, DATE_SUB(NOW(), INTERVAL 110 DAY), 'delivered', 19, 75.00, 17, 'PayPal', 824.97, 8.00),
(30, 35, DATE_SUB(NOW(), INTERVAL 90 DAY), 'delivered', 20, 20.00, 18, 'Credit Card', 279.98, 5.00),
(31, 36, DATE_SUB(NOW(), INTERVAL 70 DAY), 'cancelled', NULL, 0.00, 19, 'N/A', 599.99, 10.00),
(32, 37, DATE_SUB(NOW(), INTERVAL 50 DAY), 'shipped', 21, 15.00, 20, 'Credit Card', 384.98, 7.00),
(33, 38, DATE_SUB(NOW(), INTERVAL 40 DAY), 'processing', 22, 50.00, 21, 'PayPal', 449.98, 9.00),
(34, 39, DATE_SUB(NOW(), INTERVAL 30 DAY), 'delivered', 23, 20.00, 22, 'Credit Card', 179.99, 5.00),
(35, 40, DATE_SUB(NOW(), INTERVAL 20 DAY), 'pending', 24, 15.00, 23, 'Debit Card', 134.98, 5.00),
(36, 41, DATE_SUB(NOW(), INTERVAL 15 DAY), 'shipped', 25, 10.00, 24, 'Credit Card', 89.99, 5.00),
(37, 42, DATE_SUB(NOW(), INTERVAL 10 DAY), 'processing', 26, 20.00, 25, 'PayPal', 319.98, 8.00),
(38, 43, DATE_SUB(NOW(), INTERVAL 8 DAY), 'delivered', 27, 15.00, 26, 'Credit Card', 124.99, 5.00),
(39, 44, DATE_SUB(NOW(), INTERVAL 6 DAY), 'shipped', 28, 25.00, 27, 'Credit Card', 474.98, 10.00),
(40, 45, DATE_SUB(NOW(), INTERVAL 4 DAY), 'pending', 29, 10.00, 28, 'Bank Transfer', 49.99, 5.00),
(41, 46, DATE_SUB(NOW(), INTERVAL 3 DAY), 'processing', 30, 5.00, 29, 'Credit Card', 94.99, 5.00),
(42, 47, DATE_SUB(NOW(), INTERVAL 2 DAY), 'delivered', 31, 15.00, 30, 'PayPal', 234.99, 7.00),
(43, 48, DATE_SUB(NOW(), INTERVAL 1 DAY), 'shipped', 32, 10.00, 31, 'Credit Card', 179.98, 5.00),
(44, 49, DATE_SUB(NOW(), INTERVAL 12 HOUR), 'pending', 33, 30.00, 32, 'Debit Card', 269.99, 6.00),
(45, 50, DATE_SUB(NOW(), INTERVAL 6 HOUR), 'processing', 34, 20.00, 33, 'Credit Card', 129.99, 5.00),
(46, 2, DATE_SUB(NOW(), INTERVAL 4 HOUR), 'pending', 35, 100.00, 1, 'Credit Card', 1899.99, 20.00),
(47, 3, DATE_SUB(NOW(), INTERVAL 2 HOUR), 'processing', NULL, 0.00, 2, 'PayPal', 299.99, 8.00),
(48, 6, NOW(), 'pending', 16, 25.00, 4, 'Credit Card', 174.99, 5.00),
(49, 10, NOW(), 'pending', 17, 30.00, 8, 'Debit Card', 239.99, 7.00),
(50, 15, NOW(), 'processing', 18, 100.00, 4, 'Credit Card', 899.99, 15.00),
(51, 21, DATE_SUB(NOW(), INTERVAL 1 DAY), 'shipped', 19, 75.00, 9, 'PayPal', 524.99, 10.00),
(52, 25, DATE_SUB(NOW(), INTERVAL 2 DAY), 'delivered', 20, 20.00, 13, 'Credit Card', 179.99, 5.00),
(53, 31, DATE_SUB(NOW(), INTERVAL 3 DAY), 'delivered', 21, 15.00, 14, 'Credit Card', 294.99, 7.00),
(54, 37, DATE_SUB(NOW(), INTERVAL 4 DAY), 'shipped', 22, 50.00, 20, 'PayPal', 449.99, 9.00),
(55, 42, DATE_SUB(NOW(), INTERVAL 5 DAY), 'processing', 23, 20.00, 25, 'Credit Card', 199.99, 6.00);

-- ######################################################################
-- # 10. ADD MORE ORDER ITEMS (Extending from existing order items)     #
-- ######################################################################
-- Main data has order items up to ~70, continue from 71
INSERT INTO Order_Items (order_item_id, order_id, quantity, unit_price, variant_id) VALUES
-- Order 26 items
(71, 26, 1, 199.99, 283),   -- Digital Piano
(72, 26, 1, 129.99, 107),   -- Tennis Racket
(73, 26, 1, 89.99, 161),    -- Dog Bed
(74, 26, 1, 49.99, 181),    -- Mouse & Keyboard

-- Order 27 items
(75, 27, 1, 399.99, 353),   -- Stand Mixer
(76, 27, 1, 299.99, 221),   -- Stroller
(77, 27, 2, 199.99, 185),   -- Desk Chair
(78, 27, 1, 129.99, 301),   -- Suitcase

-- Order 28 items
(79, 28, 1, 499.99, 283),   -- Digital Piano
(80, 28, 1, 299.99, 207),   -- Earrings
(81, 28, 1, 199.99, 185),   -- Desk Chair

-- Order 29 items
(82, 29, 1, 199.99, 341),   -- Bluetooth Speaker
(83, 29, 1, 129.99, 301),   -- Suitcase
(84, 29, 1, 89.99, 265),    -- Kettlebell

-- Order 30 items
(85, 30, 2, 89.99, 145),    -- Car Vacuum
(86, 30, 1, 59.99, 121),    -- LEGO Set

-- Order 31 items (cancelled)
(87, 31, 1, 299.99, 221),   -- Stroller

-- Order 32 items
(88, 32, 1, 199.99, 205),   -- Smart Watch
(89, 32, 1, 89.99, 141),    -- Phone Mount
(90, 32, 1, 39.99, 103),    -- Yoga Mat

-- Order 33 items
(91, 33, 1, 299.99, 105),   -- Designer Handbag
(92, 33, 1, 89.99, 125),    -- RC Car

-- Order 34 items
(93, 34, 1, 129.99, 107),   -- Tennis Racket
(94, 34, 1, 24.99, 141),    -- Phone Mount

-- Order 35 items
(95, 35, 1, 89.99, 163),    -- Cat Feeder
(96, 35, 1, 34.99, 127),    -- STEM Kit

-- Order 36 items
(97, 36, 1, 59.99, 267),    -- Fitness Tracker
(98, 36, 1, 24.99, 269),    -- Yoga Blocks

-- Order 37 items
(99, 37, 1, 199.99, 179),   -- Leather Watch (Note: variant 179 doesn't exist, should be 209)
(100, 37, 1, 89.99, 161),   -- Dog Bed

-- Order 38 items
(101, 38, 1, 79.99, 101),   -- Basketball
(102, 38, 1, 29.99, 261),   -- Resistance Bands

-- Order 39 items
(103, 39, 1, 199.99, 283),  -- Digital Piano (partial shipment)
(104, 39, 2, 89.99, 143),   -- Jump Starter

-- Order 40 items
(105, 40, 1, 34.99, 247),   -- Tea Collection

-- Order 41 items
(106, 41, 1, 59.99, 121),   -- LEGO Set
(107, 41, 1, 24.99, 309),   -- Passport Holder

-- Order 42 items
(108, 42, 1, 129.99, 301),  -- Suitcase
(109, 42, 1, 79.99, 101),   -- Basketball

-- Order 43 items
(110, 43, 1, 89.99, 161),   -- Dog Bed
(111, 43, 1, 59.99, 267),   -- Fitness Tracker

-- Order 44 items
(112, 44, 1, 199.99, 205),  -- Smart Watch

-- Order 45 items
(113, 45, 1, 89.99, 125),   -- RC Car

-- Order 46 items (big VIP order)
(114, 46, 1, 499.99, 283),  -- Digital Piano
(115, 46, 1, 399.99, 353),  -- Stand Mixer
(116, 46, 1, 299.99, 221),  -- Stroller

-- Order 47 items
(117, 47, 1, 199.99, 185),  -- Desk Chair
(118, 47, 1, 89.99, 141),   -- Phone Mount

-- Order 48 items
(119, 48, 1, 129.99, 107),  -- Tennis Racket

-- Order 49 items
(120, 49, 1, 199.99, 205),  -- Smart Watch

-- Order 50 items
(121, 50, 1, 499.99, 283),  -- Digital Piano
(122, 50, 1, 299.99, 207),  -- Earrings

-- Order 51 items
(123, 51, 1, 199.99, 341),  -- Bluetooth Speaker
(124, 51, 1, 89.99, 265),   -- Kettlebell

-- Order 52 items
(125, 52, 1, 129.99, 301),  -- Suitcase

-- Order 53 items
(126, 53, 1, 199.99, 205),  -- Smart Watch
(127, 53, 1, 79.99, 101),   -- Basketball

-- Order 54 items
(128, 54, 1, 299.99, 105),  -- Designer Handbag
(129, 54, 1, 129.99, 107),  -- Tennis Racket

-- Order 55 items
(130, 55, 1, 149.99, 209),  -- Leather Watch
(131, 55, 1, 34.99, 247),   -- Tea Collection

-- Additional items for existing orders
(132, 1, 2, 35.00, 8),      -- Additional items for order 1
(133, 2, 1, 89.99, 3),      -- Additional items for order 2
(134, 3, 1, 39.99, 103),    -- Additional items for order 3
(135, 4, 2, 25.00, 70),     -- Additional items for order 4
(136, 5, 1, 15.00, 82),     -- Additional items for order 5
(137, 8, 1, 32.00, 74),     -- Additional items for order 8
(138, 12, 1, 20.00, 68),    -- Additional items for order 12
(139, 15, 1, 49.99, 58),    -- Additional items for order 15
(140, 18, 1, 35.00, 62),    -- Additional items for order 18
(141, 21, 1, 40.00, 47),    -- Additional items for order 21
(142, 24, 1, 199.50, 6),    -- Additional items for order 24
(143, 25, 1, 15.00, 82);    -- Additional items for order 25

-- Fix the variant reference in order item 99 (change 179 to 209)
UPDATE Order_Items SET variant_id = 209 WHERE order_item_id = 99;

-- ######################################################################
-- # 11. ADD MORE SHOPPING CART ITEMS                                   #
-- ######################################################################
-- Main data has cart items 1-25, continue from 26
INSERT INTO Shopping_Cart (cart_item_id, quantity, user_id, variant_id) VALUES
(26, 1, 31, 283),   -- Digital Piano in cart
(27, 2, 32, 353),   -- Stand Mixer x2
(28, 1, 33, 221),   -- Stroller
(29, 3, 34, 141),   -- Phone Mount x3
(30, 1, 35, 205),   -- Smart Watch
(31, 2, 36, 101),   -- Basketball x2
(32, 1, 37, 185),   -- Desk Chair
(33, 4, 38, 309),   -- Passport Holder x4
(34, 1, 39, 267),   -- Fitness Tracker
(35, 2, 40, 247),   -- Tea Collection x2
(36, 1, 41, 161),   -- Dog Bed
(37, 3, 42, 121),   -- LEGO Set x3
(38, 1, 43, 301),   -- Suitcase
(39, 2, 44, 107),   -- Tennis Racket x2
(40, 1, 45, 341),   -- Bluetooth Speaker
(41, 1, 46, 283),   -- Digital Piano
(42, 2, 47, 141),   -- Phone Mount x2
(43, 1, 48, 205),   -- Smart Watch
(44, 3, 49, 101),   -- Basketball x3
(45, 1, 50, 353),   -- Stand Mixer
(46, 2, 2, 341),    -- Bluetooth Speaker x2
(47, 1, 3, 301),    -- Suitcase
(48, 1, 6, 267),    -- Fitness Tracker
(49, 2, 10, 141),   -- Phone Mount x2
(50, 1, 15, 283);   -- Digital Piano

-- ######################################################################
-- # 12. ADD MORE PAYMENTS (Extending from existing 25 payments)        #
-- ######################################################################
-- Main data has payments 1-25, continue from 26
INSERT INTO Payments (payment_id, order_id, payment_date, amount) VALUES
(26, 26, DATE_SUB(NOW(), INTERVAL 170 DAY), 874.95),
(27, 27, DATE_SUB(NOW(), INTERVAL 150 DAY), 1569.95),
(28, 28, DATE_SUB(NOW(), INTERVAL 130 DAY), 1299.98),
(29, 29, DATE_SUB(NOW(), INTERVAL 110 DAY), 824.97),
(30, 30, DATE_SUB(NOW(), INTERVAL 90 DAY), 279.98),
(31, 31, DATE_SUB(NOW(), INTERVAL 70 DAY), 599.99),
(32, 32, DATE_SUB(NOW(), INTERVAL 50 DAY), 384.98),
(33, 33, DATE_SUB(NOW(), INTERVAL 40 DAY), 449.98),
(34, 34, DATE_SUB(NOW(), INTERVAL 30 DAY), 179.99),
(35, 35, DATE_SUB(NOW(), INTERVAL 20 DAY), 134.98),
(36, 36, DATE_SUB(NOW(), INTERVAL 15 DAY), 89.99),
(37, 37, DATE_SUB(NOW(), INTERVAL 10 DAY), 319.98),
(38, 38, DATE_SUB(NOW(), INTERVAL 8 DAY), 124.99),
(39, 39, DATE_SUB(NOW(), INTERVAL 6 DAY), 474.98),
(40, 40, DATE_SUB(NOW(), INTERVAL 4 DAY), 49.99),
(41, 41, DATE_SUB(NOW(), INTERVAL 3 DAY), 94.99),
(42, 42, DATE_SUB(NOW(), INTERVAL 2 DAY), 234.99),
(43, 43, DATE_SUB(NOW(), INTERVAL 1 DAY), 179.98),
(44, 44, DATE_SUB(NOW(), INTERVAL 12 HOUR), 269.99),
(45, 45, DATE_SUB(NOW(), INTERVAL 6 HOUR), 129.99),
(46, 46, DATE_SUB(NOW(), INTERVAL 4 HOUR), 1899.99),
(47, 47, DATE_SUB(NOW(), INTERVAL 2 HOUR), 299.99),
(48, 48, NOW(), 174.99),
(49, 49, NOW(), 239.99),
(50, 50, NOW(), 899.99),
(51, 51, DATE_SUB(NOW(), INTERVAL 1 DAY), 524.99),
(52, 52, DATE_SUB(NOW(), INTERVAL 2 DAY), 179.99),
(53, 53, DATE_SUB(NOW(), INTERVAL 3 DAY), 294.99),
(54, 54, DATE_SUB(NOW(), INTERVAL 4 DAY), 449.99),
(55, 55, DATE_SUB(NOW(), INTERVAL 5 DAY), 199.99);

-- ######################################################################
-- # 13. RE-ENABLE FOREIGN KEYS                                         #
-- ######################################################################
SET FOREIGN_KEY_CHECKS = 1;