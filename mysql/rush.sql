CREATE DATABASE IF NOT EXISTS rush00;

CREATE TABLE rush00.users
(
    user_id INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    username VARCHAR(255),
    passwd VARCHAR(255),
    fullname VARCHAR(255)
);

CREATE TABLE rush00.products
(
    product_id  INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    product_name VARCHAR(255),
    price DECIMAL(15, 2)
)