--Create the Database
CREATE DATABASE E_Commerce_Store;
USE E_Commerce_Store;
-- Category Table
CREATE TABLE Categories (
  category_id int(10) NOT NULL AUTO_INCREMENT, 
  name        varchar(255) NOT NULL UNIQUE, 
  PRIMARY KEY (category_id));
-- Promotions and Coupons Table
CREATE TABLE Promotions_and_Coupons (
  coupon_id       int(10) NOT NULL AUTO_INCREMENT, 
  code            varchar(255) UNIQUE, 
  discount_value  decimal(10, 2), 
  valid_from      date, 
  expires_at      date, 
  min_order_total decimal(10, 2), 
  PRIMARY KEY (coupon_id));
-- Users Table
CREATE TABLE Users (
  user_id      int(10) NOT NULL AUTO_INCREMENT, 
  email        varchar(255) NOT NULL UNIQUE, 
  password     varchar(255) NOT NULL, 
  full_name    varchar(255) NOT NULL, 
  phone_number varchar(255) NOT NULL UNIQUE, 
  created_at   timestamp NOT NULL, 
  role         varchar(255), 
  PRIMARY KEY (user_id));
-- User Addresses
CREATE TABLE User_Addresses (
  address_id     int(10) NOT NULL AUTO_INCREMENT, 
  user_id        int(10) NOT NULL, 
  label          varchar(255) NOT NULL, 
  street_address varchar(255) NOT NULL, 
  city           varchar(255) NOT NULL, 
  zip_code       int(10), 
  PRIMARY KEY (address_id));

-- Product Definition
CREATE TABLE Products (
  product_id  int(10) NOT NULL AUTO_INCREMENT, 
  name        varchar(255) NOT NULL, 
  description varchar(255), 
  price       decimal(10, 2) NOT NULL, 
  category_id int(10) NOT NULL, 
  status      varchar(255), 
  image_url   varchar(255), 
  PRIMARY KEY (product_id));
-- Product Variants
CREATE TABLE Product_Variants (
  variant_id     int(10) NOT NULL AUTO_INCREMENT, 
  product_id     int(10) NOT NULL, 
  stock_quantity int(10), 
  is_available   bit(1), 
  PRIMARY KEY (variant_id));
-- Variant Attributes (Color, Size, etc.)
CREATE TABLE Variant_Attributes (
  variant_attribute_id int(10) NOT NULL AUTO_INCREMENT, 
  variant_id           int(10) NOT NULL, 
  attribute_name       varchar(50) NOT NULL, 
  attribute_value      varchar(50), 
  PRIMARY KEY (variant_attribute_id));
-- Shopping Cart Items
CREATE TABLE Shopping_Cart (
  cart_item_id int(10) NOT NULL AUTO_INCREMENT, 
  quantity     int(10) NOT NULL, 
  user_id      int(10) NOT NULL, 
  variant_id   int(10) NOT NULL, 
  PRIMARY KEY (cart_item_id));
-- Customer Orders
CREATE TABLE Orders (
  order_id          int(10) NOT NULL AUTO_INCREMENT, 
  user_id           int(10) NOT NULL, 
  order_date        timestamp NOT NULL, 
  status            varchar(255), 
  coupon_id         int(10), 
  discount_applied  decimal(10, 2), 
  address_id        int(10) NOT NULL, 
  payment_method    varchar(50), 
  final_total       decimal(10, 2) NOT NULL, 
  shipping_cost     decimal(10, 2) NOT NULL, 
  PRIMARY KEY (order_id));
-- Order Item Details
CREATE TABLE Order_Items (
  order_item_id int(10) NOT NULL AUTO_INCREMENT, 
  order_id      int(10) NOT NULL, 
  quantity      int(10) NOT NULL, 
  unit_price    decimal(10, 2) NOT NULL, 
  variant_id    int(10) NOT NULL, 
  PRIMARY KEY (order_item_id));
-- Payment Records
CREATE TABLE Payments (
  payment_id   int(10) NOT NULL AUTO_INCREMENT, 
  order_id     int(10) NOT NULL UNIQUE, 
  payment_date timestamp NOT NULL, 
  amount       decimal(10, 2) NOT NULL, 
  PRIMARY KEY (payment_id));
-- Shopping Cart Items
CREATE TABLE Shopping_Cart (
  cart_item_id int(10) NOT NULL AUTO_INCREMENT, 
  quantity     int(10) NOT NULL, 
  user_id      int(10) NOT NULL, 
  variant_id   int(10) NOT NULL, 
  PRIMARY KEY (cart_item_id));
