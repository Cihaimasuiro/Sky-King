<?php
require_once '../../config/database.php';

class CrudHandler {
    private $conn;
    private $table;
    private $primaryKey;
    private $allowedFields;

    public function __construct($conn, $table, $primaryKey, $allowedFields) {
        $this->conn = $conn;
        $this->table = $table;
        $this->primaryKey = $primaryKey;
        $this->allowedFields = $allowedFields;
    }

    public function create($data) {
        $fields = [];
        $values = [];
        $params = [];
        $types = "";

        foreach ($this->allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = $field;
                $values[] = "?";
                $params[] = $data[$field];
                $types .= "s"; // Assuming all fields are strings, modify if needed
            }
        }

        $query = "INSERT INTO " . $this->table . " (" . implode(",", $fields) . ") VALUES (" . implode(",", $values) . ")";
        $stmt = mysqli_prepare($this->conn, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            $success = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $success;
        }
        return false;
    }

    public function update($id, $data) {
        $sets = [];
        $params = [];
        $types = "";

        foreach ($this->allowedFields as $field) {
            if (isset($data[$field])) {
                $sets[] = "$field = ?";
                $params[] = $data[$field];
                $types .= "s"; // Assuming all fields are strings, modify if needed
            }
        }

        $params[] = $id;
        $types .= "i"; // ID is typically integer

        $query = "UPDATE " . $this->table . " SET " . implode(",", $sets) . " WHERE " . $this->primaryKey . " = ?";
        $stmt = mysqli_prepare($this->conn, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, $types, ...$params);
            $success = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $success;
        }
        return false;
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table . " WHERE " . $this->primaryKey . " = ?";
        $stmt = mysqli_prepare($this->conn, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            $success = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);
            return $success;
        }
        return false;
    }

    public function read($conditions = [], $orderBy = null) {
        $query = "SELECT * FROM " . $this->table;
        
        if (!empty($conditions)) {
            $where = [];
            foreach ($conditions as $field => $value) {
                $where[] = "$field = '$value'";
            }
            $query .= " WHERE " . implode(" AND ", $where);
        }
        
        if ($orderBy) {
            $query .= " ORDER BY $orderBy";
        }

        return mysqli_query($this->conn, $query);
    }
}

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    $table = $_POST['table'] ?? '';
    $action = $_POST['action'] ?? '';
    $primaryKey = $_POST['primaryKey'] ?? 'id';
    $allowedFields = json_decode($_POST['allowedFields'] ?? '[]', true);
    
    if (!$table || !$action || empty($allowedFields)) {
        echo json_encode(['success' => false, 'error' => 'Missing required parameters']);
        exit;
    }

    $handler = new CrudHandler($conn, $table, $primaryKey, $allowedFields);
    
    switch ($action) {
        case 'create':
            $success = $handler->create($_POST);
            echo json_encode(['success' => $success]);
            break;
            
        case 'update':
            $id = $_POST['id'] ?? null;
            if ($id) {
                $success = $handler->update($id, $_POST);
                echo json_encode(['success' => $success]);
            } else {
                echo json_encode(['success' => false, 'error' => 'ID not provided']);
            }
            break;
            
        case 'delete':
            $id = $_POST['id'] ?? null;
            if ($id) {
                $success = $handler->delete($id);
                echo json_encode(['success' => $success]);
            } else {
                echo json_encode(['success' => false, 'error' => 'ID not provided']);
            }
            break;
            
        default:
            echo json_encode(['success' => false, 'error' => 'Invalid action']);
    }
    exit;
}
?>
