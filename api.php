<?php 
// File:    api.php
// Author:  Jamyang Tamang
// Date:    2021-05-30
// Purpose: Handles serverside CRUD operations through a JSON API.

// TODO: What do we need up here for sessions?
session_start();

header('Content-type: application/json');

// For debugging:
error_reporting(E_ALL);
ini_set('display_errors', '1');

// TODO Change this as needed. SQLite will look for a file with this name, or
// create one if it can't find it.
$dbName = 'teachers.db';

// Leave this alone. It checks if you have a directory named www-data in
// you home directory (on a *nix server). If so, the database file is
// sought/created there. Otherwise, it uses the current directory.
// The former works on digdug where I've set up the www-data folder for you;
// the latter should work on your computer.
$matches = [];
preg_match('#^/~([^/]*)#', $_SERVER['REQUEST_URI'], $matches);
$homeDir = count($matches) > 1 ? $matches[1] : '';
$dataDir = "/home/$homeDir/www-data";
if(!file_exists($dataDir)){
    $dataDir = __DIR__;
}
$dbh = new PDO("sqlite:$dataDir/$dbName")   ;
// Set our PDO instance to raise exceptions when errors are encountered.
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$COLUMN_CONVERTERS= [];

createTables();

////////////////////////////////////////////////////////////////////////////////
/////// Routes table
////////////////////////////////////////////////////////////////////////////////
// Handle incoming requests.
if(array_key_exists('action', $_POST)){
    $action = $_POST['action'];

    // User operations.
    if($action == 'signup'){
        signup($_POST);
    } else if($action == 'signin'){
        signin($_POST);
    } else if($action == 'signout'){
        signout();
    } else if($action == 'get-user-status'){
        getUserStatus($_POST);
    } else {
        error("Invalid action: $action");
    }
}

////////////////////////////////////////////////////////////////////////////////
/////// Table management
////////////////////////////////////////////////////////////////////////////////
// Create the Users table
function createTables(){
    global $dbh, $COLUMN_CONVERTERS;

    try{
        $dbh->exec('create table if not exists Users('. 
            'id integer primary key autoincrement, '. 
            'username text unique, password text)');

        $COLUMN_CONVERTERS = array_merge($COLUMN_CONVERTERS, [
            // Ignore id since it's alreay in $COLUMN_CONVERTERS.
            'id' => 'intval',
            'username' => 'string',
            'password' => 'string'
        ]);


    } catch(PDOException $e){
        error("There was an error creating the tables: $e");
    }
}

////////////////////////////////////////////////////////////////////////////////
////// User account handlers 
////////////////////////////////////////////////////////////////////////////////

/**
 * Signs up a user. Expects the following fields in `$data`:
 *   - username
 *   - password
 * 
 * Produces a JSON object with these fields:
 *      - success -- true/false
 *      - error -- (on success=false only) an error message
 * 
 * @param data A map of parameters and their values.
 */
function signup($data){
    // TODO
        
    global $dbh;

    try{
        $password = $data['password'];
        $saltedHash = password_hash($password, PASSWORD_BCRYPT);
        $statement = $dbh->prepare('insert into Users(username, password) '. 
            'values (:username, :password)');
        $statement->execute([
            ':username' => $data['username'],
            ':password' => $saltedHash
        ]);

        $id = intval($dbh->lastInsertId());

        $_SESSION['signed-in'] = true;
        $_SESSION['user-id'] = $id;

        success([ 
            'username' => $data['username'],
            'id' => $id
        ]);

    } catch(PDOException $e) {
        error("There was an error signing up: $e");
    }

}

/**
 * Signs in a user. Expects the following fields in `$data`:
 *   - username
 *   - password
 * 
 * Produces a JSON object with these fields:
 *      - success -- true/false
 *      - data (if success=true)
 *          * id (user id)
 *          * username (the user's username)
 *      - error -- (on success=false only) an error message
 * 
 * @param data A map of parameters and their values.
 */
function signin($data){
    global $dbh;

    try{
        $statement = $dbh->prepare("select * from Users where username = :username");
        $statement->execute([':username' => $data['username']]);
        $userInfo = $statement->fetch(PDO::FETCH_ASSOC);

        if($userInfo != null && password_verify($data['password'], 
                $userInfo['password'])){

            castColumnValues($userInfo);

            $_SESSION['signed-in'] = true;
            $_SESSION['user-id'] = $userInfo['id'];

            success([ 
                'username' => $data['username'],
                'id' => $userInfo['id']
            ]);
        } else {
            error('The username or password is incorrect.');
        }

    } catch(PDOException $e) {
        error("There was an error signing up: $e");
    }

}

