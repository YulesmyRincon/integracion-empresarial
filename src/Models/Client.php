<?php
namespace App\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController {
    
    // Listar todos
    public function index() {
        return response()->json(Client::all());
    }

    // Ver uno
    public function show($id) {
        $client = Client::find($id);
        if (!$client) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }
        return response()->json($client);
    }

    // Crear
    public function store(Request $request) {
        $data = $request->only(['name','email','phone','address']);
        $client = Client::create($data);
        return response()->json($client, 201);
    }

    // Actualizar
    public function update(Request $request, $id) {
        $client = Client::find($id);
        if (!$client) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }
        $client->update($request->only(['name','email','phone','address']));
        return response()->json($client);
    }

    // Eliminar
    public function destroy($id) {
        $client = Client::find($id);
        if (!$client) {
            return response()->json(['error' => 'Cliente no encontrado'], 404);
        }
        $client->delete();
        return response()->json(['message' => 'Cliente eliminado']);
    }
}
