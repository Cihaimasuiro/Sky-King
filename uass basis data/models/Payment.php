<?php
require_once __DIR__ . '/../includes/config.php';

class Payment {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    public function getPaymentById($id) {
        $query = "SELECT p.*, 
                         b.total_harga,
                         c.first_name, c.last_name, c.email
                  FROM pembayaran p
                  JOIN pemesanan b ON p.id_pemesanan = b.id_pemesanan
                  JOIN customer c ON b.id_customer = c.id_customer
                  WHERE p.id_pembayaran = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function createPayment($data) {
        try {
            $this->conn->begin_transaction();
            
            // Get booking details
            $query = "SELECT * FROM pemesanan WHERE id_pemesanan = ? FOR UPDATE";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $data['id_pemesanan']);
            $stmt->execute();
            $booking = $stmt->get_result()->fetch_assoc();
            
            if (!$booking) {
                throw new Exception("Booking not found");
            }
            
            if ($booking['status_pemesanan'] === 'cancelled') {
                throw new Exception("Cannot process payment for cancelled booking");
            }
            
            // Create payment record
            $query = "INSERT INTO pembayaran (
                id_pemesanan,
                jumlah_pembayaran,
                metode_pembayaran,
                tanggal_pembayaran,
                status,
                kode_transaksi,
                created_at
            ) VALUES (?, ?, ?, NOW(), ?, ?, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                "idsss",
                $data['id_pemesanan'],
                $data['jumlah_pembayaran'],
                $data['metode_pembayaran'],
                $data['status'],
                $data['kode_transaksi']
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create payment record");
            }
            
            $payment_id = $this->conn->insert_id;
            
            // Update booking status if payment is successful
            if ($data['status'] === 'completed') {
                $query = "UPDATE pemesanan 
                         SET status_pemesanan = 'confirmed' 
                         WHERE id_pemesanan = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("i", $data['id_pemesanan']);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update booking status");
                }
            }
            
            $this->conn->commit();
            return $payment_id;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
    
    public function updatePaymentStatus($id, $status) {
        try {
            $this->conn->begin_transaction();
            
            // Update payment status
            $query = "UPDATE pembayaran SET status = ? WHERE id_pembayaran = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("si", $status, $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update payment status");
            }
            
            // If payment is completed, update booking status
            if ($status === 'completed') {
                $query = "UPDATE pemesanan b 
                         JOIN pembayaran p ON b.id_pemesanan = p.id_pemesanan
                         SET b.status_pemesanan = 'confirmed'
                         WHERE p.id_pembayaran = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("i", $id);
                
                if (!$stmt->execute()) {
                    throw new Exception("Failed to update booking status");
                }
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
    
    public function getPaymentsByBooking($bookingId) {
        $query = "SELECT * FROM pembayaran 
                 WHERE id_pemesanan = ? 
                 ORDER BY tanggal_pembayaran DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        
        $payments = [];
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
        return $payments;
    }
    
    public function getRecentPayments($limit = 10) {
        $query = "SELECT p.*, 
                         b.total_harga,
                         c.first_name, c.last_name
                  FROM pembayaran p
                  JOIN pemesanan b ON p.id_pemesanan = b.id_pemesanan
                  JOIN customer c ON b.id_customer = c.id_customer
                  ORDER BY p.tanggal_pembayaran DESC
                  LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        
        $payments = [];
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
        return $payments;
    }
    
    public function searchPayments($criteria) {
        $query = "SELECT p.*, 
                         b.total_harga,
                         c.first_name, c.last_name
                  FROM pembayaran p
                  JOIN pemesanan b ON p.id_pemesanan = b.id_pemesanan
                  JOIN customer c ON b.id_customer = c.id_customer
                  WHERE 1=1";
        $params = [];
        $types = "";
        
        if (!empty($criteria['status'])) {
            $query .= " AND p.status = ?";
            $params[] = $criteria['status'];
            $types .= "s";
        }
        
        if (!empty($criteria['date_from'])) {
            $query .= " AND p.tanggal_pembayaran >= ?";
            $params[] = $criteria['date_from'];
            $types .= "s";
        }
        
        if (!empty($criteria['date_to'])) {
            $query .= " AND p.tanggal_pembayaran <= ?";
            $params[] = $criteria['date_to'];
            $types .= "s";
        }
        
        if (!empty($criteria['payment_method'])) {
            $query .= " AND p.metode_pembayaran = ?";
            $params[] = $criteria['payment_method'];
            $types .= "s";
        }
        
        $query .= " ORDER BY p.tanggal_pembayaran DESC";
        
        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        
        $payments = [];
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $payments[] = $row;
        }
        return $payments;
    }
    
    public function generateTransactionCode() {
        return 'TRX' . date('YmdHis') . rand(1000, 9999);
    }
}
