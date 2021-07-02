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
    $connection = @new mysqli($db_host, $db_user, $db_password, $db_name);
    if ($connection -> connect_errno > 0) {
        $_SESSION['error'] = "Error";
        header('Location: ../mainLogged/index.php');
        exit();
    } else {
        $page = "buildFleet";
        include_once "app/imGame.php";
        $inGame;
        $sql = "SELECT inGame FROM users WHERE BINARY nickname = BINARY ?";
        $stmt = $connection -> prepare($sql);
        $stmt -> bind_param("s", $_SESSION['nickname']);
        $stmt -> execute();
        $stmt -> store_result();
        $stmt -> bind_result($inGame);
        $stmt -> fetch();
        if (empty($inGame) || !isset($inGame) || intval($inGame) == 0 || $inGame == "") {
            $_SESSION['error'] = "Error";
            header('Location: ../mainLogged/index.php');
            $stmt -> close();
            mysqli_close($connection);
            exit();
        }
        $stmt -> close();
        $readyShips = "[";
        $sql = "SELECT gameShips FROM games WHERE id = ?";
        $stmt = $connection -> prepare($sql);
        $stmt -> bind_param("i", $inGame);
        $stmt -> execute();
        $stmt -> store_result();
        $stmt -> bind_result($gameShips);
        $stmt -> fetch();
        $gameShips = explode(";;", $gameShips);
        $clipboard = array();
        foreach ($gameShips as $key) {
            $key = explode(";",$key);
            for ($i = 0; $i < intval($key[0]); $i++) {
                $clipboard[] = "[".$key[1].",0,-1,-1]";
            }
        }
        $readyShips.=implode(",",$clipboard);
        $readyShips .= "]";
        $stmt -> close();
        mysqli_close($connection);
    }
?>
<!DOCTYPE html>
<html lang="pl">
    <head>
        <title>Statki</title>
        <meta charset="utf-8">
        <link href="../styles/game/style.css" rel="stylesheet">
        <script>
            let shipsData = <?php echo $readyShips;?>; 
        </script>
        <script>var motiveAccess = <?php  echo ((isset($_COOKIE['motive'])) ? $_COOKIE['motive'] : 0); ?>;</script>
        <script src="../mainApp.js"></script>
        <script src="app/queue/app.js"></script>
        <script src="app/queue/fleetEvents.js"></script>

    </head>
    <body>
        <header class="noSelectText">
            <a href="../user/indexManagment/logout.php"><button>Wyloguj</button></a>
            <section class="gameReturnBlock">
            </section>
            <a href="../mainLogged/profil.php" class="right"><button>Profil</button></a>
            <a href="../mainLogged/index.php" class="right" style="width:75px"><button>Lista gier</button></a>
        </header>    
        <aside class="asideBuild">
            Instrukcja:<br>
            Kliknij na statek<br>
            Najedź na miejsce gdzie statek ma być<br>
            Jak potrzebujesz go obrócić kliknij "R"<br>
            Żeby bloczek się odwrócił bądź zaktualizował trzeba poruszyć myszką<br>
            Kliknij by postawić statek <br>
            Jak chcesz zmienić miejsce statku kliknij go na planszy<br><br>

            Jak ułożysz wszystkie statki poniżej pojawi się guzik walidacja<br>
            musisz go kliknąć wyżej powinnien pojawić się rezultat który powinnien zawierać "Poprawna walidacja"<br>
            jeżeli tego nie zawiera masz źle ustawioną albo coś źle działa <br><br>

            Jak zmienisz układ statków po walidacji pamiętaj by ponownie ją zwalidować<br>
            Walidacja oznacza jestem gotów a pierwsza zakceptowana flota będzie twoja <br>
            chyba że zdążysz ją zmienić i zwalidować<br>
            <div class="validation">
                <div class="result"> 

                </div>
                <div class="doVal">

                </div>
                <div class="fastEnd">

                </div>
            </div>
        </aside>
        <section class="buildFleet noSelectText">
            <main>

            </main>
        </section>
        <div class="changeMotive" onclick="changeMotive()"> 
        </div>
        <script>
            buildEngine(0);
            let interval = setInterval(getReadyPlayers, 1000);
            setMotive();
        </script>
    </body>
    <div id="pickedUp"></div>
</html>