<?php
/**
 * Video Background Downloader
 * This script downloads a free stock video for the background
 */

// Free video URLs (these are actual working URLs from free stock video sites)
$videoUrls = [
    // Option 1: Abstract gradient (small file ~2MB)
    'abstract' => 'https://assets.mixkit.co/videos/preview/mixkit-abstract-blue-background-with-particles-5512-large.mp4',
    
    // Option 2: City lights (medium file ~5MB)
    'city' => 'https://assets.mixkit.co/videos/preview/mixkit-city-lights-at-night-2825-large.mp4',
    
    // Option 3: Technology (small file ~3MB)
    'tech' => 'https://assets.mixkit.co/videos/preview/mixkit-digital-animation-of-futuristic-devices-27744-large.mp4',
    
    // Option 4: Urban (medium file ~4MB)
    'urban' => 'https://assets.mixkit.co/videos/preview/mixkit-aerial-view-of-a-city-at-night-2869-large.mp4',
];

// Select which video to download (change this to 'city', 'tech', or 'urban')
$selectedVideo = 'abstract';

$videoUrl = $videoUrls[$selectedVideo];
$outputPath = __DIR__ . '/assets/videos/background.mp4';

// Create directory if it doesn't exist
$dir = dirname($outputPath);
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

echo "Downloading video background...\n";
echo "Source: $videoUrl\n";
echo "Destination: $outputPath\n\n";

// Download the video
$ch = curl_init($videoUrl);
$fp = fopen($outputPath, 'wb');

curl_setopt($ch, CURLOPT_FILE, $fp);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 300); // 5 minutes timeout
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

// Progress callback
curl_setopt($ch, CURLOPT_NOPROGRESS, false);
curl_setopt($ch, CURLOPT_PROGRESSFUNCTION, function($resource, $download_size, $downloaded, $upload_size, $uploaded) {
    if ($download_size > 0) {
        $progress = round(($downloaded / $download_size) * 100);
        echo "\rProgress: $progress% ($downloaded / $download_size bytes)";
    }
});

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);
fclose($fp);

echo "\n\n";

if ($result && $httpCode == 200) {
    $fileSize = filesize($outputPath);
    $fileSizeMB = round($fileSize / 1024 / 1024, 2);
    
    echo "✓ Success! Video downloaded successfully!\n";
    echo "File size: $fileSizeMB MB\n";
    echo "Location: $outputPath\n\n";
    echo "Now refresh your dashboard to see the video background!\n";
} else {
    echo "✗ Error: Failed to download video (HTTP Code: $httpCode)\n";
    echo "Please try again or download manually from:\n";
    echo "$videoUrl\n";
    
    // Clean up failed download
    if (file_exists($outputPath)) {
        unlink($outputPath);
    }
}
?>
