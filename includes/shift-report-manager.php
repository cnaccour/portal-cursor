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
            $pdo = getPDO();
            if (!$pdo) return false;
            
            // Check if shift_reports table exists (PostgreSQL syntax)
            $stmt = $pdo->query("SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = 'shift_reports' LIMIT 1");
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
            $pdo = getPDO();
            
            // Get user ID from session
            $user_id = $_SESSION['user_id'] ?? null;
            if (!$user_id) {
                throw new Exception('User ID not found in session');
            }
            
            $sql = "INSERT INTO shift_reports (
                user_id, shift_date, shift_type, location, 
                checklist_data, reviews_count, shipments_data, 
                refunds_data, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?) RETURNING id";
            
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
                return $stmt->fetchColumn();
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
            $pdo = getPDO();
            
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
                $row['checklist'] = json_decode($row['checklist_data'] ?? '[]', true);
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
            $pdo = getPDO();
            
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
            $row['checklist'] = json_decode($row['checklist_data'] ?? '[]', true);
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
    
    /**
     * Get comprehensive analytics data for the advanced dashboard
     */
    public function getAnalytics($filters = []) {
        if (!$this->isDatabaseAvailable()) {
            return $this->getAnalyticsMock($filters);
        }
        
        try {
            $pdo = getPDO();
            
            // Base WHERE conditions
            $whereConditions = [];
            $params = [];
            
            if (!empty($filters['start_date'])) {
                $whereConditions[] = "sr.shift_date >= ?";
                $params[] = $filters['start_date'];
            }
            if (!empty($filters['end_date'])) {
                $whereConditions[] = "sr.shift_date <= ?";
                $params[] = $filters['end_date'];
            }
            if (!empty($filters['location'])) {
                $whereConditions[] = "sr.location = ?";
                $params[] = $filters['location'];
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            // Get basic statistics
            $sql = "SELECT 
                COUNT(*) as total_reports,
                SUM(sr.reviews_count) as total_reviews,
                AVG(sr.reviews_count) as avg_reviews_per_report,
                COUNT(DISTINCT sr.location) as unique_locations
                FROM shift_reports sr 
                JOIN users u ON sr.user_id = u.id 
                $whereClause";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $stats = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Calculate daily average
            $startDate = !empty($filters['start_date']) ? new DateTime($filters['start_date']) : new DateTime('-30 days');
            $endDate = !empty($filters['end_date']) ? new DateTime($filters['end_date']) : new DateTime();
            $daysDiff = $endDate->diff($startDate)->days + 1;
            $avgDailyReports = $daysDiff > 0 ? $stats['total_reports'] / $daysDiff : 0;
            
            // Get top performers
            $sql = "SELECT 
                u.name as user,
                COUNT(*) as report_count,
                AVG(sr.reviews_count) as avg_reviews
                FROM shift_reports sr 
                JOIN users u ON sr.user_id = u.id 
                $whereClause 
                GROUP BY u.id, u.name 
                ORDER BY report_count DESC, avg_reviews DESC 
                LIMIT 5";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $topPerformers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get location performance
            $sql = "SELECT 
                sr.location,
                COUNT(*) as report_count,
                AVG(sr.reviews_count) as avg_reviews
                FROM shift_reports sr 
                JOIN users u ON sr.user_id = u.id 
                $whereClause 
                GROUP BY sr.location 
                ORDER BY report_count DESC";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $locationPerformance = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get daily reports for chart
            $sql = "SELECT 
                sr.shift_date as date,
                COUNT(*) as count
                FROM shift_reports sr 
                JOIN users u ON sr.user_id = u.id 
                $whereClause 
                GROUP BY sr.shift_date 
                ORDER BY sr.shift_date";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $dailyReports = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Get location distribution for pie chart
            $locationDistribution = $locationPerformance; // Same data, different use
            
            // Get weekly trends
            $sql = "SELECT 
                DATE_TRUNC('week', sr.shift_date) as week_start,
                COUNT(*) as report_count,
                SUM(sr.reviews_count) as total_reviews,
                AVG(sr.reviews_count) as avg_reviews
                FROM shift_reports sr 
                JOIN users u ON sr.user_id = u.id 
                $whereClause 
                GROUP BY DATE_TRUNC('week', sr.shift_date) 
                ORDER BY week_start DESC 
                LIMIT 8";
            
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $weeklyData = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Calculate trends for weekly data
            $weeklyTrends = [];
            for ($i = 0; $i < count($weeklyData); $i++) {
                $current = $weeklyData[$i];
                $previous = isset($weeklyData[$i + 1]) ? $weeklyData[$i + 1] : null;
                
                $trend = 0;
                if ($previous && $previous['report_count'] > 0) {
                    $trend = (($current['report_count'] - $previous['report_count']) / $previous['report_count']) * 100;
                }
                
                $weeklyTrends[] = array_merge($current, ['trend' => $trend]);
            }
            
            return [
                'total_reports' => (int)$stats['total_reports'],
                'avg_daily_reports' => round($avgDailyReports, 1),
                'total_reviews' => (int)$stats['total_reviews'],
                'avg_reviews_per_report' => round($stats['avg_reviews_per_report'], 1),
                'unique_locations' => (int)$stats['unique_locations'],
                'reports_change' => null, // Would need previous period comparison
                'top_performers' => $topPerformers,
                'location_performance' => $locationPerformance,
                'daily_reports' => $dailyReports,
                'location_distribution' => $locationDistribution,
                'weekly_trends' => array_reverse($weeklyTrends) // Show oldest to newest
            ];
            
        } catch (Exception $e) {
            return $this->getAnalyticsMock($filters);
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
    
    private function getAnalyticsMock($filters = []) {
        $reports = $this->getShiftReportsMock();
        
        // Apply date filters to reports
        if (!empty($filters['start_date'])) {
            $reports = array_filter($reports, function($r) use ($filters) {
                return $r['shift_date'] >= $filters['start_date'];
            });
        }
        if (!empty($filters['end_date'])) {
            $reports = array_filter($reports, function($r) use ($filters) {
                return $r['shift_date'] <= $filters['end_date'];
            });
        }
        if (!empty($filters['location'])) {
            $reports = array_filter($reports, function($r) use ($filters) {
                return $r['location'] === $filters['location'];
            });
        }
        
        $totalReports = count($reports);
        $totalReviews = array_sum(array_column($reports, 'reviews'));
        $uniqueLocations = count(array_unique(array_column($reports, 'location')));
        
        // Calculate date range for daily average
        $startDate = !empty($filters['start_date']) ? new DateTime($filters['start_date']) : new DateTime('-30 days');
        $endDate = !empty($filters['end_date']) ? new DateTime($filters['end_date']) : new DateTime();
        $daysDiff = $endDate->diff($startDate)->days + 1;
        
        return [
            'total_reports' => $totalReports,
            'avg_daily_reports' => $daysDiff > 0 ? $totalReports / $daysDiff : 0,
            'total_reviews' => $totalReviews,
            'avg_reviews_per_report' => $totalReports > 0 ? $totalReviews / $totalReports : 0,
            'unique_locations' => $uniqueLocations,
            'reports_change' => null, // Would need historical data
            'top_performers' => [],
            'location_performance' => [],
            'daily_reports' => [],
            'location_distribution' => [],
            'weekly_trends' => []
        ];
    }
}