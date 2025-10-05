<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: index.php"); exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    header("Location: clientes.php"); exit;
}

// Confirmación por GET ?confirm=1
if (isset($_GET['confirm']) && $_GET['confirm'] == 1) {
    $ch = curl_init("http://localhost:8080/api/clients/$id");
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$_SESSION['token']]);
    $response = curl_exec($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($status === 204) {
        header("Location: clientes.php");
        exit;
    } else {
        $error = "Error al eliminar cliente";
    }
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Eliminar Cliente</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
  <h1>Eliminar Cliente</h1>
  <p>¿Estás seguro de que deseas eliminar este cliente?</p>
  <a href="cliente_eliminar.php?id=<?= $id ?>&confirm=1" class="btn btn-danger">Sí, eliminar</a>
  <a href="clientes.php" class="btn btn-secondary">Cancelar</a>
</div>
</body>
</html>
