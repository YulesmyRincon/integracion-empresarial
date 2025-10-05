<?php
session_start();

// ✅ Cargar variables del archivo .env
$dotenvPath = __DIR__ . '/../.env';
if (file_exists($dotenvPath)) {
    $lines = file($dotenvPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        putenv($line);
    }
}

// ✅ Verificar si ya está logueado
if (isset($_SESSION['token'])) {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Endpoint del login desde el .env (si existe) o valor por defecto
    $apiUrl = getenv('APP_URL') . '/api/login';
    if (!$apiUrl) $apiUrl = 'http://localhost:8080/api/login';

    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        "email" => $email,
        "password" => $password
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $data = json_decode($response, true);

    if ($status === 200 && isset($data['token'])) {
        $_SESSION['token'] = $data['token'];
        $_SESSION['role'] = $data['role'] ?? 'user'; // Guardar rol también
        header("Location: dashboard.php");
        exit;
    } else {
        $error = $data['error'] ?? 'Credenciales inválidas';
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Login</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-4">
      <div class="card shadow">
        <div class="card-body">
          <h3>Iniciar sesión</h3>
          <?php if (!empty($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>
          <form method="post">
            <div class="mb-3">
              <label>Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="mb-3">
              <label>Contraseña</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100">Entrar</button>
          </form>
          <div class="mt-3 text-center">
            <a href="swagger/index.html" target="_blank">📖 Ver documentación API</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
