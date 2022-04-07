<?php

# copyleft rigon, 2015
# additions to use directly the Markdown parser via this single file
# see readme.txt for more information
#
# test with php-cli:
# QUERY_STRING="file=readme.txt" php spmdwe.php
# Source: http://people.w3.org/~dom/archives/2004/07/testing-php-pages-with-query_string/

# Configuration
$file_name = define_env("HOMEPAGE","home");	# file by default
$file_mode = "view";						# "view" (default), "edit", "save", "save_edit", "upload", "template_save", "publish", "published"

define_env('SITE_NAME', 'Spmdwe Editor');	# Website name
define_env('SAVE_ENABLED', true);			# set to false to disable saving ("demo mode")
define_env('REQUIRE_AUTH', false);			# require authentication for editing

define('AUTH_FILE', 'htpasswd');			# file containing credentials for authentication, htpasswd compatible
define('REVISION_MARKER', '_rev');			# marker indicating if it is a revision file
define('FILES_PATH', 'files/%s/');			# path where the files are stored. Use %s to be replaced with the filename
define('CSS_START', '<style>');				# start of a CSS section
define('CSS_END', '</style>');				# end of a CSS section
define('TEMPLATE_EDIT', 'template.php');	# template used for editing
define('TEMPLATE_PUBLISH', 'files/publish_template.php');	# template used for publishing
define('DEFAULT_TEXT', "# Creating a new file\n\nThis file does not exist. You can place your own text here.\n\n**HAVE FUN!**");


# Application

function define_env($name, $default) {
	$value = isset($_ENV[$name]) ? $_ENV[$name] : $default;
	define($name, $value);
	return $value;
}

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

# Specific info for crawling
if($file_name == "sitemap.xml") 
	die('<?xml version="1.0" encoding="UTF-8"?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
		<url><loc>http://www.spmdwe.tk/home</loc></url>
		<url><loc>http://www.spmdwe.tk/readme</loc></url>
		<url><loc>http://www.spmdwe.tk/examples</loc></url>
		<url><loc>http://www.spmdwe.tk/markdown_styles</loc></url>
		</urlset>');


# Inicializations
$message = "";
$file_readonly = true;
$file_css = array();
$user = "";


# Authentication
$authenticated = !REQUIRE_AUTH;		// Automatically authenticated if authentication is not required
if(REQUIRE_AUTH) {
	session_start();
	
	## Check if authentication is provided
	if(isset($_SERVER['PHP_AUTH_USER']) and isset($_SERVER['PHP_AUTH_PW'])) {

		## Check user credentials using environment variables (overrides htpasswd)
		if(isset($_ENV['USER']) and isset($_ENV['PASS'])) {
			$user = $_SERVER['PHP_AUTH_USER'];
			$pass = $_SERVER['PHP_AUTH_PW'];
			if($user == $_ENV['USER'] and $pass == $_ENV['PASS'])
				$authenticated = true;
		}

		## Check user credentials using htpasswd
		elseif(file_exists(AUTH_FILE)) {
			$user = escapeshellarg($_SERVER['PHP_AUTH_USER']);
			$pass = escapeshellarg($_SERVER['PHP_AUTH_PW']);
			
			exec("htpasswd -vb ".AUTH_FILE." $user $pass 2>&1", $output, $returnval);
			$message .= implode('\n', $output).'\n';
			
			// Start session if valid
			if($returnval == 0 and isset($_GET['login']))
				$_SESSION['session_started'] = true;
			
			// Authenticate user if valid
			if($returnval == 0 and isset($_SESSION['session_started']) and $_SESSION['session_started'] == true)
				$authenticated = true;
		}

		// Cleanup
		$user = $_SERVER['PHP_AUTH_USER'];
		unset($pass);
	}

	## Not authenticated
	if(!$authenticated) {
		$message .= 'Authentication failed!\n';
	}
	
	## Login
	if((isset($_GET['login']) and !$authenticated)) {
		// Force the browser to prompt for a username and password
		header('WWW-Authenticate: Basic realm="Enter your credencials to login"');
		header('HTTP/1.0 401 Unauthorized');
		$authenticated = false;
	}
	
	## Logout
	if(isset($_GET['logout']) and $authenticated) {
		unset($_SESSION['session_started']);
		$authenticated = false;
		http_response_code(401);
	}
	//	$redirect_url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER['HTTP_HOST'].substr($_SERVER['REQUEST_URI'], 0, strrpos($_SERVER['REQUEST_URI'],'?'));
	//	header("Location: $redirect_url");
}


# Default path for files
$revision_marker_position = strrpos($file_name, REVISION_MARKER);
$base_file_name = ($revision_marker_position === false ? $file_name : substr($file_name, 0, $revision_marker_position));
$file_path = sprintf(FILES_PATH, $base_file_name);
$file_path_md = "$file_path$file_name.md";
$publish_file = "$file_path$file_name.html";


# Gets the mode
if(isset($_REQUEST['mode']))
	$file_mode = $_REQUEST['mode'];

# Discover the base URL of the application
$baseurlapp = dirname($_SERVER['PHP_SELF']);
if($baseurlapp == '/') $baseurlapp = '';
$baseurl = "$baseurlapp/$file_name";
$message .= "Base URL: $baseurl\\n";

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
		if($file_mode == "publish") die("Readonly, publishing not allowed.");
		
		$file_mode = "view";
		$message .= "File in read only mode\\n";
	}
}

