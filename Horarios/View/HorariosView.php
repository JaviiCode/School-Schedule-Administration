<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- CSS only -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.2/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-Zenh87qX5JnK2Jl0vWa8Ck2rdkQ2Bzep5IDxbcnCeuOxjzrPF/et3URy9Bv1WTRi" crossorigin="anonymous">
    <title>Horario Docente</title>
</head>


<body>
    <?php
    require '..\Model\Campos.php';
    require_once '..\Controller\HorarioController.php';
    $controlador = new HorarioController(); 
    ?>
    <div class="container"><br><br>
        <h1 class="text-center">Horario de Clases</h1><br>

        <div class="row">
            <div class="container col-md-8">
                <?php
                $controlador->desplegarHorario();
                ?>

            </div>
            <br>
            <br>

            <div class="container col-md-4">
                <h5>Operaciones Horario:</h5>

                <form action="#" method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-6">
                            <label>Curso:</label>
                            <select class="form-select" id="curso" name="curso">
                                <option value=''></option>
                                <?php
                                // Cursos
                                foreach (Curso::cases() as $curso) {
                                    echo "<option value='{$curso->value}'>{$curso->name}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label>Día:</label>
                            <select class="form-select" id="dia" name="dia">
                                <option value=''></option>
                                <?php
                                // Semana
                                foreach (Semana::cases() as $dia) {
                                    echo "<option value='{$dia->value}'>{$dia->name}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <label>Tipo:</label>
                            <select class="form-select" id="tipoFranja" name="tipoFranja">
                                <option value=''></option>
                                <?php
                                // Tipo Franja Horaria
                                foreach (TipoFranja::cases() as $tipo) {
                                    echo "<option value='{$tipo->value}'>{$tipo->name}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label>Hora:</label>
                            <select class="form-select" id="hora" name="hora">
                                <option value=''></option>
                                <?php
                                // Horas
                                foreach (Hora::cases() as $hora) {
                                    echo "<option value='{$hora->value}'>{$hora->value}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <label>Materia:</label>
                            <select class="form-select" id="materia" name="materia">
                                <option value=''></option>
                                <?php
                                // Materias
                                foreach (Materia::cases() as $materia) {
                                    echo "<option value='{$materia->value}'>{$materia->name}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="col-6">
                            <label>Clase:</label>
                            <select class="form-select" id="clase" name="clase">
                                <option value=''></option>
                                <?php
                                // Clases
                                foreach (Clase::cases() as $clase) {
                                    echo "<option value='{$clase->value}'>{$clase->name}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-6">
                            <label>Color:</label>
                            <select class="form-select" id="color" name="color">
                                <option value=''></option>
                                <?php
                                // Colores
                                foreach (Color::cases() as $color) {
                                    echo "<option value='{$color->value}'>{$color->name}</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-4">
                            <input type="submit" class="btn btn-primary" name="insertar" value="Insertar Hora">
                        </div>

                        <div class="col-4">
                            <input type="submit" class="btn btn-danger" name="eliminar" value="Eliminar Hora">
                        </div>
                    </div>
                    <br>
                    <h5>Generar Horario:</h5>
                    <div class="row">
                        <div class="col-6">
                            <label>Tipo Horario:</label>
                            <select class="form-select" id="tipohorario" name="tipohorario">
                                <option value=''></option>
                                <?php
                                // Tipos de horarios
                                foreach (TiposHorarios::cases() as $tipohorario) {
                                    echo "<option value='{$tipohorario->value}'>{$tipohorario->name}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <br>
                    </div>
                    <h5>Importar Horario:</h5>
                    <div class="row">
                        <div class="col-4">
                            <input type="file" name="fhorario" id="fhorario">
                        </div>
                    </div>
                    <br>
                    <div class="row">
                        <div class="col-4">
                            <input type="submit" class="btn btn-warning" name="cargar" value="Cargar Horario">
                        </div>
                    </div>

                </form>

            </div>
        </div>

    </div>

</html>