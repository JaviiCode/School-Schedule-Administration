<?php
require_once 'Modulo.php';

class FranjaHorario extends Modulo
{
    private $dia;
    private $hora;
    private $tipoFranja;
    private $color;

    public function __construct($curso, $clase, $materia, $dia, $hora, $tipoFranja, $color)
    {
        parent::__construct($curso, $clase, $materia);
        $this->dia = $dia;
        $this->hora = $hora;
        $this->tipoFranja = $tipoFranja;
        $this->color = $color;
    }
   
    public function getDia()
    {
        return $this->dia;
    }

    public function getHora()
    {
        return $this->hora;
    }

    public function getColor()
    {
        return $this->color;
    }

    public function getTipoFranja()
    {
        return $this->tipoFranja;
    }

}
?>