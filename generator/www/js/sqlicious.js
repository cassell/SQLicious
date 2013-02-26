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

	});

}(window.jQuery, window, document));
 
 


