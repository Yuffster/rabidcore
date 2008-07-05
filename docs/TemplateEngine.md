TemplateEngine
======================

The TemplateEngine works with the File class to render HTML files.  This class is optimized to use HTML files that are easy to read for designers and front-end coders with little PHP experience.

**Note**: While this class is functional, it is not yet complete.

Coming Soon:
---------------------------------

- No requisite views folder.
- Infinite number of content replacements (for side bars and the like).
- "Inheritable" main.php.
- A less ambiguous name for main.php.

Dependencies:
---------------------------------

- File

Static Property: $baseDir
---------------------------------

Modify the $baseDir to change the location (relative to env.php) where your template files are stored.

This defaults to 'views'.

Template Method: renderPage
---------------------------------

Takes the location of a template file as its content, wraps it in views/main.php by replacing the an HTML comment with the text "Main Content Goes Here" with the content of the template file passed as the first parameter.

### Syntax:

	TemplateEngine::renderPage(contentFile[, variables]);

### Arguments:

1. contentFile - (string) The location of the template file to render relative to the base directory.  It will be assumed that the file ends in '.php'.  **Do not include the extension of the file.**
2. variables   - (array) An associative array of variables you would like to be present for use in the template files. 

### Outputs:

- (string) The rendered HTML.

### Returns:

- null

### Example:

	TemplateEngine::renderPage('filenotfound', Array('file'=>'player/inventory'));

Template Method: renderPartial
---------------------------------

Takes the location of a template file as its content and returns it without wrapping it with anything else.

### Syntax:

	TemplateEngine::renderPartial(contentFile[, variables]);

### Arguments:

1. contentFile - (string) The location of the template file to render relative to the base directory.  It will be assumed that the file ends in '.php'.  **Do not include the extension of the file.**
2. variables   - (array) An associative array of variables you would like to be present for use in the template files. 

### Returns:

- (string) The rendered HTML.

### Example:

	$result = TemplateEngine::renderPartial('user/form', Array('action'=>'edit'));
