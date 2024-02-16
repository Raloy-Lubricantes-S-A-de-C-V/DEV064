<?php

class mysql_exec {

    function multiquery($queries) {
        $mysqli = new mysqli($host, $user, $pass, $db, $port);
        $mysqli->autocommit(false);
        $errors = array();
        foreach ($queries as $query) {
            if (!$mysqli->query($query)) {
                $errors[] = $mysqli->error;
            }
        }

        if (count($errors) === 0) {
            $mysqli->commit();
            $respuesta["status"] = 1;
        } else {
            $mysqli->rollback();
            $respuesta["error"] = implode("//", $errors);
            $respuesta["status"] = 0;
        }
        $mysqli->close();
        return json_encode($respuesta);
    }

}
