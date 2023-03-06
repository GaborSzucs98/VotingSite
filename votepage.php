
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

    session_start();

    $pollid = $_POST['voteid'];
    $polls = betolt_adat("polls.json");
    $poll = $polls[$pollid];
    $error = "";

    if(!isset($_POST['voted'])){
        $voted = false;
    }
    else{
        if (!isset($_POST['answer'])) {
            $error = 'Még nem adtál választ!';
            $voted = false;
        } else{
            $voted = true;
            vote();
        }
    }

    function vote(){
        global $poll;
        global $polls;
        global $pollid;
        global $users;

        $currentvotes = $poll['answers'];
        $newvotes = $_POST['answer'];

        foreach($newvotes as $vote){
            if (array_key_exists($vote, $currentvotes)){
                $newvote = $currentvotes[$vote] + 1;
                $currentvotes[$vote] = $newvote;
            }else{
                $currentvotes[$vote] = 1;
            }
        }
        
        $polls[$pollid]['answers'] = $currentvotes;
        $polls[$pollid]['voted'][] = $users[$_SESSION['id']]['username'];
        elment_adat("polls.json", $polls);

    }


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Poll.com/poll</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
        <h1 style="width: 10%;">
            Szavazás
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

    
    <div style="margin: auto; height: auto; border: 2px solid black; background-color: white; width: 80%; padding: 2%; margin-top: 5%;">
        <div style="display: flex; justify-content:space-around; align-items: center;">
            <h1>
                Id: <?php echo $pollid ?>
            </h1>
            <div style="font-size: large;">
                <div>
                    Szavazás kezdete: <?php echo $poll["createdAt"] ?>
                </div>
                <div>
                    Szavazás vége: <?php echo $poll["deadline"] ?>
                </div>
            </div>
        </div>
        <div style="margin: auto; height: auto; font-size: x-large; text-align:center; padding: 2%;">
            <?php echo $poll["question"] ?>
        </div>
        <div class="ans">
        <?php if(!$voted) : ?>
            <?php if ($poll['isMultiple']) : ?>
                <form action="" method="post" novalidate class="ans">
                    <?php foreach($poll["answers"] as $ans => $x): ?>
                        <div style="padding: 5%; display:flex;">
                        <input type="checkbox" id=<?php echo '"'.$ans.'"' ?> name="answer[]" value=<?php echo '"'.$ans.'"' ?>>
                        <label for=<?php echo '"'.$ans.'"' ?>><?php echo $ans ?></label>
                        </div>
                    <?php endforeach; ?>
                    <input type="submit" value="Szavazok" name="vote" style="font-size: larger; margin: 5%;" >
                    <input type="text" value=<?php echo '"'.$pollid.'"' ?> name="voteid" hidden = "true">
                    <input type="text" value="" name="voted" hidden = "true">
                </form>
            <?php else: ?>
                <form action="" method="post" novalidate class="ans">
                    <?php foreach($poll["answers"] as $ans => $x): ?>
                        <div style="padding: 5%; display:flex;">
                        <input type="radio" id=<?php echo '"'.$ans.'"' ?> name="answer[]" value=<?php echo '"'.$ans.'"' ?>>
                        <label for=<?php echo '"'.$ans.'"' ?>><?php echo $ans ?></label>
                        </div>
                    <?php endforeach; ?>
                    <input type="submit" value="Szavazok" name="vote" style="font-size: larger; margin: 5%;" >
                    <input type="text" value=<?php echo '"'.$pollid.'"' ?> name="voteid" hidden = "true">
                    <input type="text" value="" name="voted" hidden = "true">
                </form>
            <?php endif; ?>
        <?php else: ?>
            <div style="font-size: x-large;">
                Szavazatod sikeresen feldolgoztuk.
            </div>
        <?php endif; ?>
        </div>
        <div style="font-size: x-large; text-align:center">
                <?php echo $error?>
        </div>
        <form action="mainpage.php" style="text-align:center; margin:2%;">
            <input type="submit" value="Vissza a főoldalra">
        </form>
    </div>
</body>
</html>