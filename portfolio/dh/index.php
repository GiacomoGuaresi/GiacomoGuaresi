<?php 
    session_start();
    if($_SESSION["username"] == NULL){
      header( "Location: login.php" );
      exit ;
    }


    $cookie_name = "user";
    $cookie_value = $_SESSION["username"];
    setcookie($cookie_name, $cookie_value,  time() + (10 * 365 * 24 * 60 * 60), "/"); // 86400 = 1 day

    include_once("php/connection.php");
?>

<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">


  <meta name="msapplication-TileImage" content="http://www.leonardosantambrogio.com/wp-content/uploads/2018/07/cropped-icona-270x270.png">
  <link rel="apple-touch-icon-precomposed" href="http://www.leonardosantambrogio.com/wp-content/uploads/2018/07/cropped-icona-180x180.png">
  <link rel="icon" href="http://www.leonardosantambrogio.com/wp-content/uploads/2018/07/cropped-icona-192x192.png" sizes="192x192">
  <link rel="icon" href="http://www.leonardosantambrogio.com/wp-content/uploads/2018/07/cropped-icona-32x32.png" sizes="32x32">


  <title>SB Admin 2 - Dashboard</title>

  <!-- Custom fonts for this template-->
  <link href="vendor/fontawesome-free/css/all.css" rel="stylesheet" type="text/css">
  <link
    href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
    rel="stylesheet">

  <!-- Custom styles for this template-->

  <?php 
    $currentTime = (new DateTime('01:00'))->modify('+1 day');
    $startTime = new DateTime('22:00');
    $endTime = (new DateTime('07:00'))->modify('+1 day');
    
    if (isset($_COOKIE["mode"]) && $_COOKIE["mode"] == "dark")
      echo '<link href="css/sb-admin-2-dark.css" rel="stylesheet">';
    else 
      echo '<link href="css/sb-admin-2.css" rel="stylesheet">';
  
  ?>

    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>


    <!-- define functionStack -->
    <script>
        var functionStack = []; 
    </script>

    <!-- Page level plugins -->
    <script src="vendor/chart.js/Chart.min.js"></script>

    <!-- Page level custom scripts -->
    <script src="js/demo/chart-area-demo.js"></script>


    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.js"></script>
</head>

