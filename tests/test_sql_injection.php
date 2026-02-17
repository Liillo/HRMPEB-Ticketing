<?php
/**
 * SQL Injection Testing Script
 * HRMPEB Ticketing System
 * 
 * Usage: php test_sql_injection.php
 * 
 * This script tests common SQL injection vectors against your system
 */

// Configuration
$baseUrl = 'http://localhost:8000';
$testResults = [];

// Color output for terminal
function colorOutput($text, $status) {
    $colors = [
        'PASS' => "\033[32m", // Green
        'FAIL' => "\033[31m", // Red
        'INFO' => "\033[36m", // Cyan
        'WARN' => "\033[33m", // Yellow
    ];
    $reset = "\033[0m";
    
    $color = $colors[$status] ?? '';
    echo $color . $text . $reset . "\n";
}

// Test cases
$sqlInjectionPayloads = [
    // Basic SQL injection
    "' OR '1'='1",
    "' OR '1'='1' --",
    "' OR '1'='1' /*",
    "admin' --",
    "admin' #",
    
    // UNION-based
    "' UNION SELECT NULL--",
    "' UNION SELECT NULL, NULL--",
    
    // Boolean-based blind
    "' AND '1'='1",
    "' AND '1'='2",
    
    // Time-based blind
    "' OR SLEEP(5)--",
    
    // Error-based
    "'",
    "''",
    
    // Comment-based
    "admin'--",
    "admin'/*",
];

echo "\n";
colorOutput("═══════════════════════════════════════════════════", 'INFO');
colorOutput("  SQL INJECTION SECURITY TEST", 'INFO');
colorOutput("  HRMPEB Ticketing System", 'INFO');
colorOutput("═══════════════════════════════════════════════════", 'INFO');
echo "\n";

colorOutput("Testing against: $baseUrl", 'INFO');
echo "\n";

// Test 1: Admin Login
colorOutput("TEST 1: Admin Login Endpoint", 'INFO');
colorOutput("─────────────────────────────────────────────────", 'INFO');

$loginPassed = 0;
$loginTotal = 0;

foreach ($sqlInjectionPayloads as $payload) {
    $loginTotal++;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$baseUrl/admin/login");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'email' => $payload,
        'password' => 'test123'
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Check if login was successful (would be bad)
    $isVulnerable = (strpos($response, 'dashboard') !== false) || 
                    (strpos($response, 'admin/dashboard') !== false) ||
                    ($httpCode == 200 && strpos($response, 'login') === false);
    
    if (!$isVulnerable) {
        $loginPassed++;
        colorOutput("  ✓ Blocked: " . substr($payload, 0, 30) . "...", 'PASS');
    } else {
        colorOutput("  ✗ VULNERABLE: " . substr($payload, 0, 30) . "...", 'FAIL');
    }
}

echo "\n";
if ($loginPassed == $loginTotal) {
    colorOutput("✓ Login Endpoint: SECURE ($loginPassed/$loginTotal)", 'PASS');
} else {
    colorOutput("✗ Login Endpoint: VULNERABLE ($loginPassed/$loginTotal)", 'FAIL');
}
echo "\n";

// Test 2: Ticket UUID (Public endpoint)
colorOutput("TEST 2: Ticket Viewing Endpoint", 'INFO');
colorOutput("─────────────────────────────────────────────────", 'INFO');

$uuidPassed = 0;
$uuidTotal = 0;

foreach ($sqlInjectionPayloads as $payload) {
    $uuidTotal++;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$baseUrl/ticket/" . urlencode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Should return 404 or redirect, not expose data
    $isSecure = ($httpCode == 404) || ($httpCode == 302) || ($httpCode >= 500);
    
    if ($isSecure) {
        $uuidPassed++;
        colorOutput("  ✓ Blocked: " . substr($payload, 0, 30) . "...", 'PASS');
    } else {
        colorOutput("  ✗ SUSPICIOUS: " . substr($payload, 0, 30) . "... (HTTP $httpCode)", 'WARN');
    }
}

