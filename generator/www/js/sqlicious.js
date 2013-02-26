(function($, window, document) {

	$(function() {

		var App = Ember.Application.create({
			rootElement: '#content'
		});
		
		// app
		App.ApplicationController = Ember.Controller.extend();
		App.ApplicationView = Ember.View.extend({
				templateName: 'sqlicious-app-template'
		});
		
		// dashboard
		App.IndexController = Ember.Controller.extend();
		App.IndexView = Ember.View.extend({
				templateName: 'dashboard-template'
		});
		App.IndexRoute = Ember.Route.extend({
			setupController: function(controller) {
				controller.set('databases',config.db);
			},
			renderTemplate: function() {
				this.render('dashboard-template');
			}
			
		});
		
		App.DatabaseController = Ember.Controller.extend();
		App.DatabaseView = Ember.View.extend({
				templateName: 'database-template'
		});
		App.DatabaseRoute = Ember.Route.extend({
			setupController: function(controller) {
				alert('here');
			},
			renderTemplate: function() {
				this.render('database-template');
			}
			
		});
		
		App.Router.map(function() {
			this.route("database", {
				path: "/database/:name"
			});
		});

	});

}(window.jQuery, window, document));
 
 


