<?php
/**


$file_name			- filename of the current file
$file_revisions		- list of revisions to the file
$file_readonly		- flag indicating if the file is read only
$file_mode			- mode of the file, view or edit
$file_contents		- contents of the file

*/
?><!DOCTYPE html>
<html>
	<head>
		<title><?php echo $file_name; ?> - Spmdwe Editor</title>
		
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
					<a class="navbar-brand" href="<?php echo $baseurl; ?>"><?php echo $file_name; ?></a>
				</div>
				<div id="navbar" class="navbar-collapse collapse">
					<ul class="nav navbar-nav">
						<li <?php if($file_name=="home") echo 'class="active"'; ?>><a href="<?php echo $baseurlapp.'/home'; ?>">Home</a></li>
						<li <?php if($file_name=="readme") echo 'class="active"'; ?>><a href="<?php echo $baseurlapp.'/readme'; ?>">Readme</a></li>
						<li <?php if($file_name=="examples") echo 'class="active"'; ?>><a href="<?php echo $baseurlapp.'/examples'; ?>">Examples</a></li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">History <span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu"><?php
									if(count($file_revisions) < 1)
										echo '<li class="dropdown-header">This file has no history</li>';
									else
										foreach($file_revisions as $rev)
											echo '<li><a href="'.$rev.'">'.$rev.'</a></li>';
								?>
								
								<li class="divider"></li>
								<li><a href="<?php echo "$baseurlapp/$base_file_name"; ?>">Original File</a></li>
							</ul>
						</li>
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Options <span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu">
								<li><a id="download_html" href="#">Download as HTML</a></li>
								<li><a id="download_markdown" href="#">Download as Markdown</a></li>
								<li class="divider"></li>
								<li><?php echo ($file_readonly ?
									'<li class="disabled"><a href="#" disabled>This file is already read-only</a></li>' :
									"<a href=\"$baseurl?mode=readonly\">Make this file read-only</a></li>"); ?>
								<li><a id="view_log" href="#">View Log</a></li>
							</ul>
						</li>
					</ul>
					<ul class="nav navbar-nav navbar-right">
						<li <?php if($file_mode=="view") echo 'class="active"'; ?>><a href="<?php echo $baseurl; ?>">View</a></li>
						<li class="<?php echo ($file_readonly ? 'disabled ' : '').($file_mode=='edit' ? 'active' : ''); ?>">
							<a href="<?php if(!$file_readonly) echo "$baseurl?mode=edit"; ?>">Edit</a>
						</li>
					</ul>
					<form class="navbar-form navbar-right">
						<input type="text" class="form-control" placeholder="Filename to open..." name="file" value="<?php echo $file_name; ?>">
					</form>
				</div><!--/.nav-collapse -->
			</div>
		</nav>
		
		<!-- Container -->
		<div class="container">
			<div class="row">
				<!-- Markdown -->
				<div id="wmd-preview-editor" class="viewer col-md-<?php echo ($file_mode=="edit" ? "6" : "12"); ?>"></div>

				<!-- Editor -->
				<?php if ($file_mode=="edit") { ?>
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
			var mode = "<?php echo $file_mode; ?>";
			var markdown = <?php echo json_encode($file_contents); ?>;
			
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
				$(this).attr("download", "<?php echo $file_name; ?>.html");
				$(this).click();
			});
			$("#download_markdown").click(function() {
				$(this).attr("href", "data:text/plain;charset=utf-8," + encodeURIComponent( mode=="edit" ? $("#wmd-input-editor").val() : markdown));
				$(this).attr("download", "<?php echo $file_name; ?>.md");
				$(this).click();
			});
		
			$("#view_log").click(function() {
				alert('<?php echo $message; ?>');
			});
		</script>
	</body>
</html>

