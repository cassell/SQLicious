(function($, window, document) {

	$(function() {
		
		// drop a {{debug}} in your template and get a nice output to your console
		Handlebars.registerHelper("debug", function(optionalValue) {console.log("Current Context");console.log("====================");console.log(this);if (optionalValue) {console.log("Value");console.log("====================");console.log(optionalValue);}});

		var SQLicious = Ember.Application.create({
			rootElement: '#content',
			LOG_TRANSITIONS: true
		});
		
		// app controller
		SQLicious.ApplicationController = Ember.Controller.extend();
		SQLicious.ApplicationView = Ember.View.extend({
				templateName: 'sqlicious-app-template'
		});
		
//		SQLicious.Model =  Ember.Object.extend({
//			ajax: function(url, args) {
//				var oldError = args.error;
//				args.error = function(xhr) {
//					return oldError($.parseJSON(xhr.responseText).errors);
//				};
//				return $.ajax(url, args);
//			}
//		});

		SQLicious.Model =  Ember.Object.extend({});
		
		SQLicious.Database = SQLicious.Model.extend({});
		
		SQLicious.Database.reopenClass({
			
			findAll: function()
			{
				var dbs = new Array()
				
				$.each(config.db,function(index,db)
				{
					dbs.push(SQLicious.Database.create({id: db.name, 'name': db.name}));
				});
					
				return dbs;
			},
			
			find: function(databaseName) {
				
				database = SQLicious.Database.create({id: databaseName, 'name':databaseName});
				return database;
			}
			
		});
		
		// dashboard (index)
		SQLicious.IndexController = Ember.Controller.extend();
		SQLicious.IndexView = Ember.View.extend();
		SQLicious.IndexRoute = Ember.Route.extend({
			setupController: function(controller) {
				controller.set('dbs',SQLicious.Database.findAll());
				//controller.set('dbs',config.db);
			},
			showDatabase: function(databaseName)
			{
				this.transitionTo('database',SQLicious.Database.find(databaseName));
			}
		});
		
		SQLicious.Router.map(function() {
			this.route('database', {path: '/databases/:name'});
		});
		
		
		SQLicious.DatabaseView = Ember.View.extend();
		SQLicious.DatabaseController = Ember.ObjectController.extend({});
		SQLicious.DatabaseRoute = Ember.Route.extend({
			
			model: function(params)
			{
				return SQLicious.Database.find(params.name);
			},
			
			serialize: function(model,params)
			{
				return { name: model.name };
			}
			
		});
		
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
 
 


