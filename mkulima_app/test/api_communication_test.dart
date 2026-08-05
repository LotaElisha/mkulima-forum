import 'package:flutter_test/flutter_test.dart';
import 'package:mkulima_app/services/api_service.dart';

void main() {
  group('App-to-Web API Communication Tests', () {
    late ApiService api;

    setUp(() {
      // Connects directly to the live production server API to test remote integration
      api = ApiService(baseUrl: 'https://mkulimaforum.app/api');
    });

    test('Public Input Verification Checklist endpoint', () async {
      final checklist = await api.get('/inputs/checklist');
      
      expect(checklist.statusCode, 200);
      expect(checklist.data, isNotNull);
      expect(checklist.data['title'], equals('Kagua Dawa Kabla ya Kununua'));
      expect(checklist.data['items'], isNotEmpty);
      
      final firstItem = checklist.data['items'][0];
      expect(firstItem['key'], isNotNull);
      expect(firstItem['text'], isNotNull);
      expect(firstItem['weight'], isNotNull);
    });

    test('Public Weather Report API endpoint', () async {
      // Fetch default region weather
      final weather = await api.getWeather(location: 'Mbeya');
      
      expect(weather, isNotNull);
      expect(weather['available'], isTrue);
      expect(weather['location'], equals('Mbeya'));
      expect(weather['report'], isNotNull);
      expect(weather['report']['temp'], isNotNull);
      expect(weather['report']['condition'], isNotNull);
    });

    test('Public Marketplace Products list endpoint', () async {
      final response = await api.get('/marketplace/products');
      
      expect(response.statusCode, 200);
      expect(response.data, isNotNull);
      expect(response.data['products'], isNotNull);
    });
  });
}
