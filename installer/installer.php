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
		if($this::testConnection($postData)){
			if($this->unZipApp()){
				if($this->setupDB($this->makeDBSettings($postData))){
					$flag = $this->importMySQL(__DIR__.DIRECTORY_SEPARATOR."slimtest.sql");
				}
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

			if($this->IMPORT_TABLES($db['HOSTNAME'],$db['USERNAME'],$db['PASSWORD'],$db['DATABASENAME'], __DIR__."/slimtest.sql")){
				return true;
			}

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

	function IMPORT_TABLES($host, $user, $pass, $dbname, $sql_file_OR_content, $replacements = array('OLD_DOMAIN.com','NEW_DOMAIN.com')) {
		set_time_limit(3000);
		$SQL_CONTENT = (strlen($sql_file_OR_content) > 200 ? $sql_file_OR_content : file_get_contents($sql_file_OR_content));
		if (function_exists('DOMAIN_or_STRING_modifier_in_DB'))
			{
			$SQL_CONTENT = DOMAIN_or_STRING_modifier_in_DB($replacements[0], $replacements[1], $SQL_CONTENT);
			}

		$allLines = explode("\n", $SQL_CONTENT);
		$mysqli = new \mysqli($host, $user, $pass, $dbname);
		if (mysqli_connect_errno())
			{
			echo "Failed to connect to MySQL: " . mysqli_connect_error();
			}

		$zzzzzz = $mysqli->query('SET foreign_key_checks = 0');
		preg_match_all("/\nCREATE TABLE(.*?)\`(.*?)\`/si", "\n" . $SQL_CONTENT, $target_tables);
		foreach($target_tables[2] as $table)
			{
			$mysqli->query('DROP TABLE IF EXISTS ' . $table);
			}

		$zzzzzz = $mysqli->query('SET foreign_key_checks = 1');
		$mysqli->query("SET NAMES 'utf8'");
		$templine = ''; // Temporary variable, used to store current query
		foreach($allLines as $line)
			{ // Loop through each line
			if (substr($line, 0, 2) != '--' && $line != '')
				{
				$templine.= $line; // (if it is not a comment..) Add this line to the current segment
				if (substr(trim($line) , -1, 1) == ';')
					{ // If it has a semicolon at the end, it's the end of the query
					$mysqli->query($templine) or print ('Error performing query \'<strong>' . $templine . '\': ' . $mysqli->error . '<br /><br />');
					$templine = ''; // set variable to empty, to start picking up the lines after ";"
					}
				}
			}

		echo 'Importing finished. Now, Delete the import file.';
	} //see also export.php


}

