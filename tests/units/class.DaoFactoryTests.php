<?php

require_once(TESTS_CONFIG_PATH."../dao/class.PeopleDaoFactory.php");
require_once(TESTS_CONFIG_PATH."../dao/class.ZipcodesDaoFactory.php");

class DaoFactoryTests extends \Enhance\TestFixture
{
	// http://www.enhance-php.com/Content/Quick-Start-Guide/

	function findId()
	{
		$f = new ZipcodesDaoFactory();
		$beverlyHills = $f->findId(1968);
		
		\Enhance\Assert::areIdentical('90210',$beverlyHills->getZipcode());
	}
	
	
	// return all objects
	function findAll()
	{
		\Enhance\Assert::inconclusive();
	}
	
	// generate the select clause from $this->fields
	function getSelectClause()
	{
		\Enhance\Assert::inconclusive();
	}
    
    function getFromClause()
    {
        \Enhance\Assert::inconclusive();
    }
	
	function setSelectFields()
	{
		\Enhance\Assert::inconclusive();
	}
	function addSelectField()
	{
		\Enhance\Assert::inconclusive();
	}
	
	// joins
	function join()
	{
		\Enhance\Assert::inconclusive();
	}
	
	
	// group by
	function groupBy()
	{
		\Enhance\Assert::inconclusive();
	}
	
	// order by
	function orderBy()
	{
		\Enhance\Assert::inconclusive();
	}
	
	function orderByAsc()
	{
		\Enhance\Assert::inconclusive();
	}
	
	// limits
	function limit()
	{
		\Enhance\Assert::inconclusive();
	}
	
	function count()
	{
       $f = new ZipcodesDaoFactory();
	   \Enhance\Assert::areIdentical($f->count(), 33178);
	   
	   
	   $f = new ZipcodesDaoFactory();
	   $f->addBinding("state LIKE 'PA'");
	   \Enhance\Assert::areIdentical($f->count(), 1776);
	}
		
    function sum()
    {
         \Enhance\Assert::inconclusive();
    }
    
	function paging()
	{
		\Enhance\Assert::inconclusive();
	}
    
    public function truncateTable()
	{
		\Enhance\Assert::inconclusive();
	}
	
    /* below are functions that are slowly being phased out */
    // used to do custom queries, uses the same get select clause that the query() method 
	function find()
	{
		\Enhance\Assert::inconclusive();
	}
    
    function deleteWhere()
	{
		\Enhance\Assert::inconclusive();
	}
	
	// find the first object matching the clause
	function findFirst()
	{
		\Enhance\Assert::inconclusive();
	}
	
	function findDistinctField()
	{
		\Enhance\Assert::inconclusive();
	}
	
	function findField()
	{
		\Enhance\Assert::inconclusive();
	}
	
	function findFirstField()
	{
		\Enhance\Assert::inconclusive();
	}
	
	function getCount()
	{
		\Enhance\Assert::inconclusive();
	}
	
	function getMaxField()
	{
		\Enhance\Assert::inconclusive();
	}
	
	function getSumField()
	{
		\Enhance\Assert::inconclusive();
	}
	
    // deprecate old naming convetion
	function orderByField()
	{
		\Enhance\Assert::inconclusive();
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