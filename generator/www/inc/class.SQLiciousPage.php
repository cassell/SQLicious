<?php

class SQLiciousPage
{
	var $scripts;
	var $styleSheets;
	
	const FAVICON_PNG = 'img/favicon.png';
	const TOUCH_ICON_PNG = 'img/touch_icon.png';
	
	function __construct($sqlicious)
	{
		$this->sqlicious = $sqlicious;
		
		$this->insertScript('js/mootools_core.js');
		$this->insertScript('js/mootools_more.js');
		$this->insertScript('js/mootools_sqlicious.js');
		
		$this->insertScript('js/onhashchange.js');
		
		$this->insertScript('js/page.js');
		
		$this->insertStyleSheet('css/reset.css');
		$this->insertStyleSheet('css/sqlicious.css');
		
		$this->insertJavascriptData($this->getConfigData(),'config');
		
		
		
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
				$d['name'] = $db->getDatabaseName();	
				$data['db'][] = $d;
			}
		}
		
		return $data;
	}
	
	function display()
	{
		$this->printHtmlHeader();
		
		echo '<div id="pageTop"></div>';
		echo '<div id="content"></div>';
		echo '<div id="pageBottom"></div>';
		
		$this->printHtmlFooter();
		
	}
	
	
	function insertScript($scriptName)
	{
		$this->scripts[] =  self::formatScript($scriptName);
	}
	
	function insertStyleSheet($styleSheet)
	{
		$this->styleSheets[] = self::formatStyleSheet($styleSheet);
	}
	
	function insertPrintMediaStyleSheet($styleSheet)
	{
		$this->styleSheets[] = self::formatPrintStyleSheet($styleSheet);
	}
	
	function insertJavaScriptBlock($block)
	{
		$this->scripts[] = '<script language="Javascript" type="text/javascript">' . $block . '</script>';
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
	
	function insertStyleBlock($block)
	{
		$this->styleSheets[] = '<style type="text/css">' . $block . '</style>';
	}
	
	function formatScript($scriptName)
	{
		return '<script language="Javascript" type="text/javascript" src="' . $scriptName . '"></script>';
	}
	
	function formatStyleSheet($styleSheet)
	{
		return '<link rel="stylesheet" href="' . $styleSheet . '" type="text/css" />';
	}
	
	function formatPrintStyleSheet($styleSheet)
	{
		return '<link rel="stylesheet" href="' . $styleSheet . '" type="text/css" media="print" />';
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
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">',
			 '<html xmlns="http://www.w3.org/1999/xhtml">',
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