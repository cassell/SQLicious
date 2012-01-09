<?php

class DataAccessObjectFromCollection extends DataAccessObject
{
	function __construct($row)
	{
		parent::__construct($row);
	}
	
	static function findSubId($id)
	{
		die("findSubId");
	}
	
	function cloneNewObject()
	{
		die("cloneNewObject");
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