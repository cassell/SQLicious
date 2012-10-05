<?php

require_once(TESTS_CONFIG_PATH."../dao/class.PeopleDaoFactory.php");
require_once(TESTS_CONFIG_PATH."../dao/class.ZipcodesDaoFactory.php");

class DaoFactoryTests extends \Enhance\TestFixture
{
	// http://www.enhance-php.com/Content/Quick-Start-Guide/

	public function countTotalRows()
	{
		$f = new ZipcodesDaoFactory();
		
		\Enhance\Assert::areIdentical($f->count(), 33178);
	}
	
	
	function findId()
	{
		\Enhance\Assert::isTrue(false);
	}
	
	
	// return all objects
	function findAll()
	{
		\Enhance\Assert::isTrue(false);
	}
	
	// generate the select clause from $this->fields
	function getSelectClause()
	{
		\Enhance\Assert::isTrue(false);
	}
    
    function getFromClause()
    {
       \Enhance\Assert::isTrue(false);
    }
	
	function setSelectFields()
	{
		\Enhance\Assert::isTrue(false);
	}
	function addSelectField()
	{
		\Enhance\Assert::isTrue(false);
	}
	
	// joins
	function join()
	{
		\Enhance\Assert::isTrue(false);
	}
	
	
	// group by
	function groupBy()
	{
		\Enhance\Assert::isTrue(false);
	}
	
	// order by
	function orderBy()
	{
		\Enhance\Assert::isTrue(false);
	}
	
	function orderByAsc()
	{
		\Enhance\Assert::isTrue(false);
	}
	
	// limits
	function limit()
	{
		\Enhance\Assert::isTrue(false);
	}
	
	function count()
	{
        \Enhance\Assert::isTrue(false);
	}
    
    function sum()
    {
         \Enhance\Assert::isTrue(false);
    }
    
	function paging()
	{
		\Enhance\Assert::isTrue(false);
	}
    
    public function truncateTable()
	{
		\Enhance\Assert::isTrue(false);
	}
	
    /* below are functions that are slowly being phased out */
    // used to do custom queries, uses the same get select clause that the query() method 
	function find()
	{
		\Enhance\Assert::isTrue(false);
	}
    
    function deleteWhere()
	{
		\Enhance\Assert::isTrue(false);
	}
	
	// find the first object matching the clause
	function findFirst()
	{
		\Enhance\Assert::isTrue(false);
	}
	
	function findDistinctField()
	{
		\Enhance\Assert::isTrue(false);
	}
	
	function findField()
	{
		\Enhance\Assert::isTrue(false);
	}
	
	function findFirstField()
	{
		\Enhance\Assert::isTrue(false);
	}
	
	function getCount()
	{
		\Enhance\Assert::isTrue(false);
	}
	
	function getMaxField()
	{
		\Enhance\Assert::isTrue(false);
	}
	
	function getSumField()
	{
		\Enhance\Assert::isTrue(false);
	}
	
    // deprecate old naming convetion
	function orderByField()
	{
		\Enhance\Assert::isTrue(false);
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