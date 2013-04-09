<?php

/* This file is generated by the SQLicious Generator. www.sqlicious.com */

require_once('class.PeopleDaoFactory.php');

class PeopleDaoObject extends DataAccessObject
{
	function __construct($row = null)
	{
		parent::__construct($row);
	}

	function PeopleDaoObject($row = null)
	{
		self::__construct($row);
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

	static function getFactory()
	{
		return new PeopleDaoFactory();
	}
	
	final function setPeopleId($val) { $this->setFieldValue('people_id',$val); }
	final function getPeopleId(){ return $this->getFieldValue('people_id'); }
	
	final function setFirstName($val) { $this->setFieldValue('first_name',$val); }
	final function getFirstName(){ return $this->getFieldValue('first_name'); }
	
	final function setLastName($val) { $this->setFieldValue('last_name',$val); }
	final function getLastName(){ return $this->getFieldValue('last_name'); }
	
	final function setZipcodeId($val) { $this->setFieldValue('zipcode_id',$val); }
	final function getZipcodeId(){ return $this->getFieldValue('zipcode_id'); }
	
	final function setArchived($val) { $this->setFieldValue('archived',$val); }
	final function getArchived(){ return $this->getFieldValue('archived'); }
	
	final function setCreateDate($val) { $this->setDatetimeFieldValue('create_date',$val); }
	final function getCreateDate($format = null) { return $this->getDatetimeFieldValue('create_date',$format); }
	
	final function setCreateDatetime($val) { $this->setDatetimeFieldValue('create_datetime',$val); }
	final function getCreateDatetime($format = null) { return $this->getDatetimeFieldValue('create_datetime',$format); }
	
	function getDefaultRow()
	{
		return array('people_id' => null, 'first_name' => null, 'last_name' => null, 'zipcode_id' => null, 'archived' => null, 'create_date' => null, 'create_datetime' => null);
	}

}


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
		return array('people_id', 'first_name', 'last_name', 'zipcode_id', 'archived', 'create_date', 'create_datetime');
	}
	
	final function addArchivedTrueBinding(){ $this->addBinding(new TrueBooleanBinding('people.archived')); }
	final function addArchivedFalseBinding(){ $this->addBinding(new FalseBooleanBinding('people.archived')); }
	final function addArchivedNotTrueBinding(){ $this->addBinding(new NotEqualsBinding('people.archived',1)); }
	final function addArchivedNotFalseBinding(){ $this->addBinding(new NotEqualsBinding('people.archived',0));  }


}

?>