-- Customer Orders
CREATE TABLE Orders (
  order_id          int(10) NOT NULL AUTO_INCREMENT, 
  user_id           int(10) NOT NULL, 
  order_date        timestamp NOT NULL, 
  status            varchar(255), 
  coupon_id         int(10), 
  discount_applied  decimal(10, 2), 
  address_id        int(10) NOT NULL, 
  payment_method    varchar(50), 
  final_total       decimal(10, 2) NOT NULL, 
  shipping_cost     decimal(10, 2) NOT NULL, 
  PRIMARY KEY (order_id));
-- Order Item Details
CREATE TABLE Order_Items (
  order_item_id int(10) NOT NULL AUTO_INCREMENT, 
  order_id      int(10) NOT NULL, 
  quantity      int(10) NOT NULL, 
  unit_price    decimal(10, 2) NOT NULL, 
  variant_id    int(10) NOT NULL, 
  PRIMARY KEY (order_item_id));
-- Payment Records
CREATE TABLE Payments (
  payment_id   int(10) NOT NULL AUTO_INCREMENT, 
  order_id     int(10) NOT NULL UNIQUE, 
  payment_date timestamp NOT NULL, 
  amount       decimal(10, 2) NOT NULL, 
  PRIMARY KEY (payment_id));
-- Relationships with Users
ALTER TABLE Orders
ADD CONSTRAINT FKOrders145166
FOREIGN KEY (user_id) REFERENCES Users (user_id);
ALTER TABLE User_Addresses
ADD CONSTRAINT FKUser_Addre599603
FOREIGN KEY (user_id) REFERENCES Users (user_id);
ALTER TABLE Shopping_Cart
ADD CONSTRAINT FKShopping_C125642
FOREIGN KEY (user_id) REFERENCES Users (user_id);
-- Relationships for Products
ALTER TABLE Products
ADD CONSTRAINT FKProducts459619
FOREIGN KEY (category_id) REFERENCES Categories (category_id);
ALTER TABLE Product_Variants
ADD CONSTRAINT FKProduct_Va769126
FOREIGN KEY (product_id) REFERENCES Products (product_id);
ALTER TABLE Variant_Attributes
ADD CONSTRAINT FKVariant_At263396
FOREIGN KEY (variant_id) REFERENCES Product_Variants (variant_id);
ALTER TABLE Shopping_Cart
ADD CONSTRAINT FKShopping_C607982
FOREIGN KEY (variant_id) REFERENCES Product_Variants (variant_id);
-- Relationships for Orders and Payments
ALTER TABLE Orders
ADD CONSTRAINT FKOrders151327
FOREIGN KEY (address_id) REFERENCES User_Addresses (address_id);
ALTER TABLE Orders
ADD CONSTRAINT FKOrders643118
FOREIGN KEY (coupon_id) REFERENCES Promotions_and_Coupons (coupon_id);
ALTER TABLE Order_Items
ADD CONSTRAINT FKOrder_Item921064
FOREIGN KEY (order_id) REFERENCES Orders (order_id);
ALTER TABLE Order_Items
ADD CONSTRAINT FKOrder_Item350744
FOREIGN KEY (variant_id) REFERENCES Product_Variants (variant_id);

ALTER TABLE Payments
ADD CONSTRAINT FKPayments35249
FOREIGN KEY (order_id) REFERENCES Orders (order_id);
-- User Constraints
ALTER TABLE Users ADD CONSTRAINT chk_user_role 
  CHECK (role IN ('customer', 'admin', 'moderator'));
-- Product Constraints
ALTER TABLE Products ADD CONSTRAINT chk_product_status 
  CHECK (status IN ('active', 'inactive', 'out_of_stock'));
ALTER TABLE Products ADD CONSTRAINT chk_price_positive 
  CHECK (price >= 0);
-- Inventory Constraints
ALTER TABLE Product_Variants ADD CONSTRAINT chk_stock_non_negative 
  CHECK (stock_quantity >= 0);
-- Order and Transaction Constraints
ALTER TABLE Orders ADD CONSTRAINT chk_order_status 
  CHECK (status IN ('pending', 'processing', 'shipped', 'delivered', 'cancelled'));
ALTER TABLE Order_Items ADD CONSTRAINT chk_quantity_positive 
  CHECK (quantity > 0);
