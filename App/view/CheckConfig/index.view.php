<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use Glial\Html\Form\Form;

function setColor($type)
{
    $hex = substr(sha1($type), 0, 2).substr(sha1($type), 20, 2).substr(md5($type), -2, 2);
    return hexToRgb($hex);
}

function hexToRgb($colorName)
{
    list($r, $g, $b) = array_map(
        function($c) {
        return hexdec(str_pad($c, 2, $c));
    }, str_split(ltrim($colorName, '#'), strlen($colorName) > 4 ? 2 : 1)
    );

    return array($r, $g, $b);
}

function getrgba($label, $alpha)
{
    list($r, $g, $b) = setColor($label);
    return "rgba(".$r.", ".$g.", ".$b.", ".$alpha.")";
}
echo '<div class="well">';



echo '<div class="row">';
echo '<form method="POST" action="">';
echo '<div class="col-md-10">';
echo Form::select("mysql_cluster", "id", $data['grappe'], "", array("data-live-search" => "true", "class" => "selectpicker form-control"));
echo '</div>';
echo '<div class="col-md-2">';
echo '<button type="submit" class="btn btn-primary">Check Result on this cluster</button>';
echo '</div>';
echo '</form>';
echo '</div>';


echo '<div class="row">';
echo '<br />';
echo '</div>';



echo '<div class="row">';
echo '<form method="POST" action="">';
echo '<div class="col-md-10">';
\Glial\Synapse\FactoryController::addNode("Common", "getSelectServerAvailable", array("mysql_server", "id", array("multiple" => "multiple", "data-width" => "100%")));
echo '</div>';
echo '<div class="col-md-2">';
echo '<button type="submit" class="btn btn-primary">Check Result on these servers</button>';
echo '</div>';

echo '</form>';
echo '</div>';




echo '</div>';


//debug($data['resultat']);


echo '<p>
  <a class="btn btn-primary" role="button" id="showdiff">Show only differences</a>
</p>';

if (!empty($data['index'])) {

    echo '<table class="table table-condensed table-bordered table-striped">';
    echo '<tr>';
    echo '<th>'.__('Top').'</th>';
    echo '<th>'.__('Diff').'</th>';
    if (!empty($data['show']) && $data['show']) {
        echo '<th>'.__("Variable_name").'</th>';
    }



    foreach ($data['groups'] as $group) {
        echo '<th>';

        $servers = array();

        foreach ($group as $value) {
            $servers[] = $data['mysql_server'][$value];
        }
        //debug($servers);

        echo implode(", ", $servers);

        echo '</th>';
    }
    echo '</tr>';

    $length = 23 * 9 / count($data['groups']);


    $i = 0;
    $j = 0;
// cas SHOW
    foreach ($data['index'] as $index) {

        $i++;
        // test egalité sur toutes les colones
        $test = array();
        foreach ($data['groups'] as $group) {

            if (isset($data['resultat'][$group[0]][$index])) {
                $test[] = $data['resultat'][$group[0]][$index];
            } else {
                $test[] = "N/A";
            }
        }

        $style = false;

        $hide  = ' class="to_hide"';

        if (count(array_unique($test)) !== 1) {
            $style = true;
            $hide  = '';
            $j++;
        }

        if (true) {
            echo '<tr'.$hide.'>';
            echo '<td>'.$i.'</td>';
            echo '<td>'.$j.'</td>';
            echo '<td class="">';
            if ($style) {
                echo '<b>'.$index.'</b>';
            } else {
                echo $index;
            }
            echo '</td>';


            foreach ($data['groups'] as $group) {
                if (isset($data['resultat'][$group[0]][$index])) {


                    $var_long = $data['resultat'][$group[0]][$index];

                    if (strlen($var_long) > $length) {


                        $var = substr($var_long, 0, $length)."...";
                    } else {
                        $var = $var_long;
                        //$var_long =
                    }
                } else {
                    $var      = "<b>N/A</b>";
                    $var_long = "N/A";
                }

                $color = "";

                if ($style) {
                    $color = "background: ".getrgba($var, 0.5);
                    $var   = "<b>".$var."</b>";
                }

                echo '<td style="'.$color.'" title="'.$var_long.'">';
                echo $var;
                echo '</td>';
            }

            echo '</tr>';
        }
    }
    echo '</table>';
}
//debug($data['resultat']);