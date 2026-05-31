<?php
/**
 * Image Classification API
 * Classifies uploaded images to detect problem types
 */

header('Content-Type: application/json');

try {
    // Check if image is provided
    if (!isset($_FILES['image'])) {
        echo json_encode(['success' => false, 'error' => 'No image in request']);
        exit;
    }
    
    if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['success' => false, 'error' => 'Upload error: ' . $_FILES['image']['error']]);
        exit;
    }

    $image_path = $_FILES['image']['tmp_name'];
    
    if (!file_exists($image_path)) {
        echo json_encode(['success' => false, 'error' => 'Image file not found']);
        exit;
    }
    
    // Check if GD library is available
    if (!function_exists('imagecreatefromjpeg')) {
        // GD not available, use simple file-based detection
        $classification = classifyByFileProperties($image_path);
        echo json_encode($classification);
        exit;
    }
    
    // Use local classification
    $classification = classifyImageLocally($image_path);
    echo json_encode($classification);
    
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Exception: ' . $e->getMessage()]);
} catch (Error $e) {
    echo json_encode(['success' => false, 'error' => 'Error: ' . $e->getMessage()]);
}

/**
 * Simple classification based on file properties
 */
function classifyByFileProperties($image_path) {
    $file_size = filesize($image_path);
    $mime_type = mime_content_type($image_path);
    
    // Simple heuristic: larger files might be outdoor photos (potholes)
    // This is a placeholder - returns random classification for demo
    $types = ['Pothole', 'Broken Pipe', 'Other'];
    $weights = [0.4, 0.3, 0.3]; // Probabilities
    
    // Use file size as seed for consistent results
    $seed = $file_size % 100;
    
    if ($seed < 40) {
        $detected_type = 'Pothole';
        $confidence = 0.65;
    } elseif ($seed < 70) {
        $detected_type = 'Broken Pipe';
        $confidence = 0.60;
    } else {
        $detected_type = 'Other';
        $confidence = 0.55;
    }
    
    return [
        'success' => true,
        'type' => $detected_type,
        'confidence' => $confidence,
        'method' => 'file_properties',
        'file_size' => $file_size,
        'mime_type' => $mime_type
    ];
}

/**
 * Local image classification fallback
 * Uses basic image analysis to detect problem types
 */
function classifyImageLocally($image_path) {
    try {
        // Get image properties
        $image_info = @getimagesize($image_path);
        
        if (!$image_info) {
            return classifyByFileProperties($image_path);
        }
        
        // Load image based on type
        $image = null;
        switch ($image_info[2]) {
            case IMAGETYPE_JPEG:
                $image = @imagecreatefromjpeg($image_path);
                break;
            case IMAGETYPE_PNG:
                $image = @imagecreatefrompng($image_path);
                break;
            case IMAGETYPE_GIF:
                $image = @imagecreatefromgif($image_path);
                break;
            default:
                return classifyByFileProperties($image_path);
        }
        
        if (!$image) {
            return classifyByFileProperties($image_path);
        }
        
        $width = imagesx($image);
        $height = imagesy($image);
        
        // Analyze image colors and patterns
        $color_analysis = analyzeImageColors($image, $width, $height);
        
        imagedestroy($image);
        
        // Enhanced classification logic
        $detected_type = 'Other';
        $confidence = 0.5;
        
        // POTHOLE DETECTION
        // Characteristics: Dark colors (asphalt), gray tones, low brightness, irregular patterns
        $pothole_score = 0;
        
        // High dark ratio indicates asphalt/road
        if ($color_analysis['dark_ratio'] > 0.35) {
            $pothole_score += 30;
        }
        
        // Gray tones indicate concrete/asphalt
        if ($color_analysis['gray_ratio'] > 0.25) {
            $pothole_score += 25;
        }
        
        // Low average brightness indicates road surface
        if ($color_analysis['avg_brightness'] < 100) {
            $pothole_score += 20;
        }
        
        // Low color variance indicates uniform surface (road)
        if ($color_analysis['color_variance'] < 40) {
            $pothole_score += 15;
        }
        
        // Brown/earth tones indicate exposed ground in pothole
        if ($color_analysis['brown_ratio'] > 0.15) {
            $pothole_score += 10;
        }
        
        // BROKEN PIPE DETECTION
        // Characteristics: Blue/cyan colors (water), high brightness, wet surfaces, reflections
        $pipe_score = 0;
        
        // Blue colors indicate water
        if ($color_analysis['blue_ratio'] > 0.25) {
            $pipe_score += 35;
        }
        
        // Cyan/light blue indicates water surface
        if ($color_analysis['cyan_ratio'] > 0.20) {
            $pipe_score += 30;
        }
        
        // High brightness with blue = water reflection
        if ($color_analysis['avg_brightness'] > 120 && $color_analysis['blue_ratio'] > 0.15) {
            $pipe_score += 20;
        }
        
        // Water-like colors
        if ($color_analysis['water_like'] > 0.30) {
            $pipe_score += 15;
        }
        
        // Determine final classification
        if ($pothole_score > 50 && $pothole_score > $pipe_score) {
            $detected_type = 'Pothole';
            $confidence = min(0.95, 0.50 + ($pothole_score / 100));
        } elseif ($pipe_score > 50 && $pipe_score > $pothole_score) {
            $detected_type = 'Broken Pipe';
            $confidence = min(0.95, 0.50 + ($pipe_score / 100));
        } else {
            // If neither score is high enough, classify as Other
            $detected_type = 'Other';
            $confidence = 0.60;
        }
        
        return [
            'success' => true,
            'type' => $detected_type,
            'confidence' => $confidence,
            'method' => 'color_analysis',
            'scores' => [
                'pothole' => $pothole_score,
                'pipe' => $pipe_score
            ],
            'analysis' => $color_analysis
        ];
    } catch (Exception $e) {
        return classifyByFileProperties($image_path);
    }
}

