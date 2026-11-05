import 'package:dio/dio.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:mkulima_app/core/api_error.dart';
import 'package:mkulima_app/trace/trace_ids.dart';
import 'package:mkulima_app/trace/trace_sync.dart';
import 'package:shared_preferences/shared_preferences.dart';

DioException _offline(String path) => DioException(
  requestOptions: RequestOptions(path: path),
  type: DioExceptionType.connectionError,
);

DioException _refused(String path, int status, String message) => DioException(
  requestOptions: RequestOptions(path: path),
  type: DioExceptionType.badResponse,
  response: Response(
    requestOptions: RequestOptions(path: path),
    statusCode: status,
    data: {'message': message, 'errors': {}},
  ),
);

TraceOp _op(String label) => TraceOp(
  id: TraceIds.uuid(),
  kind: 'event',
  path: '/v1/trace/$label',
  label: label,
  body: {'uuid': TraceIds.uuid()},
);

void main() {
  group('TraceIds', () {
    test('makes v4 UUIDs and codes in the server alphabet', () {
      final uuid = TraceIds.uuid();
      expect(
        uuid,
        matches(
          RegExp(
            r'^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$',
          ),
        ),
      );
      final codes = {for (var i = 0; i < 200; i++) TraceIds.code()};
      expect(codes.length, 200);
      for (final c in codes) {
        expect(TraceIds.isCode(c), isTrue, reason: c);
        expect(c, isNot(matches(RegExp('[01ILO]'))));
      }
    });

    test('reads a scanned link or a typed code, and nothing else', () {
      expect(
        TraceIds.fromScan('https://mkulimaforum.com/t/ABCDEFGH23'),
        'ABCDEFGH23',
      );
      expect(
        TraceIds.fromScan('https://mkulimaforum.com/t/abcdefgh23?x=1'),
        'ABCDEFGH23',
      );
      expect(TraceIds.fromScan(' abcde fgh23 '), 'ABCDEFGH23');
      expect(
        TraceIds.fromScan('ABCDEFGH2O'),
        isNull,
        reason: 'O is not in the alphabet',
      );
      expect(TraceIds.fromScan('https://example.com/other'), isNull);
    });

    test('the QR link carries only the code', () {
      final link = TraceIds.link('ABCDEFGH23');
      expect(link, endsWith('/t/ABCDEFGH23'));
      expect(link, isNot(contains('/api')));
    });
  });

  group('TraceSync', () {
    setUp(() {
      SharedPreferences.setMockInitialValues({});
      TraceSync.instance.resetForTest();
    });

    test(
      'offline writes wait on the phone and are not claimed as sent',
      () async {
        final sync = TraceSync.instance;
        final result = await sync.submit(
          (_, _) async => throw StateError('must not post'),
          _op('harvest'),
          online: false,
        );
        expect(result.outcome, TraceOutcome.queued);
        expect(sync.pendingCount, 1);
        expect(sync.ops.single.isSynced, isFalse);
      },
    );

    test('the queue survives a restart', () async {
      await TraceSync.instance.submit(
        (_, _) async => {},
        _op('harvest'),
        online: false,
      );
      TraceSync.instance.resetForTest();
      await TraceSync.instance.load();
      expect(TraceSync.instance.pendingCount, 1);
      expect(TraceSync.instance.ops.single.label, 'harvest');
    });

    test(
      'sends in order, stops on a dropped connection, and replays the same body',
      () async {
        final sync = TraceSync.instance;
        final a = _op('a');
        final b = _op('b');
        await sync.submit((_, _) async => {}, a, online: false);
        await sync.submit((_, _) async => {}, b, online: false);

        final sent = <String>[];
        await sync.syncNow((path, body) async {
          sent.add(path);
          throw _offline(path);
        });
        expect(sent, ['/v1/trace/a'], reason: 'b must not overtake a');
        expect(sync.pendingCount, 2);

        final bodies = <Map<String, dynamic>>[];
        await sync.syncNow((path, body) async {
          bodies.add(body);
          return {'data': {}};
        });
        expect(sync.pendingCount, 0);
        expect(
          bodies.first['uuid'],
          a.body['uuid'],
          reason: 'the retry carries the same UUID',
        );
      },
    );

    test(
      'a refusal is marked failed with the server message and the rest still go',
      () async {
        final sync = TraceSync.instance;
        await sync.submit((_, _) async => {}, _op('a'), online: false);
        await sync.submit((_, _) async => {}, _op('b'), online: false);

        await sync.syncNow((path, _) async {
          if (path.endsWith('/a')) {
            throw _refused(path, 403, 'Usajili wako unasubiri msimamizi.');
          }
          return {};
        });
        expect(sync.failedCount, 1);
        expect(sync.ops.first.error, 'Usajili wako unasubiri msimamizi.');
        expect(sync.ops.last.isSynced, isTrue);

        await sync.discard(sync.ops.first.id);
        expect(sync.failedCount, 0);
      },
    );

    test(
      'online: a refusal goes back to the form and nothing is queued',
      () async {
        final sync = TraceSync.instance;
        await expectLater(
          sync.submit(
            (path, _) async => throw _refused(
              path,
              422,
              'Eleza sababu ya upungufu wa 180 kg.',
            ),
            _op('a'),
            online: true,
          ),
          throwsA(
            isA<ApiError>().having(
              (e) => e.message,
              'message',
              contains('180 kg'),
            ),
          ),
        );
        expect(sync.ops, isEmpty);
      },
    );

    test(
      'online: a dropped connection or server error queues the write',
      () async {
        final sync = TraceSync.instance;
        final r1 = await sync.submit(
          (path, _) async => throw _offline(path),
          _op('a'),
          online: true,
        );
        expect(r1.outcome, TraceOutcome.queued);
        final r2 = await sync.submit(
          (path, _) async => throw _refused(path, 503, 'down'),
          _op('b'),
          online: true,
        );
        expect(r2.outcome, TraceOutcome.queued);
        expect(sync.pendingCount, 2);
      },
    );

    test('keeps the last copy of a lot for offline scans', () async {
      final sync = TraceSync.instance;
      await sync.rememberLot({
        'uuid': 'u-1',
        'code': 'ABCDEFGH23',
        'crop': 'mahindi',
      });
      expect(sync.lot('ABCDEFGH23')?['crop'], 'mahindi');
      expect(sync.lot('u-1')?['code'], 'ABCDEFGH23');
    });
  });
}
