import 'dart:io';
import 'dart:typed_data';
import 'package:flutter/material.dart';
import 'package:google_maps_flutter/google_maps_flutter.dart';
import 'package:image_picker/image_picker.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:speech_to_text/speech_to_text.dart' as stt;
import 'package:provider/provider.dart';
import 'package:latlong2/latlong.dart' as latlong2;
import '../../services/database_service.dart';
import '../../services/image_classifier.dart';
import '../../models/report.dart';
import '../../providers/language_provider.dart';
import '../../theme.dart';
import '../../widgets/leaflet_location_picker.dart';
import '../../l10n/app_localizations.dart';

class ProblemReportScreen extends StatefulWidget {
  final String problemType;
  const ProblemReportScreen({super.key, required this.problemType});

  @override
  State<ProblemReportScreen> createState() => _ProblemReportScreenState();
}

class _ProblemReportScreenState extends State<ProblemReportScreen> {
  final _formKey = GlobalKey<FormState>();
  final _descriptionController = TextEditingController();
  XFile? _imageFile;
  bool _isLoading = false;
  LatLng? _selectedLocation;
  String? _locationAddress;
  String _severity = 'Medium';

  // Voice recognition
  late stt.SpeechToText _speech;
  bool _isListening = false;
  bool _speechAvailable = false;

  // Image classification
  bool _isClassifying = false;
  String? _detectedType;
  double? _confidence;

  @override
  void initState() {
    super.initState();
    _initSpeech();
  }

  @override
  void dispose() {
    _descriptionController.dispose();
    _speech.stop();
    super.dispose();
  }

  Future<void> _initSpeech() async {
    _speech = stt.SpeechToText();
    _speechAvailable = await _speech.initialize(
      onError: (error) => debugPrint('Speech error: $error'),
      onStatus: (status) => debugPrint('Speech status: $status'),
    );
    if (mounted) setState(() {});
  }

  Future<void> _toggleListening() async {
    if (!_speechAvailable) {
      _showSnack('Voice input not available', AppTheme.danger);
      return;
    }

    if (_isListening) {
      await _speech.stop();
      setState(() => _isListening = false);
    } else {
      final languageProvider = Provider.of<LanguageProvider>(
        context,
        listen: false,
      );
      final localeId = languageProvider.isArabic ? 'ar_EG' : 'en_US';

      setState(() => _isListening = true);
      await _speech.listen(
        onResult: (result) {
          setState(() {
            _descriptionController.text = result.recognizedWords;
          });
        },
        localeId: localeId,
        listenMode: stt.ListenMode.dictation,
        cancelOnError: false,
        partialResults: true,
      );
    }
  }

  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final picked = await picker.pickImage(
      source: ImageSource.gallery,
      maxWidth: 1920,
      maxHeight: 1080,
      imageQuality: 85,
    );

