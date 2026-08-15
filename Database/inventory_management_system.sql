-- =====================================================================
-- Inventory Management System - Redesigned Schema (3NF)
-- MySQL 8.0+
-- =====================================================================

CREATE DATABASE IF NOT EXISTS inventory_management_system;
USE inventory_management_system;
SHOW TABLES;
-- ===================== SECURITY MODULE =====================
-- 1. Create role
CREATE TABLE role (
    role_id     INT AUTO_INCREMENT PRIMARY KEY,
    role_name   VARCHAR(100) NOT NULL,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status      VARCHAR(20) DEFAULT 'Active'
);
SELECT *FROM role;

-- =====================================================================
-- Stored Procedures - Role Module
-- Pro_Views_Role, Pro_Insert_Role,
-- Pro_GetRoleById, Pro_Update_Role, Pro_Delete_Role
-- =====================================================================

USE inventory_management_system;

-- 1.1 Pro_Views_Role()

DELIMITER $$
CREATE PROCEDURE Pro_Views_Role()
BEGIN
    SELECT role_id, role_name, created_at, updated_at, status
    FROM role
    ORDER BY role_id;
END$$
DELIMITER;

CALL Pro_Views_Role();
            
-- 1.2 insert Role 
DELIMITER $$
CREATE PROCEDURE Pro_Insert_Role(
    IN p_role_name VARCHAR(100)
)
BEGIN
    INSERT INTO role (role_name, created_at, updated_at, status)
    VALUES (p_role_name, NOW(), NOW(), 'Active');
END$$
DELIMITER;

CALL Pro_Insert_Role("Na");
CALL Pro_Views_Role();
-- DROP PROCEDURE Pro_Insert_Role;

-- 1.3 getRoleById

DELIMITER $$
CREATE PROCEDURE Pro_GetRoleById(
    IN p_role_id INT
)
BEGIN
    SELECT role_id, role_name, created_at, updated_at, status
    FROM role
    WHERE role_id = p_role_id;
END$$
DELIMITER;
CALL Pro_GetRoleById(4);

-- 1.4) PROCEDURE Pro_Update_Role

DELIMITER $$
CREATE PROCEDURE Pro_Update_Role(
    IN p_role_id INT,
    IN p_role_name VARCHAR(100),
    IN p_status VARCHAR(20)
)
BEGIN
    UPDATE role
    SET role_name  = p_role_name,
        status     = p_status,
        updated_at = NOW()
    WHERE role_id = p_role_id;
END$$
DELIMITER;
CALL Pro_Update_Role(1,'lin','Inactive');
CALL Pro_Views_Role();

-- 1.5) Pro_Delete_Role
DELIMITER $$
CREATE PROCEDURE Pro_Delete_Role(
    IN p_role_id INT
)
BEGIN
    DELETE FROM role WHERE role_id = p_role_id;
END$$
DELIMITER ;

CALL Pro_Delete_Role(2);
CALL Pro_Views_Role();

-- CALL Pro_Insert_Role('Admin');
-- CALL Pro_Insert_Role('Staff');
-- CALL Pro_Views_Role();
-- CALL Pro_GetRoleById(1);
-- CALL Pro_Update_Role(1, 'Super Admin', 'Active');
-- CALL Pro_Delete_Role(2);

-- 2. Create permission

CREATE TABLE permission (
    permission_id   INT AUTO_INCREMENT PRIMARY KEY,
    permission_name VARCHAR(100) NOT NULL,
    module          VARCHAR(100),
    action          VARCHAR(50),
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status          VARCHAR(20) DEFAULT 'Active'
);
SELECT*FROM permission;

-- 3. CREATE permission_role
CREATE TABLE permission_role (
    permission_role_id INT AUTO_INCREMENT PRIMARY KEY,
    role_id             INT NOT NULL,
    permission_id       INT NOT NULL,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status              VARCHAR(20) DEFAULT 'Active',
    UNIQUE KEY uq_role_permission (role_id, permission_id),
    CONSTRAINT fk_permrole_role FOREIGN KEY (role_id) REFERENCES role(role_id),
    CONSTRAINT fk_permrole_permission FOREIGN KEY (permission_id) REFERENCES permission(permission_id)
);
SELECT*FROM permission_role;

-- ===================== EMPLOYEE MODULE =====================

-- 4. CREATE table position 
CREATE TABLE position (
    position_id   INT AUTO_INCREMENT PRIMARY KEY,
    position_name VARCHAR(100) NOT NULL,
    department    VARCHAR(100),
    description   VARCHAR(255),
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status        VARCHAR(20) DEFAULT 'Active'
);

SELECT*FROM position;

-- CREATE PROCEDURE Pro_GetAll_Permission()

DELIMITER $$
CREATE PROCEDURE Pro_GetAll_Permission()
BEGIN
    SELECT permission_id, permission_name, module, action, status
    FROM permission
    ORDER BY permission_id;
