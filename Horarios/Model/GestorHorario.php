<?php

require_once '..\Model\FranjaHorario.php';
require_once '..\Model\Validaciones.php';

class GestorHorario
{
    private $archivoHorarios = '..\Horarios\horarios.dat';
    private $directorioServidor = '../Horarios/';
    private $horariosCargados = [];


    public function __construct()
    {
        // Aseguramos que el archivo de horarios existe si no, lo creo
        $this->archivoHorarios = '..\Horarios\horarios.dat';
        if(!is_dir('..\Horarios')){
            mkdir('..\Horarios', 0777, true);
        }
        if (!file_exists($this->archivoHorarios)) {
            $archivoHorario = fopen($this->archivoHorarios, 'w');
            fclose($archivoHorario); 
        }
    }

    public function getHorariosCargados()
    {
        return $this->horariosCargados;
    }

    protected function cargarArchivo()
    {
        // Leemos el contenido del archivo de horarios
        $contenidoArchivo = file_get_contents($this->archivoHorarios);
        $registros = explode('@', $contenidoArchivo); // Separar cada registro

        $this->horariosCargados = []; // Inicializamos el array de horarios

        foreach ($registros as $registro) {
            $campos = explode(';', $registro);

            // Asegurarse de que hay suficientes campos en cada registro
            if (count($campos) < 7) {
                continue;
            }

            // Asignamos valores predeterminados en caso de que no existan
            $curso = isset($campos[0]) ? $campos[0] : '';
            $clase = isset($campos[4]) ? $campos[4] : '';
            $materia = isset($campos[3]) ? $campos[3] : '';
            $dia = isset($campos[1]) ? $campos[1] : '';
            $hora = isset($campos[2]) ? $campos[2] : '';

            // Si el registro es de un tipo especial, creamos la franja sin curso ni clase
            if ($curso == "_" || $clase == "_") {
                $tipoFranja = isset($campos[3]) ? $campos[3] : '';
                $franjaHorario = new FranjaHorario(
                    "",
                    "",
                    "",
                    $dia,
                    $hora,
                    $tipoFranja,
                    isset($campos[5]) ? $campos[5] : ''
                );
            } else {
                // De lo contrario, creamos la franja con todos los datos completos
                $franjaHorario = new FranjaHorario(
                    $curso,
                    $clase,
                    $materia, // Materia
                    $dia, // Día
                    $hora, // Hora
                    isset($campos[6]) ? $campos[6] : '', // Tipo de franja
                    isset($campos[5]) ? $campos[5] : '' // Color
                );
            }

            // Verificamos si el día ya está en el array de franjas
            if (!isset($this->horariosCargados[$franjaHorario->getDia()])) {
                $this->horariosCargados[$franjaHorario->getDia()] = [];
            }
            // Asignamos la franja al día y hora correspondientes
            $this->horariosCargados[$franjaHorario->getDia()][$franjaHorario->getHora()] = $franjaHorario;
        }
    }

    public function insertarHora()
    {
        $curso = $_POST['curso'];
        $clase = $_POST['clase'];
        $materia = Materia::from($_POST['materia']);
        $dia = Semana::from($_POST['dia']);
        $hora = Hora::from($_POST['hora']);
        $tipoFranja = TipoFranja::from($_POST['tipoFranja']);
        $color = $_POST['color'];

        $this->cargarArchivo();

        // Continuar con el proceso de inserción
        $contenidoArchivo = file_get_contents($this->archivoHorarios);
        $registrosExistentes = explode('@', $contenidoArchivo); // Separar en registros

        // Crear una nueva entrada para el horario
        $nuevaLinea = implode(';', [
            $curso,
            $dia->value,
            $hora->codigoHora(),
            $materia->value,
            $clase,
            $color,
            $tipoFranja->value
        ]);

        // Añadimos la nueva entrada al conjunto de registros
        $registrosExistentes[] = $nuevaLinea;

        // Sobrescribimos el archivo con la nueva información
        $contenidoActualizado = implode('@', $registrosExistentes);
        $archivoParaEscritura = fopen($this->archivoHorarios, "w");

        if ($archivoParaEscritura === false) {
            throw new Exception("No se pudo encontrar el archivo en: '" . $this->archivoHorarios . "'");
        }

        fwrite($archivoParaEscritura, $contenidoActualizado);
        fclose($archivoParaEscritura);
    }

