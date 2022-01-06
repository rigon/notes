<?php
/**
 * GLOBAL VARIABLES
 * ================
 * SITE_NAME		- name of the website
 * $base_file_name	- name of the file, all revision files have the same base filename
 * $file_name		- filename of the current file
 * $file_revisions	- list of revisions to the file
 * $file_readonly	- flag indicating if the file is read only
 * $file_mode		- mode of the file (view, edit or published)
 * $file_contents	- contents of the file
 * $file_css		- list of CSS snippets
 * $list_files		- list of attached files
 * $url_files		- URL to the files
 * $baseurlapp		- base URL for the application, i. e. URL without the filename
 * $baseurl			- URL with the filename
 * $html			- HTML of the published version of the file
 * $max_upload		- Maximum size for uploading data
 */
?><!DOCTYPE html>
<html>
	<head>
		<title><?php echo $file_name; ?> - <?php echo SITE_NAME; ?></title>
		
		<meta name="viewport" content="width=device-width, initial-scale=1.0" />
		
		<link rel="stylesheet" type="text/css" href="<?php echo $baseurlapp; ?>/static/css/pagedown.css" />
		<link rel="stylesheet" type="text/css" href="<?php echo $baseurlapp; ?>/static/css/bootstrap.min.css" />
		
		<script type="text/javascript" src="<?php echo $baseurlapp; ?>/static/js/Markdown.Converter.js"></script>
		<script type="text/javascript" src="<?php echo $baseurlapp; ?>/static/js/Markdown.Sanitizer.js"></script>
		<script type="text/javascript" src="<?php echo $baseurlapp; ?>/static/js/Markdown.Editor.js"></script>
		<script type="text/javascript" src="<?php echo $baseurlapp; ?>/static/js/Markdown.Extra.js"></script>
		<script type="text/javascript" src="<?php echo $baseurlapp; ?>/static/js/MathJax-edit.js"></script>
		<!-- <script type="text/javascript" src="static/js/call-mathjax-edit.js"></script> -->

		
		<script src="<?php echo $baseurlapp; ?>/static/js/jquery-1.11.1.min.js"></script>
		<script src="<?php echo $baseurlapp; ?>/static/js/bootstrap.min.js"></script>
		<script src="<?php echo $baseurlapp; ?>/static/js/dropzone.js"></script>

		<script src="<?php echo $baseurlapp; ?>/static/js/ace-editor/ace.js" type="text/javascript" charset="utf-8"></script>

		<style type="text/css">
			body {
				padding-top: 70px;
				padding-bottom: 30px;
			}
			.navbar-brand {
				font-variant: small-caps;
			}
			.viewer {
			}
			.editor {
			}
			@media (min-width: 992px) {
				.editor-container {
					position: fixed;
				}
			}
			@media (max-width: 992px) {
				.editor {
					padding-left: 0px;
				}
			}
			.editor textarea {
				font-family: monospace;
				font-size: 13px;
				color: black;
				min-width: 100%;
				max-width: 100%;
				height: 80vh;
			}
			
			.scrollable-menu {
				height: auto;
				max-height: 80vh;
				overflow-x: hidden;
			}

			
			.border {
				border: 1px dotted red;
			}
			
			.container img {
				max-width: 100%;
			}

    /* Mimic table appearance */
    div.table {
      display: table;
	  margin-bottom: 0px;
    }
    div.table .file-row {
      display: table-row;
    }
    div.table .file-row > div {
      display: table-cell;
      vertical-align: top;
      border-top: 1px solid #ddd;
      padding: 8px 20px;
    }
    div.table .file-row:nth-child(odd) {
      background: #f9f9f9;
    }



    /* The total progress gets shown by event listeners */
    #total-progress {
      opacity: 0;
      transition: opacity 0.3s linear;
    }

    /* Hide the progress bar when finished */
    #previews .file-row.dz-success .progress {
      opacity: 0;
      transition: opacity 0.3s linear;
    }

    /* Hide the delete button initially */
    #previews .file-row .delete {
      display: none;
    }

    /* Hide the start and cancel buttons and show the delete button */

    #previews .file-row.dz-success .start,
    #previews .file-row.dz-success .cancel {
      display: none;
    }
    #previews .file-row.dz-success .delete {
      display: block;
    }
			
		</style>
		<?php
			foreach($file_css as $css)
				echo "<style type=\"text/css\">$css</style>";
		?>
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
					<a class="navbar-brand" href="<?php echo $baseurl; ?>"><?php echo SITE_NAME; ?></a>
				</div>
				
				<!-- Navbar -->
				<div id="navbar" class="navbar-collapse collapse">
					<ul class="nav navbar-nav">
					
						<!-- Home -->
						<li <?php if($file_name=="home") echo 'class="active"'; ?>><a href="<?php echo $baseurlapp.'/home'; ?>">Home</a></li>
						
						
						<!-- History -->
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">History <span class="caret"></span></a>
							<ul class="dropdown-menu scrollable-menu" role="menu">
								<li <?php if($file_name==$base_file_name) echo 'class="active"'; ?>><a href="<?php echo "$baseurlapp/$base_file_name"; ?>">Current File</a></li>
								<li class="divider"></li>
								<?php
									if(count($file_revisions) < 1)
										echo '<li class="dropdown-header">This file has no history</li>';
									else
										foreach($file_revisions as $rev)
											echo '<li'.($file_name==$rev ? ' class="active"' : '')."><a href=\"$baseurlapp/$rev\">$rev</a></li>";
								?>
							</ul>
						</li>
						
						
						<!-- Files -->
						<li class="dropdown files-dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Files <span class="caret"></span></a>
							<ul id="files" class="dropdown-menu scrollable-menu" role="menu" style="min-width: 700px;">
								<li style="padding: 0px 20px 5px 20px;">
									<div style="float: left; padding-right: 15px">
									<!-- The fileinput-button span is used to style the file input field as button -->
										<button class="btn btn-success fileinput-button">
											<i class="glyphicon glyphicon-plus"></i>
											<span>Add files...</span>
										</button>
										<button type="submit" class="btn btn-primary start">
											<i class="glyphicon glyphicon-upload"></i>
											<span>Upload</span>
										</button>
										<button type="reset" class="btn btn-warning cancel">
											<i class="glyphicon glyphicon-ban-circle"></i>
											<span>Cancel all</span>
										</button>
									</div>

									<!-- The global file processing state -->
									<div class="fileupload-process">
										<div id="total-progress" class="progress progress-striped active" style="height: 34px; margin-bottom: 0px;" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
											<div class="progress-bar progress-bar-success" style="width:0%;" data-dz-uploadprogress></div>
										</div>
									</div>
								</li>
								
			
								<li>
									<span class="dropdown-header files-noupload">There is no files to upload</span>

									<div class="table table-striped files" id="previews">
										<div class="file-row">
											<!-- This is used as the file preview template -->
											
											<div class="media">
												<div class="media-body" style="width: 100%">
													<h4 class="media-heading name" data-dz-name></h4>

													<span class="size label label-info" style="float: left; margin-right: 15px; height: 30px; min-width: 50px; padding: 9px 6px 9px 6px;" data-dz-size></span>
													
													<div style="float: left; margin-right: 15px">
														<button class="btn btn-primary btn-sm start" title="Start">
															<i class="glyphicon glyphicon-upload"></i>
														</button>
														<button data-dz-remove class="btn btn-warning btn-sm cancel" title="Cancel">
															<i class="glyphicon glyphicon-ban-circle"></i>
														</button>
														<button data-dz-remove class="btn btn-danger btn-sm delete" title="Delete">
															<i class="glyphicon glyphicon-trash"></i>
														</button>
													</div>
													
													<div class="progress progress-striped active" style="height: 30px" role="progressbar" aria-valuemin="0" aria-valuemax="100" aria-valuenow="0">
														<div class="progress-bar progress-bar-success" style="width: 0%;" data-dz-uploadprogress></div>
													</div>
														
													<strong class="error text-danger" data-dz-errormessage></strong>
												</div>
												<div class="media-right">
													<img class="media-object" data-dz-thumbnail />
												</div>
											</div>
										</div>
									</div>
								</li>
								<li class="divider"></li>
								<?php
									if(count($list_files) < 1)
										echo '<li class="dropdown-header">There is no files attached</li>';
									else
										foreach($list_files as $list_file)
											echo "<li><a href=\"$url_files$list_file\" target=\"_blank\">$list_file</a></li>";
								?>
							</ul>
						</li>
						
						
						<!-- Options -->
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Options <span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu">
								<li><a id="template_edit_button" href="#">Edit template</a></li>
								<li><a id="publish" href="#">Publish file</a></li>
								<li><a href="<?php echo $baseurl; ?>?mode=preview" target="_blank">View published version</a></li>
								<li class="divider"></li>
								<li><a id="download_html" href="#">Download as HTML</a></li>
								<li><a id="download_markdown" href="#">Download as Markdown</a></li>
								<li><a href="<?php echo $baseurl; ?>?mode=downloadzip">Download entire page as ZIP</a></li>
								<li class="divider"></li>
								<li><?php echo ($file_readonly ?
									'<li class="disabled"><a href="#" disabled>This file is already read-only</a></li>' :
									"<a href=\"$baseurl?mode=readonly\">Make this file read-only</a></li>"); ?>
								<li><a id="view_log" href="#">View Log</a></li>
							</ul>
						</li>
						
						
						<!-- Help -->
						<li class="dropdown">
							<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false">Help <span class="caret"></span></a>
							<ul class="dropdown-menu" role="menu">
								<li <?php if($file_name=="readme") echo 'class="active"'; ?>><a href="<?php echo $baseurlapp.'/readme'; ?>">Readme</a></li>
								<li <?php if($file_name=="examples") echo 'class="active"'; ?>><a href="<?php echo $baseurlapp.'/examples'; ?>">Examples</a></li>
								<li <?php if($file_name=="markdown_styles") echo 'class="active"'; ?>><a href="<?php echo $baseurlapp.'/markdown_styles'; ?>">Markdown styles</a></li>
							</ul>
						</li>
						
					</ul>
					
					
					<!-- Right-side navbar: File to open, View, Edit -->
					<ul class="nav navbar-nav navbar-right">
						<li <?php if($file_mode=="view") echo 'class="active"'; ?>><a href="<?php echo $baseurl; ?>">View</a></li>
						<li class="<?php echo ($file_readonly ? 'disabled ' : '').($file_mode=='edit' ? 'active' : ''); ?>">
							<a href="<?php if(!$file_readonly) echo "$baseurl?mode=edit"; ?>">Edit</a>
						</li>
					</ul>
					
					<form class="navbar-form navbar-right">
						<input type="text" class="form-control" placeholder="Filename to open..." name="file" value="<?php echo $file_name; ?>">
					</form>
					
					
					<?php if(REQUIRE_AUTH) { ?>
					<!-- Login user -->
					<ul class="nav navbar-nav navbar-right">
						<?php if($authenticated) { ?>
							<li><a href="?logout"><?php echo $user; ?> <span class="glyphicon glyphicon-log-out"></span></a></li>
						<?php } else { ?>
							<li><a href="?login">Login <span class="glyphicon glyphicon-log-in"></span></a></li>
						<?php } ?>
					</ul>
					<?php } ?>
				</div><!--/.nav-collapse -->
			</div>
		</nav>
		
		<!-- Container -->
		<div class="container">
			<div class="row">
				<!-- Markdown -->
				<div id="wmd-preview-editor" class="viewer col-md-<?php echo ($file_mode=="edit" ? "6" : "12"); ?>">
					<?php if($file_mode=="published") { echo $html; } ?>
				</div>

				<!-- Editor -->
				<?php if ($file_mode=="edit") { ?>
					<div id="editor" class="editor col-md-6">
						<div class="editor-container col-md-6">
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
					</div>
					<script type="text/javascript">

					</script><?php
				} ?>
				<!-- END Editor -->
			</div>
		</div>
		
		
		<!-- Template editor -->
		<div id="template_edit" class="modal fade" tabindex="-1" role="document">
			<div class="modal-dialog" role="document" style="width: 80%;">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">Edit template</h4>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<div class="form-control" id="template_edit_textarea" style="height: 70vh; max-width: 100%; min-width: 100%;"><?php echo $template_file; ?></div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
						<button type="button" id="template_save" class="btn btn-primary">Save</button>
						<button type="button" id="template_preview" class="btn btn-info">Preview</button>
					</div>
				</div>
			</div>
		</div>
		
		<script type="text/javascript">
			var templateEditor = ace.edit("template_edit_textarea");
			templateEditor.setTheme("ace/theme/crimson_editor");
			templateEditor.getSession().setMode("ace/mode/php");
			templateEditor.setShowPrintMargin(false);
			document.getElementById('template_edit_textarea').style.fontSize='13px';
		</script>
		
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
				if(mode != 'published') {
					$("#wmd-preview-editor").html(converter.makeHtml(markdown));
				}
				
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
					
					
					// Fix width of editor
					$('.editor-container').width($('#editor').width() - 15);
					$( window ).resize(function() {
						$('.editor-container').width($('#editor').width() - 15);
					});
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
			
			$("#template_edit_button").click(function() {
				$("#template_edit").modal('show');
			});
			
			$("#template_save").click(function() {
				$.post("<?php echo $baseurl; ?>?mode=template_save", {
					template: templateEditor.getValue()
				}).done(function(data) {
					alert(data);
				});
			});
			
			$("#template_preview").click(function() {
				$.post("<?php echo $baseurl; ?>?mode=preview", {
					template: templateEditor.getValue()
				}).done(function(data) {
					var previewWindow = window.open("", "spmdwe_preview");
					previewWindow.document.open();
					previewWindow.document.write(data);
					previewWindow.document.close();
				});
			});
			
			$("#publish").click(function() {
				$.post("<?php echo $baseurl; ?>?mode=publish", {
					html: $("#wmd-preview-editor").html()
				}).done(function(data) {
					alert(data);
				});
			});
		
			$("#view_log").click(function() {
				alert('<?php echo $message; ?>');
			});
		</script>
		
		<script type="text/javascript">
			// Disable auto discover for all elements:
			Dropzone.autoDiscover = false;
			// Get the template HTML and remove it from the doument
			var previewTemplate = $("#previews").html();
			$("#previews").empty();

			var dropzone = new Dropzone(document.documentElement, {		// Make the whole document a dropzone
				url: "<?php echo $baseurl; ?>",	// Set the url
				maxFilesize: <?php echo $max_upload; ?>,
				thumbnailWidth: 80,
				thumbnailHeight: 80,
				parallelUploads: 30,
				previewTemplate: previewTemplate,
				autoQueue: false,				// Make sure the files aren't queued until manually added
				previewsContainer: "#previews",	// Define the container to display the previews
				clickable: ".fileinput-button",	// Define the element that should be used as click trigger to select files.
			});

			dropzone.on("addedfile", function(file) {
				// Hookup the start button
				file.previewElement.querySelector(".start").onclick = function() { dropzone.enqueueFile(file); };
				$('.files-dropdown').addClass('open');
				$("#files .files-noupload").hide();
			});
			
			dropzone.on("removedfile", function(file) {
				if(this.getQueuedFiles().length + this.getUploadingFiles().length + this.getRejectedFiles().length < 1)
					$("#files .files-noupload").show();
			});

			// Update the total progress bar
			dropzone.on("totaluploadprogress", function(progress) {
				document.querySelector("#total-progress .progress-bar").style.width = progress + "%";
			});

			dropzone.on("sending", function(file, xhr, formData) {
				formData.append("mode", "upload");
				// Show the total progress bar when upload starts
				document.querySelector("#total-progress").style.opacity = "1";
				// And disable the start button
				file.previewElement.querySelector(".start").setAttribute("disabled", "disabled");
			});

			// Hide the total progress bar when nothing's uploading anymore
			dropzone.on("queuecomplete", function(progress) {
				document.querySelector("#total-progress").style.opacity = "0";
			});

			// Setup the buttons for all transfers
			// The "add files" button doesn't need to be setup because the config
			// `clickable` has already been specified.
			$("#files .start").click(function() {
				dropzone.enqueueFiles(dropzone.getFilesWithStatus(Dropzone.ADDED));
			});
			$("#files .cancel").click(function() {
				dropzone.removeAllFiles(true);
			});
			$("#files").click(function (e) {
				e.stopPropagation();
			});
		</script>
	</body>
</html>

