<?php
// Importes en céntimos para evitar errores de redondeo con dinero.
function venta_texto(array $datos, string $clave): string {
    if (!isset($datos[$clave]) || !is_string($datos[$clave])) {
        throw new InvalidArgumentException("Falta el campo: $clave.");
    }
    return trim($datos[$clave]);
}

function venta_dinero(array $datos, string $clave, bool $opcional = false): int {
    $valor = $datos[$clave] ?? '';
    if ($opcional && ($valor === '' || $valor === null)) return 0;
    if (!is_string($valor) || !preg_match('/^\d{1,8}(?:\.\d{1,2})?$/D', $valor)) {
        throw new InvalidArgumentException("Ingresa un importe válido en $clave, con máximo dos decimales.");
    }
    $partes = explode('.', $valor);
    return ((int)$partes[0] * 100) + (int)str_pad($partes[1] ?? '', 2, '0');
}

function validar_venta(array $datos): array {
    $dni = venta_texto($datos, 'dni');
    $telefono = venta_texto($datos, 'telefono');
    if (!preg_match('/^[0-9]{8}$/D', $dni)) {
        throw new InvalidArgumentException('El DNI debe tener 8 dígitos.');
    }
    if (!preg_match('/^9[0-9]{8}$/D', $telefono)) {
        throw new InvalidArgumentException('El teléfono debe tener 9 dígitos y empezar con 9.');
    }
    $id = venta_texto($datos, 'id');
    if (!preg_match('/^[1-9][0-9]{0,9}$/D', $id)) {
        throw new InvalidArgumentException('Selecciona un equipo válido.');
    }
    $columna = venta_texto($datos, 'columna');
    if (!in_array($columna, ['stock_cab1', 'stock_cab2', 'stock_modelo'], true)) {
        throw new InvalidArgumentException('Selecciona una tienda válida.');
    }
    $solicitud = venta_texto($datos, 'solicitud');
    if (!preg_match('/^[a-f0-9]{64}$/D', $solicitud)) {
        throw new InvalidArgumentException('Recarga el formulario para registrar una venta.');
    }
    $tipo = venta_texto($datos, 'tipo');
    $financiera = '';
    $inicial = 0;
    if ($tipo === 'contado') {
        $precio = venta_dinero($datos, 'precio_contado');
        $descuento = venta_dinero($datos, 'descuento_contado', true);
        $base = $precio;
    } elseif ($tipo === 'credito') {
        $financiera = venta_texto($datos, 'financiera');
        if ($financiera === '' || strlen($financiera) > 400 || preg_match('/[\x00-\x1f]/', $financiera)) {
            throw new InvalidArgumentException('Ingresa la empresa de financiamiento (máximo 100 caracteres).');
        }
        if (preg_match_all('/./us', $financiera) > 100) {
            throw new InvalidArgumentException('La empresa de financiamiento no puede superar 100 caracteres.');
        }
        $precio = venta_dinero($datos, 'precio_credito');
        $inicial = venta_dinero($datos, 'inicial');
        if ($inicial > $precio) {
            throw new InvalidArgumentException('La inicial no puede superar el precio crédito.');
        }
        $descuento = venta_dinero($datos, 'descuento_inicial', true);
        $base = $inicial;
    } else {
        throw new InvalidArgumentException('Selecciona contado o crédito.');
    }
    if ($precio <= 0) throw new InvalidArgumentException('El precio debe ser mayor a cero.');
    if ($descuento > $base) throw new InvalidArgumentException('El descuento no puede superar el importe.');
    return [
        'solicitud' => $solicitud, 'id' => (int)$id, 'columna' => $columna,
        'dni' => $dni, 'telefono' => $telefono, 'tipo' => $tipo,
        'financiera' => $financiera, 'precio' => $precio,
        'inicial' => $inicial, 'descuento' => $descuento, 'total' => $base - $descuento
    ];
}
