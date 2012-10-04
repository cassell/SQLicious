<?php

/* This file is generated by the SQLicious Generator. www.sqlicious.com */

require_once('class.PeopleDaoObject.php');

class PeopleDaoFactory extends DataAccessObjectFactory
{
	function __construct()
	{
		parent::__construct();
	}

	function PeopleDaoFactory()
	{
		self::__construct();
	}

	function getDatabaseName()
	{
		return 'sqlicious_test';
	}

	function getTableName()
	{
		return 'people';
	}

	function getIdField()
	{
		return 'people_id';
	}

	function loadDataObject($row)
	{
		return new PeopleDaoObject($row);
	}

	function getFields()
	{
		return array('people_id', 'first_name', 'last_name', 'zipcode_id', 'create_date', 'create_datetime');
	}


}
