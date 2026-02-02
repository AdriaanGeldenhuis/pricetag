<?php
/**
 * Debug script to check admin access
 * DELETE THIS FILE AFTER DEBUGGING
 */

require_once __DIR__ . '/../bootstrap.php';

header('Content-Type: text/plain');

echo "=== Admin Access Debug ===\n\n";

// Check if logged in
echo "1. Authentication Check:\n";
echo "   - auth(): " . (auth() ? 'YES (logged in)' : 'NO (not logged in)') . "\n";
echo "   - Session user_id: " . ($_SESSION['user_id'] ?? 'NOT SET') . "\n\n";

// Get user data
echo "2. User Data:\n";
if (auth()) {
    $db = \App\Core\Database::getInstance();
    $stmt = $db->prepare("SELECT id, email, role, status FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user = $stmt->fetch();

    if ($user) {
        echo "   - ID: " . $user['id'] . "\n";
        echo "   - Email: " . $user['email'] . "\n";
        echo "   - Role: '" . ($user['role'] ?? 'NULL') . "'\n";
        echo "   - Status: " . $user['status'] . "\n";
    } else {
        echo "   - User not found in database!\n";
    }
} else {
    echo "   - Not logged in, cannot check user data\n";
}

echo "\n3. isAdmin() Check:\n";
echo "   - isAdmin(): " . (isAdmin() ? 'YES' : 'NO') . "\n";

// Check what roles would pass
echo "\n4. Role Validation:\n";
$validRoles = ['admin', 'super_admin'];
if (auth()) {
    $user = user(true);
    $currentRole = $user['role'] ?? 'NULL';
    echo "   - Current role: '" . $currentRole . "'\n";
    echo "   - Is valid admin role: " . (in_array($currentRole, $validRoles, true) ? 'YES' : 'NO') . "\n";
    echo "   - Role type: " . gettype($currentRole) . "\n";
    echo "   - Role length: " . strlen($currentRole) . "\n";
}

// Check users table schema
echo "\n5. Users Table Schema:\n";
try {
    $db = \App\Core\Database::getInstance();
    $stmt = $db->query("DESCRIBE users");
    $columns = $stmt->fetchAll();
    foreach ($columns as $col) {
        if ($col['Field'] === 'role') {
            echo "   - role column exists: YES\n";
            echo "   - Type: " . $col['Type'] . "\n";
            echo "   - Default: " . ($col['Default'] ?? 'NULL') . "\n";
        }
    }
} catch (Exception $e) {
    echo "   - Error: " . $e->getMessage() . "\n";
}

echo "\n=== END DEBUG ===\n";
echo "\nDELETE THIS FILE AFTER USE: rm public/debug-admin.php\n";
