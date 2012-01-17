


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