# Application in Demo mode (read-only)
if(!SAVE_ENABLED or !$authenticated) {
	if($file_mode == "template_save")
		die("Could not save template because authentication is invalid.");
	if($file_mode == "upload")
		die("Could not upload files because authentication is invalid.");
	
	$file_mode = "view";
	$file_readonly = true;
	$message .= "Demo mode - files are just read only\\n";
}

# Published mode
if($file_mode == "preview" or ($file_mode == "view" and !$authenticated and REQUIRE_AUTH)) {
	$file_mode = "published";
	$message .= 'Proceeding in published mode.\n';
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
else if($file_mode == "upload" and !$file_readonly and REQUIRE_AUTH) {
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

# Save template
else if($file_mode == "template_save" and REQUIRE_AUTH) {
	$template = $_REQUEST['template'];
	$result = file_put_contents(TEMPLATE_PUBLISH, $template);
	
	if($result === FALSE)
		die('Error saving the template...');
	
	exit('Template saved!');
}

# Publish file
else if($file_mode == "publish") {
	$publish_html = $_REQUEST['html'];
	
	$result = file_put_contents($publish_file, $publish_html);
	
	if($result === FALSE)
		die('Error publishing file...');
	
	exit('File published!');
}

# Download ZIP file with all files of the page
else if($file_mode == "downloadzip") {
	header("Content-Type: application/x-zip");
	header("Content-Disposition: attachment; filename=\"$file_name.zip\"");

	$stream = popen("zip -qj - files/$file_name/*", "r");
	if($stream) {
		fpassthru($stream);
		fclose($stream);
	}
	exit();
}


if($authenticated) {
	# Gets the file contents
	$message .= "Opening $file_name in $file_mode mode\\n";

	// check if the file exists
	$file_contents = false;
	if(file_exists($file_path_md))
		$file_contents = file_get_contents($file_path_md);


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
}

# if the file was published
if($file_mode == "published") {
	$html = false;
	if(file_exists($publish_file))
		$html = file_get_contents($publish_file);
	
	if($html == false) {
		http_response_code(404);
		$html = <<<TEMPLATE404
			<div class="text-center">
				<h1 class="error">404</h1>
				<p class="lead text-gray-800 mb-5">Page Not Found</p>
				<p class="text-gray-500 mb-0">It looks like you found a glitch in the matrix...</p>
				<a href="/">&larr; Back to home</a>
			</div>
			TEMPLATE404;
	}
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
$file_revisions = array_reverse($file_revisions);

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

// Determines the maximum upload size allowed
$max_upload = min(
	(int)(ini_get('upload_max_filesize')),
	(int)(ini_get('post_max_size')),
	(int)(ini_get('memory_limit')));

# Get template file
$template_file = htmlspecialchars(file_get_contents(
		file_exists(TEMPLATE_PUBLISH) ? TEMPLATE_PUBLISH : TEMPLATE_EDIT));

// for unicode output: (http://stackoverflow.com/questions/713293)
header('Content-Type: text/html; charset=utf-8');
// to disable caching and force a reload each time page is read:
// (http://www.codingforums.com/archive/index.php/t-120638.html)
//header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
//header('Cache-Control: no-store, no-cache, must-revalidate');
//header('Cache-Control: post-check=0, pre-check=0', FALSE);
//header('Pragma: no-cache');

# Preview with the template provided
if($file_mode == "published" and isset($_REQUEST['template']) and REQUIRE_AUTH)
	eval('?>'.$_REQUEST['template'].'<?php ');

# Preview with the saved template
elseif($file_mode == "published" and file_exists(TEMPLATE_PUBLISH))
	include(TEMPLATE_PUBLISH);

# Use the edit template
else
	include(TEMPLATE_EDIT);
