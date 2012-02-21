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
		$f = static::getFactory();
		return $f->findId($id);
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
	
	function save()
	{
		$f = static::getFactory();
		$conn = $f->openMasterConnection();
		
		if(!empty($this->modifiedColumns))
		{
			foreach(array_keys($this->modifiedColumns) as $field)
			{
				if($field != $this->getIdField())
				{
					if($this->data[$field] !== null)
					{
						$sql[] = $this->getTableName()  . "." . $field . ' = "' . mysql_real_escape_string($this->data[$field]) . '"';
					}
					else
					{
						$sql[] = ' ' . $this->getTableName() . "." . $field . ' = NULL';
					}
				}
			}
			
			if($this->getId() != DataAccessObject::NEW_OBJECT_ID)
			{
				$f->update('UPDATE ' . $this->getTableName() . " SET " .  implode(",",$sql) . " WHERE " . $this->getTableName() . "." . $this->getIdField() . ' = ' . $this->getId());
			}
			else
			{
				if($sql != null)
				{
					$sql = 'INSERT INTO ' . $this->getTableName() . " SET " .  implode(",",$sql);
				}
				else
				{
					// empty object
					$sql = 'INSERT INTO ' . $this->getTableName() . " VALUES()";
				}
				
				$result = $f->getMySQLResult($sql);
				$this->data[$this->getIdField()] = mysql_insert_id();
				@mysql_free_result($result);
			}
			
			unset($this->modifiedColumns);
		}
	}
	
	function delete()
	{
		if(intval($this->getId()) > 0)
		{
			$f = static::getFactory();
			$this->update("DELETE FROM " . $this->getTableName() . " WHERE " . $this->getIdField()." = ".$object->getId());
		}
	}
	
	
	function getId() { return $this->getFieldValue($this->getIdField()); }
	function getObjectId() { return $this->getId(); }
	
	function toJSON()
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
		return DataAccessObjectFactory::JSONEncodeArray($this->toJSON());
	}
	
	function toArray()
	{
		$j = array();
		if($this->data != null)
		{
			foreach($this->data as $field => $value)
			{
				$j[DataAccessObjectFactory::toFieldCase($field)] = $value;
			}
		}
		return $j;
	}
	
	function toCSV()
	{
		return implode(",",array_values($this->toArray()));
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