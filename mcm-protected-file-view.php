<?php
/*
Plugin Name: MCM Protected File View
Plugin URI: http://www.mcmwebsite.com/mcm-protected-file-view.html
Description: Protect uploaded files so they can only be viewed by logged-in users 
Version: 1.2
Author: MCM Web Solutions, LLC
Author URI: http://www.mcmwebsite.com
License: GPL v. 2
*/
       
// TODO - add analytics/tracking/etc.       
       




// TODO - test on multiple PHP versions, e.g. 5.2, 5.3, 5.4, 5.5, 7.1  (main test env is 5.6.29, also tested on 7.0)  tested with php7cc (PHP7 only) and WP Engine's WP plugin tester (on 5.3-7.0)


add_action( 'init', 'mcm_protected_file_view_init' );

$mcmProtectedFileView = new MCM_Protected_File_View();

function mcm_protected_file_view_init() {
   global $mcmProtectedFileView;

   if ( isset($_GET['mcm_protected_file_view']) && isset($_GET['f']) ) {               
     $mcmProtectedFileView->viewFile($_GET['f']);  
   }  
} // end mcm_protected_file_view_init() 
                                        

class MCM_Protected_File_View {        
  
  private static $pluginName = 'MCM Protected File View';
  private static $pluginCode = 'mcm_protected_file_view';
  private static $uploadDirectory = 'mcm_protected_uploads';
       
  function __construct() {                  
    add_action( 'plugins_loaded', array($this, 'myLoaded') );         
  } // end __construct()
    

  function myLoaded() {
    add_action( 'admin_menu', array($this, 'admin_actions') );
  } // end myLoaded()
  
  
  function admin_actions() {
    add_options_page( "", self::$pluginName, 1, self::$pluginCode, array($this, "menu") );
  } // end admin_actions()
  
  
  function menu() {
    if ( !current_user_can('administrator') ) {
      die('No Access');
      return;
    }  
    
    ?>
    <div class="wrap">
    <h2><?php echo self::$pluginName; ?></h2>
    <strong>
            You <em>must</em> use a web server that supports .htaccess files for access control, such as Apache on Linux! 
            <br />
            Your files
             will <em>not</em> be protected otherwise!
            <br />
            You can test that your files are protected by using the wp-content/uploads/ path. <!-- TODO give better example or even a live link here  -->
    </strong>
    <br />
    <?php
    $startPart = '?page='.self::$pluginCode.'&amp;';
    $hiddenPart = '&amp;'.self::$pluginCode.'=Y';
        
    // See if the user has posted us some information
    // If they did, this hidden field will be set to 'Y'
    if ( isset($_REQUEST[self::$pluginCode]) && $_REQUEST[self::$pluginCode] == 'Y' ) {
  
      $op = $_REQUEST['op'];
  
      switch ($op) {
      
        case 'upload':
          $this->uploadFile();
        break;
        
        case 'list_protected_uploads':
          $this->listProtectedUploads();
        break;
        
        case 'get_file_link':
          $this->getFileLink();
        break;
        
        case 'show_upload_form':
          $this->showUploadForm();
        break;  
      }
  
    }
    else {
  ?>
      <ul>
          <li><a href="<?php echo $startPart; ?>op=list_protected_uploads<?php echo $hiddenPart; ?>">List all protected uploads</a></li>
          <li><a href="<?php echo $startPart; ?>op=show_upload_form<?php echo $hiddenPart; ?>">Upload a new protected upload</a></li>
      </ul>    
  <?php  
    }
    
  } // end menu()  
  
  
  function getFileLink($filename) {
  
    $url = get_site_url().
           '/?mcm_protected_file_view=1'.
           '&amp;'.
           'f='.
           $filename;
   
    return $url;        
  
  } // end getFileLink()
  
  
  
