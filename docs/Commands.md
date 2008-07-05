Commands
======================

The Commands class and its children define the default behaviours of all models.  It is not necessary to extend this class unless you want to change the default actions of a DataModel, or create actions for a route that does not correspond to a model.

Command methods are called by the Router based on the request URI.  See the Router documentation for more details.

The Commands class makes calls to `onDelete`, `onShow`, `onCreate`, and `onEdit` (respectively) after each command is executed.  This allows the developer to extend the functionality of these base commands without modifying them directly.

**Note:** The Commands class is executed by the Router.

Dependencies:
--------------------------------- 

- DataModel

Protected Property: $defaultAction
---------------------------------

The default action for Command objects is 'index'.  Modify if you would like to use a different default action.

Protected Property: $model
---------------------------------

Modify the model name if it is different than the class name minus "Commands".


Commands Method: index
---------------------------------

Returns every item belonging to this DataModel.

### Router Paths:

- /*model*
- /*model*/index

### Returns:

- A Query object containing every item belonging to this DataModel.


Commands Method: show
---------------------------------

Returns one item belonging to this DataModel, based on the parameters passed.

### Router Path:

- /*model*/show/*id*

### Parameters:

1. id - The unique identifier of the item.

### Returns:

- (DataModel) The specified item.


Commands Method: edit
---------------------------------

Modifies the model.

### Router Path:

- /*model*/edit/*id*

### Parameters:

1. id   - The unique identifier of the item.
2. data - (array) An array of key/value pairs of the fields to edit.  Automatically passed by the Router as the contents of the $_POST array.

### Returns:

- (DataModel) The resulting modified item.

### Redirect:

- /*model*/show/*id*


Commands Method: delete
---------------------------------

Deletes the designated item.

### Router Path:

- /*model*/delete/*id*

### Parameters:

1. id - The unique identifier of the item.

### Returns:

- *Nothing.*

### Redirect:

- /*model*/index

Commands Method: create
---------------------------------

Creates a new DataModel item.

### Router Path:

- /*model*/create

### Parameters:

1. data - (array) An array of key/value pairs of the fields to edit.  Automatically passed by the Router as the contents of the $_POST array.

### Returns:

- (DataModel) The newly created object.

### Redirect:

- /*model*/show/*id*
