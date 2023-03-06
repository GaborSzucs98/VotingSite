
<?php
    function betolt_adat($fajlnev) {
        $s =file_get_contents($fajlnev);
        return json_decode($s, true);
    }

    function elment_adat($fajlnev, $adat) {
        $s = json_encode($adat);
        file_put_contents($fajlnev, $s);
    }

    session_start();
    $polls = array_reverse(betolt_adat("polls.json"));
    $users = betolt_adat("users.json");

    $aktivpolls = array();
    foreach($polls as $pollid => $poll){
        if($poll["deadline"] >= date("Y-m-d"))
        $aktivpolls[$pollid] = $poll;
    }
    $oldpolls = array();
    foreach($polls as $pollid => $poll){
        if($poll["deadline"] < date("Y-m-d"))
        $oldpolls[$pollid] = $poll;
    }

    if(isset($_POST['logout'])){
        session_destroy();
        header("Refresh:0");
        exit();
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poll.com</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
            <h1  style="width: 10%;">
                Főoldal
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
            <div style="text-align: left;">
                <div <?php if(isset($_SESSION['id'])){echo 'hidden = "true"';}  ?>>
                    <form action="login.php">
                        <input type="submit" value="Belépés" id="login">
                    </form>
                </div>
                <div>
                    <form action="" method="post" novalidate>
                        <input type="submit" value="Kilépés" id="logout" name="logout">
                    </form>
                </div>
            </div>
    </header>
    <div style="margin: auto; background-color:burlywood; width: 70%; height: auto; padding: 10px;">

        <div style="text-align: center; margin: 10px; font-size: xx-large; border-bottom: 1px solid black;">
            Aktív szavazások
        </div>
        <?php foreach($aktivpolls as $pollid => $poll) : ?>
                <div class="polls">
                    <h1>
                    Id: <?php echo $pollid ?>
                    </h1>
                    <div>
                        <div>
                            Szavazás kezdete: <?php echo $poll["createdAt"] ?>
                        </div>
                        <div>
                            Szavazás vége: <?php echo $poll["deadline"] ?>
                        </div>
                    </div>
                    <form action=<?php if(isset($_SESSION['id'])){echo "votepage.php";} else {echo "login.php";}  ?>  method="post" novalidate <?php if($poll["deadline"] < date("Y-m-d")){echo 'hidden = "true"';} ?>>
                        <input type="submit" value=<?php if (isset($_SESSION['id'])){if(in_array($users[$_SESSION['id']]['username'],$poll['voted'])){echo "Szavaztál";}else{echo "Szavazok";}}else{echo "Szavazok";}?> name="vote" style="font-size: larger;" >
                        <input type="text" value=<?php echo '"'.$pollid.'"' ?> name="voteid" hidden = "true">
                    </form>
                </div>
            <?php endforeach; ?>

            <div style="text-align: center; margin: 10px; font-size: xx-large; border-bottom: 1px solid black;">
                Lejárt szavazások
            </div>

            <?php foreach($oldpolls as $pollid => $poll) : ?>
                <div class="polls">
                    <h1>
                    Id: <?php echo $pollid ?>
                    </h1>
                    <div>
                        <div>
                            Szavazás kezdete: <?php echo $poll["createdAt"] ?>
                        </div>
                        <div>
                            Szavazás vége: <?php echo $poll["deadline"] ?>
                        </div>
                    </div>
                    <?php 
                    $ans = $poll['answers'];
                    if($poll["deadline"] < date("Y-m-d")) : ?>
                    <div class = "ans" style="display:block;">
                        <?php foreach($ans as $key => $value) :?>
                            <div>
                            <?php echo $key.": ".$value ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
    </div>
</body>
</html>