/**
 * Analyze image colors to detect patterns
 */
function analyzeImageColors($image, $width, $height) {
    $sample_size = 30; // Sample every 30 pixels for better accuracy
    $dark_count = 0;
    $gray_count = 0;
    $blue_count = 0;
    $cyan_count = 0;
    $brown_count = 0;
    $water_like = 0;
    $total_samples = 0;
    $brightness_sum = 0;
    $color_variance_sum = 0;
    
    for ($x = 0; $x < $width; $x += $sample_size) {
        for ($y = 0; $y < $height; $y += $sample_size) {
            if ($x >= $width || $y >= $height) continue;
            
            $rgb = imagecolorat($image, $x, $y);
            $r = ($rgb >> 16) & 0xFF;
            $g = ($rgb >> 8) & 0xFF;
            $b = $rgb & 0xFF;
            
            $brightness = ($r + $g + $b) / 3;
            $brightness_sum += $brightness;
            $total_samples++;
            
            // Calculate color variance (how different R, G, B are from each other)
            $variance = (abs($r - $g) + abs($g - $b) + abs($r - $b)) / 3;
            $color_variance_sum += $variance;
            
            // Dark colors (asphalt, potholes, roads)
            if ($brightness < 85) {
                $dark_count++;
            }
            
            // Gray colors (concrete, roads, asphalt)
            if (abs($r - $g) < 25 && abs($g - $b) < 25 && abs($r - $b) < 25 && $brightness < 150) {
                $gray_count++;
            }
            
            // Blue colors (water, sky reflection in water)
            if ($b > $r + 20 && $b > $g + 20) {
                $blue_count++;
            }
            
            // Cyan/light blue (water surface, wet areas)
            if ($b > $r && $b > $g && $brightness > 100 && $brightness < 200) {
                $cyan_count++;
            }
            
            // Brown/earth tones (exposed ground in potholes)
            if ($r > $g && $g > $b && $r > 80 && $r < 180 && $brightness < 140) {
                $brown_count++;
            }
            
            // Water-like (blue-ish with moderate brightness)
            if ($b > ($r + $g) / 2 && $brightness > 60 && $brightness < 180) {
                $water_like++;
            }
        }
    }
    
    if ($total_samples == 0) $total_samples = 1; // Prevent division by zero
    
    return [
        'dark_ratio' => $dark_count / $total_samples,
        'gray_ratio' => $gray_count / $total_samples,
        'blue_ratio' => $blue_count / $total_samples,
        'cyan_ratio' => $cyan_count / $total_samples,
        'brown_ratio' => $brown_count / $total_samples,
        'water_like' => $water_like / $total_samples,
        'avg_brightness' => $brightness_sum / $total_samples,
        'color_variance' => $color_variance_sum / $total_samples,
        'total_samples' => $total_samples
    ];
}
?>
