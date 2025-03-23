<?php
    $host = '127.0.0.1';    
    $user = 'root';
    $password = '';
    $db = 'db_Cinema';
    $con = mysqli_connect($host, $user, $password, $db);

    if(!$con){
        die(mysqli_error());
    }
?>
<!-- -- Create the database
CREATE DATABASE IF NOT EXISTS db_Cinema;
USE db_Cinema;


-- Create tbl_admin
CREATE TABLE tbl_admin (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    admin_firstname VARCHAR(50) NOT NULL,
    admin_lastname VARCHAR(50) NOT NULL,
    admin_email VARCHAR(100) NOT NULL UNIQUE,
    admin_password VARCHAR(255) NOT NULL,
    verification_status ENUM('verified', 'unverified') DEFAULT 'unverified',
    status ENUM('active', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create tbl_foodpartner
CREATE TABLE tbl_foodpartner (
    partner_id INT PRIMARY KEY AUTO_INCREMENT,
    partner_firstname VARCHAR(100) NOT NULL,
    partner_lastname VARCHAR(100) NOT NULL,
    partner_email VARCHAR(100) NOT NULL UNIQUE,
    partner_password VARCHAR(255) NOT NULL,
    partner_address TEXT NOT NULL,
    business_name VARCHAR(100) NOT NULL,
    dti_permit VARCHAR(50) NOT NULL,
    mayor_permit VARCHAR(50) NOT NULL,
    sanitary_permit VARCHAR(50) NOT NULL,
    verification_status ENUM('verified', 'unverified') DEFAULT 'unverified',
    status ENUM('active', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create tbl_user
CREATE TABLE tbl_user (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    user_firstname VARCHAR(50) NOT NULL,
    user_lastname VARCHAR(50) NOT NULL,
    user_email VARCHAR(100) NOT NULL UNIQUE,
    user_password VARCHAR(255) NOT NULL,
    user_contact_number VARCHAR(20),
    user_address TEXT,
    verification_status ENUM('verified', 'unverified') DEFAULT 'unverified',
    status ENUM('active', 'suspended') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); -->