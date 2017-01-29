// #-------------------------------------------------------------------------#

// # Project:     Herkules_NLP - Extract Attachments from email
// # Author:      Clara Marquardt
// # Date:        Nov 2016

// #----------------------------------------------------------------------------#

// #----------------------------------------------------------------------------#
// #                                    Code                                    #
// #----------------------------------------------------------------------------# */

<?php

date_default_timezone_set('EST');

/* connect to gmail */
$hostname = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = getenv("email_address");
$password = getenv("email_pwd");

$date = getenv("email_date");

/* directory and file path settings */echo

/* try to connect */
$inbox = imap_open($hostname,$username,$password) or die('Cannot connect to Gmail: ' . imap_last_error());

/* extract emails */
// $emails = imap_search($inbox,'UNSEEN SUBJECT "herkules_order_update"' );
$emails = imap_search($inbox,'SUBJECT "herkules_order_update"' );
echo "number of emails: " . count($emails);
echo "\n\n";

/* if emails are returned, cycle through each email */
if($emails) {
	
	/* begin output var */
	$output = '';
	
	/* put the newest emails on top */
	rsort($emails);
	
	/* for every email... */
	foreach($emails as $email_number) {

        echo "\n\n";
		echo "email #: " . $email_number;
        echo "\n\n";


		/* get information specific to this email */
		$message = imap_fetchbody($inbox,$email_number,1);

		// #----------------------------------------------#
		// START script to extract and download attachments
		// #----------------------------------------------#

        /* get information specific to this email */
        $overview = imap_fetch_overview($inbox,$email_number,0);

        /* get mail structure */
        $structure = imap_fetchstructure($inbox, $email_number);

        $attachments = array();

        /* if any attachments found... */
        if(isset($structure->parts) && count($structure->parts)) 


        {
            for($i = 0; $i < count($structure->parts); $i++) 
            {
                $attachments[$i] = array(
                    'is_attachment' => false,
                    'filename' => '',
                    'name' => '',
                    'attachment' => ''
                );

                if($structure->parts[$i]->ifdparameters) 
                {
                    foreach($structure->parts[$i]->dparameters as $object) 
                    {
                        if(strtolower($object->attribute) == 'filename') 
                        {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['filename'] = $object->value;
                        }
                    }
                }

                if($structure->parts[$i]->ifparameters) 
                {
                    foreach($structure->parts[$i]->parameters as $object) 
                    {
                        if(strtolower($object->attribute) == 'name') 
                        {
                            $attachments[$i]['is_attachment'] = true;
                            $attachments[$i]['name'] = $object->value;
                        }
                    }
                }

                if($attachments[$i]['is_attachment']) 
                {
                    $attachments[$i]['attachment'] = imap_fetchbody($inbox, $email_number, $i+1);

                    /* 3 = BASE64 encoding */
                    if($structure->parts[$i]->encoding == 3) 
                    { 
                        $attachments[$i]['attachment'] = base64_decode($attachments[$i]['attachment']);
                    }
                    /* 4 = QUOTED-PRINTABLE encoding */
                    elseif($structure->parts[$i]->encoding == 4) 
                    { 
                        $attachments[$i]['attachment'] = quoted_printable_decode($attachments[$i]['attachment']);
                    }
                }
            }



        }

        echo "number of attachments: ".count($attachments);
        echo "\n\n";

        /* iterate through each attachment and save it */
        $folder = getenv("raw_order_path");
        if(!is_dir($folder)) {
            mkdir($folder);
		}

        for($j = 0; $j < count($attachments); $j++) {

        	$filename = "raw_order" . "_" . $date . "_" . $email_number . "_" . $j . "." . "pdf";
            // echo $folder;
            // echo $filename;
        	echo "attachment #: " . $j;
            echo "\n\n";

            if($attachments[$j]['is_attachment'] == 1)
            {

                $filepath = $folder ."/". $filename;
                // echo ($filepath);

                $fp = fopen($filepath,"w+");
                fwrite($fp, $attachments[$j]['attachment']);
                fclose($fp);
            }
        }

        // #----------------------------------------------#
		// END script to extract and download attachments
		// #----------------------------------------------#

    }
}


/* close the connection */
imap_close($inbox);

/* parse all attachments - shell script */
$shell_file =  'order_extract.sh';
$shell_root_path = getenv("extract_code_path_exec");

$shell_path = "$shell_root_path" . "/" .  "$shell_file";
echo $shell_path;
shell_exec("sh $shell_path");


 ?>

// #----------------------------------------------------------------------------#
// #                                    End                                     #
// #----------------------------------------------------------------------------# */
