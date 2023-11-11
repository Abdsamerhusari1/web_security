# web_security

## Database Setup

### Accessing Your Database in phpMyAdmin:

1. **Open phpMyAdmin:**
   - Navigate to `http://localhost/phpmyadmin` in your browser.
   
2. **Select Database:**
   - Click on your `webshop_db` database to select it.
   
3. **Navigate to SQL Tab:**
   - In your database, click on the "SQL" tab at the top of the phpMyAdmin interface for SQL queries.

### Writing and Executing SQL Queries:

- In the SQL query text area, write your SQL commands to create tables.
- Example SQL commands to create tables:

```sql
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
```
ALTER TABLE users
ADD COLUMN public_key VARCHAR(2048);

CREATE TABLE user_private_keys (
    user_id INT PRIMARY KEY,
    private_key VARCHAR(2048) NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(user_id)
);

- After typing each SQL command, click the "Go" button in phpMyAdmin to execute the query.

### Test Connection:

- Visit `https://localhost/webshop/test_connection.php` to test database connection.

### Sample Data Insertion:

- Insert sample user data:

```sql
INSERT INTO users (username, password_hash, address) VALUES 
('testuser1', '[hash]', '123 Test Address, City, Country'),
('testuser2', '[hash]', '456 Another St, City, Country');
```

- Insert sample product data:

```sql
INSERT INTO products (name, description, price, stock, image) VALUES 
('First Product', 'Description for first product', 99.99, 10, '1.png'),
('Second Product', 'Description for second product', 149.99, 5, '2.png'),
('Third Product', 'Description for Third product', 49.99, 5, '3.png'),
('Fourth Product', 'Description for Fourth product', 49.99, 0, '4.png');
```

## To-Do List

### Server and PHP Configuration:

- Ensure server and PHP configurations are optimized for security against common attacks.

### User Authentication:

- Implement a robust password policy.
- Passwords should be a minimum of 8 characters, include a mix of cases, numbers, and symbols, and avoid common patterns.
- Utilize a password blacklist to prevent the use of common passwords: [OWASP 10k Worst Passwords](https://github.com/OWASP/passfault/blob/master/wordlists/wordlists/10k-worst-passwords.txt).

### Password Handling:

- Use a pepper for added security: `kQa9e4v8Jy3Cf1u5Rm7N0w2Hz8G6pX`.
- The `password_hash` function should concatenate the password with the pepper, generate a random salt, hash the concatenated string with the salt, and return the complete hash string.

### Brute Force Attack Mitigation:

- Implement a strategy to mitigate brute-force attacks by tracking consecutive failed login attempts and imposing delays after a threshold is reached.

- After each failed login attempt, increment a counter in the user's session or database.
- If the counter exceeds a certain number (e.g., 3 attempts), record the time of the last failed attempt.
- When the user tries to log in, check the time of the last failed attempt. If it was less than 30 minutes ago and the counter -is over the threshold, deny the login attempt.
- If the login is successful, reset the counter.