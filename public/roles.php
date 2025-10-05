<?php
session_start();
if (!isset($_SESSION['token']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php"); exit;
}

// Ejemplo: listar usuarios y permitir cambiar roles
$ch = curl_init("http://localhost:8080/api/users");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$_SESSION['token']]);
$response = curl_exec($ch);
curl_close($ch);
$usuarios = json_decode($response, true);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gestión de Roles</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
  <h1>Gestión de Usuarios</h1>
  <table class="table table-striped">
    <thead>
      <tr><th>ID</th><th>Nombre</th><th>Email</th><th>Rol</th></tr>
    </thead>
    <tbody>
      <?php foreach ($usuarios as $u): ?>
      <tr>
        <td><?= $u['id'] ?></td>
        <td><?= htmlspecialchars($u['name']) ?></td>
        <td><?= htmlspecialchars($u['email']) ?></td>
        <td><?= htmlspecialchars($u['role']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>
