<?php

/*
  $Id: task_show.php 2263 2009-08-01 02:39:44Z andrewsimpson $

  (c) 2002 - 2012 Andrew Simpson <andrewnz.simpson at gmail.com>

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

  Show a task

 */

//security check
if (!defined('UID')) {
    die('Direct file access not permitted');
}

//includes
require_once(BASE . 'includes/usergroup_security.php');
include_once(BASE . 'includes/details.php' );
include_once(BASE . 'tasks/task_common.php' );
include_once(BASE . 'includes/time.php' );

//secure variables
$content = '';

//is there an id ?
if (!@safe_integer($_GET['taskid']) || $_GET['taskid'] == 0) {
    error('Task show', 'Not a valid value for taskid');
}

$taskid = $_GET['taskid'];

//check usergroup security
$taskid = usergroup_check($taskid);

$q = db_prepare('SELECT ' . PRE . 'tasks.created AS created,
                      ' . PRE . 'tasks.finished_time AS finished,
                      ' . PRE . 'tasks.completion_time AS completion,
                      ' . PRE . 'users.fullname AS fullname,
                      ' . PRE . 'taskgroups.group_name AS taskgroup_name,
                      ' . PRE . 'usergroups.group_name AS usergroup_name,
                      ' . db_epoch() . ' ' . PRE . 'seen.seen_time) AS last_seen,
                      ' . PRE . 'tasks.precedents AS precedents
                      FROM ' . PRE . 'tasks
                      LEFT JOIN ' . PRE . 'users ON (' . PRE . 'users.id=' . PRE . 'tasks.task_owner)
                      LEFT JOIN ' . PRE . 'taskgroups ON (' . PRE . 'taskgroups.id=' . PRE . 'tasks.taskgroupid)
                      LEFT JOIN ' . PRE . 'usergroups ON (' . PRE . 'usergroups.id=' . PRE . 'tasks.usergroupid)
                      LEFT JOIN ' . PRE . 'seen ON (' . PRE . 'tasks.id=' . PRE . 'seen.taskid AND ' . PRE . 'seen.userid=?)
                      WHERE ' . PRE . 'tasks.id=? LIMIT 1');

db_execute($q, array(UID, $taskid));

//get the data
if (!($row = db_fetch_array($q, 0) )) {
    error('Task show', 'The requested item has either been deleted, or is now invalid.');
}

//mark this as seen in seen ;)
if ($row['last_seen']) {
    $q = db_prepare('UPDATE ' . PRE . 'seen SET seen_time=now() WHERE taskid=? AND userid=?');
    db_execute($q, array($taskid, UID));
} else {
    $q = db_prepare('INSERT INTO ' . PRE . 'seen(userid, taskid, seen_time) VALUES (?, ?, now() )');
    db_execute($q, array(UID, $taskid));
}

//text link for 'printer friendly' page
if (isset($_GET['action']) && $_GET['action'] === "show_print") {
    $content .= "<p><span class=\"textlink\">[<a href=\"tasks.php?x=" . X . "&amp;action=show&amp;taskid=" . $taskid . "\">" . $lang['normal_version'] . "</a>]</span></p>";
} else {
    //show print tag
    $content .= "<div style=\"text-align : right\">" .
            "<a href=\"icalendar.php?x=" . X . "&amp;action=project&amp;taskid=" . $taskid . "\" title=\"" . $lang['icalendar'] . "\">" .
            "<img src=\"images/calendar_link.png\" alt=\"" . $lang['icalendar'] . "\" width=\"16\" height=\"16\" /></a>&nbsp;&nbsp;&nbsp;" .
            "<a href=\"tasks.php?x=" . X . "&amp;action=show_print&amp;taskid=" . $taskid . "\" title= \"" . $lang['print_version'] . "\">" .
            "<img src=\"images/printer.png\" alt=\"" . $lang['print_version'] . "\" width=\"16\" height=\"16\" /></a></div>\n";
    //show 'project jump' select box
    $content .= project_jump($taskid);
}

//start of header table
$content .= "<div class=\"taskshow\">\n";

//percentage_completed gauge if this is a project
if ($TASKID_ROW['parent'] == 0) {
    $content .= sprintf($lang['percent_project_sprt'], $TASKID_ROW['completed']) . "\n";
    $content .= show_percent($TASKID_ROW['completed']);
}

