# chatroom-php
A first attempt to build a realtime chatroom application using PHP, Bootstrap, SCSS, and JavaScript.
Created in early 2020.

## Features
- signup page: add your name and password, and choose an icon
- login page: log in using your credentials. Validation included!
- homepage: chat with other online users here! 

## Setup Needs
- XAMPP (PHP 7 + MySQL)
- Node.js 16+
- node modules (update as you go)
- websocket support
- Font Awesome (already included in repo)
  
## How to run (XAMPP setup)
- clone repository to `htdocs` folder
- run XAMPP and navigate to index.php
- in a separate Windows terminal, run the `server.php` file
  ```
  php websocket/server.php
  ```
- users should then be able to sign up, log in, and chat!
