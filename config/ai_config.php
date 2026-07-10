<?php
// ============================================
// AI Configuration - Groq API
// Version: 3.0 (Secure - Environment Variables)
// ============================================

// Load .env file
$env = parse_ini_file(__DIR__ . '/../.env');

// Groq API - Free Tier (14,400 requests/day)
define('AI_PROVIDER', 'groq');
define('AI_API_KEY', $env['GROQ_API_KEY'] ?? 'YOUR_API_KEY_HERE');
define('AI_MODEL', 'llama-3.3-70b-versatile');
define('AI_API_URL', 'https://api.groq.com/openai/v1/chat/completions');

// Screening Rules
define('DEFAULT_SHORTLIST_SCORE', 80);
define('DEFAULT_REJECT_SCORE', 40);

// File Upload
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'txt']);
?>