//project/task name
$content .= "<p style=\"margin-top: 5px; margin-bottom: 10px; font-weight: bold\">" . $TASKID_ROW['task_name'] . "</p>\n";

//show text
$content .= "<div class=\"textbackground\" style=\"width: 95%\">\n";

$content .= nl2br(bbcode($TASKID_ROW['task_text']));
$content .= "</div>\n</div>\n";

//start of info table
$content .= "<table class=\"celldata\">\n";

//get owner information
if ($TASKID_ROW['task_owner'] == 0) {
    $content .= "<tr><td>" . $lang['owned_by'] . ":</td><td>" . $lang['nobody'] . "</td></tr>\n";
} else {
    $content .= "<tr><td>" . $lang['owned_by'] . ": </td><td><a href=\"users.php?x=" . X . "&amp;action=show&amp;userid=" . $TASKID_ROW['task_owner'] . "\">" . $row['fullname'] . "</a></td></tr>\n";
}

//get creator information (null if creator has been deleted!)
$q = db_prepare('SELECT fullname FROM ' . PRE . 'users WHERE id=? LIMIT 1');
db_execute($q, array($TASKID_ROW['creator']));
$creator = @db_result($q, 0, 0);

$content .= "<tr><td>" . $lang['created_on'] . ": </td><td>";
if ($creator == NULL) {
    $content .= nicedate($TASKID_ROW['created']);
} else {
    $content .= sprintf($lang['by_sprt'], nicedate($row['created']), "<a href=\"users.php?x=" . X . "&amp;action=show&amp;userid=" . $TASKID_ROW['creator'] . "\">" . $creator . "</a>");
}
$content .= "</td></tr>\n";

//get precedents/constraints
$precedentsarr = [];
$arrcurrprec = ($TASKID_ROW['precedents'] != "") ? explode(",", $TASKID_ROW['precedents']) : [];
if (count($arrcurrprec) > 0) {
    $precedentsq = 'SELECT t.id, CONCAT(IF(parentnames.parent=0,"",CONCAT(LEFT(parentnames.task_name,5),">")),LEFT(t.task_name,12)) compeltename,UNIX_TIMESTAMP(t.deadline) precdue, t.parent, projectid FROM ' . PRE . 'tasks t LEFT JOIN (SELECT id,task_name,parent FROM ' . PRE . 'tasks) parentnames ON t.parent=parentnames.id WHERE t.id IN (' . implode(",", $arrcurrprec) . ') ORDER BY deadline DESC';
    $q = db_query($precedentsq);
    for ($i = 0; $task_row_prec = @db_fetch_array($q, $i); ++$i) {
        $state = ( ($task_row_prec['precdue'] - TIME_NOW) / 86400 );
        if ($state > 1) {
            $precdue = "(" . sprintf($lang['due_sprt'], ceil((real) $state)) . ")";
        } else if ($state > 0) {
            $precdue = "(" . $lang['tomorrow'] . ")";
        } else {
            switch (-ceil($state)) {

                case 0:
                    $precdue = "<span class=\"green\">(<i>" . $lang['due_today'] . "</i>)</span>";
                    break;

                case 1:
                    $precdue = "<span class=\"red\">(" . $lang['overdue_1'] . ")</span>";
                    break;

                default:
                    $precdue = "<span class=\"red\">(" . sprintf($lang['overdue_sprt'], -ceil((real) $state)) . ")</span>";
                    break;
            }
        }
        $precedentsarr[] = $task_row_prec['compeltename'] . " <small>" . $precdue . "</small>";
    }
}
$content .= (count($precedentsarr) > 0) ? "<tr><td>Precedencias: </td><td>" . implode(" ,", $precedentsarr) . "</td></tr>\n" : "";

//get deadline
$content .= "<tr><td>" . $lang['deadline'] . ": </td><td>" . nicedate($TASKID_ROW['deadline']) . "</td></tr>\n";

