<?php
/*
  $Id$

  (c) 2002 - 2014 Andrew Simpson <andrewnz.simpson at gmail.com>

  WebCollab
  ---------------------------------------

  This program is free software; you can redistribute it and/or modify it under the
  terms of the GNU General Public License as published by the Free Software Foundation;
  either version 2 of the License, or (at your option) any later version.

  This program is distributed in the hope that it will be useful, but WITHOUT ANY
  WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A
  PARTICULAR PURPOSE. See the GNU General Public License for more details.

  You should have received a copy of the GNU General Public License along with this
  program; if not, write to the Free Software Foundation, Inc., 675 Mass Ave,
  Cambridge, MA 02139, USA.

  Function:
  ---------

  All functions and code needed to manage files

*/

require_once('path.php');
require_once(BASE.'includes/security.php' );
include_once(BASE.'includes/screen.php' );

//
// The action handler
//
if(isset($_POST['action'] ) ) {
  $action = $_POST['action'];
}
elseif(isset($_GET['action'] ) ) {
  $action = $_GET['action'];
}
else {
  error('Files action handler', 'No request given' );
}

//what do you want to task today =]
switch($action ) {

  //download a file
  case 'download':
    include(BASE.'files/file_download.php' );
    break;

  //upload a file
  case 'upload':
    create_top($lang['file_choose'], 0, 'file-upload', 1 );
    include(BASE.'includes/mainmenu.php' );
    goto_main();
    include(BASE.'files/file_upload.php' );
    create_bottom();
    break;

  //update a file
  case 'update':
    create_top($lang['file_choose'], 0, 'file-update', 1 );
    include(BASE.'includes/mainmenu.php' );
    goto_main();
    include(BASE.'files/file_update.php' );
    create_bottom();
    break;

  //delete a file
  case 'delete':
    create_top($lang['file_choose'] );
    include(BASE.'includes/mainmenu.php' );
    goto_main();
    include(BASE.'files/file_update.php' );
    create_bottom();
    break;

  //admin files
  case 'admin':
    create_top($lang['file_admin'], 0, 'file-admin' );
    include(BASE.'includes/mainmenu.php' );
    include(BASE.'files/file_menubox.php' );
    goto_main();
    include(BASE.'files/file_admin.php' );
    create_bottom();
    break;

    //search
  case 'search':
    create_top($lang['info'], 0, 'file-search-results' );
    include(BASE.'includes/mainmenu.php');
    //include(BASE.'forum/forum_menubox.php');
    goto_main();
    include(BASE.'files/file_search.php');
    create_bottom();
    break;
    
  //display search box
  case 'search_box':
    create_top($lang['info'], 0, 'file-search', 2 );
    include(BASE.'includes/mainmenu.php');
    //include(BASE.'forum/forum_menubox.php');
    goto_main();
    include(BASE.'files/file_searchbox.php');
    create_bottom();
    break;

  case 'submit_del':
  case 'submit_upload':
  case 'submit_update':
    include(BASE.'files/file_submit.php' );
    break;

  //error case
  default:
    error('File action handler', 'Invalid request given' );
    break;
}

?>