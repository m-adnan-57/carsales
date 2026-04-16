<?php
// backend/api/favourites.php

function handleFavourites(string $method, ?string $id): void {
    $db   = getDB();
    $user = requireAuth();

    switch ($method) {
        case 'GET':
            $stmt = $db->prepare('
                SELECT c.*, f.id AS fav_id FROM favourites f
                JOIN cars c ON c.id = f.car_id
                WHERE f.user_id = ?
                ORDER BY f.created_at DESC
            ');
            $stmt->execute([$user['sub']]);
            echo json_encode($stmt->fetchAll());
            break;

        case 'POST':
            $data = json_decode(file_get_contents('php://input'), true);
            if (empty($data['car_id'])) {
                http_response_code(422);
                echo json_encode(['error' => 'car_id required']);
                return;
            }
            try {
                $stmt = $db->prepare('INSERT INTO favourites (user_id,car_id) VALUES (?,?)');
                $stmt->execute([$user['sub'], $data['car_id']]);
                http_response_code(201);
                echo json_encode(['message' => 'Added to favourites']);
            } catch (PDOException $e) {
                http_response_code(409);
                echo json_encode(['error' => 'Already in favourites']);
            }
            break;

        case 'DELETE':
            if (!$id) { http_response_code(400); echo json_encode(['error'=>'ID required']); return; }
            $stmt = $db->prepare('DELETE FROM favourites WHERE id=? AND user_id=?');
            $stmt->execute([$id, $user['sub']]);
            echo json_encode(['message' => 'Removed from favourites']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}
