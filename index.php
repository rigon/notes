<?php

# spmdwe.php
# copyleft sdaau, 2012
# additions to use directly the Markdown parser via this single file
# callable online with: spmdwe.php?file=readme.txt
# see readme.txt for more information
#
# test with php-cli:
# QUERY_STRING="file=readme.txt" php spmdwe.php

// errors:
//~ ini_set('display_errors', '1');


// ## MAIN ## /////////

#include_once "./php-markdown/markdown.php";

//http://people.w3.org/~dom/archives/2004/07/testing-php-pages-with-query_string/
if (empty($_GET)) {
	//~ parse_str($_ENV['QUERY_STRING'],$_GET); //nowork
	parse_str(getenv('QUERY_STRING'),$_GET); //OK
}
if (empty($_REQUEST)) {
	parse_str(getenv('QUERY_STRING'),$_REQUEST);
}

//~ echo print_r($_GET);

$fname="home";		# file by default
$fmode="view";		# "view" (implied default); "edit", "save", "save_edit"

$saveenabled = true;	# set to false to disable saving ("demo mode")

$revisonmarker = '_rev';

# Gets the file
if(isset($_REQUEST['file'])) {
	$tmpname=urldecode(preg_replace('/\/\\:*?"<>|/', '', $_REQUEST['file']));
	if(!empty($tmpname)) $fname = $tmpname;
}
#Gets the mode
if(isset($_REQUEST['mode']))
	$fmode=$_REQUEST['mode'];
	

# Default path for files
$fpath = "./files/$fname.md";	# Change this accordingly

$baseurlapp = dirname($_SERVER['PHP_SELF']);
$baseurl = "$baseurlapp/$fname";
$message = "Base URL: $baseurl\\n";

# Applicatoin in Demo mode (read-only)
if(!$saveenabled) {
	$fmode="view";
	$message .= "Demo mode - files are just read only";
}

# Set file as read-only
if($fmode=="readonly") {
	$r=chmod($fpath, 0444);
	$message .= ($r ? 'Successfully' : 'Unsuccessfully').' changed file to read-only mode\\n';
	$fmode="view";
}

if($fmode=="save" or $fmode=="save_edit") {
	// calculate backup filename
	$posdot = strrpos($fpath, '.');
	$nfilename = substr($fpath, 0, $posdot);
	$nextension = substr($fpath, $posdot + 1);
	
	$time = time();
	$nfpath = "$nfilename$revisonmarker$time.$nextension";

	//save backup
	$r = rename($fpath, $nfpath);
	$message .= $r ? "Saved old backup to $nfpath.\\n" : "Could not create a backup from $fpath to $nfpath!\\n";
	$r = chmod($nfpath, 0444);
	$message .= "Setting backup as read-only: $r\\n";

	// save (new) contents of textarea to original filename
	$new_content = $_REQUEST['form-input-ta'];
	if (file_put_contents($fpath, $new_content))
		$message .= "Saved new content to $fname.\\n"; //

	$message .= "Performed save!\\n";
	
	if($fmode=="save")
		$fmode="view";
	if($fmode=="save_edit")
		$fmode="edit";
}

$freadonly = !is_writable($fpath);
# If the specified file doesn't exist
if(!file_exists($fpath)) {
	$message .= "Cannot find $fname!\\n";
	$message .= "Proceeding to edit $fname... \\n";
	$message .= "Restoring edit mode.\\n";
	$fmode="edit";
	$freadonly = false;
	if(!$saveenabled){
		$fmode="view";
		$message .= "Demo mode - files are just read only\\n";
	}
}

# Gets the file contents
$ftext = file_get_contents($fpath);
$message .= "Opening $fname in $fmode mode\\n";
if($ftext==false) {
	$ftext="# Creating a new file\n\nThis file does not exist. You can place your own text here.\n\n**HAVE FUN!**";
	$fmode='edit';
	$message .= "Cannot open $fname. Proceeding in edit mode...\\n";
}

