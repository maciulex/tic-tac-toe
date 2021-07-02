<?php
    session_start();
    if (!isset($_SESSION['nickname']) || !isset($_SESSION['authCode'])) {
        session_destroy();
        header("Location: ../../index.php");
        exit();
    } else {
        @include_once "../../user/loggedCheck.php";
    }
    if (!isset($_SESSION['serverName']) || !isset($_SESSION['serverName']) || !isset($_GET['action'])) {
        echo "error 1";
        exit();
    }
    $action = intval($_GET['action']);
    include_once "../../../base.php";
    $connection = new mysqli($db_host, $db_user, $db_password, $db_name);
    if ($connection -> connect_errno > 0) {
        echo "error";
        exit();
    }
    switch ($action) {
        case 0:
            //getGameData
            $sql = "SELECT name, playersNicks, whosTour, timeout, lastAction, shipsP1, shipsP2, gameShips, status FROM games WHERE name = ?";
            $stmt = $connection -> prepare($sql);
            $stmt -> bind_param("s", $_SESSION['serverName']);
            $stmt -> execute();
            $stmt -> store_result();
            $stmt -> bind_result($name, $playersNicks, $whosTour, $timeout, $lastAction, $shipsP1, $shipsP2, $gameShips, $status);
            $stmt -> fetch();
            $let = explode(";",$playersNicks);
            $me;
            if ($let[0] != $_SESSION['nickname']) {
                $shipsP1 = str_replace("1","0", $shipsP1);
            } else {
                $me = 0;
            }
            if ($let[1] != $_SESSION['nickname']) {
                $shipsP2 = str_replace("1","0", $shipsP2);
            } else {
                $me = 1;
            }
            echo $name.";;;".$playersNicks.";;;".$whosTour.";;;".$timeout.";;;".$lastAction.";;;".$shipsP1.";;;".$shipsP2.";;;".$gameShips.";;;".$me.";;;".time().";;;".$status;
        break;
        case 1:
            //shoting
            $cord = intval($_GET['cord']);
            if (!empty($cord) && $cord > -1 && $cord < 100) {
                $playersNicks; $whosTour; $ships = array(); $status; $score;
                $sql = "SELECT playersNicks, whosTour, shipsP1, shipsP2, status, score FROM games WHERE name = ?";
                $stmt = $connection -> prepare($sql);
                $stmt -> bind_param("s", $_SESSION['serverName']);
                $stmt -> execute();
                $stmt -> store_result();
                $stmt -> bind_result($playersNicks, $whosTour, $ships[0], $ships[1], $status, $score);
                $stmt -> fetch();
                $stmt-> close();
                if ($status == "3") {
                    $playersNicks = explode(";",$playersNicks);
                    if ($playersNicks[$whosTour] == $_SESSION["nickname"]) {
                        $target;
                        switch ($whosTour) {
                            case "0":
                                $target = 1;
                            break;
                            case "1":
                                $target = 0;
                            break;
                        }
                        $hit = false;
                        $ships[$target] = explode(";",$ships[$target]);
                        if ($ships[$target][$cord] == "1") {
                            $ships[$target][$cord] = 3;
                            $hit = true;
                        } else if ($ships[$target][$cord] == "0") {
                            $ships[$target][$cord] = 2;
                        }
                        $gameEnd = true;
                        foreach ($ships[$target] as $key) {
                            if ($key == "1") {
                                $gameEnd = false;
                            }
                        }
                        $ships[$target] = implode(";",$ships[$target]);
                        $dateNow = time();
                        if (!$hit) {
                            if ($whosTour == "0") {
                                $whosTour = "1";
                            } else {
                                $whosTour = "0";
                            }
                        }
                        $sql = "UPDATE games SET shipsP1 = ?, shipsP2 = ?, lastAction = ?, whosTour = ? WHERE name = ?";
                        $stmt = $connection -> prepare($sql);
                        $stmt -> bind_param("ssdss", $ships[0],$ships[1],$dateNow,$whosTour,$_SESSION['serverName']);
                        $stmt -> execute();
                        $stmt-> close();
                        if ($gameEnd) {
                            $score = explode(";", $score);
                            if ($playersNicks[0] == $_SESSION["nickname"]){
                                $score[0] = intval($score[0])+1;
                            } else {
                                $score[1] = intval($score[1])+1;
                            }
                            $score = implode(";", $score);
                            $stats = array(0,0);
                            $winMess = "Wygrał gracz ".$playersNicks[intval($whosTour)];
                            if ($whosTour == "0") {
                                $stats[0] = 1;
                                $stats[1] = 0;
                            } else {
                                $stats[0] = 0;
                                $stats[1] = 1;
                            }
                            $sql = "UPDATE games SET status = 4, gameEnd = ?, score = ? WHERE name = ?";
                            $stmt = $connection -> prepare($sql);
                            $stmt -> bind_param("sss", $winMess, $score, $_SESSION['serverName']);
                            $stmt -> execute();
                            $stmt -> close();
                            $sql = "UPDATE users SET inGame = 0, Sgames = Sgames + 1, SgamesWin = SgamesWin + ?, SgamesLose = SgamesLose + ? WHERE nickname = ?";
                            $stmt = $connection -> prepare($sql);
                            $stmt -> bind_param("iis", $stats[0], $stats[1], $playersNicks[0]);
                            $stmt -> execute();
                            $stmt -> bind_param("iis", $stats[1], $stats[0], $playersNicks[1]);
                            $stmt -> execute();
                            $stmt -> close();
                        }
                    }
                }
            }
        break;
        case 2:
            //early end >:
            echo"WORKING";
            $lastAction; $playersNicks; $status;
            $sql = "SELECT lastAction, playersNicks, status FROM games WHERE name = ?";
            $stmt = $connection -> prepare($sql);
            $stmt -> bind_param("s", $_SESSION["serverName"]);
            $stmt -> execute();
            $stmt -> store_result();
            $rows = $stmt->num_rows;
            $stmt -> bind_result($lastAction, $playersNicks, $status);
            $stmt -> fetch();
            $stmt -> close();
            if (intval($lastAction)+300 < time() && $status != "4" && $status != "1") {
                if ($rows == 1) {
                    $playersNicks = explode(";", $playersNicks);
                    foreach ($playersNicks as $key) {
                        if ($key == $_SESSION["nickname"]) {
                            $sql = "UPDATE users SET SgamesAbound = SgamesAbound + 1 WHERE nickname = ?";
                            $stmt = $connection -> prepare($sql);
                            $stmt -> bind_param("s", $key);
                            $stmt -> execute();
                            $stmt -> close();
                        } else {
                            $sql = "UPDATE users SET  SgamesEarlyEnd = SgamesEarlyEnd + 1 WHERE nickname = ?";
                            $stmt = $connection -> prepare($sql);
                            $stmt -> bind_param("s", $key);
                            $stmt -> execute();
                            $stmt -> close();
                        }
                    }
                    $sql = 'UPDATE games SET status = 4, gameEnd = CONCAT("Po 5 min bezczynności gra zakończona za życzenie gracza: ", ?) WHERE name = ?';
                    $stmt = $connection -> prepare($sql);
                    $stmt -> bind_param("ss", $_SESSION['nickname'], $_SESSION['serverName']);
                    $stmt -> execute();
                    $stmt -> close();
                    $stats = array(0,1);
                    $sql = "UPDATE users SET inGame = 0, SgamesAbound = SgamesAbound + ?, SgamesEarlyEnd = SgamesEarlyEnd + ? WHERE nickname = ?";
                    $stmt = $connection -> prepare($sql);
                    foreach ($playersNicks as $key) {
                        if ($key == $_SESSION["nickname"]) {
                            $stmt -> bind_param("iis", $stats[0], $stats[1], $key);
                            $stmt -> execute();
                        } else {
                            $stmt -> bind_param("iis", $stats[1], $stats[0], $key);
                            $stmt -> execute();
                        }
                    }
                    $stmt -> close();
                }
            }
        break;
    }
    mysqli_close($connection);
?>