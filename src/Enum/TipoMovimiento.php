<?php

namespace App\Enum;

/**
 * Enumeración de tipos de movimientos de stock permitidos
 *
 * Esta enumeración define los tipos válidos de movimientos que pueden
 * realizarse en el inventario. Cada caso representa una operación específica
 * que afecta al stock de un producto.
 *
 * Casos disponibles:
 * - ENTRADA: Incrementa el stock (compras, devoluciones recibidas)
 * - SALIDA: Decrementa el stock (ventas, pérdidas)
 * - AJUSTE: Corrige el stock (inventarios físicos, correcciones)
 *
 * @author Jhonatan Fernandez
 */
enum TipoMovimiento: string
{
    /**
     * Entrada de stock: aumenta la cantidad disponible
     * Ejemplos: compras, devoluciones de clientes, transferencias recibidas
     */
    case ENTRADA = 'ENTRADA';

    /**
     * Salida de stock: disminuye la cantidad disponible
     * Ejemplos: ventas, pérdidas, mermas, transferencias enviadas
     */
    case SALIDA = 'SALIDA';

    /**
     * Ajuste de inventario: corrige discrepancias
     * Ejemplos: inventario físico, correcciones, regularizaciones
     */
    case AJUSTE = 'AJUSTE';

    /**
     * Obtiene la etiqueta legible para humanos del tipo de movimiento
     *
     * Este método convierte el valor técnico del enum en un texto
     * amigable para mostrar en la interfaz de usuario.
     *
     * @return string Etiqueta legible del tipo de movimiento
     */
    public function label(): string
    {
        return match($this) {
            self::ENTRADA => 'Entrada',
            self::SALIDA => 'Salida',
            self::AJUSTE => 'Ajuste de inventario'
        };
    }
}
