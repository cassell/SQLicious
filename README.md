![SQLicious Logo](http://static.andrewcassell.com/github/sqlicious/SQLicious.png)

SQLicious
=============

SQLicious is a PHP Active Record ORM that generates an object models from your MySQL database. Its powerful closure based query processing and ability to handle large datasets make it powerful and flexible. Its included web interface and ease of development make it a joy to use.

The features that make SQLicious easy and powerful are:

1. Web UI for code generation and fast paced development. It helps with common programming tasks (object creation, APIs, class stubs, queries).
1. Easily output data as JSON for APIs
1. Queries can easily be limited to a subset of fields in a table ("select first_name, last_name from table" vs. "select * from table"). You can still use objects when using a subset of the fields.
1. UPDATEs are minimal and only changed columns are updated
1. Closure based query processing that lets you handle data efficently and fully customizable manner
1. Buffered queries for performance and Unbuffered queries for processing huge datasets while staying memory safe
1. You can extend the Factories and Objects to encapsulate the logic of a model
1. Process any SQL query (multiple tables and joins) using the same closure based process model, or get an Array, or JSON
1. Handles the CRUD (Creating, Reading, Updating, and Deleting)
1. Convert Timezones Using MySQL Timezone Tables
1. Generated Code is creating using Mustache Templates
1. Command line tool for generating code
1. Generate JSON APIs easily


CRUD: Creating, Reading, Updating, and Deleting
==============

Creating a new record
	
	$user = new User();
	$user->setFirstName('Ada');
	$user->setLastName('Lovelace');
	$user->setEmail('lovelace@example.com');
	$user->save();
	echo $user->getId() // will print the new primary key
	
Finding an object with id 17.
	
	$f = new UserFactory();
	$user = $f->getObject(17);
	
	// shorthand
	$user = User::findId(17);

Querying for objects
	
	$f = new UserFactory();
	$f->addBinding(new EqualsBinding("archived","0"));
	$users = $f->getObjects();
	
Contains searches for objects
	
	// looking for users with example.com in their email
	$f = new UserFactory();
	$f->addBinding(new ContainsBinding("email","example.com"));
	$users = $f->getObjects();
	
	
String based binding clauses
	
	// looking for active users
	$f = new UserFactory();
	$f->addBinding("user.archived != 1");
	$users = $f->getObjects();
	
Updating a record.
	
	$user = User::findId(17);
	$user->setArchived(1);
	$user->save();
	
Deleting a single record.
	
	$user = User::findId(18);
	$user->delete();

Deleting multiple records.

	// delete archived users
	$f = new UserFactory();
	$f->addBinding(new EqualsBinding("archived","1"));
	$f->delete();
	
Limit the query to the first 20 rows
	
	$f = new UserFactory();
	$f->setLimit(20);
	$users = $f->getObjects();
	
Running a count query
	
	$f = new UserFactory();
	$f->addArchivedFalseBinding()
	$count = $f->count(); // count of all not archived users
	

Performance
=============
Limiting the fields that are pulled back from the database. You can still use objects
	
	$f = new UserFactory();
	$f->setSelectFields("first_name","last_name","email");
	$users = $f->getObjects();
	
Getting a JSON ready array
	
	$f = new UserFactory();
	$f->setSelectFields("first_name","last_name","email");
	$userJSON = $f->getJSON(); // returns an an array of PHP objects that can be encoded to  [ { 'id' : 1, 'firstName' : 'John', 'lastName' : 'Doe', 'email' : 'doe@example.com'}, ... ]
	

Closures
============
Process each row queried with an anonymous function. To iterate over very large datasets without hitting memory constraints use unbufferedProcess()
	
	$f = new UserFactory();
	$f->process(function($user)
	{
		if(!validate_email($user->getEmail()))
		{
			$user->setEmail('');
			$user->save();
		}
	});

Getting count of Rows before process
	
	$f = new UserFactory();
	$f->query();
	$countOfUsers = $f->getNumberOfRows();
	$f->process(function($user)
	{
		if(!validate_email($user->getEmail()))
		{
			$user->setEmail('');
			$user->save();
		}
	});
	$f->freeResult();


Memory Safe Outputs (works with billions of rows)
============	

Output directly to CSV
	
	$f = new UserFactory();
	$f->outputCSV();
	
Output directly to JSON
	
	$f = new UserFactory();
	$f->outputJSONString();


Memory Safe Closures
============
	
Unbuffered Processing of large datasets	(will potentially lock the table while processing)
	
	$f = new UserFactory(); // imagine a table with millions of rows
	$f->unbufferedProcess(function($user)
	{
		if(!validate_email($user->getEmail()))
		{
			$user->setEmail('');
			$user->save();
		}
	});
	
Data Processors
=============

Data processors are great for processing the results from an entirely custom SELECT query with closures.

Buffered Queries for Speed	
	
	$p = new DatabaseProcessor('example');
	$p->setSQL('select first_name, last_name from user');
	$p->process(function($row)
	{
		echo $row['first_name'];
		print_r($row);
		
	});

Unbuffered for Large Datasets

	$p = new DatabaseProcessor('example');
	$p->setSQL('select first_name, last_name from user');
	$p->unbufferedProcess(function($row)
	{
		echo $row['first_name'];
	});

	
Other flexibile queries
============
	
Find method for writing a custom where clause (returns objects)
	
	$f = new UserFactory();
	$users = $f->findObjectWhere("where archived != 1 and email like '%@example.com'");
	
	

Web UI
===========
http://demo.sqlicious.com


Converting Timezones
=============

	// Note requires time zones installed in mysql database
	// usage: ($dateTime,$sourceTimezone,$destTimezone). $dateTime may be string or time(), returns a timestamp
	$f = new UserLoginFactory();
	$centralTime = $f->convertTimezone('2012-02-23 04:10PM', 'US/Eastern',  'US/Central');


Setup
=============

1. Download the source code
2. Use the example.config.inc.php to build your config.inc.php
3. Make sure the generator has write access to the folders you specify in config
4. Generate the DAO using the web UI or command line
5. require_once('sqlicious.inc.php') in your project
6. require_once or include any other generated factories that you need in your project (suggestion: use an autoloader)

Requirements
=============
* PHP 5.3 or greater
* MySQL

What next?
=============
Profit.