//get priority
$content .= "<tr><td>" . $lang['priority'] . ": </td><td>";
switch ($TASKID_ROW['priority']) {

    case 0:
        $content .= $task_state['dontdo'];
        break;
    case 1:
        $content .= $task_state['low'];
        break;
    case 2:
        $content .= $task_state['normal'];
        break;
    case 3:
        $content .= "<b>" . $task_state['high'] . "</b>";
        break;
    case 4:
        $content .= "<b><span class=\"red\">" . $task_state['yesterday'] . "</span></b>";
        break;
}
$content .= "</td></tr>\n";

//status info and task completion date
switch ($TASKID_ROW['parent']) {
    case 0:
        //project - show the finish date and status
        $title = $lang['project_details'];
        switch ($TASKID_ROW['task_status']) {
            case 'cantcomplete':
                $content .= "<tr><td>" . $lang['status'] . ": </td><td><b>" . $lang['project_on_hold'] . "</b></td></tr>\n";
                $content .= "<tr><td>" . $lang['modified_on'] . ": </td><td>" . nicedate($row['finished']) . "</td></tr>\n";
                break;

            case 'notactive':
                $content .= "<tr><td>" . $lang['status'] . ": </td><td>" . $lang['project_planned'] . "</td></tr>\n";
                break;

            case 'nolimit':
                $content .= "<tr><td>" . $lang['status'] . ": </td><td>" . $lang['project_no_deadline'] . "</td></tr>\n";
                break;

            case 'done':
            default:
                if ($TASKID_ROW['completed'] == 100) {
                    $content .= "<tr><td>" . $lang['completed_on'] . ": </td><td>" . nicedate($row['completion']) . "</td></tr>\n";
                }
                break;
        }
        break;

    default:
        //task
        $title = $lang['task_info'];
        $content .= "<tr><td>" . $lang['status'] . ": </td><td>";
        switch ($TASKID_ROW['task_status']) {
            case 'created':
                $content .= $task_state['new'];
                break;
            case 'notactive':
                $content .= $task_state['planned'];
                break;
            case 'active':
                $content .= $task_state['active'];
                break;
            case 'cantcomplete':
                $content .= "<b>" . $task_state['cantcomplete'] . "</b>";
                break;
            case 'done':
                $content .= $task_state['done'];
                break;
            default:
                $content .= $TASKID_ROW['task_status'];
                break;
        }
        $content .= "</td></tr>\n";

        //is there a finished date ?
        switch ($TASKID_ROW['task_status']) {
            case 'done':
                $content .= "<tr><td>" . $lang['completed_on'] . ": </td><td>" . nicedate($row['finished']) . "</td></tr>\n";
                break;

            case 'cantcomplete':
                $content .= "<tr><td>" . $lang['modified_on'] . ": </td><td>" . nicedate($row['finished']) . "</td></tr>\n";
                break;

            default:
                break;
        }
        break;
}

//task group
if ($TASKID_ROW['parent'] != 0) {

    switch ($TASKID_ROW['taskgroupid']) {
        case 0:
            $content .= "<tr><td><a href=\"help/help_language.php?item=taskgroup&amp;type=help&amp;lang=" . LOCALE_USER . "\" onclick=\"window.open('help/help_language.php?item=taskgroup&amp;type=help&amp;lang=" . LOCALE_USER . "'); return false\">" . $lang['taskgroup'] . "</a>: </td><td>" . $lang['none'] . "</td></tr>\n";
            break;

        default:
            $content .= "<tr><td><a href=\"help/help_language.php?item=taskgroup&amp;type=help&amp;lang=" . LOCALE_USER . "\" onclick=\"window.open('help/help_language.php?item=taskgroup&amp;type=help&amp;lang=" . LOCALE_USER . "'); return false\">" . $lang['taskgroup'] . "</a>: </td><td>" . $row['taskgroup_name'] . "</td></tr>\n";
            break;
    }
}