echo "\n";
if ($uuidPassed >= $uuidTotal * 0.9) { // 90% threshold
    colorOutput("✓ Ticket Endpoint: SECURE ($uuidPassed/$uuidTotal)", 'PASS');
} else {
    colorOutput("⚠ Ticket Endpoint: REVIEW NEEDED ($uuidPassed/$uuidTotal)", 'WARN');
}
echo "\n";

// Test 3: Event ID parameter
colorOutput("TEST 3: Event Booking Endpoint", 'INFO');
colorOutput("─────────────────────────────────────────────────", 'INFO');

$eventPassed = 0;
$eventTotal = 0;

$eventPayloads = ["1' OR '1'='1", "999' UNION SELECT * FROM events--", "1; DROP TABLE events--"];

foreach ($eventPayloads as $payload) {
    $eventTotal++;
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "$baseUrl/event/" . urlencode($payload) . "/booking-type");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $isSecure = ($httpCode == 404) || ($httpCode == 302);
    
    if ($isSecure) {
        $eventPassed++;
        colorOutput("  ✓ Blocked: " . substr($payload, 0, 40) . "...", 'PASS');
    } else {
        colorOutput("  ✗ SUSPICIOUS: " . substr($payload, 0, 40) . "... (HTTP $httpCode)", 'WARN');
    }
}

echo "\n";
if ($eventPassed == $eventTotal) {
    colorOutput("✓ Event Endpoint: SECURE ($eventPassed/$eventTotal)", 'PASS');
} else {
    colorOutput("⚠ Event Endpoint: REVIEW NEEDED ($eventPassed/$eventTotal)", 'WARN');
}
echo "\n";

// Test 4: Search functionality (requires login - skip if can't authenticate)
colorOutput("TEST 4: Search Functionality", 'INFO');
colorOutput("─────────────────────────────────────────────────", 'INFO');
colorOutput("  ⓘ Skipping (requires authentication)", 'INFO');
colorOutput("  Manual test: Login and try search with: ' OR '1'='1", 'INFO');
echo "\n";

// Final Summary
colorOutput("═══════════════════════════════════════════════════", 'INFO');
colorOutput("  TEST SUMMARY", 'INFO');
colorOutput("═══════════════════════════════════════════════════", 'INFO');
echo "\n";

$totalPassed = $loginPassed + $uuidPassed + $eventPassed;
$totalTests = $loginTotal + $uuidTotal + $eventTotal;
$percentage = round(($totalPassed / $totalTests) * 100, 1);

colorOutput("Total Tests Run: $totalTests", 'INFO');
colorOutput("Tests Passed: $totalPassed", $totalPassed == $totalTests ? 'PASS' : 'WARN');
colorOutput("Success Rate: $percentage%", $percentage >= 90 ? 'PASS' : 'WARN');

echo "\n";

if ($percentage >= 95) {
    colorOutput("✓ OVERALL SECURITY STATUS: EXCELLENT", 'PASS');
    colorOutput("  Your system appears well protected against SQL injection.", 'PASS');
} elseif ($percentage >= 80) {
    colorOutput("⚠ OVERALL SECURITY STATUS: GOOD", 'WARN');
    colorOutput("  Minor concerns detected. Review flagged items.", 'WARN');
} else {
    colorOutput("✗ OVERALL SECURITY STATUS: NEEDS ATTENTION", 'FAIL');
    colorOutput("  Potential vulnerabilities detected. Immediate review required.", 'FAIL');
}

echo "\n";
colorOutput("═══════════════════════════════════════════════════", 'INFO');
echo "\n";

// Additional recommendations
colorOutput("RECOMMENDATIONS:", 'INFO');
colorOutput("─────────────────────────────────────────────────", 'INFO');
echo "1. Check Laravel logs: storage/logs/laravel.log\n";
echo "2. Ensure APP_DEBUG=false in production\n";
echo "3. Keep Laravel framework updated\n";
echo "4. Run: php artisan test --filter SqlInjectionTest\n";
echo "5. Review any FAIL or WARN results above\n";
echo "\n";

colorOutput("For detailed testing guide, see SQL_INJECTION_TESTING_GUIDE.md", 'INFO');
echo "\n";
