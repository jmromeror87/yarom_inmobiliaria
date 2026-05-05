<?php

/*
|--------------------------------------------------------------------------
| YarOM ERP - Soluciones de Gestión
|--------------------------------------------------------------------------
| Proyecto privado desarrollado por:
| Ingeniero Jhoan Romero Rivera
| LinkedIn: https://linkedin.com/in/jmromeror87
|
| Módulo: \1
| Archivo: NumeroALetras.php
| Fecha: CURRENT_DAY/05/2026
| Versión: v1.0
|--------------------------------------------------------------------------
*/
        

namespace App\Helpers;

class NumeroALetras
{
    private static array $unidades = [
        '', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE',
        'DIEZ', 'ONCE', 'DOCE', 'TRECE', 'CATORCE', 'QUINCE', 'DIECISÉIS', 'DIECISIETE',
        'DIECIOCHO', 'DIECINUEVE', 'VEINTE', 'VEINTIUNO', 'VEINTIDÓS', 'VEINTITRÉS',
        'VEINTICUATRO', 'VEINTICINCO', 'VEINTISÉIS', 'VEINTISIETE', 'VEINTIOCHO', 'VEINTINUEVE'
    ];

    private static array $decenas = [
        '', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA',
        'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'
    ];

    private static array $centenas = [
        '', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS',
        'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'
    ];

    public static function convertir(float $numero, string $moneda = 'PESOS MONEDA CORRIENTE'): string
    {
        $entero    = (int) $numero;
        $decimales = round(($numero - $entero) * 100);
        $letras    = self::convertirEntero($entero);
        if ($entero === 1) $letras = 'UN';
        $resultado = $letras . ' ' . $moneda;
        $resultado .= $decimales > 0
            ? ' CON ' . str_pad($decimales, 2, '0', STR_PAD_LEFT) . '/100'
            : ' EXACTOS';
        return $resultado;
    }

    private static function convertirEntero(int $numero): string
    {
        if ($numero === 0)   return 'CERO';
        if ($numero < 0)     return 'MENOS ' . self::convertirEntero(abs($numero));
        if ($numero < 30)    return self::$unidades[$numero];
        if ($numero < 100) {
            $d = (int)($numero / 10);
            $u = $numero % 10;
            return self::$decenas[$d] . ($u ? ' Y ' . self::$unidades[$u] : '');
        }
        if ($numero === 100) return 'CIEN';
        if ($numero < 1000) {
            $c = (int)($numero / 100);
            $r = $numero % 100;
            return self::$centenas[$c] . ($r ? ' ' . self::convertirEntero($r) : '');
        }
        if ($numero < 2000) {
            $r = $numero % 1000;
            return 'MIL' . ($r ? ' ' . self::convertirEntero($r) : '');
        }
        if ($numero < 1000000) {
            $m = (int)($numero / 1000);
            $r = $numero % 1000;
            return self::convertirEntero($m) . ' MIL' . ($r ? ' ' . self::convertirEntero($r) : '');
        }
        if ($numero < 2000000) {
            $r = $numero % 1000000;
            return 'UN MILLÓN' . ($r ? ' ' . self::convertirEntero($r) : '');
        }
        if ($numero < 1000000000) {
            $m = (int)($numero / 1000000);
            $r = $numero % 1000000;
            return self::convertirEntero($m) . ' MILLONES' . ($r ? ' ' . self::convertirEntero($r) : '');
        }
        return (string)$numero;
    }

    public static function diaEnLetras(int $dia): string
    {
        $letras = [
            1=>'uno',2=>'dos',3=>'tres',4=>'cuatro',5=>'cinco',6=>'seis',7=>'siete',
            8=>'ocho',9=>'nueve',10=>'diez',11=>'once',12=>'doce',13=>'trece',
            14=>'catorce',15=>'quince',16=>'dieciséis',17=>'diecisiete',18=>'dieciocho',
            19=>'diecinueve',20=>'veinte',21=>'veintiún',22=>'veintidós',23=>'veintitrés',
            24=>'veinticuatro',25=>'veinticinco',26=>'veintiséis',27=>'veintisiete',
            28=>'veintiocho',29=>'veintinueve',30=>'treinta',31=>'treinta y uno',
        ];
        return $letras[$dia] ?? (string)$dia;
    }
}
