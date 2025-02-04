<?php
// Condicion verificarFichero
function verificarFichero($fichero)
{
    if (!file_exists($fichero["tmp_name"])) {
        throw new Exception("El archivo no existe.");
    }

    $tipoMime = mime_content_type($fichero["tmp_name"]);
    if ($tipoMime !== "text/csv") {
        throw new Exception("El archivo no es un archivo válido.");
    }

    $tamFichero = $fichero["size"];
    if ($tamFichero > 256 * 1024 * 1024) {
        throw new Exception("El fichero es demasiado grande, no se puede procesar.");
    }
}

function verificarDatosInsertar() {
    if (!isset($_POST["tipoFranja"]) || empty($_POST["tipoFranja"])) {
        throw new Exception("Error Campos: El campo no puede estar vacío.");

    } else {
        $tipoFranja = $_POST["tipoFranja"];

        // validar primero los valores generales necesarios
        if (!isset($_POST["dia"]) || empty($_POST["dia"]) || !isset($_POST["hora"]) || empty($_POST["hora"]) || !isset($_POST["materia"]) || empty($_POST["materia"])) {
            throw new Exception("Error Campos: El campo no puede estar vacío.");
            
        } else {
            // comprobar si es lectiva
            if ($tipoFranja == TipoFranja::Lectiva->value) {// para las horas lectivas hacen falta este extra de valores
                if (!isset($_POST["curso"]) || empty($_POST["curso"]) || !isset($_POST["color"]) || empty($_POST["color"]) || !isset($_POST["clase"]) || empty($_POST["clase"])) {
                    throw new Exception("Error Campos: El campo no puede estar vacío.");
                }
            }
        }
    }
}


