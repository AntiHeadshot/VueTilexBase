<?php
    include_once 'internal/connect.php';

    switch($_SERVER['REQUEST_METHOD'])
    {
        case 'GET':
            if(isset($_GET['id'])) {    
                include("internal/secretUser.php");
                echoChar($_SESSION['userid'],$_GET['id']);
            }else
                include("internal/secretUser.php");
                echoAllChar($_SESSION['userid']);
            break;
        case 'POST':
            if(isset($_GET['id'])) {    
                include("internal/secretAdmin.php");
                if(isset($_POST['isActive'])){
                    updateUser($_GET['id'], "isActive",$_POST['isActive']);
                }
                else if(isset($_POST['privilege'])){
                    updateUser($_GET['id'], "privilege",$_POST['privilege']);
                }
            }else{
                if(isset($_SESSION['userid']))
                {
                    include("internal/secretUser.php");
                    //todo Update Email/Password
                }else{
                    register();
                }
            }
            break;
        case 'DELETE':
            if(isset($_GET['id'])) {        
                include("internal/secretAdmin.php");
                $statement = $pdo->prepare("DELETE FROM users WHERE id = :id");
                if(!$statement->execute(array('id',$_GET['id'])))
                    http_response_code(500);
            }else
                http_response_code(400);
            break;
    }
    
?>