<?php
require_once __DIR__ . '/../includes/config.php';

class Airport {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    public function getAirportById($id) {
        $query = "SELECT * FROM bandara WHERE id_bandara = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function getAirportByCode($code) {
        $query = "SELECT * FROM bandara WHERE kode = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $code);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function createAirport($data) {
        $query = "INSERT INTO bandara (
            nama_bandara,
            kode,
            kota,
            negara,
            timezone
        ) VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "sssss",
            $data['nama_bandara'],
            $data['kode'],
            $data['kota'],
            $data['negara'],
            $data['timezone']
        );
        return $stmt->execute();
    }
    
    public function updateAirport($id, $data) {
        $query = "UPDATE bandara SET 
                  nama_bandara = ?,
                  kode = ?,
                  kota = ?,
                  negara = ?,
                  timezone = ?
                  WHERE id_bandara = ?";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "sssssi",
            $data['nama_bandara'],
            $data['kode'],
            $data['kota'],
            $data['negara'],
            $data['timezone'],
            $id
        );
        return $stmt->execute();
    }
    
    public function deleteAirport($id) {
        // First check if airport is used in any flights
        $query = "SELECT COUNT(*) as count FROM penerbangan 
                 WHERE id_bandara_asal = ? OR id_bandara_tujuan = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ii", $id, $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
            throw new Exception("Cannot delete airport: It is used in existing flights");
        }
        
        $query = "DELETE FROM bandara WHERE id_bandara = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    public function getAllAirports() {
        $query = "SELECT * FROM bandara ORDER BY id_bandara";
        $result = $this->conn->query($query);
        $airports = [];
        while ($row = $result->fetch_assoc()) {
            $airports[] = $row;
        }
        return $airports;
    }
    
    public function searchAirports($search) {
        $search = "%$search%";
        $query = "SELECT * FROM bandara 
                 WHERE nama_bandara LIKE ? 
                 OR kode LIKE ? 
                 OR kota LIKE ? 
                 OR negara LIKE ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ssss", $search, $search, $search, $search);
        $stmt->execute();
        $result = $stmt->get_result();
        $airports = [];
        while ($row = $result->fetch_assoc()) {
            $airports[] = $row;
        }
        return $airports;
    }
}
