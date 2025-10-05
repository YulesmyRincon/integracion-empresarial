<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: index.php"); exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: productos.php"); exit;
}

// Obtener producto
$ch = curl_init("http://localhost:8080/api/products/$id");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$_SESSION['token']]);
$response = curl_exec($ch);
curl_close($ch);
$producto = json_decode($response, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'],
        'price' => $_POST['price'],
        'stock' => $_POST['stock'],
    ];

    $ch = curl_init("http://localhost:8080/api/products/$id");
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
        header("Location: productos.php");
        exit;
    } else {
        $error = "Error al actualizar producto";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Producto</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
  <h1>Editar Producto</h1>
  <form method="post">
    <div class="mb-3">
      <label>Nombre</label>
      <input type="text" name="name" value="<?= htmlspecialchars($producto['name'] ?? '') ?>" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Precio</label>
      <input type="number" step="0.01" name="price" value="<?= htmlspecialchars($producto['price'] ?? '') ?>" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Stock</label>
      <input type="number" name="stock" value="<?= htmlspecialchars($producto['stock'] ?? '') ?>" class="form-control" required>
    </div>
    <button class="btn btn-primary">Actualizar</button>
    <a href="productos.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>
</body>
</html>
