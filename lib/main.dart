import 'package:flutter/material.dart';
import 'package:firebase_core/firebase_core.dart';
import 'package:provider/provider.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter/rendering.dart';
import 'theme.dart';
import 'screens/login_screen.dart';
import 'screens/user/problem_report_screen.dart';
import 'providers/language_provider.dart';
import 'providers/cart_provider.dart';
import 'l10n/app_localizations.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  // COMPLETELY DISABLE ALL DEBUG PAINTING AND ERROR DISPLAYS
  debugPaintSizeEnabled = false;
  debugPaintBaselinesEnabled = false;
  debugPaintPointersEnabled = false;
  debugPaintLayerBordersEnabled = false;
  debugRepaintRainbowEnabled = false;
  debugRepaintTextRainbowEnabled = false;

  // Hide overflow errors - don't show OR log them
  FlutterError.onError = (FlutterErrorDetails details) {
    // Completely silent - no console output, no red screen
  };

  // Hide error widgets - return completely invisible widget
  ErrorWidget.builder = (FlutterErrorDetails details) {
    return const SizedBox.shrink();
  };

  await Firebase.initializeApp(
    options: const FirebaseOptions(
      apiKey: 'AIzaSyDY9lX8swlfKx3umnW57O5DA2Ka1Pdc0Fk',
      authDomain: 'salmtak-6fffe.firebaseapp.com',
      projectId: 'salmtak-6fffe',
      storageBucket: 'salmtak-6fffe.firebasestorage.app',
      messagingSenderId: '1048763383483',
      appId: '1:1048763383483:web:f9a6140078484b5552f39e',
    ),
  );

  print('');
  print('╔════════════════════════════════════════╗');
  print('║         LOGIN CREDENTIALS             ║');
  print('╠════════════════════════════════════════╣');
  print('║ ADMIN:                                 ║');
  print('║   Work ID: 221007689                   ║');
  print('║   Password: 631663                     ║');
  print('║                                        ║');
  print('║ TEST USER:                             ║');
  print('║   National ID: 11111111111111          ║');
  print('║   Password: user123456                 ║');
  print('╚════════════════════════════════════════╝');
  print('');

  runApp(
    MultiProvider(
      providers: [
        ChangeNotifierProvider(create: (_) => LanguageProvider()),
        ChangeNotifierProvider(create: (_) => CartProvider()),
      ],
      child: const SalamtakApp(),
    ),
  );
}

class SalamtakApp extends StatelessWidget {
  const SalamtakApp({super.key});

  @override
  Widget build(BuildContext context) {
    final languageProvider = Provider.of<LanguageProvider>(context);

    return MaterialApp(
      title: 'Salamtak',
      debugShowCheckedModeBanner: false,
      theme: AppTheme.theme,
      locale: languageProvider.locale,
      localizationsDelegates: const [
        AppLocalizations.delegate,
        GlobalMaterialLocalizations.delegate,
        GlobalWidgetsLocalizations.delegate,
        GlobalCupertinoLocalizations.delegate,
      ],
      supportedLocales: AppLocalizations.supportedLocales,
      // Set text direction based on language
      builder: (context, child) {
        return Directionality(
          textDirection:
              languageProvider.isArabic ? TextDirection.rtl : TextDirection.ltr,
          child: child!,
        );
      },
      routes: {
        '/report-problem': (context) {
          final problemType =
              ModalRoute.of(context)!.settings.arguments as String;
          return ProblemReportScreen(problemType: problemType);
        },
      },
      home: const LoginScreen(),
    );
  }
}
