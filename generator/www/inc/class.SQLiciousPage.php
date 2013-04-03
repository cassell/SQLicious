<?php

class SQLiciousPage
{
	var $scripts;
	var $styleSheets;
	var $htmlTitle = 'SQLicious';
	
	const FAVICON_PNG = 'img/favicon.png';
	const TOUCH_ICON_PNG = 'img/touch_icon.png';
	
	function __construct($sqlicious)
	{
		$this->sqlicious = $sqlicious;
		
		$this->insertScript('/js/jquery.js');
		$this->insertScript('/js/handlebars.js');
		$this->insertScript('/js/ember.js');
		$this->insertScript('/js/sqlicious.js');
		
		$this->insertStyleSheet('/css/bootstrap.css');
		$this->insertStyleSheet('/css/bootstrap-responsive.css');
		$this->insertStyleSheet('/css/font-awesome.css');
		$this->insertStyleSheet('/css/sqlicious.css');
		
		$this->insertJavascriptData($this->getConfigData(),'config');
		
		$this->insertHandlebarsTemplate('/app.template');
		
	}
	
	function getConfigData()
	{
		$data = array();
		
		$data['db'] = array();
		
		if($this->sqlicious->databases != null && count($this->sqlicious->databases) > 0)
		{
			foreach($this->sqlicious->databases as $db)
			{
				$d = array();
				$d['databaseName'] = $db->getDatabaseName();	
				$data['db'][] = $d;
			}
		}
		
		return $data;
	}
	
	function display()
	{
		$this->printHtmlHeader();
		echo '<div id="content"></div>';
		$this->printHtmlFooter();
	}
	
	function insertScript($scriptName)
	{
		$this->scripts[] =  '<script src="' . $scriptName . '"></script>';
	}
	
	function insertStyleSheet($styleSheet)
	{
		$this->styleSheets[] = '<link rel="stylesheet" href="' . $styleSheet . '" type="text/css" />';
	}
	
	function insertJavaScriptBlock($block)
	{
		$this->scripts[] = '<script>' . $block . '</script>';
	}
	
	function insertJavascriptData($array,$variableName = 'data')
	{
		if($array != null)
		{
			$this->insertJavaScriptBlock('var ' . $variableName . ' = ' . self::jsonEncodeArray($array) . ';');
		}
		else
		{
			$this->insertJavaScriptBlock('var ' . $variableName . ' = [];');
		}
	}
	
	function insertHandlebarsTemplate($templateFile)
	{
		$templateFile = rtrim(SQLICIOUS_INCLUDE_PATH,"/") . "/generator/www/inc/templates/" . $templateFile;
		
		if(file_exists($templateFile))
		{
			$this->scripts[] = file_get_contents($templateFile);
		}
		else
		{
			throw new Exception($templateFile . ' does not exist');
		}
	}
	
	function getScripts()
	{
		return @implode("",$this->scripts);
	}
	
	function getStyleSheets()
	{
		return @implode("",$this->styleSheets);
	}
	
	function setHtmlTitle($val) { $this->htmlTitle = $val; }
	function getHtmlTitle() { return $this->htmlTitle; }
	
	function printHtmlHeader()
	{
		echo '<html>',
		 	 '<head>',
		  	 '<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1" />',
			 '<meta http-equiv="Pragma" content="no-cache" />',
		 	 '<meta http-equiv="content-script-type" content="text/javascript" />',
			 '<title>' , $this->getHtmlTitle() , '</title>',
			 '<link rel="shortcut icon" href="', self::FAVICON_PNG ,'" type="image/png" />',
		     '<link rel="icon" href="', self::FAVICON_PNG ,'" type="image/png" />',
			 '<link rel="apple-touch-icon" href="' . self::TOUCH_ICON_PNG . '" />';
		
		echo $this->getScripts();
		echo $this->getStyleSheets();
		
		echo '</head>';

		flush();
		
		echo '<body>';
		
	}
	
	function printHtmlFooter() 
	{
		echo '</body></html>';
	}
	
	static function jsonEncodeArray($array)
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