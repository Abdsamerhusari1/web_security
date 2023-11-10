# web_security

#database:

Access Your Database in phpMyAdmin:

Go to http://localhost/phpmyadmin in your browser.
Click on your webshop_db database to select it.
Navigate to the SQL Tab:

Once you're in your database, click on the "SQL" tab at the top of the phpMyAdmin interface. This is where you can run SQL queries.
Write and Execute SQL Queries:

In the SQL query text area, you can write your SQL commands to create tables.

CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    address TEXT NOT NULL
);

CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT NOT NULL,
    image VARCHAR(255)
);



CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(product_id)
);


After typing each SQL command, click the "Go" button in phpMyAdmin to execute the query

go to https://localhost/webshop/test_connection.php


INSERT INTO users (username, password_hash, address) VALUES 
('testuser1', '$2y$10$iP0Gy6dlb86Z5WY2UKxxNOpG4UjLzTQnreSUzABiTWjbeh11Wqw3a', '123 Test Address, City, Country'),
('testuser2', '$2y$10$Sec5Il/l0O6188TIblWgh.8dt98xyhKZpYk1Sish6l9.k/e95.V7C', '456 Another St, City, Country');

INSERT INTO products (name, description, price, stock, image) VALUES 
('First Product', 'Description for first product', 99.99, 10, '1.png'),
('Second Product', 'Description for second product', 149.99, 5, '2.png');


----------------------