END$$
DELIMITER;

-- CREATE PROCEDURE Pro_GetPermission_ById()

DELIMITER $$
CREATE PROCEDURE Pro_GetPermission_ById(
    IN p_permission_id INT
)
BEGIN
    SELECT permission_id, permission_name, module, action, status
    FROM permission
    WHERE permission_id = p_permission_id;
END$$
DELIMITER;

CALL Pro_GetAll_User();
CALL Pro_GetUser_ByName('admin');

-- 5.Create table branch
CREATE TABLE branch (
    branch_id   INT AUTO_INCREMENT PRIMARY KEY,
    branch_name VARCHAR(100) NOT NULL,
    address     VARCHAR(255),
    phone       VARCHAR(20),
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status      VARCHAR(20) DEFAULT 'Active'
);
SELECT*FROM branch;

-- 6.Create table employee 
CREATE TABLE employee (
    employee_id INT AUTO_INCREMENT PRIMARY KEY,
    position_id INT,
    branch_id   INT,
    first_name  VARCHAR(100) NOT NULL,
    last_name   VARCHAR(100) NOT NULL,
    phone       VARCHAR(20),
    email       VARCHAR(100),
    hire_date   DATE,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status      VARCHAR(20) DEFAULT 'Active',
    CONSTRAINT fk_employee_position FOREIGN KEY (position_id) REFERENCES `position`(position_id),
    CONSTRAINT fk_employee_branch FOREIGN KEY (branch_id) REFERENCES branch(branch_id)
);
SELECT*FROM employee;

-- 7 CREATE TABLE USER
CREATE TABLE user (
    user_id     INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT UNIQUE,
    role_id     INT NOT NULL,
    username    VARCHAR(100) NOT NULL UNIQUE,
    password    VARCHAR(255) NOT NULL,
    email       VARCHAR(100),
    last_login  DATETIME,
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status      VARCHAR(20) DEFAULT 'Active',
    CONSTRAINT fk_user_employee FOREIGN KEY (employee_id) REFERENCES employee(employee_id),
    CONSTRAINT fk_user_role FOREIGN KEY (role_id) REFERENCES role(role_id)
);
SELECT*FROM user;

-- --------------------pro user--------------------
-- 1 Pro_Create_User
DELIMITER $$
CREATE PROCEDURE Pro_Create_User(
    IN p_employee_id INT,
    IN p_role_id INT,
    IN p_username VARCHAR(100),
    IN p_password VARCHAR(255),
    IN p_email VARCHAR(100)
)
BEGIN
    INSERT INTO `user` (employee_id, role_id, username, password, email, created_at, updated_at, status)
    VALUES (p_employee_id, p_role_id, p_username, p_password, p_email, NOW(), NOW(), 'Active');
END$$
DELIMITER;
SHOW PROCEDURE STATUS;
DROP PROCEDURE Pro_Create_User;
CALL Pro_GetAll_User();

SHOW CREATE PROCEDURE Pro_GetAll_User;



-- Pro_Update_User

DELIMITER $$
CREATE PROCEDURE Pro_Update_User(
    IN p_user_id  INT,
    IN p_username VARCHAR(100),
    IN p_email    VARCHAR(100),
    IN p_role_id  INT,
    IN p_status   VARCHAR(20)
)
BEGIN
    UPDATE `user`
    SET username   = p_username,
        email      = p_email,
        role_id    = p_role_id,
        status     = p_status,
        updated_at = NOW()
    WHERE user_id = p_user_id;
END$$
DELIMITER;

DROP PROCEDURE IF EXISTS Pro_Update_User;

-- Pro_Delete_User
DELIMITER $$
CREATE PROCEDURE Pro_Delete_User(
    IN p_user_id INT
)
BEGIN
    DELETE FROM `user` WHERE user_id = p_user_id;
END$$
DELIMITER;

SELECT * FROM employee;

-- Insert test employees if needed
INSERT INTO employee (position_id, branch_id, first_name, last_name, phone, email, hire_date, status)
VALUES 
    (1, 1, 'Sok', 'Dara', '012345678', 'sok.dara@company.com', CURDATE(), 'Active'),
    (2, 1, 'Chea', 'Virak', '012345679', 'chea.virak@company.com', CURDATE(), 'Active'),
    (3, 2, 'Meas', 'Sophea', '012345680', 'meas.sophea@company.com', CURDATE(), 'Active'),
    (1, 2, 'Kong', 'Sokha', '012345681', 'kong.sokha@company.com', CURDATE(), 'Active');

SELECT * FROM `position`;

INSERT INTO `position` (position_id, position_name, description, created_at, updated_at)
VALUES 
    (1, 'Manager', 'Manages team and operations', NOW(), NOW()),
    (2, 'Supervisor', 'Supervises daily activities', NOW(), NOW()),
    (3, 'Staff', 'Regular staff member', NOW(), NOW()),
    (4, 'Intern', 'Intern position', NOW(), NOW()),
    (5, 'Director', 'Director level position', NOW(), NOW());