  function getAbsoluteUploadPath() {
  
    $upload_dir = wp_upload_dir();
    $protectedDirname = $upload_dir['basedir'].'/'.self::$uploadDirectory;
    if ( !file_exists($protectedDirname) ) {
        wp_mkdir_p($protectedDirname); // create protected upload directory if it doesn't exist
        $this->addHtaccessToProtectedDirectory($protectedDirname);
    } 
    
    return $protectedDirname;
  
  }  // end getAbsoluteUploadPath()
    
  
  function addHtaccessToProtectedDirectory($dirPath) {
  
    $str = "<Files *>\n".
           "Order Deny,Allow\n".
           "Deny from all\n".
           "</Files>";
           
    $filename = $dirPath . '/' . '.htaccess';
    if ( $handle = fopen($filename, 'w') ) { 
      $written = fwrite($handle, $str);
      $closed = fclose($handle);     
      
      if ($written === FALSE) {
        // error writing to file
      ?>
        <h3>Error creating or writing to .htaccess file - you must manually create a .htaccess file to protect your files!</h3>
      <?php
      }  
      
      if ($closed === FALSE) {
        // error on file close
      ?>
        <h3>Error closing .htaccess file - you must manually create a .htaccess file to protect your files!</h3>
      <?php  
      }
      
    }
    else {
      // error opening/creating file
      ?>
        <h3>Error opening or creating .htaccess file - you must manually create a .htaccess file to protect your files!</h3>
      <?php  
    }
  
  } // addHtaccesToProtectedDirectory()
    
 
  function listProtectedUploads() {
  
    $files = array_diff( scandir( $this->getAbsoluteUploadPath() ), array('..', '.', '.htaccess') );
    if ( count($files) ) {
  ?>
    <h3>Click on any filename below to get a protected link to it</h3>
    <ul>
  <?php  
      foreach ($files as $f) {
  ?>
        <li><a href="<?php echo $this->getFileLink($f); ?>"><?php echo $f; ?></a></li>
  <?php  
      }
  ?>
    </ul>
  <?php    
    }
    else {
    ?>
      <h3>No files were found</h3>
    <?php
    }
  
  } // end listProtectedUploads()
  
  
  function showUploadForm() {
  ?>
  <h3>Upload Protected File</h3>
  <form action="" method="post" enctype="multipart/form-data">
  
    <input type="file" name="myFile" />
    
    <input type="hidden" name="<?php echo self::$pluginCode; ?>" value="Y" />
    
    <input type="hidden" name="op" value="upload" />
    
    <input type="submit" name="submit" value="Upload File" />
  </form>
  <?php
  } // end showUploadForm()
  
  
  // TODO - CHECK $mimeTypesAllowed list
  function uploadFile() {          
  
     ?>
     <style type="text/css">
     .mcm-error {
       color: red;
     }
     </style>
     <?php 

     $debug = 0;
     //error_reporting(E_ALL); // DEBUG CODE (TODO - REMOVE)

     $fieldName = 'myFile';
     $IMAGE_DIR = $this->getAbsoluteUploadPath();       
     
     $fileExtsAllowed = array('jpg', 'jpeg', 'gif', 'png', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'pot', 'ppt', 'pptx'); 
     $max_size = wp_max_upload_size(); 
     $mimeTypesAllowed = array("image/x-png", 
                               'image/png',
                               "image/pjpeg", 
                               "image/gif", 
                               "image/jpeg", 
                               'application/pdf', 
                               'application/x-pdf', 
                               'text/pdf', 
                               'application/vnd.pdf',
                               'application/msword',
                               'application/vnd.ms-powerpoint',
                               'application/vnd.ms-excel',
                               'application/vnd.openxmlformats-officedocument.wordprocessingml.document'); 
                             


     $userfile = $_FILES[$fieldName]['name'];
      
     if ( trim($userfile) == '' )
       return '';
      
     $image_to_edit = $userfile;
      
     $uploadfile = $IMAGE_DIR . "/" . $image_to_edit;
     
     if ( strpos($image_to_edit, '..') !== FALSE ) {
	     die(); // avoid directory traversal attacks	    
     }
     
     if ( strpos($image_to_edit, '/') !== FALSE ) {
	     die(); // avoid directory traversal attacks	    
     }
      
      if ($debug)
         print "uploadfile=$uploadfile";


      $tmpFilename = $_FILES[$fieldName]['tmp_name'];                          
      
      $imname = $_FILES[$fieldName]["name"];                                   
      
      $sArr = explode(".", $imname); // get the extension
      $ct = count($sArr) - 1;
      $ext = $sArr[$ct];
      
      $imtype = $_FILES[$fieldName]['type'];
      
      if ($imtype == 'application/octet-stream') { 
        $fileInfo = wp_check_filetype($uploadfile);
        $imtype = $fileInfo['type'];
      }
      
      $fileMimeTypeOk = 0;
      foreach ($mimeTypesAllowed as $mimeType) {
         if ( strcasecmp($imtype, $mimeType) == 0 ) {
            $fileMimeTypeOk = 1;
            break;
         }
      }
      if (!$fileMimeTypeOk) {
         //echo $closeS;
         exit("<div class=\"mcm-error\">Please upload files with the extensions ".implode(',', $fileExtsAllowed)." only (not $imtype)</div>");
      }
      
      $fileExtOk = 0;
      foreach ($fileExtsAllowed as $fileExt) {
         if ( strcasecmp($ext, $fileExt) == 0 ) {  
            $fileExtOk = 1;
            break;  
         }   
      }
      if (!$fileExtOk) {
         //echo $closeS;
         exit("<div class=\"mcm-error\">Please upload images with the extensions ".implode(',', $fileExtsAllowed)." only (not $imname)");
      }
      
      // rejects all .exe, .com, .bat, and .html files, etc.
      if( preg_match("/.exe$|.com$|.bat$|.php$|.asp$|.html$|.htm$|.shtml$|.js$|.shtm$/i", $imname) ) {
        //echo $closeS;
        exit("<div class=\"mcm-error\">You cannot upload this type of file.</div>");
      }
      
      
      // make sure the file is $max_size bytes or smaller
      if ($_FILES[$fieldName]['size'] > $max_size) {
         echo $closeS;
         exit("The file you are trying to upload is too large.");
      }
      
      $success = 0;
      
      
      // copy the file to destination
      if ( is_uploaded_file($tmpFilename) ) {
      
         $ct = 0;
         while ( file_exists($uploadfile) ) {
              $uploadfileBase = str_replace('.'.$ext, '', $uploadfile);
              $uploadfileBase .= $ct; 
              $uploadfile = $uploadfileBase . '.' . $ext; // try to create a unique filename
              $ct++;
         }
      
         if ( copy($tmpFilename, $uploadfile) ) {
      
            $success = 1;
            echo "\n<p><strong>File uploaded successfully</strong>";
         }
         else
            echo "COPY FAILED $tmpFilename, $uploadfile"; // DEBUG CODE
      }
      else {
         switch($_FILES[$fieldName]['error']) {
          case 0: // no error; possible file attack!
            echo "There was a problem with your upload.";
            break;
          case 1: // uploaded file exceeds the upload_max_filesize directive in php.ini
            echo "The file you are trying to upload is too big.";
            break;
          case 2: // uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the html form
            echo "The file you are trying to upload is too big (bigger than MAX_FILE_SIZE).";
            break;
          case 3: // uploaded file was only partially uploaded
            echo "The file you are trying upload was only partially uploaded.";
            break;
          case 4: // no file was uploaded
            echo "No file selected.";//You must select an image for upload.";
            break;
          default: // a default error, just in case!  :)
            echo "There was a problem with your upload.";
            break;
         }
      }

      return $uploadfile;
      
  } // end uploadFile()
  
      
  function checkLoggedin() {
  
    global $current_user;
    get_currentuserinfo();
  
    $loggedIn = false;
    $userID = $current_user->ID; 
    if ( is_user_logged_in() ) {
      if ( is_user_member_of_blog($user_id) ) {   
        $loggedIn = true;
      }
    }
    if (!$loggedIn) {   
      // if not logged in, redirect to login page (or just die()???)
      die();
    }
  
  } // end checkLoggedin()
  
  
  function viewFile($filename) {
        
    $this->checkLoggedin();
    
    
    if ($filename == '') {
      die();
    }
	 
	  if ( strpos($filename, '..') !== FALSE ) {
	    die(); // avoid directory traversal attacks	    
    }
        
    
    $docRoot = $this->getAbsoluteUploadPath();
          
	  $filename = $docRoot.'/'.$filename;
	  	 
    $fileExt = $this->getFileExt($filename);
        
    
    $contentType = $this->getMIMEType($fileExt);
    
    
    return $this->sendFile($filename, $contentType);
        
  } // end viewFile()
  
  
  function getFileExt($filename) {
  
    $fileArr = explode('/', $filename);
    $fileArrSize = count($fileArr);
    $file = $fileArr[$fileArrSize-1];

    $fileExtArr = explode('.', $file);
    $fileExt = $fileExtArr[count($fileExtArr)-1];
    
    return $fileExt;
  
  } // end getFileExt()
  
  
  // TODO - UPDATE LIST OF MIME TYPES
  function getMIMEType($fileExt) {
  
    $contentType = '';
  
    switch ($fileExt) {
   
     case 'jpg':
     case 'jpeg':
     case 'JPG':
     case 'JPEG':
       $contentType = 'image/jpeg';
     break;
     
     case 'pdf':
     case 'PDF':
       $contentType = 'application/pdf';
     break;
           
     case 'gif':
     case 'GIF':
        $contentType = 'image/gif';
     break;
     
     case 'png':
     case 'PNG':
        $contentType = 'image/png';
     break;
   
     // ms office types
     case 'doc':
     case 'docx':
        $contentType = 'application/msword';
     break;
     
     case 'pot':
     case 'ppt':
     case 'pptx':
        $contentType = 'application/vnd.ms-powerpoint';
     break;
     
     case 'xls':
     case 'xlsx':
        $contentType = 'application/vnd.ms-excel';
     break;   
   
     default:
        $contentType = 'application/download';
        
    }   
    
    return $contentType;
  
  } // end getMIMEType()
  
  
  function sendFile($filename, $contentType) {
  
    // required for IE
	 if(ini_get('zlib.output_compression')) { ini_set('zlib.output_compression', 'Off');	}
   header("Pragma: public");
   header("Expires: 0");
   header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
   header("Cache-Control: private",false);
   header("Content-Type: $contentType");   
   //header("Content-Disposition: attachment; filename=\"".$orgFilename."\";" );
   header('Content-Transfer-Encoding: binary');
   header("Content-Length: " . filesize($filename));
   //header('Connection: close');     
   readfile($filename);
   exit(); 
  
  } // end sendFile()
  
  
} // end class
?>