    public function mostrarHorarios()
    {
        // Asegurarse de que los horarios estén cargados antes de la visualización
        $this->cargarArchivo();

        // Comenzamos creando la estructura de la tabla HTML
        echo "<table class='table' style='border-spacing: 0; width: 100%;'>";

        // Cabecera de la tabla con los días de la semana
        echo "<thead style='background-color: #333; color: white;'>";
        echo "<tr><th style='text-align: center; padding: 10px;'>HORA</th>";
        foreach (Semana::cases() as $dia) {
            echo "<th style='text-align: center; padding: 10px; border-left: 1px solid #fff;'>{$dia->name}</th>";
        }
        echo "</tr></thead>";

        // Iteramos sobre las franjas horarias
        echo "<tbody>";
        foreach (Hora::cases() as $hora) {
            echo "<tr>";

            // Primera columna para mostrar la hora
            echo "<td style='text-align: center; font-weight: bold; padding: 10px;'>" . strtoupper($hora->value) . "</td>";

            // Recorremos los días de la semana para cada hora
            foreach (Semana::cases() as $dia) {
                $diaCodigo = $dia->value;
                $horaCodigo = $hora->codigoHora();

                // Verificamos si hay una franja horaria asignada
                if (isset($this->horariosCargados[$diaCodigo][$horaCodigo]) || !empty($this->horariosCargados[$diaCodigo][$horaCodigo])) {
                    $franja = $this->horariosCargados[$diaCodigo][$horaCodigo];
                    $color = $franja->getColor();

                    if ($franja->getTipoFranja() == TipoFranja::Complementaria->value) {
                        // Mostramos la franja en la celda correspondiente
                        echo "<td style='background-color: ".Color::Azul->value."; text-align: center; padding: 10px;'>";
                        echo "<div>{$franja->getMateria()}</div>";
                        echo "</td>";

                    } else if($franja->getTipoFranja() == TipoFranja::Recreo->value) {
                        // Mostramos la franja en la celda correspondiente
                        echo "<td style='background-color: ".Color::AzulClaro->value."; text-align: center; padding: 10px;'>";
                        echo "<div>".strtoupper(TipoFranja::from($franja->getTipoFranja())->name)."</div>";
                        echo "</td>";
                    } else {
                        // Mostramos la franja en la celda correspondiente
                        echo "<td style='background-color: $color; text-align: center; padding: 10px;'>";
                        echo "<div><strong>{$franja->getCurso()}</strong></div>";
                        echo "<div>{$franja->getMateria()}</div>";
                        echo "<div>{$franja->getClase()}</div>";
                        echo "<div>{$franja->getTipoFranja()}</div>";
                        echo "</td>";
                    }

                } else {
                    // Celda vacía si no hay franja horaria
                    echo "<td style='text-align: center; padding: 10px;'></td>";
                }
            }

            echo "</tr>";
        }
        echo "</tbody>";

        // Cerramos la tabla
        echo "</table>";
    }

    public function subirFichero($rutaFicheroSubido)
    {
        // Comprobar si el archivo subido existe y es válido
        if (file_exists($rutaFicheroSubido) && is_readable($rutaFicheroSubido)) {
            // Leer el contenido del archivo subido
            $contenido = file_get_contents($rutaFicheroSubido);

            // Asegurarse de que la carpeta 'horarios' existe, si no, crearla
            if (!is_dir("horarios")) {
                mkdir("horarios", 0777, true);
            }

            // Sobrescribir el contenido de horarios.dat con el contenido del archivo subido
            file_put_contents($this->archivoHorarios, $contenido);

            echo "El archivo se ha subido y el horario ha sido actualizado con éxito.";
        } else {
            // Manejo de errores si no se puede acceder al archivo subido
            echo "Error: No se pudo procesar el archivo subido. Verifique que sea válido.";
        }
    }

    private function reemplazarArchivo($fichero)
    {
        try {
            // Ruta al archivo temporal
            $rutaTemporal = $this->directorioServidor . $fichero["name"];

            // Leemos el contenido del archivo nuevo
            $contenidoNuevo = file_get_contents($rutaTemporal);

            // Abrimos el archivo original para sobrescribir
            $archivoOriginal = fopen($this->archivoHorarios, "w");
            if ($archivoOriginal === false) {
                throw new Exception("Error: No se pudo encontrar el archivo en la ruta indicada.");
            }

            // Sobrescribimos el contenido
            fwrite($archivoOriginal, $contenidoNuevo);
            fclose($archivoOriginal);

        } catch (Exception $e) {
            throw new Exception("<h2 style='color:red'>Error: Ocurrió un problema al sobrescribir el archivo.</h2>");
        }

        return true;
    }

