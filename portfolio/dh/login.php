<!DOCTYPE html>

<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <meta name="msapplication-TileImage"
        content="http://www.leonardosantambrogio.com/wp-content/uploads/2018/07/cropped-icona-270x270.png">
    <link rel="apple-touch-icon-precomposed"
        href="http://www.leonardosantambrogio.com/wp-content/uploads/2018/07/cropped-icona-180x180.png">
    <link rel="icon" href="http://www.leonardosantambrogio.com/wp-content/uploads/2018/07/cropped-icona-192x192.png"
        sizes="192x192">
    <link rel="icon" href="http://www.leonardosantambrogio.com/wp-content/uploads/2018/07/cropped-icona-32x32.png"
        sizes="32x32">

    <title>SB Admin 2 - Login</title>

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="css/sb-admin-2.css" rel="stylesheet">

    <script src="vendor/jquery/jquery.min.js"></script>

    <script>
        function pinEntry(n){
            $("#pin").val($("#pin").val()+n);
            if($("#pin").val().length == 4)
            {
                $("#formLoginPin").submit();
            }
        }

        function pinBack(){
            var d = $("#pin").val();
            $("#pin").val(d.slice(0,-1))
        }
    </script>

</head>

<body class="bg-gradient-primary">
  <style>
  svg {
    background-size: cover;
    width: 100%;
    display: block;
    position: fixed; 
    bottom: 0px; 
  }
  </style>
  <svg viewbox="0 0 100 25">
    <path fill="#9EAFFD" opacity="0.5" d="M0 30 V15 Q30 3 60 15 V30z" />
    <path fill="#9EAFFD" d="M0 30 V12 Q30 17 55 12 T100 11 V30z" />
  </svg>
  <div class="container">

    <!-- Outer Row -->
    <div class="row justify-content-center">

      <div class="col-xl-10 col-lg-12 col-md-9">

        <div class="card o-hidden border-0 shadow-lg my-5">
          <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
            <div class="row">
              <div class="col-lg-6 d-none d-lg-block bg-login-image"></div>
              <div class="col-lg-6">
                <div class="p-5">
                  <div class="text-center">
                    <h1 class="h4 text-gray-900 mb-4">Benvenuto!</h1>
                  </div>


                  <form class="user" action="./php/login.php" method="post">
                    <div class="form-group">
                      <input type="text" class="form-control form-control-user" name="username"
                        aria-describedby="Username" placeholder="Username">
                    </div>
                    <div class="form-group">
                      <input type="password" class="form-control form-control-user" name="password"
                        placeholder="Password">
                    </div>

                    <button type="submit" class="btn btn-primary btn-user btn-block">
                      Login
                    </button>
                  </form>
                  <hr>

                  <?php 
                    if(!isset($_COOKIE["user"])) {
                      echo "<p>Dispositivo mai connesso in precedenza, procedere con l'autenticazione classica per registrare il dispositivo e abilitare il pin</p>";
                    } else {
                        ?>
                  <div class="clearfix hidden-sm-up">
                    <form class="user" action="./php/loginPin.php" id="formLoginPin" method="post">
                      <div class="form-group">
                        <input type="password" class="form-control form-control-user" name="pin" id="pin"
                          placeholder="pin" readonly>
                      </div>

                      <div class="row" style="padding-bottom:16px;">
                        <div class="col-4"><a style="color: #fff" onclick="pinEntry(1)"
                            class="btn btn-primary btn-user btn-block">1</a></div>
                        <div class="col-4"><a style="color: #fff" onclick="pinEntry(2)"
                            class="btn btn-primary btn-user btn-block">2</a></div>
                        <div class="col-4"><a style="color: #fff" onclick="pinEntry(3)"
                            class="btn btn-primary btn-user btn-block">3</a></div>
                      </div>
                      <div class="row" style="padding-bottom:16px;">
                        <div class="col-4"><a style="color: #fff" onclick="pinEntry(4)"
                            class="btn btn-primary btn-user btn-block">4</a></div>
                        <div class="col-4"><a style="color: #fff" onclick="pinEntry(5)"
                            class="btn btn-primary btn-user btn-block">5</a></div>
                        <div class="col-4"><a style="color: #fff" onclick="pinEntry(6)"
                            class="btn btn-primary btn-user btn-block">6</a></div>
                      </div>
                      <div class="row" style="padding-bottom:16px;">
                        <div class="col-4"><a style="color: #fff" onclick="pinEntry(7)"
                            class="btn btn-primary btn-user btn-block">7</a></div>
                        <div class="col-4"><a style="color: #fff" onclick="pinEntry(8)"
                            class="btn btn-primary btn-user btn-block">8</a></div>
                        <div class="col-4"><a style="color: #fff" onclick="pinEntry(9)"
                            class="btn btn-primary btn-user btn-block">9</a></div>
                      </div>
                      <div class="row" style="padding-bottom:16px;">
                        <div class="col-4"></div>
                        <div class="col-4"><a style="color: #fff" onclick="pinEntry(0)"
                            class="btn btn-primary btn-user btn-block">0</a></div>
                        <div class="col-4"><a style="color: #fff" onclick="pinBack()"
                            class="btn btn-primary btn-user btn-block"><i class="fas fa-backspace"></i></a></div>
                        <div class="col-4"></div>
                      </div>
                  </div>



                  </form>
                  <?php 
                    }
                  ?>





                </div>
              </div>
            </div>
          </div>
        </div>

      </div>

    </div>

  </div>

  <!-- Bootstrap core JavaScript-->
  <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

  <!-- Core plugin JavaScript-->
  <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

  <!-- Custom scripts for all pages-->
  <script src="js/sb-admin-2.js"></script>

</body>

</html>