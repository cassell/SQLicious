<?php

define("SQLICIOUS_INCLUDE_PATH", dirname(__FILE__));
define("SQLICIOUS_CONFIG_GLOBAL", "DATABASE_CONFIG");

require_once(SQLICIOUS_INCLUDE_PATH.'/classes/class.DatabaseConfiguration.php');
require_once(SQLICIOUS_INCLUDE_PATH.'/classes/class.DataAccessArray.php');
require_once(SQLICIOUS_INCLUDE_PATH.'/classes/class.DatabaseProcessor.php');
require_once(SQLICIOUS_INCLUDE_PATH.'/classes/class.DataAccessObject.php');
require_once(SQLICIOUS_INCLUDE_PATH.'/classes/class.DataAccessObjectFactory.php');

?>