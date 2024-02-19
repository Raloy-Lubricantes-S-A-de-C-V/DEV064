<?php

$fase = filter_input(INPUT_GET, "fx");
$response = call_user_func($fase);
//header('Content-Type: text/html; charset=utf-8');
echo $response;
$task_array = array();
$parent_array = array();
$shown_array = array();
$task_count = 0;
$level_count = 1;

function createChart() {
    global $task_array, $parent_array, $shown_array, $task_count, $level_count;
    $mysqli = new mysqli("localhost", "zarkruse_intrane", "Totich182308", "zarkruse_webcollab", "3306");
    $mysqli->set_charset("utf8");
    if ($mysqli->connect_errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->connect_error;
        return json_encode($respuesta);
    }
    $projectid = filter_input(INPUT_GET, "projectid", FILTER_SANITIZE_NUMBER_INT);
    $projectid=8;
    $q = "SELECT 
            t.id,
            t.task_name label,
            t.parent,
            t.precedents,
            t.completed,
            UNIX_TIMESTAMP(DATE(created)) startunix,
            UNIX_TIMESTAMP(deadline) deadlineunix,
            DATEDIFF(deadline,DATE(created)) duration,
            t.sequence,
            t.taskgroupid typeid,
            tg.group_name type,
            t.task_text,
            t.task_status
        FROM
            tasks t LEFT JOIN taskgroups tg ON t.taskgroupid=tg.id
        WHERE t.projectid = $projectid 
        ORDER BY IF(tg.group_name IS NULL, 1, 0),tg.group_name, deadlineunix ASC, priority DESC, task_name";

    $result = $mysqli->query($q);
    if ($mysqli->errno) {
        $respuesta["status"] = 0;
        $respuesta["error"] = $mysqli->error;
        return json_encode($respuesta);
    }
    if ($result->num_rows > 0) {
        $task_count = $result->num_rows;
        $respuesta["status"] = 1;
//        $nodes = [];
//        $tasks = [];
//        $objs2 = [];
//        $yaxis = 0;
//        $scalesize = 300;
        while ($row = $result->fetch_assoc()) {
            if ($row["parent"] == $row["projectid"]) {
                $row["haschild"]=false;
            } else {
                $parent_array[$row["parent"]] = 1;
                $row["haschild"]=true;
            }
            $task_array[] = $row;
        }
        $result->free();
//        $respuesta["parent_array"] = $parent_array;
//        $respuesta["task_Array"] = $task_array;
        $respuesta["tasks"] = listTasks($projectid);
        
    } else {
        $respuesta["status"] = 0;
        $respuesta["error"] = "No data available";
    }

    return json_encode($respuesta);
}

function listTasks($parentid) {
    global $task_array, $parent_array, $shown_array, $task_count, $level_count;
    $tasks = [];
    for ($i = 0; $i < $task_count; ++$i) {
        if ($task_array[$i]["parent"] != $parentid) {
            continue;
        }
        $tasks[] = task_fetch($i,$level_count);
        if (isset($parent_array[$task_array[$i]["id"]])) {
            $children = find_task_children($task_array[$i]['id']);
            foreach ($children as $child) {
                $tasks[] = $child;
            }
        }
    }
    return $tasks;
}

function find_task_children($parent) {
    global $task_array, $parent_array, $shown_array, $task_count, $level_count;
    $content_flag = 0;
    ++$level_count;
    $tasks = array();
    for ($i = 0; $i < $task_count; ++$i) {

        //ignore tasks not directly under this parent
        if ($task_array[$i]['parent'] != $parent) {
            continue;
        }

        $tasks[] = task_fetch($i,$level_count);

        //we have content to show
        $content_flag = 1;

        //if this task has children (subtasks), iterate recursively to find them
        if (isset($parent_array[($task_array[$i]['id'])])) {
            $children = find_task_children($task_array[$i]['id']);
            foreach ($children as $child) {
                $tasks[] = $child;
            }
        }
    }
    --$level_count;
    return $tasks;
}

function getChildren($row) {
    global $tasks, $objs;
    $children = $objs[$row["id"]];

    if (count($children) > 0) {
        $tasks[] = fetchTask($row, true);
        foreach ($children as $child) {
            $children = $objs[$child["id"]];
            if (count($children) > 0) {
                getchildren($child);
            } else {
                $tasks[] = fetchTask($row, false);
            }
        }
    } else {
        $tasks[] = fetchTask($row, false);
    }
}

function task_fetch($key,$level_count) {
    global $task_array;
    $row = $task_array[$key];
    $duration = ($row["duration"] - 1 > 0) ? $row["duration"] - 1 : 1;
    $arrStatus=array("active"=>"STATUS_ACTIVE","done"=>"STATUS_DONE","created"=>"STATUS_WAITING","notactive"=>"STATUS_SUSPENDED","cantcomplete"=>"STATUS_FAILED");
    $status = (array_key_exists($row["task_status"],$arrStatus)) ? $arrStatus[$row["task_status"]] : "STATUS_UNDEFINED";
    $precedents = ($row["precedents"] != "" && $row["precedents"] != null) ? $row["precedents"] : "";
    
    $task = array("id" => $row["id"], "name" => $row["label"], "progress" => $row["completed"], "progressByWorklog" => false, "relevance" => $row["priority"], "type" => $row["type"], "typeId" => $row["typeid"], "description" => $row["task_text"], "code" => $row["type"], "level" => $level_count, "status" => $status, "depends" => $precedents, "canWrite" => true, "start" => $row["startunix"] * 1000, "duration" => $duration, "end" => $row["deadlineunix"] * 1000, "startIsMilestone" => false, "endIsMilestone" => false, "collapsed" => false, "assigs" => array(), "hasChild" => $row["haschild"]);
    return $task;
}
