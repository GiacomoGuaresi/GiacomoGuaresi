<!-- Content Row -->
<div class="row">

    <!-- Area Chart -->
    <div class="col-xl-12 col-lg-12">
        <div class="card shadow mb-4">
            <!-- Card Header - Dropdown -->
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary">Consumo Energetico 24h</h6>
                <div class="dropdown no-arrow">
                    <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown"
                        aria-haspopup="true" aria-expanded="false">
                        <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                        aria-labelledby="dropdownMenuLink">
                        <div class="dropdown-header">Consumo Energetico:</div>
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
                    <canvas id="myAreaChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

<script>

if(typeof vlastDate === 'undefined')
    var vlastDate = Math.floor(new Date().getTime() / 1000) - 86400; 

if(typeof def_timeSpan === 'undefined')
    var def_timeSpan = {
        "1h":60*1,
        "6h":60*6,
        "1g":60*24*1,
        "3g":60*24*3,
        "14g":60*24*14,
        "30g":60*24*30,
        "90g":60*24*90
    }

// grafico corrente 
var myLineChart; 

// Area Chart Example
var ctx = document.getElementById("myAreaChart");

$(function() {
    console.log("Loading Grafico_corrente Scripts");
   
    myLineChart = new Chart(ctx, {
    type: 'line',
    data: {
        labels: [],
        datasets: [{
        label: "Consumo",
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
                return 'kw' + number_format(value);
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
            return datasetLabel + ': kw' + number_format(tooltipItem.yLabel);
            }
        }
        }
    }
    });
    myLineChart_currentTimeSpan = def_timeSpan["1g"]; 

    functionStack.push(function(data){
        $.ajax({
            url: "php/api/get_lastKw.php",
            context: document.body,
            data: {lastDate:vlastDate}
        }).done(function(data) {

            data = JSON.parse(data);    
            
            if(data.data.length > 0){
            vlastDate = data.labels[data.labels.length-1]

            data.labels.forEach((item,index)=>{
                var dataLabel = new Date(item*1000); 
                var output = dataLabel.getDate()+"/"+(dataLabel.getMonth()+1)+"/"+dataLabel.getFullYear()+" "+dataLabel.getHours()+":"+dataLabel.getMinutes();
                myLineChart.data.labels.push(output)
            })
                
            data.data.forEach((item,index)=>{
                myLineChart.data.datasets[0].data.push(item)
            })
                
            while(myLineChart.data.labels.length > myLineChart_currentTimeSpan){
                myLineChart.data.labels.shift()
                myLineChart.data.datasets[0].data.shift()
            }
            myLineChart.update();
            }
        });
    });
});
</script>