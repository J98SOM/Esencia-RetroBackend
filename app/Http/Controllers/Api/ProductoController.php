<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductoController extends Controller
{
    public function index()
    {
        return response()->json(Producto::all());
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'precio' => 'required|numeric',
            'imagen' => 'nullable|file|image|max:5120',
        ]);

        try {
            if ($request->hasFile('imagen')) {
                $upload = $this->uploadToCloudinary($request->file('imagen'));
                if ($upload && isset($upload['secure_url'])) {
                    $data['imagen_url'] = $upload['secure_url'];
                    $data['imagen_public_id'] = $upload['public_id'] ?? null;
                }
            }

            $producto = Producto::create([
                'nombre' => $data['nombre'],
                'categoria' => $data['categoria'],
                'precio' => $data['precio'],
                'imagen_url' => $data['imagen_url'] ?? null,
                'imagen_public_id' => $data['imagen_public_id'] ?? null,
            ]);

            return response()->json($producto, 201);
        } catch (\Exception $e) {
            Log::error('Producto store error: '.$e->getMessage());

            return response()->json(['message' => 'Error al guardar producto'], 500);
        }
    }

    public function show(Producto $producto)
    {
        return response()->json($producto);
    }

    public function update(Request $request, Producto $producto)
    {
        $data = $request->validate([
            'nombre' => 'required|string|max:255',
            'categoria' => 'required|string|max:255',
            'precio' => 'required|numeric',
            'imagen' => 'nullable|file|image|max:5120',
        ]);

        try {
            if ($request->hasFile('imagen')) {
                // delete old image if present
                if ($producto->imagen_public_id) {
                    $this->deleteFromCloudinary($producto->imagen_public_id);
                }
                $upload = $this->uploadToCloudinary($request->file('imagen'));
                if ($upload && isset($upload['secure_url'])) {
                    $data['imagen_url'] = $upload['secure_url'];
                    $data['imagen_public_id'] = $upload['public_id'] ?? null;
                }
            }

            $producto->update([
                'nombre' => $data['nombre'],
                'categoria' => $data['categoria'],
                'precio' => $data['precio'],
                'imagen_url' => $data['imagen_url'] ?? $producto->imagen_url,
                'imagen_public_id' => $data['imagen_public_id'] ?? $producto->imagen_public_id,
            ]);

            return response()->json($producto);
        } catch (\Exception $e) {
            Log::error('Producto update error: '.$e->getMessage());

            return response()->json(['message' => 'Error al actualizar producto'], 500);
        }
    }

    public function destroy(Producto $producto)
    {
        try {
            if ($producto->imagen_public_id) {
                $this->deleteFromCloudinary($producto->imagen_public_id);
            }
            $producto->delete();

            return response()->json(['message' => 'Producto eliminado']);
        } catch (\Exception $e) {
            Log::error('Producto destroy error: '.$e->getMessage());

            return response()->json(['message' => 'Error al eliminar producto'], 500);
        }
    }

    protected function uploadToCloudinary($file)
    {
        $cloudinary = env('CLOUDINARY_URL');
        if (! $cloudinary) {
            return null;
        }

        // CLOUDINARY_URL format: cloudinary://API_KEY:API_SECRET@CLOUD_NAME
        try {
            $parts = parse_url($cloudinary);
            if (! $parts || ! isset($parts['user']) || ! isset($parts['pass']) || ! isset($parts['host'])) {
                return null;
            }

            $apiKey = $parts['user'];
            $apiSecret = $parts['pass'];
            $cloudName = $parts['host'];

            $timestamp = time();
            $signature = sha1('timestamp='.$timestamp.$apiSecret);

            $url = "https://api.cloudinary.com/v1_1/{$cloudName}/image/upload";

            $post = [
                'api_key' => $apiKey,
                'timestamp' => $timestamp,
                'signature' => $signature,
            ];

            $curlFile = curl_file_create($file->getRealPath(), $file->getMimeType(), $file->getClientOriginalName());
            $post['file'] = $curlFile;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $result = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);

            if ($err) {
                Log::error('Cloudinary upload curl error: '.$err);

                return null;
            }

            $decoded = json_decode($result, true);

            return $decoded;
        } catch (\Exception $e) {
            Log::error('Cloudinary upload error: '.$e->getMessage());

            return null;
        }
    }

    protected function deleteFromCloudinary($publicId)
    {
        $cloudinary = env('CLOUDINARY_URL');
        if (! $cloudinary) {
            return false;
        }

        $parts = parse_url($cloudinary);
        if (! $parts || ! isset($parts['user']) || ! isset($parts['pass']) || ! isset($parts['host'])) {
            return false;
        }

        $apiKey = $parts['user'];
        $apiSecret = $parts['pass'];
        $cloudName = $parts['host'];

        // Admin delete: use HTTP DELETE to resources endpoint with basic auth
        $url = "https://api.cloudinary.com/v1_1/{$cloudName}/resources/image/upload";
        $query = http_build_query(['public_ids' => [$publicId]]);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url.'?'.$query);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $apiKey.':'.$apiSecret);
        $result = curl_exec($ch);
        $err = curl_error($ch);
        curl_close($ch);

        if ($err) {
            Log::error('Cloudinary delete curl error: '.$err);

            return false;
        }

        return true;
    }
}
