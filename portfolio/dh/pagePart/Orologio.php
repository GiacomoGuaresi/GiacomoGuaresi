<!-- Content Row -->
<div class="row">

    <!-- Area Chart -->
    <div class="col-xl-12 col-lg-12" style="text-align:center">
        <p id="orario"style="font-size: 8rem; margin-bottom: 0;" >00:00</p>
        <p id="data"style="font-size: 2rem;" >## [mese] ###</p>
    </div>
</div>
<script>
    

    $( document ).ready(function() {
        var currentdate = new Date(); 
        var orario = currentdate.getHours() + ":" + currentdate.getMinutes();
        var data =  currentdate.getDate() + "/"+ (currentdate.getMonth()+1)  + "/" + currentdate.getFullYear();
        
        $("#orario").text(orario);
        $("#data").text(data);
        window.setInterval(function(){
        
        }, 15000);
    });
</script>


<script>
$(function() {
   console.log("Loading Orologio Scripts");
});
</script>