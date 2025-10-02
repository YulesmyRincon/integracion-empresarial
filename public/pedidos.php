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

$pedidos = apiGet("/api/pedidos", $token); // ajusta si tu endpoint es diferente
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Pedidos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-4">
  <h1>Pedidos</h1>
  <a href="pedido_nuevo.php" class="btn btn-primary mb-3">+ Nuevo Pedido</a>
  <table class="table table-hover">
    <thead class="table-dark">
      <tr>
        <th>ID</th>
        <th>Cliente</th>
        <th>Producto(s)</th>
        <th>Cantidad</th>
        <th>Estado</th>
        <th>Acciones</th>
      </tr>
    </thead>
    <tbody>
    <?php foreach ($pedidos as $ped): ?>
      <tr>
        <td><?= $ped['id'] ?></td>
        <td><?= htmlspecialchars($ped['cliente']['name'] ?? 'N/A') ?></td>
        <td>
          <?php if (!empty($ped['productos'])): ?>
            <ul>
              <?php foreach ($ped['productos'] as $prod): ?>
                <li><?= $prod['name'] ?> (<?= $prod['pivot']['cantidad'] ?? 1 ?>)</li>
              <?php endforeach; ?>
            </ul>
          <?php endif; ?>
        </td>
        <td><?= $ped['cantidad'] ?? '-' ?></td>
        <td><?= $ped['estado'] ?? 'Pendiente' ?></td>
        <td>
          <a href="pedido_editar.php?id=<?= $ped['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
          <a href="pedido_eliminar.php?id=<?= $ped['id'] ?>" class="btn btn-danger btn-sm">Eliminar</a>
        </td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
</body>
</html>
