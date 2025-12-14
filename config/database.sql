  CREATE DATABASE


CREATE DATABASE IF NOT EXISTS slasher_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_general_ci;

USE slasher_db;



TABLE: categories


CREATE TABLE categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(100) NOT NULL,
  UNIQUE KEY slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




TABLE: platforms


CREATE TABLE platforms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  slug VARCHAR(100) NOT NULL,
  UNIQUE KEY slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




TABLE: products


CREATE TABLE products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) NOT NULL,
  description TEXT,
  price INT NOT NULL DEFAULT 0,
  discount_percent INT NOT NULL DEFAULT 0,
  image VARCHAR(255),
  is_featured TINYINT(1) DEFAULT 0,
  is_best_deal TINYINT(1) DEFAULT 0,
  is_trending TINYINT(1) DEFAULT 0,
  is_upcoming TINYINT(1) DEFAULT 0,
  is_bestseller TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




TABLE: product_categories (pivot)


CREATE TABLE product_categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  category_id INT NOT NULL,
  is_primary TINYINT(1) DEFAULT 0,

  CONSTRAINT fk_pc_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_pc_category
    FOREIGN KEY (category_id) REFERENCES categories(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




TABLE: product_platforms (pivot)


CREATE TABLE product_platforms (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  platform_id INT NOT NULL,

  CONSTRAINT fk_pp_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_pp_platform
    FOREIGN KEY (platform_id) REFERENCES platforms(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




TABLE: product_screenshots


CREATE TABLE product_screenshots (
  id INT AUTO_INCREMENT PRIMARY KEY,
  product_id INT NOT NULL,
  image VARCHAR(255) NOT NULL,

  CONSTRAINT fk_ps_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




TABLE: users


CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('admin','user') DEFAULT 'user',
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




TABLE: orders


CREATE TABLE orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  total_price INT NOT NULL,
  status VARCHAR(50) DEFAULT 'pending',
  midtrans_order_id VARCHAR(100),
  snap_token VARCHAR(255),
  payment_status VARCHAR(50),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_orders_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




TABLE: order_items


CREATE TABLE order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  platform_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  unit_price INT NOT NULL,

  CONSTRAINT fk_oi_order
    FOREIGN KEY (order_id) REFERENCES orders(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_oi_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_oi_platform
    FOREIGN KEY (platform_id) REFERENCES platforms(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




TABLE: redeem_codes


CREATE TABLE redeem_codes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_item_id INT NOT NULL,
  platform_id INT NOT NULL,
  code VARCHAR(100) NOT NULL,
  delivered_at DATETIME NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_rc_order_item
    FOREIGN KEY (order_item_id) REFERENCES order_items(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_rc_platform
    FOREIGN KEY (platform_id) REFERENCES platforms(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




TABLE: settings


CREATE TABLE settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  `key` VARCHAR(100) NOT NULL,
  `value` TEXT,
  UNIQUE KEY `key` (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;




TABLE: wishlists


CREATE TABLE wishlists (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  product_id INT NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_wishlist_user
    FOREIGN KEY (user_id) REFERENCES users(id)
    ON DELETE CASCADE,

  CONSTRAINT fk_wishlist_product
    FOREIGN KEY (product_id) REFERENCES products(id)
    ON DELETE CASCADE,

  UNIQUE KEY unique_wishlist (user_id, product_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
