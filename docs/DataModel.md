DataModel
======================

The DataModel class is at the heart of the object-relational mapping layer that makes RabidCore so powerful.  It interacts with the Database to create a transparent persistent data layer for your application.

Please note that the DataModel class should never be accessed directly.  Instead, create a new class for every Model in your application and have it extend from the DataModel class.

For the purposes of demonstration, we will be using a DataModel classed called User.

Dependencies:
--------------------------------- 

- Database


DataModel: Scope Methods
---------------------------------

Scope methods determine what permissions a user has on an object.  Currently supported scopes are `canEdit`, `canView`, `canCreate` and `canDelete`.  `canEdit` and `canView` are passed the key name of the property being attempted to view/modify.

**Note**: Failed scope will *not* cause a DataModel object to abort saving.

### Example:

	class SecretIdea extends DataModel {
		
		//Only the user with the owner_id of this object may view its details.
		public function canView($key) {
			if (CurrentUser::getId() == $this->owner_id) return true;
		}
		
		//Only the user with the owner_id of this object may edit it.
		public function canEdit($key) {
			if (CurrentUser::getId() == $this->owner_id) return true;
		}
		
		//Only the user with the owner_id of this object may delete it.
		public function canDelete($key) {
			if (CurrentUser::getId() == $this->owner_id) return true;
		}
		
		//Only a user may create a SecretIdea.
		public function canCreate() {
			if (CurrentUser::loggedIn()) return true;
		}
		
	}
	
	$idea = new SecretIdea(Array(
		'owner_id'=>5, 
		'title'=>'World Domination', 
		'message'=>"I'd start a revolution if I could get up in the morning."
	)); $idea->save();
	
	CurrentUser::setId(6);
	echo $idea->title;   //Returns *null*.
	echo $idea->message; //Returns *null*.
	
	CurrentUser::setId(5);
	echo $idea->title;  //Returns "World Domination".
	

DataModel: Set Methods
---------------------------------

To add custom setting logic to your Model objects, create a method called setKey, where Key is the key name of the value being set.

If errors are found, the data object **will not save**.

### Passed Parameters

1. value - (string) The value of the property to being modified.

### Example

	class User extends DataModel {
		public function setGender($gender) {
			$g = strtolower(gender);
			if ($g == "m" or $g == "male")   return "m";
			if ($g == "f" or $g == "female") return "f";
			complain("must be male or female");
		}
	}

	//Later:
	$user->gender = 'please!';
	if ($user->errors) {
		echo "There are errors with your data:\n";
		foreach ($user->errors as $key=>$problem) {
			echo "\t - ".ucfirst($key)." ".""$problem.\n";
		}
	}

Outputs:

    There are errors with your data:

		- Gender must be male or female.

DataModel: Get Methods
---------------------------------

To add custom getting logic to your Model objects, create a method called getKey, where Key is the key name of the value being accessed.

### Passed Parameters:

- *None*.

### Example:

	class User extends DataModel {
		//Makes all user names awesome.
		public function getName() {
			return strtoupper($this->getRaw('name'))."!!!!";
		}
	}
	
	$user->name = "trogdor";
	$user->name; //TROGDOR!!!!

DataModel: Validation Methods
---------------------------------

The validate method is called every time a DataModel object's property is modified.  To add custom validation to your Model objects, create a method called validateKey, where Key is the key name of the value to be validated.

If errors are found, the data object **will not save**.

### Passed Parameters

1. value - (string) The value of the property to modify.

### Example

	class User extends DataModel {
		public function validateAge($age) {
			if (!is_numeric($age)) complain("must be a number");
			else if ($age < 13 || $age > 120) complain("must be between 13 and 120");
			else $this->setModifiedValue('age', $age);
		}
	}
	
	//Later:
	$user->age = 'really old';
	if ($user->errors) {
		echo "There are errors with your data:\n";
		foreach ($user->errors as $key=>$problem) {
			echo "\t - ".ucfirst($key)." ".""$problem.\n";
		}
	}
	
Outputs:

    There are errors with your data:
		
		- Age must be a number.


DataModel Method: constructor
---------------------------------

Creates a new DataModel object.

### Syntax:

	new DataModel(data);

### Arguments:

1. data    - (array) An associative array containing key value pairs to set on the newly created Model.

### Returns:

- (DataModel) A new instance of the DataModel object.

### Example:

	$user = new User(Array('name'=>'Trogdor', 'password'=>'burninate'));
	$user->save();


DataModel Method: init
---------------------------------

Called when a new DataModel object is created.  Useful for extending __construct functionality without having to overload the __construct method.


DataModel Method: toArray
---------------------------------

Returns the associative array of all fields in the DataModel.  The results are scoped to the current permissions.

### Syntax:

	$dataModel->toArray();

### Arguments:

*None.*

### Returns:

- (array) An associative array of the data for the object.

### Example:

	json_encode($user->toArray()); //{'name':'Trogdor', 'password':'burninate'}


DataModel Method: getTable
---------------------------------

Returns the name of the table within the database where data associated with this model is stored.

### Syntax:

	$dataModel->getTable();
	
### Arguments:

*None.*

### Returns:

- (string) The name of the table associated with this model.

### Example:

	$user->getTable(); //Returns 'user'.


DataModel Method: getModel
---------------------------------

Returns the lowercased name of the DataModel.

### Syntax:

	$dataModel->getModel();

### Arguments:

*None.*

### Returns:

- (string) The name of the model.

### Example:

	$user->getModel(); //Returns 'user'.


DataModel Method: save
---------------------------------

Saves the object's information to the database.  This will be called automatically unless the DataModel's $autosave property is set to false.

### Syntax:

	$dataModel->save();

### Arguments:

*None.*

### Returns:

- *Nothing.*

### Example:

    $user->status = "away";
	$user-save();


DataModel Method: delete
---------------------------------

Deletes the model.

### Syntax:

	$dataModel->delete();

### Arguments:

*None.*

### Returns:

- *Nothing.*

### Example

    $user->delete();


