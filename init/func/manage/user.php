<?php 
function createUser(){
    global $db;
    $query = $db->prepare("INSERT INTO users(username, password) VALUES (?,?,?,'User')");
    $query-> bind_param('ss', $username, $password);
    $query->execute();
    if($db->affected_rows){
        return true;
    }
    return false;
}

?>