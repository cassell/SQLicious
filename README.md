

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

Usage Examples
==============

	/* Object Queries */
	// find user with primary key 17
	$f = new UserFactory();
	$user = $f->findId(17);
	
	// shorthand find with primary key 17
	$user = User::findId(17);
	
	// creating a new record
	$user = new User();
	$user->setFirstName('Ada');
	$user->setLastName('Lovelace');
	$user->setEmail('lovelace@example.com');
	$user->save();
	echo $user->getId() // will print the new primary key

	// finding non archived users
	$f = new UserFactory();
	$f->addBinding(new EqualsBinding("archived","0"));
	$users = $f->query();
	
	// looking for users with example.com in their email
	$f = new UserFactory();
	$f->addBinding(new ContainsBinding("email","example.com"));
	$users = $f->query();
	
	// select only users first name, last name, and email but still wrap up in objects
	$f = new UserFactory();
	$f->setSelectFields("first_name","last_name","email");
	$users = $f->query();
	
	/* Data Queries */
	// now do the same query but output as JSON ready arary
	$f = new UserFactory();
	$f->setReturnTypeToJSON();
	$f->setSelectFields("first_name","last_name","email");
	$userJSON = $f->query();
	
	// or go straight to JSON encoded string
	$f = new UserFactory();
	$f->setReturnTypeToJSONString();
	$f->setSelectFields("last_name","email");
	echo $f->query(); // prints [ { 'id' : 1, 'lastName' : 'Smith', 'email' : 'smith@example.com'}, { 'id' : 2, 'lastName' : 'Smith', 'email' : 'smith@example.com'} ]
	
	// limit to 20 rows
	$f = new UserFactory();
	$f->setLimit(20);
	$users = $f->query();
	
	// now do the same query but output as PHP Array
	$f = new UserFactory();
	$f->setReturnTypeToArray();
	$userArray = $f->query();
	

Example Mac Apache Config:
=============
	<VirtualHost *:80>
	        DocumentRoot /Library/WebServer/Documents/sqlicious/www
	        ServerName sqlicious.local
	        RewriteEngine on
	</VirtualHost>