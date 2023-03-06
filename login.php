
<?php
function betolt_adat($fajlnev) {
    $s = file_get_contents($fajlnev);
    return json_decode($s, true);
}

function elment_adat($fajlnev, $adat) {
    $s = json_encode($adat);
    file_put_contents($fajlnev, $s);
}
    session_start();

    $users = betolt_adat("users.json");

    $hibalogin = true;

    if ($_POST) {
        
        if(isset($_POST['login'])){

        $loginname =  $_POST['loginname'];
        $loginpw = $_POST['loginpw'];

        $userid = 0;
        foreach($users as $id => $user){
            if($user['username'] == $loginname && $user['password'] == $loginpw){
                $hibalogin = false;
                $userid = $id;
            }
        }

        if (!$hibalogin) {
            $_SESSION['id'] = $userid;
            }
        }
    }

    $hibak = array();
    if($_POST){
        
        if (isset($_POST['regist'])){

            $username = trim($_POST['username']);
            $email = trim($_POST['email']);
            $pw1 = trim($_POST['password']);
            $pw2 = trim($_POST['password2']);

            if ($username == '') {
                $hibak[] = 'Felhasználónév kötelező';
            }
            if ($email == '') {
                $hibak[] = 'E-mail kötelező';
            }else if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
                $hibak[] = 'E-mail invalid';
            }
            if ($pw1 == '') {
                $hibak[] = 'Jelszó kötelező';
            }
            if ($pw2 == '') {
                $hibak[] = 'Jelszó megismétlése kötelező';
            }
            if(!$hibak){
                if($pw1 != $pw2){
                    $hibak[] = 'Jelszók nem egyeznek';
                }
                $lastid = 0;
                foreach($users as $id => $user){
                    if($user['username'] == $username){
                        $hibak[] = 'Ilyen felhasználónév már létezik';
                        
                    }
                    $lastid = $id;
                }
                $newid = $lastid + 1;
                if(!$hibak) {
                    $users[$newid] = array(
                        'id' => $newid,
                        'username' => $username,
                        'isAdmin' => filter_var('false', FILTER_VALIDATE_BOOLEAN),
                        'email' => $email,
                        'password' => $pw1
                    );
                    elment_adat('users.json', $users);
                    $_SESSION['id'] = $userid;
                }
            }
        }
    }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header>
            <h1  style="width: 30%;">
                Regisztráció vagy Belépés
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
        <div style="justify-content:space-around; align-items: center; display:flex;">
            <form action="" method="post" style="border-right: 2px solid black; width:50%; padding:5%" novalidate>
            <h3>Bejelentkezés</h3>
                <div <?php if(isset($_POST['login']) && !$hibalogin){echo 'hidden = "true"';} ?>>
                    <div style="padding:1%;">
                        <label for="loginname">Felhasználónév:</label>
                        <input type="text" id="loginname" name="loginname">
                    </div>
                    <div style="padding:1%;">
                        <label for="loginpw">Jelszó:</label>
                        <input type="text" id="loginpw" name="loginpw">
                    </div>
                    <div style="padding:1%;">
                        <input type="submit" id="login" name="login" value="Login">
                    </div>
                </div>
                <div style="padding:1%;" <?php if(!(isset($_POST['login']))){echo 'hidden = "true"';} ?>>
                    <?php if(!$hibalogin){echo 'Sikeres bejelentkezés!';}else{echo 'Felhasználónév vagy jelszó hibás!';} ?>    
                </div>
            </form>


            <form action="" method="post" style="width:50%; padding:5%" novalidate>
            <h3>Regisztráció</h3>
            <div <?php if(isset($_POST['regist']) && !$hibak){echo 'hidden = "true"';} ?>>
                <div style="padding:1%;">
                    <label for="username">Felhasználónév:</label>
                    <input type="text" name="username" id="username">
                </div>
                <div style="padding:1%;">
                    <label for="email">E-mail:</label>
                    <input type="text" name="email" id="email">
                </div>
                <div style="padding:1%;">
                    <label for="password">Jelszó:</label>
                    <input type="text" name="password" id="password">
                </div>
                <div style="padding:1%;">
                    <label for="password2">Jelszó mégegyszer:</label>
                    <input type="text" name="password2" id="password2">
                </div>
                <div style="padding:1%;">
                    <input type="submit" id="regist" name="regist" value="Regisztráció">
                </div>
            </div>
            <div style="padding:1%;" <?php if(!(isset($_POST['regist']))){echo 'hidden = "true"';} ?>>
                <?php if(!$hibak) : ?>
                    <?php echo 'Sikeres regisztráció!'; ?>
                <?php else: ?>
                    <?php foreach($hibak as $hiba) : ?>
                        <div>
                            <?php echo $hiba; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            </form>
        </div>
        <form action="mainpage.php" style="text-align:center; margin:2%;" novalidate>
            <input type="submit" value="Vissza a főoldalra">
        </form>
    </div>
</body>
</html>