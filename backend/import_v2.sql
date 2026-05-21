SET FOREIGN_KEY_CHECKS=0;

-- CATEGORIES
INSERT INTO solevia_v3.categories (id, name, description)
SELECT id, name, description FROM solevia.categories;

-- BRANDS
INSERT INTO solevia_v3.brands (id, name, logo_url, description)
SELECT id, name, logo_url, description FROM solevia.brands;

-- PRODUCTS
INSERT INTO solevia_v3.products (id, name, slug, description, price, discount_percentage, category_id, brand_id, created_at, updated_at)
SELECT id, name, slug, description, price, discount_percentage, category_id, brand_id, created_at, updated_at FROM solevia.products;

-- PRODUCT IMAGES
INSERT INTO solevia_v3.product_images (id, product_id, image_url)
SELECT id, product_id, image_url FROM solevia.product_images;

-- PRODUCT VARIANTS
INSERT INTO solevia_v3.product_variants (id, product_id, size, stock, created_at, updated_at)
SELECT id, product_id, size, stock, created_at, updated_at FROM solevia.product_variants;

-- USERS
INSERT INTO solevia_v3.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at)
SELECT id, name, email, email_verified_at, password, remember_token, created_at, updated_at FROM solevia.users;

-- CARTS (V2 tidak punya timestamps)
INSERT INTO solevia_v3.carts (id, user_id)
SELECT id, user_id FROM solevia.carts;

-- CART ITEMS
INSERT IGNORE INTO solevia_v3.cart_items (id, cart_id, variant_id, quantity, created_at)
SELECT id, cart_id, variant_id, quantity, created_at FROM solevia.cart_items;

-- ORDERS (map processing -> paid)
INSERT INTO solevia_v3.orders (id, user_id, order_number, total_amount, status, payment_status, created_at, updated_at)
SELECT id, user_id, order_number, total_amount, CASE WHEN status='processing' THEN 'paid' ELSE status END, payment_status, created_at, updated_at FROM solevia.orders;

-- ORDER ITEMS
INSERT INTO solevia_v3.order_items (id, order_id, variant_id, quantity, item_price)
SELECT id, order_id, variant_id, quantity, item_price FROM solevia.order_items;

-- PAYMENTS
INSERT INTO solevia_v3.payments (id, order_id, payment_method, amount, status, paid_at)
SELECT id, order_id, payment_method, amount, status, paid_at FROM solevia.payments;

-- SHIPPINGS
INSERT INTO solevia_v3.shippings (id, order_id, recipient_name, phone_number, address, city, province, postal_code, shipping_cost, created_at)
SELECT id, order_id, recipient_name, phone_number, address, city, province, postal_code, shipping_cost, created_at FROM solevia.shippings;

-- WISHLISTS
INSERT IGNORE INTO solevia_v3.wishlists (id, user_id, product_id, created_at)
SELECT id, user_id, product_id, created_at FROM solevia.wishlists;

SET FOREIGN_KEY_CHECKS=1;
SELECT 'IMPORT SELESAI!' AS result;
