<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: index.php"); exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: clientes.php"); exit;
}

// Obtener datos del cliente
$ch = curl_init("http://localhost:8080/api/clients/$id");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$_SESSION['token']]);
$response = curl_exec($ch);
curl_close($ch);
$cliente = json_decode($response, true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'name' => $_POST['name'],
        'email' => $_POST['email'],
        'phone' => $_POST['phone'],
        'address' => $_POST['address'],
    ];

    $ch = curl_init("http://localhost:8080/api/clients/$id");
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
        header("Location: clientes.php");
        exit;
    } else {
        $error = "Error al actualizar cliente";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Editar Cliente</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
  <h1>Editar Cliente</h1>
  <form method="post">
    <div class="mb-3">
      <label>Nombre</label>
      <input type="text" name="name" value="<?= htmlspecialchars($cliente['name'] ?? '') ?>" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Email</label>
      <input type="email" name="email" value="<?= htmlspecialchars($cliente['email'] ?? '') ?>" class="form-control" required>
    </div>
    <div class="mb-3">
      <label>Teléfono</label>
      <input type="text" name="phone" value="<?= htmlspecialchars($cliente['phone'] ?? '') ?>" class="form-control">
    </div>
    <div class="mb-3">
      <label>Dirección</label>
      <input type="text" name="address" value="<?= htmlspecialchars($cliente['address'] ?? '') ?>" class="form-control">
    </div>
    <button class="btn btn-primary">Actualizar</button>
    <a href="clientes.php" class="btn btn-secondary">Cancelar</a>
  </form>
</div>
</body>
</html>
