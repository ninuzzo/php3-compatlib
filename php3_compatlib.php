<?php
/*
   PHP3 compatibility library to run legacy applications unmodified.

   Copyright 2012 Antonio Bonifati <https://ninuzzo.github.com>, released under the
   PHP license. Please see for license details: http://www.php.net/license/3_01.txt
   Use at your own risk and understand the security implications of running any
   PHP3 code which has not been designed with security in mind:

   Emulates the PHP3 environment in later PHP versions (e.g. PHP5), including
   'register_globals On'.  This is useful to run legacy code without
   modification, especially because this directive is going to be removed as of
   PHP 5.4.0.

   This file should be included at the beginning of all PHP3 scripts to run, or
   a bit more securely just before any external variable is used, but thay may
   be inconvenient for a quick usage. In the former case you can simply set
   auto_prepend_file=/path/php3_compatlib.php in your php.ini file, or you can set
   the auto_prepend_file value as a directive in .htaccess or your hosts
   section of httpd.conf.

   If you include or require it manually in every file you may want to use
   include_once or require_once to avoid including it more than one time if you
   just decide to include/require it at the beginning of each php3 and you do
   not want to track inclusion dependencies (using auto_prepend_file is
   preferred in this case because the file will only be included ones per
   request, that is it won't be included twice or more if your script then
   includes/requires other PHP files).

   Please note register_globals must be turned Off when using this script
   (and not only when using this script!)
*/

/* We do not want to pollute the global environment. Unfortunately, PHP has no
   a lexical scope, namespaces cannot contain private variables and extract()
   extracts variables only in the current scope. It seems the best we can do it
   to use only one reserved name like $php3_compatlib and unset the variable
   later on, just to free space and for added security. See:
   http://stackoverflow.com/questions/3605595/creating-and-invoking-an-anonymous-function-in-a-single-statement */
$php3_compatlib = array();

/* We should extract in the same order that's defined in variables_order. We do
   not extract $_SESSION, session management wasn't available until PHP4 and
   thus no PHP3 code should need that. */
/* import_request_variables could also be used, but it does not support $_SERVER.
   If you do not need $_SERVER, e.g. $PHP_SELF, $SCRIPT_FILENAME, etc. you can
   uncomment this line and comment the whole following foreach. */
//@import_request_variables(strtr(ini_get('variables_order'), array('E' => '', 'S' => '')), '');
foreach (str_split(ini_get('variables_order')) as $php3_compatlib['vartype']) {
  switch ($php3_compatlib['vartype']) {
    case 'G':
      extract($_GET, EXTR_SKIP); // Never overwrite existing variable, for security!
    break;

    case 'P':
      extract($_POST, EXTR_SKIP);
    break;

    case 'C':
      extract($_COOKIE, EXTR_SKIP);
    break;

    case 'S':
      extract($_SERVER, EXTR_SKIP);
    break;
  }
}

/* This ensures compatibility with the old PHP3 file upload structures. */
foreach ($_FILES as $php3_compatlib['userfile'] => $php3_compatlib['filedata']) {
  /* We only extract what the latest PHP3 version defined. */
  foreach (array('name', 'size', 'type') as $php3_compatlib['suffix']) {
    $php3_compatlib['name'] = $php3_compatlib['userfile'] . "_$php3_compatlib[suffix]";
    $$php3_compatlib['name'] = $php3_compatlib['filedata'][$php3_compatlib['suffix']];
  }
  $$php3_compatlib['userfile'] = $php3_compatlib['filedata']['tmp_name'];
}

unset($php3_compatlib);

// There should be no last newline.
?>
