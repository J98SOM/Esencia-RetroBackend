<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Producto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProductoController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) ($request->query('q') ?? $request->query('search') ?? ''));
        if ($q) {
            $query = Producto::query();
            if (is_numeric($q)) {
                $query->where('id', $q)->orWhere('nombre', 'like', "%{$q}%");
            } else {
                $query->where('nombre', 'like', "%{$q}%");
            }
            $results = $query->select('id', 'nombre', 'categoria', 'precio', 'imagen_url')->limit(15)->get();

            return response()->json($results);
        }

        return response()->json(Producto::select('id', 'nombre', 'categoria', 'precio', 'imagen_url')->get());
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
                } else {
                    $data['imagen_url'] = $this->storeLocalImage($request->file('imagen'));
                    $data['imagen_public_id'] = null;
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
                } else {
                    $data['imagen_url'] = $this->storeLocalImage($request->file('imagen'));
                    $data['imagen_public_id'] = null;
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
            $this->deleteStoredImage($producto);
            $producto->delete();

            return response()->json(['message' => 'Producto eliminado']);
        } catch (\Exception $e) {
            Log::error('Producto destroy error: '.$e->getMessage());

            return response()->json(['message' => 'Error al eliminar producto'], 500);
        }
    }

    protected function uploadToCloudinary($file)
    {
        $cloudinary = $this->cloudinaryConfig();
        if (! $cloudinary) {
            return null;
        }

        try {
            $apiKey = $cloudinary['api_key'];
            $apiSecret = $cloudinary['api_secret'];
            $cloudName = $cloudinary['cloud_name'];

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
        $cloudinary = $this->cloudinaryConfig();
        if (! $cloudinary) {
            return false;
        }

        $apiKey = $cloudinary['api_key'];
        $apiSecret = $cloudinary['api_secret'];
        $cloudName = $cloudinary['cloud_name'];

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

    protected function deleteStoredImage(Producto $producto): void
    {
        if ($producto->imagen_public_id) {
            $this->deleteFromCloudinary($producto->imagen_public_id);

            return;
        }

        if (! $producto->imagen_url) {
            return;
        }

        $path = parse_url($producto->imagen_url, PHP_URL_PATH);
        if (! $path) {
            return;
        }

        if (str_starts_with($path, '/img/productos/')) {
            $fullPath = public_path(ltrim($path, '/'));
            if (is_file($fullPath)) {
                unlink($fullPath);
            }
        }
    }

    protected function storeLocalImage($file): string
    {
        $directory = public_path('img/productos');

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        $filename = uniqid('producto_', true).'_'.$file->getClientOriginalName();
        $file->move($directory, $filename);

        return '/img/productos/'.$filename;
    }

    protected function cloudinaryConfig(): ?array
    {
        $url = env('CLOUDINARY_URL');

        if (! is_string($url) || $url === '') {
            return null;
        }

        $pattern = '/^cloudinary:\/\/([^:]+):([^@]+)@([^\/]+)$/';
        if (! preg_match($pattern, $url, $matches)) {
            return null;
        }

        return [
            'api_key' => rawurldecode($matches[1]),
            'api_secret' => rawurldecode($matches[2]),
            'cloud_name' => rawurldecode($matches[3]),
        ];
    }
}
