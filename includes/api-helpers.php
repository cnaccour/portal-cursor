<?php
/**
 * API Helper Functions
 * Provides standardized response functions and validation helpers for API endpoints
 */

/**
 * Send a standardized JSON error response and exit
 * @param int $httpCode HTTP response code
 * @param string $message Error message
 */
function sendErrorResponse(int $httpCode, string $message): void
{
    http_response_code($httpCode);
    echo json_encode(['success' => false, 'error' => $message]);
    exit;
}

/**
 * Send a standardized JSON success response and exit
 * @param mixed $data Response data (optional)
 * @param string $message Success message (optional)
 */
function sendSuccessResponse($data = null, string $message = ''): void
{
    $response = ['success' => true];
    
    if (!empty($message)) {
        $response['message'] = $message;
    }
    
    if ($data !== null) {
        $response = array_merge($response, is_array($data) ? $data : ['data' => $data]);
    }
    
    echo json_encode($response);
    exit;
}

/**
 * Validate CSRF token from POST data
 * @throws void Sends error response and exits if validation fails
 */
function validateCSRFToken(): void
{
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        sendErrorResponse(403, 'Invalid CSRF token');
    }
}

/**
 * Ensure request method is POST
 * @throws void Sends error response and exits if not POST
 */
function requirePostMethod(): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        sendErrorResponse(405, 'Method not allowed');
    }
}

/**
 * Validate required POST fields exist and are not empty
 * @param array $requiredFields Array of field names to validate
 * @throws void Sends error response and exits if validation fails
 */
function validateRequiredFields(array $requiredFields): void
{
    foreach ($requiredFields as $field) {
        if (empty($_POST[$field])) {
            sendErrorResponse(400, "Field '$field' is required");
        }
    }
}

/**
 * Validate field is within allowed values
 * @param string $fieldName Name of the field being validated
 * @param mixed $value Value to validate
 * @param array $allowedValues Array of allowed values
 * @throws void Sends error response and exits if validation fails
 */
function validateFieldInArray(string $fieldName, $value, array $allowedValues): void
{
    if (!in_array($value, $allowedValues)) {
        sendErrorResponse(400, "Invalid $fieldName value");
    }
}

/**
 * Validate string length
 * @param string $fieldName Name of the field being validated
 * @param string $value Value to validate
 * @param int $maxLength Maximum allowed length
 * @throws void Sends error response and exits if validation fails
 */
function validateStringLength(string $fieldName, string $value, int $maxLength): void
{
    if (strlen($value) > $maxLength) {
        sendErrorResponse(400, "$fieldName too long (max $maxLength characters)");
    }
}

/**
 * Validate date format
 * @param string $fieldName Name of the field being validated
 * @param string $date Date string to validate
 * @param string $format Expected date format (default: Y-m-d)
 * @throws void Sends error response and exits if validation fails
 */
function validateDateFormat(string $fieldName, string $date, string $format = 'Y-m-d'): void
{
    $dateCheck = DateTime::createFromFormat($format, $date);
    if (!$dateCheck || $dateCheck->format($format) !== $date) {
        sendErrorResponse(400, "Invalid $fieldName format");
    }
}

/**
 * Load JSON file with error handling
 * @param string $filePath Path to JSON file
 * @param bool $returnEmptyArrayOnError Whether to return empty array if file doesn't exist
 * @return array Decoded JSON data
 * @throws void Sends error response and exits if file cannot be read (when $returnEmptyArrayOnError is false)
 */
function loadJSONFile(string $filePath, bool $returnEmptyArrayOnError = false): array
{
    if (!file_exists($filePath)) {
        return $returnEmptyArrayOnError ? [] : sendErrorResponse(500, 'Data file not found');
    }
    
    $content = file_get_contents($filePath);
    if ($content === false) {
        return $returnEmptyArrayOnError ? [] : sendErrorResponse(500, 'Could not read data file');
    }
    
    $data = json_decode($content, true);
    return $data ?: [];
}

/**
 * Save data to JSON file atomically
 * @param string $filePath Path to JSON file
 * @param array $data Data to save
 * @param string $errorMessage Custom error message on failure
 * @throws void Sends error response and exits if save fails
 */
function saveJSONFile(string $filePath, array $data, string $errorMessage = 'Could not save data'): void
{
    // Create directory if it doesn't exist
    $directory = dirname($filePath);
    if (!is_dir($directory)) {
        if (!mkdir($directory, 0755, true)) {
            sendErrorResponse(500, 'Could not create data directory');
        }
    }
    
    // Save atomically using temp file
    $tempFile = $filePath . '.tmp';
    if (file_put_contents($tempFile, json_encode($data, JSON_PRETTY_PRINT), LOCK_EX) === false) {
        sendErrorResponse(500, $errorMessage);
    }
    
    if (!rename($tempFile, $filePath)) {
        sendErrorResponse(500, $errorMessage);
    }
}
?>