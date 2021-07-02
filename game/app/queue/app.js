var status;
var host = false;
var playersNumber = 0;
var size;
// czy jest coś złapane który statek który blok statku rotacja długość statku
var track = [false,0,0,0,0];
let width;
//element x y width height
let elementB = [null,null,null,null,null];
let lastMove = 0;
let mouseClickHelper = false;
let setShips = 0;
function queueEngine(arg) {
    function xmlEngine() {
        var xml = new XMLHttpRequest;
        xml.onreadystatechange = function () {
            if (this.readyState == 4 && this.status == 200) {
                if (this.responseText[0] == "e" && this.responseText[4] == "r") {
                    alert("wystąpił błąd ");
                    return;
                }
                switch (arg) {
                    case 0:
                        dealData(this.responseText);
                    break;
                    case 1:
                        window.location = "../mainLogged/";
                    break;
                }
            }
        }
        xml.open("GET", "app/queue/queueEngine.php?action="+arg, true);
        xml.send();
    }
    function dealData(arg) {
        arg = arg.split(";;;");
        gameLoad(arg[0]);
        playerLoad(arg[1]);
        if (host) {
            hostLoad();
        }
        if (status == "5") {
            document.querySelector("#throwOut").parentNode.innerHTML="";
        }
    }
    function playerLoad(data) {
        let iStillHERE = false;
        let place = document.querySelector(".mainQueue main");
        let kick = "";
        place.innerHTML = "";
        data = data.split(";;");
        playersNumber = data.length-1;
        for (var i = 0; i < data.length-1; i++) {
            let localData = data[i].split(";"); 
            if (localData[0] == "true") {
                if (i == 0) {
                    host = true;
                }
                iStillHERE = true;
                kick = `<button onclick="queueEngine(1)">Opuść grę</button>`;
            } else if (host && localData[0] == "false") {
                kick = `<button onclick="queueEngine(2)" id="throwOut">Wyrzuć gracza</button>`;
            }
            if (localData[6] == "false") {
                var avatar = "def.jpg"; 
            } else {
                var avatar = localData[6]; 
            }
            let raw = `
                <div class="player">
                    <section class="header">
                        <img src="../photos/avatars/${avatar}">
                        <section>
                            <h1>${localData[1]} <br> <div>${kick}</div></h1>
                            <div>
                                Opis gracza: ${localData[2]}
                            </div>
                        </section>
                    </section>
                    <section class="stats">
                        <div>Rozegrane: <br> ${localData[3]}</div><div>Wygrane: <br> ${localData[4]}</div><div>Przegrane: <br> ${localData[5]}</div>
                    </section>
                </div>
            `;
            place.innerHTML += raw; 
        }
        if (!iStillHERE) {
            window.location = "../mainLogged/";
        }
    }
    function gameLoad(data) {
        data = data.split(";");
        status = data[1];
        if (data[1] == "2") {
            window.location = "buildFleet.php";
        } else if (data[1] == "3") {
            window.location = "battleField.php";
        } 
        let place = document.querySelector(".mainQueue aside");
        place.innerHTML = `
            <br><br>
            Nazwa gry: ${data[0]}<br>
            Prywatność: ${getPrivacy(data[2])}<br>
            Status: ${getStatus(data[1])}<br>
            Graczy: ${data[3]}/2<br>
            Host: ${data[4]}<br>
            <div class="hostOption"></div>
        `;
        function getStatus(arg) {
            if (playersNumber == 2 && arg == '1') {
                return "Oczekiwanie na rozpoczęcie przez hosta";
            }
            switch (arg) {
                case '1':
                    return "Nie rozpoczęta";
                case '2':
                    return "Rozpoczęta";
                case '3':
                    return "W trakcie przygotowań";   
                case '4':
                    return "Zakończona";
                case '5':
                    return "Poczekalnia rewanżu";
            }
        }
        function getPrivacy(arg) {
            switch (arg) {
                case '1':
                    return "Publiczna";
                case '2':
                    return "Nie publiczna";
            }
        }
    }
    function hostLoad() {
        if (playersNumber == 2) {
            document.querySelector(".hostOption").innerHTML = `<button onclick="queueEngine(3)">Rozpocznij grę</button>`;
        }
    }
    xmlEngine();
}

