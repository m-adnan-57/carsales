<?php
// backend/api/messages.php

function handleMessages(string $method, ?string $id): void {
    $db   = getDB();
    $user = requireAuth();

    switch ($method) {
        case 'GET':
            if ($id) {
                // Get full thread
                if ($user['role'] === 'admin') {
                    $stmt = $db->prepare('
                        SELECT m.*, u.name AS sender_name, c.brand, c.model
                        FROM messages m
                        JOIN users u ON u.id = m.sender_id
                        JOIN cars  c ON c.id = m.car_id
                        WHERE m.conversation_id = ? OR m.id = ?
                        ORDER BY m.created_at ASC
                    ');
                    $stmt->execute([$id, $id]);
                } else {
                    $stmt = $db->prepare('
                        SELECT m.*, u.name AS sender_name, c.brand, c.model
                        FROM messages m
                        JOIN users u ON u.id = m.sender_id
                        JOIN cars  c ON c.id = m.car_id
                        WHERE (m.conversation_id = ? OR m.id = ?)
                        AND (m.sender_id = ? OR m.receiver_id = ?)
                        ORDER BY m.created_at ASC
                    ');
                    $stmt->execute([$id, $id, $user['sub'], $user['sub']]);
                }
                echo json_encode($stmt->fetchAll());
            } else {
                if ($user['role'] === 'admin') {
                    $stmt = $db->prepare('
                        SELECT m.*, u.name AS sender_name, c.brand, c.model,
                               (SELECT COUNT(*) FROM messages r WHERE r.conversation_id = m.id) as reply_count
                        FROM messages m
                        JOIN users u ON u.id = m.sender_id
                        JOIN cars  c ON c.id = m.car_id
                        WHERE m.conversation_id IS NULL
                        ORDER BY m.created_at DESC
                    ');
                    $stmt->execute();
                } else {
                    $stmt = $db->prepare('
                        SELECT m.*, u.name AS sender_name, c.brand, c.model,
                               (SELECT COUNT(*) FROM messages r WHERE r.conversation_id = m.id) as reply_count
                        FROM messages m
                        JOIN users u ON u.id = m.sender_id
                        JOIN cars  c ON c.id = m.car_id
                        WHERE m.conversation_id IS NULL
                        AND (m.sender_id = ? OR m.receiver_id = ?)
                        ORDER BY m.created_at DESC
                    ');
                    $stmt->execute([$user['sub'], $user['sub']]);
                }
                echo json_encode($stmt->fetchAll());
            }
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['message'])) {
                http_response_code(422);
                echo json_encode(['error' => 'message is required']);
                return;
            }
            if (!empty($data['conversation_id'])) {
                $orig = $db->prepare('SELECT * FROM messages WHERE id = ?');
                $orig->execute([$data['conversation_id']]);
                $original = $orig->fetch();
                if (!$original) { http_response_code(404); echo json_encode(['error' => 'Not found']); return; }
                $receiver_id = ($original['sender_id'] == $user['sub']) ? 1 : $original['sender_id'];
                $stmt = $db->prepare('INSERT INTO messages (car_id,sender_id,receiver_id,message,conversation_id) VALUES (?,?,?,?,?)');
                $stmt->execute([$original['car_id'], $user['sub'], $receiver_id, $data['message'], $data['conversation_id']]);
            } else {
                if (empty($data['car_id'])) { http_response_code(422); echo json_encode(['error' => 'car_id required']); return; }
                $stmt = $db->prepare('INSERT INTO messages (car_id,sender_id,receiver_id,message,conversation_id) VALUES (?,?,1,?,NULL)');
                $stmt->execute([$data['car_id'], $user['sub'], $data['message']]);
            }
            http_response_code(201);
            echo json_encode(['message' => 'Sent', 'id' => $db->lastInsertId()]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}
