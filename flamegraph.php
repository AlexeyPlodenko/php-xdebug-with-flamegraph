<?php
$dir = ini_get('xdebug.trace_output_dir');
if (!$dir) $dir = '/tmp';
$dir = rtrim($dir, '/\\') .'/';

if (isset($_GET['file'], $_GET['width']) && is_scalar($_GET['file']) && is_scalar($_GET['width'])) {
    $file = $_GET['file'];
    $width = $_GET['width'];

    $error = '';
    if (!preg_match('/^[a-z\d._]+\.xt$/', $file)) {
        $error = 'Invalid file name.';
    }
    if (!$error && !ctype_digit($width)) {
        $error = 'Width is not a number.';
    }

    $filePath = $dir . $file;
    if (!$error && !file_exists($filePath)) {
        $error = 'Input file does not exist.';
    }
    if (!$error && !is_readable($filePath)) {
        $error = 'Cannot read input file.';
    }

    if ($error) {
        echo '<div style="color: red;">Error. ', $error ,'</div>';
    } else {
        passthru(
            'php ' . __DIR__ . '/FlameGraph/stackcollapse-xdebug.php ' . $filePath
            .' | ' . __DIR__ . '/FlameGraph/flamegraph.pl --width='. $width
        );
    }
    return;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>XDebug Flame Graph</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=2">
    <style>
        label {
            cursor: pointer;
        }
        .loading {
            animation: blinker 1s linear infinite;
            font-style: italic;
        }
        @keyframes blinker {
            50% {
                opacity: 0;
            }
        }
        .graph {
            margin: 5px 0;
        }
    </style>
    <script>
        function loadGraph() {
            const $file = document.getElementById('file');
            const fileName = $file.value;
            if (!fileName) {
                document.getElementById('reload').style.display = 'none';
                return;
            }

            const $graph = document.getElementById('graph');
            $graph.innerHTML = '<div class="loading">Loading...</div>';
            const graphWidth = $graph.offsetWidth;

            const urlSearchParams = new URLSearchParams({
                file: encodeURIComponent(fileName),
                width: graphWidth
            });
            const urlBase = [location.protocol, '//', location.host].join('');
            const url = new URL('flamegraph.php', urlBase);
            url.search = urlSearchParams.toString();
            fetch(url)
                .then(async (response) => {
                    $graph.innerHTML = await response.text();
                    document.getElementById('reload').style.display = '';
                });
        }

        window.onload = function() {
            document.getElementById('file').onchange = loadGraph;
            document.getElementById('reload').onclick = loadGraph;
        };
    </script>
</head>
<body>
    <label for="file">Choose file:</label>
    <select name="file" id="file">
        <option></option>
        <?php
            $files = glob("$dir/*.xt");
            $files = array_reverse($files);
            foreach ($files as $file) {
                $fileName = basename($file);
                $fileNameEsc = htmlspecialchars($fileName);
                echo '<option value="', $fileNameEsc ,'">', $fileNameEsc ,'</option>';
            }
        ?>
    </select> <input type="button" value="reload" id="reload" style="display: none;"> from
    <em>xdebug.trace_output_dir = <?php echo htmlspecialchars($dir) ?></em>
    <div id="graph" class="graph"></div>
</body>
</html>
