<!-- Content Row -->
<div class="row">

    <!-- Area Chart -->
    <div class="col-xl-12 col-lg-12">
        <div class="card shadow mb-4">
            <!-- Card Header - Dropdown -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Temperatura e Umidita</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLinkT" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                        aria-labelledby="dropdownMenuLinkT">
                        <div class="dropdown-header">Grafico:</div>
                        <a class="dropdown-item" href="#">Visualizza altro (WIP)</a>
                        <!-- <a class="dropdown-item" href="#">Another action</a> -->
                        <!-- <div class="dropdown-divider"></div>
            <a class="dropdown-item" href="#">Something else here</a> -->
                    </div>
                </div>
            </div>
            <!-- Card Body -->
            <div class="card-body">
                <div class="chart-area">
                    <canvas id="myAreaChartTemp"></canvas>
                </div>
                <hr>
                <div value="1h" id="ChartTemp_timeSpan_1h" class="CharTemp_setTimeSpan btn btn-outline-primary">1h</div>
                <div value="6h" id="ChartTemp_timeSpan_6h" class="CharTemp_setTimeSpan btn btn-outline-primary">6h</div>
                <div value="1g" id="ChartTemp_timeSpan_1g" class="CharTemp_setTimeSpan btn btn-outline-primary">1g</div>
                <div value="3g" id="ChartTemp_timeSpan_3g" class="CharTemp_setTimeSpan btn btn-primary">3g</div>
                <div value="14g" id="ChartTemp_timeSpan_14g" class="CharTemp_setTimeSpan btn btn-outline-primary">14g</div>
                <div value="30g" id="ChartTemp_timeSpan_30g" class="CharTemp_setTimeSpan btn btn-outline-primary">30g</div>
                <div value="90g" id="ChartTemp_timeSpan_90g" class="CharTemp_setTimeSpan btn btn-outline-primary">90g</div>
                <span id="CharTemp_log"></span>
            </div>
        </div>
    </div>
</div>

<script>
if(typeof vlastDate === 'undefined')
    var vlastDate = Math.floor(new Date().getTime() / 1000) - 86400; 

if(typeof def_timeSpan === 'undefined')
    var def_timeSpan = {
        // "1h":60*1,
        // "6h":60*6,
        // "12h":60*12,
        // "1g":60*24,
        // "2g":60*24*2,

        "1h":60*1,
        "6h":60*6,
        "1g":60*24*1,
        "3g":60*24*3,
        "14g":60*24*14,
        "30g":60*24*30,
        "90g":60*24*90
    }


// grafico Temperatura
var myAreaChartTemp;
var myAreaChartTemp_globData = [[],[]];
var myAreaChartTemp_globLabels = [];
var myAreaChartTemp_skipCounter = 0; 

// Area Chart Example
var cty = document.getElementById("myAreaChartTemp");

