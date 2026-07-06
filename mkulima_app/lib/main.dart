import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:provider/provider.dart';
import 'core/app_router.dart';
import 'core/theme.dart';
import 'providers/auth_provider.dart';
import 'providers/cart_provider.dart';
import 'providers/connectivity_provider.dart';
import 'services/api_service.dart';
import 'services/local_database.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  SystemChrome.setPreferredOrientations([
    DeviceOrientation.portraitUp,
    DeviceOrientation.portraitDown,
  ]);

  final db = LocalDatabase();
  // Configure per environment:
  //   flutter run --dart-define=API_URL=http://10.0.2.2:8000/api   (local dev)
  //   flutter build apk --dart-define=API_URL=https://mkulima.hudumapro.com/api
  const apiUrl = String.fromEnvironment(
    'API_URL',
    defaultValue: 'http://10.0.2.2:8000/api',
  );
  final api = ApiService(baseUrl: apiUrl);

  runApp(MkulimaApp(db: db, api: api));
}

class MkulimaApp extends StatelessWidget {
  final LocalDatabase db;
  final ApiService api;

  const MkulimaApp({super.key, required this.db, required this.api});

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        Provider.value(value: api),
        ChangeNotifierProvider(create: (_) => ConnectivityProvider()),
        ChangeNotifierProvider(
          create: (_) => AuthProvider(api: api, db: db),
        ),
        ChangeNotifierProvider(create: (_) => CartProvider()),
      ],
      child: MaterialApp.router(
        title: 'MkulimaForum',
        debugShowCheckedModeBanner: false,
        theme: mkLightTheme(),
        darkTheme: mkDarkTheme(),
        routerConfig: appRouter,
      ),
    );
  }
}
