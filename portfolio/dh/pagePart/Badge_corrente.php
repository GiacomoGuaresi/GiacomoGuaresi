<!-- Content Row -->

<div class="row">
    <div class="col-12" id="errorSensorKwAlert" style="display:none">
        <div class="alert alert-danger" role="alert">
            <strong>Sensore Non Connesso</strong> il sensore dei Kw non invia più dati dal <span id="errorSensorKwAlert_data"></span>
        </div>
    </div>
    <!-- Earnings (Monthly) Card Example -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2" id="cardKwInUso">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Consumo Watt
                        </div>
                        <div class="row no-gutters align-items-center">
                            <div class="col-auto">
                                <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800"><span id="kwInUso"></span>kW -
                                    <span id="kwPerc"></span>%</div>
                            </div>
                            <div class="col">
                                <div class="progress progress-sm mr-2">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 50%"
                                        id="kwProgress">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-bolt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Earnings (Monthly) Card Example -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Volt</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"> <span id="volt"></span>V</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-bolt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Earnings (Monthly) Card Example -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Ampere</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><span id="ampere"></span>A</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-bolt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Requests Card Example -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-warning shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Frequenza</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><span id="frequenza"></span>Hz
                        </div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-bolt fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
   console.log("Loading Badge_corrente Scripts");
   functionStack.push(function(data){
        $("#kwInUso").text(data["kwInUso"])
        $("#kwMax").text(data["KwMax"])
        $("#volt").text(data["volt"])
        $("#ampere").text(data["ampere"])
        $("#frequenza").text(data["frequenza"])
    
        var d = new Date();
        var n = (d.getTime()/1000).toFixed(0);
        
        if(n-data["regDati_sensCorr"] > 60*5){
            dataLabel = new Date(data["regDati_sensTemp"] * 1000);
            var output = dataLabel.getDate()+"/"+(dataLabel.getMonth()+1)+"/"+dataLabel.getFullYear()+" "+dataLabel.getHours()+":"+dataLabel.getMinutes();
            $("#errorSensorKwAlert_data").text(output);
            $("#errorSensorKwAlert").fadeIn();
        }
        else
            $("#errorSensorKwAlert").fadeOut();

        var perc = (Number(data["kwInUso"])/Number(data["KwMax"])*100).toFixed(0); 

        if(Number(perc) > Number(data["percAllarmeKw"]))
        $("#cardKwInUso").addClass("card-danger");
        else 
        $("#cardKwInUso").removeClass("card-danger");

        $("#kwPerc").text(perc);
        $("#kwProgress").css("width",perc+"%");
    });
});
</script>