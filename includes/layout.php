<?php
// Common layout functions and variables
function getPageTitle()
{
    global $page_title;
    return isset($page_title) ? $page_title : 'Ryvah Commerce';
}

// Common CSS files
function getCommonCSS()
{
    return [
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'
    ];
}

// Common JavaScript files
function getCommonJS()
{
    return [
        'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js'
    ];
}

// Function to render CSS links
function renderCSSLinks()
{
    $cssFiles = getCommonCSS();
    foreach ($cssFiles as $css) {
        echo '<link href="' . $css . '" rel="stylesheet">';
    }
}

// Function to render JavaScript links
function renderJSLinks()
{
    $jsFiles = getCommonJS();
    foreach ($jsFiles as $js) {
        echo '<script src="' . $js . '"></script>';
    }
}