SELECT * FROM branch;

-- Insert branches if they don't exist
INSERT INTO branch (branch_id, branch_name, address, created_at, updated_at)
VALUES 
    (4, 'Head Office', 'Phnom Penh', NOW(), NOW()),
    (2, 'Branch 1', 'Siem Reap', NOW(), NOW()),
    (3, 'Branch 2', 'Battambang', NOW(), NOW());

SELECT * FROM role;
INSERT INTO role (role_name, created_at, updated_at, status)
VALUES 
    ('admin', NOW(), NOW(), 'Active'),
    ('manager', NOW(), NOW(), 'Active'),
    ('staff', NOW(), NOW(), 'Active'),
    ('superadmin', NOW(), NOW(), 'Active');
SELECT * FROM `user`;

-- Pro_GetAll_User()
DELIMITER $$
CREATE PROCEDURE Pro_GetAll_User()
BEGIN
    SELECT u.user_id, u.username, u.email, u.employee_id,
           r.role_name, u.last_login, u.status, u.created_at
    FROM `user` u
    LEFT JOIN role r ON u.role_id = r.role_id
    ORDER BY u.user_id;
END$$
DELIMITER;

-- Pro_GetUser_ByName
DELIMITER $$
CREATE PROCEDURE Pro_GetUser_ByName(
    IN p_username VARCHAR(100)
)
BEGIN
    SELECT u.user_id, u.username, u.email, u.employee_id,
           r.role_name, u.status
    FROM `user` u
    LEFT JOIN role r ON u.role_id = r.role_id
    WHERE u.username LIKE CONCAT('%', p_username, '%');
END$$
DELIMITER;

-- Pro_GetUser_ById

DELIMITER $$
CREATE PROCEDURE Pro_GetUser_ById(
    IN p_user_id INT
)
BEGIN
    SELECT u.user_id, u.username, u.email, u.employee_id,
           r.role_name, u.last_login, u.status, u.created_at
    FROM `user` u
    LEFT JOIN role r ON u.role_id = r.role_id
    WHERE u.user_id = p_user_id;
END$$
DELIMITER;

-- ===================== PRODUCT MODULE =====================
-- 8 CREATE TABLE CATEGORY
CREATE TABLE category (
    category_id   INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL,
    description   VARCHAR(255),
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status        VARCHAR(20) DEFAULT 'Active'
);
SELECT*FROM category;

-- 9 create table brand 

CREATE TABLE brand (
    brand_id    INT AUTO_INCREMENT PRIMARY KEY,
    brand_name  VARCHAR(100) NOT NULL,
    description VARCHAR(255),
    created_at  DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status      VARCHAR(20) DEFAULT 'Active'
);

SELECT*FROM brand;

-- 10 Create table unit

CREATE TABLE unit (
    unit_id      INT AUTO_INCREMENT PRIMARY KEY,
    unit_name    VARCHAR(50) NOT NULL,
    abbreviation VARCHAR(10),
    created_at   DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at   DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status       VARCHAR(20) DEFAULT 'Active'
);
SELECT*FROM unit;

-- 11 Create Table product

CREATE TABLE product (
    product_id     INT AUTO_INCREMENT PRIMARY KEY,
    category_id    INT,
    brand_id       INT,
    unit_id        INT,
    product_code   VARCHAR(50) NOT NULL UNIQUE,
    product_name   VARCHAR(150) NOT NULL,
    description    VARCHAR(255),
    cost_price     DECIMAL(12,2),
    selling_price  DECIMAL(12,2),
    minimum_stock  INT DEFAULT 0,
    barcode        VARCHAR(50),
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status         VARCHAR(20) DEFAULT 'Active',
    CONSTRAINT fk_product_category FOREIGN KEY (category_id) REFERENCES category(category_id),
    CONSTRAINT fk_product_brand FOREIGN KEY (brand_id) REFERENCES brand(brand_id),
    CONSTRAINT fk_product_unit FOREIGN KEY (unit_id) REFERENCES unit(unit_id)
);
SELECT*FROM product;

-- ===================== SUPPLIER & PURCHASING MODULE =====================
-- 12 Create table supplier
CREATE TABLE supplier (
    supplier_id    INT AUTO_INCREMENT PRIMARY KEY,
    supplier_name  VARCHAR(150) NOT NULL,
    contact_person VARCHAR(100),
    phone          VARCHAR(20),
    email          VARCHAR(100),
    address        VARCHAR(255),
    payment_term   VARCHAR(50),
    created_at     DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status         VARCHAR(20) DEFAULT 'Active'
);
SELECT*FROM supplier;

-- CREATE PROCEDURE Pro_GetAll_Supplier()
DELIMITER $$
CREATE PROCEDURE Pro_GetAll_Supplier()
BEGIN
    SELECT supplier_id, supplier_name, contact_person, phone, email, status
    FROM supplier
    ORDER BY supplier_id;
