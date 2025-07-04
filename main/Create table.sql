USE BakeryOrderSystem;
GO

-- Drop tables in reverse dependency order if they exist
IF OBJECT_ID('Payments', 'U') IS NOT NULL DROP TABLE Payments;
IF OBJECT_ID('Order_Items', 'U') IS NOT NULL DROP TABLE Order_Items;
IF OBJECT_ID('Orders', 'U') IS NOT NULL DROP TABLE Orders;
IF OBJECT_ID('Products', 'U') IS NOT NULL DROP TABLE Products;
IF OBJECT_ID('Staff', 'U') IS NOT NULL DROP TABLE Staff;
IF OBJECT_ID('Customers', 'U') IS NOT NULL DROP TABLE Customers;
GO

-- Create Customers table
CREATE TABLE Customers (
    customer_id INT IDENTITY(1,1) PRIMARY KEY,
    first_name NVARCHAR(50) NOT NULL,
    last_name NVARCHAR(50) NOT NULL,
    email NVARCHAR(100) UNIQUE NOT NULL,
    phone NVARCHAR(20),
    address NVARCHAR(MAX),
    password_hash NVARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE()
);
GO

-- Create Categories table
--CREATE TABLE Categories (
--    category_id INT IDENTITY(1,1) PRIMARY KEY,
--    name NVARCHAR(50) NOT NULL,
--    description NVARCHAR(MAX),
--    created_at DATETIME DEFAULT GETDATE()
--);
--GO

-- Create Products table
CREATE TABLE Products (
    product_id INT IDENTITY(1,1) PRIMARY KEY,
--    category_id INT,
    name NVARCHAR(100) NOT NULL,
    description NVARCHAR(MAX),
    price DECIMAL(10, 2) NOT NULL,
--    cost DECIMAL(10, 2) NOT NULL,
    image_url NVARCHAR(255),
    is_available BIT DEFAULT 1,
    created_at DATETIME DEFAULT GETDATE(),
    updated_at DATETIME DEFAULT GETDATE(),
--    FOREIGN KEY (category_id) REFERENCES Categories(category_id) ON DELETE SET NULL
);
GO

-- Create Staff table
CREATE TABLE Staff (
    staff_id INT IDENTITY(1,1) PRIMARY KEY,
    first_name NVARCHAR(50) NOT NULL,
    last_name NVARCHAR(50) NOT NULL,
    email NVARCHAR(100) UNIQUE NOT NULL,
    phone NVARCHAR(20) NOT NULL,
    role NVARCHAR(20) NOT NULL CHECK (role IN ('Baker', 'Cashier', 'Manager', 'Delivery')),
    hire_date DATE NOT NULL,
    salary DECIMAL(10, 2),
    is_active BIT DEFAULT 1
);
GO

-- Create Orders table
CREATE TABLE Orders (
    order_id INT IDENTITY(1,1) PRIMARY KEY,
    customer_id INT NOT NULL,
    order_datetime DATETIME DEFAULT GETDATE(),
    total_amount DECIMAL(10, 2) NOT NULL,
    status NVARCHAR(20) NOT NULL DEFAULT 'Pending' 
        CHECK (status IN ('Pending', 'Processing', 'Preparing', 'Completed', 'Cancelled')),
    notes NVARCHAR(MAX),
    FOREIGN KEY (customer_id) REFERENCES Customers(customer_id) ON DELETE CASCADE
);
GO

-- Create Order_Items table
CREATE TABLE Order_Items (
    order_item_id INT IDENTITY(1,1) PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL CHECK (quantity > 0),
	unit_price DECIMAL(10, 2) NOT NULL,
    special_instructions NVARCHAR(MAX),
    FOREIGN KEY (order_id) REFERENCES Orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES Products(product_id) ON DELETE NO ACTION
);
GO

-- Create Inventory table
--CREATE TABLE Inventory (
--    inventory_id INT IDENTITY(1,1) PRIMARY KEY,
--    product_id INT NOT NULL,
--    quantity_in_stock INT NOT NULL DEFAULT 0,
--    minimum_stock_level INT NOT NULL DEFAULT 5,
--    last_restocked DATE DEFAULT GETDATE(),
--    FOREIGN KEY (product_id) REFERENCES Products(product_id) ON DELETE CASCADE
--);
--GO

-- Create Payments table
CREATE TABLE Payments (
    payment_id INT IDENTITY(1,1) PRIMARY KEY,
    order_id INT NOT NULL FOREIGN KEY REFERENCES Orders(order_id),
    amount DECIMAL(10,2) NOT NULL CHECK (amount > 0),
    payment_method VARCHAR(20) NOT NULL 
        CHECK (payment_method IN ('Credit Card', 'Debit Card')),
    payment_status VARCHAR(20) NOT NULL DEFAULT 'Pending'
        CHECK (payment_status IN ('Pending', 'Completed', 'Failed', 'Refunded')),
    -- Security measures:
    masked_card_number CHAR(4) NOT NULL, -- Stores only last 4 digits
    payment_date DATETIME DEFAULT GETDATE(),
);