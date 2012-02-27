var Page = new Class
({
	initialize: function()
	{
		this.content = $('content');
		this.header = $('pageTop');
		this.footer = $('pageBottom');
	},
	
	getHeader: function()
	{
		var header = new Element('div',{'class' : 'header'});
		
		var logo = new Element('div',{'class' : 'logo'}).inject(header);
		var logoLink = new Element('a',{'href' : '#/'}).inject(logo);
		var logoImage = new Element('img',{'src' : 'img/logo_top.png'}).inject(logoLink);
		
		var actions = new Element('div',{'class' : 'actions'}).inject(header);
		
		var toolsLink = new Element('a',{'href' : '#/tools'}).inject(actions);
			new Element('img',{'src' : 'img/wrench_plus_16.png'}).inject(toolsLink);
			new Element('span',{'text' : 'Coding Tools'}).inject(toolsLink);
		
		var regenerateLink = new Element('a',{'href' : '#/regenerate'}).inject(actions); //new Element('a').inject(actions);
			new Element('img',{'src' : 'img/refresh_icon_16.png'}).inject(regenerateLink);
			new Element('span',{'text' : 'Regenerate'}).inject(regenerateLink);
			
//		var settingsLink = new Element('a',{'href' : '#/settings'}).inject(actions);
//			new Element('img',{'src' : 'img/cog_icon_16.png'}).inject(settingsLink);
//			new Element('span',{'text' : 'Settings'}).inject(settingsLink);
		
		new Element('div',{'class' : 'cb'}).inject(header);
		
		return header;
		
	},
	
	getFooter: function()
	{
		var footer = new Element('div',{'class' : 'footer'});
		
		return footer;
		
	},
	
	parseBrowserURL: function()
	{
		this.cleanupPage();
		
		url = window.location.hash.replace(/^#\//,'');
		
		this.parseURL('/' + url,false);
		
	},
	
	getAjaxUrl: function(url)
	{
		return window.location.href.replace(window.location.hash,"").replace("index.php","") + "ajax/" + url;
	},
	
	parseURL: function(relativeURL,rewrite)
	{
		this.database, this.table, this.action = null;
		
		if(relativeURL == "/tools")
		{
			this.codingTools();
		}
		else if(relativeURL == "/tools/server")
		{
			this.severCodingTools();
		}
		else if(relativeURL == "/tools/client")
		{
			this.clientCodingTools();
		}
		else if(relativeURL == "/regenerate")
		{
			var content = new Element('div',{'class' : 'content generating'}).inject(this.content);
			
			new Element('div',{'class' : 'title', 'text' : 'Regenerating DAO'}).inject(content);
			new Element('br').inject(content);
			
			var pacman = new Element('div',{ 'class' : 'pacman' }).inject(content);
			new Element('img',{'src' : 'img/pacman.gif'}).inject(pacman);
			new Element('span',{'html' : '.&nbsp;&nbsp;.&nbsp;&nbsp;.'}).inject(pacman);
			
			new Request.WithErrorHandling({'url': this.getAjaxUrl('generate.php'), onSuccess: function(resp) { window.location = '#/'; } }).send();
		}
		else if(relativeURL.match(/\/database\/(\w+)\/table\/(\w+)\/action\/(\w+)/))
		{
			this.database = relativeURL.match(/\/database\/(\w+)\/table\/(\w+)\/action\/(\w+)/)[1];
			this.table = relativeURL.match(/\/database\/(\w+)\/table\/(\w+)\/action\/(\w+)/)[2];
			this.action = relativeURL.match(/\/database\/(\w+)\/table\/(\w+)\/action\/(\w+)/)[3];
			
			if(this.action == "structure")
			{
				//new Request.WithErrorHandling({'url': this.getAjaxUrl('list_tables.php'), onSuccess: this.listTables.bind(this) }).send(Object.toQueryString({'database' : this.database}));
				this.showTableStructure();
			}
			else if(this.action == "new")
			{
				new Request.WithErrorHandling({'url': this.getAjaxUrl('object_creation.php'), onSuccess: this.showNewObjectBuilder.bind(this) }).send(Object.toQueryString({'database' : this.database, 'table' : this.table}));
			}
			else if(this.action == "query")
			{
				this.showQueryBuilderBuilder();
			}
			else if(this.action == "extensions")
			{
				new Request.WithErrorHandling({'url': this.getAjaxUrl('stub_builder.php'), onSuccess: this.showExtendedObjectStubBuilder.bind(this) }).send(Object.toQueryString({'database' : this.database, 'table' : this.table}));
			}
			else
			{
				this.showTableObtions();
			}
		}
		else if(relativeURL.match(/\/database\/(\w+)\/table\/(\w+)/))
		{
			this.database = relativeURL.match(/\/database\/(\w+)\/table\/(\w+)/)[1];
			this.table = relativeURL.match(/\/database\/(\w+)\/table\/(\w+)/)[2];
			this.showTableObtions();
		}
		else if(relativeURL.match(/database/))
		{
			this.database = relativeURL.match(/\/database\/(\w+)/)[1];
			new Request.WithErrorHandling({'url': this.getAjaxUrl('list_tables.php'), onSuccess: this.listTables.bind(this) }).send(Object.toQueryString({'database' : this.database}));
		}
		else
		{
			this.selectADatabase();
		}
	},
	
	cleanupPage: function()
	{
		this.content.empty();
	},
	
	codingTools: function()
	{
		var content = new Element('div',{'styles' : {'margin':'20px'}}).inject(this.content);
		
		var div = new Element('div',{'class':'content optionsBlock'}).inject(content);
		new Element('img',{'src' : 'img/round_plus_48.png'}).inject(div);
		new Element('div',{'text' : 'PHP Server Tools'}).inject(div);
		div.addEvent('click',function(){ window.location = '#/tools/server'; }.bind(this));
		
//		var div = new Element('div',{'class':'content optionsBlock'}).inject(content);
//		new Element('img',{'src' : 'img/round_plus_48.png'}).inject(div);
//		new Element('div',{'text' : 'Clientside Tools'}).inject(div);
//		div.addEvent('click',function(){ window.location = '#/tools/client'; }.bind(this));
		
	},

	severCodingTools: function()
	{
		new Element('h1',{'text' : 'Server Coding Tools'}).inject(this.content);
		
		var content = new Element('div',{'class' : 'content'}).inject(this.content);
		
		new Element('h2',{'text' : 'Set and Get'}).inject(content);
		new Element('br').inject(content);
		
		var h3 = new Element('h3',{'text' : 'Class Variable Name: '}).inject(content);
		var input = new Element('input',{'type' : 'text'}).inject(h3);
		var pre = new Element('pre',{'type' : 'text','html':'<br/><br/><br/>'}).inject(content);
		
		input.addEvent('keyup',function(input,pre){
			
			variableName = input.value;
			
			variableNameArray = variableName.split("");
			
			if(variableNameArray[0] == '$')
			{
				variableNameArray.shift();
			}
			
			variableName  = variableNameArray.join("");
			variableNameArray[0] = variableNameArray[0].toUpperCase();
			capVariableName = variableNameArray.join("");
			
			methods =  '<pre>\n\nfunction set' + capVariableName + "($val) ";
			methods += "{";
			methods += " $this->" + variableName + " = $val;";
			methods += " }\n";
			methods +=  'function get' + capVariableName + "() ";
			methods += "{";
			methods += " return $this->" + variableName + ";";
			methods += " }\n\n\n</pre>";

			pre.innerHTML = methods;
			
		}.bind(this,input,pre));
		
	},
	
	clientCodingTools: function()
	{
		var content = new Element('div',{'class' : 'content'}).inject(this.content);
		
		var h2 = new Element('h2',{'text' : 'Coding Tools'}).inject(content);
		
		
		
	},
	
	selectADatabase: function()
	{
		var h1 = new Element('h1',{'text' : 'Select a Database'}).inject(this.content);
		
		var content = new Element('div',{'class' : 'content noPadding'}).inject(this.content);
		
		var databaseList = new Element('ul',{'class' : 'listOfThings'}).inject(content);
		
		config.db.each(function(db)
		{
			var item = new Element('li').inject(databaseList);
			new Element('img',{'src' : 'img/db_32.png'}).inject(item);
			new Element('span',{'text' : db.name}).inject(item);

			item.addEvent('click',function(){ window.location = '#/database/' + db.name; });
			
		});
	},
	
	listTables: function(resp)
	{
		var h1 = new Element('h1').inject(this.content);
		var databaseLink = new Element('a',{'text' : this.database}).inject(h1);
		databaseLink.addEvent('click',function(){ window.location = '#/database/' + this.database; }.bind(this));
		
		new Element('span',{'text' : " > "}).inject(h1);
		new Element('span',{'text' : "Select a Table"}).inject(h1);
		
		var content = new Element('div',{'class' : 'content noPadding'}).inject(this.content);
		
		var search = new Element('div', {'class' : 'search'}).inject(content);
		new Element('span',{'text' : 'Search: '}).inject(search);
		this.searchBox = new Element('input',{'type' : 'text'}).inject(search);
		
		var tableList = new Element('ul',{'class' : 'listOfThings listOfTables'}).inject(content);
		
		this.listItems = new Array();
	
		resp.tables.each(function(table)
		{
			var item = new Element('li').inject(tableList);
			new Element('img',{'src' : 'img/align_just_16.png'}).inject(item);
			new Element('span',{'text' : table}).inject(item);
	
			item.addEvent('click',function(){ window.location = '#/database/' + this.database + "/table/" + table; }.bind(this));
			
			this.listItems.push(item);
			
		},this);
		
		this.searchBox.focus();
		this.searchBox.addEvent('keyup', this.filterTableList.bind(this));
		
	},
	
	filterTableList: function()
	{
		if(this.listItems.length > 0)
		{
			var count = 0;
			var lastMatch;
			
			this.listItems.each(function(item)
			{
				if(this.searchBox.value == "" || item.getElements('span').getLast().get('text').test(this.searchBox.value, "i"))
				{
					item.setStyle('display', 'block');
					lastMatch = item.getElements('span').getLast().get('text');
					count++;
				}
				else
				{
					item.setStyle('display', 'none');
				}
			},this);
			
			if(count == 1)
			{
				window.location = '#/database/' + this.database + "/table/" + lastMatch;
			}
		}
	},
	
	showTableObtions: function()
	{
		var h1 = new Element('h1').inject(this.content);
		var databaseLink = new Element('a',{'text' : this.database}).inject(h1);
		databaseLink.addEvent('click',function(){ window.location = '#/database/' + this.database; }.bind(this));
		
		new Element('span',{'text' : " > "}).inject(h1);
		var tableLink = new Element('a',{'text' : this.table}).inject(h1);
		tableLink.addEvent('click',function(){ window.location = '#/database/' + this.database + '/table/' + this.table; }.bind(this));
		
		var content = new Element('div',{'styles' : {'margin':'20px'}}).inject(this.content);
		
		var div = new Element('div',{'class':'content optionsBlock'}).inject(content);
		new Element('img',{'src' : 'img/round_plus_48.png'}).inject(div);
		new Element('div',{'text' : 'Object Creation'}).inject(div);
		div.addEvent('click',function(){ window.location = '#/database/' + this.database + '/table/' + this.table + '/action/new'; }.bind(this));
		
//		var div = new Element('div',{'class':'content optionsBlock'}).inject(content);
//		new Element('img',{'src' : 'img/round_plus_48.png'}).inject(div);
//		new Element('div',{'text' : 'Query Builder'}).inject(div);
//		div.addEvent('click',function(){ window.location = '#/database/' + this.database + '/table/' + this.table + '/action/query'; }.bind(this));
//		
		var div = new Element('div',{'class':'content optionsBlock'}).inject(content);
		new Element('img',{'src' : 'img/round_plus_48.png'}).inject(div);
		new Element('div',{'text' : 'Extended Object Stubs'}).inject(div);
		div.addEvent('click',function(){ window.location = '#/database/' + this.database + '/table/' + this.table + '/action/extensions'; }.bind(this));
//		
//		var div = new Element('div',{'class':'content optionsBlock'}).inject(content);
//		new Element('img',{'src' : 'img/cogs_48.png'}).inject(div);
//		new Element('div',{'text' : 'Table Structure'}).inject(div);
//		div.addEvent('click',function(){ window.location = '#/database/' + this.database + '/table/' + this.table + '/action/structure'; }.bind(this));
		
	},
	
	showNewObjectBuilder: function(resp)
	{
		var h1 = new Element('h1').inject(this.content);
		var databaseLink = new Element('a',{'text' : this.database}).inject(h1);
		databaseLink.addEvent('click',function(){ window.location = '#/database/' + this.database; }.bind(this));
		
		new Element('span',{'text' : " > "}).inject(h1);
		var tableLink = new Element('a',{'text' : this.table}).inject(h1);
		tableLink.addEvent('click',function(){ window.location = '#/database/' + this.database + '/table/' + this.table; }.bind(this));
		
		var content = new Element('div',{'class' : 'content'}).inject(this.content);
		
		new Element('h2',{'text' : 'New Object Creation'}).inject(content);
		new Element('br').inject(content);
		
		var h3 = new Element('h3',{'text' : 'Object Variable Name: '}).inject(content);
		var input = new Element('input',{'type' : 'text','value' : 'obj'}).inject(h3);
		var pre = new Element('pre',{'type' : 'text','html':'<br/><br/><br/>'}).inject(content);
		
		input.addEvent('keyup',function(resp,input,pre){
			
			variableName = input.value;
			
			variableNameArray = variableName.split("");
			
			if(variableNameArray[0] == '$')
			{
				variableNameArray.shift();
			}
			
			variableName  = variableNameArray.join("");
			variableNameArray[0] = variableNameArray[0].toUpperCase();
			capVariableName = variableNameArray.join("");
			
			methods =  "<pre>\n\n";
			
			methods += resp.include + "\n\n";
			
			methods += '$' + variableName + " = new " + resp.className + "();\n";
			
			resp.columns.each(function(col)
			{
				methods += '$' + variableName + "->" + col.setter + "();\n";
				
				
			});
			methods += '$' + variableName + "->save();\n";
			
			methods += "\n\n\n</pre>";

			pre.innerHTML = methods;
			
		}.bind(this,resp,input,pre));
		
		input.fireEvent('keyup');
		
	},
	
	showExtendedObjectStubBuilder: function(resp)
	{
		var h1 = new Element('h1').inject(this.content);
		var databaseLink = new Element('a',{'text' : this.database}).inject(h1);
		databaseLink.addEvent('click',function(){ window.location = '#/database/' + this.database; }.bind(this));
		
		new Element('span',{'text' : " > "}).inject(h1);
		var tableLink = new Element('a',{'text' : this.table}).inject(h1);
		tableLink.addEvent('click',function(){ window.location = '#/database/' + this.database + '/table/' + this.table; }.bind(this));
		
		var content = new Element('div',{'class' : 'content'}).inject(this.content);
		
		new Element('h2',{'text' : 'Extended DAO Object Stub Builder'}).inject(content);
		new Element('br').inject(content);
		
		
		new Element('h3',{'text' : 'Extended DAO Factory'}).inject(content);
		new Element('br').inject(content);
		
		var daoFactoryPre = new Element('pre',{'text':resp.factory.html + "\n\n\n"}).inject(content);
		
		new Element('h3',{'text' : 'Extended DAO Object'}).inject(content);
		new Element('br').inject(content);
		
		var daoObjectPre = new Element('pre',{'text':resp.object.html}).inject(content);
		
		/*
	
		
		var h3 = new Element('h3',{'text' : 'Object Variable Name: '}).inject(content);
		var input = new Element('input',{'type' : 'text','value' : 'obj'}).inject(h3);
		var pre = new Element('pre',{'type' : 'text','html':'<br/><br/><br/>'}).inject(content);
		
		input.addEvent('keyup',function(resp,input,pre){
			
			variableName = input.value;
			
			variableNameArray = variableName.split("");
			
			if(variableNameArray[0] == '$')
			{
				variableNameArray.shift();
			}
			
			variableName  = variableNameArray.join("");
			variableNameArray[0] = variableNameArray[0].toUpperCase();
			capVariableName = variableNameArray.join("");
			
			methods =  "<pre>\n\n";
			
			methods += resp.include + "\n\n";
			
			methods += '$' + variableName + " = new " + resp.className + "();\n";
			
			resp.columns.each(function(col)
			{
				methods += '$' + variableName + "->" + col.setter + "();\n";
				
				
			});
			methods += '$' + variableName + "->save();\n";
			
			methods += "\n\n\n</pre>";

			pre.innerHTML = methods;
			
		}.bind(this,resp,input,pre));
		
		input.fireEvent('keyup');
		*/
		
	}
	
});
	
	

var page = null;

function loadPage()
{
	page = new Page();
	page.getHeader().inject(page.header);
	page.getFooter().inject(page.footer);
	
	page.parseBrowserURL();
	
	window.addEvent('hashchange',function(newhash) {
		page.parseBrowserURL();
	});
	
	
	
}

window.addEvent('domready', loadPage);