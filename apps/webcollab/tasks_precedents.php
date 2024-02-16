<?php

/*
  $Id$

  (c) 2004 - 2014 Andrew Simpson <andrewnz.simpson at gmail.com>

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

  All functions and code needed to manage and show tasks

 */

require_once('path.php');
require_once(BASE . 'includes/security.php' );
//include_once(BASE . 'includes/screen.php' );
//includes
require_once(BASE . 'includes/token.php' );
include_once(BASE . 'includes/admin_config.php' );
include_once(BASE . 'includes/details.php' );
//include_once(BASE . 'includes/time.php' );
//include_once(BASE . 'tasks/task_common.php' );

include_once(BASE . 'tasks_precedents/tasks_precedents.php');

//create_top('tasks_precedent', 0, 'task-precedents', 1);
//include(BASE . 'includes/mainmenu.php' );
//include(BASE . 'tasks/task_navigate.php' );
//include(BASE . 'tasks/task_menubox.php' );
//goto_main();

$fase = filter_input(INPUT_GET, "fx");
$response = call_user_func($fase);
echo $response;

//create_bottom();
?>
