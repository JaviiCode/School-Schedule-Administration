<?php

require_once '..\Model\GestorHorario.php';
require_once '..\Model\Validaciones.php'; // Asegúrate de que aquí esté la función verificarInsertar

class HorarioController
{
    private $gestorDeHorarios;

    function __construct()
    {
        // Inicializamos el gestor de horarios
        $this->gestorDeHorarios = new GestorHorario();

        try {
            if (isset($_POST['insertar'])) {
                // Obtenemos los datos necesarios desde el formulario
                verificarDatosInsertar();
                $franjaHorario = new FranjaHorario($_POST['curso'] ?? null, $_POST['clase'] ?? null, $_POST['materia'] ?? null, $_POST['dia'] ?? null, $_POST['hora'] ?? null, $_POST['tipoFranja'] ?? null, $_POST['color'] ?? null);
                verificarInsertar($franjaHorario);
                $this->gestorDeHorarios->insertarHora();
            }

            // Otras funcionalidades aún no implementadas (eliminar, cargar, generar)
            if (isset($_POST['eliminar'])) {
                $this->gestorDeHorarios->eliminarHora();
                
            }

            if (isset($_POST['cargar'])) {
                $this->gestorDeHorarios->cargarhorario();
            }

            if (isset($_POST['generar'])) {
                // Aquí iría la lógica para generar un nuevo horario
            }

        } catch (Exception $e) {
            // Mostramos el error en caso de que ocurra alguna excepción
            echo '<p style="color:red">Error: ', $e->getMessage(), "</p><br>";
        }
    }

    // Función para mostrar el horario
    function desplegarHorario()
    {
        $this->gestorDeHorarios->mostrarHorarios();
    }
}
?>