<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: index.php"); exit;
}

// Cargar clientes y productos para el formulario
function apiGet($url, $token) {
    $ch = curl_init("http://localhost:8080".$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$token]);
    $resp = curl_exec($ch);
    curl_close($ch);
    return json_decode($resp, true);
}

$clientes = apiGet("/api/clients", $_SESSION['token']);
$productos = apiGet("/api/products", $_SESSION['token']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'cliente_id' => $_POST['cliente_id'],
        'producto_id' => $_POST['producto_id'],
        'cantidad' => $_POST['cantidad'],
    ];

    $ch = curl_init("http://localhost:8080/api/pedidos");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer '.$_SESSION['token'],
        'Content-Type: application/json'
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status === 201) {
        header("Location: pedidos.php");
        exit;
    } else {
        $error = "Error al crear pedido";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Nuevo Pedido</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
  <h1>Registrar Pedido</h1>
  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= $error ?></div>
  <?php endif; ?>
  <form method="post">
    <div class="mb-3">
      <label>Cliente</label>
      <select name="cliente_id" class="form-control" required>
        <option value="">Seleccione un cliente</option>
        <?php foreach ($clientes as $c): ?>
          <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label>Producto</label>
      <select name="producto_id" class="form-control" required>
        <option value="">Seleccione un producto</option>
        <?php foreach ($productos as $p): ?>
          <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label>Cantidad</label>
      <input type="number" name="cantidad" class="form-control" required>
    </div>
    <button class="btn btn-primary">Guardar</button>
    <a href="pedidos.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>
</body>
</html>
