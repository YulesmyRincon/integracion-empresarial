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

$clientes = apiGet("/api/clients", $token);
$productos = apiGet("/api/products", $token);
// pedidos pendiente de implementar
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <span class="navbar-brand">Panel</span>
    <a href="logout.php" class="btn btn-outline-light btn-sm">Salir</a>
  </div>
</nav>
<div class="container mt-4">
  <h1>Bienvenido</h1>
  <div class="row">
    <div class="col-md-4">
      <div class="card border-primary">
        <div class="card-body">
          <h5>Clientes</h5>
          <p><?= count($clientes ?? []) ?> registrados</p>
          <a href="clientes.php" class="btn btn-primary btn-sm">Ver clientes</a>
        </div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="card border-success">
        <div class="card-body">
          <h5>Productos</h5>
          <p><?= count($productos ?? []) ?> disponibles</p>
          <a href="productos.php" class="btn btn-success btn-sm">Ver productos</a>
        </div>
      </div>
    </div>
  </div>
</div>
</body>
</html>
