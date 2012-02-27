<?php 

class SQLiciousTools
{
	function __construct()
	{
		
	}
	
	function doesExtendedDaoObjectExist()
	{
		return true;
	}
	
	function getExtendedDaoClassRequireOnce($db,$className)
	{
		return $this->getDaoClassRequireOnce($db,$className."DaoObject");
	}
	
	function getDaoClassRequireOnce($db,$daoClassName)
	{
		$filePath = '';
		
		$this->find_files($db->getGeneratorDestinationDirectory(),"/class.".$daoClassName.".php/i",function($filename)  use (&$filePath)
		{
			$filePath = $filename;
		});
		
		return $this->getClassRequireOnce($filePath);
	}
	
	function getClassRequireOnce($filePath)
	{
		if(count($this->includePathStringReplace) > 0)
		{
			foreach($this->includePathStringReplace as $path)
			{
				if($path['constant'] == true && strpos($filePath, $path['search']) !== false)
				{
					return "require_once(".str_replace($path['search'], $path['replaceWith'] . ' . "', $filePath) . '");';
				}
				else if(strpos($filePath, $path['search']) !== false)
				{
					return 'require_once("'.str_replace($path['search'], $path['replaceWith'], $filePath) . '");';
				}
			}
			
			return 'require_once("'.$filePath. '");';
			
		}
		else
		{
			return 'require_once("'.$filePath. '");';
		}
		
	}
	
	
	/*
	function getExtendedClassRequireOnce($daoClassName)
	{
		$filePath = $this->getFilePathFromClassName($daoClassName);
		
		if(count($this->includePathStringReplace) > 0)
		{
			foreach($this->includePathStringReplace as $path)
			{
				if($path['constant'] == true && strpos($filePath, $path['search']) !== false)
				{
					$filePath = "require_once(".str_replace($path['search'], $path['replaceWith'] . ' . "', $filePath) . '");';
				}
				else if(strpos($filePath, $path['search']) !== false)
				{
					$filePath = 'require_once("'.str_replace($path['search'], $path['replaceWith'], $filePath) . '");';
				}
				
			}
			
		}
		
		return $filePath;
	}
	
	function getFilePathFromClassName($daoClassName)
	{
		$filePath = '';
		
		// look for dao factories
		if(count($this->getIncludePaths()) > 0)
		{
			foreach($this->getIncludePaths() as $path)
			{
				$this->find_files($path,"/class.".$daoClassName.".php/i",function($filename)  use (&$filePath)
				{
					
					$filePath = $filename;
				});
			}
		}
		
		return $filePath;
	}
	*/
	
	/*
	function getClassRequireOnce($daoClassName,$useExtended = false)
	{
		
		
		if(count($this->includePathStringReplace) > 0)
		{
			foreach($this->includePathStringReplace as $path)
			{
				if($path['constant'] == true)
				{
					$filePath = "require_once(".str_replace($path['search'], $path['replaceWith'] . ' . "', $filePath) . '");';
				}
				else
				{
					$filePath = "require_once(".str_replace($path['search'], $path['replaceWith'], $filePath) . '");';
				}
				
			}
			
		}
		
		
		return $filePath;
		
		
	}
	*/
	
	/*
	function getPHPRequireOnce($filePath)
	{
		if(count($this->includePathStringReplace) > 0)
		{
			foreach($this->includePathStringReplace as $path)
			{
				if($path['constant'] == true)
				{
					
					//$filePath = str_replace($path['search'], $path['replaceWith'] . ' . "', $filePath) . '"';
				}
				else
				{
					//$filePath = str_replace($path['search'], $path['replaceWith'], $filePath);
				}
			}
		}
		
		
		return "require_once(" . $filePath . ")";
		
		// "require_once(".$tools->stringReplaceFilePath() . ");
	}
	
	function stringReplaceFilePath($filePath)
	{
		if(count($this->includePathStringReplace) > 0)
		{
			foreach($this->includePathStringReplace as $path)
			{
				if($path['constant'] == true)
				{
					$filePath = str_replace($path['search'], $path['replaceWith'] . ' . "', $filePath);
				}
				else
				{
					$filePath = str_replace($path['search'], $path['replaceWith'], $filePath);
				}
			}
		}
		
		return $filePath;
		
	}
	*/
	
	
	function find_files($path, $pattern, $callback) {
	  $path = rtrim(str_replace("\\", "/", $path), '/') . '/';
	  $matches = Array();
	  $entries = Array();
	  $dir = @dir($path);
	  
	  if(!is_object($dir))
	  {
	  	return;
	  }
	  
	  
	  while (false !== ($entry = $dir->read())) {
	    $entries[] = $entry;
	  }
	  
	  $dir->close();
	  
	  foreach ($entries as $entry) {
	    $fullname = $path . $entry;
	    if ($entry != '.' && $entry != '..' && $entry != '.svn' && is_dir($fullname)) {
	      $this->find_files($fullname, $pattern, $callback);
	    } else if (is_file($fullname) && preg_match($pattern, $entry)) {
	      call_user_func($callback, $fullname);
	    }
	  }
	  
	  
	}
		
	function setLookForExtendedObjects($val) { $this->lookForExtendedObjects = $val; }
	function getLookForExtendedObjects() { return $this->lookForExtendedObjects; }
	
	function addIncludePath($path) { $this->addIncludePaths[] = $path; }
	function getIncludePaths() { return $this->addIncludePaths; }
	function addIncludePathStringReplace($search,$replaceWith,$constant = false) { $this->includePathStringReplace[] = array("search" => $search, "replaceWith" => $replaceWith,"constant" => $constant); }
	
}

?>