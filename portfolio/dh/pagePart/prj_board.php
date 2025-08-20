<div class="row">
<?php 
$ftp_server = "nasjackleo.myqnapcloud.com";
$ftp_user_name = "GiacomoGuaresi";
$ftp_user_pass = "8v6bzlyoko";

// set up basic connection
$conn_id = ftp_connect($ftp_server);

// login with username and password
$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);

// get contents of the current directory
$contents = ftp_nlist($conn_id, "./Progetti Condivisi");
$localId = 0;
foreach($contents as $prj){
    if(!strrpos($prj,"@")){
        $prj_contents = ftp_nlist($conn_id, $prj); // elenco file 
        // var_dump($prj_contents);

        if (in_array($prj.'/manifest.txt', $prj_contents)) 
        {
            $h = tmpfile();
            ftp_fget($conn_id, $h, $prj.'/manifest.txt', FTP_BINARY);
            
            
            $contentManifest = parse_ini_file(stream_get_meta_data($h)['uri'],true);
            
                                    
            ?>
            <div class="col-lg-4">
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <?php 
                            echo("<span class='m-0 font-weight-bold text-primary'>".$contentManifest["Propietà"]["Nome"]." ");
                            if(isset($contentManifest["Propietà"]["Versione"])) echo("<span class='badge badge-dark'>".$contentManifest["Propietà"]["Versione"]."</span> ");
                            

                            // WIP      <- success
                            // Hold     <- warning
                            // CLose    <- dark
                            // Critical <- danger
                            // Open     <- info
                            // [other]  <- secondary
                            
                            switch($contentManifest["Propietà"]["Stato"]){
                                case "WIP": 
                                    $badgeType = "success"; 
                                    break; 
                                case "Hold": 
                                    $badgeType = "warning"; 
                                    break; 
                                case "CLose": 
                                    $badgeType = "dark"; 
                                    break; 
                                case "Critical": 
                                    $badgeType = "danger"; 
                                    break; 
                                case "Open": 
                                    $badgeType = "info"; 
                                    break; 
                                default:
                                    $badgeType = "secondary"; 
                                    break; 
                            }

                            if(isset($contentManifest["Propietà"]["Stato"])) echo("<span class='badge badge-".$badgeType."'>".$contentManifest["Propietà"]["Stato"]."</span> ");
                            echo("</span>");
                        
                        ?>
                        </span> - 
                        <span class="m-0 font-weight-bold"><?php echo($contentManifest["Propietà"]["Data"]); ?></span>
                        <button class="float-sm-right btn btn-primary" type="button" data-toggle="collapse" data-target="#collapsePrj_<?php echo($localId); ?>" aria-expanded="false" aria-controls="collapsePrj_<?php echo($localId); ?>">
                            <i class="far fa-eye"></i>
                        </button>
                    </div>
                    <div class="collapse" id="collapsePrj_<?php echo($localId); ?>">
                        <div class="card-body">
                        <p><?php echo($contentManifest["Propietà"]["Descrizione"]); ?></p>
                        <?php 
                            if(isset($contentManifest["Liste"]))
                                foreach($contentManifest["Liste"] as $key => $lista){
                                    echo ('<h6>'.$key.'</h6>');
                                    echo ('<ul class="list-group">');
                                    foreach($lista as $elemento){
                                        echo('<li class="list-group-item">'.$elemento.'</li>');
                                    }
                                    echo ('</ul><hr>');
                                }  
                        ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php 
            fclose($h);
            $localId++; 
        }    

        echo("<hr>");
    }
}

// close connection
ftp_close($conn_id);
?>
</div>

<script>
$(function() {
   console.log("Loading prj_board Scripts");
});
</script>