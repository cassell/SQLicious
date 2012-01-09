<?php

class DataAccessObjectFromCollection extends DataAccessObject
{
	function __construct($row)
	{
		parent::__construct($row);
	}
	
	/*
	static function findSubId($id)
	{
		$factory = static::getFactory();
		$factory = new $factory();
		return reset($factory->findId($id));
	}
	*/
	
	function cloneNewObject()
	{
		die("cloneNewObject");
		/*
		$obj = new static();

		// clone data
		$obj->data = $this->data;
		
		// set object_id to NEW_OBJECT_ID (-1)
		$obj->data[static::getIdField()] = static::NEW_OBJECT_ID;
		
		// set all modified colums to 1
		$obj->modifiedColumns = static::getDefaultRow();
		array_walk($obj->modifiedColumns,function(&$v,$k){ $v = 1; });
		
		return $obj;
		*/
	}
	
	function toJsonArray()
	{
		$j = array();
		if($this->data != null)
		{
			foreach($this->data as $field => $value)
			{
				if($field == static::getSubIdFieldName())
				{
					// push sub_id as id
					$j['id'] = $value;
				}
				else if($field != $this->getIdField()) // skip the primary key
				{
					$j[DataAccessObjectFactory::toFieldCase($field)] = $value;
				}
			}
		}
		
		return $j;
	}
	
}

?>