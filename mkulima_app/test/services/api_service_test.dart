import 'package:flutter_test/flutter_test.dart';
import 'package:mkulima_app/services/api_service.dart';

void main() {
  group('ApiService Unit Tests', () {
    late ApiService api;
    bool unauthorizedTriggered = false;

    setUp(() {
      unauthorizedTriggered = false;
      api = ApiService(
        baseUrl: 'https://example.com/api',
        onUnauthorized: () {
          unauthorizedTriggered = true;
        },
      );
    });

    test('ApiService initializes with baseUrl and headers', () {
      expect(api, isNotNull);
      expect(api.onUnauthorized, isNotNull);
    });

    test('onUnauthorized callback can be invoked', () {
      api.onUnauthorized?.call();
      expect(unauthorizedTriggered, isTrue);
    });
  });
}