function verificarInsertar($franjaHorario)
{
    // Leer el archivo de horarios y dividirlo en registros
    $registros = explode('@', file_get_contents('..\Horarios\horarios.dat'));

    // Definir los contadores
    $materiasPorDia = 0;
    $horasLectivasPorDia = 0;
    $horasNoLectivasPorDia = 0;
    $horasLectivasSemana = 0;
    $horasNoLectivasSemana = 0;
    $horasTutoria = 0;
    $guardiaHoraAnterior = false;
    $guardiaHoraSiguiente = false;
    $guardiaHora2Anterior = false;
    $guardiaHora2Siguiente = false;

    // Obtener los atributos del objeto franjaHorario
    $dia = $franjaHorario->getDia();
    $hora = $franjaHorario->getHora();
    $materia = $franjaHorario->getMateria();
    $codigoHora = Hora::from($hora)->codigoHora();
    $tipoFranja = $franjaHorario->getTipoFranja();

    foreach ($registros as $registro) {
        $campos = explode(';', $registro);
        if (count($campos) < 7) {
            continue;
        }
        // condicion 1
        if ($campos[1] == $dia && $codigoHora == $campos[2]) {
            throw new Exception("Error Horario: La franja de hora ya existe, elige otra que esté disponible.");
        }

        // condicion 2
        if ($campos[1] == $dia && $campos[3] == $materia) {
            $materiasPorDia++;
        }

        // condicion 3
        if ($campos[6] == TipoFranja::Lectiva->value && $campos[1] == $dia) {
            $horasLectivasPorDia++;
        }

        // condicion 4
        if ($campos[6] == TipoFranja::Complementaria->value && $campos[1] == $dia) {
            $horasNoLectivasPorDia++;
        }

        // condicion 5
        if ($campos[6] == TipoFranja::Lectiva->value) {
            $horasLectivasSemana++;
        }

        // condicion 6
        if ($campos[6] == TipoFranja::Complementaria->value) {
            $horasNoLectivasSemana++;
        }

        // condicion 8
        if ($campos[3] == Materia::REUNIÓN_DEPARTAMENTO->value && $campos[3] == $materia) {
            throw new Exception("Error Horario: Ya existe la franja horaria de reunión de departamento.");
        }

        // condicion 10
        if ($campos[3] == Materia::TUTORÍA->value) {
            $horasTutoria++;
        }

        // condicion 11
        if ($campos[1] == $dia && $campos[2] == ($codigoHora-1) && $campos[3] == Materia::GUARDIA->value) { //Comprobar si hay guardia la hora anterior
            $guardiaHoraAnterior = true;
        }
        if ($campos[1] == $dia && $campos[2] == ($codigoHora+1) && $campos[3] == Materia::GUARDIA->value) { //Comprobar si hay guardia la siguiente hora
            $guardiaHoraSiguiente = true;
        }
        if ($campos[1] == $dia && $campos[2] == ($codigoHora-2) && $campos[3] == Materia::GUARDIA->value) { //Comprobar si hay guardia 2 horas antes
            $guardiaHora2Anterior = true;
        }
        if ($campos[1] == $dia && $campos[2] == ($codigoHora+2) && $campos[3] == Materia::GUARDIA->value) { //Comprobar si hay guardia 2 horas despues
            $guardiaHora2Siguiente = true;
        }

    }

    //Condicion 2
    if ($materiasPorDia >= 3) {
        throw new Exception("Error Horario: La franja horaria, ha superado el número de horas por día.");
    }

    //Condicion 3
    if ($horasLectivasPorDia >= 5 && $tipoFranja == TipoFranja::Lectiva->value) {
        throw new Exception("Error Horario: El número de horas lectivas durante el día se ha superado.");
    }

    //Condicion 4
    if ($horasNoLectivasPorDia >= 3 && $tipoFranja == TipoFranja::Complementaria->value) {
        throw new Exception("Error Horario: El número de horas complementarias durante este día se ha superado.");
    }

    //Condicion 5
    if ($horasLectivasSemana >= 18 && $tipoFranja == TipoFranja::Lectiva->value) {
        throw new Exception("Error Horario: El número de horas lectivas durante la semana se ha superado.");
    }

    //Condicion 6
    if ($horasNoLectivasSemana >= 6 && $tipoFranja == TipoFranja::Complementaria->value) {
        throw new Exception("Error Horario: El número de horas complementarias durante la semana se ha superado.");
    }

    //Condicion 7
    if ($hora == Hora::Octava->value && $materia != Materia::REUNIÓN_DEPARTAMENTO->value && $dia == Semana::Martes->value) {
        throw new Exception("Error Horario: Esta franja horaria esta reservada únicamente para la hora complementaria 'Reunión de departamento', no se puede establecer.");
    }

    //Condicion 9
    if ($hora == Hora::Cuarta->value || $hora == Hora::Onceava->value) {
        if ($tipoFranja != TipoFranja::Recreo->value) {
            throw new Exception("Error Horario: Esta franja horaria está reservada para los recreos.");
        }
    }

    //Condicion 10
    if ($horasTutoria && $materia == Materia::TUTORÍA->value) {
        throw new Exception("Error Horario: La tutoría ya está establecida en el horario semanal.");
    }

    //Condicion 11
    if ($materia == Materia::GUARDIA->value && $guardiaHoraSiguiente && $guardiaHoraAnterior) {
        throw new Exception("Error Horario: Las guardias no pueden establecerse en franjas horarias seguidas.");

    } else if ($materia == Materia::GUARDIA->value && ($guardiaHora2Anterior && $guardiaHoraAnterior || $guardiaHora2Siguiente && $guardiaHoraSiguiente)) {
        throw new Exception("Error Horario: Las guardias no pueden establecerse en franjas horarias seguidas.");
    }
}
function validarEliminarHorario($horariosCargados, $dia, $hora)
{
    // Verificar si los campos 'dia' y 'hora' están vacíos
    if (empty($dia) || empty($hora)) {
        throw new Exception("Error Campos: El campo no puede ser vacío");
    }

    // Condicion 1 eliminar - Verificar si la franja horaria existe para ese día y hora
    if (!isset($horariosCargados[$dia][Hora::from($hora)->codigoHora()])) {
        throw new Exception("Error Eliminar Hora: La hora y el día seleccionado no existe, no se puede eliminar.");
    }
}