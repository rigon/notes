# notes
A notebok application to have your notes always at hand.

Single-page Markdown Web Editor (`spmdwe`)
===
Copyleft rigon, December 2014

Homepage and demo:

* [http://www.spmdwe.tk](http://www.spmdwe.tk)

Intro
---

This is a small "web application". It's based on a application called **spmdwe**, created by sdaau in 2012:

* [http://sdaaubckp.sourceforge.net/spmdwe/spmdwe.php](http://sdaaubckp.sourceforge.net/spmdwe/spmdwe.php) 

The application consists on one file,  (_which was renamed to `index.php`_). It can be installed on a php server, and used to edit text files with live preview for Markdown syntax.

With proper permissions on the server directory, files can also be saved on the host (a backup of the previous version is also saved). There is also a switch that disables saving, enabled at the demo page for protection.

It is based on three other open-source packages, which can be downloaded separately (see Installation below):

* [pagedown - A JavaScript Markdown converter and editor](http://code.google.com/p/pagedown/) (_see also [this post](http://stackoverflow.com/a/135155/277826)_)
* [JQuery](http://jquery.com/)
* [Bootstrap](http://getbootstrap.com/)

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
* Option to make files read only
* Ready for Mobile, with responsive layout that automatically adapts
* Application improvements

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

    chmod 775 spmdwe  # on server, else cannot save backups!
    cd spmdwe/files/

Usage
----

The application is pretty simple:

* there is a view mode, which doesn't show an edit box, shows the html view
* there is edit mode, which shows a live html preview, edit box right next to it, and shows **Save** and **Save and View** at the end
* Both `view` and `edit` can be used as values for the `mode` query string argument (see source code for details)
* Upon save, the old version is backed up, with a filename with appended unix timestamp; the new version is saved under the original filename - and the new version is displayed in view mode
* **Save** will save the file and continue in editing mode and **Save and View** will save and change to view mode
* A file is chosen by adding manually the filename in the URL, say `http://example.com/spmdwe/somefile.`
* If a requested file (`somefile`) doesn't exist, it should be created automatically, and edit mode displayed
* Otherwise, without any other arguments, a file is loaded in view mode
* If the application is called without any arguments whatsoever (say, `http://example.com/spmdwe/`), then it loads `readme` and goes into view mode.
