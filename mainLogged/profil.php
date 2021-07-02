<?php
    include_once "../base.php";
    $mainIndexPath = "../index.php";
    session_start();
    if (!isset($_SESSION['nickname']) || !isset($_SESSION['authCode'])) {
        session_destroy();
        header("Location: ../index.php");
        exit();
    } else {
        @include_once "../user/loggedCheck.php";
    }
    $connection = new mysqli($db_host, $db_user, $db_password, $db_name);
?>
<!DOCTYPE html>
<html lang="pl">
    <head>
        <title>Statki</title>
        <meta charset="utf-8">
        <link href="../styles/gameList/style.css" rel="stylesheet">
        <script>var motiveAccess = <?php  echo ((isset($_COOKIE['motive'])) ? $_COOKIE['motive'] : 0); ?>;</script>
        <script src="mainApp.js"></script>
    </head>
    <body>
        <header>
            <a href="../user/indexManagment/logout.php"><button>Wyloguj</button></a>
            <?php
                $sql = "SELECT inGame FROM users WHERE BINARY nickname = BINARY ?";
                $stmt = $connection -> prepare($sql);
                $stmt -> bind_param("s", $_SESSION['nickname']);
                $stmt -> execute();
                $stmt -> store_result();
                $stmt -> bind_result($inGame);
                $stmt -> fetch();
                if (intval($inGame) != 0) {
                    $sql = "SELECT status, players FROM games WHERE BINARY name = BINARY ?";
                    $stmt2 = $connection -> prepare($sql);
                    $stmt2 -> bind_param("s", $_SESSION['serverName']);
                    $stmt2 -> execute();
                    $stmt2 -> store_result();
                    $stmt2 -> bind_result($status, $players);
                    $stmt2 -> fetch();
                    switch (intval($status)) {
                        case 1:
                            $status = "W poczekalni";
                        break;
                        case 2:
                            $status = "Budowa floty";
                        break;
                        case 3:
                            $status = "W grzę";
                        break;
                    }
                    echo '
                    <section class="gameReturnBlock">
                        <div>Nazwa servera: </div>
                        <div>'.$_SESSION["serverName"].'</div>
                        <div> | </div>
                        <div>Graczy: '.$players.'/2</div>
                        <div> | </div>
                        <div>Status: '.$status.'</div>
                        <div> | </div>
                        <button onclick=\'location="../game/gameQueue.php"\'>Powróć</button>
                    </section>
                    ';
                    $stmt2 -> close();
                }
                $stmt -> close();
            ?>
            <a href="index.php" class="right" style="width:75px"><button>Lista gier</button></a>
            <a href="gameCreate.php" class="right" style="width:75px"><button>Stwórz grę</button></a>
        </header>  
        <section class="mainProfil">
            <main class="mainProfilContent">
                <h1>
                    Profil gracza: <?php echo $_SESSION['nickname'];?>
                </h1>
                <hr>
                <?php 
                    $sql = "SELECT avatar FROM users WHERE nickname = ?";
                    $stmt = $connection -> prepare($sql);
                    $stmt -> bind_param("s", $_SESSION['nickname']);
                    $stmt -> execute();
                    $stmt -> store_result();
                    $stmt -> bind_result($avatar);
                    $stmt -> fetch();
                    if ($avatar != "" && !empty($avatar)) {
                        echo '<img src="../photos/avatars/'.$avatar.'">';
                    } else {
                        echo '<img src="../photos/avatars/def.jpg">';
                    }
                    $stmt -> close();
                ?>
                <button onclick="changeTo(1)">Edycja profilu</button>

            </main>
            <main class="mainProfilEdit noDisplay">
                <h1>
                    Edycja profilu gracza: <?php echo $_SESSION['nickname'];?>
                </h1>
                <hr>
                <form method="POST" action="app/updateProfil.php" enctype="multipart/form-data">
                    <label for="avatar">
                        Profilowe może mieć rozmiar do 2mb i rozszerzenie jpg/png<br>
                        Zalecane proporcje: 1x szerokości do 1.5x wysokości 
                        <br>
                        Profilowe: 
                    </label>
                    <input type="file" name="avatar" id="avatar">
                    <br>
                    <input type="submit">
                </form>

                <button onclick="changeTo(2)">Powrót do profilu</button>
            </main>
        </section>
        <div class="changeMotive" onclick="changeMotive()"> 
            <img src="photos/ico/sun-solid.svg">
        </div>
        <script>
            let error = <?php echo ((isset($_SESSION['error'])) ? '"'.$_SESSION['error'].'"' : "undefined");?>;
            if (error != undefined) {
                alert(error);
            }
            function changeTo(arg) {
                switch (arg) {
                    case 1:
                        document.querySelector(".mainProfilContent").classList.add("noDisplay");
                        document.querySelector(".mainProfilEdit").classList.remove("noDisplay");
                    break;
                    case 2:
                        document.querySelector(".mainProfilEdit").classList.add("noDisplay");
                        document.querySelector(".mainProfilContent").classList.remove("noDisplay");
                    break;
                }
            }
            setMotive();
        </script>
    </body>
</html> 
<?php
    if (isset($_SESSION['error'])) {
        unset($_SESSION['error']);
    }
    mysqli_close($connection);
?>