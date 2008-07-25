<?

include('bootstrapper.php');
/**
 * Development error output.
 */
function outputError($e) {
	echo "Whoops, there was an error: ".$e->getMessage();
	echo "<pre>";
	print_r($e);
	echo "</pre>";
	die();
}
routeRequest();

?>
