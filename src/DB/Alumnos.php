<?php

namespace  Emi88\AsyncPhp\DB;

use Emi88\AsyncPhp\Util\Util;
use FICH\APIInfofich\Query\Alumnos\QueryAlumnosPorCarrera;
use FICH\APIRectorado\Config\WSHelper;
use PDO;

class Alumnos
{

    /**
     * Devuelve un listado de alumnos por carrera
     *
     * @param $codigoCarrera
     * @return bool|mixed
     * @throws array|\FICH\APIRectorado\Exception\APIRectoradoErrorException
     */
    public static function getAlumnosPorCarrera($codigoCarrera)
    {
        $alumnosPorCarrera = new QueryAlumnosPorCarrera();
        $alumnosPorCarrera->setUnidadAcademica(WSHelper::UA_FICH)
            ->setCacheEnabled(false)
            ->setWsEnv(WSHelper::ENV_PROD)
            ->setTipoTitulo(WSHelper::TIPO_TITULO_GRADO);

        return $alumnosPorCarrera
            ->setCarrera($codigoCarrera)
            ->getResultado();
    }



    /**
     * Genera un stament para una insercion mas eficiente en la base de datos
     * @param PDO $pdoLink
     * @return false|PDOStatement
     */
    public static function getSqlStmtDatosCensales(PDO $pdoLink){
        $sql = "INSERT INTO alumnos(
                id, tipoDocumento, nroDocumento, fechaNacimiento, apellido, nombre,
                sexo, localidadProc, cpProc, localidadRes, cpRes, direccionRes,
                nroRes, pisoRes, deptoRes, unidad, telefono, email, paisDocumento,
                nroCelular, compCelular, carrera) VALUES (NULL,
                :tipoDocumento, :nroDocumento, :fechaNacimiento, :apellido, :nombre,
                :sexo, :localidadProc, :cpProc, :localidadRes, :cpRes, :direccionRes,
                :nroRes, :pisoRes, :deptoRes, :unidad, :telefono, :email, :paisDocumento,
                :nroCelular, :compCelular, :carrera)";

        return $pdoLink->prepare($sql);
    }

    public static function get(PDO $pdoLink, $tipoDocumento, $nroDocumento, $sexo){

        $pdoLink = Util::getPDOLink();
        $stmt = $pdoLink->prepare("SELECT id FROM alumnos WHERE tipoDocumento = ? AND nroDocumento = ? AND sexo = ?");

        if($stmt->execute([$tipoDocumento, $nroDocumento, $sexo]) && $stmt->rowCount() > 0){
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int) $row['id'];
        }

        return false;
    }

    public static function insertarAlumno(PDO $pdoLink, array $alumno, $cod_carrera){

        $td = $alumno['tipoDocumento'];
        $nd = $alumno['nroDocumento'];
        $s = $alumno['sexo'];

        $stmt = self::getSqlStmtDatosCensales($pdoLink);
        $stmt->bindParam(":tipoDocumento", $td);
        $stmt->bindParam(":nroDocumento", $nd);
        $stmt->bindParam(":sexo", $s);
        $stmt->bindParam(":fechaNacimiento", $alumno['fechaNacimiento']);
        $stmt->bindParam(":apellido", $alumno['apellido']);
        $stmt->bindParam(":nombre", $alumno['nombre']);
        $stmt->bindParam(":localidadProc", $alumno['localidadProc']);
        $stmt->bindParam(":cpProc", $alumno['cpProc']);
        $stmt->bindParam(":localidadRes", $alumno['localidadRes']);
        $stmt->bindParam(":cpRes", $alumno['cpRes']);
        $stmt->bindParam(":direccionRes", $alumno['direccionRes']);
        $stmt->bindParam(":nroRes", $alumno['nroRes']);
        $stmt->bindParam(":pisoRes", $alumno['pisoRes']);
        $stmt->bindParam(":deptoRes", $alumno['deptoRes']);
        $stmt->bindParam(":unidad", $alumno['unidad']);
        $stmt->bindParam(":telefono", $alumno['telefono']);
        $stmt->bindParam(":email", $alumno['email']);
        $stmt->bindParam(":paisDocumento", $alumno['paisDocumento']);
        $stmt->bindParam(":nroCelular", $alumno['nroCelular']);
        $stmt->bindParam(":compCelular", $alumno['compCelular']);
        $stmt->bindParam(":carrera", $cod_carrera);

        return $stmt->execute();
    }

}