END$$
DELIMITER;

-- 13 Create table purchaseorder
CREATE TABLE purchase_order (
    purchase_order_id INT AUTO_INCREMENT PRIMARY KEY,
    supplier_id       INT NOT NULL,
    branch_id         INT NOT NULL,
    employee_id       INT NOT NULL,
    po_number         VARCHAR(50) NOT NULL UNIQUE,
    order_date        DATE,
    expected_date     DATE,
    total_amount      DECIMAL(14,2),
    created_at        DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status            VARCHAR(20) DEFAULT 'Active',
    CONSTRAINT fk_po_supplier FOREIGN KEY (supplier_id) REFERENCES supplier(supplier_id),
    CONSTRAINT fk_po_branch FOREIGN KEY (branch_id) REFERENCES branch(branch_id),
    CONSTRAINT fk_po_employee FOREIGN KEY (employee_id) REFERENCES employee(employee_id)
);
SELECT*FROM purchase_order;

-- 14 Create table purchase_oder_detail

CREATE TABLE purchase_order_detail (
    purchase_order_detail_id INT AUTO_INCREMENT PRIMARY KEY,
    purchase_order_id        INT NOT NULL,
    product_id                INT NOT NULL,
    quantity                   INT NOT NULL,
    unit_cost                  DECIMAL(12,2) NOT NULL,
    subtotal                   DECIMAL(14,2),
    created_at                 DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at                 DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status                     VARCHAR(20) DEFAULT 'Active',
    CONSTRAINT fk_pod_po FOREIGN KEY (purchase_order_id) REFERENCES purchase_order(purchase_order_id),
    CONSTRAINT fk_pod_product FOREIGN KEY (product_id) REFERENCES product(product_id)
);
SELECT*FROM purchase_order_detail;

-- CREATE PROCEDURE Pro_GetPurchaseOrder_ById()
DELIMITER $$
CREATE PROCEDURE Pro_GetPurchaseOrder_ById(
    IN p_purchase_order_id INT
)
BEGIN
    SELECT po.purchase_order_id, po.po_number, po.order_date, po.expected_date,
           po.total_amount, po.status,
           s.supplier_name, b.branch_name,
           CONCAT(e.first_name, ' ', e.last_name) AS created_by
    FROM purchase_order po
    LEFT JOIN supplier s ON po.supplier_id = s.supplier_id
    LEFT JOIN branch b ON po.branch_id = b.branch_id
    LEFT JOIN employee e ON po.employee_id = e.employee_id
    WHERE po.purchase_order_id = p_purchase_order_id;
END$$
DELIMITER;
 
-- CREATE PROCEDURE Pro_Create_PurchaseOrder()

DELIMITER $$
CREATE PROCEDURE Pro_Create_PurchaseOrder(
    IN p_supplier_id   INT,
    IN p_branch_id     INT,
    IN p_employee_id   INT,
    IN p_po_number     VARCHAR(50),
    IN p_order_date    DATE,
    IN p_expected_date DATE
)
BEGIN
    INSERT INTO purchase_order
        (supplier_id, branch_id, employee_id, po_number, order_date, expected_date,
         total_amount, created_at, updated_at, status)
    VALUES
        (p_supplier_id, p_branch_id, p_employee_id, p_po_number, p_order_date, p_expected_date,
         0, NOW(), NOW(), 'Active');
 
    SELECT LAST_INSERT_ID() AS new_purchase_order_id;
END$$
DELIMITER ;
  
-- CREATE PROCEDURE Pro_CalculateTotalAmount()
DELIMITER $$
CREATE PROCEDURE Pro_CalculateTotalAmount(
    IN p_purchase_order_id INT
)
BEGIN
    UPDATE purchase_order po
    SET po.total_amount = (
            SELECT IFNULL(SUM(subtotal), 0)
            FROM purchase_order_detail
            WHERE purchase_order_id = p_purchase_order_id
        ),
        po.updated_at = NOW()
    WHERE po.purchase_order_id = p_purchase_order_id;
 
    SELECT total_amount FROM purchase_order WHERE purchase_order_id = p_purchase_order_id;
END$$
DELIMITER;
 
-- CREATE PROCEDURE Pro_AddItems_ToPO()

DELIMITER $$
CREATE PROCEDURE Pro_AddItems_ToPO(
    IN p_purchase_order_id INT,
    IN p_product_id        INT,
    IN p_quantity          INT,
    IN p_unit_cost         DECIMAL(12,2)
)
BEGIN
    INSERT INTO purchase_order_detail
        (purchase_order_id, product_id, quantity, unit_cost, subtotal, created_at, updated_at, status)
    VALUES
        (p_purchase_order_id, p_product_id, p_quantity, p_unit_cost,
         p_quantity * p_unit_cost, NOW(), NOW(), 'Active');
 
    CALL Pro_CalculateTotalAmount(p_purchase_order_id);
