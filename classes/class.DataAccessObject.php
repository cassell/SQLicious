<?php

abstract class DataAccessObject
{
	var $data;
	var $modifiedColumns;
	
	const NEW_OBJECT_ID = -1;
	
	abstract function getDatabaseName();
	abstract function getTableName();
	abstract function getIdField();
	abstract function getFactory();
	
	function __construct($row)
	{
		if($row != null)
		{
			$this->data = $row;
		}
		else
		{
			$this->data = static::getDefaultRow();
			$this->data[static::getIdField()] = self::NEW_OBJECT_ID;
			$this->modifiedColumns[static::getIdField()] = 1;
		}
	}
	
	static function findId($id)
	{
		$factory = static::getFactory();
		$factory = new $factory();
		return reset($factory->findId($id));
	}
	
	
	
	function save()
	{
		$f = static::getFactory();
		$f->save($this);
	}
	
	function delete()
	{
		$f = static::getFactory();
		$f->delete($this);
	}
	
	function cloneNewObject()
	{
		$obj = new static();

		// clone data
		$obj->data = $this->data;
		
		// set object_id to NEW_OBJECT_ID (-1)
		$obj->data[static::getIdField()] = static::NEW_OBJECT_ID;
		
		// set all modified colums to 1
		$obj->modifiedColumns = static::getDefaultRow();
		array_walk($obj->modifiedColumns,function(&$v,$k){ $v = 1; });
		
		return $obj;
	}
	
	
	function getId() { return $this->getFieldValue($this->getIdField()); }
	function getObjectId() { return $this->getId(); }
	
	function toJSON()
	{
		return $this->toJSONArray();
	}
	
	function toJSONArray()
	{
		$j = array();
		if($this->data != null)
		{
			foreach($this->data as $field => $value)
			{
				if($field == $this->getIdField())
				{
					$j['id'] = $value;
				}
				else
				{
					$j[DataAccessObjectFactory::toFieldCase($field)] = $value;
				}
			}
		}
		
		return $j;
	}
	
	function toJSONString()
	{
		return DataAccessObjectFactory::JSONEncodeArray($this->toJSONArray());
	}
	
	function setFieldValue($fieldName,$val)
	{
		if(strcmp($this->data[$fieldName],$val) !== 0)
		{	
			$this->modifiedColumns[$fieldName] = 1;
		}
		$this->data[$fieldName] = $val;
	}
	
	function setDatetimeFieldValue($fieldName,$val)
	{
		if($val != "" && $val != '')
		{
			$this->setFieldValue($fieldName, $val);
		}
		else
		{
			$this->setFieldValue($fieldName, NULL);
		}
	}
	
	function getFieldValue($fieldName)
	{
		if(array_key_exists($fieldName,$this->data))
		{
			return $this->data[$fieldName];
		}
		else
		{
			trigger_error($fieldName . ' not initilized for get method in ' . get_class($this));
		}
	}
	
}

?>