<?php
ini_set('display_errors', 1);

if(!empty($_POST['data'])){
    $json = $_POST['data'];

    $data_decoded = json_decode($json);
    $recipe_name = $data_decoded->name;
    $file1 = "data/".$recipe_name.".txt";
    $file2 = "data/".$recipe_name.".pdf";

    if (!file_exists($file1)){
        file_put_contents($file1, "");
    }
    if (!file_exists($file2)){
        file_put_contents($file2, "");
    }

    $file = fopen($file1, 'r+');
    file_put_contents($file1, base64_decode($json, TRUE));

    $pdf = $data_decoded->pdf;
    file_put_contents($file2, base64_decode($pdf, TRUE));

    if ($data_decoded->request == "send_mail"){
        $message = "python3 lib/export_mail.py \"";
        $message .= "Im Anhang findest du ein Rezept.\" ";
        $message .= "\"".$data_decoded->mail."\" ";
        $message .= "\"".$recipe_name."\"";
        $command = escapeshellcmd($message);
        echo $command;
        shell_exec($command);
    }
} else {
    echo "No Data Sent";
}

//send Types: send Push, send mail(adress), export pdf

?>
