<?

/**
 * All requests should be redirected to this file, or a file with the same call
 * to routeRequest() in bootstrapper.php.
 */

include('bootstrapper.php');

try { 
	echo routeRequest();
//Fallback error handler for when the TemplateEngine isn't around to help.
} catch (Exception $e) {
	echo "Sorry, there was an error: ".$e->getMessage().".";
}

?>
