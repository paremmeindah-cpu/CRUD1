<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type");

require_once 'db.php';

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {

    // 🟢 READ (GET)
    case 'GET':
        if (isset($_GET['id'])) {
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            echo json_encode($user ?: ['message' => 'User not found']);
        } else {
            $stmt = $pdo->query("SELECT * FROM users");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    // 🟡 CREATE (POST)
    case 'POST':
        $data = json_decode(file_get_contents("php://input"), true);
        if (!isset($data['username'], $data['email'], $data['password'])) {
            echo json_encode(['error' => 'Incomplete data']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        $success = $stmt->execute([
            $data['username'],
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT)
        ]);

        echo json_encode(['message' => $success ? 'User created' : 'Failed to create user']);
        break;

    // 🟠 UPDATE (PUT)
    case 'PUT':
        if (!isset($_GET['id'])) {
            echo json_encode(['error' => 'Missing ID']);
            exit;
        }

        $data = json_decode(file_get_contents("php://input"), true);
        $stmt = $pdo->prepare("UPDATE users SET username=?, email=?, password=? WHERE id=?");
        $success = $stmt->execute([
            $data['username'],
            $data['email'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $_GET['id']
        ]);

        echo json_encode(['message' => $success ? 'User updated' : 'Failed to update user']);
        break;

    // 🔴 DELETE (DELETE)
    case 'DELETE':
        if (!isset($_GET['id'])) {
            echo json_encode(['error' => 'Missing ID']);
            exit;
        }

        $stmt = $pdo->prepare("DELETE FROM users WHERE id=?");
        $success = $stmt->execute([$_GET['id']]);

        echo json_encode(['message' => $success ? 'User deleted' : 'Failed to delete user']);
        break;

    default:
        echo json_encode(['error' => 'Invalid method']);
    break;
}
?>
