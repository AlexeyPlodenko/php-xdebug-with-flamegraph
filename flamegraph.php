<?php
$currentDir = __DIR__;
$dir = ini_get('xdebug.trace_output_dir');
if (!$dir) {
    $dir = '/tmp';
}
$dir = rtrim($dir, '/\\') .'/';

$error = null;
if (!is_dir("$currentDir/FlameGraph/")) {
    $flameGraphRepoUrl = 'https://github.com/AlexeyPlodenko/FlameGraph';

    $error = "The directory \"$currentDir/FlameGraph/\" does not exist.";
    $error .= "<br>Clone <a href=\"$flameGraphRepoUrl\" target=\"_blank\">$flameGraphRepoUrl</a>";
    $error .= " to the web root directory.";
}

if (!$error && isset($_GET['file'], $_GET['width']) && is_scalar($_GET['file']) && is_scalar($_GET['width'])) {
    $file = $_GET['file'];
    $width = $_GET['width'];

    if (!preg_match('/^[a-z\d._]+\.xt$/', $file)) {
        $error = 'File name contains not allowed characters.';
    }
    if (!$error && !ctype_digit($width)) {
        $error = 'Parameter "width" is not a number.';
    }

    $filePath = $dir . $file;
    if (!$error && !file_exists($filePath)) {
        $error = 'Input file does not exist.';
    }
    if (!$error && !is_readable($filePath)) {
        $error = 'Cannot read input file. Check permissions.';
    }

    if (!$error) {
        $cmd = "php $currentDir/FlameGraph/stackcollapse-xdebug.php $filePath";
        $cmd .= " | $currentDir/FlameGraph/flamegraph.pl --width=$width 2>&1";
        passthru($cmd, $execResCode);
        if ($execResCode) {
            echo '<div style="color: red;">Error. The command execution "', htmlspecialchars($cmd);
            echo '" has failed with the code ', $execResCode ,'.</div>';
        }
    }

    return;
}

?><!DOCTYPE html>
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
    <?php if ($error) : ?>
        <div style="color: red;">Error.<br><?php echo $error ?></div>
    <?php endif ?>
</body>
</html>
