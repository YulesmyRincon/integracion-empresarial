<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\User;
use Firebase\JWT\JWT;

/**
 * @OA\Post(
 *   path="/api/v1/auth/register",
 *   summary="Register a new user",
 *   @OA\RequestBody(@OA\JsonContent(
 *     required={"name","email","password"},
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="email", type="string"),
 *     @OA\Property(property="password", type="string")
 *   )),
 *   @OA\Response(response=201, description="Created")
 * )
 */
class AuthController {
    public function register(Request $req, Response $res) {
        $data = (array)$req->getParsedBody();
        if(empty($data['email']) || empty($data['password']) || empty($data['name'])) {
            $res->getBody()->write(json_encode(['error'=>'Missing fields']));
            return $res->withHeader('Content-Type','application/json')->withStatus(400);
        }
        if(User::where('email', $data['email'])->exists()) {
            $res->getBody()->write(json_encode(['error'=>'Email exists']));
            return $res->withHeader('Content-Type','application/json')->withStatus(400);
        }
        $user = User::create([
            'name'=>$data['name'],
            'email'=>$data['email'],
            'password'=>password_hash($data['password'], PASSWORD_BCRYPT),
            'role'=>$data['role'] ?? 'user'
        ]);
        $user->makeHidden(['password']);
        $res->getBody()->write(json_encode(['user'=>$user]));
        return $res->withHeader('Content-Type','application/json')->withStatus(201);
    }

    /**
     * @OA\Post(
     *   path="/api/v1/auth/login",
     *   summary="Login",
     *   @OA\RequestBody(@OA\JsonContent(
     *     required={"email","password"},
     *     @OA\Property(property="email", type="string"),
     *     @OA\Property(property="password", type="string")
     *   )),
     *   @OA\Response(response=200, description="OK")
     * )
     */
    public function login(Request $req, Response $res) {
        $data = (array)$req->getParsedBody();
        $user = User::where('email', $data['email'])->first();
        if(!$user || !password_verify($data['password'], $user->password)) {
            $res->getBody()->write(json_encode(['error'=>'Invalid credentials']));
            return $res->withHeader('Content-Type','application/json')->withStatus(401);
        }
        $payload = [
            'iss' => getenv('APP_URL'),
            'sub' => $user->id,
            'role' => $user->role,
            'iat' => time(),
            'exp' => time() + intval(getenv('JWT_TTL'))
        ];
        $jwt = JWT::encode($payload, getenv('JWT_SECRET'), 'HS256');
        $res->getBody()->write(json_encode(['token'=>$jwt,'user'=>['id'=>$user->id,'name'=>$user->name,'email'=>$user->email,'role'=>$user->role]]));
        return $res->withHeader('Content-Type','application/json')->withStatus(200);
    }
}
