# slim-hmvc-installer
A HMVC modular application for Slim Framework. Use this application to quickly setup and start working on a new Slim Framework 3 with HMVC capabilities.

## Install

Run this command from the directory in which you want to install your new Slim 3 Framework with modular HMVC.

Via Composer:

    php composer.phar create-project amitkhare/slim-hmvc-installer [my-app-name]

Via Git:

    git clone https://github.com/amitkhare/slim-hmvc-installer.git [my-app-name]

    composer update

> Replace `[my-app-name]` with the desired directory name for your new application. You'll want to:

> Point your virtual host document root to your new [my-app-name]'s root / directory. it will automatically redirect all calls to /public/index.php via .htaccess

>> Open in Web Browser, follow onscreen instructions.