function buildEngine(arg) {
    //length, rotation | x, y if null == -1 
    function buildBoard(x,y) {
        size = [x,y];
        let place = document.querySelector(".buildFleet");
        let raw = "";
        for (let i = 0; i < 20; i++) {
            raw += "<tr>";
            for (let z = 0; z < 20; z++) {
                raw += "<td></td>";
            }
            raw += "</tr>";
        }
        place.innerHTML = `<table id="freeShips"><tbody>${raw}</tbody></table>`;
        raw = "";
        for (let i = 0; i < x; i++) {
            raw += "<tr>";
            for (let z = 0; z < y; z++) {
                raw += "<td></td>";
            }
            raw += "</tr>";
        }
        place.innerHTML += `<table id="busyShips"><tbody>${raw}</tbody></table>`;

    }
    function buildShips() {
        let freeShips = document.querySelector("#freeShips");
        let busyShips = document.querySelector("#busyShips");
        for (let i = 0; i < shipsData.length; i++) {
            if (shipsData[i][2] == -1) {
                for (let z = 0; z < shipsData[i][0]; z++) {
                    freeShips.rows[i*2].cells[19-z].classList.add("ship", "Ship"+i, "No"+z);
                    freeShips.rows[i*2].cells[19-z].setAttribute("onclick", `shipClicked(${i}, ${z})`);
                }
            } else {
                
            }
        }
    }
    switch (arg) {
        case 0: 
            buildBoard(10,10);
            buildShips();
        break;
    }
}
let lastCords = [0,0];

function deleteShip(arg) {
    let shipPlaced = document.querySelectorAll(".shipNo"+arg);
    let shipBuild = document.querySelectorAll(".ship.Ship"+arg);
    for (let i = 0; i < shipPlaced.length; i++) {
        shipPlaced[i].removeAttribute("class");
        shipPlaced[i].removeAttribute("onclick");
        shipBuild[i].classList.remove("itsPlaced");
    }
    setShips--;
    document.querySelector(".doVal").innerHTML = ``;
}
function shipClicked(which, block) {
    if (!track[0]) {
        let elements = document.querySelectorAll(".ship.Ship"+which);
        if (elements[0].classList.contains("itsPlaced")) {
            return;
        }
        track[0] = true;
        track[1] = which;
        track[2] = block;
        let pickedRaw = "";
        for (let i = 0; i < elements.length; i++) {
            elements[i].classList.add("pickedUp");
            pickedRaw += "<td></td>";
            track[4] += 1;
        }
        width = document.querySelector("#busyShips tr td").offsetWidth;
        document.querySelector("#pickedUp").innerHTML = `<table cellspacing="0" cellpadding="0"><tbody><tr>${pickedRaw}</tr></tbody></table>`;
        let pickedUp = document.querySelectorAll("#pickedUp tr td");
        for (let i = 0; i < pickedUp.length; i++) {
            pickedUp[i].style.width = width+"px";
            pickedUp[i].style.height = width+"px";
        }
        elementB[0] = document.querySelector("#busyShips")
        elementB[1] = elementB[0].offsetLeft;
        elementB[2] = elementB[0].offsetTop;
        elementB[3] = elementB[0].offsetWidth;
        elementB[4] = elementB[0].offsetHeight;
        document.querySelector("#pickedUp").classList.remove("displayOff");
    }
}
function shipsValidation() {
    let shipS = [];
    for (let i = 0; i < shipsData.length; i++) {
        let ship = document.querySelectorAll(".shipNo"+i);
        let mystr = [];
        for (let z = 0; z < ship.length; z++) {
            mystr.push((ship[z].cellIndex+";"+ship[z].parentNode.rowIndex));
        }
        mystr= mystr.join(";;");
        shipS.push(mystr);
    }
    shipS = shipS.join(";;;");
    var xml = new XMLHttpRequest;
    xml.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            alert(this.responseText);
        }
    }
    xml.open("GET", "app/queue/validateShips.php?data="+shipS, true);
    xml.send();
}

function getReadyPlayers() {
    let xml = new XMLHttpRequest;
    xml.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            let data = this.responseText.split(";");
            let players = data[0];
            document.querySelector(".gameReturnBlock").innerHTML = "Gotowych graczy: "+players+"/2";
            if (players == "2") {
                window.location = "battleField.php";
            }
            if (parseInt(data[1])+300 < parseInt(data[2])) {
                document.querySelector(".fastEnd").innerHTML = `<button onclick="earlyEnd()">Zgłoś przedwczesne zakończenie gry</button>`;
            } else {
                document.querySelector(".fastEnd").innerHTML = "";
            }
        } 
    }
    xml.open("GET", "app/queue/queueEngine.php?action=4", true);
    xml.send();
}
function earlyEnd() {
    let xml = new XMLHttpRequest;
    xml.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            console.log(this.responseText);
        } 
    }
    xml.open("GET", "app/game/gameEngine.php?action=2", true);
    xml.send();
}