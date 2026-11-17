<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Dedoc\Scramble\Attributes\Group;
use Dedoc\Scramble\Attributes\QueryParameter;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

#[Group('APIs externas')]
class ExternaController extends Controller
{
    /**
     * Clima actual en Santa Cruz
     *
     * Consulta la API gratuita Open-Meteo (no pide registro ni clave)
     * y devuelve los datos principales en español.
     */
    public function clima(): JsonResponse
    {
        try {
            $respuesta = Http::timeout(10)->get(config('externas.clima.url'), [
                'latitude' => -17.78,
                'longitude' => -63.18,
                'current' => 'temperature_2m,relative_humidity_2m,wind_speed_10m,weather_code',
                'timezone' => 'America/La_Paz',
            ]);
        } catch (ConnectionException) {
            return $this->error(502, 'API externa sin respuesta', 'No se pudo conectar con Open-Meteo.');
        }

        if ($respuesta->failed()) {
            return $this->error(502, 'Error de la API externa', 'Open-Meteo respondió con el código '.$respuesta->status().'.');
        }

        $actual = $respuesta->json('current');

        return response()->json([
            'data' => [
                'ciudad' => 'Santa Cruz de la Sierra',
                'hora' => $actual['time'],
                'temperatura_c' => $actual['temperature_2m'],
                'humedad_pct' => $actual['relative_humidity_2m'],
                'viento_kmh' => $actual['wind_speed_10m'],
                'estado' => $this->describirClima((int) $actual['weather_code']),
                'fuente' => 'Open-Meteo (gratuita, sin registro)',
            ],
        ], 200);
    }

    /**
     * Convertir monedas
     *
     * Consulta ExchangeRate-API, que pide registrarse y enviar una clave (API key).
     * La clave se guarda en el archivo .env como EXCHANGERATE_KEY.
     */
    #[QueryParameter('monto', description: 'Cantidad a convertir.', type: 'number', default: 1, example: 100)]
    #[QueryParameter('de', description: 'Moneda de origen (código de 3 letras).', type: 'string', default: 'USD')]
    #[QueryParameter('a', description: 'Moneda de destino (código de 3 letras).', type: 'string', default: 'BOB')]
    public function tipoCambio(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'monto' => 'nullable|numeric|min:0',
            'de' => 'nullable|string|size:3',
            'a' => 'nullable|string|size:3',
        ]);

        $clave = config('externas.tipo_cambio.key');

        if (! $clave) {
            return $this->error(503, 'Servicio no configurado', 'Falta la clave EXCHANGERATE_KEY en el archivo .env.');
        }

        $monto = (float) ($datos['monto'] ?? 1);
        $de = strtoupper($datos['de'] ?? 'USD');
        $a = strtoupper($datos['a'] ?? 'BOB');

        try {
            $respuesta = Http::timeout(10)->get(config('externas.tipo_cambio.url')."/{$clave}/pair/{$de}/{$a}/{$monto}");
        } catch (ConnectionException) {
            return $this->error(502, 'API externa sin respuesta', 'No se pudo conectar con ExchangeRate-API.');
        }

        if ($respuesta->json('result') !== 'success') {
            return $this->error(502, 'Error de la API externa', 'ExchangeRate-API respondió: '.$respuesta->json('error-type', 'error desconocido').'.');
        }

        return response()->json([
            'data' => [
                'monto' => $monto,
                'de' => $de,
                'a' => $a,
                'tasa' => $respuesta->json('conversion_rate'),
                'resultado' => $respuesta->json('conversion_result'),
                'actualizado' => $respuesta->json('time_last_update_utc'),
                'fuente' => 'ExchangeRate-API (gratuita con registro y clave)',
            ],
        ], 200);
    }

    // Respuesta de error con el mismo formato que el resto de la API
    private function error(int $codigo, string $titulo, string $detalle): JsonResponse
    {
        return response()->json([
            'error' => [
                'codigo' => $codigo,
                'titulo' => $titulo,
                'detalle' => $detalle,
            ],
        ], $codigo);
    }

    // Traduce el código de clima de Open-Meteo (estándar WMO) a un texto corto
    private function describirClima(int $codigo): string
    {
        return match (true) {
            $codigo === 0 => 'Despejado',
            $codigo <= 3 => 'Parcialmente nublado',
            $codigo <= 48 => 'Niebla',
            $codigo <= 57 => 'Llovizna',
            $codigo <= 67 => 'Lluvia',
            $codigo <= 77 => 'Nieve',
            $codigo <= 82 => 'Chubascos',
            default => 'Tormenta',
        };
    }
}
