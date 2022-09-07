<?php
$basePath = dirname(dirname(__DIR__));
$youtubePath = $basePath . '/docs/json/youtube';

$listFile = $youtubePath . '/list.csv';
$fh = fopen($listFile, 'r');
fgetcsv($fh, 2048);
$keywords = ['街講', '掃街'];
$pool = [];
while ($line = fgetcsv($fh, 2048)) {
    $keywordFound = false;
    foreach ($keywords as $keyword) {
        if (false === $keywordFound && false !== strpos($line[1], $keyword)) {
            $keywordFound = true;
            if (!isset($pool[$keyword])) {
                $pool[$keyword] = [
                    'count' => 0,
                    'hours' => 0,
                    'minutes' => 0,
                    'seconds' => 0,
                ];
            }
            ++$pool[$keyword]['count'];
            $detail = json_decode(file_get_contents($basePath . '/docs/json/youtube/details/' . $line[0] . '.json'), true);
            $parts = preg_split('/[^0-9]/', $detail['items'][0]['contentDetails']['duration']);
            switch (count($parts)) {
                case 6:
                    $pool[$keyword]['hours'] += $parts[2];
                    $pool[$keyword]['minutes'] += $parts[3];
                    $pool[$keyword]['seconds'] += $parts[4];
                    break;
                case 5:
                    $pool[$keyword]['minutes'] += $parts[2];
                    $pool[$keyword]['seconds'] += $parts[3];
                    break;
            }
        }
    }
}

foreach ($pool as $keyword => $data) {
    $finalSeconds = $data['seconds'] % 60;
    $data['minutes'] += ($data['seconds'] - $finalSeconds) / 60;
    $finalMinutes = $data['minutes'] % 60;
    $data['hours'] += ($data['minutes'] - $finalMinutes) / 60;

    echo "{$keyword}: {$data['count']} times / {$data['hours']}:{$finalMinutes}:{$finalSeconds}\n";
}
