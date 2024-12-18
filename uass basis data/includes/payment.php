<?php
require_once 'config.php';
require_once 'functions.php';

class Payment {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    public function processPayment($bookingId, $paymentData) {
        try {
            $this->conn->begin_transaction();
            
            // Update booking payment status
            $query = "UPDATE bookings SET 
                     payment_status = ?,
                     payment_method = ?,
                     payment_date = NOW(),
                     total_paid = ?
                     WHERE id = ?";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ssdi", 
                $paymentData['status'],
                $paymentData['method'],
                $paymentData['amount'],
                $bookingId
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update booking payment status");
            }
            
            // Create payment record
            $query = "INSERT INTO payments (
                booking_id, 
                amount, 
                payment_method, 
                transaction_id, 
                payment_status,
                payment_date
            ) VALUES (?, ?, ?, ?, ?, NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("idsss",
                $bookingId,
                $paymentData['amount'],
                $paymentData['method'],
                $paymentData['transaction_id'],
                $paymentData['status']
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create payment record");
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
    
    public function getPaymentsByBooking($bookingId) {
        $query = "SELECT * FROM payments WHERE booking_id = ? ORDER BY payment_date DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
