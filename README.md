# TimeSheet

This is the time sheet where teachers can login at a given period and log out. This will essentially be an 
attendece sheet


## Source Code

Here's a quick rundown of the source code.

  * `index.html` -- this is the full page! It's a single page app, so all of the
                    static HTML is here, though there are some elements that
                    you will find injected by various JavaScript functions.
  * `js/`        -- this folder contains all of the JavaScript files. They
                    have been broken up based on their primary purpose.
    - `crud.js`  -- handles saving and loading data.
    - `*-view.js` -- handles functionality for the three main views: the 
                    "homepage" with the login section, and the "signout" page
    - `main.js` -- handles the initial kickoff code (`$(document).ready(...)`)
                    and switching between views.
  * api.php     -- the server-side JSON API code, which interacts with an
                   SQLite3 database.
                   