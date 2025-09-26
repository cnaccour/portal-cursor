<?php
/**
 * Shift Report Manager
 * Handles database operations for shift reports
 */

require_once __DIR__ . '/db.php';

class ShiftReportManager {
    private static $instance = null;
    private $use_mock = false;
    
    private function __construct() {
        $this->use_mock = !$this->isDatabaseAvailable();
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Check if database is available
     */
    private function isDatabaseAvailable() {
        try {
            // Include db.php to get the PDO connection
            require_once __DIR__ . '/db.php';
            global $pdo;
            if (!$pdo) return false;
            
            // Check if shift_reports table exists (MySQL syntax)
            $stmt = $pdo->query("SHOW TABLES LIKE 'shift_reports'");
            return (bool)$stmt->fetchColumn();
        } catch (Throwable $e) {
            return false;
        }
    }
    
    /**
     * Check if using mock mode
     */
    public function isUsingMockMode() {
        return $this->use_mock;
    }
    
    /**
     * Save a shift report
     */
    public function saveShiftReport($data) {
        if ($this->use_mock) {
            return $this->saveShiftReportMock($data);
        }
        
        try {
            // Include db.php to get the PDO connection
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            // Get user ID from session
            $user_id = $_SESSION['user_id'] ?? null;
            if (!$user_id) {
                throw new Exception('User ID not found in session');
            }
            
            $sql = "INSERT INTO shift_reports (
                user_id, shift_date, shift_type, location, 
                checklist_data, reviews_count, shipments_data, 
                refunds_data, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([
                $user_id,
                $data['shift_date'],
                $data['shift_type'],
                $data['location'],
                json_encode($data['checklist'] ?? []),
                intval($data['reviews'] ?? 0),
                json_encode($data['shipments'] ?? []),
                json_encode($data['refunds'] ?? []),
                $data['notes'] ?? ''
            ]);
            
            if ($result) {
                return $pdo->lastInsertId();
            }
            
            throw new Exception('Failed to save shift report');
            
        } catch (Exception $e) {
            error_log("ShiftReportManager::saveShiftReport error: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Get all shift reports with filtering
     */
    public function getShiftReports($filters = []) {
        if ($this->use_mock) {
            return $this->getShiftReportsMock($filters);
        }
        
        try {
            // Include db.php to get the PDO connection
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            $sql = "SELECT sr.*, u.name as user_name, u.email as user_email 
                    FROM shift_reports sr 
                    LEFT JOIN users u ON sr.user_id = u.id 
                    WHERE 1=1";
            $params = [];
            
            // Apply filters
            if (!empty($filters['location'])) {
                $sql .= " AND sr.location = ?";
                $params[] = $filters['location'];
            }
            
            if (!empty($filters['shift_type'])) {
                $sql .= " AND sr.shift_type = ?";
                $params[] = $filters['shift_type'];
            }
            
            if (!empty($filters['user_id'])) {
                $sql .= " AND sr.user_id = ?";
                $params[] = $filters['user_id'];
            }
            
            if (!empty($filters['date_from'])) {
                $sql .= " AND sr.shift_date >= ?";
                $params[] = $filters['date_from'];
            }
            
            if (!empty($filters['date_to'])) {
                $sql .= " AND sr.shift_date <= ?";
                $params[] = $filters['date_to'];
            }
            
            // Search across multiple fields
            if (!empty($filters['search'])) {
                $sql .= " AND (sr.notes LIKE ? OR sr.location LIKE ? OR u.name LIKE ?)";
                $searchTerm = '%' . $filters['search'] . '%';
                $params[] = $searchTerm;
                $params[] = $searchTerm;
                $params[] = $searchTerm;
            }
            
            // Default order by newest first
            $sortBy = $filters['sort'] ?? 'date';
            switch ($sortBy) {
                case 'date':
                    $sql .= " ORDER BY sr.shift_date DESC, sr.created_at DESC";
                    break;
                case 'location':
                    $sql .= " ORDER BY sr.location ASC, sr.shift_date DESC";
                    break;
                case 'user':
                    $sql .= " ORDER BY u.name ASC, sr.shift_date DESC";
                    break;
                case 'type':
                    $sql .= " ORDER BY sr.shift_type ASC, sr.shift_date DESC";
                    break;
                default:
                    $sql .= " ORDER BY sr.created_at DESC";
            }
            
            // Apply limit if specified
            if (!empty($filters['limit'])) {
                $sql .= " LIMIT " . intval($filters['limit']);
            }
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            
            $reports = [];
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Decode JSON fields
                $checklistData = json_decode($row['checklist_data'] ?? '[]', true);
                $row['checklist'] = $checklistData;
                $row['shipments'] = json_decode($row['shipments_data'] ?? '{}', true);
                $row['refunds'] = json_decode($row['refunds_data'] ?? '[]', true);
                $row['reviews'] = $row['reviews_count'];
                $row['user'] = $row['user_name']; // For backward compatibility
                $row['time'] = $row['created_at']; // For backward compatibility
                
                // Clean up
                unset($row['checklist_data'], $row['shipments_data'], $row['refunds_data']);
                
                $reports[] = $row;
            }
            
            return $reports;
            
        } catch (Exception $e) {
            error_log("ShiftReportManager::getShiftReports error: " . $e->getMessage());
            return [];
        }
    }
    
    /**
     * Get a single shift report by ID
     */
    public function getShiftReport($id) {
        if ($this->use_mock) {
            return $this->getShiftReportMock($id);
        }
        
        try {
            // Include db.php to get the PDO connection
            require_once __DIR__ . '/db.php';
            global $pdo;
            
            $sql = "SELECT sr.*, u.name as user_name, u.email as user_email 
                    FROM shift_reports sr 
                    LEFT JOIN users u ON sr.user_id = u.id 
                    WHERE sr.id = ?";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$row) {
                return null;
            }
            
            // Decode JSON fields
            $checklistData = json_decode($row['checklist_data'] ?? '[]', true);
            $row['checklist'] = $checklistData;
            $row['shipments'] = json_decode($row['shipments_data'] ?? '{}', true);
            $row['refunds'] = json_decode($row['refunds_data'] ?? '[]', true);
            $row['reviews'] = $row['reviews_count'];
            $row['user'] = $row['user_name']; // For backward compatibility
            $row['time'] = $row['created_at']; // For backward compatibility
            
            // Clean up
            unset($row['checklist_data'], $row['shipments_data'], $row['refunds_data']);
            
            return $row;
            
        } catch (Exception $e) {
            error_log("ShiftReportManager::getShiftReport error: " . $e->getMessage());
            return null;
        }
    }
    
    // Mock implementations for development/fallback
    private function saveShiftReportMock($data) {
        // Fallback to original text file method
        $data['user'] = $_SESSION['name'] ?? 'Unknown';
        $data['time'] = date('Y-m-d H:i:s');
        
        $file = __DIR__ . '/../shift-reports.txt';
        $line = json_encode($data, JSON_UNESCAPED_UNICODE) . PHP_EOL;
        file_put_contents($file, $line, FILE_APPEND);
        
        return true;
    }
    
    private function getShiftReportsMock($filters = []) {
        // Read from text file
        $file = __DIR__ . '/../shift-reports.txt';
        $reports = [];
        
        if (file_exists($file)) {
            $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $index => $line) {
                $row = json_decode($line, true);
                if ($row) {
                    $row['id'] = $index;
                    $reports[] = $row;
                }
            }
        }
        
        // Apply basic filtering
        if (!empty($filters['location'])) {
            $reports = array_filter($reports, fn($r) => $r['location'] === $filters['location']);
        }
        
        if (!empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $reports = array_filter($reports, function($r) use ($search) {
                return stripos($r['user'] ?? '', $search) !== false ||
                       stripos($r['location'] ?? '', $search) !== false ||
                       stripos($r['notes'] ?? '', $search) !== false;
            });
        }
        
        // Apply limit
        if (!empty($filters['limit'])) {
            $reports = array_slice($reports, 0, intval($filters['limit']));
        }
        
        return array_reverse($reports); // Newest first
    }
    
    private function getShiftReportMock($id) {
        // Read from text file and find by original index
        $file = __DIR__ . '/../shift-reports.txt';
        if (!file_exists($file)) {
            return null;
        }
        
        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if (!isset($lines[$id])) {
            return null;
        }
        
        $row = json_decode($lines[$id], true);
        if ($row) {
            $row['id'] = $id;
            return $row;
        }
        
        return null;
    }
}