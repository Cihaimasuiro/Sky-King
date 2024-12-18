<?php
require_once __DIR__ . '/../includes/config.php';

class Flight {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    public function getFlightById($id) {
        $query = "SELECT p.*, 
                         m.nama_maskapai, m.logo,
                         b1.nama_bandara as bandara_asal, b1.kota as kota_asal, b1.kode as kode_asal,
                         b2.nama_bandara as bandara_tujuan, b2.kota as kota_tujuan, b2.kode as kode_tujuan
                  FROM penerbangan p
                  JOIN maskapai m ON p.id_maskapai = m.id_maskapai
                  JOIN bandara b1 ON p.id_bandara_asal = b1.id_bandara
                  JOIN bandara b2 ON p.id_bandara_tujuan = b2.id_bandara
                  WHERE p.id_penerbangan = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function createFlight($data) {
        $query = "INSERT INTO penerbangan (
            nomor_penerbangan,
            id_maskapai,
            id_bandara_asal,
            id_bandara_tujuan,
            waktu_berangkat,
            waktu_tiba,
            harga,
            status,
            kursi_tersedia,
            kelas
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "siiisstsis",
            $data['nomor_penerbangan'],
            $data['id_maskapai'],
            $data['id_bandara_asal'],
            $data['id_bandara_tujuan'],
            $data['waktu_berangkat'],
            $data['waktu_tiba'],
            $data['harga'],
            $data['status'],
            $data['kursi_tersedia'],
            $data['kelas']
        );
        return $stmt->execute();
    }
    
    public function updateFlight($id, $data) {
        $query = "UPDATE penerbangan SET 
                  nomor_penerbangan = ?,
                  id_maskapai = ?,
                  id_bandara_asal = ?,
                  id_bandara_tujuan = ?,
                  waktu_berangkat = ?,
                  waktu_tiba = ?,
                  harga = ?,
                  status = ?,
                  kursi_tersedia = ?,
                  kelas = ?
                  WHERE id_penerbangan = ?";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "siiisstsis",
            $data['nomor_penerbangan'],
            $data['id_maskapai'],
            $data['id_bandara_asal'],
            $data['id_bandara_tujuan'],
            $data['waktu_berangkat'],
            $data['waktu_tiba'],
            $data['harga'],
            $data['status'],
            $data['kursi_tersedia'],
            $data['kelas'],
            $id
        );
        return $stmt->execute();
    }
    
    public function deleteFlight($id) {
        // Check if flight has any bookings
        $query = "SELECT COUNT(*) as count FROM pemesanan WHERE id_penerbangan = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
            throw new Exception("Cannot delete flight: It has existing bookings");
        }
        
        $query = "DELETE FROM penerbangan WHERE id_penerbangan = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    public function searchFlights($criteria) {
        $query = "SELECT p.*, 
                         m.nama_maskapai, m.logo,
                         b1.nama_bandara as bandara_asal, b1.kota as kota_asal, b1.kode as kode_asal,
                         b2.nama_bandara as bandara_tujuan, b2.kota as kota_tujuan, b2.kode as kode_tujuan
                  FROM penerbangan p
                  JOIN maskapai m ON p.id_maskapai = m.id_maskapai
                  JOIN bandara b1 ON p.id_bandara_asal = b1.id_bandara
                  JOIN bandara b2 ON p.id_bandara_tujuan = b2.id_bandara
                  WHERE p.id_bandara_asal = ? 
                  AND p.id_bandara_tujuan = ?
                  AND DATE(p.waktu_berangkat) = ?
                  AND p.status = 'On Time'
                  AND p.kursi_tersedia >= ?";
                  
        if (isset($criteria['kelas']) && $criteria['kelas']) {
            $query .= " AND p.kelas = ?";
        }
        
        $query .= " ORDER BY p.harga ASC";
        
        $stmt = $this->conn->prepare($query);
        
        if (isset($criteria['kelas']) && $criteria['kelas']) {
            $stmt->bind_param("iisis", 
                $criteria['id_bandara_asal'],
                $criteria['id_bandara_tujuan'],
                $criteria['tanggal'],
                $criteria['jumlah_penumpang'],
                $criteria['kelas']
            );
        } else {
            $stmt->bind_param("iisi", 
                $criteria['id_bandara_asal'],
                $criteria['id_bandara_tujuan'],
                $criteria['tanggal'],
                $criteria['jumlah_penumpang']
            );
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        $flights = [];
        while ($row = $result->fetch_assoc()) {
            $flights[] = $row;
        }
        return $flights;
    }
    
    public function updateSeatsAvailable($id, $change) {
        $query = "UPDATE penerbangan 
                 SET kursi_tersedia = kursi_tersedia + ? 
                 WHERE id_penerbangan = ? 
                 AND kursi_tersedia + ? >= 0";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iii", $change, $id, $change);
        return $stmt->execute();
    }
    
    public function updateStatus($id, $status) {
        $query = "UPDATE penerbangan SET status = ? WHERE id_penerbangan = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }
}
