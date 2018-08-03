<?php
/**
 * User: Lucie
 * Date: 15/01/2017
 * Time: 18:54
 */
?>

<html>
<head>
    <title>SkyRankHotel</title>
    <link rel="stylesheet" href="css.css">
    <link href='http://fonts.googleapis.com/css?family=OSWALD' rel='stylesheet' type='text/css'>
    <meta charset="UTF-8">
</head>
<body>
    <h1>SKYRANK HOTEL</h1>

    <form action="resultat.php" method="post">
        <p>Vos préférences</p>
        <label for="prix">Prix</label>
        <input id="prix" type="text" name="Prix">
        <select name="pprix">
            <option>MIN
            <option>MAX
        </select><br>
        <label for="distance">Distance</label>
        <input id="distance" type="text" name="Distance">
        <select name="pdistance">
            <option>MIN
            <option>MAX
        </select><br>
        <label for="etoile">Nombre d'étoiles</label>
        <input id="etoile" type="text" name="Etoile">
        <select name="petoile">
            <option>MIN
            <option>MAX
        </select><br>

        <button type="submit" name="valider">Valider</button>
    </form>
</body>
</html>