# Base path for history files
$markpos = strrpos($fname, '_rev');
$basefname = ($markpos === false ? $fname : substr($fname, 0, $markpos));
$fpath_history = dirname($fpath)."/$basefname\_rev??????????.md";


// for unicode output: (http://stackoverflow.com/questions/713293)
header('Content-Type: text/html; charset=utf-8');
// to disable caching and force a reload each time page is read:
// (http://www.codingforums.com/archive/index.php/t-120638.html)
//header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
//header('Cache-Control: no-store, no-cache, must-revalidate');
//header('Cache-Control: post-check=0, pre-check=0', FALSE);
//header('Pragma: no-cache');

?><!DOCTYPE html>
<html>
	<head>
		<title><?php echo $fname; ?> - Spmdwe Editor</title>
		
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		
		<link rel="stylesheet" type="text/css" href="static/css/pagedown.css" />
		<link rel="stylesheet" type="text/css" href="static/css/bootstrap.min.css" />
		
		<script type="text/javascript" src="static/js/Markdown.Converter.js"></script>
		<script type="text/javascript" src="static/js/Markdown.Sanitizer.js"></script>
		<script type="text/javascript" src="static/js/Markdown.Editor.js"></script>
		<script type="text/javascript" src="static/js/Markdown.Extra.js"></script>
		<script type="text/javascript" src="static/js/MathJax-edit.js"></script>
		<!-- <script type="text/javascript" src="static/js/call-mathjax-edit.js"></script> -->

		
		<script src="static/js/jquery-1.11.1.min.js"></script>
		<script src="static/js/bootstrap.min.js"></script>

		<style type="text/css">
			body {
				padding-top: 70px;
				padding-bottom: 30px;
			}
			.navbar-brand {
				min-width: 200px;
				background-color: #f3f3f3;
			}
			.viewer {
				padding-right: 0;
			}
			.editor {
				padding-right: 0;
			}
			.editor textarea {
				font-family: monospace;
				font-size: 13px;
				color: black;
				min-width: 100%;
				max-width: 100%;
				height: 70vh;
			}
		</style>
	</head>
	<body>
		<noscript>
			JavaScript not detected; JavaScript is required for editing parts of the application.
		</noscript>
		
		<!-- Fixed navbar -->
		<nav class="navbar navbar-default navbar-fixed-top" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
						<span class="sr-only">Toggle navigation</span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
						<span class="icon-bar"></span>
					</button>
					<a class="navbar-brand" href="<?php echo $baseurl; ?>"><?php echo $fname; ?></a>
				</div>
				<div id="navbar" class="navbar-collapse collapse">
					<ul class="nav navbar-nav">
						<li <?php if($fname=="home") echo 'class="active"'; ?>><a href="<?php echo $baseurlapp; ?>">Home</a></li>
						<li <?php if($fname=="readme") echo 'class="active"'; ?>><a href="<?php echo $baseurlapp.'/readme'; ?>">Readme</a></li>
						<li <?php if($fname=="examples") echo 'class="active"'; ?>><a href="<?php echo $baseurlapp.'/examples'; ?>">Examples</a></li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">History <span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu"><?php	
								$count = 0;
								foreach(glob($fpath_history) as $f) {
									$pos = strrpos($f, $revisonmarker) + strlen($revisonmarker);
									$n = substr($f, $pos, strrpos($f, '.') - $pos);
									echo "<li><a href=\"$baseurlapp/$basefname$revisonmarker$n\">rev$n</a></li>";
									$count++;
								}
								if($count == 0)
									echo '<li class="dropdown-header">This file has no history</li>';
								?>
								
								<li class="divider"></li>
								<li><a href="<?php echo "$baseurlapp/$basefname"; ?>">Original File</a></li>
								<!--
								<li class="divider"></li>
								<li class="dropdown-header">Nav header</li>
								<li><a href="#">Separated link</a></li> -->
							</ul>
						</li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Options <span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu">
								<li><a id="download_html" href="#">Download as HTML</a></li>
								<li><a id="download_markdown" href="#">Download as Markdown</a></li>
								<li class="divider"></li>
								<li><?php echo ($freadonly ?
									'<li class="disabled"><a href="#" disabled>This file is already read-only</a></li>' :
									"<a href=\"$baseurl?mode=readonly\">Make this file read-only</a></li>"); ?>
								<li><a id="view_log" href="#">View Log</a></li>
							</ul>
						</li>
					</ul>
					<ul class="nav navbar-nav navbar-right">
						<li <?php if($fmode=="view") echo 'class="active"'; ?>><a href="<?php echo $baseurl; ?>">View</a></li>
						<li class="<?php echo ($freadonly ? 'disabled ' : '').($fmode=='edit' ? 'active' : ''); ?>">
							<a href="<?php if(!$freadonly) echo "$baseurl?mode=edit"; ?>">Edit</a>
						</li>
					</ul>
					<form class="navbar-form navbar-right">
						<input type="text" class="form-control" placeholder="Filename to open..." name="file" value="<?php echo $fname; ?>">
					</form>
				</div><!--/.nav-collapse -->
			</div>
		</nav>
		
		<!-- Container -->
		<div class="container">
			<div class="row">
				<!-- Markdown -->
				<div id="wmd-preview-editor" class="viewer col-md-<?php echo ($fmode=="edit" ? "6" : "12"); ?>"></div>

				<!-- Editor -->
				<?php if ($fmode=="edit") { ?>
					<div class="editor col-md-6">
						<form method="post" action="<?php echo $baseurl; ?>" role="form">
							<div class="form-group">
								<div id="wmd-button-bar-editor"></div>
								<textarea id="wmd-input-editor" class="wmd-input" name="form-input-ta"></textarea>
							</div>
							
							<div id="form-group" class="text-right">
								<button type="submit" class="btn btn-primary" name="mode" value="save_edit">Save</button>
								<button type="submit" class="btn" name="mode" value="save">Save and View</button>
							</div>
						</form>
					</div>
					<script type="text/javascript">

					</script><?php
				} ?>
				<!-- END Editor -->
			</div>
		</div>
		<script type="text/javascript">
			var mode = "<?php echo $fmode; ?>";
			var markdown = <?php echo json_encode($ftext); ?>;
			
			window.onload = function() {
				var converter = new Markdown.Converter();
				
				Markdown.Extra.init(converter, {
					extensions: ["fenced_code_gfm", "tables", "def_list", "attr_list", "footnotes", "smartypants", "newlines", "strikethrough"],
					table_class: "table table-striped",
				});
				
				// Fills the viewer with HTML
				$("#wmd-preview-editor").html(converter.makeHtml(markdown));
				
				if(mode == 'edit') {
					// Sets textarea with the text
					$("#wmd-input-editor").val(markdown);
					
					// Creates the editor
					var editor = new Markdown.Editor(converter, "-editor", {
						handler: function() {
							alert("Do you need help? Try http://stackoverflow.com/editing-help");
						}
					});
					editor.run();
				}
			}
		
			$("#download_html").click(function() {
				$(this).attr("href", "data:text/plain;charset=utf-8," + encodeURIComponent($("#wmd-preview-editor").html()));
				$(this).attr("download", "<?php echo $fname; ?>.html");
				$(this).click();
			});
			$("#download_markdown").click(function() {
				$(this).attr("href", "data:text/plain;charset=utf-8," + encodeURIComponent( mode=="edit" ? $("#wmd-input-editor").val() : markdown));
				$(this).attr("download", "<?php echo $fname; ?>.md");
				$(this).click();
			});
		
			$("#view_log").click(function() {
				alert('<?php echo $message; ?>');
			});
		</script>
	</body>
</html>