END$$
DELIMITER;

-- CREATE PROCEDURE Pro_RemoveItem_FromPO()
DELIMITER $$
CREATE PROCEDURE Pro_RemoveItem_FromPO(
    IN p_purchase_order_detail_id INT
)
BEGIN
    DECLARE v_po_id INT;
 
    SELECT purchase_order_id INTO v_po_id
    FROM purchase_order_detail
    WHERE purchase_order_detail_id = p_purchase_order_detail_id;
 
    DELETE FROM purchase_order_detail
    WHERE purchase_order_detail_id = p_purchase_order_detail_id;
 
    CALL Pro_CalculateTotalAmount(v_po_id);
END$$
DELIMITER;
 
-- CREATE PROCEDURE Pro_Update_PurchaseOrder()
DELIMITER $$
CREATE PROCEDURE Pro_Update_PurchaseOrder(
    IN p_purchase_order_id INT,
    IN p_supplier_id       INT,
    IN p_branch_id         INT,
    IN p_expected_date     DATE,
    IN p_status            VARCHAR(20)
)
BEGIN
    UPDATE purchase_order
    SET supplier_id   = p_supplier_id,
        branch_id     = p_branch_id,
        expected_date = p_expected_date,
        status        = p_status,
        updated_at    = NOW()
    WHERE purchase_order_id = p_purchase_order_id;
END$$
DELIMITER;
 
-- CREATE PROCEDURE Pro_Update_PurchaseOrder()

DELIMITER $$
CREATE PROCEDURE Pro_Update_PurchaseOrder(
    IN p_purchase_order_id INT
)
BEGIN
    SELECT d.purchase_order_detail_id, d.product_id, p.product_name,
           d.quantity, d.unit_cost, d.subtotal
    FROM purchase_order_detail d
    LEFT JOIN product p ON d.product_id = p.product_id
    WHERE d.purchase_order_id = p_purchase_order_id;
END$$
DELIMITER;

-- CREATE PROCEDURE Pro_Delete_PurchaseOrder()

DELIMITER $$
CREATE PROCEDURE Pro_Delete_PurchaseOrder(
    IN p_purchase_order_id INT
)
BEGIN
    DELETE FROM purchase_order_detail WHERE purchase_order_id = p_purchase_order_id;
    DELETE FROM purchase_order WHERE purchase_order_id = p_purchase_order_id;
END$$
DELIMITER;

-- CREATE PROCEDURE Pro_Search_PO()

DELIMITER $$
CREATE PROCEDURE Pro_Search_PO(
    IN p_keyword VARCHAR(150)
)
BEGIN
    SELECT po.purchase_order_id, po.po_number, po.order_date, po.total_amount, po.status,
           s.supplier_name
    FROM purchase_order po
    LEFT JOIN supplier s ON po.supplier_id = s.supplier_id
    WHERE po.po_number LIKE CONCAT('%', p_keyword, '%')
       OR s.supplier_name LIKE CONCAT('%', p_keyword, '%');
END$$
DELIMITER ;

DELETE FROM purchase_order WHERE po_number = 'PO-0001';
SELECT*FROM purchase_order;
SELECT*FROM supplier;

INSERT INTO supplier (supplier_name, contact_person, phone, email, status)
SELECT 'Water Supply Co.', 'V', '034222333', 'v@cocasupply.com', 'Active'
WHERE NOT EXISTS (SELECT 3 FROM supplier WHERE supplier_name = 'Water Supply Co.');

INSERT INTO category (category_name,description, status)
SELECT 'TOY', 'Kid car', 'Active'
WHERE NOT EXISTS (SELECT 2 FROM category WHERE category_name = 'TOY');
SELECT*FROM category;

INSERT INTO brand (brand_name,description,status)
SELECT 'ASUS','Model 2026','Active'
WHERE NOT EXISTS (SELECT 2 FROM brand WHERE brand_name = 'ASUS');

SELECT*FROM brand;

INSERT INTO unit (unit_name, abbreviation, status)
SELECT 'meter', 'm', 'Active'
WHERE NOT EXISTS (SELECT 3 FROM unit WHERE unit_name = 'meter');

SELECT*FROM unit;

INSERT INTO product (category_id, brand_id, unit_id, product_code, product_name, cost_price, selling_price, minimum_stock, status)
SELECT
    (SELECT category_id FROM category WHERE category_name = 'General' LIMIT 1),
    (SELECT brand_id FROM brand WHERE brand_name = 'Generic' LIMIT 1),
    (SELECT unit_id FROM unit WHERE unit_name = 'Piece' LIMIT 1),
    'PRD-0001', 'Sample Product', 2.00, 3.50, 10, 'Active'
WHERE NOT EXISTS (SELECT 1 FROM product WHERE product_code = 'PRD-0001');

SELECT*FROM product;


