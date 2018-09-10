<?php

use Glial\Html\Form\Form;

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


echo '<form action="" method="POST">';
echo '<div class="well">';

echo '<div class="row">';
\Glial\Synapse\FactoryController::addNode("Common", "displayClientEnvironment", array());

echo ' <a href="'.LINK.'Mysql/add/" class="btn btn-primary" style="font-size:12px"><span class="glyphicon glyphicon-plus" style="font-size:12px"></span> Add a MySQL server</a> ';
echo '</div>';



echo '</div>';
echo '</form>';

echo '<form action="" method="POST">';
echo '<table class="table table-bordered table-striped" id="table">';
echo '<tr>';
echo '<th>'.__('Top').'</th>';
echo '<th>'.__('ID').'</th>';
echo '<th>'.__('MySQL').'</th>';
echo '<th>'.__('SSH').'</th>';
echo '<th><input id="checkAll" type="checkbox" onClick="toggle(this)" /> '.__("Monitored").'</th>';

//echo '<th>'.__('Monitored').'</th>';
echo '<th>'.__('Client').'</th>';
echo '<th>'.__('Environment').'</th>';
echo '<th>'.__('Tags').'</th>';
echo '<th>'.__('Name').'</th>';
echo '<th>'.__('Display name').'</th>';
echo '<th>'.__('IP').'</th>';
echo '<th>'.__('Port').'</th>';

echo '</tr>';

$i     = 0;
$style = '';

Form::setIndice(true);

foreach ($data['servers'] as $server) {

    $i++;
    echo '<tr>';
    echo '<td>'.$i.'</td>';
    echo '<td>'.$server['id'];
    //print_r($server);
    echo '<input type="hidden" name="id['.($i - 1).']" value="'.$server['id'].'" />';

    echo '</td>';

    echo '<td style="'.$style.'">';
    echo '<span class="glyphicon '.(empty($server['error']) ? "glyphicon-ok" : "glyphicon-remove").'" aria-hidden="true"></span>';
    echo '</td>';

    echo '<td style="'.$style.'">';
    echo '<span class="glyphicon '.(empty($server['ssh_available']) ? "glyphicon-remove" : "glyphicon-ok").'" aria-hidden="true"></span>';
    echo '</td>';


    $checked = $server['is_monitored'] == 1 ? 'checked="checked"' : '';

    echo '<td style="'.$style.'">'
    .'<input type="checkbox" name="mysql_server['.($i - 1).'][is_monitored]" '.$checked.' />'.'</td>';

    echo '<td>';
    echo Form::select("mysql_server", "id_client", $data['clients'], $server['id_client'], array("data-live-search" => "true", "class" => "selectpicker", "data-actions-box" => "true"));
    echo '</td>';

    echo '<td>';

    echo Form::select("mysql_server", "id_environment", $data['environments'], $server['id_environment'], array("data-live-search" => "true", "class" => "selectpicker", "data-actions-box" => "true"));
    echo '</td>';
    echo '<td>';

    //$server['id_tag'] = json_encode(array(1,2));
    //$tag = "[2,4,6]";


    if (!empty($data['tag_selected'][$server['id']])) {
        $_GET['link__mysql_server_tag']['tag'] =  "[".implode(",",$data['tag_selected'][$server['id']])."]";

        //debug($_GET['link__mysql_server_tag']['tag']);
    }
    else
    {
        $_GET['link__mysql_server_tag']['tag'] = "";
    }

    
//$_GET['link__mysql_server_tag'][5]['tag'] = json_encode(array(2,4));
    echo Form::select("link__mysql_server_tag", "tag", $data['tag'], "", array("data-live-search" => "true", "class" => "selectpicker", "multiple" => "multiple"));


    echo '</td>';
    echo '<td>'.$server['name'].'</td>';


    if (empty($server['display_name'])) {
        $server['display_name'] = $server['name'];
    }


    $_GET["mysql_server"][($i - 1)]["display_name"] = $server['display_name'];
    echo '<td>'.Form::input("mysql_server", "display_name", array("class" => "form-control"));



    echo ' <a class="btn-xs btn btn-primary" href="'.LINK.'server/password/'.$server['id'].'">'.__('Edit password').'</a>';
    echo ' <a class="btn-xs btn btn-danger" href="'.LINK.'server/remove/'.$server['id'].'">'.__('Remove').'</a>';

    echo '</td>';



    echo '<td>'.$server['ip'].'</td>';
    echo '<td>'.$server['port'].'</td>';
    echo '</tr>'."\n";
}

Form::setIndice(false);

echo '</table>';

echo '<input type="hidden" name="settings" value="1" />';
echo '<button type="submit" class="btn btn-primary">'.__("Update").'</button>';
echo '</form>';