$(function() {
    console.log("Loading Grafico_temperatura Scripts");

    myAreaChartTemp = new Chart(cty, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
            label: "Temperatura",
            lineTension: 0.3,
            backgroundColor: "rgba(78, 115, 223, 0.05)",
            borderColor: "rgba(78, 115, 223, 1)",
            pointRadius: 0,
            pointBackgroundColor: "rgba(78, 115, 223, 1)",
            pointBorderColor: "rgba(78, 115, 223, 1)",
            pointHoverRadius: 3,
            pointHoverBackgroundColor: "rgb(30, 66, 168, 1)",
            pointHoverBorderColor: "rgb(30, 66, 168, 1)",
            pointHitRadius: 10,
            pointBorderWidth: 1,
            data: [],
            },{
            label: "Umidità",
            lineTension: 0.3,
            backgroundColor: "rgba(54, 185, 254, 0.05)",
            borderColor: "rgba(54, 185, 254, 1)",
            pointRadius: 0,
            pointBackgroundColor: "rgba(54, 185, 254, 1)",
            pointBorderColor: "rgba(54, 185, 254, 1)",
            pointHoverRadius: 3,
            pointHoverBackgroundColor: "rgb(30, 66, 168, 1)",
            pointHoverBorderColor: "rgb(30, 66, 168, 1)",
            pointHitRadius: 10,
            pointBorderWidth: 1,
            data: [],
            }],
        },
        options: {
            maintainAspectRatio: false,
            layout: {
            padding: {
                left: 10,
                right: 25,
                top: 25,
                bottom: 0
            }
            },
            scales: {
            xAxes: [{
                time: {
                unit: 'minute'
                },
                gridLines: {
                display: false,
                drawBorder: false
                },
                ticks: {
                maxTicksLimit: 7
                }
            }],
            yAxes: [{
                ticks: {
                maxTicksLimit: 5,
                padding: 10,
                // Include a dollar sign in the ticks
                callback: function (value, index, values) {
                    return number_format(value);
                }
                },
                gridLines: {
                color: "rgb(234, 236, 244)",
                zeroLineColor: "rgb(234, 236, 244)",
                drawBorder: false,
                borderDash: [2],
                zeroLineBorderDash: [2]
                }
            }],
            },
            legend: {
            display: false
            },
            tooltips: {
            backgroundColor: "rgb(255,255,255)",
            bodyFontColor: "#858796",
            titleMarginBottom: 10,
            titleFontColor: '#6e707e',
            titleFontSize: 14,
            borderColor: '#dddfeb',
            borderWidth: 1,
            xPadding: 15,
            yPadding: 15,
            displayColors: false,
            intersect: false,
            mode: 'index',
            caretPadding: 10,
            callbacks: {
                label: function (tooltipItem, chart) {
                var datasetLabel = chart.datasets[tooltipItem.datasetIndex].label || '';
                return datasetLabel + ': ' + number_format(tooltipItem.yLabel);
                }
            }
            }
        }
    });
    myAreaChartTemp_currentTimeSpan = def_timeSpan["1g"]; 

    $(".CharTemp_setTimeSpan").on('click', function(e) {
        console.log("set " + def_timeSpan[$(this).attr("value")]);
        myAreaChartTemp_currentTimeSpan = def_timeSpan[$(this).attr("value")]; 
        $(".CharTemp_setTimeSpan").removeClass("btn-primary").addClass("btn-outline-primary")
        $("#ChartTemp_timeSpan_"+$(this).attr("value")).addClass("btn-primary").removeClass("btn-outline-primary")

        myAreaChartTemp.data.labels = [];
        myAreaChartTemp.data.datasets[0].data = [];
        myAreaChartTemp.data.datasets[1].data = [];
        myAreaChartTemp_skipCounter = 0; 
        $("#CharTemp_log").text("loading...");
        vlastDate = Math.floor(new Date().getTime() / 1000) - (60*def_timeSpan[$(this).attr("value")]); 
        updateView();
        //TODO: imposta valore e ricarica grafico 
    });

    functionStack.push(function(data){
        $.ajax({
            url: "php/api/get_lastTemp.php",
            context: document.body,
            data: {lastDate:vlastDate},
            async: false
        }).done(function(data) {
            
            data = JSON.parse(data);    

            console.log(data);
            
            pointNumber = 1440; //1gg; 
            if(myAreaChartTemp_currentTimeSpan > pointNumber)
                pointSkipping = Math.floor(myAreaChartTemp_currentTimeSpan / pointNumber);
            else 
                pointSkipping = 1; 

            if(data.data.length > 0){
                vlastDate = data.labels[data.labels.length-1]

            for(index = 0; index < data.labels.length; index++){
                if(myAreaChartTemp_skipCounter % pointSkipping == 0){
                    item = Number(data.labels[index])*1000;
                    var dataLabel = new Date(item); 
                    var output = dataLabel.getDate()+"/"+(dataLabel.getMonth()+1)+"/"+dataLabel.getFullYear()+" "+dataLabel.getHours()+":"+dataLabel.getMinutes();
                    myAreaChartTemp.data.labels.push(output);
                    myAreaChartTemp.data.datasets[0].data.push(data.data[index]);
                    myAreaChartTemp.data.datasets[1].data.push(data.data2[index]);

                    if(myAreaChartTemp.data.labels.length > pointNumber){
                        myAreaChartTemp.data.labels.shift();
                        myAreaChartTemp.data.datasets[0].data.shift();
                        myAreaChartTemp.data.datasets[1].data.shift();
                    }

                     myAreaChartTemp_skipCounter = 0; 
                }
                myAreaChartTemp_skipCounter++; 
            };
            console.log("----");
            myAreaChartTemp.update();
            $("#CharTemp_log").text("NOTA: 1 punto ogni " +pointSkipping+" Minuti" );
            }
        });
    });

});
</script>