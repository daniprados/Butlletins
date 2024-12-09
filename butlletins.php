<?php

$verbose = false;
if($argc > 2 && $argv[2] == "-v"){
    $verbose = true;
}

if(!checkIfPdftkIsInstalled()){
    echo "Error: pdftk no està instal·lat.\n";
    exit(-1);
}

if(!checkIfPdftotextIsInstalled()){
    echo "Error: pdftotext no està instal·lat.\n";
    exit(-1);
}


if($argc > 1 && $argv[1] == "-h" || $argc == 1){
    echo "Crea els butlletins de cada alumne a la carpeta Butlletins/[nom del butlletí]\n";
    echo "Usage: php butlletins.php [-c] [nom del mòdul] [-v]\n\n";
    echo "Si s'indica el nom del butlletí, es creen el butlletí de cada alumne a la carpeta Butlletins/[nom del butlletí]\n";
    echo "-c, es copien tots els butlletins a la carpeta amb el nom del cicle i nivell (AiF 2A, DAW 2A...).\n";
    echo "-v, es mostra el procés de creació dels butlletins.\n";
    echo "-h, es mostra aquesta ajuda.\n\n";
    echo "Exemple: php butlletins.php 1DAW \n";
    echo "Extreu els butlletins de 1DAW.pdf a la carpeta Butlletins/1DAW\n\n";
    echo "Exemple: php butlletins.php -c\n";
    echo "Copia tots els butlletins a la carpeta Butlletins/{nom del cicle - nivell}\n\n";
    echo "Aquest script necessita el pdftk i pdftotxt instal·lats.\n";
    echo "Credits: Dani Prados - Institut Cendrassos - MIT License\n";
    exit(1);
}

/* Si hi ha menys de dos parametres copies els butlletins a la carpeta corresponent, així els tutors tindran el llistat complet */
if($argc > 1 && $argv[1] == "-c"){
    $butlletins = glob("*.pdf");
    foreach($butlletins as $butlleti){
        $t = explode(".", $butlleti);
        $folder = "Butlletins/". $t[0] . "/";
        @mkdir($folder);
        echo "cp \"{$butlleti}\" \"{$folder}{$butlleti}\"\n";
        exec("cp \"{$butlleti}\" \"{$folder}{$butlleti}\"\n");
    }
    exit(1);
}

/* Recuperem els noms dels alumnes del SAGA,  així podem fer els fitxers amb el format cognom1 cognom2, nom */
if(!file_exists("saga.csv")){
    echo "Error: No s'ha trobat el fitxer saga.csv\n";
    exit(-1);
}
$saga = fopen("saga.csv", "r"); 
$alumnes2 = [];
$data = fgetcsv($saga, 1000, ",");
while (($data = fgetcsv($saga, 1000, ",")) !== FALSE) {
    if(isset($data[19]) && str_starts_with($data[19], $argv["1"])){
        $nomArray = explode(",", $data[2]);
        // Formatem el nom com surt al butlletí, així el podrem trobar.
        $nom = trim($nomArray[1]) . " " . trim($nomArray[0]);
        $data[] = $nom;
        $alumnes2[$nom] = $data[2];
    }
}

if($verbose){
    print_r($alumnes2);
}

$i = 1;


