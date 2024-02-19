<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function estructura() {
    $html = "";
    $comboStatus = "<option value='0' selected='selected'>Pendiente</option><option value='1'>Realizado</option>";
    $comboCalificacion = "<option  selected='selected' value='100'>100%</option><option  value='90'>90%</option><option  value='80'>80%</option><option  value='70'>70%</option><option  value='60'>60%</option><option  value='50'>50%</option><option  value='40'>40%</option><option  value='30'>30%</option><option  value='20'>20%</option><option  value='10'>10%</option><option  value='0'>0%</option>";

    $hrs = array();
    for ($i = 0; $i <= 23; $i++) {
        if ($i <= 9) {
            $hrs[] = "0" . $i . ":00";
        } else {
            $hrs[] = $i . ":00";
        }
    }
    foreach ($hrs as $hr) {
        $html.=<<<HTML
                    <tr>
                        <td>$hr</td>
                        <td><select class='operadores'></select></td>
                        <td><input class='actividades' type='text'/></td>
                        <td><input class='cantidades' type='text'/></td>
                        <td><input class='meta_valor' type='text'/></td>
                        <td><select class='status'>$comboStatus</select></td>
                        <td><select class='calificaciones'>$comboCalificacion</select></td>
                    </tr>
HTML;
    }
    echo $html;
}
