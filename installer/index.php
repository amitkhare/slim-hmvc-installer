<?php
require(__DIR__."/installer.php");
use AmitKhare\PHPInstaller\Installer;

$installer = new Installer(ROOT);
if(isset($_POST['submit'])){
	if($installer->setup($_POST)){
    if(unlink(__FILE__)){
      if(unlink("./installer.php")){
          if(unlink("./app.zip")){
             header("Location: /");
          }
      }
    }
		die('could not redirect.');
	} else {
		echo "failed, please check you db settings";
		die;
	}
}elseif(isset($_POST['checkConnection'])){
	if(Installer::testConnection($_POST)){
		echo "Connection successful";
		die;
	} else {
		echo "Connection failed";
		die;
	}
} else { ?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Basic Slim HMVC module template built on Bootstrap.">
    <meta name="author" content="Amit Kumar Khare @amitkhare">
   
    <title>Slim HMVC Skeleton</title>

    <link href="./installer/assets/bootstrap.css" rel="stylesheet" />
  </head>

  <body>

    <div class="site-wrapper">

      <div class="site-wrapper-inner">

        <div class="container">

          <div class="inner cover">
            <h1 class="cover-heading">Slim HMVC Installer</h1>
            <p class="lead">A HMVC modular application for Slim Framework. Use this application to quickly setup and
              start working on a new <a href="https://slimframework.com"  target="_blank">Slim Framework 3</a> with <a href="https://en.wikipedia.org/wiki/Hierarchical_model%E2%80%93view%E2%80%93controller" target="_blank">HMVC</a> capabilities.</p>
            <div class="lead">
              <form class="form-horizontal" method="POST" target="_blank">
                <fieldset>

                <!-- Form Name -->
                <legend>Slim HMVC Installer</legend>

                <!-- Text input-->
                <div class="form-group">
                  <label class="col-md-4 control-label" for="hostname">Hostname</label>  
                  <div class="col-md-4">
                  <input id="hostname" name="hostname" type="text" placeholder="localhost" class="form-control input-md" required="">
                  </div>
                </div>

                <!-- Text input-->
                <div class="form-group">
                  <label class="col-md-4 control-label" for="username">Username</label>  
                  <div class="col-md-4">
                  <input id="username" name="username" type="text" placeholder="Your MySQL username?" class="form-control input-md" required="">
                  </div>
                </div>

                <!-- Text input-->
                <div class="form-group">
                  <label class="col-md-4 control-label" for="databasename">Database Name</label>  
                  <div class="col-md-4">
                  <input id="databasename" name="databasename" type="text" placeholder="MySQL database Name" class="form-control input-md" required="">
                  </div>
                </div>

                <!-- Text input-->
                <div class="form-group">
                  <label class="col-md-4 control-label" for="password">Password</label>  
                  <div class="col-md-4">
                  <input id="password" name="password" type="text" placeholder="Your MySQL password?" class="form-control input-md" required="">
                  </div>
                </div>

                <!-- Button (Double) -->
                <div class="form-group">
                  <label class="col-md-4 control-label" for="submit">Authenticate</label>
                  <div class="col-md-8">
                    <button id="submit" name="submit" class="btn btn-success">Setup Site</button>
                    <button id="checkConnection" name="checkConnection" class="btn btn-info">Test Connection</button>
                  </div>
                </div>

                </fieldset>
                </form>
            </div>
          </div>

          <div class="footer" >
            <div class="inner">
              <p>Slim HMVC Skeleton Installer for <a href="https://github.com/amitkhare/slim-hmvc">Slim HMVC</a>, by <a href="https://twitter.com/amitkhare">@amitkhare</a>.</p>
            </div>
          </div>

        </div>

      </div>

    </div>

    <script src="./installer/assets/jquery.js"></script>
    <script src="./installer/assets/bootstrap.js"></script>
  </body>
</html>
<?php } ?>