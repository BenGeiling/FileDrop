<?php
#ini_set('upload_max_filesize', '16M');  
#ini_set('post_max_size', '16M');  
#ini_set('max_input_time', 7200);  
#ini_set('max_execution_time', 3600);
#ini_set('memory_limit', 2048); //limiting factor.


//User vars
$fileDir = "/home/ben/public_html/daena.ca/public/files/uploads/";
//$newName = true; //true: if file names are same, modify new file name (ie add a number). false: overwrites existing file.
//$hashCheck = true; //compute hash of all files
$smartCheck = true;
$logActivity = true;
$logfile = "uploads.log";

// e.g. url:"page.php?upload=true" as handler property
//if(isset($_GET['upload']) && $_GET['upload'] === 'true'){
	$headers = getallheaders();
	if(1==1
		// basic checks
		#isset(
			#$headers['Content-Type']
			#$headers['Content-Length']
			#$headers['X-File-Size']
			#$headers['X-File-Name']
		#) #&&
		#$headers['Content-Type'] === 'multipart/form-data-'
		#$headers['Content-Length'] === $headers['X-File-Size']
	){

		// create the object and assign property
		$file = new stdClass;
		$file->name = basename($headers['X-File-Name']);
		//$file->name = "TEST";		
		$file->size = $headers['X-File-Size'];
		$file->content = file_get_contents("php://input");
		$file->hash = md5($file->content); //faster hashing method: = split('=',exec("openssl md5 $file_path"));
		$file->clientpath = $headers['clientpath'];
		$file->serverpath = $fileDir.$file->name;

		
		$i=1;
		$newpath = $file->serverpath;

		if($smartCheck)
		{
			while(file_exists($newpath))
			{
				if(md5_file($newpath) === $file->hash) //if same hashes
					break;
				else
					$newpath = $file->serverpath . " ($i)";

				$i++;
			}
		}

		// if everything is ok, save the file somewhere
		if(file_put_contents($newpath, $file->content))

		//write a log entry
		if($logActivity)
		{
			$remoteIP = $_SERVER['REMOTE_ADDR'];
			$remotePort = $_SERVER['REMOTE_PORT'];
			$remoteHost = htmlentities(gethostbyaddr($remoteIP));
			$unixTimestamp = date("U");
			//$remoteRefer = htmlentities($_SERVER['HTTP_REFERER']);
			$remoteAgent = htmlentities($_SERVER['HTTP_USER_AGENT']);

			$fh = fopen($logfile, "a+");
			if(file_exists($logfile)){
				fputs($fh, "$remoteIP\t$remotePort\t$remoteHost\t$unixTimestamp\t$file->hash\t$file->size\t$file->name\t$file->clientpath\t$newpath\t$remoteAgent\n");
				fclose($fh);
			}
		}

		exit('OK');
	}
	// if there is an error this will be the output instead of "OK"
	exit('Error');
//}

?>
