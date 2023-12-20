<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>Daily</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.7.1/css/lightbox.css" rel="stylesheet">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.4.1/jquery.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@latest"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.7.1/js/lightbox.min.js" type="text/javascript"></script>
</head>

<body>
    <div id="app"></div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-A3rJD856KowSb7dwlZdYEkO39Gagi7vIsF0jrRAoQmDKKtQBHUuLZ9AsSv4jD4Xa" crossorigin="anonymous">
    </script>
    <script type="text/javascript" src="{{ asset('js/app.js') }}"></script>

    <div id="head-navbar">
        <livewire:navbar />
    </div>
    <div id="content">
        <livewire:content />
    </div>
    <div id="footer">
        <livewire:footer />
    </div>
</body>

<script>
    // daily-log 重設選項值用
    Livewire.on('resetSelectOptions', (index) => {
        const selectCategorys = document.querySelectorAll(`select[name="工作類別"]`);
        if (index >= 0 && index < selectCategorys.length) {
            selectCategorys[index].value = -1;
        }
        const selectProgresses = document.querySelectorAll(`select[name="進度"]`);

        if (index >= 0 && index < selectProgresses.length) {
            selectProgresses[index].value = -1;
        }
    });
</script>

<script>
    // daily-form 重設選項值用
    Livewire.on('resetAllSelectOptions', () => {
        document.querySelector('select[name="工作類別"]').value = -1;
        document.querySelector('select[name="進度"]').value = -1;
    });
</script>

<script>
    // 圖表 "專案" => 時數
    // personal-reports
    Livewire.on('googleDraw', (data) => { // googleDrawProjects
        google.charts.load('current', {
            'packages': ['corechart']
        });

        const link = Object.values(data)[0]['link']

        const logsData = Object.values(data)[0]['logs']

        var chartAll = null

        var chartLastMonth = null

        var chartLastWeek = null

        var chartYesterday = null

        Object.keys(logsData).forEach((key) => {
            if (typeof logsData[key] === 'object') {
                google.charts.setOnLoadCallback(() => {
                    drawChart(logsData, key);
                });
            }
        });

        var chartDataAll = null

        var chartDataLastMonth = null

        var chartDataLastWeek = null

        var chartDataYesterday = null

        function drawChart(logsData, key) {

            const chartData = [
                ['Task', 'Hours per Day']
            ];

            Object.keys(logsData[key]).forEach((subKey) => {
                if (logsData[key].length != 0) {
                    chartData.push([subKey, logsData[key][subKey]]);
                }
            });

            var drawdata = google.visualization.arrayToDataTable(chartData);

            var options = {
                is3D: false,
                backgroundColor: {
                    fill: '#f7c7c6',
                },
                chartArea: {
                    width: 500,
                    height: 300
                }
            };

            switch (key) {
                case 'all':
                    chartAll = new google.visualization.PieChart(document.getElementById(`${key}` +
                        '-piechart'));
                    google.visualization.events.addListener(chartAll, 'select', selectHandlerAll);
                    chartAll.draw(drawdata, options);
                    chartDataAll = chartData.splice(1);
                    break
                case 'lastMonth':
                    chartLastMonth = new google.visualization.PieChart(document.getElementById(`${key}` +
                        '-piechart'));
                    google.visualization.events.addListener(chartLastMonth, 'select', selectHandlerLastMonth);
                    chartLastMonth.draw(drawdata, options);
                    chartDataLastMonth = chartData.splice(1);
                    break
                case 'lastWeek':
                    chartLastWeek = new google.visualization.PieChart(document.getElementById(`${key}` +
                        '-piechart'));
                    google.visualization.events.addListener(chartLastWeek, 'select', selectHandlerLastWeek);
                    chartLastWeek.draw(drawdata, options);
                    chartDataLastWeek = chartData.splice(1);
                    break
                case 'yesterday':
                    chartYesterday = new google.visualization.PieChart(document.getElementById(`${key}` +
                        '-piechart'));
                    google.visualization.events.addListener(chartYesterday, 'select', selectHandlerYesterday);
                    chartYesterday.draw(drawdata, options);
                    chartDataYesterday = chartData.splice(1);
                    break
            }
        }

        var chartDataAll = null

        var chartDataLastMonth = null

        var chartDataLastWeek = null

        var chartDataYesterday = null

        function selectHandlerAll(e) {
            var selectedItem = chartAll.getSelection();

            Livewire.dispatch('updatedPieChart', {
                link: link,
                projectName: chartDataAll[selectedItem[0].row][0],
                type: 'all'
            });
        }

        function selectHandlerLastMonth(e) {
            var selectedItem = chartLastMonth.getSelection();

            Livewire.dispatch('updatedPieChart', {
                link: link,
                projectName: chartDataLastMonth[selectedItem[0].row][0],
                type: 'lastMonth'
            });
        }

        function selectHandlerLastWeek(e) {
            var selectedItem = chartLastWeek.getSelection();

            Livewire.dispatch('updatedPieChart', {
                link: link,
                projectName: chartDataLastWeek[selectedItem[0].row][0],
                type: 'lastWeek'
            });
        }

        function selectHandlerYesterday(e) {
            var selectedItem = chartYesterday.getSelection();

            Livewire.dispatch('updatedPieChart', {
                link: link,
                projectName: chartDataYesterday[selectedItem[0].row][0],
                type: 'yesterday'
            });
        }
    });
