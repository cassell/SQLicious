<?php 

class SQLiciousTools
{
	function __construct()
	{
		
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