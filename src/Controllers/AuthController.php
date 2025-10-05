<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\User;
use Firebase\JWT\JWT;

class AuthController {

    /**
     * @OA\Post(
     *   path="/api/register",
     *   summary="Registrar usuario",
     *   tags={"Autenticación"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"name","email","password"},
     *       @OA\Property(property="name", type="string"),
     *       @OA\Property(property="email", type="string"),
     *       @OA\Property(property="password", type="string"),
     *       @OA\Property(property="role", type="string", example="user")
     *     )
     *   ),
     *   @OA\Response(response=201, description="Usuario registrado"),
     *   @OA\Response(response=409, description="Email ya registrado")
     * )
     */
    public function register(Request $req, Response $res) {
        $data = (array)$req->getParsedBody();
        if (empty($data['email']) || empty($data['password']) || empty($data['name'])) {
            $res->getBody()->write(json_encode(['error'=>'Missing fields']));
            return $res->withStatus(400)->withHeader('Content-Type','application/json');
        }
        if (User::where('email', $data['email'])->exists()) {
            $res->getBody()->write(json_encode(['error'=>'Email already registered']));
            return $res->withStatus(409)->withHeader('Content-Type','application/json');
        }
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'] ?? 'user'
        ]);
        $res->getBody()->write(json_encode(['id'=>$user->id,'email'=>$user->email]));
        return $res->withStatus(201)->withHeader('Content-Type','application/json');
    }

    /**
     * @OA\Post(
     *   path="/api/login",
     *   summary="Iniciar sesión (obtiene token JWT)",
     *   tags={"Autenticación"},
     *   @OA\RequestBody(
     *     required=true,
     *     @OA\JsonContent(
     *       required={"email","password"},
     *       @OA\Property(property="email", type="string"),
     *       @OA\Property(property="password", type="string")
     *     )
     *   ),
     *   @OA\Response(response=200, description="Token JWT")
     * )
     */
    public function login(Request $req, Response $res) {
        $data = (array)$req->getParsedBody();
        $email = $data['email'] ?? '';
        $password = $data['password'] ?? '';

        $user = User::where('email', $email)->first();
        if (!$user || !password_verify($password, $user->password)) {
            $res->getBody()->write(json_encode(['error'=>'Invalid credentials']));
            return $res->withStatus(401)->withHeader('Content-Type','application/json');
        }

        $now = time();
        $exp = $now + 3600 * 4; // 4 horas
        $payload = [
            'sub' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role,
            'iat' => $now,
            'exp' => $exp
        ];
        $secret = $_ENV['JWT_SECRET'] ?? 'change_this_secret';
        $token = JWT::encode($payload, $secret, 'HS256');

        $res->getBody()->write(json_encode(['token'=>$token, 'role'=>$user->role]));
        return $res->withHeader('Content-Type','application/json');
    }
}
