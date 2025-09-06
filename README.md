replace the connection password to your actual password 


    $user_name = "root";
    $password = "your_password";  
    $database = "your_database_name";
    $server = "localhost";
    $port = "your_port";

-- ==========================================
-- CREATE DATABASE
-- ==========================================
CREATE DATABASE IF NOT EXISTS onlineshopping 
DEFAULT CHARACTER SET latin1 
COLLATE latin1_swedish_ci;

USE onlineshopping;

-- ==========================================
-- TABLES
-- ==========================================

-- Admin Table
CREATE TABLE IF NOT EXISTS admin (
  id int(11) NOT NULL AUTO_INCREMENT,
  fname varchar(50) NOT NULL,
  mname varchar(50) DEFAULT NULL,
  lname varchar(50) NOT NULL,
  username varchar(50) NOT NULL,
  birthdate date NOT NULL,
  shopname varchar(255) NOT NULL,
  password varchar(255) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY username (username)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
  user_id int(11) NOT NULL AUTO_INCREMENT,
  fname varchar(50) NOT NULL,
  lname varchar(50) NOT NULL,
  birthdate date NOT NULL,
  email_or_phone varchar(100) NOT NULL,
  password varchar(255) NOT NULL,
  address varchar(255) NOT NULL,
  profile_picture varchar(255) DEFAULT NULL,
  PRIMARY KEY (user_id),
  UNIQUE KEY email_or_phone (email_or_phone)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Products Table
CREATE TABLE IF NOT EXISTS products (
  id int(11) NOT NULL AUTO_INCREMENT,
  picture varchar(255) NOT NULL,
  price decimal(10,2) NOT NULL,
  stocks int(11) NOT NULL,
  description text NOT NULL,
  product_name text NOT NULL,
  admin_id int(11) NOT NULL,
  PRIMARY KEY (id),
  KEY fk_admin (admin_id),
  CONSTRAINT fk_admin FOREIGN KEY (admin_id) REFERENCES admin (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Cart Table
CREATE TABLE IF NOT EXISTS cart (
  id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  product_id int(11) NOT NULL,
  quantity int(11) DEFAULT 1,
  total_price decimal(10,2) DEFAULT 0.00,
  PRIMARY KEY (id),
  KEY user_id (user_id),
  KEY product_id (product_id),
  CONSTRAINT cart_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (user_id),
  CONSTRAINT cart_ibfk_2 FOREIGN KEY (product_id) REFERENCES products (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
  order_id int(11) NOT NULL AUTO_INCREMENT,
  user_id int(11) NOT NULL,
  name varchar(100) NOT NULL,
  email varchar(100) NOT NULL,
  address varchar(255) NOT NULL,
  order_date timestamp NULL DEFAULT current_timestamp(),
  total_amount decimal(10,2) DEFAULT NULL,
  payment_method varchar(50) DEFAULT NULL,
  PRIMARY KEY (order_id),
  KEY user_id (user_id),
  CONSTRAINT orders_ibfk_1 FOREIGN KEY (user_id) REFERENCES users (user_id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Order Items Table
CREATE TABLE IF NOT EXISTS order_items (
  id int(11) NOT NULL AUTO_INCREMENT,
  order_id int(11) NOT NULL,
  product_id int(11) NOT NULL,
  quantity int(11) NOT NULL,
  price decimal(10,2) NOT NULL,
  PRIMARY KEY (id),
  KEY order_id (order_id),
  KEY product_id (product_id),
  CONSTRAINT order_items_ibfk_1 FOREIGN KEY (order_id) REFERENCES orders (order_id) ON DELETE CASCADE,
  CONSTRAINT order_items_ibfk_2 FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Messages Table
CREATE TABLE IF NOT EXISTS messages (
  id int(11) NOT NULL AUTO_INCREMENT,
  sender_id int(11) NOT NULL,
  recipient_id int(11) NOT NULL,
  admin_id int(11) NOT NULL,
  product_id int(11) NOT NULL,
  message text NOT NULL,
  timestamp datetime DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY sender_id (sender_id),
  KEY recipient_id (recipient_id),
  KEY admin_id (admin_id),
  KEY product_id (product_id),
  CONSTRAINT messages_ibfk_1 FOREIGN KEY (sender_id) REFERENCES users (user_id),
  CONSTRAINT messages_ibfk_2 FOREIGN KEY (recipient_id) REFERENCES users (user_id),
  CONSTRAINT messages_ibfk_3 FOREIGN KEY (admin_id) REFERENCES admin (id),
  CONSTRAINT messages_ibfk_4 FOREIGN KEY (product_id) REFERENCES products (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Admin Reply Table
CREATE TABLE IF NOT EXISTS admin_reply (
  id int(11) NOT NULL AUTO_INCREMENT,
  admin_id int(11) NOT NULL,
  user_id int(11) DEFAULT NULL,
  product_id int(11) NOT NULL,
  reply text NOT NULL,
  timestamp datetime DEFAULT current_timestamp(),
  admin_reply text DEFAULT NULL,
  message_id int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY admin_id (admin_id),
  KEY user_id (user_id),
  KEY product_id (product_id),
  KEY fk_message (message_id),
  CONSTRAINT admin_reply_ibfk_1 FOREIGN KEY (admin_id) REFERENCES admin (id),
  CONSTRAINT admin_reply_ibfk_2 FOREIGN KEY (user_id) REFERENCES users (user_id),
  CONSTRAINT admin_reply_ibfk_3 FOREIGN KEY (product_id) REFERENCES products (id),
  CONSTRAINT fk_message FOREIGN KEY (message_id) REFERENCES messages (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Comments Table
CREATE TABLE IF NOT EXISTS comments (
  id int(11) NOT NULL AUTO_INCREMENT,
  product_id int(11) NOT NULL,
  user_id int(11) NOT NULL,
  admin_id int(11) NOT NULL,
  comment text NOT NULL,
  comment_date timestamp NULL DEFAULT current_timestamp(),
  parent_comment_id int(11) DEFAULT NULL,
  reply_to int(11) DEFAULT NULL,
  PRIMARY KEY (id),
  KEY product_id (product_id),
  KEY user_id (user_id),
  KEY admin_id (admin_id),
  CONSTRAINT comments_ibfk_1 FOREIGN KEY (product_id) REFERENCES products (id),
  CONSTRAINT comments_ibfk_2 FOREIGN KEY (user_id) REFERENCES users (user_id),
  CONSTRAINT comments_ibfk_3 FOREIGN KEY (admin_id) REFERENCES admin (id)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- Replies Table
CREATE TABLE IF NOT EXISTS replies (
  id int(11) NOT NULL AUTO_INCREMENT,
  comment_id int(11) NOT NULL,
  admin_id int(11) NOT NULL,
  reply text NOT NULL,
  reply_date timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (id),
  KEY comment_id (comment_id),
  KEY admin_id (admin_id),
  CONSTRAINT replies_ibfk_1 FOREIGN KEY (comment_id) REFERENCES comments (id) ON DELETE CASCADE,
  CONSTRAINT replies_ibfk_2 FOREIGN KEY (admin_id) REFERENCES admin (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    admin_id INT NOT NULL, -- shop owner of the product
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_wishlist_user FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    CONSTRAINT fk_wishlist_product FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    CONSTRAINT fk_wishlist_admin FOREIGN KEY (admin_id) REFERENCES admin(id) ON DELETE CASCADE,
    UNIQUE KEY unique_wishlist (user_id, product_id) -- Prevent duplicate wishlist entries per user
);
