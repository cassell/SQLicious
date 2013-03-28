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
					if(db.databaseName == databaseName)
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
			
			this.route('generate', {path: '/generate'});
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
				return SQLicious.Database.find(params.databaseName);
			},
			
			serialize: function(model,params)
			{
				if(model)
				{
					return {databaseName: model.databaseName};
				}
				else
				{
					return {};
				}
				
			}
			
		});
		
		SQLicious.TableView = Ember.View.extend();
		SQLicious.TableController = Ember.ObjectController.extend({});
		
		SQLicious.TableRoute = Ember.Route.extend({
			
			templateName: 'table',
			
			setupController: function(controller) {
				controller.set('database',SQLicious.Database.find(this.context.databaseName));
			},
			
			model: function(params)
			{
				return SQLicious.DatabaseTable.create(
				{
					tableName : params.tableName,
					database : SQLicious.Database.find(params.databaseName),
					databaseName: params.databaseName
				});
				
			},
			
			serialize: function(model,params)
			{
				if(model)
				{
					return {databaseName: model.databaseName, tableName : model.tableName};
				}
				else
				{
					return {};
				}
			}
			
		});
		
		SQLicious.ObjectCreationView = Ember.View.extend();
		SQLicious.ObjectCreationController = Ember.ObjectController.extend({});
		
		SQLicious.ObjectCreationRoute = Ember.Route.extend({
			
			templateName: 'table',
			
			setupController: function(controller) {
				controller.set('database',SQLicious.Database.find(this.context.databaseName));
				controller.set('table',this.model({'databaseName':this.context.databaseName,'tableName':this.context.tableName}));
			},
			
			model: function(params)
			{
				return SQLicious.DatabaseTable.create(
				{
					tableName : params.tableName,
					database : SQLicious.Database.find(params.databaseName),
					databaseName: params.databaseName
				});
				
			},
			
			serialize: function(model,params)
			{
				return {databaseName: model.databaseName, tableName : model.tableName};
			},
			
			activate: function()
			{
				SQLicious.ajaxWithErrorHandling({ 
					url : '/api/table/object_creation.php',
					data : {'database' : this.context.databaseName, 'table' : this.context.tableName},
					success : function(resp)
					{
						this.controller.set('responseTemplate',resp.html);
						// responseTemplate
						console.log(resp);
					}.bind(this)
				});
			}
			
		});
		

	});

}(window.jQuery, window, document));
 
 


