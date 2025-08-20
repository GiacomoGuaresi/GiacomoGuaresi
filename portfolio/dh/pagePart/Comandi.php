<!-- Content Row -->
<div class="row">

    <div class="col-lg-12 mb-12">

        <!-- Comandi -->
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold text-primary">Comandi</h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-sm-3"><button class="btn btn-danger btn-user btn-block"
                            id="statoCaldaia">Caldaia</button><br></div>
                </div>


            </div>
        </div>

    </div>
</div>

<script>
$(function() {
   console.log("Loading comandi Scripts");

   $("#statoCaldaia").click(function(){

        var valueCaldaia;
        if( $("#statoCaldaia").hasClass("btn-success"))
        valueCaldaia = 0;
        else 
        valueCaldaia = 1;


        $("#statoCaldaia").removeClass("btn-success").removeClass("btn-danger").addClass("btn-secondary");
        $.ajax({
        data: '{"head": {"user": "EPS8266","password": "Password123", "agent":"web"},"data": {"Caldaia": '+valueCaldaia+'}}',
        success: function(data){
            console.log("device control succeeded");
            updateView();
        },
        error: function(){
            console.log("Device control failed");
        },
        processData: false,
        type: 'POST',
        url: 'php/api/set_vars.php'
        });
    });

    functionStack.push(function(data){
        if(data["Caldaia"] == 1)
            $("#statoCaldaia").addClass("btn-success").removeClass("btn-danger")
        else
            $("#statoCaldaia").addClass("btn-danger").removeClass("btn-success")
    });
});
</script>