/**
 * Signs out the currently signed in user.
 * 
 * Produces a JSON object with these fields:
 *      - success -- true/false
 *      - error -- (on success=false only) an error message
 * 
 * @param data A map of parameters and their values.
 */
function signout(){
    session_destroy();
    success(null);
}


/**
 * Gets the username/id of the currently logged in user, if any.
 * 
 * Produces a JSON object with these fields:
 *      - success -- true/false (false is returned if no user is logged in)
 *      - data
 *          * id (the user's id in the database)
 *          * username 
 *      - error -- (on success=false only) an error message
 * 
 * @param data A map of parameters and their values.
 */
function getUserStatus(){
    global $dbh;

    if(!array_key_exists('signed-in', $_SESSION) || !$_SESSION['signed-in']){
        error('There is no current user session.');
    }

    try{
        $statement = $dbh->prepare("select * from Users where id = :id");
        $statement->execute([':id' => $_SESSION['user-id']]);
        $userInfo = $statement->fetch(PDO::FETCH_ASSOC);
        castColumnValues($userInfo);

        if($userInfo){
            success([ 
                'username' => $userInfo['username'],
                'id' => $userInfo['id']
            ]);
        } else {
            session_destroy();
            error('There is no current user session.');
        }
       

    } catch(PDOException $e) {
        error("There was an error finding information about the current ". 
            "session: $e");
    }
}



////////////////////////////////////////////////////////////////////////////////
////// Helper functions 
////////////////////////////////////////////////////////////////////////////////

/**
 * If no user is signed in, an error message is emitted and the script stops.
 */
function stopUnlessUserSignedIn(){
    if(!array_key_exists('signed-in', $_SESSION) || !$_SESSION['signed-in']){
        error('You must be signed in to perform the requested action.');
    }
}


/**
 * Emits a JSON object with two fields:
 *   - success => true
 *   - data => the data that was passed in as `$data`
 * 
 * @param $data The value to assign to the `data` field of the output.
 */
function success($data){
    $response = ['success' => true];
    if($data){
        $response['data'] = $data;
    }
    die(json_encode($response));
}

/**
 * Emits a JSON object with two fields:
 *   - success => false
 *   - error => an error message`
 * 
 * @param $error The value to assign to the `error` field of the output.
 */
function error($error){
    die(json_encode([
        'success' => false,
        'error' => $error
    ]));
}

/**
 * Copies all the key-value pairs from `$data` to a new associative array, 
 * but all of the keys are prefixed with a colon (:) so they can be used 
 * as placeholders in a prepared PDO statement.
 * 
 * @param data The associative array to copy.
 * @param keys (Can be null) A list of the keys to copy. If null, all keys are
 *              copied.
 * @return A new associative array.
 */
function keysToPlaceholders($data, $keys){
    $dataCopy = [];

    if($keys === null){
        $keys = array_keys($data);
    }

    foreach($keys as $key){
        if(array_key_exists($key, $data)){
            $dataCopy[":$key"] = $data[$key];
        }
    }

    return $dataCopy;
}

/**
 * Casts all of the columns in the given associative array based on the
 * cast function specfified in $COLUMN_CONVERTERS. Columns with a cast
 * function of "string" are kept as is.
 * 
 * @param columns An associative array of column names to values. Values are 
 *                casted in place.
 */
function castColumnValues(&$columns){
    global $COLUMN_CONVERTERS;
    $keys = array_keys($columns);
    foreach($keys as $column){
        if($COLUMN_CONVERTERS[$column] != 'string'){
            // What's happening here? `$COLUMN_CONVERTERS[$column]` returns 
            // the name of a function (e.g., 'intval'). In PHP, if you take
            // string and put parentheses after it, it will try to execute 
            // a function with that name. E.g., 'intval'('10') is the same
            // as inval('10'). This is a *super* handy property!
            $columns[$column] = $COLUMN_CONVERTERS[$column]($columns[$column]);
        }
    }
}

?>