SELECT 'role' AS table_name, COUNT(*) AS row_count FROM role
UNION ALL SELECT 'position', COUNT(*) FROM position
UNION ALL SELECT 'branch', COUNT(*) FROM branch
UNION ALL SELECT 'employee', COUNT(*) FROM employee
UNION ALL SELECT 'supplier', COUNT(*) FROM supplier
UNION ALL SELECT 'category', COUNT(*) FROM category
UNION ALL SELECT 'brand', COUNT(*) FROM brand
UNION ALL SELECT 'unit', COUNT(*) FROM unit
UNION ALL SELECT 'product', COUNT(*) FROM product;


-- ===================== INVENTORY MODULE =====================

-- 15 Create table stock-in

CREATE TABLE stock_in (
    stock_in_id       INT AUTO_INCREMENT PRIMARY KEY,
    purchase_order_id INT,
    employee_id       INT NOT NULL,
    branch_id         INT NOT NULL,
    reference_no      VARCHAR(50),
    stock_date        DATE,
    note              VARCHAR(255),
    created_at        DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at        DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status            VARCHAR(20) DEFAULT 'Active',
    CONSTRAINT fk_stockin_po FOREIGN KEY (purchase_order_id) REFERENCES purchase_order(purchase_order_id),
    CONSTRAINT fk_stockin_employee FOREIGN KEY (employee_id) REFERENCES employee(employee_id),
    CONSTRAINT fk_stockin_branch FOREIGN KEY (branch_id) REFERENCES branch(branch_id)
);
SELECT*FROM stock_in;

-- 16 Create table stock-in_detail

CREATE TABLE stock_in_detail (
    stock_in_detail_id INT AUTO_INCREMENT PRIMARY KEY,
    stock_in_id        INT NOT NULL,
    product_id         INT NOT NULL,
    quantity            INT NOT NULL,
    unit_cost           DECIMAL(12,2),
    expiry_date         DATE,
    created_at          DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status              VARCHAR(20) DEFAULT 'Active',
    CONSTRAINT fk_stockindetail_stockin FOREIGN KEY (stock_in_id) REFERENCES stock_in(stock_in_id),
    CONSTRAINT fk_stockindetail_product FOREIGN KEY (product_id) REFERENCES product(product_id)
);
SELECT*FROM stock_in_detail;

-- CREATE PROCEDURE Pro_GetAll_StockIn()
DELIMITER $$
CREATE PROCEDURE Pro_GetAll_StockIn()
BEGIN
    SELECT s.stock_in_id, s.reference_no, s.stock_date, s.note,
           b.branch_name, CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
           s.status
    FROM stock_in s
    LEFT JOIN branch b ON s.branch_id = b.branch_id
    LEFT JOIN employee e ON s.employee_id = e.employee_id
    ORDER BY s.stock_in_id DESC;
END$$
DELIMITER;

-- CREATE PROCEDURE Pro_GetStockIn_ById()
DELIMITER $$
CREATE PROCEDURE Pro_GetStockIn_ById(
    IN p_stock_in_id INT
)
BEGIN
    SELECT s.stock_in_id, s.reference_no, s.stock_date, s.note,
           b.branch_name, CONCAT(e.first_name, ' ', e.last_name) AS employee_name,
           s.status
    FROM stock_in s
    LEFT JOIN branch b ON s.branch_id = b.branch_id
    LEFT JOIN employee e ON s.employee_id = e.employee_id
    WHERE s.stock_in_id = p_stock_in_id;
 
    SELECT d.stock_in_detail_id, d.product_id, p.product_name,
           d.quantity, d.unit_cost, d.expiry_date
    FROM stock_in_detail d
    LEFT JOIN product p ON d.product_id = p.product_id
    WHERE d.stock_in_id = p_stock_in_id;
END$$
DELIMITER;

-- 17 Create table stock_out

CREATE TABLE stock_out (
    stock_out_id  INT AUTO_INCREMENT PRIMARY KEY,
    employee_id   INT NOT NULL,
    branch_id     INT NOT NULL,
    reference_no  VARCHAR(50),
    stock_date    DATE,
    purpose       VARCHAR(150),
    created_at    DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status        VARCHAR(20) DEFAULT 'Active',
    CONSTRAINT fk_stockout_employee FOREIGN KEY (employee_id) REFERENCES employee(employee_id),
    CONSTRAINT fk_stockout_branch FOREIGN KEY (branch_id) REFERENCES branch(branch_id)
);

SELECT*FROM stock_out;

-- 18 Create table stockout detail
CREATE TABLE stock_out_detail (
    stock_out_detail_id INT AUTO_INCREMENT PRIMARY KEY,
    stock_out_id         INT NOT NULL,
    product_id            INT NOT NULL,
    quantity               INT NOT NULL,
    selling_price          DECIMAL(12,2),
    created_at             DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at             DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status                 VARCHAR(20) DEFAULT 'Active',
    CONSTRAINT fk_stockoutdetail_stockout FOREIGN KEY (stock_out_id) REFERENCES stock_out(stock_out_id),
    CONSTRAINT fk_stockoutdetail_product FOREIGN KEY (product_id) REFERENCES product(product_id)
);

