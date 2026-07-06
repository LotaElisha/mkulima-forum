import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

import 'package:mkulima_app/core/theme.dart';
import 'package:mkulima_app/widgets/mk_empty_state.dart';

void main() {
  testWidgets('MkEmptyState renders title, subtitle and action',
      (WidgetTester tester) async {
    var tapped = false;

    await tester.pumpWidget(
      MaterialApp(
        theme: mkLightTheme(),
        home: Scaffold(
          body: MkEmptyState(
            title: 'Hakuna taarifa kwa sasa',
            subtitle: 'Jaribu tena baadaye',
            actionLabel: 'Jaribu tena',
            onAction: () => tapped = true,
          ),
        ),
      ),
    );

    expect(find.text('Hakuna taarifa kwa sasa'), findsOneWidget);
    expect(find.text('Jaribu tena baadaye'), findsOneWidget);

    await tester.tap(find.text('Jaribu tena'));
    expect(tapped, isTrue);
  });

  testWidgets('MkEmptyState hides action when not provided',
      (WidgetTester tester) async {
    await tester.pumpWidget(
      const MaterialApp(
        home: Scaffold(
          body: MkEmptyState(title: 'Tupu'),
        ),
      ),
    );

    expect(find.byType(FilledButton), findsNothing);
  });
}
