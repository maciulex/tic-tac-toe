
function basicLoad() {
    let my = document.querySelector(".myFleet tbody");
    let enemy = document.querySelector(".enemyFleet tbody");
    let raw = "";
    for (let i = 0; i < 10; i++) {
        raw += "<tr>";
        for (let z = 0; z < 10; z++) {
            raw += `<td class="f${i*10+z}" onclick="shoot(${i*10+z})"></td>`;
        }
        raw += "</tr>";
    }
    my.innerHTML = `<tr><td colspan="10" class="headerTable">Twoje Statki</td></tr>`+raw;
    enemy.innerHTML = `<tr><td colspan="10" class="headerTable">Wrogie statki</td></tr>`+raw;
    let squere = document.querySelector(".headerTable").clientWidth;
    let stinki = document.querySelectorAll("tr");
    for (let i = 0; i < stinki.length; i++) {
        stinki[i].style.height = (squere/10)+"px";
    }
}
function engine() {
    let xml = new XMLHttpRequest;
    xml.onreadystatechange = function () {
        if (this.status == 200 && this.readyState == 4) {
            let data = this.responseText.split(";;;");
            if (data[10] == "4") {
                window.location = "postGame.php?serverName="+server;
            }
            loadPlayers(data[5], data[6], data[8]);
            loadAside(data[0], data[1], data[2], data[3], data[4],data[9]);
        }
    }
    xml.open("GET", "app/game/gameEngine.php?action=0", true);
    xml.send();
    function loadPlayers(p1, p2, me) {
        basicLoad();
        me = parseInt(me);
        let my = document.querySelector(".myFleet tbody");
        let enemy = document.querySelector(".enemyFleet tbody");
        let players = [p1.split(";"),p2.split(";")];
        for (let p = 0; p < 2; p++) {
            for (let i = 0; i < 100; i++) {
                switch (players[p][i]) {
                    case "1":
                        if (p == me) {
                            my.rows[Math.floor(i/10)+1].cells[i%10].style.backgroundColor = "black";
                        }
                    break;
                    case "2":
                        if (p == me) {
                            my.rows[Math.floor(i/10)+1].cells[i%10].style.backgroundColor = "grey";
                        } else {
                            enemy.rows[Math.floor(i/10)+1].cells[i%10].style.backgroundColor = "grey";
                        }
                    break;
                    case "3":
                        if (p == me) {
                            my.rows[Math.floor(i/10)+1].cells[i%10].style.backgroundColor = "red";
                        } else {
                            enemy.rows[Math.floor(i/10)+1].cells[i%10].style.backgroundColor = "red";
                        }
                    break;
                }
            }
        }
        
    }
}
function loadAside(serverName, playersNicks, whosTour, timeout, lastAction, timeNow) {
    let timeLeft = (parseInt(timeNow)-parseInt(lastAction));
    let gameInfo = document.querySelector(".gameInfo");
    playersNicks = playersNicks.split(";");
    gameInfo.innerHTML = "<div>Nazwa serwera: "+serverName+"</div>";
    gameInfo.innerHTML += "<div>Gracze: "+playersNicks[0]+", "+playersNicks[1]+"</div>";
    gameInfo.innerHTML += "<div>Tura gracza: "+playersNicks[parseInt(whosTour)]+"</div>";
    gameInfo.innerHTML += "<div>Ostatni ruch: "+timeLeft+"s</div>";
    let infoP = document.querySelector(".blockInfo");
    if (myNick == playersNicks[parseInt(whosTour)]) {
        infoP.style.backgroundColor = "green";
        infoP.innerHTML = "Twoja tura";
    } else {
        infoP.style.backgroundColor = "red";
        infoP.innerHTML = "Poczekaj"; 
    }
    if (timeLeft > 300) {
        gameInfo.innerHTML += `<button onclick="earlyEnd()">Zgłoś przedwczesne zakończenie gry</button>`;
    }
}
function shoot(where) {
    let fild = document.querySelector(`.enemyFleet .f${where}`).style.backgroundColor;
    if (fild != "red" && fild != "grey") {
        let xml = new XMLHttpRequest;
        xml.onreadystatechange = function () {
            if (this.status == 200 && this.readyState == 4) {
                console.log(this.responseText);
                engine();
            }
        }
        xml.open("GET", "app/game/gameEngine.php?action=1&cord="+where, true);
        xml.send();
    }
}

function earlyEnd() {
    let xml = new XMLHttpRequest;
    xml.open("GET", "app/game/gameEngine.php?action=2", true);
    xml.send();
}
engine();
let interval = setInterval(engine, 800);