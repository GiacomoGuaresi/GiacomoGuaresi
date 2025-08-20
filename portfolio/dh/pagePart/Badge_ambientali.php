<!-- Content Row -->
<div class="row">
    <div class="col-12" id="errorSensorTempAlert" style="display:none">
        <div class="alert alert-danger" role="alert">
            <strong>Sensore Non Connesso</strong> il sensore ambientale non invia più dati dal <span id="errorSensorTempAlert_data"></span>
        </div>
    </div>
    <!-- Earnings (Monthly) Card Example -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Previsione Bolletta
                            (bimestrale)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"> <span id="kwTot"></span>kW - €<span
                                id="costoTot"></span></div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-euro-sign fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Earnings (Monthly) Card Example -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Umidità</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><span id="umidita"></span>%</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-tint fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Requests Card Example -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-info shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Temperatura</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><span id="temperatura"></span> c°</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-thermometer-half fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
   console.log("Loading Badge_ambientali Scripts");

   functionStack.push(function(data){
        $("#umidita").text(data["Umidita"])
        $("#temperatura").text(data["Temperatura"])

        var d = new Date();
        var n = (d.getTime()/1000).toFixed(0);
            
        if(n-data["regDati_sensTemp"] > 60*5){
            dataLabel = new Date(data["regDati_sensTemp"] * 1000);
            var output = dataLabel.getDate()+"/"+(dataLabel.getMonth()+1)+"/"+dataLabel.getFullYear()+" "+dataLabel.getHours()+":"+dataLabel.getMinutes();
            $("#errorSensorTempAlert_data").text(output);
            $("#errorSensorTempAlert").fadeIn();
        }
        else
            $("#errorSensorTempAlert").fadeOut();

        $("#kwTot").text(data["kwTot"])
        costo = Number(data["kwTot"]) * Number(data["CostoKw"]);
        $("#costoTot").text(costo)
    });
});
</script>