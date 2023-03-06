
<?php
    function betolt_adat($fajlnev) {
        $s = file_get_contents($fajlnev);
        return json_decode($s, true);
    }

    function elment_adat($fajlnev, $adat) {
        $s = json_encode($adat);
        file_put_contents($fajlnev, $s);
    }

    $users = betolt_adat("users.json");
    $polls = betolt_adat('polls.json');

    session_start();

    $hibak = array();
    $siker = false;
    if (isset($_POST['name'])) {
        $voted = array();
        $nev = trim($_POST['name']);
        $quest = trim($_POST['quest']);
        $datenow = date("Y-m-d");
        if ($nev == '') {
            $hibak[] = 'Név kötelező';
        }else if(array_key_exists($nev,$polls)){
            $hibak[] = 'Ilyen nevű szavazás már van';
        }
        if ($quest == '') {
            $hibak[] = 'Kérdés kötelező';
        }
        if (!isset($_POST['multi'])){
            $hibak[] = 'Igen/nem kötelező';
        }else{
            $multi = $_POST['multi'];
        }
        if(!isset($_POST['date'])){
            $hibak[] = 'Határidő kötelező';
        }else if(!preg_match("/^((((19|[2-9]\d)\d{2})\-(0[13578]|1[02])\-(0[1-9]|[12]\d|3[01]))|(((19|[2-9]\d)\d{2})\-(0[13456789]|1[012])\-(0[1-9]|[12]\d|30))|(((19|[2-9]\d)\d{2})\-02\-(0[1-9]|1\d|2[0-8]))|(((1[6-9]|[2-9]\d)(0[48]|[2468][048]|[13579][26])|((16|[2468][048]|[3579][26])00))\-02\-29))$/",$_POST['date'])){
            $hibak[] = 'Határidő nincs vagy rossz formátum';
        }else{
            $date = $_POST['date'];
        }
        if (!isset($_POST['answer'])){
            $hibak[] = 'Nincsenek válaszok megadva!';
        }else if (!$_POST['answer']){
            $hibak[] = 'Nincsenek válaszok megadva!';
        }else if (sizeof($_POST['answer']) != $_GET['range']){
            $hibak[] = 'Nem adtál meg minden választ!';
        }else{
            $ans = array();
            foreach($_POST['answer'] as $answer){
                $ans[$answer] = 0;
            }
        }
        if (!$hibak) {
            global $polls;
            $polls[$nev] = array(
                'id' => $nev,
                'question' => $quest,
                'isMultiple' => filter_var($multi, FILTER_VALIDATE_BOOLEAN),
                'createdAt' => $datenow,
                'deadline' => $date,
                'answers' => $ans,
                'voted' => $voted
            );

            elment_adat('polls.json', $polls);
            $siker = true;
        }
    }

    if(isset($_POST['delete'])){
        unset($polls[$_POST['deleteid']]);
        elment_adat('polls.json', $polls);
    }
    
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poll.com/pollmake</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1 style="width: 10%;">
            Szavazáskészítő
        </h1>
        <div>
        Felhasználó:
                <div>
                    <?php if(isset($_SESSION['id'])){ echo $users[$_SESSION['id']]['username'];} else {echo "Vendég";} ?>
                </div>
                <a href="votemake.php" <?php if(isset($_SESSION['id'])){ if($users[$_SESSION['id']]['isAdmin'] == false){ echo 'hidden = "true"'; }}else{echo 'hidden = "true"';}  ?>>
                Szavazásszerkesztő
                </a>
        </div>
</header>

<div style="margin: auto; height: auto; border: 2px solid black; background-color: white; width: 80%; padding: 2%; margin-top: 5%; justify-content:space-around; align-items: center;">         
    <form action="" method="get" novalidate>
        <h3 style="text-align:center;">Új szavazás készítése</h3>
        <div style="padding:1%;">
            Válaszok száma: 2 <input type="range" min="2" max = "8" value = <?php if(isset($_GET['range'])){echo '"'.$_GET['range'].'"';}else{echo "2";} ?> name="range"> 8
            <input type="submit" value="Válaszok" name="ans">
        </div>
    </form>

    <form action="" method="post" novalidate style="display:flex;" >
        <div style="padding:1%; width:50%;">
            <div style="padding:1%;">
                <label for="name">Szavazás neve:</label>
                <input type="text" id="name" name="name" value="">
            </div>
            <div style="padding:1%;">
                <label for="quest">Kérdés:</label>
                <input type="text" id="quest" name="quest" value="">
            </div>
            <div style="padding:1%;">
                Több válaszlehetőség:
                <label for="True">Igen</label>
                <input type="radio" name="multi" value="true" id="True">
                <label for="False">Nem</label>
                <input type="radio" name="multi" value="false" id="False">
            </div>
            <div style="padding:1%;">
                <label for="date">Határidő:</label>
                <input type="text" id="date" name="date" value="" placeholder="YYYY-MM-DD">
            </div>
            <div style="padding:1%;">
                <button type="submit">Létrehozás</button>
            </div>
            <div style="margin:2%;">
                <?php foreach($hibak as $hiba): ?>
                    <div style="padding: 1%; color:red">
                        <?php echo $hiba; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <div style="padding:1%; width:50%;">
            <?php 
            if(isset($_GET['ans'])){
                $range = $_GET['range'];
            }else{
                $range=0;
            }
            ?>
            <?php
            for($i=1;$i<=$range;$i++): ?>
                <div style="padding:1%;">
                    <label for=<?php echo '"'.$i.'"'; ?>>Válasz <?php echo $i; ?>.:</label>
                    <input type="text" name="answer[]" id=<?php echo '"'.$i.'"'; ?>>
                </div>
            <?php endfor; ?>
        </div>
    </form>
    <?php if($siker): ?>
            <div style="font-size: x-large;">
                Sikeres új szavazás indult!
            </div>
    <?php endif; ?>
    <form action="mainpage.php" style="text-align:center; margin:2%;">
            <input type="submit" value="Vissza a főoldalra">
    </form>
</div>
<div style="margin: auto; height: auto; border: 2px solid black; background-color: white; width: 80%; padding: 2%; margin-top: 5%; text-align:center; align-items:center;">
    <h3>Szavazás törlése</h3>
        <?php foreach($polls as $pollid => $poll): ?>
            <form action="" method="post" novalidate style="padding: 2%;">    
                <div style="font-size:x-large; display:inline">
                <?php echo $pollid; ?> törlése: 
                </div>
                <input type="submit" value="Törlés" name="delete" style="font-size: larger;" >
                <input type="text" value=<?php echo '"'.$pollid.'"' ?> name="deleteid" hidden = "true">
            </form>
        <?php endforeach; ?>
    <form action="mainpage.php" style="text-align:center; margin:2%;">
        <input type="submit" value="Vissza a főoldalra">
    </form>
</div>

</body>
</html>