<body id="page-top">

  <!-- Page Wrapper -->
  <div id="wrapper">

    <!-- Content Wrapper -->
    <div id="content-wrapper" class="d-flex flex-column">

      <!-- Main Content -->
      <div id="content">

        <!-- Topbar -->
        <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">

          <!-- Topbar Navbar -->
          <ul class="navbar-nav ml-auto">

            <!-- Nav Item - Alerts -->
            <li class="nav-item dropdown no-arrow mx-1">
              <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <i class="fas fa-bell fa-fw"></i>

                <!-- Counter - Alerts -->
                <?php 
                  $sql = "SELECT * FROM `Events` WHERE `ack` = 0";
                  $res = $mysqli->query($sql);
                  if(mysqli_num_rows($res) > 0)
                    echo '<span class="badge badge-danger badge-counter">'.mysqli_num_rows($res).'</span>';
                ?>

              </a>
              <!-- Dropdown - Alerts -->
              <div class="dropdown-list dropdown-menu dropdown-menu-right shadow animated--grow-in"
                aria-labelledby="alertsDropdown">
                <h6 class="dropdown-header">
                  Alerts Center
                </h6>

                <?php 
                  $sql = "SELECT * FROM `Events` WHERE `ack` = 0";
                  $res = $mysqli->query($sql);
                  while($row = $res->fetch_assoc()){
                    ?>
                <a class="dropdown-item d-flex align-items-center" href="php/ack.php?id=<?php echo($row["ID"]);?>">
                  <div class="mr-3">
                    <div class="icon-circle bg-warning">
                      <i class="fas fa-exclamation-triangle text-white"></i>
                    </div>
                  </div>
                  <div>
                    <div class="small text-gray-500"><?php echo($row["data"]);?></div>
                    <?php echo($row["descrizione"]);?>
                  </div>
                </a>
                <?php
                  }
                ?>





                <a class="dropdown-item text-center small text-gray-500" href="alertCenter.php">Mostra Tutti (WIP)</a>
                <a class="dropdown-item text-center small text-gray-500" href="php/ack.php?all">ACk tutti</a>
              </div>
            </li>

            <div class="topbar-divider d-none d-sm-block"></div>

            <!-- Nav Item - User Information -->
            <li class="nav-item dropdown no-arrow">
              <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown"
                aria-haspopup="true" aria-expanded="false">
                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $_SESSION["username"]?></span>
                <img class="img-profile rounded-circle"
                  src="https://www.klrealty.com.au/wp-content/uploads/2018/11/user-image-.png">
              </a>
              <!-- Dropdown - User Information -->
              <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                <a class="dropdown-item" href="#">
                  <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                  Profilo (WIP)
                </a>

                <?php 
                if(isset($_GET["edit"])){
                ?>
                  <a class="dropdown-item" href=".">
                    <i class="fas fa-save fa-sm fa-fw mr-2 text-gray-400"></i>
                    Esci Modifica
                  </a>
                <?php 
                }
                else{
                ?>
                  <a class="dropdown-item" href="?edit">
                    <i class="fas fa-edit fa-sm fa-fw mr-2 text-gray-400"></i>
                    Imposta Pannelli
                  </a>
                <?php 
                }
                ?>


                <a class="dropdown-item" href="php/mode.php">
                  <i class="fas fa-adjust fa-sm fa-fw mr-2 text-gray-400"></i>

                  Dark/Light mode
                </a>

                <a class="dropdown-item" href="#">
                  <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                  Logs (WIP)
                </a>
                <div class="dropdown-divider"></div>
                <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                  <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                  Logout
                </a>
              </div>
            </li>

          </ul>

        </nav>
        <!-- End of Topbar -->

        <!-- Begin Page Content -->
        <div class="container-fluid">

          <!-- Page Heading -->
          <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
          </div>

          <?php 
            $sql = "SELECT * FROM `pagePart` Left join users on pagePart.user = users.id WHERE `username` = '".$_SESSION["username"]."' order by `orderNumber`";
            $res = $mysqli->query($sql);
            while($row = $res->fetch_assoc()){
              if(isset($_GET["edit"])){
                ?>
                <!-- Content Row -->
                <div class="row">
                    <a href="php/movePagePart.php?id=<?php echo($row["orderNumber"]); ?>&action=mup" class="btn"><i class="fas fa-arrow-up"></i></a> 
                    <a href="php/movePagePart.php?id=<?php echo($row["orderNumber"]); ?>&action=mdown" class="btn"><i class="fas fa-arrow-down"></i></a> 
                    <a href="php/movePagePart.php?id=<?php echo($row["orderNumber"]); ?>&action=remove" class="btn"><i class="fas fa-trash"></i></a> 
                    <a class="btn"><?php echo $row["fileName"]." [#".$row["orderNumber"]."]"; ?></a> 
                    
                </div>
                <?php
              }

              include("pagePart/".$row["fileName"]);
              
            }

            if(isset($_GET["edit"])){
              ?>
                <!-- Content Row -->
                <div class="row">
                  <div class="col xs-12">
                    <a href="#" data-toggle="modal" data-target="#editPartModal" class="btn" style="border: 2px dashed; padding: 20px; margin: 0 0 20px 0; width: 100%;">
                      <i class="fas fa-plus"></i> Aggiungi nuovo pannello
                    </a> 
                  </div>
                </div>
                <?php
            }

          ?>


        </div>
        <!-- /.container-fluid -->

      </div>
      <!-- End of Main Content -->

      <!-- Footer -->
      <footer class="sticky-footer bg-white">
        <div class="container my-auto">
          <div class="copyright text-center my-auto">
            <span>Copyright &copy; Giacomo Guaresi & Giacomo Guaresi 2019 | </span>
            <span><b>Ultima scansione: </b><span id="lastUpdateTime"></span></span>
          </div>
        </div>
      </footer>
      <!-- End of Footer -->

    </div>
    <!-- End of Content Wrapper -->

  </div>
  <!-- End of Page Wrapper -->

  <!-- Scroll to Top Button-->
  <a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
  </a>

  <!-- Logout Modal-->
  <div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Disconnetti?</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">Seleziona "Logout" se vuoi disconnetterti dal pannello di controllo</div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Annulla</button>
          <a class="btn btn-primary" href="php/logout.php">Logout</a>
        </div>
      </div>
    </div>
  </div>

  <!-- EditPart Modal-->
  <div class="modal fade" id="editPartModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLabel">Aggiungi Pannelli</h5>
          <button class="close" type="button" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">×</span>
          </button>
        </div>
        <div class="modal-body">
        
        <div class="row">
        <?php 
          $sql = "SELECT * FROM  `pagePart_register` ";
          $res = $mysqli->query($sql);
          while($row = $res->fetch_assoc()){
            ?>
            <div class="col-md-6">
              <div class="card" style="width: 100%; margin-bottom: 20px; ">
                <div class="card-body">
                  <h5 class="card-title"><?php echo $row["nome"]; ?></h5>
                  <h6 class="card-subtitle mb-2 text-muted"><?php echo $row["descrizione"]; ?></h6>
                  <p class="card-text"><?php echo $row["fileName"]; ?></p>
                  <a href="php/movePagePart.php?id=<?php echo($row["fileName"]); ?>&action=add" class="card-link">Aggiungi</a>
                </div>
              </div>
            </div>
            <?php
          }
        ?>
        </div>
        
        </div>
        <div class="modal-footer">
          <button class="btn btn-secondary" type="button" data-dismiss="modal">Annulla</button>
          <a class="btn btn-primary" href="php/logout.php">Logout</a>
        </div>
      </div>
    </div>
  </div>



</body>

</html>


<!--- 

  TODO
    watt consumati:
      totale watt su badge in alto, con percentuale su 5kw 
      grafico riepilogativo del mese (bimestrale) e del periodo totale
      previsione costo bolletta badge (bimestrale)

    temperatura
      temperatura corrente 
      grafico della temperatura 

    umidità 
      umidità corrrente 
      grafico della umidità 
    
    sezione di controllo e stato boiler e interruttore luce (interruttori vari)

    link a siti personali utili (ex nas)

    pagina di configurazione del sito (valire std ecc)

    login di sicurezza  

    auto refresh dei valori  -> client-rest system structure 

-->