<?php

require_once(TESTS_CONFIG_PATH."../dao/class.PeopleDaoFactory.php");
require_once(TESTS_CONFIG_PATH."../dao/class.ZipcodesDaoFactory.php");

class SQLiciousDaoTests extends \Enhance\TestFixture
{
	// http://www.enhance-php.com/Content/Quick-Start-Guide/

    public function instantiateAndConnectToDatabase() 
    {
		$dp = new DatabaseProcessor('sqlicious_test');
		$dp->openNewConnection();
		$dp->setSQL('SHOW TABLES');
		$result = $dp->query();
		
		\Enhance\Assert::areIdentical(get_class($result), "mysqli_result");
    }
	
//	public function dropZipcodesTable() 
//    {
//		$dp = new DatabaseProcessor('sqlicious_test');
//		$dp->openNewConnection();
//		$dp->update('DROP TABLE `zipcodes`;DROP TABLE `people`');
//    }
//	
//	public function testDatabaseLoadFileExists()
//	{
//		\Enhance\Assert::isTrue(strlen(file_get_contents(TESTS_CONFIG_PATH."../database/sqlicious-test.sql")) > 0);
//	}
//	
//	public function loadDatabases()
//	{
//		$dp = new DatabaseProcessor('sqlicious_test');
//		$dp->openNewConnection();
//		$dp->update(file_get_contents(TESTS_CONFIG_PATH."../database/sqlicious-test.sql"));
//	}
	
	public function countNumberOfTables()
	{
		$dp = new DatabaseProcessor('sqlicious_test');
		$dp->openNewConnection();
		$dp->setSQL('SHOW TABLES');
		$result = $dp->query();
		
		\Enhance\Assert::areIdentical($result->num_rows, 2);
	}
	
	public function countTotalRows()
	{
		$f = new ZipcodesDaoFactory();
		
		\Enhance\Assert::areIdentical($f->count(), 33178);
	}
	
	public function countFilteredRows()
	{
		$f = new ZipcodesDaoFactory();
		$f->addBinding("state LIKE 'PA'");
		
		\Enhance\Assert::areIdentical($f->count(), 1776);
	}
	
	public function equalsBinding()
	{
		$f = new ZipcodesDaoFactory();
		$f->addBinding(new EqualsBinding('state', 'PA'));
		
		\Enhance\Assert::areIdentical($f->count(), 1776);
	}
	
	public function containsBinding()
	{
		$f = new ZipcodesDaoFactory();
		$f->addBinding(new ContainsBinding('state_name', 'New'));
		
		\Enhance\Assert::areIdentical($f->count(), 2877);
	}
	
	public function getFirstObject()
	{
		$f = new ZipcodesDaoFactory();
		$f->addBinding(new EqualsBinding('zipcode', '20170'));
		
		$herndon = $f->getFirstObject();
		
		\Enhance\Assert::areIdentical(get_class($herndon), "ZipcodesDaoObject");
		\Enhance\Assert::areIdentical($herndon->getCity(), "Herndon");
		\Enhance\Assert::areIdentical($herndon->getStateName(), "Virginia");
	}
	
	
}
		
?>