SELECT*FROM stock_out_detail;

-- 19 Create table stock adjustment
CREATE TABLE stock_adjustment (
    adjustment_id   INT AUTO_INCREMENT PRIMARY KEY,
    employee_id     INT NOT NULL,
    branch_id       INT NOT NULL,
    reason          VARCHAR(255),
    adjustment_date DATE,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status          VARCHAR(20) DEFAULT 'Active',
    CONSTRAINT fk_adjustment_employee FOREIGN KEY (employee_id) REFERENCES employee(employee_id),
    CONSTRAINT fk_adjustment_branch FOREIGN KEY (branch_id) REFERENCES branch(branch_id)
);
SELECT*FROM stock_adjustment;

-- 20 Create table stock adjustment detail

CREATE TABLE stock_adjustment_detail (
    adjustment_detail_id INT AUTO_INCREMENT PRIMARY KEY,
    adjustment_id         INT NOT NULL,
    product_id             INT NOT NULL,
    old_quantity            INT,
    new_quantity            INT,
    difference              INT,
    created_at              DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at              DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status                  VARCHAR(20) DEFAULT 'Active',
    CONSTRAINT fk_adjdetail_adjustment FOREIGN KEY (adjustment_id) REFERENCES stock_adjustment(adjustment_id),
    CONSTRAINT fk_adjdetail_product FOREIGN KEY (product_id) REFERENCES product(product_id)
);
SELECT*FROM stock_adjustment_detail;

-- 21 Create table stock transfer
CREATE TABLE stock_transfer (
    transfer_id     INT AUTO_INCREMENT PRIMARY KEY,
    product_id      INT NOT NULL,
    from_branch_id  INT NOT NULL,
    to_branch_id    INT NOT NULL,
    employee_id     INT NOT NULL,
    quantity        INT NOT NULL,
    transfer_date   DATE,
    created_at      DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status          VARCHAR(20) DEFAULT 'Active',
    CONSTRAINT fk_transfer_product FOREIGN KEY (product_id) REFERENCES product(product_id),
    CONSTRAINT fk_transfer_frombranch FOREIGN KEY (from_branch_id) REFERENCES branch(branch_id),
    CONSTRAINT fk_transfer_tobranch FOREIGN KEY (to_branch_id) REFERENCES branch(branch_id),
    CONSTRAINT fk_transfer_employee FOREIGN KEY (employee_id) REFERENCES employee(employee_id)
);
SELECT*FROM stock_transfer;

-- ===================== AUDIT MODULE =====================
-- 22 Create table activity_log
CREATE TABLE activity_log (
    log_id     INT AUTO_INCREMENT PRIMARY KEY,
    user_id    INT NOT NULL,
    action     VARCHAR(50),
    module     VARCHAR(100),
    record_id  INT,
    old_value  VARCHAR(255),
    new_value  VARCHAR(255),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    status     VARCHAR(20) DEFAULT 'Active',
    CONSTRAINT fk_log_user FOREIGN KEY (user_id) REFERENCES user(user_id)
);
SELECT * FROM activity_log;
USE inventory_management_system; 
SELECT * FROM product;
INSERT INTO product(product_id,category_id,brand_id,unit_id,product_code,product_name,description,cost_price,selling_price,minimum_stock,barcode)VALUES();
INSERT INTO product (
    product_id,
    category_id,
    brand_id,
    unit_id,
    product_code,
    product_name,
    description,
    cost_price,
    selling_price,
    minimum_stock,
    barcode
) VALUES (
    1,
    10,
    5,
    1,
    'PRD-1001',
    'Wireless Ergonomic Mouse',
    '2.4GHz wireless mouse with adjustable DPI and rechargeable battery.',
    15.50,
    29.99,
    10,
    '0123456789012'
);

SELECT * FROM  category;
INSERT INTO category(category_name,description)VALUE('beverage','good quality');
INSERT INTO category(category_name,description) VALUES ('electronic','good quality')
,('Snack','Fexible'),('skincare','Pretties');

SELECT * FROM unit;
INSERT INTO category(category_name) VALUE('beauty');

SELECT * FROM category;

SELECT * FROM brand;
INSERT INTO brand(brand_name) VALUES('Essence'),('Glamour Beauty'),('Velvet Touch'),('Nail Couture');

SELECT * FROM unit;
INSERT INTO unit (unit_name, abbreviation) 
VALUES 
    ('Gram', 'g'),
    ('Piece', 'pcs'),
    ('Liter', 'L'),
    ('Box', 'box');


SELECT * FROM product;
INSERT INTO product(category_id,brand_id,unit_id,product_code,product_name,cost_price,selling_price,minimum_stock,barcode)
VALUE(6,1,4,'LMSCR002','Essence Mascara Lash Princess',10.20,11.0,200,'112345678');

