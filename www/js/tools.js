function constructGetSet()
{
	variableName = document.setandget.classVariableName.value;
	
	variableNameArray = variableName.split("");
	
	if(variableNameArray[0] == '$')
	{
		variableNameArray.shift();
	}
	
	variableName  = variableNameArray.join("");
	variableNameArray[0] = variableNameArray[0].toUpperCase();
	capVariableName = variableNameArray.join("");
	
	methods =  '<pre>function set' + capVariableName + "($val) ";
	methods += "{";
	methods += " $this->" + variableName + " = $val;";
	methods += " }\n";
	methods +=  'function get' + capVariableName + "() ";
	methods += "{";
	methods += " return $this->" + variableName + ";";
	methods += " }\n</pre>";

	return methods;
	
}