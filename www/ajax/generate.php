<?php

include('../../config.inc.php');
include('../../www/ajax/ajax.inc.php');

if(!$generator->generate())
{
	returnError($generator->getErrorMessage());
}
else
{
	$resp['success'] = 1;
	returnResponse($resp);
}

?>