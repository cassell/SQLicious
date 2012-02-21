
SQLicious
=============

SQLicious is a PHP Database ORM and abstraction layer that handles generating
an object model from your database schema. The six features that make
SQLicious easy, powerful, and a joy to use:

1. Handles the CRUD
1. Web UI for common programming tasks (object creation, class stubs, queries).
1. Queries can easily be limited to a subset of fields in a table ("select first_name, last_name ..." vs. "select *")
1. Updates are minimal and only changed columns are updated
1. Closure based query processing that lets you handle data efficently, within memory constraints, and gracefully
1. Factories and Objects are Automatically Generated



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
	
Updating a record.
	$user = User::findId(17);
	$user->setArchived(1);
	$user->save();
	
Deleting a record.
	$user = User::findId(18);
	$user->delete();
	
Limit the query to the first 20 rows
	$f = new UserFactory();
	$f->setLimit(20);
	$users = $f->getObjects();
	

Performance
=============
Limiting select
	$f = new UserFactory();
	$f->setSelectFields("first_name","last_name","email");
	$users = $f->getObjects();
	
Getting a JSON ready array
	$f = new UserFactory();
	$f->setSelectFields("first_name","last_name","email");
	$userJSON = $f->getJSON(); // returns an an array of PHP objects that can be encoded to  [ { 'id' : 1, 'firstName' : 'John', 'lastName' : 'Doe', 'email' : 'doe@example.com'}, ... ]
	

Closures
============
Process each row queried with an anonymous function. You can iterate over very large datasets without hitting memory constraints
	$f = new UserFactory();
	$f->process(function($user)
	{
		if(!validate_email($user->getEmail()))
		{
			$user->setEmail('');
			$user->save();
		}
	});

Memory Safe Outputs
============	
Output directly to CSV
	$f = new UserFactory();
	$f->outputCSV();
	
	

Setup
=============

1. Setup a webhost for the SQLicious project or place the SQLicious folder inside your project. (See the example Mac Apache Virtual Host config below)
2. Use the example.config.inc.php to build your config.inc.php
3. Make sure the generator has write access to the folders you specify in config
4. Generate the DAO using the web UI
5. Make sure to include the following files in your project:
	* classes/class.DataAccessObject.php
	* classes/class.DataAccessObjectFactory.php
	* the generated class.DatabaseConnector.php
6. Include any other generated factories that you need in your project



Example Mac Apache Config:
=============
	<VirtualHost *:80>
	        DocumentRoot /Library/WebServer/Documents/sqlicious/www
	        ServerName sqlicious.local
	        RewriteEngine on
	</VirtualHost>