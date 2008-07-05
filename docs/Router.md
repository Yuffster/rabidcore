Router
======================

This nonstandard class (you should never call it directly) is responsible for translating the site path to a standard action.

Dependencies
---------------------------------

- Commands

Router Method: route
---------------------------------

The route will automatically receive the path of the request URI from env.php.  It will then get the return value of the command and output it according to the format requested with the X-REQUEST header.

### Router Path:

- /*model*/*action*/[extra/parameters/as/necessary]

### Parameters:

1. model   - The model name of the request.  The ModelCommands class will be called, where Model is the first parameter passed as the request path.
2. action  - The method to execute on the Commands object.
When a path is requested, the base URI of the site will be extracted and the remaining path will be passed to the Router.
3. *Additional parameters may be passed as necessary by the command.*

Router Method: output_full
---------------------------------

This default output method will output the full rendered HTML page, including the content template and the layout template.

The TemplateEngine is passed the following values:

- result (mixed) The return result of the Commands class operation.
- errors (array) An array of key=>value errors, if they exist.

Router Method: output_json
---------------------------------

This default output a JSON string in the following format:

    {'success':1, 'data':[{'key1':'value', 'key2':'value'}, {'key1':'value', 'key2':'value'}]};
	{'success':0, 'data':{'gender':'must be male or female', 'age':'must be between 13 and 120.'}};
	{'success':0, 'data':'Database connection failed.'};

Success can be one or zero, depending on whether the operation was a success.  Data can be any JSON object.


Router Method: output_partial
---------------------------------

This default output method will output the full the content template without wrapping it in the full site layout.


