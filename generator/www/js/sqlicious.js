(function($, window, document) {

	$(function() {
		
		// drop a {{debug}} in your template and get a nice output to your console
		Handlebars.registerHelper("debug", function(optionalValue) {console.log("Current Context");console.log("====================");console.log(this);if (optionalValue) {console.log("Value");console.log("====================");console.log(optionalValue);}});

		var SQLicious = Ember.Application.create({
			rootElement: '#content',
			LOG_TRANSITIONS: true
		});
		
		SQLicious.getAPIUrl = function(url)
		{
			return window.location.href.replace(window.location.hash,"").replace("index.php","") + url.substring(1);
		}
		
		SQLicious.ajaxWithErrorHandling = function(options)
		{
			options.dataType = 'json';
			
			$.ajax(SQLicious.getAPIUrl(options.url),{
				success : options.success,
				data : options.data,
				dataType: options.dataType
			});
		}
		
		// app controller
		SQLicious.ApplicationController = Ember.Controller.extend();
		SQLicious.ApplicationView = Ember.View.extend({
			templateName: 'sqlicious-app-template'
		});
		
		SQLicious.Model =  Ember.Object.extend();
		
		SQLicious.Database = SQLicious.Model.extend({
			databaseName: null
		});
		SQLicious.DatabaseTable = SQLicious.Model.extend({
			tableName: null,
			database: new SQLicious.Database()
		});
		
		SQLicious.Database.reopenClass({
			
			findAll: function()
			{
				var dbs = new Array()
				
				$.each(config.db,function(index,db)
				{
					dbs.push(SQLicious.Database.create({
						'databaseName': db.databaseName
					}));
				});
					
				return dbs;
			},
			
			find: function(databaseName)
			{
				var database;
				
				$.each(config.db,function(index,db)
				{
					if(db.name == databaseName)
					{
						database = SQLicious.Database.create({
							'databaseName': db.databaseName
						});
					}
				});
				
				return database;
			}
		});
		
		// dashboard (index)
		SQLicious.IndexController = Ember.Controller.extend();
		SQLicious.IndexView = Ember.View.extend();
		SQLicious.IndexRoute = Ember.Route.extend({
			setupController: function(controller) {
				controller.set('dbs',SQLicious.Database.findAll());
			}
		});
		
		SQLicious.Router.map(function() {
			
			this.route('database', {path: '/database/:databaseName'});
			this.route('table', {path: '/database/:databaseName/table/:tableName'});
			this.route('objectCreation', {path: '/database/:databaseName/table/:tableName/objectCreation'});
			this.route('structure', {path: '/database/:databaseName/table/:tableName/structure'});
			this.route('extendedObjectStubs', {path: '/database/:databaseName/table/:tableName/extendedObjectStubs'});
			this.route('api', {path: '/database/:databaseName/table/:tableName/api'});
			
		});
		
		// database page is a list of tables
		SQLicious.DatabaseView = Ember.View.extend();
		SQLicious.DatabaseController = Ember.ObjectController.extend({});
		
		// sub-template controller and view for database page
		SQLicious.DatabaseTablesView = Ember.View.extend();
		SQLicious.DatabaseTablesController = Ember.ArrayController.extend();
		
		SQLicious.DatabaseRoute = Ember.Route.extend({
			
			setupController: function()
			{
				this.databaseTablesController = this.controllerFor('databaseTables');
				this.databaseTablesController.set('content',[]);
			},
			
			activate: function()
			{
				SQLicious.ajaxWithErrorHandling({ 
					url : '/api/tables/list.php',
					data : {'database' : this.context.databaseName},
					success : function(resp)
					{
						this.databaseTablesController.set('database',SQLicious.Database.find(resp.databaseName));
						this.databaseTablesController.set('content',resp.tables);
					}.bind(this)
				});
			},
			
			model: function(params)
			{
				var db = SQLicious.Database.find(params.databaseName);
				return db;
			},
			
			serialize: function(model,params)
			{
				console.log({'model':model,'params' : params});
				
				return {databaseName: model.databaseName};
			}
			
		});
		
		
		SQLicious.TableView = Ember.View.extend();
		SQLicious.TableController = Ember.ObjectController.extend({});
		
		SQLicious.TableRoute = Ember.Route.extend({
			
			templateName: 'table',
			
			setupController: function(controller) {
				
				console.log(controller.content);
				//controller.set('table',{'tableName' : 'testTable'});
				//controller.set('db',SQLicious.Database.find('intranet'));
			},
			
			model: function(params)
			{
				var table = new SQLicious.DatabaseTable(
				{
					tableName : params.tableName,
					database : SQLicious.Database.find(params.databaseName),
					databaseName: params.databaseName
				});
				
				return table;
				
				
//				console.log('SQLicious.TableRoute -> model');
//				console.log(params);
//				var db = SQLicious.Database.find(params.databaseName);
//				return db;
			},
			
			activate: function()
			{
				console.log('SQLicious.TableRoute -> activate');
				console.log(this.context);
			},
			
			serialize: function(model,params)
			{
				return {databaseName: model.databaseName, tableName : model.tableName};
			}
			
		});
		
//		SQLicious.TableStructureRoute = Ember.Route.extend({
//			
//			activate: function()
//			{
//				console.log('SQLicious.TableStructureRoute -> activate');
//			}
//		});
		
//		
//		SQLicious.TableOptionsRoute = Ember.Route.extend({
//			
//			templateName: 'table-options'
//			
//		});
		
		
		
//		SQLicious.TableIndexView = Ember.View.extend();
//		SQLicious.TableIndexController = Ember.ObjectController.extend({});
//		
//		SQLicious.TableIndexRoute = Ember.Route.extend({
//			
//			serialize: function(model,params)
//			{
//				return {name: model.databaseName, tableName : model.tableName};
//			}
//			
//		});
		
		
		/*
		
		// ember-data
		SQLicious.Store = DS.Store.extend({
			revision: 11,
			adapter: 'DS.FixtureAdapter'
		});
		
		SQLicious.Database = DS.Model.extend({
			tables: DS.hasMany('SQLicious.Tables')
		});
		SQLicious.Database.FIXTURES = [{"id":"intranet"},{"id":"mail"},{"id":"customer_survey"},{"id":"msrc"}]; //config.db;
		
		SQLicious.Tables = DS.Model.extend({
			name: DS.attr('string')
		});
		
		// dashboard (index)
		SQLicious.IndexController = Ember.Controller.extend();
		SQLicious.IndexView = Ember.View.extend();
		SQLicious.IndexRoute = Ember.Route.extend({
			setupController: function(controller) {
				controller.set('dbs',SQLicious.Database.find());
			}
		});
		
		
		// Router
		SQLicious.Router.map(function() {
			this.resource('databases', function() {
				this.resource('database', {path: ':id'});
			});
		});
		
		SQLicious.DatabasesView = Ember.View.extend();
		SQLicious.DatabasesController = Ember.ObjectController.extend({});
		SQLicious.DatabasesRoute = Ember.Route.extend({
			enter: function(router, context) {
				
				console.log(router);
				console.log(context);
				 
			},
			
			setupController: function(controller, params)
			{
				console.log("DatabasesRoute");
				console.log(controller);
				console.log(params);
				//controller.set('database',SQLicious.Database.find(params.id));
				//console.log(SQLicious.Database.find(params.id));
				//this.controllerFor('database').set('content');
			}
		});
		
		SQLicious.DatabaseView = Ember.View.extend();
		SQLicious.DatabaseController = Ember.ObjectController.extend({});
		SQLicious.DatabaseRoute = Ember.Route.extend({
			model: function(params) {
				return SQLicious.Database.find(params.id);
			},
			serialize: function(model) {
				// this will make the URL `/posts/12`
				return { post_id: model.id };
			}
		});
		
		*/
		
		
		/*
		SQLicious.IndexRoute = Ember.Route.extend({
			setupController: function(controller) {
				controller.set('databases',config.db);
			}
		});
		
		// Databases
		
		
		// Tables
		
		*/
		
		/*
		$.sqlicious = {};
		
		$.sqlicious.postJSON = function(url,data,success)
		{
			return $.ajax({
				type: "POST",
				url: url,
				data: data,
				success: success,
				dataType: "json"
			});
		}
		
		$.sqlicious.getAjaxUrl = function(url)
		{
			return window.location.href.replace(window.location.hash,"").replace("index.php","") + "ajax/" + url;
		};
		
		var App = Ember.Application.create({
			rootElement: '#content'
		});
		
		// app
		SQLicious.ApplicationController = Ember.Controller.extend();
		SQLicious.ApplicationView = Ember.View.extend({
				templateName: 'sqlicious-app-template'
		});
		
		// dashboard
		SQLicious.IndexController = Ember.Controller.extend();
		SQLicious.IndexView = Ember.View.extend({
				templateName: 'dashboard-template'
		});
		SQLicious.IndexRoute = Ember.Route.extend({
			setupController: function(controller) {
				controller.set('databases',config.db);
			},
			renderTemplate: function() {
				this.render('dashboard-template');
			}
		});
		
		
		
//		SQLicious.Database = Ember.Object.extend({
//			name : 'database',
//			tables : [],
//			loadTables: function()
//			{
//				$.sqlicious.postJSON($.sqlicious.getAjaxUrl('list_tables.php'), {'database' : this.name}, function(json) {
//					this.set('tables',json);
//				}.bind(this));
//			}
//		});
		
//		// database page
//		SQLicious.DatabaseController = Ember.ObjectController.extend({
//			getTables: function()
//			{
//				this.get('content.tables')
//			}.property('content.tables')
//		});

			// database page
		SQLicious.DatabaseController = Ember.ObjectController.extend({
			content : {
				name: '',
				tables: []
			},
			
			tables: function()
			{
				return this.get('content.tables');
			}.property('content.tables')
			
		});
		
		SQLicious.DatabaseView = Ember.View.extend({
			templateName: 'database-template'
		});
		
		SQLicious.DatabaseRoute = Ember.Route.extend({
			model: function(params) {
				
				this.params = params;
				//this.database = SQLicious.Database.create({name:params.name});
				//this.database.loadTables();
				// new Request.WithErrorHandling({'url': this.getAjaxUrl('list_tables.php'), onSuccess: this.listTables.bind(this)}).send(Object.toQueryString({'database' : this.database}));
				
			},
			setupController: function(controller,params) {
				
				//console.log('setupController params');
				//console.log(this.params.name);
				//console.log(this.datasbaseName);
				
				controller.set('content',{'databaseName' : this.params.name});
				
				$.sqlicious.postJSON($.sqlicious.getAjaxUrl('list_tables.php'), {'database' : this.params.name}, function(json) {
					controller.set('content',json);
				});
				
				//
			},
			renderTemplate: function() {
				this.render('database-template');
			}
			
		});
		
		SQLicious.Router.map(function() {
			this.route("database", {
				path: "/database/:name"
			});
		});
		
		*/

	});

}(window.jQuery, window, document));
 
 


