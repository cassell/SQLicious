<?php

class DataAccessArray implements ArrayAccess
{
	var $data = array();
	
	function __construct($row)
	{
		$this->data = $row;
	}
	
	public function offsetSet($offset, $value)
	{
        if (is_null($offset))
		{
            $this->data[] = $value;
        }
		else
		{
            $this->data[$offset] = $value;
        }
    }
	
    public function offsetExists($offset)
	{
        return isset($this->data[$offset]);
    }
	
    public function offsetUnset($offset)
	{
        unset($this->data[$offset]);
    }
	
    public function offsetGet($offset)
	{
        return isset($this->data[$offset]) ? $this->data[$offset] : null;
    }
	
	// return something
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
	
	// returns an associative array of the row retrieved from the database
	function toArray()
	{
		return $this->data;
	}
	
	// returns an associative array with camel case array keys for use in javascript
	function toJSON()
	{
		$json = array();
		if($this->data != null)
		{
			foreach($this->data as $field => $value)
			{
				$json[self::toFieldCase($field)] = $value;
			}
		}
		
		return $json;
	}
	
	// returns a string 
	function toJSONString()
	{
		return self::JSONEncodeArray($this->toJSON());
	}
	
	// return the row values seperated by commas
//	function toCSV()
//	{
//		throw new SQLiciousErrorException("toCSV has not been implemented on DataAccessArray");
//	}
	
	// utils
	static function toFieldCase($val)
	{
		$result = '';
		
		$segments = explode("_", $val);
		for ($i = 0; $i < count($segments); $i++)
		{
		$segment = $segments[$i];
		if ($i == 0)
			$result .= $segment;
			else
				$result .= strtoupper(substr($segment, 0, 1)).substr($segment, 1);
		}
			return $result;
	}
	
	static function JSONEncodeArray($array)
	{
		return json_encode(self::utf8EncodeArray($array));
	}
	
	static function utf8EncodeArray($array)
	{
		foreach($array as $key => $value)
		{
			if(is_array($value))
			{
				$array[$key] = self::utf8EncodeArray($value);
			}
			else
			{
				$array[$key] = utf8_encode($value);
			}
		}

		return $array;
	}
}

?>