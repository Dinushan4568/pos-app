<?php
// WhatsApp Notification Helper
// This file contains functions to send WhatsApp notifications

function sendWhatsAppNotification($filename, $phone = '0716662848') {
    $message = "Database exported successfully!\n\n";
    $message .= "File: {$filename}\n";
    $message .= "Location: database/backups/\n";
    $message .= "Time: " . date('Y-m-d H:i:s') . "\n";
    $message .= "User: " . ($_SESSION['user'] ?? 'Unknown');
    
    $encodedMessage = urlencode($message);
    $whatsappUrl = "https://wa.me/{$phone}?text={$encodedMessage}";
    
    return $whatsappUrl;
}

function sendWhatsAppNotificationWithDetails($details) {
    $phone = '0716662848';
    $message = "Database Export Notification\n\n";
    
    foreach ($details as $key => $value) {
        $message .= ucfirst($key) . ": {$value}\n";
    }
    
    $message .= "Time: " . date('Y-m-d H:i:s');
    
    $encodedMessage = urlencode($message);
    $whatsappUrl = "https://wa.me/{$phone}?text={$encodedMessage}";
    
    return $whatsappUrl;
}

// Function to create a clickable WhatsApp link
function createWhatsAppLink($text = "Open WhatsApp") {
    $phone = '0716662848';
    $message = urlencode("Database exported successfully!\n\nTime: " . date('Y-m-d H:i:s'));
    $whatsappUrl = "https://wa.me/{$phone}?text={$message}";
    
    return "<a href='{$whatsappUrl}' target='_blank' class='bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600'>{$text}</a>";
}
?> 