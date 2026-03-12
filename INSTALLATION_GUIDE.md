# MRM Grocery & Wholesale - Local Installation Guide

Welcome to the **MRM Grocery & Wholesale** ecommerce web application! This guide will walk you through the steps to seamlessly run and demo this website locally on your computer using either **XAMPP** or **MAMP** for Windows.

---

## 🟢 Option 1: Running via MAMP (Recommended for this project)

MAMP is simple and sets up an isolated Apache and MySQL environment. Because your project is currently configured to run smoothly on MAMP, this is the quickest way to test.

### Step 1: Preparation
1. Download and install [MAMP for Windows](https://www.mamp.info/en/windows/).
2. Copy the entire `ecomgura` project folder into the MAMP document root directory.
   - Typically, this is located at: `C:\MAMP\htdocs\`
   - So your project should sit at: `C:\MAMP\htdocs\ecomgura\`

### Step 2: Start the Servers
1. Open the **MAMP** application.
2. Click the **Start Servers** button. You should see both the Apache Server and MySQL Server indicators light up (turn green).

### Step 3: Set up the Database
1. Open your web browser and navigate to: `http://localhost/phpMyAdmin/`
2. In the left panel, click on **New**.
3. Create a new database and name it exactly: `mrm_grocery`
   - *If you're importing an old database, make sure you name it `mrm_grocery` unless you change the name in `includes/db.php`.*
4. Click the **Import** tab at the top.
5. Choose your `.sql` database file (if available) and click **Import/Go**. 
   *(Note: The database connection credentials for MAMP are usually Username: `root` / Password: `root` by default. The project's `includes/db.php` is already configured to work with these or standard local configurations).*

### Step 4: Run the Website
1. Your database and servers are now fully running!
2. Open your web browser and go to: `http://localhost/ecomgura/`
3. Enjoy the MRM Grocery experience!

---

## 🟠 Option 2: Running via XAMPP

XAMPP is another highly popular local development environment. It works perfectly with this project as well.

### Step 1: Preparation
1. Download and install [XAMPP for Windows](https://www.apachefriends.org/index.html).
2. Copy the entire `ecomgura` project folder into the XAMPP web root directory.
   - Typically, this is located at: `C:\xampp\htdocs\`
   - So your project should sit at: `C:\xampp\htdocs\ecomgura\`

### Step 2: Update Database Credentials (Crucial!)
XAMPP uses a different default password for the MySQL database than MAMP. You must update your config file:
1. Open `C:\xampp\htdocs\ecomgura\includes\db.php` in a text editor (like VS Code or Notepad).
2. Change the `DB_PASS` variable to be empty. It should look exactly like this:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');           // <-- Ensure password is empty for XAMPP
   define('DB_NAME', 'mrm_grocery');
   ```

### Step 3: Start the Servers
1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache** and click **Start** next to **MySQL**. Both should turn green.

### Step 4: Set up the Database
1. Open your web browser and navigate to: `http://localhost/phpmyadmin/`
2. Click **New** on the left sidebar.
3. Name the database `mrm_grocery` and click **Create**.
4. Click the **Import** tab.
5. Select your `.sql` database backup file and click **Import** at the very bottom.

### Step 5: Run the Website
1. You are good to go!
2. Open your web browser and visit: `http://localhost/ecomgura/`

---

## 🔐 Built-in Demo Accounts
To easily test checkout, ordering, and the admin panel, here are the default configured accounts you can use on the `/login.php` page:

**👑 Admin Account** (Access to Product Management & Order Tracking)
- **Email:** `admin@mrm.com`
- **Password:** `admin123`
- *Dashboard Path:* `http://localhost/ecomgura/admin/index.php`

**👤 Standard User Account** (Can add to cart, view orders, etc.)
- **Email:** `kamal@example.com`
- **Password:** `user123`


