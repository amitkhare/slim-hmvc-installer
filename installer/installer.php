<?php namespace AmitKhare\PHPInstaller;

class Installer {
	private $baseDir;

	function __construct($baseDir){
		$this->baseDir = $baseDir;
	}

	private function makeWritable($path=__DIR__){
		if(chmod($path, 0777)){
			return true;
		}
	}

	private function returnHtaccessRoot(){
		return "<IfModule mod_rewrite.c>
    RewriteEngine on
    RewriteRule    ^$   public/    [L]
    RewriteRule    (.*) public/$1  [L]
</IfModule>";
	}

	private function returnHtaccessPublic(){
		return "RewriteEngine On
# Some hosts may require you to use the `RewriteBase` directive.
# If you need to use the `RewriteBase` directive, it should be the
# absolute physical path to the directory that contains this htaccess file.
#
# RewriteBase /

RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [QSA,L]";
	}

	private function createFile($path,$name="file.ext",$content=""){
		$filepath = $path.DIRECTORY_SEPARATOR.$name;
		if(!$this->folder_exist($path)){
			$this->createDir($path);
		}
		
		if($f = fopen($filepath, "c")){
			fwrite($f, $content);
			fclose($f);
			return true;			
		}

	}

	private function deleteSelf() {
	    if($this->rmdir_recursive(__DIR__.DIRECTORY_SEPARATOR)){
	    	return true;
	    }
	}

	private function rmdir_recursive($dir) {
	    foreach(scandir($dir) as $file) {
	        if ('.' === $file || '..' === $file) continue;
	        if (is_dir("$dir/$file")) $this->rmdir_recursive("$dir/$file");
	        else unlink("$dir/$file");
	    }
	    if(rmdir($dir)){
	    	return true;
	    }
	}

	private function createHtaccess($path,$content=""){
		if($this->createFile($path,".htaccess",$content)){
			return true;
		}
	}

	private function createHtaccessRoot(){
		if($this->createHtaccess( $this->baseDir , $this->returnHtaccessRoot() ) ){
			return true;
		}
	}

	private function createHtaccessPublic(){
		if($this->createHtaccess( $this->baseDir.DIRECTORY_SEPARATOR."public" , $this->returnHtaccessPublic() ) ){
			return true;
		}
	}

	private function createDir($path){
		if (!$this->folder_exist($path)) {
		    mkdir($path, 0777);
		    return true;
		} else {
		   return false;
		}
	}

	private function folder_exist($folder) {
	    // Get canonicalized absolute pathname
	    $path = realpath($folder);

	    // If it exist, check if it's a directory
	    return ($path !== false AND is_dir($path)) ? $path : false;
	}


	public function setup($postData){
		$flag = false;
		if($this::testConnection($_POST)){
			if($this->unZipApp()){
				$flag = $this->setupDB($this->makeDBSettings($postData));
			}
		}
		return $flag;
	}

	private function makeDBSettings($postData){
		$dbSettings['HOSTNAME'] = $postData['hostname'];
		$dbSettings['USERNAME'] = $postData['username'];
		$dbSettings['PASSWORD'] = $postData['password'];
		$dbSettings['DATABASENAME'] = $postData['databasename'];
		return $dbSettings;
	}
		

	private function setupDB ($db=array()){
		$path = $this->baseDir.DIRECTORY_SEPARATOR."app".DIRECTORY_SEPARATOR."src".DIRECTORY_SEPARATOR;
		$filename = "settings.php";
		$path_to_file = $path.$filename;

		$search  = array('DBHOSTNAME', 'DBUSERNAME', 'DBPASSWORD', 'DBDATABASENAME');
	    $replace = array($db['HOSTNAME'], $db['USERNAME'], $db['PASSWORD'], $db['DATABASENAME']);

		$file_contents = file_get_contents($path_to_file);
		$file_contents = str_replace($search, $replace, $file_contents);
		if(file_put_contents($path_to_file,$file_contents)){
			
			return true;

		}
	}
	private function unZipApp(){
		$zip = new \ZipArchive;
		$res = $zip->open(__DIR__."/app.zip");
		if ($res === TRUE) {
		  $zip->extractTo($this->baseDir.DIRECTORY_SEPARATOR);
		  $zip->close();
		  return true;
		}
	}

	public static function testConnection($dbSettings){
		$dbhost = $dbSettings['hostname'];
		$dbuser = $dbSettings['username'];
		$dbpass = $dbSettings['password'];
		$dbname = $dbSettings['databasename'];

		$conn = new \mysqli($dbhost,$dbuser,$dbpass,$dbname);

		if ($conn->connect_error) {
		    return false;//die("Connection failed: " . $conn->connect_error);
		}else{
			return true;
		}
	}
}
