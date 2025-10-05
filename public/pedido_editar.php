<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: index.php"); exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: pedidos.php"); exit;
}

// Función para obtener info de API
function apiGet($url, $token) {
    $ch = curl_init("http://localhost:8080".$url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$token]);
    $resp = curl_exec($ch);
    curl_close($ch);
    return json_decode($resp, true);
}

$pedido = apiGet("/api/pedidos/$id", $_SESSION['token']);
$clientes = apiGet("/api/clients", $_SESSION['token']);
$productos = apiGet("/api/products", $_SESSION['token']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'cliente_id' => $_POST['cliente_id'],
        'producto_id' => $_POST['producto_id'],
        'cantidad' => $_POST['cantidad'],
        'estado' => $_POST['estado']
    ];

    $ch = curl_init("http://localhost:8080/api/pedidos/$id");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer '.$_SESSION['token'],
        'Content-Type: application/json'
    ]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status === 200) {
        header("Location: pedidos.php");
        exit;
    } else {
        $error = "Error al actualizar pedido";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Pedido</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
  <h1>Editar Pedido</h1>
  <form method="post">
    <div class="mb-3">
      <label>Cliente</label>
      <select name="cliente_id" class="form-control" required>
        <?php foreach ($clientes as $c): ?>
          <option value="<?= $c['id'] ?>" <?= ($pedido['cliente_id']==$c['id'])?'selected':'' ?>>
            <?= htmlspecialchars($c['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label>Producto</label>
      <select name="producto_id" class="form-control" required>
        <?php foreach ($productos as $p): ?>
          <option value="<?= $p['id'] ?>" <?= ($pedido['producto_id']==$p['id'])?'selected':'' ?>>
            <?= htmlspecialchars($p['name']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label>Cantidad</label>
      <input type="number" name="cantidad" value="<?= htmlspecialchars($pedido['cantidad'] ?? '') ?>" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Estado</label>
      <select name="estado" class="form-control">
        <option value="pendiente" <?= ($pedido['estado']=='pendiente')?'selected':'' ?>>Pendiente</option>
        <option value="procesado" <?= ($pedido['estado']=='procesado')?'selected':'' ?>>Procesado</option>
        <option value="completado" <?= ($pedido['estado']=='completado')?'selected':'' ?>>Completado</option>
      </select>
    </div>
    <button class="btn btn-primary">Actualizar</button>
    <a href="pedidos.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>
</body>
</html>
