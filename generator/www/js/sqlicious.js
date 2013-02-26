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
		App.DashboardController = Ember.ObjectController.extend();
		App.DashboardView = Ember.View.extend({
			templateName:  'dashboard'
		});
		

	});

}(window.jQuery, window, document));
 
 


