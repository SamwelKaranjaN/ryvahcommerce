<?php
return [
    'database' => [
        'host' => 'localhost',
        'username' => 'root',
        'password' => '@X6js1488',
        'dbname' => 'ryvahcommerce',
        'port' => 3306
    ],
    'upload' => [
        'max_file_size' => 100 * 1024 * 1024, // 10MB for PDF
        'max_thumb_size' => 20 * 1024 * 1024, // 2MB for thumbnails
        'allowed_pdf_types' => ['application/pdf'],
        'allowed_image_types' => ['image/jpeg', 'image/png', 'image/gif'],
        'upload_dir' => [
            'pdf' => 'uploads/pdfs/',
            'thumb' => 'uploads/thumbs/'
        ]
    ]
];