</script>

<script>
    // // 圖表 "人" => 時數
    // project-reports 
    Livewire.on('googleDrawUsers', (data) => { // 可改成googleDraw用type區分 差異在返回dispatch給定的格式
        google.charts.load('current', {
            'packages': ['corechart']
        });

        const link = Object.values(data)[0]['link']

        const logsData = Object.values(data)[0]['logs']

        var chartAll = null

        var chartLastMonth = null

        var chartLastWeek = null

        var chartYesterday = null

        Object.keys(logsData).forEach((key) => {
            if (typeof logsData[key] === 'object') {
                google.charts.setOnLoadCallback(() => {
                    drawChart(logsData, key);
                });
            }
        });

        var chartDataAll = null

        var chartDataLastMonth = null

        var chartDataLastWeek = null

        var chartDataYesterday = null

        function drawChart(logsData, key) {

            const chartData = [
                ['Task', 'Hours per Day']
            ];

            Object.keys(logsData[key]).forEach((subKey) => {
                if (logsData[key].length != 0) {
                    chartData.push([subKey, logsData[key][subKey]]);
                }
            });

            var drawdata = google.visualization.arrayToDataTable(chartData);

            var options = {
                is3D: false,
                backgroundColor: {
                    fill: '#f7c7c6',
                },
                chartArea: {
                    width: 500,
                    height: 300
                }
            };

            switch (key) {
                case 'all':
                    chartAll = new google.visualization.PieChart(document.getElementById(`${key}` +
                        '-piechart'));
                    google.visualization.events.addListener(chartAll, 'select', selectHandlerAll);
                    chartAll.draw(drawdata, options);
                    chartDataAll = chartData.splice(1);
                    break
                case 'lastMonth':
                    chartLastMonth = new google.visualization.PieChart(document.getElementById(`${key}` +
                        '-piechart'));
                    google.visualization.events.addListener(chartLastMonth, 'select', selectHandlerLastMonth);
                    chartLastMonth.draw(drawdata, options);
                    chartDataLastMonth = chartData.splice(1);
                    break
                case 'lastWeek':
                    chartLastWeek = new google.visualization.PieChart(document.getElementById(`${key}` +
                        '-piechart'));
                    google.visualization.events.addListener(chartLastWeek, 'select', selectHandlerLastWeek);
                    chartLastWeek.draw(drawdata, options);
                    chartDataLastWeek = chartData.splice(1);
                    break
                case 'yesterday':
                    chartYesterday = new google.visualization.PieChart(document.getElementById(`${key}` +
                        '-piechart'));
                    google.visualization.events.addListener(chartYesterday, 'select', selectHandlerYesterday);
                    chartYesterday.draw(drawdata, options);
                    chartDataYesterday = chartData.splice(1);
                    break
            }
        }

        var chartDataAll = null

        var chartDataLastMonth = null

        var chartDataLastWeek = null

        var chartDataYesterday = null

        function selectHandlerAll(e) {
            var selectedItem = chartAll.getSelection();

            Livewire.dispatch('updatedPieChartUsers', {
                link: link,
                userName: chartDataAll[selectedItem[0].row][0],
                type: 'all'
            });
        }

        function selectHandlerLastMonth(e) {
            var selectedItem = chartLastMonth.getSelection();

            Livewire.dispatch('updatedPieChartUsers', {
                link: link,
                userName: chartDataLastMonth[selectedItem[0].row][0],
                type: 'lastMonth'
            });
        }

        function selectHandlerLastWeek(e) {
            var selectedItem = chartLastWeek.getSelection();

            Livewire.dispatch('updatedPieChartUsers', {
                link: link,
                userName: chartDataLastWeek[selectedItem[0].row][0],
                type: 'lastWeek'
            });
        }

        function selectHandlerYesterday(e) {
            var selectedItem = chartYesterday.getSelection();

            Livewire.dispatch('updatedPieChartUsers', {
                link: link,
                userName: chartDataYesterday[selectedItem[0].row][0],
                type: 'yesterday'
            });
        }
    });
</script>

