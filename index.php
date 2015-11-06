<?php

# copyleft rigon, 2015
# additions to use directly the Markdown parser via this single file
# see readme.txt for more information
#
# test with php-cli:
# QUERY_STRING="file=readme.txt" php spmdwe.php
# Source: http://people.w3.org/~dom/archives/2004/07/testing-php-pages-with-query_string/

# Configuration
$file_name = "home";		# file by default
$file_mode = "view";		# "view" (implied default); "edit", "save", "save_edit"

define('SAVE_ENABLED', true);			# set to false to disable saving ("demo mode")
define('REVISION_MARKER', '_rev');		# marker indicating if it is a revision file
define('FILES_PATH', 'files/%s/');		# path where the files are stored. Use %s to be replaced with the filename
define('CSS_START', '<style>');			# start of a CSS section
define('CSS_END', '</style>');			# end of a CSS section
define('DEFAULT_TEXT', "# Creating a new file\n\nThis file does not exist. You can place your own text here.\n\n**HAVE FUN!**");


# Application

# Ensure if the enviroment is correct
if(empty($_GET))
	parse_str(getenv('QUERY_STRING'),$_GET);

if(empty($_REQUEST))
	parse_str(getenv('QUERY_STRING'),$_REQUEST);


# Gets the filename
if(isset($_REQUEST['file'])) {
	$tmpname = urldecode(preg_replace('/\/\\:*?"<>|/', '', $_REQUEST['file']));
	if(!empty($tmpname)) $file_name = $tmpname;
}

# Google specific info for crawling
if($file_name == "google53c7003e2135dc17.html")
	die('google-site-verification: google53c7003e2135dc17.html');
