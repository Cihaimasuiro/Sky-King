<?php
require_once __DIR__ . '/../includes/config.php';

class Customer {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    public function getCustomerById($id) {
        $query = "SELECT * FROM customer WHERE id_customer = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function createCustomer($data) {
        $query = "INSERT INTO customer (
            first_name, 
            last_name, 
            email, 
            phone, 
            address, 
            nationality,
            date_of_birth,
            passport_number,
            username,
            password,
            created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "ssssssssss",
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $data['nationality'],
            $data['date_of_birth'],
            $data['passport_number'],
            $data['username'],
            $hashedPassword
        );
        return $stmt->execute();
    }
    
    public function updateCustomer($id, $data) {
        $query = "UPDATE customer SET 
                  first_name = ?,
                  last_name = ?,
                  email = ?,
                  phone = ?,
                  address = ?,
                  nationality = ?,
                  date_of_birth = ?,
                  passport_number = ?
                  WHERE id_customer = ?";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "ssssssssi",
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['phone'],
            $data['address'],
            $data['nationality'],
            $data['date_of_birth'],
            $data['passport_number'],
            $id
        );
        return $stmt->execute();
    }
    
    public function updatePassword($id, $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $query = "UPDATE customer SET password = ? WHERE id_customer = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $hashedPassword, $id);
        return $stmt->execute();
    }
    
    public function deleteCustomer($id) {
        $query = "DELETE FROM customer WHERE id_customer = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    public function getAllCustomers() {
        $query = "SELECT id_customer, first_name, last_name, email, phone, nationality 
                 FROM customer 
                 ORDER BY first_name, last_name";
        $result = $this->conn->query($query);
        $customers = [];
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
        return $customers;
    }
    
    public function searchCustomers($search) {
        $search = "%$search%";
        $query = "SELECT id_customer, first_name, last_name, email, phone, nationality 
                 FROM customer 
                 WHERE first_name LIKE ? 
                 OR last_name LIKE ? 
                 OR email LIKE ? 
                 OR phone LIKE ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssss", $search, $search, $search, $search);
        $stmt->execute();
        $result = $stmt->get_result();
        $customers = [];
        while ($row = $result->fetch_assoc()) {
            $customers[] = $row;
        }
        return $customers;
    }
    
    public function validateLogin($username, $password) {
        $query = "SELECT id_customer, password FROM customer WHERE username = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result && password_verify($password, $result['password'])) {
            return $result['id_customer'];
        }
        return false;
    }
}
