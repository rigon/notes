Single-page Markdown Web Editor (`spmdwe`)
===
Copyleft [rigon](https://rigon.github.io), February 2016

Homepage and demo:

* [http://spmdwe.rigon.uk](http://spmdwe.rigon.uk)


Intro
---

This is a small "web application". It's based on a application called **spmdwe**, created by sdaau in 2012:

* [http://sdaaubckp.sourceforge.net/spmdwe/spmdwe.php](http://sdaaubckp.sourceforge.net/spmdwe/spmdwe.php) 

The application consists on one file. It can be installed on a PHP Web Server and can used to edit text files with live preview for Markdown syntax. With proper permissions on the server's directory, files can also be saved on the host (a backup of the previous version is also saved). There are two switches, one that disables saving and other that disables authentication.

It is based on four other open-source packages, which can be downloaded separately (see Installation below):

* [pagedown - A JavaScript Markdown converter and editor](http://code.google.com/p/pagedown/) (_see also [this post](http://stackoverflow.com/a/135155/277826)_)
* [JQuery](http://jquery.com/)
* [Bootstrap](http://getbootstrap.com/)
* [Dropzone.js](http://www.dropzonejs.com/)

While the default .css style shown here is hardly impressive, do check out the list of some [free markdown css styles](markdown_styles), which can be used instead.


Features
---
Since this project is a fork from the original project **spmdwe**, it inherits the main features:

* Editing Markdown files
* Live editing, writing automatically updates the output
* View and edit modes
* Backup old versions of the file

In this version, these new features have been added:

* Fresh and modern design and layout reorganization
* Clear URLs, without the hassle of URL parameters
* Edition side by side with the view
* Ability to navigate through history
* Ready for Mobile, with responsive layout that adapts automatically
* File upload management, attached to each page
* Publish mode for guest users
* Website template editor and previewer
* Page download in HTML, Markdown and ZIP
* Option to make files read only
* Application improvements


Docker
------

You can the project inside docker with:

    docker run -p 80:80 -d rigon/spmdwe

To save the data, you can run with a volume:

    docker run -p 80:80 -v spmdwe_data:/var/www/html/files -d rigon/spmdwe

You can set environment variables to customize your installation:

 - `SITE_NAME`: Name of the Website
 - `HOMEPAGE`: Default page loaded for homepage
 - `SAVE_ENABLED`: Allow modifications
 - `REQUIRE_AUTH`: Require authentication to make modifications
 - `USER`: Username used for authentication
 - `PASS`: Password for the user

For a multi-arch build, it can be done with:

    docker buildx build --push -t rigon/spmdwe --platform linux/386,linux/amd64,linux/arm/v6,linux/arm/v7,linux/arm64/v8,linux/ppc64le .

Download
---
There is the link to download:

* [https://github.com/rigon/notes/archive/master.zip](https://github.com/rigon/notes/archive/master.zip)


Installation
---

Here are the steps needed to install this application, in the form of `bash` commands:

    # mkdir spmdwe # if downloading manually, else:
    # checkout from svn - creates `spmdwe` directory and files in it
    wget https://github.com/rigon/notes/archive/master.zip
    unzip master.zip
    mv notes-master/ notes

    sudo chown www-data:www-data notes/files  # on server, else cannot save files!


Configuration
---
To work properly it is required the ```ReWrite``` Module to be active in apache.

Then the next configuration should be used. This can be done via `.htaccess` file or on apache configuration:

    RewriteEngine on
    
    # Rewrite URL as a query parameter for index.php
    RewriteCond %{REQUEST_URI} !^/static
    RewriteCond %{REQUEST_URI} !^/files/(([\w-_,\ ]+\.)*[\w-_,\ ]+\/)+([\w-_,\ ]+\.)*[\w-_,\ ]+$
    RewriteRule ^([^/]*)$ index.php?file=$1 [L,QSA]
    
    # Forbid non-existing URLs
    RewriteCond %{REQUEST_URI} !^/static
    RewriteCond %{REQUEST_URI} !^/files/(([\w-_,\ ]+\.)*[\w-_,\ ]+\/)+([\w-_,\ ]+\.)*[\w-_,\ ]+$
    RewriteRule .* "-" [F]

You have to change the directory `/notes` to the directory you've installed.


Usage
----

The application is pretty simple:

* there is a view mode, which doesn't show an edit box, shows the html view
* there is edit mode, which shows a live html preview, edit box right next to it, and shows **Save** and **Save and View** at the end
* Both `view` and `edit` can be used as values for the `mode` query string argument (see source code for details)
* Upon save, the old version is backed up, with a filename with appended unix timestamp; the new version is saved under the original filename - and the new version is displayed in view mode
* **Save** will save the file and continue in editing mode and **Save and View** will save and change to view mode
* A file is chosen by adding manually the filename in the URL, say `http://example.com/notes/somefile.`
* If a requested file (`somefile`) doesn't exist, it should be created automatically, and edit mode displayed
* Otherwise, without any other arguments, a file is loaded in view mode
* If the application is called without any arguments whatsoever (say, `http://example.com/notes/`), then it loads `home` and goes into view mode.
* If you are a guest user, you will see the published version and you cannot edit files.

