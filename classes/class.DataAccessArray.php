<?php

class DataAccessArray
{
	var $data;
	
	function __construct($row)
	{
		$this->data = $row;
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
	function toCSV()
	{
		return implode(",",array_values($this->toArray()));
	}
	
	// utils
	static function toFieldCase($val)
	{
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