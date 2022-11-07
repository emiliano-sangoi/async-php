<?php

namespace Emi88\AsyncPhp\Util;

use PDO;

class Util
{

    /**
     * @return PDO|void
     */
    public static function getPDOLink($dbParams)
    {

        $dsn = "mysql:host=" . $dbParams['hostname'] . ";dbname=" . $dbParams['dbname'] . ';charset=utf8';
        try {
            $dbLink = new PDO($dsn, $dbParams['username'], $dbParams['password']);
            $dbLink->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            return $dbLink;
        } catch (Exception $ex) {
            throw $ex;
        }

        return false;
    }

    /**
     * @param $db_params
     * @return false|\mysqli
     */
    public static function getMysqliLink(array $db_params)
    {
        return mysqli_connect($db_params['hostname'], $db_params['username'], $db_params['password'], $db_params['dbname']);
    }

    /**
     * Verifica que ambas tablas no tengan registros cargados
     *
     * @param PDO $pdoLink
     * @return bool
     */
    public static function verificarTablas(\mysqli $mysqli)
    {

        $result = $mysqli->query('SELECT COUNT(*) AS c FROM alumnos');
        if($result instanceof \mysqli_result){
            $row = $result->fetch_assoc();
            if ($row['c'] != 0) {
                return false;
            }
        }

        $result = $mysqli->query('SELECT COUNT(*) AS c FROM rendimiento_acad_alumno');
        if($result instanceof \mysqli_result){
            $row = $result->fetch_assoc();
            if ($row['c'] != 0) {
                return false;
            }
        }

        return true;
    }


}
