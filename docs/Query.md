Query
======================

The Query class acts as an iterator of DataModel objects based on the results of a Database query.  For more information on the Iterator interface, see the PHP manual.

Dependencies:
---------------------------------

- Database
- DataModel

Query Method: constructor
---------------------------------

Creates a new Query object.

The Query class will not perform its search until a method which requires its results is called (or until the object is used in a foreach loop).  This allows Query objects to be used in places which would cause unacceptable recursion, such as in self-referential models.  It also allows for modification of the Query after constructing the object.

### Syntax:

	new <model>Query(criteria, limit);

### Arguments:

1. model    - (string) The name of the model to perform the query on.
2. criteria - (mixed, optional) Criteria parameter to be passed to the Database class.  If a number is passed, the criteria will become Array('id'=>*n*), where *n* is the number passed.  See the Database documentation for more information about how the criteria array should be formatted.  
3. limit    - (number, optional) Maximum number of results to return.

### Returns:

- (Query) A new Query object.

### Example:

	$children = new Query('comment', Array('parent_id'=>$my->id));
	foreach ($children as $child) {
		echo $child->subject;
	}


Query Method: findOne
---------------------------------

A static method which works like the Query constructor except that it performs the search immediately and returns a single DataModel object.

### Syntax:

	Query::findOne(model, criteria);

### Arguments:

1. model    - (string) The name of the model to perform the query on.
2. criteria - (mixed)  Criteria parameter to be passed to the Database class.  If a number is passed, the criteria will become Array('id'=>*n*), where *n* is the number passed.  See the Database documentation for more information about how the criteria array should be formatted.

### Example:

    $user = Query::findOne('user', Array('login'=>$login, 'password'=>$password));


Query Method: orderBy
---------------------------------

Adds an ORDER BY clause to the Query.  Can only be used before the query has taken place.

### Syntax:

	$query->orderBy(field, order);

### Arguments:

1. field - (string) The field name to order the results by.
2. order - (string) "ASC" or "DESC", the direction to order results by.

### Example:

   $query->orderBy('name', 'DESC');


Query Method: getTotal
---------------------------------

Returns the total of results.  Calling this method will cause the Query class to perform its query.

### Syntax:

	$query->getTotal();

### Arguments:

*None.*

### Returns:

- (number) The number of results.

### Example:

	There are <?= $query->getTotal(); ?> results.


Query Method: toArray
---------------------------------

Returns the associative array of the fields and values of all rows returned by the query's execution.

### Syntax:

	$query->toArray();

### Arguments:

*None.*

### Returns:

- (array) An associative array of the data for each object.

### Example:

	$data = json_encode($query->toArray());

Query Method: getRow
---------------------------------

Returns the DataModel representing the specified row of data.

### Syntax:

	$query->getRow(rowNumber);

### Arguments:

1. rowNumber - (number) The row number to return.

### Returns:

- (DataModel) A DataModel object filled with the data from the requested row.

### Example:

	$data = json_encode($query->toArray());

