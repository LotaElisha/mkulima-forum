import 'package:flutter_test/flutter_test.dart';
import 'package:mkulima_app/models/app_notification.dart';

/// Regression tests for the crash that took the Arifa screen down:
///
///   type 'List<dynamic>' is not a subtype of type
///   'FutureOr<Map<String, dynamic>>'
///
/// The envelope below is the real response captured from
/// GET /api/notifications on a running server, not an invented shape.
void main() {
  group('NotificationFeed.fromResponse', () {
    test('parses the real API envelope', () {
      final body = {
        'notifications': [
          {
            'id': 'welcome_23',
            'title': 'Karibu Mkulima Forum!',
            'message': 'Asante kwa kujiunga. Anza kununua au kuuza bidhaa za kilimo.',
            'type': 'system',
            'read': false,
            'created_at': '2026-08-23T07:48:56+00:00',
            'data': null,
          },
        ],
        'unread_count': 1,
      };

      final feed = NotificationFeed.fromResponse(body);

      expect(feed.items, hasLength(1));
      expect(feed.unreadCount, 1);
      expect(feed.items.first.id, 'welcome_23');
      expect(feed.items.first.type, 'system');
      expect(feed.items.first.read, isFalse);
      expect(feed.items.first.createdAt, isNotNull);
      expect(feed.items.first.data, isNull);
    });

    test('accepts a bare list, which is what used to crash the screen', () {
      final feed = NotificationFeed.fromResponse([
        {'id': 'a', 'title': 'T', 'message': 'M', 'type': 'order', 'read': true},
      ]);

      expect(feed.items, hasLength(1));
      expect(feed.unreadCount, 0);
    });

    test('accepts a paginated envelope without being taught about it', () {
      // Nobody has paginated this endpoint yet. When someone does, the screen
      // must not be the thing that discovers it.
      final feed = NotificationFeed.fromResponse({
        'data': {
          'data': [
            {'id': 'a', 'title': 'T', 'message': 'M', 'type': 'order', 'read': false},
          ],
          'current_page': 1,
        },
      });

      expect(feed.items, hasLength(1));
      expect(feed.unreadCount, 1);
    });

    test('empty is empty, not an error', () {
      expect(NotificationFeed.fromResponse({'notifications': [], 'unread_count': 0}).isEmpty, isTrue);
      expect(NotificationFeed.fromResponse(const []).isEmpty, isTrue);
      expect(NotificationFeed.fromResponse(null).isEmpty, isTrue);
      expect(NotificationFeed.fromResponse('unexpected').isEmpty, isTrue);
    });

    test('survives rows that are not maps', () {
      final feed = NotificationFeed.fromResponse({
        'notifications': [
          'nonsense',
          42,
          {'id': 'ok', 'title': 'T', 'message': 'M', 'type': 'order', 'read': false},
        ],
      });

      expect(feed.items, hasLength(1));
      expect(feed.items.first.id, 'ok');
    });

    test('reads booleans however the database spelled them', () {
      // MySQL sends tinyint 1/0, SQLite true/false, a JSON cast can send "1".
      for (final value in [true, 1, '1', 'true']) {
        final feed = NotificationFeed.fromResponse({
          'notifications': [
            {'id': 'x', 'title': 'T', 'message': 'M', 'type': 'order', 'read': value},
          ],
        });
        expect(feed.items.first.read, isTrue, reason: 'read: $value');
      }

      for (final value in [false, 0, '0', null]) {
        final feed = NotificationFeed.fromResponse({
          'notifications': [
            {'id': 'x', 'title': 'T', 'message': 'M', 'type': 'order', 'read': value},
          ],
        });
        expect(feed.items.first.read, isFalse, reason: 'read: $value');
      }
    });

    test('missing fields fall back rather than throwing', () {
      final feed = NotificationFeed.fromResponse({
        'notifications': [<String, dynamic>{}],
      });

      final item = feed.items.single;
      expect(item.id, '');
      expect(item.type, 'system');
      expect(item.read, isFalse);
      expect(item.createdAt, isNull);
    });

    test('trusts the server unread count over its own arithmetic', () {
      final feed = NotificationFeed.fromResponse({
        'notifications': [
          {'id': 'a', 'title': 'T', 'message': 'M', 'type': 'order', 'read': true},
        ],
        'unread_count': 7,
      });

      expect(feed.unreadCount, 7);
    });
  });
}
