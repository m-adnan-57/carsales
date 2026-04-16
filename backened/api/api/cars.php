<?php
// backend/api/cars.php

function handleCars(string $method, ?string $id): void {
    $db = getDB();

    switch ($method) {

        // ── GET /cars  or  GET /cars/{id} ───────────────────────────────
        case 'GET':
            if ($id) {
                $stmt = $db->prepare('SELECT * FROM cars WHERE id = ?');
                $stmt->execute([$id]);
                $car = $stmt->fetch();
                if (!$car) { http_response_code(404); echo json_encode(['error'=>'Car not found']); return; }
                echo json_encode($car);
            } else {
                // Search / filter support
                $where  = [];
                $params = [];

                if (!empty($_GET['brand'])) {
                    $where[] = 'brand LIKE ?';
                    $params[] = '%' . $_GET['brand'] . '%';
                }
                if (!empty($_GET['model'])) {
                    $where[] = 'model LIKE ?';
                    $params[] = '%' . $_GET['model'] . '%';
                }
                if (!empty($_GET['min_price'])) {
                    $where[] = 'price >= ?';
                    $params[] = (float)$_GET['min_price'];
                }
                if (!empty($_GET['max_price'])) {
                    $where[] = 'price <= ?';
                    $params[] = (float)$_GET['max_price'];
                }
                if (!empty($_GET['year'])) {
                    $where[] = 'year = ?';
                    $params[] = (int)$_GET['year'];
                }

                $sql = 'SELECT * FROM cars';
                if ($where) $sql .= ' WHERE ' . implode(' AND ', $where);
                $sql .= ' ORDER BY created_at DESC';

                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                echo json_encode($stmt->fetchAll());
            }
            break;

        // ── POST /cars  (admin only) ─────────────────────────────────────
        case 'POST':
            requireAdmin();
            $data = json_decode(file_get_contents('php://input'), true);

            // Validation
            $required = ['brand','model','year','price','mileage'];
            foreach ($required as $field) {
                if (empty($data[$field])) {
                    http_response_code(422);
                    echo json_encode(['error' => "Field '$field' is required"]);
                    return;
                }
            }

            $stmt = $db->prepare('INSERT INTO cars
                (brand,model,year,price,mileage,fuel_type,transmission,color,image,description)
                VALUES (?,?,?,?,?,?,?,?,?,?)');
            $stmt->execute([
                $data['brand'], $data['model'], $data['year'],
                $data['price'], $data['mileage'],
                $data['fuel_type']     ?? 'Petrol',
                $data['transmission']  ?? 'Manual',
                $data['color']         ?? null,
                $data['image']         ?? null,
                $data['description']   ?? null,
            ]);
            http_response_code(201);
            echo json_encode(['id' => $db->lastInsertId(), 'message' => 'Car created']);
            break;

        // ── PUT /cars/{id}  (admin only) ─────────────────────────────────
        case 'PUT':
            requireAdmin();
            if (!$id) { http_response_code(400); echo json_encode(['error'=>'ID required']); return; }
            $data = json_decode(file_get_contents('php://input'), true);

            $stmt = $db->prepare('UPDATE cars SET
                brand=?,model=?,year=?,price=?,mileage=?,
                fuel_type=?,transmission=?,color=?,image=?,description=?
                WHERE id=?');
            $stmt->execute([
                $data['brand'], $data['model'], $data['year'],
                $data['price'], $data['mileage'],
                $data['fuel_type']    ?? 'Petrol',
                $data['transmission'] ?? 'Manual',
                $data['color']        ?? null,
                $data['image']        ?? null,
                $data['description']  ?? null,
                $id
            ]);
            echo json_encode(['message' => 'Car updated']);
            break;

        // ── DELETE /cars/{id}  (admin only) ──────────────────────────────
        case 'DELETE':
            requireAdmin();
            if (!$id) { http_response_code(400); echo json_encode(['error'=>'ID required']); return; }
            $db->prepare('DELETE FROM cars WHERE id=?')->execute([$id]);
            echo json_encode(['message' => 'Car deleted']);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Method not allowed']);
    }
}