SELECT * FROM product WHERE product_id=2;
UPDATE product set category_id=6,brand_id=1,unit_id=4,product_name='Eyeshadow Palette with Mirror'
,cost_price=19.99,selling_price=21.0,minimum_stock=100,barcode='9170275171413';

SELECT * FROM category WHERE category_id=1;
INSERT INTO category (category_name) VALUE('cake');

SELECT * FROM brand WHERE brand_id=1;

SELECT * FROM unit WHERE unit_id=1;
UPDATE unit set unit_name=?,abbreviation=? WHERE unit_id;
USE inventory_management_system;
SELECT * FROM product;
SELECT * FROM category;
INSERT INTO product(category_id,brand_id,unit_id,product_id,product_name,description,cost_price,selling_price,minimum_stock,barcode)
VALUES(?,?,?,?,?,?,?,?,?,?);
SELECT * FROM product;
SELECT * FROM category;
SELECT * FROM unit;
SELECT * FROM brand;
INSERT INTO category(category_name,description)VALUE();
SELECT * FROM user;
ALTER TABLE user DROP FOREIGN KEY fk_user_role;
ALTER TABLE user DROP FOREIGN KEY fk_user_role;

ALTER TABLE user 
ADD CONSTRAINT fk_user_role 
FOREIGN KEY (role_id) REFERENCES role (role_id) 
ON DELETE CASCADE;

SELECT * FROM permission;
ALTER TABLE brand ADD COLUMN website VARCHAR(255) NULL;
ALTER TABLE brand ADD COLUMN origin VARCHAR(100) NULL;
SELECT * FROM product;
ALTER TABLE product ADD COLUMN stock INT DEFAULT 0;
UPDATE product set stock=100 WHERE product_id IN (4,7,8,10);
SELECT * FROM purchase_order;
SELECT * FROM supplier;
INSERT INTO supplier(supplier_name,contact_person,phone,email,address)VALUES
("Lim Sovann","Chea soma","0886789034","sovann@gmail.com","PP");
INSERT INTO purchase_order (supplier_id, branch_id, employee_id, po_number, order_date, expected_date, total_amount, created_at, updated_at) 
VALUES (1, 1, 1, 'PO-2026-001', '2026-08-15', '2026-08-25', 1250.00, NOW(), NOW());
SELECT * FROM stock_transfer;
INSERT INTO stock_transfer (
    product_id, 
    from_branch_id, 
    to_branch_id, 
    employee_id, 
    quantity, 
    transfer_date, 
    status
) VALUES (
    1,                  -- product_id (សូមដូរតាម product_id ដែលមានក្នុងតារាង products របស់អ្នក)
    1,                  -- from_branch_id (ប្រើ 1 ដែលមានស្រាប់)
    3,                  -- to_branch_id (ប្រើ 3 ដែលមានស្រាប់)
    1,                  -- employee_id
    50,                 -- quantity
    '2026-08-15',       -- transfer_date
    'Completed'         -- status
);
SELECT * FROM product;
SELECT * FROM branch;
INSERT INTO branch ( branch_name) VALUES ('Main Branch'), ('Secondary Branch');
INSERT INTO stock_transfer (
    product_id, 
    from_branch_id, 
    to_branch_id, 
    employee_id, 
    quantity, 
    transfer_date, 
    status
) VALUES (
    4,                  -- product_id (ប្រើលេខ 4 ដែលមានស្រាប់: Eyeshadow Palette with Mirror)
    1,                  -- from_branch_id (ប្រើលេខ 1 ដែលមានស្រាប់ក្នុងតារាង branch)
    3,                  -- to_branch_id (ប្រើលេខ 3 ដែលមានស្រាប់ក្នុងតារាង branch)
    1,                  -- employee_id
    50,                 -- quantity
    '2026-08-15',       -- transfer_date
    'Completed'         -- status
);
SELECT * FROM activity_log;
SELECT * FROM user;
INSERT INTO activity_log (
    user_id, 
    action, 
    module, 
    record_id, 
    old_value, 
    new_value, 
    status
) VALUES (
    5,                  -- user_id (លេខ ID របស់បុគ្គលិក ឬអ្នកប្រើប្រាស់)
    'CREATE',           -- action (សកម្មភាព ឧទាហរណ៍: CREATE, UPDATE, DELETE)
    'Product Module',   -- module (ផ្នែកដែលបានធ្វើសកម្មភាព)
    4,                  -- record_id (ID នៃទិន្នន័យដែលបានប៉ះពាល់)
    NULL,               -- old_value (តម្លៃចាស់ ក្នុងករណីបង្កើតថ្មីអាចដាក់ NULL)
    'Added new product',-- new_value (តម្លៃថ្មី)
    'SUCCESS'           -- status (ស្ថានភាព: SUCCESS ឬ FAILURE)
);

SELECT * FROM purchase_order;
SELECT * FROM purchase_order_detail;