    if (picked != null) {
      setState(() => _imageFile = picked);
      await _classifyImage(picked);
    }
  }

  Future<void> _classifyImage(XFile imageFile) async {
    setState(() => _isClassifying = true);

    try {
      final file = File(imageFile.path);
      final result = await ImageClassifier.classifyImage(file);

      if (result.success) {
        setState(() {
          _detectedType = result.type;
          _confidence = result.confidence;
          _isClassifying = false;
        });

        final confidencePercent = (result.confidence * 100).toInt();
        _showSnack(
          'Detected: ${result.type} ($confidencePercent% confidence)',
          AppTheme.success,
        );

        if (result.type != widget.problemType && result.type != 'Other') {
          await Future.delayed(const Duration(milliseconds: 500));
          if (mounted) {
            _showChangeTypeDialog(result.type);
          }
        }
      } else {
        setState(() => _isClassifying = false);
        _showSnack('Image analysis failed', AppTheme.warning);
      }
    } catch (e) {
      setState(() => _isClassifying = false);
      _showSnack('Error analyzing image', AppTheme.danger);
    }
  }

  void _showChangeTypeDialog(String detectedType) {
    final l10n = AppLocalizations.of(context);
    showDialog(
      context: context,
      builder:
          (context) => AlertDialog(
            title: Text(l10n.changeProblemType),
            content: Text(
              '${l10n.imageAppearsToBeType} $detectedType. ${l10n.wouldYouLikeToChange}',
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(context),
                child: Text(l10n.noKeepCurrent),
              ),
              ElevatedButton(
                onPressed: () {
                  Navigator.pop(context);
                  Navigator.pushReplacement(
                    context,
                    MaterialPageRoute(
                      builder:
                          (context) =>
                              ProblemReportScreen(problemType: detectedType),
                    ),
                  );
                },
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primary,
                ),
                child: Text(l10n.yesChange),
              ),
            ],
          ),
    );
  }

  Future<void> _openLocationPicker() async {
    // Convert Google Maps LatLng to Leaflet LatLng if needed
    final initialLeafletLocation =
        _selectedLocation != null
            ? latlong2.LatLng(
              _selectedLocation!.latitude,
              _selectedLocation!.longitude,
            )
            : null;

    final result = await Navigator.push<LocationResult>(
      context,
      MaterialPageRoute(
        builder:
            (_) => LeafletLocationPickerScreen(
              initialLocation: initialLeafletLocation,
            ),
      ),
    );
    if (result != null) {
      setState(() {
        // Convert Leaflet LatLng back to Google Maps LatLng
        _selectedLocation = LatLng(
          result.latLng.latitude,
          result.latLng.longitude,
        );
        _locationAddress = result.address;
      });
    }
  }

  Future<void> _submitReport() async {
    final l10n = AppLocalizations.of(context);
    if (!_formKey.currentState!.validate()) return;
    if (_selectedLocation == null) {
      _showSnack(l10n.pleaseSelectLocation, AppTheme.warning);
      return;
    }

    setState(() => _isLoading = true);

    try {
      final prefs = await SharedPreferences.getInstance();
      final uid = prefs.getString('userId') ?? '';
      final nationalId = prefs.getString('nationalId') ?? '';
      final name = prefs.getString('name') ?? '';

      // Upload image and get base64 string if image is selected
      String imagePath = '';
      if (_imageFile != null) {
        print('=== UPLOADING IMAGE (SCREEN) ===');
        print('Image file path: ${_imageFile!.path}');

        final uploadedPath = await DatabaseService.instance.uploadReportImage(
          _imageFile!,
        );

        print('Upload result: ${uploadedPath != null ? "SUCCESS" : "FAILED"}');

        if (uploadedPath != null) {
          print('✓ Base64 string length: ${uploadedPath.length}');
          imagePath = uploadedPath;
        } else {
          print('❌ Upload failed, imagePath will be empty');

          // Show error to user and ask if they want to continue
          if (mounted) {
            setState(() => _isLoading = false);

            final shouldContinue = await showDialog<bool>(
              context: context,
              builder:
                  (context) => AlertDialog(
                    title: const Text('Image Upload Failed'),
                    content: const Text(
                      'Failed to upload image. Would you like to submit the report without an image?',
                    ),
                    actions: [
                      TextButton(
                        onPressed: () => Navigator.pop(context, false),
                        child: Text(l10n.cancel),
                      ),
                      ElevatedButton(
                        onPressed: () => Navigator.pop(context, true),
                        style: ElevatedButton.styleFrom(
                          backgroundColor: AppTheme.primary,
                        ),
                        child: const Text('Continue Without Image'),
                      ),
                    ],
                  ),
            );

            if (shouldContinue != true) {
              // User cancelled, stop submission
              return;
            }

            setState(() => _isLoading = true);
          }
        }
      } else {
        print('⚠️ No image file selected');
      }

      final result = await DatabaseService.instance.createReport(
        Report(
          uid: uid,
          nationalId: nationalId,
          name: name,
          type: widget.problemType,
          description: _descriptionController.text,
          imagePath: imagePath, // Use base64 string
          status: 'Pending',
          severity: _severity,
          createdAt: DateTime.now().toIso8601String(),
          latitude: _selectedLocation!.latitude,
          longitude: _selectedLocation!.longitude,
          locationAddress:
              _locationAddress ??
              '${_selectedLocation!.latitude}, ${_selectedLocation!.longitude}',
        ),
      );

      print('=== REPORT CREATION RESULT ===');
      print('Success: ${result != null}');
      print('Report ID: $result');
      print('Image path length: ${imagePath.length}');
      print('Has image: ${imagePath.isNotEmpty}');

      if (!mounted) return;

      if (result != null) {
        _showSnack(l10n.reportSubmittedSuccess, AppTheme.success);
        await Future.delayed(const Duration(seconds: 1));
        if (mounted) Navigator.pop(context);
      } else {
        _showSnack(l10n.errorSubmittingReport, AppTheme.danger);
      }
    } catch (e) {
      if (!mounted) return;
      _showSnack(l10n.errorSubmittingReport, AppTheme.danger);
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  void _showSnack(String msg, Color color) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(msg),
        backgroundColor: color,
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        duration: const Duration(seconds: 3),
      ),
    );
  }

  Color get _typeColor {
    switch (widget.problemType) {
      case 'Pothole':
        return AppTheme.primary; // Changed to dark blue
      case 'Broken Pipe':
        return AppTheme.primary; // Keep dark blue
      default:
        return AppTheme.primary; // Changed to dark blue for consistency
    }
  }

  IconData get _typeIcon {
    switch (widget.problemType) {
      case 'Pothole':
        return Icons.construction_rounded; // Better icon for pothole
      case 'Broken Pipe':
        return Icons.plumbing_rounded; // Better icon for broken pipe
      default:
        return Icons.report_gmailerrorred_rounded; // Better icon for other
    }
  }

  @override
  Widget build(BuildContext context) {
    final l10n = AppLocalizations.of(context);

    return Scaffold(
      backgroundColor: AppTheme.surface,
      appBar: AppBar(
        backgroundColor: _typeColor,
        foregroundColor: Colors.white,
        elevation: 0,
        title: Row(
          children: [
            Image.asset(
              'assets/logof.png',
              width: 35,
              height: 35,
              fit: BoxFit.contain,
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                '${l10n.report} ${widget.problemType}',
                style: const TextStyle(fontWeight: FontWeight.w700),
              ),
            ),
          ],
        ),
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_rounded),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(20),
          children: [
            // Type badge
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: _typeColor.withValues(alpha: 0.08),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: _typeColor.withValues(alpha: 0.2)),
              ),
              child: Row(
                children: [
                  Icon(_typeIcon, color: _typeColor, size: 28),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          widget.problemType,
                          style: TextStyle(
                            fontSize: 16,
                            fontWeight: FontWeight.w700,
                            color: _typeColor,
                          ),
                        ),
                        Text(
                          l10n.fillDetails,
                          style: const TextStyle(
                            fontSize: 12,
                            color: AppTheme.textSecondary,
                          ),
                        ),
                      ],
                    ),
                  ),
                  if (_isClassifying)
                    const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(strokeWidth: 2),
                    ),
                ],
              ),
            ),
            const SizedBox(height: 24),

            // Photo picker
            _SectionLabel(label: l10n.photo, required: true),
            const SizedBox(height: 10),
            GestureDetector(
              onTap: _pickImage,
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 200),
                height: 180,
                decoration: BoxDecoration(
                  color: AppTheme.cardBg,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(
                    color: _imageFile != null ? _typeColor : AppTheme.border,
                    width: _imageFile != null ? 2 : 1,
                  ),
                ),
                child:
                    _imageFile != null
                        ? ClipRRect(
                          borderRadius: BorderRadius.circular(15),
                          child: FutureBuilder<List<int>>(
                            future: _imageFile!.readAsBytes().then(
                              (b) => b.toList(),
                            ),
                            builder: (ctx, snap) {
                              if (!snap.hasData) {
                                return const Center(
                                  child: CircularProgressIndicator(),
                                );
                              }
                              return Stack(
                                fit: StackFit.expand,
                                children: [
                                  Image.memory(
                                    Uint8List.fromList(snap.data!),
                                    fit: BoxFit.cover,
                                  ),
                                  if (_detectedType != null)
                                    Positioned(
                                      top: 10,
                                      left: 10,
                                      child: Container(
                                        padding: const EdgeInsets.symmetric(
                                          horizontal: 10,
                                          vertical: 6,
                                        ),
                                        decoration: BoxDecoration(
                                          color: AppTheme.success,
                                          borderRadius: BorderRadius.circular(
                                            8,
                                          ),
                                        ),
                                        child: Row(
                                          mainAxisSize: MainAxisSize.min,
                                          children: [
                                            const Icon(
                                              Icons.check_circle,
                                              color: Colors.white,
                                              size: 14,
                                            ),
                                            const SizedBox(width: 4),
                                            Text(
                                              '$_detectedType ${(_confidence! * 100).toInt()}%',
                                              style: const TextStyle(
                                                color: Colors.white,
                                                fontSize: 12,
                                                fontWeight: FontWeight.bold,
                                              ),
                                            ),
                                          ],
                                        ),
                                      ),
                                    ),
                                ],
                              );
                            },
                          ),
                        )
                        : Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Container(
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: _typeColor.withValues(alpha: 0.1),
                                shape: BoxShape.circle,
                              ),
                              child: Icon(
                                Icons.add_photo_alternate_outlined,
                                size: 32,
                                color: _typeColor,
                              ),
                            ),
                            const SizedBox(height: 10),
                            Text(
                              l10n.tapToUpload,
                              style: TextStyle(
                                fontSize: 14,
                                fontWeight: FontWeight.w600,
                                color: _typeColor,
                              ),
                            ),
                          ],
                        ),
              ),
            ),
            const SizedBox(height: 24),

            // Location
            _SectionLabel(label: l10n.location, required: true),
            const SizedBox(height: 10),
            GestureDetector(
              onTap: _openLocationPicker,
              child: Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: AppTheme.cardBg,
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(
                    color:
                        _selectedLocation != null
                            ? AppTheme.success
                            : AppTheme.border,
                    width: _selectedLocation != null ? 2 : 1,
                  ),
                ),
                child: Row(
                  children: [
                    Icon(
                      _selectedLocation != null
                          ? Icons.location_on
                          : Icons.add_location_alt_outlined,
                      color:
                          _selectedLocation != null
                              ? AppTheme.success
                              : AppTheme.primary,
                      size: 26,
                    ),
                    const SizedBox(width: 14),
                    Expanded(
                      child: Text(
                        _selectedLocation != null
                            ? _locationAddress ?? 'Location selected'
                            : l10n.setLocationOnMap,
                        style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w600,
                          color:
                              _selectedLocation != null
                                  ? AppTheme.success
                                  : AppTheme.textPrimary,
                        ),
                      ),
                    ),
                    const Icon(
                      Icons.arrow_forward_ios_rounded,
                      size: 14,
                      color: AppTheme.textSecondary,
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),

            // Description with voice
            _SectionLabel(label: l10n.description, required: true),
            const SizedBox(height: 10),
            Stack(
              children: [
                TextFormField(
                  controller: _descriptionController,
                  maxLines: 5,
                  decoration: InputDecoration(
                    hintText: l10n.describeTheProblem,
                    contentPadding: const EdgeInsets.fromLTRB(16, 16, 60, 16),
                  ),
                  validator: (v) {
                    if (v == null || v.isEmpty) {
                      return l10n.descriptionMinLength;
                    }
                    if (v.length < 10) return l10n.descriptionTooShort;
                    return null;
                  },
                ),
                Positioned(
                  right: 12,
                  top: 12,
                  child: GestureDetector(
                    onTap: _toggleListening,
                    child: Container(
                      width: 44,
                      height: 44,
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          colors:
                              _isListening
                                  ? [
                                    const Color(0xFFEF4444),
                                    const Color(0xFFDC2626),
                                  ]
                                  : [
                                    const Color(0xFF6366F1),
                                    const Color(0xFF4F46E5),
                                  ],
                        ),
                        shape: BoxShape.circle,
                      ),
                      child: Icon(
                        _isListening ? Icons.mic : Icons.mic_none,
                        color: Colors.white,
                        size: 20,
                      ),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 24),

            // Severity
            _SectionLabel(label: l10n.severity, required: true),
            const SizedBox(height: 10),
            DropdownButtonFormField<String>(
              value: _severity,
              decoration: const InputDecoration(
                prefixIcon: Icon(
                  Icons.warning_amber_rounded,
                  color: AppTheme.primary,
                ),
              ),
              items: [
                DropdownMenuItem(value: 'Low', child: Text(l10n.low)),
                DropdownMenuItem(value: 'Medium', child: Text(l10n.medium)),
                DropdownMenuItem(value: 'High', child: Text(l10n.high)),
                DropdownMenuItem(value: 'Critical', child: Text(l10n.critical)),
              ],
              onChanged: (v) => setState(() => _severity = v ?? 'Medium'),
            ),
            const SizedBox(height: 32),

            // Submit
            SizedBox(
              height: 52,
              child: ElevatedButton(
                onPressed: _isLoading ? null : _submitReport,
                style: ElevatedButton.styleFrom(
                  backgroundColor: _typeColor,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14),
                  ),
                ),
                child:
                    _isLoading
                        ? const SizedBox(
                          width: 22,
                          height: 22,
                          child: CircularProgressIndicator(
                            color: Colors.white,
                            strokeWidth: 2.5,
                          ),
                        )
                        : Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            const Icon(Icons.send_rounded, size: 18),
                            const SizedBox(width: 8),
                            Text(
                              l10n.submitReport,
                              style: const TextStyle(
                                fontSize: 16,
                                fontWeight: FontWeight.w600,
                              ),
                            ),
                          ],
                        ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _SectionLabel extends StatelessWidget {
  final String label;
  final bool required;
  const _SectionLabel({required this.label, this.required = false});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
            color: AppTheme.textPrimary,
          ),
        ),
        if (required) ...[
          const SizedBox(width: 4),
          const Text(
            '*',
            style: TextStyle(
              color: AppTheme.danger,
              fontSize: 14,
              fontWeight: FontWeight.w700,
            ),
          ),
        ],
      ],
    );
  }
}
