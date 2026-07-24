<?php

require_once __DIR__ . '/../Models/Cliente.php';

/**
 * Clase ClienteController
 * -----------------------------------------------------
 * Responsable única: coordinar el catálogo simple de
 * Clientes con el Modelo Cliente.
 *
 * NOTA: no es el controlador de un módulo independiente.
 * Solo existe para alimentar el selector "Cliente" del
 * formulario de Trabajos (modal "+ Nuevo Cliente").
 * No contiene SQL. No contiene HTML.
 * -----------------------------------------------------
 */
class ClienteController
{
    private Cliente $clienteModel;

    public function __construct()
    {
        $this->clienteModel = new Cliente();
    }

    /**
     * Devuelve el listado de clientes para el selector
     * del formulario de Trabajos.
     */
    public function listar(): array
    {
        return $this->clienteModel->listar();
    }

    /**
     * Registra un cliente nuevo a partir de los datos
     * enviados desde el modal "+ Nuevo Cliente".
     * Único campo obligatorio: nombre_cliente.
     */
    public function registrar(array $datosFormulario): array
    {
        $nombre = trim($datosFormulario['nombre_cliente'] ?? '');

        if ($nombre === '') {
            return [
                'exito'   => false,
                'mensaje' => 'El nombre del cliente es obligatorio.',
            ];
        }

        $idCreado = $this->clienteModel->crear([
            'nombre_cliente' => $nombre,
            'ruc'            => trim($datosFormulario['ruc'] ?? ''),
            'telefono'       => trim($datosFormulario['telefono'] ?? ''),
            'correo'         => trim($datosFormulario['correo'] ?? ''),
            'observaciones'  => trim($datosFormulario['observaciones'] ?? ''),
        ]);

        if ($idCreado === false) {
            return [
                'exito'   => false,
                'mensaje' => 'Ocurrió un error al registrar el cliente.',
            ];
        }

        return [
            'exito'          => true,
            'mensaje'        => 'Cliente registrado correctamente.',
            'id_cliente'     => $idCreado,
            'nombre_cliente' => $nombre,
        ];
    }
}
