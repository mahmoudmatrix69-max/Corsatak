<?php

function getVideoDuration(string $videoPath): int
{
    if (!is_file($videoPath)) {
        return 0;
    }

    $command = 'ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 '
        . escapeshellarg($videoPath);

    $output = shell_exec($command);

    if ($output === null || trim($output) === '') {
        return 0;
    }

    return (int) round((float) trim($output));
};


function formatDuration(int $seconds):string
{
    if ($seconds >= 3600) {
        return round($seconds / 3600, 1) . ' ساعة';
    }

    return round($seconds / 60) . ' دقيقة';
};

function formatDurationTotal(int $seconds):string
{
    $hours = floor($seconds / 3600);
    $minutes = floor(($seconds % 3600) / 60);

    if ($hours > 0 && $minutes > 0) {
        return $hours . ' ساعة و ' . $minutes . ' دقيقة';
    }

    if ($hours > 0) {
        return $hours . ' ساعة';
    }

    return $minutes . ' دقيقة';
}




?>
