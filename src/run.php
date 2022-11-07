<?php

require '../vendor/autoload.php';

use Emi88\AsyncPhp\DB\Alumnos;
use FICH\APIRectorado\Exception\APIRectoradoErrorException;
use Spatie\Async\Pool;
use FICH\APIRectorado\Config\WSHelper;
use Emi88\AsyncPhp\Util\Util;

$start = microtime(true);

$db_params = array(
    'hostname' => 'localhost',
    'dbname' => 'test',
    'username' => 'root',
    'password' => 'root',
);

$mysqli_link = Util::getMysqliLink($db_params);
//var_dump(Alumnos::get($pdoLink, "0","10315826","1"));
//$stmt2 = $pdoLink->prepare("SELECT id FROM alumnos WHERE tipoDocumento = ? AND nroDocumento = ? AND sexo = ?");
//if($stmt2->execute(["0","33496269","1"])) {
//    $row = $stmt2->fetch(PDO::FETCH_ASSOC);
//    var_dump($row, $stmt2->rowCount());
//}
//exit;

// Verificar las tablas
echo "Verificando tablas ws_guarani_datosCensalesAlumno y ws_guarani_rendimientoAcademico ...\n";
if (!Util::verificarTablas($mysqli_link)) {
    echo "Error: Las tablas no estan VACIAS\n\n";
    exit;
} else {
    echo "Ambas tablas existen y están vacias\n\n";
}

// Carreras para las cuales se realiza la busqueda:
$arrayCarrerasVigentes = array(
    WSHelper::CARRERA_IAMB,
    WSHelper::CARRERA_IRH,
    WSHelper::CARRERA_II,
    WSHelper::CARRERA_IAGR
);

foreach ($arrayCarrerasVigentes as $cod_carrera) {

    echo 'Carrera: ' . $cod_carrera . "\n";
    echo '======================================================================================================' . "\n";

    try {
        $alumnos = Alumnos::getAlumnosPorCarrera($cod_carrera);
    } catch (APIRectoradoErrorException $e) {
        echo "Ocurrio un error al obtener los alumnos de la carrera.\n";
        echo $e->getMessage();
        exit;
    }

    $pool = Pool::create();

    foreach ($alumnos as $alumno) {

        $tdoc = $alumno["tipoDocumento"];
        $ndoc = $alumno["nroDocumento"];
        $sexo = $alumno["sexo"];


        $pool->add(function () use ($alumno, $cod_carrera, $tdoc, $ndoc, $sexo, $pdoLink2) {
            // Do a thing

            //echo "Insertando datos censales del alumno ... ";
            $ape = trim(mb_strtoupper($alumno["apellido"]));
            $nom = trim(mb_strtoupper($alumno["nombre"]));

           // echo "Tipo y nro. doc: $tdoc - $ndoc | Ape y nom: $ape, $nom \n";

            $pdoLink = Util::getPDOLink();
            $id = Alumnos::get($pdoLink, $tdoc, $ndoc, $sexo);

            if(!$id){
                Alumnos::insertarAlumno($pdoLink, $alumno, $cod_carrera);
            }

        })->then(function ($output) use ($tdoc, $ndoc, $sexo) {

            // Handle success
            $pdoLink = Util::getPDOLink();
            $id = Alumnos::get($pdoLink, $tdoc, $ndoc, $sexo);
         //   echo "Alumno creado correctamente con el id: $id\n";

        })->catch(function (Throwable $exception) {
            // Handle exception
            echo $exception->getMessage() . "\n";
        });
    }

    $pool->wait();

}

$elapsed = microtime(true) - $start;
$elapsed_min = round($elapsed / 60, 2);
echo "El script tardo: $elapsed segundos / $elapsed_min minutos \n\n";

