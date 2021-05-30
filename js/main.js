// File:    main.js
// Author:  Jamyang Tamang
// Date:    2021-05-30
// Purpose: Specifies globals, the document login/logout function, and the 
//          view switching code.

// Globals.
var username;
var userId;
var signedIn = false;

/**
 * Updates the view based on the hash state.
 */
function updateViews(){
    console.log('in updateView');
    var id = window.location.hash.match(/^#?([^?]*)/)[1];

    // Hides all views so we can show just the one that's been selected.
    $('.view').hide();

    // Sign out page.
    if(id === "signout" ){
        $('.signout-view').show();
    // Sign up page.
    } else if(id === "signup" ){
        $('.signup-view').show();

    // If not signed in and not requesting sign in or sign up, redirect to
    // sign in page.
    } else if(!signedIn){
        window.location.hash = "#signin";
        return;    
    // Sign in page.
    } else {
        $('.signin-view').show();
    }
}

// Main.
$(document).ready(function(){
    inEditMode = false;

    $('.view').hide();

    // User accounts.
    $(document).on('submit', '#signup form', onSignupFormSubmission);
    $(document).on('submit', '#signin form', onSigninFormSubmission);
    $(document).on('click', '#signout', onSignout);

    // Check if the user is signed in.
    userSignedIn(function(data){
        username = data.username;
        updateUIForSignin();
    });

    $('.signedin').hide();
    $('.signedout').show();

    updateViews();
});