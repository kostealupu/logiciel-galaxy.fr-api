<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once("/var/www/kostealupu/data/www/test.smartmozg.com/wp-load.php");
global $wpdb;
$table_name = $wpdb->prefix.'cursvalid';

//$wpdb->query("TRUNCATE TABLE $table_name");
$wpdb->query( $wpdb->prepare( "UPDATE $table_name SET formare_conf = %d", 0) );

function funcurl($metod, $stag)
{

    $parameters = array(
        "method" => $metod,
        "apikey_" => "30644818731052372180653039218066590",
        "stagiaire_id" => $stag,
        "choix_fonction" => $metod
    );

    $parameters = array_change_key_case($parameters);
    $request_headers = array();
    $request_headers[] = 'Accept: application/octet-stream';
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_HTTPHEADER, $request_headers);

    curl_setopt($curl, CURLOPT_HTTPHEADER, array(
        "Content-type: multipart/form-data"
    ));
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_URL, "https://www.logiciel-galaxy.fr/works/api.php");

    curl_setopt($curl, CURLOPT_POSTFIELDS, flatten_GP_array($parameters));
    $response = curl_exec($curl);
    $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    $error = curl_error($curl);
    curl_close($curl);
    $json_objekat = json_decode($response);
    $quotes = $json_objekat->result;
    return $quotes;
}

function flatten_GP_array(array $var, $prefix = false)
{
    $return = array();
    foreach ($var as $idx => $value)
    {
        if (is_scalar($value))
        {
            if ($prefix)
            {
                $return[$prefix . '[' . $idx . ']'] = $value;
            }
            else
            {
                $return[$idx] = $value;
            }
        }
        else
        {
            $return = array_merge($return, flatten_GP_array($value, $prefix ? $prefix . '[' . $idx . ']' : $idx));
        }
    }
    return $return;
}

$quotes = funcurl("get_stagiaires", 0); //toti clientii 

foreach ($quotes as $intKey => $objQuote)
{
    //echo $objQuote->id       . '<br>';
    // echo $objQuote->mail_pro       . '-<br>';
    $quotes = funcurl("get_historique_formation_sta", $objQuote->id); //clienti din forma

    foreach ($quotes as $intKey => $objQuote)
    {
        //echo $objQuote->base_client_id      . '<br>';
        //echo $objQuote->formation_id       . '<br>';
        //echo $objQuote->etat_ins       . '<br>';
        $idclient = $objQuote->base_client_id; //id client din forma
        $idform = $objQuote->formation_id; 
        if ($objQuote->etat_ins == "Confirmé") //vaildat sau nu
        {
            $activ = 1;
        }
        else
        {
            $activ = 0;
        }
        ////
        $quotes = funcurl("get_formations", 0);

        foreach ($quotes as $intKey => $objQuote)
        {

            if ($idform == $objQuote->formation_id)
            {
                $prodid = $objQuote->produit_id; //id curs 
                $formid = $objQuote->formation_id; 
                $namepr = $objQuote->nom_produit; //nume curs 
            }
        }
        ///////////////
        $quotes = funcurl("get_stagiaires", 0);

        foreach ($quotes as $intKey => $objQuote)
        {


            //$unicid = $idclient.''.$formid.'0000';
            if ($idclient == $objQuote->id)
           {

                //echo $objQuote->id       . '/<br>';
                $maile = $objQuote->mail_pro;
                $namest = $objQuote->nom;

     //1
               $sel = "SELECT 1 from $table_name WHERE client_id='$idclient' AND formare_id='$formid'";
                $res = $wpdb->get_results($sel);  

if (count($res) == 0) {

$result = $wpdb->insert(
            $table_name,
            array(
                'client_id'     => $idclient,  
                'client_email'    => $maile,     
                'formare_id'   => $formid,            
                'formare_conf'   => $activ,            
                'prod_id'        => $prodid,             
                'prod_name'  => $namepr
                 ),
      array( '%d', '%s', '%d',  '%d',   '%d',   '%s')
     );

if($result){

$ids = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_title = '".$namepr."'");
$link = get_permalink($ids);
$elink = $link;

  $exists = email_exists($maile);
  if (!$exists && ($activ==1)) {
$passw=wp_generate_password( 6, true );
wp_insert_user( array(
  'user_login' => $maile,
  'user_pass' => $passw,
  'user_email' => $maile,
  'first_name' => $namest,
  'display_name' => $namest,
  'role' => 'customer'
));



$message = "Acum puteti cumpara cursul: ".$namepr."\r\n";
$message .= "Link: ". $elink ."\r\n";
$message .= "Login: ".$maile."\r\n";
$message .= "Pass: ".$passw;
wp_mail( $maile, 'Accueil - Academie Anais Abaakil', $message );
 
  } else if ($exists && ($activ==1)) {

$message = "Acum puteti cumpara cursul: ".$namepr."\r\n";
$message .= "Link: ". $elink ;
wp_mail( $maile, 'Accueil - Academie Anais Abaakil', $message );

  }
  
}

} else {

/*
$resconf = $wpdb->get_var("SELECT formare_conf FROM $table_name WHERE client_id='$idclient' AND formare_id='$formid'");
$conf = $resconf;

if (($conf==0) && ($activ==1)) {

$ids = $wpdb->get_var("SELECT ID FROM $wpdb->posts WHERE post_title = '".$namepr."'");
$link = get_permalink($ids);
$elink = $link;

$exists = email_exists($maile);

if (!$exists && ($activ==1)) {

$passw=wp_generate_password( 6, true );
wp_insert_user( array(
  'user_login' => $maile,
  'user_pass' => $passw,
  'user_email' => $maile,
  'first_name' => $namest,
  'display_name' => $namest,
  'role' => 'customer'
));   

$message = "Acum puteti cumpara cursul: ".$namepr."\r\n";
$message .= "Link: ". $elink ."\r\n";
$message .= "Login: ".$maile."\r\n";
$message .= "Pass: ".$passw;
wp_mail( $maile, 'Accueil - Academie Anais Abaakil', $message );
 
} else if ($exists && ($activ==1)) {

$message = "Acum puteti cumpara cursul: ".$namepr."\r\n";
$message .= "Link: ". $elink ;
wp_mail( $maile, 'Accueil - Academie Anais Abaakil', $message );

  }
}

$wpdb->query( $wpdb->prepare( "UPDATE $table_name SET formare_conf = %d WHERE client_id = %d AND formare_id=%d", 0, $maile, $formid) );*/

                 $wpdb->query( $wpdb->prepare( "UPDATE $table_name SET formare_conf = %d, client_email = %s WHERE client_id = %d AND formare_id=%d", $activ, $maile, $idclient, $formid) );
            
      
                  }
            } 
        }

    }

}
