<?php
namespace App;

/**
 * @OA\Info(
 *     title="API de Integración Empresarial",
 *     version="1.0.0",
 *     description="Documentación de la API RESTful para Clientes, Productos y Pedidos"
 * )
 *
 * @OA\Server(
 *     url="http://localhost:8080",
 *     description="Servidor local de desarrollo"
 * )
 *
 * @OA\SecurityScheme(
 *   securityScheme="bearerAuth",
 *   type="http",
 *   scheme="bearer",
 *   bearerFormat="JWT"
 * )
 *
 * @OA\Tag(name="Clientes", description="Operaciones CRUD con clientes")
 * @OA\Tag(name="Productos", description="Operaciones CRUD con productos")
 * @OA\Tag(name="Pedidos", description="Operaciones CRUD con pedidos")
 * @OA\Tag(name="Autenticación", description="Registro y login")
 */
class OpenApiSpec {}
