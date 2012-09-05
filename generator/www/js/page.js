
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
			new Element('span',{'text' : 'Regenerate All'}).inject(regenerateLink);
			
		var githubLink = new Element('a',{'target':'_BLANK', 'href' : 'https://github.com/cassell/SQLicious'}).inject(actions);
			new Element('img',{'src' : 'img/github_16.png'}).inject(githubLink);
			var githubLinkText = new Element('span',{'text' : 'View on GitHub'}).inject(githubLink);
			
		
		new Request.JSONP({'url': 'https://api.github.com/repos/cassell/SQLicious/commits', onSuccess: function(resp){
			
			if(resp != null && resp.data != null && resp.data[0] != null)
			{
				githubLink.set('title','SQLicious was last updated on ' + Date.parse(resp.data[0].commit.author.date));
			}
				
		}}).send();
		
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
		this.database = null;
		this.table = null;
		this.action = null;
		
		if(relativeURL == "/tools")
		{
			this.codingTools();
		}
		else if(relativeURL == "/tools/server")
		{
			this.severCodingTools();
		}
		else if(relativeURL == "/regenerate")
		{
			var content = new Element('div',{'class' : 'content generating'}).inject(this.content);
			
			new Element('div',{'class' : 'title', 'text' : 'Regenerating DAO'}).inject(content);
			new Element('br').inject(content);
			
			var pacman = new Element('div',{'class' : 'pacman'}).inject(content);
			new Element('img',{'src' : 'img/pacman.gif'}).inject(pacman);
			new Element('span',{'html' : '.&nbsp;&nbsp;.&nbsp;&nbsp;.'}).inject(pacman);
			
			new Request.WithErrorHandling({'url': this.getAjaxUrl('generate.php'), onSuccess: function(resp) {window.location = '#/';}}).send();
		}
		else if(relativeURL.match(/\/regenerate\/(\w+)/))
		{
			var database = relativeURL.match(/\/regenerate\/(\w+)/)[1];
			
			var content = new Element('div',{'class' : 'content generating'}).inject(this.content);
			
			new Element('div',{'class' : 'title', 'text' : 'Regenerating ' + database + ' DAO'}).inject(content);
			new Element('br').inject(content);
			
			var pacman = new Element('div',{'class' : 'pacman'}).inject(content);
			new Element('img',{'src' : 'img/pacman.gif'}).inject(pacman);
			new Element('span',{'html' : '.&nbsp;&nbsp;.&nbsp;&nbsp;.'}).inject(pacman);
			
			new Request.WithErrorHandling({'url': this.getAjaxUrl('generate.php?database='+database), onSuccess: function(resp) {window.location = '#/';}}).send();
			
		}
		else if(relativeURL.match(/\/database\/(\w+)\/table\/(\w+)\/action\/(\w+)/))
		{
			this.database = relativeURL.match(/\/database\/(\w+)\/table\/(\w+)\/action\/(\w+)/)[1];
			this.table = relativeURL.match(/\/database\/(\w+)\/table\/(\w+)\/action\/(\w+)/)[2];
			this.action = relativeURL.match(/\/database\/(\w+)\/table\/(\w+)\/action\/(\w+)/)[3];
			
			if(this.action == "structure")
			{
				new Request.WithErrorHandling({'url': this.getAjaxUrl('table_details.php'), onSuccess: this.showTableStructure.bind(this)}).send(Object.toQueryString({'database' : this.database, 'table' : this.table}));
			}
			else if(this.action == "new")
			{
				new Request.WithErrorHandling({'url': this.getAjaxUrl('object_creation.php'), onSuccess: this.showNewObjectBuilder.bind(this)}).send(Object.toQueryString({'database' : this.database, 'table' : this.table}));
			}
			else if(this.action == "query")
			{
				this.showQueryBuilderBuilder();
			}
			else if(this.action == "extensions")
			{
				new Request.WithErrorHandling({'url': this.getAjaxUrl('stub_builder.php'), onSuccess: this.showExtendedObjectStubBuilder.bind(this)}).send(Object.toQueryString({'database' : this.database, 'table' : this.table}));
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
			new Request.WithErrorHandling({'url': this.getAjaxUrl('list_tables.php'), onSuccess: this.listTables.bind(this)}).send(Object.toQueryString({'database' : this.database}));
		}
		else
		{
			this.selectADatabase();
		}
		
		this.buildTitle();
		
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
		div.addEvent('click',function(){window.location = '#/tools/server';}.bind(this));
		
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
	
	selectADatabase: function()
	{
		var content = new Element('div',{'class' : 'content noPadding'}).inject(this.content);
		
		var databaseList = new Element('ul',{'class' : 'listOfThings'}).inject(content);
		
		config.db.each(function(db)
		{
			var item = new Element('li').inject(databaseList);
			new Element('img',{'src' : 'img/db_32.png'}).inject(item);
			new Element('span',{'text' : db.name}).inject(item);

			item.addEvent('click',function(){window.location = '#/database/' + db.name;});
			
		});
	},
	
	listTables: function(resp)
	{
		var content = new Element('div',{'class' : 'content noPadding'}).inject(this.content);
		
		var search = new Element('div', {'class' : 'search'}).inject(content);
		new Element('span',{'text' : 'Search: '}).inject(search);
		this.searchBox = new Element('input',{'type' : 'text'}).inject(search);
		
		var a = new Element('a',{'class': 'regenerateDatabase', 'text':'Regenerate Tables'}).inject(search);
		a.addEvent('click',function(){window.location = '#/regenerate/' + this.database}.bind(this));
		
		var tableList = new Element('ul',{'class' : 'listOfThings listOfTables'}).inject(content);
		
		this.listItems = new Array();
	
		resp.tables.each(function(table)
		{
			var item = new Element('li').inject(tableList);
			new Element('img',{'src' : 'img/align_just_16.png'}).inject(item);
			new Element('span',{'text' : table}).inject(item);
	
			item.addEvent('click',function(){window.location = '#/database/' + this.database + "/table/" + table;}.bind(this));
			
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
		var content = new Element('div',{'styles' : {'margin':'20px'}}).inject(this.content);
		
		var div = new Element('div',{'class':'content optionsBlock'}).inject(content);
		new Element('img',{'src' : 'img/round_plus_48.png'}).inject(div);
		new Element('div',{'text' : 'Object Creation'}).inject(div);
		div.addEvent('click',function(){window.location = '#/database/' + this.database + '/table/' + this.table + '/action/new';}.bind(this));
		
//		var div = new Element('div',{'class':'content optionsBlock'}).inject(content);
//		new Element('img',{'src' : 'img/round_plus_48.png'}).inject(div);
//		new Element('div',{'text' : 'Query Builder'}).inject(div);
//		div.addEvent('click',function(){ window.location = '#/database/' + this.database + '/table/' + this.table + '/action/query'; }.bind(this));
		
		var div = new Element('div',{'class':'content optionsBlock'}).inject(content);
		new Element('img',{'src' : 'img/round_plus_48.png'}).inject(div);
		new Element('div',{'text' : 'Extended Object Stubs'}).inject(div);
		div.addEvent('click',function(){window.location = '#/database/' + this.database + '/table/' + this.table + '/action/extensions';}.bind(this));

		var div = new Element('div',{'class':'content optionsBlock'}).inject(content);
		new Element('img',{'src' : 'img/cogs_48.png'}).inject(div);
		new Element('div',{'text' : 'Table Structure'}).inject(div);
		div.addEvent('click',function(){ window.location = '#/database/' + this.database + '/table/' + this.table + '/action/structure'; }.bind(this));
		
	},
	
	showNewObjectBuilder: function(resp)
	{
		this.addBreadCrumb('Object Creation');
		
		var content = new Element('div',{'class' : 'content'}).inject(this.content);
		
		new Element('br').inject(content);
		
		var h3 = new Element('h3',{'text' : 'Object Variable Name: '}).inject(content);
		var input = new Element('input',{'type' : 'text','value' : resp.variableName}).inject(h3);
		
		new Element('br').inject(content);
		
		var label = new Element('label',{'text' : ' Use Extended Object'}).inject(content);
		var extendObjects = new Element('input',{'type' : 'checkbox'}).inject(label,'top');
		extendObjects.addEvent('change',function()
		{
			input.fireEvent('change');
		});
		
		var pre = new Element('pre',{'type' : 'text','html':'<br/><br/><br/>'}).inject(content);
		
		input.addEvent('change',function(resp,input,pre){
			
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
			
			methods += '$' + variableName + " = new " + resp.className + (extendObjects.checked ? '' : 'DaoObject') + "();\n";
			
			resp.columns.each(function(col)
			{
				methods += '$' + variableName + "->" + col.setter + "();\n";
				
				
			});
			methods += '$' + variableName + "->save();\n";
			
			methods += "\n\n\n</pre>";

			pre.innerHTML = methods;
			
		}.bind(this,resp,input,pre));
		
		input.fireEvent('change');
		
	},
	
	showExtendedObjectStubBuilder: function(resp)
	{
		this.addBreadCrumb('Extended DAO Stub Builder');
		
		var content = new Element('div',{'class' : 'content'}).inject(this.content);
		
		new Element('pre',{'text':resp.stub.html + "\n\n"}).inject(content);
		
	},
	
	showTableStructure: function(resp)
	{
		this.addBreadCrumb('Table Structure');
		
		var content = new Element('div',{'class' : 'content'}).inject(this.content);
		
		var table = new Element('table', {'class':'table'}).inject(content);
		
			var tableHeader = new Element('tr').inject(new Element('thead').inject(table));
				new Element('th',{'text':'Column'}).inject(tableHeader);
				new Element('th',{'text':'Type'}).inject(tableHeader);
				new Element('th',{'text':'Null'}).inject(tableHeader);
				new Element('th',{'text':'Default Value'}).inject(tableHeader);
				new Element('th',{'text':'Getter'}).inject(tableHeader);
				new Element('th',{'text':'Setter'}).inject(tableHeader);
				
			if(resp.columns != null)
			{
				var tbody = new Element('tbody').inject(table);
				resp.columns.each(function(col)
				{
					var row = new Element('tr').inject(tbody);
						new Element('td',{'text':col['name']}).inject(row);
						new Element('td',{'text':col['type']}).inject(row);
						new Element('td',{'text':col['null']}).inject(row);
						new Element('td',{'text':col['default']}).inject(row);
						new Element('pre',{'text':col['getter']}).inject(new Element('td').inject(row));
						new Element('pre',{'text':col['setter']}).inject(new Element('td').inject(row));
				})
			}
	},
	
	buildTitle: function()
	{
		this.h1 = new Element('h1').inject(this.content,'top');
		
		if(this.database != null)
		{
			this.addBreadCrumb('Databases','#/');
			if(this.table != null)
			{
				this.addBreadCrumb(this.database,'#/database/' + this.database);
				
				if(this.action != null)
				{
					this.addBreadCrumb(this.table,'#/database/' + this.database + '/table/' + this.table);
				}
				else
				{
					this.addBreadCrumb(this.table);
				}
			}
			else
			{
				this.addBreadCrumb(this.database);
			}
		}
		else
		{
			this.addBreadCrumb('Select a Database');
		}
		
	},
	
	addBreadCrumb: function(text,url)
	{
		if(this.h1.getChildren().length != 0)
		{
			new Element('span',{'text' : " > "}).inject(this.h1);
		}
		
		if(url != null)
		{
			var link = new Element('a',{'text' : text}).inject(this.h1);
			link.addEvent('click',function(){window.location = url;}.bind(this));
		}
		else
		{
			new Element('span',{'text' : text}).inject(this.h1);
		}
		
		
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