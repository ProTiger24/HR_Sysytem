<?php
// ============================================
// AI Configuration - Groq API
// ============================================

define('AI_PROVIDER', 'groq');
define('AI_API_KEY', getenv('GROQ_API_KEY') ?? 'YOUR_API_KEY_HERE');
define('AI_MODEL', 'llama-3.3-70b-versatile');
define('AI_API_URL', 'https://api.groq.com/openai/v1/chat/completions');

define('DEFAULT_SHORTLIST_SCORE', 80);
define('DEFAULT_REJECT_SCORE', 40);
define('MAX_FILE_SIZE', 10 * 1024 * 1024);
define('ALLOWED_EXTENSIONS', ['pdf', 'doc', 'docx', 'txt']);
?>
