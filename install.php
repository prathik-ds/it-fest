<?php
// FUSIONVERSE AUTO-INSTALLER

$host = 'localhost';
$user = 'root';
$pass = ''; // Default XAMPP/Wamp password
$db   = 'fusionverse_db';

echo "<h2 style='font-family: sans-serif;'>FUSIONVERSE DATABASE SETUP</h2>";

try {
    // 1. Connect without DB
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 2. Create DB
    echo "Creating database if not exists...<br>";
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "<b>Database Created Successfully.</b><br><br>";

    // 3. Connect to DB
    $pdo->exec("USE `$db` ");
    
    // 4. Create Tables
    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id VARCHAR(20) UNIQUE NOT NULL,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) UNIQUE NOT NULL,
        phone VARCHAR(20) NOT NULL,
        college VARCHAR(255) NOT NULL,
        course VARCHAR(255) NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        category ENUM('IT', 'Commerce') NOT NULL,
        description TEXT,
        rules TEXT,
        date DATE,
        time TIME,
        venue VARCHAR(255),
        coordinator_name VARCHAR(255),
        coordinator_phone VARCHAR(20),
        max_participants INT DEFAULT 0,
        current_participants INT DEFAULT 0,
        status ENUM('active', 'full', 'completed') DEFAULT 'active',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );

    CREATE TABLE IF NOT EXISTS registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id VARCHAR(20) NOT NULL,
        event_id INT NOT NULL,
        status ENUM('registered', 'winner', 'runner', 'participated') DEFAULT 'registered',
        score INT DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE,
        UNIQUE(user_id, event_id)
    );

    CREATE TABLE IF NOT EXISTS announcements (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content TEXT NOT NULL,
        type ENUM('alert', 'update', 'result') DEFAULT 'update',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );";

    echo "Executing Table Schemas...<br>";
    $pdo->exec($sql);
    echo "<b>Tables Initialized Successfully.</b><br><br>";

    // 5. Seed Demo Data
    echo "Seeding Demo Data...<br>";
    
    // Check if admin exists
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $stmt->execute();
    if ($stmt->fetchColumn() == 0) {
        $admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO users (user_id, name, email, phone, college, course, password, role) VALUES ('ADMIN001', 'Super Admin', 'admin@fusionverse.com', '1234567890', 'FUSIONVERSE HQ', 'Management', '$admin_pass', 'admin')");
        echo "Admin account created (admin@fusionverse.com / admin123).<br>";
    }

    // Check if events exist
    $stmt = $pdo->query("SELECT COUNT(*) FROM events");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO events (name, category, description, rules, date, time, venue, max_participants) VALUES 
        ('Code Rush', 'IT', 'Fast-paced coding competition where logic meets speed.', 'Individual. No internet allowed.', '2026-04-10', '10:00:00', 'Lab 1', 50),
        ('Biz Quiz', 'Commerce', 'Test your business knowledge and strategy across rounds.', 'Team of 2. Buzzer format.', '2026-04-10', '11:30:00', 'Main Auditorium', 100),
        ('Fusion Hack', 'IT', 'Build the future in this intensive 12-hour build-a-thon.', 'Team of 3-4. Innovative solutions only.', '2026-04-11', '09:00:00', 'Arena North', 30)");
        echo "Demo events populated.<br>";
    }

    echo "<br><b style='color: green;'>ALL SYSTEMS NOMINAL. YOU MAY NOW DELETE THIS FILE (install.php) AND ACCESS THE SYSTEM.</b>";
    echo "<br><br><a href='index.php' style='display: inline-block; padding: 10px 20px; background: #007bff; color: #fff; border-radius: 5px; text-decoration: none;'>GOTO SYSTEM HOME</a>";

} catch (PDOException $e) {
    die("<br><b style='color: red;'>CRITICAL ERROR DURING SETUP: " . $e->getMessage() . "</b>");
}
?>