if($file_name == "sitemap.xml") 
	die('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
		<url><loc>http://www.spmdwe.tk/home</loc></url>
		<url><loc>http://www.spmdwe.tk/readme</loc></url>
		<url><loc>http://www.spmdwe.tk/examples</loc></url>
		<url><loc>http://www.spmdwe.tk/markdown_styles</loc></url>
		</urlset>');


# Default path for files
$revision_marker_position = strrpos($file_name, REVISION_MARKER);
$base_file_name = ($revision_marker_position === false ? $file_name : substr($file_name, 0, $revision_marker_position));
$file_path = sprintf(FILES_PATH, $base_file_name);
$file_path_md = "$file_path$file_name.md";


# Gets the mode
if(isset($_REQUEST['mode']))
	$file_mode = $_REQUEST['mode'];


# Inicializations
$message = "";
$file_readonly = true;
$file_css = array();


# Discover the base URL of the application
$baseurlapp = dirname($_SERVER['PHP_SELF']);
if($baseurlapp == '/') $baseurlapp = '';
$baseurl = "$baseurlapp/$file_name";
$message = "Base URL: $baseurl\\n";

$url_files = sprintf($baseurlapp.'/'.FILES_PATH, $base_file_name);


# If the specified file doesn't exist
if(!file_exists($file_path_md)) {
	$message .= "File $file_name not found. Proceeding to edit\\n";
	$file_readonly = false;
}
else {
	# Discover if the file is read only
	$file_readonly = !is_writable($file_path_md);
	if($file_readonly) {
		$file_mode = "view";
		$message .= "File in read only mode\\n";
	}
}

# Application in Demo mode (read-only)
if(!SAVE_ENABLED) {
	$file_mode = "view";
	$file_readonly = true;
	$message .= "Demo mode - files are just read only\\n";
}


# Set file as read-only
if($file_mode == "readonly") {
	$file_mode = "view";
	$file_readonly = true;
	
	foreach(glob("$file_path*") as $file_path_item) {
		$result = chmod($file_path_item, 0444);
		$message .= $file_path_item.' '.($result ? 'successfully' : 'unsuccessfully').' changed to read-only mode\\n';
	}
	$result = chmod($file_path, 0555);
	$message .= $file_path.' '.($result ? 'successfully' : 'unsuccessfully').' changed to read-only mode\\n';
}

# Save the file and view or continue editing
else if(($file_mode == "save" or $file_mode == "save_edit") and !$file_readonly) {
	// Create a new folder for the page
	if(!file_exists($file_path))
		mkdir($file_path);

	// Revision file is only created if previous file exists
	if(file_exists($file_path_md)) {
		// calculate backup filename
		date_default_timezone_set('UTC');
		$time = date('Ymd_His');
		$revision_file_path = $file_path . $base_file_name . REVISION_MARKER . $time . ".md";

		//save backup as a revision file
		$result = rename($file_path_md, $revision_file_path);
		chmod($revision_file_path, 0444);
		$message .= ($result ? "Revision saved to $revision_file_path.\\n" : "Could not create revision from $file_path_md to $revision_file_path!\\n");
	}

	// save new file (textarea form-input-ta) to original filename
	$new_content = $_REQUEST['form-input-ta'];
	if (file_put_contents($file_path_md, $new_content))
		$message .= "Saved new content to $file_name.\\n";
	
	
	if($file_mode == 'save')
		$file_mode = 'view';
	
	if($file_mode == 'save_edit')
		$file_mode = 'edit';
}

# Upload a new file
else if(($file_mode == "upload") and !$file_readonly) {
	$uploadfile = $file_path . basename($_FILES['file']['name']);

	if (move_uploaded_file($_FILES['file']['tmp_name'], $uploadfile)) {
		echo "File is valid, and was successfully uploaded.\n";
	} else {
		echo "Possible file upload attack!\n";
	}

	echo 'Here is some more debugging info:';
	print_r($_FILES);
	
	exit();
}


# Gets the file contents
$message .= "Opening $file_name in $file_mode mode\\n";

// check if the file exists
$file_contents = false;
if(file_exists($file_path_md))
	$file_contents = file_get_contents($file_path_md);

#else die("NOT EXISTS $file_path_md");

if($file_contents == false) {
	$file_contents = DEFAULT_TEXT;
	$file_mode = 'edit';
	$message .= "Cannot open $file_path_md. Proceeding in edit mode...\\n";
}
else {
	$pos = 0;
	do {
		$pos_start = strpos($file_contents, CSS_START, $pos);		# find the next occurrence of CSS_START
		if($pos_start === false) break;								# if there is no more CSS
		$pos_end = strpos($file_contents, CSS_END, $pos_start);		# find the end of CSS block
		if($pos_end !== false) {									# if the end if found, add it to the list
			$file_css[] = substr($file_contents, $pos_start + strlen(CSS_START), $pos_end - $pos_start - strlen(CSS_START));
			$pos = $pos_end + strlen(CSS_END);
		}
		else
			$pos = strlen($file_contents) - 1;
	} while(true);
}


# Base path for history files
$file_revisions_path = $file_path.$base_file_name.REVISION_MARKER.'????????_??????.md';
$list_files_path = "$file_path*";

# List of revision files
$count = 0;
$file_revisions = array();
foreach(glob($file_revisions_path) as $file_revision) {	
	$file_revisions[$count] = substr($file_revision, strlen($file_path), -strlen('.md'));
	$count++;
}

# List of files
$count = 0;
$list_files = array();
foreach(glob($list_files_path) as $list_file) {
	$list_file_name = substr($list_file, strlen($file_path));
	if($list_file_name !== $file_name.".md" and substr($list_file_name, 0, strlen($base_file_name.REVISION_MARKER)) !== $base_file_name.REVISION_MARKER) {	// Exclude main file and revisions
		$list_files[$count] = $list_file_name;
		$count++;
	}
}

function max_upload() {
	// Determines the maximum upload size allowed
	$max_upload = (int)(ini_get('upload_max_filesize'));
	$max_post = (int)(ini_get('post_max_size'));
	$memory_limit = (int)(ini_get('memory_limit'));
	$upload_mb = min($max_upload, $max_post, $memory_limit);

	return $upload_mb;
}

// for unicode output: (http://stackoverflow.com/questions/713293)
header('Content-Type: text/html; charset=utf-8');
// to disable caching and force a reload each time page is read:
// (http://www.codingforums.com/archive/index.php/t-120638.html)
//header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
//header('Cache-Control: no-store, no-cache, must-revalidate');
//header('Cache-Control: post-check=0, pre-check=0', FALSE);
//header('Pragma: no-cache');

include('template.php');