//show the usergroupid
if ($TASKID_ROW['usergroupid'] != 0) {
    $content .= "<tr><td><a href=\"help/help_language.php?item=usergroup&amp;type=help&amp;lang=" . LOCALE_USER . "\" onclick=\"window.open('help/help_language.php?item=usergroup&amp;type=help&amp;lang=" . LOCALE_USER . "'); return false\">" . $lang['usergroup'] . "</a>: </td><td>" . $row['usergroup_name'] . " ";

    switch ($TASKID_ROW['globalaccess']) {
        case 't':
            $content .= $lang[$TYPE . "_accessible"] . "</td></tr>\n";
            break;

        case 'f':
        default:
            $content .= "<b>" . $lang[$TYPE . "_not_accessible"] . "</b></td></tr>\n";
            break;
    }

    if ($TASKID_ROW['groupaccess'] == 't') {
        $content .= "<tr><td>&nbsp;</td><td><i>" . $lang["usergroup_can_edit_" . $TYPE] . "</i></td></tr>\n";
    }
} else {
    $content .= "<tr><td><a href=\"help/help_language.php?item=usergroup&amp;type=help&amp;lang=" . LOCALE_USER . "\" onclick=\"window.open('help/help_language.php?item=usergroup&amp;type=help&amp;lang=" . LOCALE_USER . "'); return false\">" . $lang['usergroup'] . "</a>: </td><td>" . $lang[$TYPE . "_not_in_usergroup"] . "</td></tr>\n";
}

$content .= "</table>\n";

//if this is an archived task, or you are a GUEST user, then no user functions are available
if (($TASKID_ROW['archive'] == 0 ) && (!GUEST )) {

    $content .= "<div style=\"text-align : center\"><span class=\"textlink\">\n";

    //set add function
    switch ($TYPE) {
        case 'project':
            $content .= "[<a href=\"tasks.php?x=" . X . "&amp;action=add&amp;parentid=" . $taskid . "\">" . $lang['add_task'] . "</a>]&nbsp;\n";
            break;

        case 'task':
            $content .= "[<a href=\"tasks.php?x=" . X . "&amp;action=add&amp;parentid=" . $taskid . "\">" . $lang['add_subtask'] . "</a>]&nbsp;\n";
            break;
    }

    //check for owner or group access
    if ((UID == $TASKID_ROW['task_owner'] ) ||
            ($TASKID_ROW['groupaccess'] == "t") && (isset($GID[($TASKID_ROW['usergroupid'])]) )) {
        $access = true;
    } else {
        $access = false;
    }

    //admin - owner - groupaccess  ==> [edit] button
    if ((ADMIN ) || ($access )) {
        $content .= "[<a href=\"tasks.php?x=" . X . "&amp;action=edit&amp;taskid=" . $taskid . "\">" . $lang['edit'] . "</a>]&nbsp;\n";
    }

    //(owner) & (uncompleted task)==> [I don't want it anymore] button
    if (UID == $TASKID_ROW['task_owner'] && ($TASKID_ROW['task_status'] != 'done' )) {
        $content .= "[<a href=\"tasks.php?x=" . X . "&amp;action=edit&amp;taskid=" . $taskid . "&amp;owner=0\">" . $lang['i_dont_want'] . "</a>]&nbsp;\n";
    }

    //(owner - groupaccess) & (uncompleted task)  ==> [I finished it] button
    if (($access ) && ($TASKID_ROW['task_status'] != 'done' ) && ($TASKID_ROW['parent'] != 0 )) {
        $content .= "[<a href=\"tasks.php?x=" . X . "&amp;action=edit&amp;taskid=" . $taskid . "&amp;status=1\">" . $lang['i_finished'] . "</a>]&nbsp;\n";
    }

    //unowned task ==> [I'll take it!] button
    if ($TASKID_ROW['task_owner'] == 0) {
        $content .= "[<a href=\"tasks.php?x=" . X . "&amp;action=edit&amp;taskid=" . $taskid . "&amp;owner=" . UID . "\">" . sprintf($lang['i_take_it']) . "</a>]&nbsp;\n";
    }

    //(admin) & (not owner) & (has owner) & (uncompleted task) ==> [Take over task] button
    if ((ADMIN ) && (UID != $TASKID_ROW['task_owner'] ) && ($TASKID_ROW['task_owner'] != 0 ) && ($TASKID_ROW['task_status'] != 'done' )) {
        $content .= "[<a href=\"tasks.php?x=" . X . "&amp;action=edit&amp;taskid=" . $taskid . "&amp;owner=" . UID . "\">" . sprintf($lang["take_over_" . $TYPE]) . "</a>]&nbsp;\n";
    }
    $content .= "</span></div>\n";
}

new_box($title, $content, 'boxdata-normal', 'head-normal', 'boxstyle-short');
?>
