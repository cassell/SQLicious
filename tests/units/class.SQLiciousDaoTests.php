<?php

class SQLiciousDaoTests extends \Enhance\TestFixture
{
	// http://www.enhance-php.com/Content/Quick-Start-Guide/

    public function instantiateAndConnectToDatabase() 
    {
		$dp = new DatabaseProcessor('sqlicious_test');
		$dp->setSQL('SHOW TABLES');
		$result = $dp->query();
		
		\Enhance\Assert::areIdentical(get_class($result), "mysqli_result");
    }
	
	public function countNumberOfTables()
	{
		$dp = new DatabaseProcessor('sqlicious_test');
		$dp->setSQL('SHOW TABLES');
		$result = $dp->query();
		
		\Enhance\Assert::areIdentical($result->num_rows, 2);
	}
	
}
		
?>
