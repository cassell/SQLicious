
// extend json request to encapsulate single error processing
Request.WithErrorHandling = new Class(
{
	Extends: Request.JSON,

	initialize: function(options)
	{
		// if onErrors is not specified us this one
		if(options.onErrors == undefined)
		{
			options.onErrors = this.onErrors.bind(this);
		}
		
		// if onFailure is not specified us this one
		if(options.onFailure == undefined)
		{
			options.onFailure = this.onFailed.bind(this);
		}
		
		this.parent(options);
	},

	onSuccess: function(resp)
	{
		if(resp == undefined || resp == null)
		{
			alert(defaultAjaxErrorMessage);
		}
		else if(resp.errors != undefined)
		{
			this.fireEvent('errors', arguments);
		}
		else
		{
			// all is well
			this.fireEvent('success', arguments);
		}
		
	},
	
	onErrors: function(resp)
	{
		alert(Object.values(resp.errors));
	},
	
	onFailed: function()
	{
		alert('An error occurred.  Please check your network connection.');
	}

});

String.implement(
{
	nl2br: function()
	{
		return this.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br/>$2');
	}
	
});


Element.implement(
{
	nl2br: function()
	{
		this.innerHTML = this.innerHTML.replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br/>$2');
		
		return this;
	}
	
});



