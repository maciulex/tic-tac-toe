<?php
    include_once "../../base.php";
    $mainIndexPath = "../../index.php";
    session_start();
    if (!isset($_SESSION['nickname']) || !isset($_SESSION['authCode'])) {
        session_destroy();
        header("Location: ../../index.php");
        exit();
    } else {
        @include_once "../../user/loggedCheck.php";
    }
    if (!isset($_POST['name']) || empty($_POST['name']) || strlen($_POST['name']) < 3 || trim($_POST['name'],";") != $_POST['name'] || !isset($_POST['gameStyle'])) {
        $_SESSION['error'] = "Coś się nie udało! 1";
        header("Location: ../gameCreate.php");
        exit();
    }   
    $passwordBool = false;
    $password;
    if (isset($_POST['passwordCh'])) {
        $password = $_POST['password'];
        if ($password != trim($password,";")) {
            $_SESSION['error'] = "Nie dozwolony znak: ;";
            header("Location: ../gameCreate.php");
            exit();
        }
        $passwordBool = true;
    } else {
        $passwordBool = false;
    }
    $gameStyle = intval($_POST['gameStyle']);
    switch ($gameStyle) {
        default:
        case 0:
            $gameStyle = '1;5;;1;4;;2;3;;1;2';
        break;
        case 1:
            $gameStyle = '1;4;;2;3;;3;2;;4;1';
        break;
        case 2:
            $gameStyle = array();
            $shipsSum = 0;
            $shipsSum += intval($_POST['ship5']);
            $shipsSum += intval($_POST['ship4']);
            $shipsSum += intval($_POST['ship3']);
            $shipsSum += intval($_POST['ship2']);
            $shipsSum += intval($_POST['ship1']);
            if ($shipsSum > 10 || $shipsSum < 0) {
                $_SESSION['error'] = "To więcej niż 10 statków!";
                header("Location: ../gameCreate.php");
                exit();  
            }
            if (intval($_POST['ship5']) > 0) {
                $gameStyle[] = $_POST['ship5'].";5";
            }
            if (intval($_POST['ship4']) > 0) {
                $gameStyle[] = $_POST['ship4'].";4";
            }
            if (intval($_POST['ship3']) > 0) {
                $gameStyle[] = $_POST['ship3'].";3";
            }
            if (intval($_POST['ship2']) > 0) {
                $gameStyle[] = $_POST['ship2'].";2";
            }
            if (intval($_POST['ship1']) > 0) {
                $gameStyle[] = $_POST['ship1'].";1";
            }
            $gameStyle = implode(";;", $gameStyle);
        break;
    }
    $connection = @new mysqli($db_host,$db_user,$db_password,$db_name);
    if ($connection -> connect_errno > 0) {
        $_SESSION['error'] = "Coś się nie udało!";
        header("Location: ../gameCreate.php");
        exit();
    } else {
        $rawPlank = "0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0;0";
        $location;
        $sql = "SELECT id FROM games WHERE BINARY name = ?";
        $stmt = $connection -> prepare($sql);
        $stmt -> bind_param("s", $_POST['name']);
        $stmt -> execute();
        $stmt -> store_result();
        $numRows = $stmt -> num_rows;
        $stmt -> close();
        if ($numRows > 0) {
            $_SESSION['error'] = "Serwer z taką nazwą istnieje!";
            header("Location: ../gameCreate.php");
            exit();
        }
        if ($passwordBool) {
            $sql = "INSERT INTO games (name,password,privacy,shipsP1,shipsP2,gameShips) VALUES (?,?,2,?,?,?)";
            $stmt = $connection -> prepare($sql);
            $stmt -> bind_param("sssss", $_POST['name'], $password,$rawPlank,$rawPlank,$gameStyle);
            $stmt -> execute();
            $stmt -> close();
            $location = "Location: gameJoin.php?name=".$_POST['name']."&password=".$password;
        } else {
            $sql = "INSERT INTO games (name,privacy,shipsP1,shipsP2,gameShips) VALUES (?,1,?,?,?)";
            $stmt = $connection -> prepare($sql);
            $stmt -> bind_param("ssss", $_POST['name'],$rawPlank,$rawPlank,$gameStyle);
            $stmt -> execute();
            $stmt -> close();
            $location = "Location: gameJoin.php?name=".$_POST['name'];
        }
        mysqli_close($connection);
        header($location);
        exit();
    }
    mysqli_close($connection);
?>