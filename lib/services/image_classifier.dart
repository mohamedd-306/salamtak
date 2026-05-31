import 'dart:io';
import 'package:image/image.dart' as img;

class ImageClassifier {
  static Future<ClassificationResult> classifyImage(File imageFile) async {
    try {
      // Read image file
      final bytes = await imageFile.readAsBytes();
      final image = img.decodeImage(bytes);

      if (image == null) {
        return ClassificationResult(
          type: 'Other',
          confidence: 0.5,
          success: false,
          error: 'Failed to decode image',
        );
      }

      // Analyze image colors
      final analysis = _analyzeImageColors(image);

      // Classification logic
      String detectedType = 'Other';
      double confidence = 0.5;

      // POTHOLE DETECTION
      int potholeScore = 0;

      if (analysis['darkRatio']! > 0.35) potholeScore += 30;
      if (analysis['grayRatio']! > 0.25) potholeScore += 25;
      if (analysis['avgBrightness']! < 100) potholeScore += 20;
      if (analysis['colorVariance']! < 40) potholeScore += 15;
      if (analysis['brownRatio']! > 0.15) potholeScore += 10;

      // BROKEN PIPE DETECTION
      int pipeScore = 0;

      if (analysis['blueRatio']! > 0.25) pipeScore += 35;
      if (analysis['cyanRatio']! > 0.20) pipeScore += 30;
      if (analysis['avgBrightness']! > 120 && analysis['blueRatio']! > 0.15) {
        pipeScore += 20;
      }
      if (analysis['waterLike']! > 0.30) pipeScore += 15;

      // Determine classification
      if (potholeScore > 50 && potholeScore > pipeScore) {
        detectedType = 'Pothole';
        confidence = (0.50 + (potholeScore / 100)).clamp(0.0, 0.95);
      } else if (pipeScore > 50 && pipeScore > potholeScore) {
        detectedType = 'Broken Pipe';
        confidence = (0.50 + (pipeScore / 100)).clamp(0.0, 0.95);
      } else {
        detectedType = 'Other';
        confidence = 0.60;
      }

      return ClassificationResult(
        type: detectedType,
        confidence: confidence,
        success: true,
        potholeScore: potholeScore,
        pipeScore: pipeScore,
        analysis: analysis,
      );
    } catch (e) {
      return ClassificationResult(
        type: 'Other',
        confidence: 0.5,
        success: false,
        error: e.toString(),
      );
    }
  }

  static Map<String, double> _analyzeImageColors(img.Image image) {
    final sampleSize = 30;
    int darkCount = 0;
    int grayCount = 0;
    int blueCount = 0;
    int cyanCount = 0;
    int brownCount = 0;
    int waterLike = 0;
    int totalSamples = 0;
    double brightnessSum = 0;
    double colorVarianceSum = 0;

    for (int x = 0; x < image.width; x += sampleSize) {
      for (int y = 0; y < image.height; y += sampleSize) {
        if (x >= image.width || y >= image.height) continue;

        final pixel = image.getPixel(x, y);
        final r = pixel.r.toInt();
        final g = pixel.g.toInt();
        final b = pixel.b.toInt();

        final brightness = (r + g + b) / 3;
        brightnessSum += brightness;
        totalSamples++;

        final variance = ((r - g).abs() + (g - b).abs() + (r - b).abs()) / 3;
        colorVarianceSum += variance;

        // Dark colors (asphalt, potholes)
        if (brightness < 85) darkCount++;

        // Gray colors (concrete, roads)
        if ((r - g).abs() < 25 &&
            (g - b).abs() < 25 &&
            (r - b).abs() < 25 &&
            brightness < 150) {
          grayCount++;
        }

        // Blue colors (water)
        if (b > r + 20 && b > g + 20) blueCount++;

        // Cyan/light blue (water surface)
        if (b > r && b > g && brightness > 100 && brightness < 200) {
          cyanCount++;
        }

        // Brown/earth tones (exposed ground)
        if (r > g && g > b && r > 80 && r < 180 && brightness < 140) {
          brownCount++;
        }

        // Water-like
        if (b > (r + g) / 2 && brightness > 60 && brightness < 180) {
          waterLike++;
        }
      }
    }

    if (totalSamples == 0) totalSamples = 1;

    return {
      'darkRatio': darkCount / totalSamples,
      'grayRatio': grayCount / totalSamples,
      'blueRatio': blueCount / totalSamples,
      'cyanRatio': cyanCount / totalSamples,
      'brownRatio': brownCount / totalSamples,
      'waterLike': waterLike / totalSamples,
      'avgBrightness': brightnessSum / totalSamples,
      'colorVariance': colorVarianceSum / totalSamples,
    };
  }
}

class ClassificationResult {
  final String type;
  final double confidence;
  final bool success;
  final String? error;
  final int? potholeScore;
  final int? pipeScore;
  final Map<String, double>? analysis;

  ClassificationResult({
    required this.type,
    required this.confidence,
    required this.success,
    this.error,
    this.potholeScore,
    this.pipeScore,
    this.analysis,
  });
}
