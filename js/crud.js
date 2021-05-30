// File:    crud.js
// Author:  Jamyang Tamang
// Date:    2022-05-30
// Purpose: Handles saving and loading data.

/**
 * Copies the specified fields from `data` (if they exists) into a new object 
 * and returns it.
 * 
 * @param data The object to copy.
 * @param keys The fields to copy.
 * @return A copy of `data` with only the specified fields.
 */
function copyByKey(data, keys){
    var dataCopy = {};
    for(var i = 0; i < keys.length; i++){
        var key = keys[i];
        if(data[key] !== undefined){
            dataCopy[key] = data[key];
        }
    }
    return dataCopy;
}

/**
 * Signs a user up 
 * 
 * @param username The desired username.
 * @param password The desired password.
 * @param onsuccess (Optional) The function to call when the user has been 
 *                  successfully signed up.
 * @param onerror (Optional) The function to call if the user couldn't be 
 *                signed up.
 */
// function signup(username, password, onsuccess, onerror){

//     // If there's no error handler, add the generic one.
//     if(!onerror){
//         onerror = genericErrorHandler;
//     }

//     $.ajax({
//         url: 'api.php',
//         method: 'post',
//         data: {
//             username: username,
//             password: password,
//             action: 'signup'
//         },
//         success: function(data){
//             if(data.success && onsuccess){
//                 onsuccess(data.data);
//             } else if(!data.success){
//                 onerror(data.error);
//             }
//         },
//         error: function(jqXHR, status, error){
//             onerror(error);
//         }
//     });
// }

/**
 * Signs a user in.
 * 
 * @param username The username.
 * @param password The password.
 * @param onsuccess (Optional) The function to call when the user has been 
 *                  successfully signed in.
 * @param onerror (Optional) The function to call if the user couldn't be 
 *                signed in.
 */
function signin(username, password, onsuccess, onerror){

    // If there's no error handler, add the generic one.
    if(!onerror){
        onerror = genericErrorHandler;
    }

    $.ajax({
        url: 'api.php',
        method: 'post',
        data: {
            username: username,
            password: password,
            action: 'signin'
        },
        success: function(data){
            if(data.success && onsuccess){
                onsuccess(data.data);
            } else if(!data.success){
                onerror(data.error);
            }
        },
        error: function(jqXHR, status, error){
            onerror(error);
        }
    });
}

/**
 * Signs a user out.
 * 
 * @param onsuccess (Optional) The function to call when the user has been 
 *                  successfully signed out.
 * @param onerror (Optional) The function to call if the user couldn't be 
 *                signed out.
 */
function signout(onsuccess, onerror){

    // If there's no error handler, add the generic one.
    if(!onerror){
        onerror = genericErrorHandler;
    }

    $.ajax({
        url: 'api.php',
        method: 'post',
        data: {
            action: 'signout'
        },
        success: function(data){
            if(data.success && onsuccess){
                onsuccess(data.data);
            } else if(!data.success){
                onerror(data.error);
            }
        },
        error: function(jqXHR, status, error){
            onerror(error);
        }
    });
}

/**
 * Gets the staus of a user (whether they are signed in or not).
 * 
 * @param onsuccess (Optional) The function to call when the user status has  
 *                  been successfully retrieved.
 * @param onerror (Optional) The function to call if the user status couldn't be 
 *                retrieved.
 */
function userSignedIn(onsuccess, onerror){

    // If there's no error handler, add the generic one.
    if(!onerror){
        onerror = genericErrorHandler;
    }

    $.ajax({
        url: 'api.php',
        method: 'post',
        data: {
            action: 'get-user-status'
        },
        success: function(data){
            if(data.success && onsuccess){
                onsuccess(data.data);
            } else if(!data.success){
                onerror(data.error);
            }
        },
        error: function(jqXHR, status, error){
            onerror(error);
        }
    });
}

/**
 * Prints the error to the console.
 */
function genericErrorHandler(error){
    console.log(error);
}