<?php
/**
 * Base Controller
 * All controllers should extend this class
 */
abstract class BaseController {
    
    /**
     * Load a view file
     * @param string $view Path to view file (without .php)
     * @param array $data Data to extract for the view
     * @return void
     */
    protected function view($view, $data = []) {
        // Extract data array to individual variables
        extract($data);
        
        // Define the view path
        $viewPath = __DIR__ . '/../views/' . $view . '.php';
        
        // Include header
        require_once __DIR__ . '/../views/layouts/header.php';
        
        // Include the view file if it exists
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            die("View '{$view}' not found!");
        }
        
        // Include footer
        require_once __DIR__ . '/../views/layouts/footer.php';
    }
    
    /**
     * Redirect to a specific URL
     * @param string $url URL to redirect to
     * @return void
     */
    protected function redirect($url) {
        header("Location: $url");
        exit();
    }
    
    /**
     * Get JSON data from POST request
     * @return array Decoded JSON data
     */
    protected function getJsonInput() {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }
    
    /**
     * Send JSON response
     * @param mixed $data Data to send
     * @param int $statusCode HTTP status code
     * @return void
     */
    protected function jsonResponse($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit();
    }
}