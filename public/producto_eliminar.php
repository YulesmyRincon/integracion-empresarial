<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: index.php"); exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: productos.php"); exit;
}

if (isset($_GET['confirm']) && $_GET['confirm'] == 1) {
    $ch = curl_init("http://localhost:8080/api/products/$id");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$_SESSION['token']]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status === 204) {
        header("Location: productos.php");
        exit;
    } else {
        $error = "Error al eliminar producto";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Eliminar Producto</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
  <h1>Eliminar Producto</h1>
  <p>¿Estás seguro de que deseas eliminar este producto?</p>
  <a href="producto_eliminar.php?id=<?= $id ?>&confirm=1" class="btn btn-danger">Sí, eliminar</a>
  <a href="productos.php" class="btn btn-secondary">Cancelar</a>
</div>
</body>
</html>
