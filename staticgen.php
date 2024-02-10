<?php
define('FILES_DIR', "files/");
define('OUT_FOLDER', "out-website/");

env_var('SITE_NAME');
env_var('SAVE_ENABLED');
env_var('REQUIRE_AUTH');
env_var('USER');
env_var('PASS');

function env_var($name) {
    $value = getenv($name);
    if($value)
        $_ENV[$name] = $value;
}

# Create output folder
if(!is_dir(OUT_FOLDER))
    mkdir(OUT_FOLDER);
if(!is_dir(OUT_FOLDER.FILES_DIR))
    mkdir(OUT_FOLDER.FILES_DIR);

$files = scandir(FILES_DIR);
foreach ($files as $file) {
    if($file === "..")
        continue;
    
    if(!is_dir(FILES_DIR . $file))
        continue;
    
    if($file === ".")    # Homepage
        unset($_REQUEST['file']);
    else {
        $_REQUEST['file'] = $file;
        # Create folder for page
        if(!is_dir(OUT_FOLDER . $file))
            mkdir(OUT_FOLDER . $file);
    }

    $outfile = OUT_FOLDER . ($file === "." ? "" : $file . "/") ."index.html";
    
    ob_start();
    include('index.php');
    $output = ob_get_clean();
    file_put_contents($outfile, $output);

    # Copy files
    if($file !== ".") {
        if(!is_dir(OUT_FOLDER.FILES_DIR.$file))
            mkdir(OUT_FOLDER.FILES_DIR.$file);
        foreach($list_files as $copyfile) {
            echo(FILES_DIR.$file."/".$copyfile." -> ".OUT_FOLDER.FILES_DIR.$file."/".$copyfile."\n");
            copy(FILES_DIR.$file."/".$copyfile, OUT_FOLDER.FILES_DIR.$file."/".$copyfile);
        }
    }
}

?>
