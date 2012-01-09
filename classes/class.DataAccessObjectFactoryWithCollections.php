<?php

class DataAccessObjectFactoryWithCollections extends DataAccessObjectFactory
{
	
// 	const DEFAULT_SUB_ID_FIELD = 'sub_id';
// 	const DEFAULT_SUB_ID_COLLECTION_ID_FIELD = 'organization_id';
	
	// overridden
	private function getOutputFromMysqlQuery($sql)
	{
		if($this->getReturnType() == self::RETURN_TYPE_JSON_ARRAY || $this->getReturnType() == self::RETURN_TYPE_JSON_STRING)
		{
			$result = $this->getMySQLResult($sql);
			
			$data = array();
			while ($row = mysql_fetch_assoc($result))
			{
				$j = array();
				if($row != null)
				{
					foreach($row as $field => $value)
					{
						if($field == static::getSubIdFieldName())
						{
							// push sub_id as id
							$j['id'] = $value;
						}
						else if($field != $this->getIdField()) // skip the primary key
						{
							$j[self::toFieldCase($field)] = $value;
						}
					}
				}
				
				$data[] = $j;
			}
			mysql_free_result($result);
			
			if($this->getReturnType() == self::RETURN_TYPE_JSON_STRING)
			{
				return self::jsonEncodeArray($data);
			}
			else
			{
				return $data;
			}
		}
		else 
		{
			// only json types are effected by sub_id
			return parent::getOutputFromMysqlQuery($sql);
		}
	}
	
	// overridden
	function save($object)
	{
		die("save");
		/*
		$conn = $this->openMasterConnection();
		
		if(!empty($object->modifiedColumns))
		{
			foreach(array_keys($object->modifiedColumns) as $field)
			{
				if($field != static::getIdField())
				{
					if($object->data[$field] !== null)
					{
						if($this->fields[$field] == self::DATETIME_TYPE && $this->getTimezoneAware() && $this->getServerTimezone() != $this->getDestinationTimezone())
						{
							$sql[] = static::getTableName()  . "." . $field . ' = CONVERT_TZ(' . static::getTableName() . '.' . $field . ', "' . $this->getServerTimezone() . '","' . $this->getDestinationTimezone() . '")';	
						}
						else
						{
							$sql[] = static::getTableName()  . "." . $field . ' = "' . mysql_real_escape_string($object->data[$field]) . '"';
						}
					}
					else
					{
						$sql[] = ' ' . static::getTableName() . "." . $field . ' = NULL';
					}
				}
			}
			
			if($object->getId() != DataAccessObject::NEW_OBJECT_ID)
			{
				$sql = 'UPDATE ' . static::getTableName() . " SET " .  implode(",",$sql) . " WHERE " . static::getTableName() . "." . static::getIdField() . ' = ' . $object->getId();
				$result = mysql_query($sql,$this->openMasterConnection()) or trigger_error("DAOFactory Update Error: ". htmlentities($sql), E_USER_ERROR);
			}
			else
			{
				// add sub id from nex sub_id
				$object->modifiedColumns[static::getSubIdFieldName()] = 1;
				$object->data[static::getSubIdFieldName()] = $this->getNextSubId();
				$sql[] = static::getTableName()  . "." . static::getSubIdFieldName() . ' = "' . mysql_real_escape_string($object->data[static::getSubIdFieldName()]) . '"';
				
				$sql = 'INSERT INTO ' . static::getTableName() . " SET " .  implode(",",$sql);
				$result = mysql_query($sql,$this->openMasterConnection()) or trigger_error("DAOFactory Insert Error: ". htmlentities($sql), E_USER_ERROR);
				$object->data[static::getIdField()] = mysql_insert_id();
			}
			
			@mysql_free_result($result);
			unset($object->modifiedColumns);
		}
		*/
	}
	
	function addCollectionIdBinding($collectionId)
	{
		$this->addBinding(new EqualsBinding(static::getSubIdCollectionIdFieldName(), $collectionId));
	}
	
	function findSubId($collectionId,$id)
	{
		$this->addCollectionIdBinding($collectionId);
		$this->addBinding(new EqualsBinding(static::getSubIdFieldName(),intval($id)));
		$this->setLimit(1);
		return $this->getQueryFirst();
	}
	
	function getSubIdFieldName()
	{
		die("getSubIdFieldName");
		//return static::DEFAULT_SUB_ID_FIELD;
	}
	
	function getSubIdCollectionIdFieldName()
	{
		die("getSubIdCollectionIdFieldName");
		//return static::DEFAULT_SUB_ID_COLLECTION_ID_FIELD;
	}
	
	function getNextSubId($collectionId)
	{
		$this->addCollectionIdBinding($collectionId);
		return intval($this->getMaxField(static::getSubIdFieldName(),$this->conditional->getSQL())) + 1;
	}
	
}

?>