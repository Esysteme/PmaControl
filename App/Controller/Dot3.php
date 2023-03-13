<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Controller;

use \Glial\Synapse\Controller;
use App\Library\Extraction2;
use App\Library\Extraction;
use App\Library\Mysql;
use \App\Library\Debug;
use \Glial\Sgbd\Sgbd;

// ""	&#9635;   ▣
// "□"	&#9633;	&#x25A1;
// ""	&#9679;  ●
//"○"	&#9675;
//"◇"	&#9671;	&#x25C7;
//"◈"	&#9672;	&#x25C8;
// Joining: receiving State Transfer   => IST change color
//add virtual_ip
// ha proxy
// https://renenyffenegger.ch/notes/tools/Graphviz/examples/index  <= to check for GTID (nice idea)
class Dot3 extends Controller
{

    /*
    * récupére toutes les infomations du serveur à un date t   
    */



    public function getInformation($param)
    {
        Debug::parseDebug($param);

        $date_request = $param[0] ?? "";

        $versioning = "";
        $versioning2 = "";
        if ( ! empty($date_request))
        {
            $versioning = " WHERE '".$date_request."' between a.row_start and a.row_end ";
            $versioning2 = " WHERE '".$date_request."' between b.row_start and b.row_end AND '".$date_request."' between c.row_start and c.row_end ";
            $date_request = array($date_request);
        }

        Debug::debug($date_request, "Date");

        $db  = Sgbd::sql(DB_DEFAULT);
        $all = Extraction2::display(array("variables::hostname", "variables::binlog_format", "variables::time_zone", "variables::version",
                "variables::system_time_zone", "variables::wsrep_desync", "variables::port", "variables::is_proxysql", "variables::wsrep_cluster_address",
                "variables::wsrep_cluster_name", "variables::wsrep_provider_options", "variables::wsrep_on", "variables::wsrep_sst_method",
                "variables::wsrep_desync", "status::wsrep_cluster_status", "status::wsrep_local_state", "status::wsrep_local_state_comment",
                "status::wsrep_incoming_addresses", "variables::wsrep_patch_version","mysql_server::available", "mysql_server::ping", "mysql_server::error",
                "status::wsrep_cluster_size", "status::wsrep_cluster_state_uuid", "status::wsrep_gcomm_uuid", "status::wsrep_local_state_uuid",
                "slave::master_host", "slave::master_port", "slave::seconds_behind_master", "slave::slave_io_running",
                "slave::slave_sql_running", "slave::replicate_do_db", "slave::replicate_ignore_db", "slave::last_io_errno", "slave::last_io_error",
                "slave::last_sql_error", "slave::last_sql_errno", "slave::using_gtid", "variables::is_proxysql"),array() , $date_request);

        $sql = "SELECT id as id_mysql_server, ip, port, display_name,is_available  
        FROM mysql_server a ".$versioning."
        UNION select b.id_mysql_server, b.dns as ip, b.port, c.display_name, c.is_available  
        from alias_dns b INNER JOIN mysql_server c ON b.id_mysql_server =c.id
        ".$versioning2.";";


        Debug::sql($sql);

        $res = $db->sql_query($sql);

        $server_mysql = array();
        //$mapping_master = array();

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $server_mysql[$arr['id_mysql_server']] = $arr;

            //TODO add alias_dns and virtual_ip
            $data['mapping'][$arr['ip'].':'.$arr['port']] = $arr['id_mysql_server'];
        }

        $data['servers'] = array_replace_recursive($all, $server_mysql);

        Debug::debug($all, "ALL INFORMATION");

        echo json_encode($data, JSON_PRETTY_PRINT);

        $proxy = Extraction2::display(array("proxysql_main_var::mysql-interfaces", "proxysql_main_var::admin-web_port", "proxysql_main_var::admin-version"));
        Debug::debug($proxy, "proxysql");

        return $data;
    }
}