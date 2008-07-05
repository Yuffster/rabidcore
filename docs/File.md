File
======================

A Singleton with a collection of handy methods for handling files.

This is an internal file.  It should not be necessary to use this class directly for normal operations.

Dependencies:
---------------------------------

- *None.*

File Method: getMimeType
---------------------------------

Gleans the mime type based on extension.

### Syntax:

	File::getMimeType(filename);

### Arguments:

1. filename - (*string*) The filename to base the mime type on.

### Returns:

- (string) A string containing the mime type.

### Example:

	File::getMimeType('script.js');  //Returns 'application/javascript'.
	File::getMimeType('index.html'); //Returns 'text/html'.

### Notes:

- To make this more accurate, rewrite this method to utilize more advanced (non-standard) PHP modules, or modify the $types array in the constructor of this class.


File Method: render
---------------------------------

Outputs a file with proper headers and then dies.  If this method is called, it should be the *only* method called which outputs any data.

### Syntax:

	File::render(location);

### Arguments:

1. location - (*string*) The location of the file to render relative to env.php.

### Example:

File::render('static/image.png');


File Method: find
---------------------------------

Checks to see if a file exists, relative to the location of env.php.

### Syntax:

	File::find(filename);

### Arguments:

1. location - (*string*) The location of the file to render relative to env.php.

### Example:

File::find('env.php'); //Returns true.


File Method: getContents
---------------------------------

Returns the contents of a file or throws a FileNotFoundException if the file doesn't exist.

### Syntax:

	File::getContents(filename);

### Arguments:

1. location - (*string*) The location of the file to render relative to env.php.

### Returns:

 - (string) The contents of the file.

### Throws:

 - FileNotFoundException if the file is not found.

### Example:

	File::getContents('index.html');


File Method: collectDir
---------------------------------

A recursive method which collects all files in a specified directory and combines them into a text string. 

### Syntax:

	File::collectDir(location);

### Arguments:

1. location - (*string*) The location of the directory to collect relative to env.php.

### Returns:

 - (string) The contents of all files within the directory, excluding files and directories which start with a period.

### Example:

	File::collectDir('static/js');


File Method: getRecognizedTypes
---------------------------------

Returns the $types array.  Useful for printing out a table of the application's supported extensions.

### Syntax:

	File::getRecognizedTypes()

### Returns:

 - (array) The $types array (as defined in the File constructor).

### Example:

	<table>                                                                                            
		<thead>
			<th>Extension</th>
			<th>Mimetype</th>
		</thead>   
		<? foreach (File::getRecognizedTypes() as $type=>$exts): ?>
			<? foreach ($exts as $key=>$value): ?>
				<tr>
					<? if (!is_numeric($key)) $ext = $key; else $ext = $value; ?>                                                                                  
					<td><?=$ext?></td>
					<td><?=File::getMimeType("file.$ext");?></td>
				</tr>
			<?endforeach;?>
		<?endforeach;?>
	</table>
