<?php

//require_once $_SERVER['DOCUMENT_ROOT'].'/inc/db_sqlite3.inc';	//contains functions for db interaction
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/dbconfig_controller.inc';			//contains functions for getting, settings dbconfig parameters.
require_once $_SERVER['DOCUMENT_ROOT'].'/inc/util.inc';			//contains functions for socket interaction, error message display, and logging. 

class MessagePriorityController
{
        private $dbconfig = '';

        function __construct()
        {
                $this->dbconfig = new dbconfigController();
        }

        /**
         * getMessagePriorityData
         *
         * Wrapper for getDbconfigData
         * @param string $param
         * @return string
         * @author Sean Toscano (sean@absolutetrac.com)
         */
        
} //END class MessagePriorityController
?>
