<?
/* The class for installing Lobby
 * Contains Database & Software Creation
*/
class Installation extends L{
	private $database = array();
	private $dbh;

	/* The $check parameter tells if any Success output should be made or not. Default : Visible */
	public function step1($check=false){
		if(!is_writable(L_ROOT)){
    		ser("Error", "Lobby Directory is not Writable. Please set <blockquote>" . L_ROOT . "</blockquote> directory's permission to writable.<cl/><a href='install.php?step=1' class='button'>Check Again</a>");
    	}elseif(file_exists($this->root . "/config.php")){
    		ser("config.php File Exists", "A config.php file already exitsts in <blockquote>{$this->root}</blockquote> directory. Remove it and try again. <cl/><a href='install.php?step=1' class='button'>Check Again</a>");
    	}else{
    		/* Display "OK to continue" message */
    		$check ?: sss("Good", "Everything looks OK. You can continue the installation.<cl/><a href='install.php?step=2' class='button'>Next Step</a>");
    	}
	}
	
	/* We don't have a step2 function because There is no Step 3 */
	
	public function checkDatabaseConnection(){
		try {
      	$db = new PDO("mysql:dbname={$this->database['name']};host={$this->database['host']};port={$this->database['port']}", $this->database['user'], $this->database['pass'], array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION));
      	$this->dbh = $db;
     	}
     	catch( PDOException $Exception ) {
      	ser("Error", "Unable to connect. Make sure that the settings you entered are correct. <cl/><a href='install.php?step=1'>Try Again</a>");
     	}
	}
	
	public function makeConfigFile(){
		$randString    = $this->randStr(35);
		$configFileLoc	= $this->root . "/config.php";
		$cfg 				= $this->database;
		
		/* Make the configuration file */
		$config_sample = file_get_contents(L_ROOT . "/config-sample.php");
     	$config_file 	= str_replace("/* A Sample Configuration File */", "/* The Configuration File */", $config_sample);
     	$config_file 	= str_replace("/* !! DO NOT EDIT THIS FILE !! */", "/* Be careful while editing */", $config_file);
     	$config_file 	= preg_replace("/host(.*?)\'\'/", "host$1'{$cfg['host']}'", $config_file);
     	$config_file 	= preg_replace("/port(.*?)''/",   "port$1'{$cfg['port']}'", $config_file);
     	$config_file 	= preg_replace("/user(.*?)''/",   "user$1'{$cfg['user']}'", $config_file);
     	$config_file 	= preg_replace("/pass(.*?)''/",   "pass$1'{$cfg['pass']}'", $config_file);
		$config_file 	= preg_replace("/db(.*?)''/",     "db$1'{$cfg['name']}'", $config_file);
     	$config_file 	= preg_replace("/key(.*?)''/",    "key$1'{$randString}'", $config_file);
     	$config_file 	= preg_replace("/prefix(.*?)''/", "prefix$1'{$cfg['prefix']}'", $config_file);
		
		/* Create the config.php file */
     	if(!file_put_contents($configFileLoc, $config_file)){
      	ser("Failed Creating Config File", "Something happened while creating the file. Perhaps it was something that you did ?");
		}
	}
	
	/* Create Tables in the DB */
	public function makeDatabase($prefix){
		try {
  			/* Create Tables */
  			$sql = $this->dbh->prepare("
   			CREATE TABLE IF NOT EXISTS `{$prefix}options` (
    				`id` int(11) NOT NULL AUTO_INCREMENT,
    				`name` varchar(64) NOT NULL,
    				`val` tinytext NOT NULL,
    				PRIMARY KEY (`id`),
   				UNIQUE(`name`)
   			) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;
   			CREATE TABLE IF NOT EXISTS `{$prefix}data` (
    				`id` int(11) NOT NULL AUTO_INCREMENT,
    				`app` varchar(50) NOT NULL,
    				`name` varchar(150) NOT NULL,
    				`content` longtext NOT NULL,
    				`updated` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
   				PRIMARY KEY (`id`)
   			) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;"
  			);
  			$sql->execute();
  			
  			/* Insert The Default Data In To Tables */
  			$Linfo = file_get_contents(L_ROOT."/lobby.json");
  			$Linfo = json_decode($Linfo, true);
  			$sql = $this->dbh->prepare("
   			INSERT INTO `{$prefix}options` (
    				`id`, 
    				`name`, 
    				`val`
    			) VALUES (
     				NULL, 
     				'active_apps', 
     				'[\"ledit\"]'
    			),(
     				NULL,
     				'lobby_version',
     				?
    			),(
     				NULL,
     				'lobby_version_release',
     				?
    			);"
  			);
  			$sql->execute(array($Linfo['version'], $Linfo['released']));
  			return true;
 		}catch(PDOException $Exception){
  			return false;
 		}
	}
	
	public function dbConfig($array){
		$this->database = $array;
	}
}
?>