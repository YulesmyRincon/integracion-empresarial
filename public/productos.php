<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: index.php");
    exit;
}
$token = $_SESSION['token'];

function apiGet($url, $token) {
    $ch = curl_init("http://localhost:8080".$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$token]);
    $resp = curl_exec($ch);
    curl_close($ch);
    return json_decode($resp, true);
}

$productos = apiGet("/api/products", $token);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Productos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
  <h1>Productos</h1>
  <a href="producto_nuevo.php" class="btn btn-primary mb-3">+ Nuevo Producto</a>
  <table class="table table-striped">
    <thead class="table-dark">
      <tr>
        <th>ID</th><th>Nombre</th><th>Precio</th><th>Stock</th><th>Acciones</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($productos as $p): ?>
      <tr>
        <td><?= $p['id'] ?></td>
        <td><?= htmlspecialchars($p['name']) ?></td>
        <td>$<?= number_format($p['price'],2) ?></td>
        <td><?= $p['stock'] ?></td>
        <td>
          <a href="producto_editar.php?id=<?= $p['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
          <a href="producto_eliminar.php?id=<?= $p['id'] ?>" class="btn btn-danger btn-sm">Eliminar</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>