<script>
    // 圖表 "專案類別" => 時數
    // project-reports
    Livewire.on('googleDrawSubCategory', (data) => { // 可改成googleDraw用type區分 差異在返回dispatch給定的格式
        google.charts.load('current', {
            'packages': ['corechart']
        });

        const link = Object.values(data)[0]['link']

        const logsData = Object.values(data)[0]['logs']

        var chartAll = null

        var chartLastMonth = null

        var chartLastWeek = null

        var chartYesterday = null

        Object.keys(logsData).forEach((key) => {
            if (typeof logsData[key] === 'object') {
                google.charts.setOnLoadCallback(() => {
                    drawChart(logsData, key);
                });
            }
        });

        var chartDataAll = null

        var chartDataLastMonth = null

        var chartDataLastWeek = null

        var chartDataYesterday = null

        function drawChart(logsData, key) {

            const chartData = [
                ['Task', 'Hours per Day']
            ];

            Object.keys(logsData[key]).forEach((subKey) => {
                if (logsData[key].length != 0) {
                    chartData.push([subKey, logsData[key][subKey]]);
                }
            });

            var drawdata = google.visualization.arrayToDataTable(chartData);

            var options = {
                is3D: false,
                backgroundColor: {
                    fill: '#f7c7c6',
                },
                chartArea: {
                    width: 500,
                    height: 300
                }
            };

            switch (key) {
                case 'all':
                    chartAll = new google.visualization.PieChart(document.getElementById(`${key}` +
                        '-piechart'));
                    google.visualization.events.addListener(chartAll, 'select', selectHandlerAll);
                    chartAll.draw(drawdata, options);
                    chartDataAll = chartData.splice(1);
                    break
                case 'lastMonth':
                    chartLastMonth = new google.visualization.PieChart(document.getElementById(`${key}` +
                        '-piechart'));
                    google.visualization.events.addListener(chartLastMonth, 'select', selectHandlerLastMonth);
                    chartLastMonth.draw(drawdata, options);
                    chartDataLastMonth = chartData.splice(1);
                    break
                case 'lastWeek':
                    chartLastWeek = new google.visualization.PieChart(document.getElementById(`${key}` +
                        '-piechart'));
                    google.visualization.events.addListener(chartLastWeek, 'select', selectHandlerLastWeek);
                    chartLastWeek.draw(drawdata, options);
                    chartDataLastWeek = chartData.splice(1);
                    break
                case 'yesterday':
                    chartYesterday = new google.visualization.PieChart(document.getElementById(`${key}` +
                        '-piechart'));
                    google.visualization.events.addListener(chartYesterday, 'select', selectHandlerYesterday);
                    chartYesterday.draw(drawdata, options);
                    chartDataYesterday = chartData.splice(1);
                    break
            }
        }

        var chartDataAll = null

        var chartDataLastMonth = null

        var chartDataLastWeek = null

        var chartDataYesterday = null

        function selectHandlerAll(e) {
            var selectedItem = chartAll.getSelection();

            Livewire.dispatch('updatedPieChartSubCategory', {
                link: link,
                subCategoryName: chartDataAll[selectedItem[0].row][0],
                type: 'all'
            });
        }

        function selectHandlerLastMonth(e) {
            var selectedItem = chartLastMonth.getSelection();

            Livewire.dispatch('updatedPieChartSubCategory', {
                link: link,
                subCategoryName: chartDataLastMonth[selectedItem[0].row][0],
                type: 'lastMonth'
            });
        }

        function selectHandlerLastWeek(e) {
            var selectedItem = chartLastWeek.getSelection();

            Livewire.dispatch('updatedPieChartSubCategory', {
                link: link,
                subCategoryName: chartDataLastWeek[selectedItem[0].row][0],
                type: 'lastWeek'
            });
        }

        function selectHandlerYesterday(e) {
            var selectedItem = chartYesterday.getSelection();

            Livewire.dispatch('updatedPieChartSubCategory', {
                link: link,
                subCategoryName: chartDataYesterday[selectedItem[0].row][0],
                type: 'yesterday'
            });
        }
    });
</script>

<script>
    Livewire.on('ShowSweetAlert', (data) => 
    {
        data = Object.values(data)[0];

        const swalConfig = 
        {
            title: data['title'],
            text: data['text'],
            html: "",
            icon: data['icon'],
            showCloseButton: true,
        };

        if (data.hasOwnProperty('timer')) 
        {
            swalConfig.timer = data['timer'];
        }

        Swal.fire(swalConfig)
        .then((result) => 
        {
            if(data.hasOwnProperty('redirect'))
            {
                window.location.href = '/home';
            }
            if (data.hasOwnProperty('dismiss')) 
            {
                if (result.isConfirmed) {
                    Livewire.dispatch('SweetAlertCallback', {
                        tag: data['tag']
                    });
                } 
                else if(result.dismiss === Swal.DismissReason.close) {
                    const dismissConfig = {
                        title: data['dismiss']['title'],
                        text: data['dismiss']['text'],
                        icon: data['dismiss']['icon'],
                        //showCloseButton: true,
                        timer: 1500,
                    };
                    Swal.fire(dismissConfig);
                }
            }
        });
    });
</script>

</html>
