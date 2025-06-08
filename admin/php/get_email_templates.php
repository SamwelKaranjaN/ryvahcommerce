<?php
require_once 'email_templates.php';
session_start();

// Check if admin is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'Admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'list':
        echo json_encode([
            'success' => true,
            'templates' => EmailTemplates::getTemplatesList()
        ]);
        break;

    case 'get':
        $templateId = $_GET['id'] ?? '';
        if (empty($templateId)) {
            echo json_encode(['success' => false, 'message' => 'Template ID required']);
            exit();
        }

        $template = EmailTemplates::getTemplate($templateId);
        if ($template) {
            echo json_encode([
                'success' => true,
                'template' => $template
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Template not found']);
        }
        break;

    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}
