// File:    accounts.js
// Author:  Jamyang Tamang
// Date:    2022-05-30
// Purpose: Provides functions for user account management like signing up.

/**
 * A listener for the signup form. Verifies that the fields are present, and if
 * so, signs the user up. Otherwise, shows an error message about what's
 * missing.
 */
function onSignupFormSubmission(event){
    console.log('in onSignupFormSubmission');

    username = this.username.value;
    var password = this.password.value;
    var password2 = this.password2.value;

    console.log(username, password, password2);

    $(this).siblings('.error-message').hide();

    // Check that the passwords match.
    if(password !== password2){
        $(this).siblings('.error-message').show().html('The passwords must match!');
    } else {
        signup(username, password, updateUIForSignin);
    }

    event.preventDefault();
}

/**
 * A listener for the signin form.
 */
function onSigninFormSubmission(event){
    username = this.username.value;
    var password = this.password.value;

    signin(username, password, updateUIForSignin);
    event.preventDefault();
}

/**
 * Updates the UI for the logged in user.
 */
function updateUIForSignin(data){
    console.log('in updateUIForSignin');

    username = data.username;
    userId = data.id;
    
    signedIn = true;
    
    $('.username').html(username);
    $('.signedin').show();
    $('.signedout').hide();

    window.location.hash = '#';
}

/**
 * Signs the user out (if one is signed in);
 */
function onSignout(event){
    signout(function(){
        signedIn = false;
        username = '';
        userId = -1;
        $('.username').html(username);
        $('.signedin').hide();
        $('.signedout').show();
        if(player){
            player.destroy();
            player = null;
        }
        playerInfo = {};
        
        if(inEditMode){
            toggleMode();
        }

        window.location.hash = '#';
        updateViews();
    });


    event.preventDefault();
    return false;
}
