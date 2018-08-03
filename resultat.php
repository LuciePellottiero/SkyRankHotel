<?php
// Permet d'afficher les erreurs
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Infos de la BD
$dbName = "";
$userName = "";
$pwd = "";

/**
 * Permet de créer un objet PDO et de le connecter a la BD. besoin de l'executer qu'une fois (comme un constructeur)
 * @param $dbName String Le nom de la BD
 * @param $userName String Le nom d'utilisateur
 * @param $pwd String Le mot de passe
 * @return PDO L'objet qui permet de gérer la connexion PDO
 */
function initConnection($dbName, $userName, $pwd) {
    try {
        // host correspond à l'adresse du serveur mysql
        $dns = '' . $dbName;
        $pdo = new PDO($dns, $userName, $pwd);
        $pdo->exec('SET CHARACTER SET utf8');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    }
    catch (PDOException $e) {
        die('Error : ' . $e->getMessage());
    }
}

/**
 * Permet d'executer un script SQL.
 * @param $pdo PDO Le gestionnaire PDO.
 * @param $sql String La requete SQL.
 * @return PDOStatement null if failed.
 */
function execQuery($pdo, $sql) {
    if (!$sql || !$pdo) {
        return null;
    }
    else {
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt;
    }
}



$pdo = initConnection($dbName, $userName, $pwd);

$priceWeight    = $_POST["Prix"];
$pricePref      = $_POST["pprix"];
$distanceWeight = $_POST["Distance"];
$distancePref   = $_POST["pdistance"];
$nbEtWeight     = $_POST["Etoile"];
$nbEtPref       = $_POST["petoile"];

$sommePoids = $priceWeight + $distanceWeight + $nbEtWeight;

if($sommePoids != 1){
    echo "<p>Erreur : la somme des poids n'est pas égale à 1</p>";
    exit;
}

$priceOrder;
$distOrder;
$nbEtOrder;

if ($pricePref == "MAX") {
    $priceOrder = '>';
}
else {
    $priceOrder = '<';
}
if ($distancePref == "MAX") {
    $distOrder = '>';
}
else {
    $distOrder = '<';
}
if ($nbEtPref == "MAX") {
    $nbEtOrder = '>';
}
else {
    $nbEtOrder = '<';
}

$sqlFirst = "CREATE OR REPLACE VIEW HOTEL_SKY 
AS
SELECT 	IdH, prix, distance, NbEt
FROM 	HOTEL h1
WHERE 	NOT EXISTS 
(
	SELECT	*
	FROM	HOTEL h2
	WHERE	h2.prix $priceOrder= h1.prix
	AND		h2.distance $distOrder= h1.distance
	AND		h2.NbEt $nbEtOrder= h1.NbEt
	AND 
	(
			h2.prix $priceOrder h1.prix
		OR 	h2.distance $distOrder h1.distance
		OR 	h2.NbEt $nbEtOrder h1.NbEt
	)
)
;";

$stmt = execQuery($pdo, $sqlFirst);
//$res = $stmt->fetch(PDO::FETCH_OBJ);

$sqlMinMax = "CREATE OR REPLACE VIEW MIN_MAX
AS
SELECT $pricePref(prix) Pref_Prix, $distancePref(distance) Pref_Distance, $nbEtPref(NbEt) Pref_NbEt
FROM HOTEL_SKY
;";

$stmt = execQuery($pdo, $sqlMinMax);
//$res = $stmt->fetch(PDO::FETCH_OBJ);

$sqlNorm = "CREATE OR REPLACE VIEW HOTEL_NORM
AS
SELECT 	HS.IdH
		,
		(MM.Pref_Prix/HS.prix) Prix_Norm
		,
		(MM.Pref_Distance/HS.distance) Distance_Norm
		,
		(HS.NbEt/MM.Pref_NbEt) NbEt_Norm
FROM HOTEL_SKY HS, MIN_MAX MM
;
";

$stmt = execQuery($pdo, $sqlNorm);
//$res = $stmt->fetch(PDO::FETCH_OBJ);

$sqlPond = "CREATE OR REPLACE VIEW HOTEL_POND
AS
SELECT 	IdH
		,
		($priceWeight*Prix_Norm) Prix_Pond
		,
		($distanceWeight*Distance_Norm) Distance_Pond
		,
		($nbEtWeight*NbEt_Norm) NbEt_Pond
FROM HOTEL_NORM
;";

$stmt = execQuery($pdo, $sqlPond);
//$res = $stmt->fetch(PDO::FETCH_OBJ);

$sqlScore = "CREATE OR REPLACE VIEW HOTEL_SCORE
AS
SELECT 	IdH
		,
		Prix_Pond
		,
		Distance_Pond
		,
		NbEt_Pond
		,
		(Prix_Pond+Distance_Pond+NbEt_Pond) Score
FROM HOTEL_POND
;";

$stmt = execQuery($pdo, $sqlScore);
//$res = $stmt->fetch(PDO::FETCH_OBJ);

$sql = "SELECT H.IdH, H.prix, H.distance, H.NbEt, S.Score
        FROM HOTEL H, HOTEL_SCORE S
        WHERE H.IdH = S.IdH
        ORDER BY S.Score Desc";
$stmt = execQuery($pdo, $sql);

echo "<h1>SKYRANK HOTEL</h1>\n";
echo "<p>Les meilleurs hotels pour vous :</p>\n";
echo "<table>\n";
echo "    <tr>\n";
echo "        <th>IdH</th>\n";
echo "        <th>Prix</th>\n";
echo "        <th>Distance</th>\n";
echo "        <th>NbEt</th>\n";
echo "        <th>Score</th>\n";
echo "    </tr>\n";

while ($res = $stmt->fetch(PDO::FETCH_OBJ)) {
    echo "    <tr>\n";
    echo "        <td>" . $res->IdH . "</td>\n";
    echo "        <td>" . $res->prix . "</td>\n";
    echo "        <td>" . $res->distance . "</td>\n";
    echo "        <td>" . $res->NbEt . "</td>\n";
    echo "        <td>" . $res->Score . "</td>\n";
    echo "    </tr>\n";
}

echo "</table>\n";

?>

<link rel="stylesheet" href="css.css">
