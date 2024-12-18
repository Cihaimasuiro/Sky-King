<?php
require_once __DIR__ . '/../includes/config.php';

class Airline {
    private $conn;
    
    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
    
    public function getAirlineById($id) {
        $query = "SELECT * FROM maskapai WHERE id_maskapai = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
    
    public function createAirline($data) {
        $query = "INSERT INTO maskapai (
            nama_maskapai,
            kode_maskapai,
            logo,
            kontak_email,
            kontak_telp
        ) VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "sssss",
            $data['nama_maskapai'],
            $data['kode_maskapai'],
            $data['logo'],
            $data['kontak_email'],
            $data['kontak_telp']
        );
        return $stmt->execute();
    }
    
    public function updateAirline($id, $data) {
        $query = "UPDATE maskapai SET 
                  nama_maskapai = ?,
                  kode_maskapai = ?,
                  logo = ?,
                  kontak_email = ?,
                  kontak_telp = ?
                  WHERE id_maskapai = ?";
                  
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param(
            "sssssi",
            $data['nama_maskapai'],
            $data['kode_maskapai'],
            $data['logo'],
            $data['kontak_email'],
            $data['kontak_telp'],
            $id
        );
        return $stmt->execute();
    }
    
    public function deleteAirline($id) {
        // First check if airline has any flights
        $query = "SELECT COUNT(*) as count FROM penerbangan WHERE id_maskapai = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_assoc();
        
        if ($result['count'] > 0) {
            throw new Exception("Cannot delete airline: It has existing flights");
        }
        
        $query = "DELETE FROM maskapai WHERE id_maskapai = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    public function getAllAirlines() {
        $query = "SELECT * FROM maskapai ORDER BY nama_maskapai";
        $result = $this->conn->query($query);
        $airlines = [];
        while ($row = $result->fetch_assoc()) {
            $airlines[] = $row;
        }
        return $airlines;
    }
    
    public function searchAirlines($search) {
        $search = "%$search%";
        $query = "SELECT * FROM maskapai 
                 WHERE nama_maskapai LIKE ? 
                 OR kode_maskapai LIKE ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $search, $search);
        $stmt->execute();
        $result = $stmt->get_result();
        $airlines = [];
        while ($row = $result->fetch_assoc()) {
            $airlines[] = $row;
        }
        return $airlines;
    }
    
    public function uploadLogo($id, $file) {
        // Get airline details
        $airline = $this->getAirlineById($id);
        if (!$airline) {
            throw new Exception("Airline not found");
        }
        
        // Delete old logo if exists
        if ($airline['logo'] && file_exists("../uploads/airlines/" . $airline['logo'])) {
            unlink("../uploads/airlines/" . $airline['logo']);
        }
        
        // Generate new filename
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filename = "airline_" . $id . "_" . time() . "." . $extension;
        
        // Create directory if not exists
        if (!file_exists("../uploads/airlines")) {
            mkdir("../uploads/airlines", 0777, true);
        }
        
        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], "../uploads/airlines/" . $filename)) {
            throw new Exception("Failed to upload logo");
        }
        
        // Update database
        $query = "UPDATE maskapai SET logo = ? WHERE id_maskapai = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("si", $filename, $id);
        if (!$stmt->execute()) {
            // Delete uploaded file if database update fails
            unlink("../uploads/airlines/" . $filename);
            throw new Exception("Failed to update logo in database");
        }
        
        return $filename;
    }
}
