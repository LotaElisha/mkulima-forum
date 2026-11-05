import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mkulima_app/providers/connectivity_provider.dart';
import 'package:mkulima_app/screens/trace/trace_lot_screen.dart';
import 'package:mkulima_app/screens/trace/trace_sync_screen.dart';
import 'package:mkulima_app/services/api_service.dart';
import 'package:mkulima_app/trace/trace_ids.dart';
import 'package:mkulima_app/trace/trace_sync.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';

/// Screens render at the 360 px floor from the copy kept on the phone, with
/// the server unreachable, and never call an offline write "confirmed".
void main() {
  setUp(() {
    SharedPreferences.setMockInitialValues({});
    TraceSync.instance.resetForTest();
    final messenger =
        TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger;
    messenger.setMockMethodCallHandler(
      const MethodChannel('dev.fluttercommunity.plus/connectivity'),
      (_) async => ['none'],
    );
    messenger.setMockMethodCallHandler(
      const MethodChannel('dev.fluttercommunity.plus/connectivity_status'),
      (_) async => null,
    );
  });

  Widget app(Widget child) => MultiProvider(
    providers: [
      Provider<ApiService>(
        create: (_) => ApiService(baseUrl: 'http://127.0.0.1:9/api'),
      ),
      ChangeNotifierProvider(create: (_) => ConnectivityProvider()),
    ],
    child: MaterialApp(home: child),
  );

  Future<void> phone(WidgetTester tester) async {
    tester.view.physicalSize = const Size(360 * 2, 780 * 2);
    tester.view.devicePixelRatio = 2;
    addTearDown(tester.view.reset);
  }

  testWidgets('a lot seen before opens offline with its history', (
    tester,
  ) async {
    await phone(tester);
    await TraceSync.instance.rememberLot({
      'tier': 'full',
      'uuid': 'lot-1',
      'code': 'ABCDEFGH23',
      'crop': 'mahindi',
      'quantity': 10,
      'unit': 'bag_90kg',
      'quantity_kg': 900,
      'status': 'active',
      'i_hold': true,
      'plot': {'region': 'Morogoro'},
      'grower': {'type': 'farmer', 'verified': false, 'name': 'Asha Mkulima'},
      'holder': {'type': 'collector', 'verified': true, 'name': 'Ushirika'},
      'parents': [],
      'children': [],
      'timeline': [
        {
          'type': 'harvest',
          'occurred_at': '2026-11-01T08:00:00Z',
          'quantity_kg': 900,
          'confirmation_status': 'not_required',
        },
        {
          'type': 'handoff',
          'occurred_at': '2026-11-02T08:00:00Z',
          'quantity_kg': 900,
          'confirmation_status': 'confirmed',
          'from': {'type': 'farmer', 'name': 'Asha Mkulima'},
          'to': {'type': 'collector', 'name': 'Ushirika'},
          'price_per_kg': 850,
          'currency': 'TZS',
          'payment': {
            'method': 'mobile_money',
            'reference': 'QK12',
            'verified': false,
          },
        },
      ],
    });

    await tester.pumpWidget(
      app(const TraceLotScreen(uuid: 'lot-1', code: 'ABCDEFGH23')),
    );
    await tester.pumpAndSettle();

    expect(find.text('mahindi'), findsOneWidget);
    await tester.scrollUntilVisible(
      find.text('Kabidhi mzigo'),
      300,
      scrollable: find.byType(Scrollable).first,
    );
    expect(find.text('Kabidhi mzigo'), findsOneWidget);
    await tester.scrollUntilVisible(
      find.textContaining('Mkulima Trace haikuhamisha'),
      300,
      scrollable: find.byType(Scrollable).first,
    );
    expect(find.textContaining('Mkulima Trace haikuhamisha'), findsWidgets);
    expect(tester.takeException(), isNull);
  });

  testWidgets(
    'a write waiting on the phone says so, not that it was confirmed',
    (tester) async {
      await phone(tester);
      await TraceSync.instance.submit(
        (_, _) async => {},
        TraceOp(
          id: TraceIds.uuid(),
          kind: 'confirm',
          path: '/v1/trace/events/x/confirm',
          label: 'Kuthibitisha makabidhiano ya ABCDEFGH23',
          body: const {},
        ),
        online: false,
      );

      await tester.pumpWidget(app(const TraceSyncScreen()));
      await tester.pumpAndSettle();

      expect(find.text('Inasubiri kutumwa'), findsOneWidget);
      expect(find.textContaining('Imethibitishwa'), findsNothing);
      expect(tester.takeException(), isNull);
    },
  );
}
