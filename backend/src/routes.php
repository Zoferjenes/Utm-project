<?php
declare(strict_types=1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

$app->get('/', function (Request $request, Response $response) {
    return json_response([
        'name' => 'Arcade FixIt API',
        'version' => '1.0.0',
        'stack' => ['Slim 4', 'PDO', 'MySQL', 'JWT'],
    ]);
});

$app->post('/auth/register', function (Request $request, Response $response) use ($pdo) {
    $body = body_array($request);
    $errors = require_fields($body, ['name', 'email', 'password', 'role']);

    $role = $body['role'] ?? '';
    if ($role && !in_array($role, ['customer', 'provider'], true)) {
        $errors['role'] = 'Role must be customer or provider';
    }

    if ($errors) {
        return json_response(['errors' => $errors], 400);
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = :email');
    $stmt->execute([':email' => strtolower(trim($body['email']))]);
    if ($stmt->fetch()) {
        return json_response(['error' => 'Email already registered'], 409);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO users (name, email, password_hash, role, phone)
         VALUES (:name, :email, :password_hash, :role, :phone)'
    );
    $stmt->execute([
        ':name' => trim($body['name']),
        ':email' => strtolower(trim($body['email'])),
        ':password_hash' => password_hash((string)$body['password'], PASSWORD_DEFAULT),
        ':role' => $role,
        ':phone' => trim((string)($body['phone'] ?? '')),
    ]);

    return json_response(['status' => 'success', 'user_id' => (int)$pdo->lastInsertId()], 201);
});

$app->post('/auth/login', function (Request $request, Response $response) use ($pdo, $jwt) {
    $body = body_array($request);
    $errors = require_fields($body, ['email', 'password']);
    if ($errors) {
        return json_response(['errors' => $errors], 400);
    }

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = :email AND status = "active"');
    $stmt->execute([':email' => strtolower(trim($body['email']))]);
    $user = $stmt->fetch();

    if (!$user || !password_verify((string)$body['password'], $user['password_hash'])) {
        return json_response(['error' => 'Invalid email or password'], 401);
    }

    unset($user['password_hash']);

    return json_response([
        'access_token' => $jwt->issue($user),
        'user' => $user,
    ]);
});

$app->get('/auth/me', function (Request $request, Response $response) use ($pdo) {
    $auth = (array)$request->getAttribute('auth');
    $stmt = $pdo->prepare('SELECT id, name, email, role, phone, status, created_at FROM users WHERE id = :id');
    $stmt->execute([':id' => (int)$auth['sub']]);
    return json_response(['user' => $stmt->fetch()]);
})->add($auth);

$app->get('/categories', function () use ($pdo) {
    $stmt = $pdo->query('SELECT * FROM service_categories WHERE is_active = 1 ORDER BY name');
    return json_response(['data' => $stmt->fetchAll()]);
});

$app->post('/admin/categories', function (Request $request) use ($pdo) {
    $auth = (array)$request->getAttribute('auth');
    if ($fail = require_role($auth, ['admin'])) {
        return $fail;
    }

    $body = body_array($request);
    $errors = require_fields($body, ['name']);
    if ($errors) {
        return json_response(['errors' => $errors], 400);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO service_categories (name, description, icon)
         VALUES (:name, :description, :icon)'
    );
    $stmt->execute([
        ':name' => trim($body['name']),
        ':description' => trim((string)($body['description'] ?? '')),
        ':icon' => trim((string)($body['icon'] ?? 'tool')),
    ]);

    return json_response(['status' => 'success', 'id' => (int)$pdo->lastInsertId()], 201);
})->add($auth);

$app->patch('/admin/categories/{id:[0-9]+}', function (Request $request, Response $response, array $args) use ($pdo) {
    $auth = (array)$request->getAttribute('auth');
    if ($fail = require_role($auth, ['admin'])) {
        return $fail;
    }

    $body = body_array($request);
    $errors = require_fields($body, ['name']);
    if ($errors) {
        return json_response(['errors' => $errors], 400);
    }

    $stmt = $pdo->prepare('
        UPDATE service_categories
        SET name = :name, description = :description, icon = :icon, is_active = 1
        WHERE id = :id
    ');
    $stmt->execute([
        ':id' => (int)$args['id'],
        ':name' => trim($body['name']),
        ':description' => trim((string)($body['description'] ?? '')),
        ':icon' => trim((string)($body['icon'] ?? 'tool')),
    ]);

    return json_response(['status' => 'success']);
})->add($auth);

$app->delete('/admin/categories/{id:[0-9]+}', function (Request $request, Response $response, array $args) use ($pdo) {
    $auth = (array)$request->getAttribute('auth');
    if ($fail = require_role($auth, ['admin'])) {
        return $fail;
    }

    $stmt = $pdo->prepare('UPDATE service_categories SET is_active = 0 WHERE id = :id');
    $stmt->execute([':id' => (int)$args['id']]);

    return json_response(['status' => 'success']);
})->add($auth);

$app->get('/providers', function (Request $request) use ($pdo) {
    $params = $request->getQueryParams();
    $where = ['pp.is_verified = 1'];
    $bind = [];

    if (!empty($params['category_id'])) {
        $where[] = 'pc.category_id = :category_id';
        $bind[':category_id'] = (int)$params['category_id'];
    }

    if (!empty($params['q'])) {
        $where[] = '(u.name LIKE :q OR pp.location LIKE :q OR sc.name LIKE :q)';
        $bind[':q'] = '%' . trim($params['q']) . '%';
    }

    if (isset($params['max_rate']) && (float)$params['max_rate'] > 0) {
        $where[] = 'pp.base_rate <= :max_rate';
        $bind[':max_rate'] = (float)$params['max_rate'];
    }

    $sql = '
        SELECT
            pp.id AS provider_id,
            u.name,
            u.phone,
            pp.bio,
            pp.location,
            pp.base_rate,
            pp.photo_url,
            pp.is_verified,
            COALESCE(ROUND(AVG(r.rating), 1), 0) AS rating_avg,
            GROUP_CONCAT(DISTINCT sc.name ORDER BY sc.name SEPARATOR ", ") AS categories
        FROM provider_profiles pp
        JOIN users u ON u.id = pp.user_id
        LEFT JOIN provider_categories pc ON pc.provider_id = pp.id
        LEFT JOIN service_categories sc ON sc.id = pc.category_id
        LEFT JOIN jobs j ON j.provider_id = pp.id
        LEFT JOIN reviews r ON r.job_id = j.id
        WHERE ' . implode(' AND ', $where) . '
        GROUP BY pp.id, u.name, u.phone, pp.bio, pp.location, pp.base_rate, pp.photo_url, pp.is_verified
        ORDER BY rating_avg DESC, u.name ASC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($bind);

    return json_response(['data' => $stmt->fetchAll()]);
});

$app->get('/providers/{id:[0-9]+}', function (Request $request, Response $response, array $args) use ($pdo) {
    $stmt = $pdo->prepare('
        SELECT pp.*, u.name, u.email, u.phone
        FROM provider_profiles pp
        JOIN users u ON u.id = pp.user_id
        WHERE pp.id = :id
    ');
    $stmt->execute([':id' => (int)$args['id']]);
    $provider = $stmt->fetch();

    if (!$provider) {
        return json_response(['error' => 'Provider not found'], 404);
    }

    $stmt = $pdo->prepare('
        SELECT sc.*
        FROM provider_categories pc
        JOIN service_categories sc ON sc.id = pc.category_id
        WHERE pc.provider_id = :id
        ORDER BY sc.name
    ');
    $stmt->execute([':id' => (int)$args['id']]);

    return json_response(['data' => $provider, 'categories' => $stmt->fetchAll()]);
});

$app->get('/providers/profile', function (Request $request) use ($pdo) {
    $auth = (array)$request->getAttribute('auth');
    if ($fail = require_role($auth, ['provider'])) {
        return $fail;
    }

    $stmt = $pdo->prepare('
        SELECT pp.*, u.name, u.email, u.phone
        FROM provider_profiles pp
        JOIN users u ON u.id = pp.user_id
        WHERE pp.user_id = :user_id
    ');
    $stmt->execute([':user_id' => (int)$auth['sub']]);
    $profile = $stmt->fetch();

    $selected = [];
    if ($profile) {
        $stmt = $pdo->prepare('SELECT category_id FROM provider_categories WHERE provider_id = :provider_id');
        $stmt->execute([':provider_id' => (int)$profile['id']]);
        $selected = array_map('intval', array_column($stmt->fetchAll(), 'category_id'));
    }

    return json_response(['data' => $profile ?: null, 'selected_categories' => $selected]);
})->add($auth);

$app->post('/providers/profile', function (Request $request) use ($pdo) {
    $auth = (array)$request->getAttribute('auth');
    if ($fail = require_role($auth, ['provider'])) {
        return $fail;
    }

    $body = body_array($request);
    $errors = require_fields($body, ['bio', 'location', 'base_rate']);
    if ($errors) {
        return json_response(['errors' => $errors], 400);
    }

    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare('
            INSERT INTO provider_profiles (user_id, bio, location, base_rate, photo_url, kyc_doc_url)
            VALUES (:user_id, :bio, :location, :base_rate, :photo_url, :kyc_doc_url)
            ON DUPLICATE KEY UPDATE
                bio = VALUES(bio),
                location = VALUES(location),
                base_rate = VALUES(base_rate),
                photo_url = VALUES(photo_url),
                kyc_doc_url = VALUES(kyc_doc_url)
        ');
        $stmt->execute([
            ':user_id' => (int)$auth['sub'],
            ':bio' => trim($body['bio']),
            ':location' => trim($body['location']),
            ':base_rate' => (float)$body['base_rate'],
            ':photo_url' => trim((string)($body['photo_url'] ?? '/provider-ali.svg')),
            ':kyc_doc_url' => trim((string)($body['kyc_doc_url'] ?? 'mock-kyc.pdf')),
        ]);

        $stmt = $pdo->prepare('SELECT id FROM provider_profiles WHERE user_id = :user_id');
        $stmt->execute([':user_id' => (int)$auth['sub']]);
        $providerId = (int)$stmt->fetchColumn();

        if (isset($body['category_ids']) && is_array($body['category_ids'])) {
            $pdo->prepare('DELETE FROM provider_categories WHERE provider_id = :provider_id')
                ->execute([':provider_id' => $providerId]);

            $insert = $pdo->prepare('
                INSERT IGNORE INTO provider_categories (provider_id, category_id)
                VALUES (:provider_id, :category_id)
            ');
            foreach ($body['category_ids'] as $categoryId) {
                $insert->execute([
                    ':provider_id' => $providerId,
                    ':category_id' => (int)$categoryId,
                ]);
            }
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }

    return json_response(['status' => 'success']);
})->add($auth);

$app->post('/providers/kyc', function (Request $request) use ($pdo) {
    $auth = (array)$request->getAttribute('auth');
    if ($fail = require_role($auth, ['provider'])) {
        return $fail;
    }

    $files = $request->getUploadedFiles();
    if (empty($files['document'])) {
        return json_response(['error' => 'document file is required'], 400);
    }

    $file = $files['document'];
    if ($file->getError() !== UPLOAD_ERR_OK) {
        return json_response(['error' => 'Upload failed'], 400);
    }

    if ($file->getSize() > 2 * 1024 * 1024) {
        return json_response(['error' => 'KYC file must be 2MB or smaller'], 400);
    }

    $name = $file->getClientFilename() ?: 'kyc.pdf';
    $extension = strtolower(pathinfo($name, PATHINFO_EXTENSION));
    if (!in_array($extension, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
        return json_response(['error' => 'Only PDF, JPG, JPEG, or PNG files are allowed'], 400);
    }

    $stmt = $pdo->prepare('SELECT id FROM provider_profiles WHERE user_id = :user_id');
    $stmt->execute([':user_id' => (int)$auth['sub']]);
    $providerId = (int)$stmt->fetchColumn();
    if (!$providerId) {
        return json_response(['error' => 'Create provider profile before uploading KYC'], 400);
    }

    $dir = __DIR__ . '/../storage/kyc';
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }

    $safeName = 'provider-' . $providerId . '-' . bin2hex(random_bytes(8)) . '.' . $extension;
    $file->moveTo($dir . '/' . $safeName);

    $path = 'storage/kyc/' . $safeName;
    $stmt = $pdo->prepare('UPDATE provider_profiles SET kyc_doc_url = :path WHERE id = :id');
    $stmt->execute([':path' => $path, ':id' => $providerId]);

    return json_response(['status' => 'success', 'kyc_doc_url' => $path], 201);
})->add($auth);

$loadAccessibleJob = function (array $auth, int $jobId) use ($pdo): ?array {
    $role = $auth['role'] ?? '';
    $bind = [':id' => $jobId];
    $where = 'j.id = :id';

    if ($role === 'customer') {
        $where .= ' AND j.customer_id = :user_id';
        $bind[':user_id'] = (int)$auth['sub'];
    } elseif ($role === 'provider') {
        $where .= ' AND pp.user_id = :user_id';
        $bind[':user_id'] = (int)$auth['sub'];
    } elseif ($role !== 'admin') {
        return null;
    }

    $stmt = $pdo->prepare("
        SELECT j.id
        FROM jobs j
        JOIN provider_profiles pp ON pp.id = j.provider_id
        WHERE {$where}
    ");
    $stmt->execute($bind);
    $job = $stmt->fetch();

    return $job ?: null;
};

$app->get('/jobs', function (Request $request) use ($pdo) {
    $auth = (array)$request->getAttribute('auth');
    $role = $auth['role'] ?? '';
    $userId = (int)$auth['sub'];
    $bind = [];

    $where = '1=1';
    if ($role === 'customer') {
        $where = 'j.customer_id = :user_id';
        $bind[':user_id'] = $userId;
    } elseif ($role === 'provider') {
        $where = 'pp.user_id = :user_id';
        $bind[':user_id'] = $userId;
    } elseif ($role !== 'admin') {
        return json_response(['error' => 'Forbidden for this role'], 403);
    }

    $stmt = $pdo->prepare("
        SELECT
            j.*,
            c.name AS customer_name,
            pu.name AS provider_name,
            sc.name AS category_name
        FROM jobs j
        JOIN users c ON c.id = j.customer_id
        JOIN provider_profiles pp ON pp.id = j.provider_id
        JOIN users pu ON pu.id = pp.user_id
        JOIN service_categories sc ON sc.id = j.category_id
        WHERE {$where}
        ORDER BY j.created_at DESC
    ");
    $stmt->execute($bind);

    return json_response(['data' => $stmt->fetchAll()]);
})->add($auth);

$app->post('/jobs', function (Request $request) use ($pdo) {
    $auth = (array)$request->getAttribute('auth');
    if ($fail = require_role($auth, ['customer'])) {
        return $fail;
    }

    $body = body_array($request);
    $errors = require_fields($body, ['provider_id', 'category_id', 'scheduled_at', 'address', 'description']);
    if ($errors) {
        return json_response(['errors' => $errors], 400);
    }

    $stmt = $pdo->prepare('
        INSERT INTO jobs (customer_id, provider_id, category_id, scheduled_at, address, description, total)
        VALUES (:customer_id, :provider_id, :category_id, :scheduled_at, :address, :description, :total)
    ');
    $stmt->execute([
        ':customer_id' => (int)$auth['sub'],
        ':provider_id' => (int)$body['provider_id'],
        ':category_id' => (int)$body['category_id'],
        ':scheduled_at' => trim($body['scheduled_at']),
        ':address' => trim($body['address']),
        ':description' => trim($body['description']),
        ':total' => (float)($body['total'] ?? 0),
    ]);

    return json_response(['status' => 'success', 'job_id' => (int)$pdo->lastInsertId()], 201);
})->add($auth);

$app->get('/jobs/{id:[0-9]+}/timeline', function (Request $request, Response $response, array $args) use ($pdo, $loadAccessibleJob) {
    $auth = (array)$request->getAttribute('auth');
    $jobId = (int)$args['id'];

    if (!$loadAccessibleJob($auth, $jobId)) {
        return json_response(['error' => 'Job not found or not allowed'], 404);
    }

    $stmt = $pdo->prepare('
        SELECT
            l.id,
            l.status,
            l.changed_at,
            u.name AS changed_by_name,
            u.role AS changed_by_role
        FROM job_status_logs l
        JOIN users u ON u.id = l.changed_by
        WHERE l.job_id = :job_id
        ORDER BY l.changed_at ASC, l.id ASC
    ');
    $stmt->execute([':job_id' => $jobId]);

    return json_response(['data' => $stmt->fetchAll()]);
})->add($auth);

$app->get('/jobs/{id:[0-9]+}/messages', function (Request $request, Response $response, array $args) use ($pdo, $loadAccessibleJob) {
    $auth = (array)$request->getAttribute('auth');
    $jobId = (int)$args['id'];

    if (!$loadAccessibleJob($auth, $jobId)) {
        return json_response(['error' => 'Job not found or not allowed'], 404);
    }

    $stmt = $pdo->prepare('
        SELECT
            m.id,
            m.body,
            m.sent_at,
            u.id AS sender_id,
            u.name AS sender_name,
            u.role AS sender_role
        FROM messages m
        JOIN users u ON u.id = m.sender_id
        WHERE m.job_id = :job_id
        ORDER BY m.sent_at ASC, m.id ASC
    ');
    $stmt->execute([':job_id' => $jobId]);

    return json_response(['data' => $stmt->fetchAll()]);
})->add($auth);

$app->post('/jobs/{id:[0-9]+}/messages', function (Request $request, Response $response, array $args) use ($pdo, $loadAccessibleJob) {
    $auth = (array)$request->getAttribute('auth');
    $jobId = (int)$args['id'];

    if (!$loadAccessibleJob($auth, $jobId)) {
        return json_response(['error' => 'Job not found or not allowed'], 404);
    }

    $body = body_array($request);
    $message = trim((string)($body['body'] ?? ''));
    if ($message === '') {
        return json_response(['errors' => ['body' => 'body is required']], 400);
    }
    if (strlen($message) > 500) {
        return json_response(['errors' => ['body' => 'Message must be 500 characters or fewer']], 400);
    }

    $stmt = $pdo->prepare('INSERT INTO messages (job_id, sender_id, body) VALUES (:job_id, :sender_id, :body)');
    $stmt->execute([
        ':job_id' => $jobId,
        ':sender_id' => (int)$auth['sub'],
        ':body' => $message,
    ]);

    return json_response(['status' => 'success', 'message_id' => (int)$pdo->lastInsertId()], 201);
})->add($auth);

$app->patch('/jobs/{id}/status', function (Request $request, Response $response, array $args) use ($pdo) {
    $auth = (array)$request->getAttribute('auth');
    $body = body_array($request);
    $status = $body['status'] ?? '';
    $allowed = ['requested', 'accepted', 'rejected', 'in_progress', 'completed', 'reviewed'];

    if (!in_array($status, $allowed, true)) {
        return json_response(['error' => 'Invalid job status'], 400);
    }

    if (!in_array($auth['role'] ?? '', ['provider', 'admin'], true)) {
        return json_response(['error' => 'Only providers/admins can update job status'], 403);
    }

    $stmt = $pdo->prepare('
        SELECT j.id
        FROM jobs j
        JOIN provider_profiles pp ON pp.id = j.provider_id
        WHERE j.id = :id
          AND (:role = "admin" OR pp.user_id = :user_id)
    ');
    $stmt->execute([
        ':id' => (int)$args['id'],
        ':role' => $auth['role'],
        ':user_id' => (int)$auth['sub'],
    ]);
    if (!$stmt->fetch()) {
        return json_response(['error' => 'Job not found or not allowed'], 404);
    }

    $stmt = $pdo->prepare('UPDATE jobs SET status = :status WHERE id = :id');
    $stmt->execute([':status' => $status, ':id' => (int)$args['id']]);

    $log = $pdo->prepare('INSERT INTO job_status_logs (job_id, status, changed_by) VALUES (:job_id, :status, :changed_by)');
    $log->execute([':job_id' => (int)$args['id'], ':status' => $status, ':changed_by' => (int)$auth['sub']]);

    return json_response(['status' => 'success', 'current_status' => $status]);
})->add($auth);

$app->patch('/jobs/{id}/cost', function (Request $request, Response $response, array $args) use ($pdo) {
    $auth = (array)$request->getAttribute('auth');
    $body = body_array($request);

    if (!in_array($auth['role'] ?? '', ['provider', 'admin'], true)) {
        return json_response(['error' => 'Only providers/admins can set final cost'], 403);
    }

    if (!isset($body['final_cost']) || (float)$body['final_cost'] < 0) {
        return json_response(['error' => 'final_cost must be zero or greater'], 400);
    }

    $stmt = $pdo->prepare('
        SELECT j.id
        FROM jobs j
        JOIN provider_profiles pp ON pp.id = j.provider_id
        WHERE j.id = :id
          AND (:role = "admin" OR pp.user_id = :user_id)
    ');
    $stmt->execute([
        ':id' => (int)$args['id'],
        ':role' => $auth['role'],
        ':user_id' => (int)$auth['sub'],
    ]);
    if (!$stmt->fetch()) {
        return json_response(['error' => 'Job not found or not allowed'], 404);
    }

    $stmt = $pdo->prepare('UPDATE jobs SET final_cost = :final_cost, final_cost_confirmed = 0 WHERE id = :id');
    $stmt->execute([':final_cost' => (float)$body['final_cost'], ':id' => (int)$args['id']]);

    return json_response(['status' => 'success', 'final_cost' => (float)$body['final_cost']]);
})->add($auth);

$app->patch('/jobs/{id}/confirm-cost', function (Request $request, Response $response, array $args) use ($pdo) {
    $auth = (array)$request->getAttribute('auth');
    if ($fail = require_role($auth, ['customer'])) {
        return $fail;
    }

    $stmt = $pdo->prepare('
        SELECT id
        FROM jobs
        WHERE id = :id
          AND customer_id = :customer_id
          AND final_cost IS NOT NULL
          AND status IN ("completed", "reviewed")
    ');
    $stmt->execute([
        ':id' => (int)$args['id'],
        ':customer_id' => (int)$auth['sub'],
    ]);

    if (!$stmt->fetch()) {
        return json_response(['error' => 'Completed job with final cost not found'], 404);
    }

    $stmt = $pdo->prepare('UPDATE jobs SET final_cost_confirmed = 1 WHERE id = :id');
    $stmt->execute([':id' => (int)$args['id']]);

    return json_response(['status' => 'success']);
})->add($auth);

$app->post('/reviews', function (Request $request) use ($pdo) {
    $auth = (array)$request->getAttribute('auth');
    if ($fail = require_role($auth, ['customer'])) {
        return $fail;
    }

    $body = body_array($request);
    $errors = require_fields($body, ['job_id', 'rating']);
    $rating = (int)($body['rating'] ?? 0);
    if ($rating < 1 || $rating > 5) {
        $errors['rating'] = 'Rating must be between 1 and 5';
    }
    if ($errors) {
        return json_response(['errors' => $errors], 400);
    }

    $stmt = $pdo->prepare('
        SELECT id
        FROM jobs
        WHERE id = :job_id AND customer_id = :customer_id AND status IN ("completed", "reviewed")
    ');
    $stmt->execute([
        ':job_id' => (int)$body['job_id'],
        ':customer_id' => (int)$auth['sub'],
    ]);
    if (!$stmt->fetch()) {
        return json_response(['error' => 'Completed job not found for this customer'], 404);
    }

    $stmt = $pdo->prepare('
        INSERT INTO reviews (job_id, rating, comment)
        SELECT id, :rating, :comment
        FROM jobs
        WHERE id = :job_id AND customer_id = :customer_id AND status IN ("completed", "reviewed")
        ON DUPLICATE KEY UPDATE rating = VALUES(rating), comment = VALUES(comment)
    ');
    $stmt->execute([
        ':job_id' => (int)$body['job_id'],
        ':customer_id' => (int)$auth['sub'],
        ':rating' => $rating,
        ':comment' => trim((string)($body['comment'] ?? '')),
    ]);

    $pdo->prepare('
        UPDATE jobs
        SET status = "reviewed"
        WHERE id = :id AND customer_id = :customer_id
    ')->execute([
        ':id' => (int)$body['job_id'],
        ':customer_id' => (int)$auth['sub'],
    ]);

    return json_response(['status' => 'success'], 201);
})->add($auth);

$app->get('/admin/overview', function (Request $request) use ($pdo) {
    $auth = (array)$request->getAttribute('auth');
    if ($fail = require_role($auth, ['admin'])) {
        return $fail;
    }

    $countQueries = [
        'total_users' => 'SELECT COUNT(*) FROM users',
        'total_providers' => 'SELECT COUNT(*) FROM provider_profiles',
        'verified_providers' => 'SELECT COUNT(*) FROM provider_profiles WHERE is_verified = 1',
        'pending_providers' => 'SELECT COUNT(*) FROM provider_profiles WHERE is_verified = 0',
        'active_jobs' => 'SELECT COUNT(*) FROM jobs WHERE status NOT IN ("completed", "reviewed", "rejected")',
        'completed_jobs' => 'SELECT COUNT(*) FROM jobs WHERE status IN ("completed", "reviewed")',
        'active_categories' => 'SELECT COUNT(*) FROM service_categories WHERE is_active = 1',
    ];

    $counts = [];
    foreach ($countQueries as $key => $sql) {
        $counts[$key] = (int)$pdo->query($sql)->fetchColumn();
    }

    $stmt = $pdo->query('
        SELECT status, COUNT(*) AS total
        FROM jobs
        GROUP BY status
        ORDER BY status
    ');
    $statusBreakdown = $stmt->fetchAll();

    $stmt = $pdo->query('
        SELECT
            j.id,
            j.status,
            j.total,
            j.final_cost,
            j.created_at,
            c.name AS customer_name,
            pu.name AS provider_name,
            sc.name AS category_name
        FROM jobs j
        JOIN users c ON c.id = j.customer_id
        JOIN provider_profiles pp ON pp.id = j.provider_id
        JOIN users pu ON pu.id = pp.user_id
        JOIN service_categories sc ON sc.id = j.category_id
        ORDER BY j.created_at DESC
        LIMIT 6
    ');
    $latestJobs = $stmt->fetchAll();

    return json_response([
        'data' => [
            'counts' => $counts,
            'status_breakdown' => $statusBreakdown,
            'latest_jobs' => $latestJobs,
        ],
    ]);
})->add($auth);

$app->get('/admin/providers/pending', function (Request $request) use ($pdo) {
    $auth = (array)$request->getAttribute('auth');
    if ($fail = require_role($auth, ['admin'])) {
        return $fail;
    }

    $stmt = $pdo->query('
        SELECT
            pp.*,
            u.name,
            u.email,
            u.phone,
            GROUP_CONCAT(DISTINCT sc.name ORDER BY sc.name SEPARATOR ", ") AS categories
        FROM provider_profiles pp
        JOIN users u ON u.id = pp.user_id
        LEFT JOIN provider_categories pc ON pc.provider_id = pp.id
        LEFT JOIN service_categories sc ON sc.id = pc.category_id
        GROUP BY
            pp.id,
            pp.user_id,
            pp.bio,
            pp.location,
            pp.base_rate,
            pp.photo_url,
            pp.is_verified,
            pp.kyc_doc_url,
            pp.created_at,
            u.name,
            u.email,
            u.phone
        ORDER BY pp.is_verified ASC, pp.created_at DESC
    ');

    return json_response(['data' => $stmt->fetchAll()]);
})->add($auth);

$app->patch('/admin/providers/{id}/verify', function (Request $request, Response $response, array $args) use ($pdo) {
    $auth = (array)$request->getAttribute('auth');
    if ($fail = require_role($auth, ['admin'])) {
        return $fail;
    }

    $body = body_array($request);
    $isVerified = (int)!empty($body['is_verified']);

    $stmt = $pdo->prepare('UPDATE provider_profiles SET is_verified = :verified WHERE id = :id');
    $stmt->execute([':verified' => $isVerified, ':id' => (int)$args['id']]);

    return json_response(['status' => 'success', 'is_verified' => (bool)$isVerified]);
})->add($auth);
