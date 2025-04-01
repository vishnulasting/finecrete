<?php
//echo getcwd();die();

require __DIR__ . '/vendor/autoload.php';

use Spatie\DbDumper\Databases\MySql;


class DatabaseBackup
{

    protected $host, $username, $password, $database, $email;

    function __construct($host, $username, $password, $database, $email){
	$this->host = $host;
	$this->username = $username;
    $this->password = $password;
    $this->database = $database;
	 
	  $result = mysqli_connect($host,$username,'#Lh{a@I~VQ6I') or die("Could not connect to database." .mysqli_error());
        mysqli_select_db($result,$database) or die("Could not select the databse." .mysqli_error());
	 
	  // $image_query = mysqli_query($result,"SELECT * FROM `user` WHERE `role`=1 and `email_status`='verified' and `business_status`=1 and `company_db_name`!=''");
	   $image_query = mysqli_query($result,"SELECT user.* FROM `user` LEFT JOIN company_settings on company_settings.c_id = user.c_id WHERE user.role =1 and user.email_status ='verified' and user.business_status =1 and user.company_db_name!='' and company_settings.backup_frequency='daily'");
		$resultArray = array();
        while($rows = mysqli_fetch_assoc($image_query)){
			$resultArray[] = $rows;
		}
		
     //   $this->email = $email;

        $this->initMySQLDBBackup($resultArray);
		
	//	$resArray['email'], $resArray['company_db_name']
	//	$file_name = 'database_backups/'. $this->database . '_' . date('Y_m_d', time()) . '.sql';
		
     //  $this->sendEmail('dev@lastingerp.com', $this->database);
	 
	 
		$resultMainDB = mysqli_connect('localhost','ERP_root','#Lh{a@I~VQ6I') or die("Could not connect to database." .mysqli_error());
		mysqli_select_db($resultMainDB,'azuka_erp_new') or die("Could not select the databse." .mysqli_error());
		$resMainDB = array();
		$resMainDB[] = array('company_db_name'=> 'azuka_erp_new', 'email'=> 'dev@lastingerp.com' );
		$this->initMySQLDBBackup($resMainDB);
		
		
		
		/*$main_file_name = 'database_backups/'. $resArray['company_db_name'] . '_' . date('Y_m_d', time()) . '.sql';
			MySql::create()
            ->setDbName('azuka_erp_new')
            ->setUserName('ERP_root')
            ->setPassword('#Lh{a@I~VQ6I')
            ->dumpToFile($main_file_name);
			$this->sendEmail('dev@lastingerp.com', 'azuka_erp_new');
			mysqli_close($resultMainDB);*/
	 
	 
	 
	 
	 mysqli_close($result);
    }

    public function initMySQLDBBackup($resultArray) {	
	//print_r($resultArray);
		foreach($resultArray as $resArray){	
		
			$result = mysqli_connect('localhost','ERP_root','#Lh{a@I~VQ6I') or die("Could not connect to database." .mysqli_error());
			mysqli_select_db($result,$resArray['company_db_name']) or die("Could not select the databse." .mysqli_error());
			$file_name = 'database_backups/'. $resArray['company_db_name'] . '_' . date('Y_m_d', time()) . '.sql';
			
			MySql::create()
            ->setDbName($resArray['company_db_name'])
            ->setUserName('ERP_root')
            ->setPassword('#Lh{a@I~VQ6I')
            ->dumpToFile($file_name);
			$this->sendEmail($resArray['email'], $resArray['company_db_name']);
			mysqli_close($result);
		}
		
		
		/*$resultMainDB = mysqli_connect('localhost','ERP_root','#Lh{a@I~VQ6I') or die("Could not connect to database." .mysqli_error());
		mysqli_select_db($resultMainDB,'azuka_erp_new') or die("Could not select the databse." .mysqli_error());
		$main_file_name = 'database_backups/'. $resArray['company_db_name'] . '_' . date('Y_m_d', time()) . '.sql';
			MySql::create()
            ->setDbName('azuka_erp_new')
            ->setUserName('ERP_root')
            ->setPassword('#Lh{a@I~VQ6I')
            ->dumpToFile($main_file_name);
			$this->sendEmail('dev@lastingerp.com', 'azuka_erp_new');
			mysqli_close($resultMainDB);*/
		
      
			// mysqli_close($result);
			// /home/ao6gqm9lzt0t/public_html/new_test
		    // $file_name = 'http://localhost/database_backups_2/'. $this->database . '_' . date('Y_m_d', time()) . '.sql';
		    //PATH FOR ADD CRON JOB//---->      php -f /home/qtq41w98unio/public_html/cron/index.php
				
    }

public function sendEmail($to = '',$db =''){
	$htmlbody = "Hello Please Check Your Database Backup Files";
	//$to = "lsplpkl@gmail.com"; //Recipient Email Address
	$subject = "DataBase Backup"; //Email Subject
	$headers = "From: admin@lastingerp.com\r\nReply-To: admin@lastingerp.com";
	$random_hash = md5(date('r', time()));
	$headers .= "\r\nContent-Type: multipart/mixed; 
	boundary=\"PHP-mixed-".$random_hash."\"";
	// Set your file path here
	$filename = 'database_backups/'. $db . '_' . date('Y_m_d', time()) . '.sql';
	$attachment = chunk_split(base64_encode(file_get_contents($filename)));
	//define the body of the message.
	$message = "--PHP-mixed-$random_hash\r\n"."Content-Type: multipart/alternative; 
	boundary=\"PHP-alt-$random_hash\"\r\n\r\n";
	$message .= "--PHP-alt-$random_hash\r\n"."Content-Type: text/plain; 
	charset=\"iso-8859-1\"\r\n"."Content-Transfer-Encoding: 7bit\r\n\r\n";
	//Insert the html message.
	$message .= $htmlbody;
	$message .="\r\n\r\n--PHP-alt-$random_hash--\r\n\r\n";
	//include attachment
	$message .= "--PHP-mixed-$random_hash\r\n"."Content-Type: application/zip; 
	name=\"".$filename."\"\r\n"."Content-Transfer-Encoding: 
	base64\r\n"."Content-Disposition: attachment\r\n\r\n";
	//$body .= "Content-Type: application/octet-stream; name=\"" . $filename . "\"" . $eol;
	$message .= $attachment;
	$message .= "/r/n--PHP-mixed-$random_hash--";
	//send the email
	$mail = mail( $to, $subject , $message, $headers );
	echo $mail ? "Mail sent" : "Mail failed";
	unlink($filename);
 }
}
$host = 'localhost';
$username = 'ERP_root';
$password = '#Lh{a@I~VQ6I';
$database = 'azuka_erp_new';
$email = 'rachna@lastingerp.com';
(new DatabaseBackup($host, $username, $password, $database, $email));