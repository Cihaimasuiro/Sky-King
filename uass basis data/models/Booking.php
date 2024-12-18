<?php
require_once __DIR__ . '/../includes/config.php';

class Booking {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    public function getBookingById($id) {
        $query = "SELECT b.*, 
                         c.first_name, c.last_name, c.email,
                         p.nomor_penerbangan, p.waktu_berangkat, p.waktu_tiba,
                         m.nama_maskapai,
                         b1.nama_bandara as bandara_asal, b1.kota as kota_asal,
                         b2.nama_bandara as bandara_tujuan, b2.kota as kota_tujuan
                  FROM pemesanan b
                  JOIN customer c ON b.id_customer = c.id_customer
                  JOIN penerbangan p ON b.id_penerbangan = p.id_penerbangan
                  JOIN maskapai m ON p.id_maskapai = m.id_maskapai
                  JOIN bandara b1 ON p.id_bandara_asal = b1.id_bandara
                  JOIN bandara b2 ON p.id_bandara_tujuan = b2.id_bandara
                  WHERE b.id_pemesanan = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function createBooking($data) {
        try {
            $this->conn->begin_transaction();
            
            // Check flight availability
            $query = "SELECT kursi_tersedia, harga FROM penerbangan WHERE id_penerbangan = ? FOR UPDATE";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $data['id_penerbangan']);
            $stmt->execute();
            $flight = $stmt->get_result()->fetch_assoc();
            
            if (!$flight || $flight['kursi_tersedia'] < $data['jumlah_penumpang']) {
                throw new Exception("Not enough seats available");
            }
            
            // Calculate total price
            $total_harga = $flight['harga'] * $data['jumlah_penumpang'];
            
            // Create booking
            $query = "INSERT INTO pemesanan (
                id_customer,
                id_penerbangan,
                tanggal_pemesanan,
                jumlah_penumpang,
                kelas_penerbangan,
                total_harga,
                status_pemesanan,
                created_at
            ) VALUES (?, ?, NOW(), ?, ?, ?, 'pending', NOW())";
            
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param(
                "iiids",
                $data['id_customer'],
                $data['id_penerbangan'],
                $data['jumlah_penumpang'],
                $total_harga,
                $data['kelas_penerbangan']
            );
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to create booking");
            }
            
            $booking_id = $this->conn->insert_id;
            
            // Update available seats
            $query = "UPDATE penerbangan 
                     SET kursi_tersedia = kursi_tersedia - ? 
                     WHERE id_penerbangan = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $data['jumlah_penumpang'], $data['id_penerbangan']);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update seat availability");
            }
            
            $this->conn->commit();
            return $booking_id;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
    
    public function updateBookingStatus($id, $status) {
        $query = "UPDATE pemesanan SET status_pemesanan = ? WHERE id_pemesanan = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }
    
    public function cancelBooking($id) {
        try {
            $this->conn->begin_transaction();
            
            // Get booking details
            $booking = $this->getBookingById($id);
            if (!$booking) {
                throw new Exception("Booking not found");
            }
            
            // Only allow cancellation of pending or confirmed bookings
            if (!in_array($booking['status_pemesanan'], ['pending', 'confirmed'])) {
                throw new Exception("Cannot cancel booking with status: " . $booking['status_pemesanan']);
            }
            
            // Update booking status
            $query = "UPDATE pemesanan SET status_pemesanan = 'cancelled' WHERE id_pemesanan = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $id);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update booking status");
            }
            
            // Return seats to flight
            $query = "UPDATE penerbangan 
                     SET kursi_tersedia = kursi_tersedia + ? 
                     WHERE id_penerbangan = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $booking['jumlah_penumpang'], $booking['id_penerbangan']);
            
            if (!$stmt->execute()) {
                throw new Exception("Failed to update seat availability");
            }
            
            $this->conn->commit();
            return true;
            
        } catch (Exception $e) {
            $this->conn->rollback();
            throw $e;
        }
    }
    
    public function getCustomerBookings($customerId) {
        $query = "SELECT b.*, 
                         p.nomor_penerbangan, p.waktu_berangkat, p.waktu_tiba,
                         m.nama_maskapai,
                         b1.nama_bandara as bandara_asal, b1.kota as kota_asal,
                         b2.nama_bandara as bandara_tujuan, b2.kota as kota_tujuan,
                         py.status as status_pembayaran
                  FROM pemesanan b
                  JOIN penerbangan p ON b.id_penerbangan = p.id_penerbangan
                  JOIN maskapai m ON p.id_maskapai = m.id_maskapai
                  JOIN bandara b1 ON p.id_bandara_asal = b1.id_bandara
                  JOIN bandara b2 ON p.id_bandara_tujuan = b2.id_bandara
                  LEFT JOIN pembayaran py ON b.id_pemesanan = py.id_pemesanan
                  WHERE b.id_customer = ?
                  ORDER BY b.tanggal_pemesanan DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        
        $bookings = [];
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        return $bookings;
    }
    
    public function getRecentBookings($limit = 10) {
        $query = "SELECT b.*, 
                         c.first_name, c.last_name,
                         p.nomor_penerbangan,
                         m.nama_maskapai,
                         py.status as status_pembayaran
                  FROM pemesanan b
                  JOIN customer c ON b.id_customer = c.id_customer
                  JOIN penerbangan p ON b.id_penerbangan = p.id_penerbangan
                  JOIN maskapai m ON p.id_maskapai = m.id_maskapai
                  LEFT JOIN pembayaran py ON b.id_pemesanan = py.id_pemesanan
                  ORDER BY b.tanggal_pemesanan DESC
                  LIMIT ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        
        $bookings = [];
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        return $bookings;
    }
    
    public function searchBookings($criteria) {
        $query = "SELECT b.*, 
                         c.first_name, c.last_name,
                         p.nomor_penerbangan,
                         m.nama_maskapai
                  FROM pemesanan b
                  JOIN customer c ON b.id_customer = c.id_customer
                  JOIN penerbangan p ON b.id_penerbangan = p.id_penerbangan
                  JOIN maskapai m ON p.id_maskapai = m.id_maskapai
                  WHERE 1=1";
        $params = [];
        $types = "";
        
        if (!empty($criteria['status'])) {
            $query .= " AND b.status_pemesanan = ?";
            $params[] = $criteria['status'];
            $types .= "s";
        }
        
        if (!empty($criteria['date_from'])) {
            $query .= " AND b.tanggal_pemesanan >= ?";
            $params[] = $criteria['date_from'];
            $types .= "s";
        }
        
        if (!empty($criteria['date_to'])) {
            $query .= " AND b.tanggal_pemesanan <= ?";
            $params[] = $criteria['date_to'];
            $types .= "s";
        }
        
        if (!empty($criteria['customer_name'])) {
            $search = "%" . $criteria['customer_name'] . "%";
            $query .= " AND (c.first_name LIKE ? OR c.last_name LIKE ?)";
            $params[] = $search;
            $params[] = $search;
            $types .= "ss";
        }
        
        $query .= " ORDER BY b.tanggal_pemesanan DESC";
        
        $stmt = $this->conn->prepare($query);
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        
        $bookings = [];
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $bookings[] = $row;
        }
        return $bookings;
    }
}
