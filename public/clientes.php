<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: index.php");
    exit;
}
$token = $_SESSION['token'];

$ch = curl_init("http://localhost:8080/api/clients");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$token]);
$response = curl_exec($ch);
curl_close($ch);
$clientes = json_decode($response, true);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Clientes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
  <h1>Clientes</h1>
  <table class="table table-bordered">
    <thead class="table-dark">
      <tr>
        <th>ID</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Acciones</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($clientes as $c): ?>
      <tr>
        <td><?= $c['id'] ?></td>
        <td><?= $c['name'] ?></td>
        <td><?= $c['email'] ?></td>
        <td><?= $c['phone'] ?></td>
        <td>
          <a href="cliente_editar.php?id=<?= $c['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
          <a href="cliente_eliminar.php?id=<?= $c['id'] ?>" class="btn btn-danger btn-sm">Eliminar</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>

