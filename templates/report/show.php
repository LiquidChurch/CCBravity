<div class="wrap">
    <?php
    $form = $this->get('form');
    $entries = $this->get('entries');
    $entry_count = $entry_succs_count = $synced = $unsynced = 0;

    foreach ($entries as $index => $entry) {
        $api_data = gform_get_meta($entry['id'], 'api_data');
        $entry_count++;
        if ($api_data['api_sync'] == true)
            $entry_succs_count++;
    }

    if($entry_count == $entry_succs_count) {
        $synced = $entry_succs_count;
    } else {
        $unsynced = $entry_count - $entry_succs_count;
    }

    p($form, 0);
    p($entries, 0);
    ?>
    <h2><?php echo $form['title'] ?></h2>
    <hr/>

    <div id="canvas-holder" style="width:300px;">
        <canvas id="chart-area"/>
    </div>

    <script>
        window.chartColors = {
            red: 'rgb(255, 99, 132)',
            orange: 'rgb(255, 159, 64)',
            yellow: 'rgb(255, 205, 86)',
            green: 'rgb(75, 192, 192)',
            blue: 'rgb(54, 162, 235)',
            purple: 'rgb(153, 102, 255)',
            grey: 'rgb(231,233,237)'
        };

        window.randomScalingFactor = function () {
            return (Math.random() > 0.5 ? 1.0 : -1.0) * Math.round(Math.random() * 100);
        }

        var randomScalingFactor = function () {
            return Math.round(Math.random() * 100);
        };

        var config = {
            type: 'pie',
            data: {
                datasets: [{
                    data: [
                        <?php echo $synced ?>,
                        <?php echo $unsynced ?>,
                    ],
                    backgroundColor: [
                        window.chartColors.red,
                        window.chartColors.orange,
                    ],
                    label: 'Dataset 1'
                }],
                labels: [
                    "Synced Entries",
                    "Not Synced Entries",
                ]
            },
            options: {
                responsive: true
            }
        };

        window.onload = function () {
            var ctx = document.getElementById("chart-area").getContext("2d");
            window.myPie = new Chart(ctx, config);
        };

    </script>
</div>