<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Library;

use \Glial\Sgbd\Sgbd;

class Table
{

    static $table = array();

    static $count = array();
    /*
     * (PmaControl 2.0.64)<br/>
     * @author Aurélien LEQUOY, <aurelien.lequoy@68koncept.com>
     * @return boolean Success
     * @package Controller
     * @since 2.0.64  First time this was introduced.
     * @since pmacontrol 2.0.64 
     * @description test if daemon is launched or not according with pid saved in table daemon_main
     * @access public
     *
     */

    static public function getTableDefinition($param)
    {
        Debug::parseDebug($param);
        
        $id_mysql_server = $param[0];
        $table_schema = $param[1];
        $table_name = $param[2];

        if (empty(self::$table[$id_mysql_server][$table_schema][$table_name]))
        {
            $db = Mysql::getDbLink($id_mysql_server);

            $sql = "DESCRIBE `".$table_schema."`.`".$table_name."`;";
            Debug::sql($sql);

            $res = $db->sql_query($sql);

            while($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
                self::$table[$id_mysql_server][$table_schema][$table_name][] = $arr;
            }
            
            Debug::debug(self::$table[$id_mysql_server][$table_schema][$table_name], "Table $table_schema.$table_name");
        }

        return self::$table[$id_mysql_server][$table_schema][$table_name];
    }

    static public function findFieldPosition($param)
    {
        Debug::parseDebug($param);
        
        //$id_mysql_server = $param[0];
        //$table_schema = $param[1];
        //$table_name = $param[2];
        $field_name = $param[3];

        $def = self::getTableDefinition($param);

        $i=1;
        foreach($def as $row) {   
            if ($row['Field'] === $field_name) {
                Debug::debug($i, "Position of $field_name");
                return $i;
            }
            $i++;
        }
    }

    static public function getNumberOfField($param)
    {
        Debug::parseDebug($param);

        $def = self::getTableDefinition($param);

        return count($def);
    }


    static public function getCount($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $table_schema = $param[1];
        $table_name = $param[2];

        if (empty(self::$count[$id_mysql_server][$table_schema][$table_name]))
        {
            $db = Mysql::getDbLink($id_mysql_server);

            $sql = "SELECT count(1) as cpt FROM `".$table_schema."`.`".$table_name."`";
            $res = $db->sql_query($sql);
            
            while($ob = $db->sql_fetch_object($res)) {
                self::$count[$id_mysql_server][$table_schema][$table_name] = $ob->cpt;
            }
        }

        return self::$count[$id_mysql_server][$table_schema][$table_name];
    }

    static public function getIndex($param)
    {
        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $table_schema = $param[1];
        $table_name = $param[2];
    }


    static public function importRealForeignKey($param)
    {

        Debug::parseDebug($param);

        $id_mysql_server = $param[0];
        $database        = $param[1] ?? false;

        $db = Mysql::getDbLink($id_mysql_server);
        $default = Sgbd::sql(DB_DEFAULT);

        $sql = "SELECT CONSTRAINT_SCHEMA as constraint_schema,TABLE_NAME as constraint_table,COLUMN_NAME as constraint_column, CONSTRAINT_NAME as constraint_name, "
            ." REFERENCED_TABLE_SCHEMA as referenced_schema, REFERENCED_TABLE_NAME as referenced_table,REFERENCED_COLUMN_NAME as referenced_column"
            ." FROM `information_schema`.`KEY_COLUMN_USAGE` "
            ."WHERE `REFERENCED_TABLE_NAME` IS NOT NULL ";


        if ($database !== false)
        {
            $sql .= " AND `REFERENCED_TABLE_SCHEMA`='".$database."' "
            ." AND `CONSTRAINT_SCHEMA` ='".$database."' ";
        }

        Debug::sql($sql);
        $res = $db->sql_query($sql);
        $sql2 = "DELETE FROM `foreign_key_real` WHERE id_mysql_server=".$id_mysql_server." OR id_mysql_server__link=".$id_mysql_server."";
        
        if ($database !== false)
        {
            $sql2 .=" AND (constraint_table ='".$database."' OR referenced_table ='".$database."')";
        }
        Debug::sql($sql2);

        $default->sql_query($sql2);

        while ($arr = $db->sql_fetch_array($res, MYSQLI_ASSOC)) {
            $table = array();
            $table['foreign_key_real'] = $arr;
            $table['foreign_key_real']['id_mysql_server'] = $id_mysql_server;
            $table['foreign_key_real']['id_mysql_server__link'] = $id_mysql_server;
            
            Debug::debug($table);
            $default->sql_save($table);
        }
    }

}