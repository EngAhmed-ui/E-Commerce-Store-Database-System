<?php
// File: ../includes/functions.php (or wherever your includes directory is)

// --- CONFIGURATION CONSTANTS ---
// Defines an absolute path to the database config file.
// This assumes this file is in 'includes/' and db.php is in 'config/'.
define('DB_CONFIG_PATH', __DIR__ . '/../config/db.php');

// --- FLASH MESSAGE FUNCTIONS ---

/**
 * Sets a flash message to be displayed on the next page load.
 * @param string $message The message content.
 * @param string $type The message type (e.g., 'success', 'error', 'warning', 'info').
 */
function set_flash_message($message, $type = 'info') {
    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    // Store the message content and type in the session
    $_SESSION['flash_message'] = ['content' => $message, 'type' => $type];
}

/**
 * Retrieves the flash message array from the session and immediately clears it.
 * @return array|null An array containing ['content' => string, 'type' => string] or null if none exists.
 */
function get_flash_message() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        // Clear the message immediately after retrieval
        unset($_SESSION['flash_message']);
        return $message;
    }
    return null;
}

/**
 * Displays the flash message as a Bootstrap alert and clears it from the session.
 * NOTE: This is the function that was missing in your previous code.
 */
function display_flash_message() {
    $flash = get_flash_message();

    if ($flash) {
        $message = htmlspecialchars($flash['content']);
        $type = htmlspecialchars($flash['type']);
        
        // Output the Bootstrap alert markup
        echo '<div class="alert alert-' . $type . ' alert-dismissible fade show" role="alert">';
        echo $message;
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
    }
}

// --- DATABASE CONNECTION HELPER ---

/**
 * Includes the database configuration file and makes the $pdo object available.
 * @global PDO $pdo The PDO connection object.
 */
function include_db_config() {
    // We use the defined constant to include the file
    require_once DB_CONFIG_PATH; 
}


