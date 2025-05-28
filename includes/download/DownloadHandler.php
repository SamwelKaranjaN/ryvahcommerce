<?php
require_once __DIR__ . '/../bootstrap.php';

class DownloadHandler
{
    private $conn;
    private $user_id;

    public function __construct($conn, $user_id)
    {
        $this->conn = $conn;
        $this->user_id = $user_id;
    }

    public function validateDownload($product_id)
    {
        try {
            // Check if user has purchased the product
            $sql = "SELECT up.*, p.filepath, p.name 
                    FROM user_purchases up 
                    JOIN products p ON up.product_id = p.id 
                    WHERE up.user_id = ? AND up.product_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $this->user_id, $product_id);
            $stmt->execute();
            $result = $stmt->get_result();
            $purchase = $result->fetch_assoc();

            if (!$purchase) {
                return [
                    'success' => false,
                    'message' => 'You have not purchased this product'
                ];
            }

            // Check if download limit is reached (e.g., 3 downloads per purchase)
            if ($purchase['download_count'] >= 3) {
                return [
                    'success' => false,
                    'message' => 'You have reached the maximum number of downloads for this product'
                ];
            }

            return [
                'success' => true,
                'filepath' => $purchase['filepath'],
                'filename' => $purchase['name']
            ];
        } catch (Exception $e) {
            error_log("Error validating download: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error validating download'
            ];
        }
    }

    public function processDownload($product_id)
    {
        try {
            $validation = $this->validateDownload($product_id);
            if (!$validation['success']) {
                return $validation;
            }

            // Update download count
            $sql = "UPDATE user_purchases 
                    SET download_count = download_count + 1,
                        last_download = NOW() 
                    WHERE user_id = ? AND product_id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->bind_param("ii", $this->user_id, $product_id);
            $stmt->execute();

            // Return file information
            return [
                'success' => true,
                'filepath' => $validation['filepath'],
                'filename' => $validation['filename']
            ];
        } catch (Exception $e) {
            error_log("Error processing download: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Error processing download'
            ];
        }
    }
}