$butlleti = "{$argv[1]}.pdf";
if(!file_exists($butlleti)){
    echo "Error: No s'ha trobat el fitxer {$butlleti}\n";
    exit(-1);
}
if($verbose){
    echo "cp \"{$butlleti}\" \"temp/{$butlleti}\"\n";
}
exec("cp \"{$butlleti}\" \"temp/{$butlleti}\"\n");
exec("cd temp && pdftk \"{$butlleti}\" burst ");
$num = paginesPDF($argv[1]);
$alumnes = [];
for($i = 1; $i <= $num; $i++){
    $page = str_pad($i, 4, "0", STR_PAD_LEFT);
    if($verbose){
        echo "pdftotext -layout  temp/pg_{$page}.pdf temp/pg_{$page}.txt";
    }
    exec("pdftotext -layout  temp/pg_{$page}.pdf temp/pg_{$page}.txt");
    $nom = exec("awk '/Alumne/{getline; print}' temp/pg_{$page}.txt");
    $nom = explode("  ", $nom);
    $regex="/[0-9]{8}[A-Z]/";
    $nom = trim(preg_replace($regex, "", $nom[0]));
    if($nom == ""){
        continue;
    }
    $index = isset($alumnes2[$nom]) ? $alumnes2[$nom] : $nom . "(no trobat)";
    if(!isset($alumnes[$index])){
        $alumnes[$index] = [];
    }
    $alumnes[$index][] = $i;
    if($verbose){
        echo "Alumne: {$nom} - {$index} - {$i}\n";
    }  
}
exec("rm temp/*");


$folder = "Butlletins/".$argv["1"] . "/";
$numAlumnes = count($alumnes);
$k = 1;
@mkdir($folder);
foreach($alumnes as $alumne => $pagines){
    echo "($k de {$numAlumnes}) - Generant el butlletí de {$alumne}  \n";
    $file =  eliminar_acentos($alumne);
    $i = $pagines[0];
    $j = $pagines[count($pagines) - 1];
    if($verbose){
        echo "pdftk {$argv[1]}.pdf cat {$i}-{$j} output \"{$folder}{$file}.pdf\"\n";
    }
    exec("pdftk \"{$argv[1]}.pdf\" cat {$i}-{$j} output \"{$folder}{$file}.pdf\"");
    $k++;
}


function paginesPDF($nom) {
    //echo "pdftk \"{$nom}.pdf\" dump_data | grep NumberOfPages";
    $str = exec("pdftk \"{$nom}.pdf\" dump_data | grep NumberOfPages");
    $str = explode(": ", $str);
    return $str[1];
}

function checkIfPdftkIsInstalled(){
    $str = exec("pdftk --version | head -n 1");
    if(str_starts_with($str, "pdftk")){
        return true;
    }
    return false;
}

function checkIfPdftotextIsInstalled(){
    $str = exec("pdftotext -v 2>&1  | head -n 1");   // 2>&1  redirigeix stderr a stdout
    if(str_starts_with($str, "pdftotext")){
        return true;
    }
    return false;
}



function eliminar_acentos($cadena){
    //Reemplazamos la A y a
    $cadena = str_replace(
    array('Á', 'À', 'Â', 'Ä', 'á', 'à', 'ä', 'â', 'ª'),
    array('A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a'),
    $cadena
    );

    //Reemplazamos la E y e
    $cadena = str_replace(
    array('É', 'È', 'Ê', 'Ë', 'é', 'è', 'ë', 'ê'),
    array('E', 'E', 'E', 'E', 'e', 'e', 'e', 'e'),
    $cadena );

    //Reemplazamos la I y i
    $cadena = str_replace(
    array('Í', 'Ì', 'Ï', 'Î', 'í', 'ì', 'ï', 'î'),
    array('I', 'I', 'I', 'I', 'i', 'i', 'i', 'i'),
    $cadena );

    //Reemplazamos la O y o
    $cadena = str_replace(
    array('Ó', 'Ò', 'Ö', 'Ô', 'ó', 'ò', 'ö', 'ô'),
    array('O', 'O', 'O', 'O', 'o', 'o', 'o', 'o'),
    $cadena );

    //Reemplazamos la U y u
    $cadena = str_replace(
    array('Ú', 'Ù', 'Û', 'Ü', 'ú', 'ù', 'ü', 'û'),
    array('U', 'U', 'U', 'U', 'u', 'u', 'u', 'u'),
    $cadena );

    //Reemplazamos la N, n, C y c
    $cadena = str_replace(
    array('Ñ', 'ñ', 'Ç', 'ç'),
    array('N', 'n', 'C', 'c'),
    $cadena
    );
    
    return $cadena;
}