    public function eliminarHora()
    {
        // Obtengo datos
        $dia = $_POST['dia'] ?? null;
        $hora = $_POST['hora'] ?? null;
        if (empty($dia) || empty($hora)) {
            throw new Exception("Error Campos: El campo no puede ser vacío");
        }
        //Cargamos el horario
        $this->cargarArchivo();

        // Verificamos si existe la franja horaria para ese día y hora.
        if (isset($this->horariosCargados[$dia][Hora::from($hora)->codigoHora()])) {
            // Si la franja existe, se elimina con `unset`.
            unset($this->horariosCargados[$dia][Hora::from($hora)->codigoHora()]);

            // Guardar los cambios en el archivo tras eliminar la franja.
            $this->guardarCambiosArchivo();
            // Volvemos a cargar el archivo después de guardar los cambios.
            $this->cargarArchivo();
        } else {
            // Si no se encontró la franja horaria, lanzamos una excepción indicando que no existe para ese día y hora.
            throw new Exception("Error: No se encontró ninguna franja horaria para ese día y hora.");
        }
    }
    // Función auxiliar para guardar los cambios en el archivo
    private function guardarCambiosArchivo()
    {
        $registros = [];

        // Iteramos sobre los horarios cargados. Cada entrada en '$this->horariosCargados' tiene un 'día' como clave.
        foreach ($this->horariosCargados as $dia => $horas) {
            // Para cada 'día', iteramos sobre las franjas horarias (las 'horas').
            foreach ($horas as $hora => $franjaHorario) {
                // Guardamos la información de cada franja horaria en un array.
                $registros[] = implode(';', [
                    $franjaHorario->getCurso(),
                    $franjaHorario->getDia(),    
                    $franjaHorario->getHora(),    
                    $franjaHorario->getMateria(),  
                    $franjaHorario->getClase(),    
                    $franjaHorario->getColor(),    
                    $franjaHorario->getTipoFranja()
                ]);
            }
        }

        // Unimos todos los registros con el separador '@' para generar el contenido que se va a escribir en el archivo.
        $contenidoActualizado = implode('@', $registros);
        // Abrimos el archivo en modo escritura ('w') para sobreescribir su contenido.
        $archivoParaEscritura = fopen($this->archivoHorarios, "w");
        // Si no se puede abrir el archivo, lanzamos una excepción.
        if ($archivoParaEscritura === false) {
            throw new Exception("No se pudo encontrar el archivo en: '" . $this->archivoHorarios . "'");
        }

        // Escribimos el contenido actualizado en el archivo.
        fwrite($archivoParaEscritura, $contenidoActualizado);

        // Cerramos el archivo después de escribir.
        fclose($archivoParaEscritura);
    }

    public function cargarhorario()
    {
        // Comprobar si se ha subido un archivo y si no hay errores en la carga
        if (isset($_FILES['fhorario']) && $_FILES['fhorario']['error'] === UPLOAD_ERR_OK) {
            // Obtener la ruta temporal del archivo que se ha subido
            $archivoTemporal = $_FILES['fhorario']['tmp_name'];

            // Asegurarse de que el archivo tiene contenido
            if (filesize($archivoTemporal) > 0) {
                // Definir el directorio para almacenar archivos temporales y crearlo si no existe
                $directorioTemporal = '../datos_temporales';
                if (!is_dir($directorioTemporal)) {
                    mkdir($directorioTemporal, 0777, true);
                }

                // Establecer la ruta donde se guardará el archivo temporal
                $rutaDestino = $directorioTemporal . '/datos_temp.dat';

                // Intentar mover el archivo subido al directorio designado
                if (move_uploaded_file($archivoTemporal, $rutaDestino)) {
                    try {
                        // Crear una instancia del gestor de horarios
                        $gestorHorario = new GestorHorario();

                        // Cargar el contenido del archivo temporal en el sistema
                        $gestorHorario->subirFichero($rutaDestino);

                        // Redirigir al usuario a la vista de horarios si todo ha ido bien
                        header("Location: ../View/HorariosView.php");
                        exit;
                    } catch (Exception $e) {
                        // Capturar y mostrar cualquier error que ocurra durante el proceso
                        echo $e->getMessage();
                    }
                } else {
                    echo "Error: No se pudo trasladar el archivo al directorio de destino.";
                }
            } else {
                echo "Error: El archivo que se ha subido está vacío.";
            }
        } else {
            echo "Error: No se ha recibido ningún archivo o ha habido un problema durante la carga.";
        }
    }
}
