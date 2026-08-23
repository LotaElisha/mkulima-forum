import 'package:drift/drift.dart';
import 'package:drift/wasm.dart';

/// Web backend for drift. Requires two static assets in `web/`:
///   curl -Lo web/sqlite3.wasm https://github.com/simolus3/sqlite3.dart/releases/latest/download/sqlite3.wasm
///   curl -Lo web/drift_worker.js https://github.com/simolus3/drift/releases/latest/download/drift_worker.js
/// Falls back to in-memory storage if the browser lacks OPFS/IndexedDB.
QueryExecutor openConnection() {
  return DatabaseConnection.delayed(Future(() async {
    final result = await WasmDatabase.open(
      databaseName: 'mkulima_database',
      // Production shared hosting blocks static WebAssembly payloads. Laravel
      // exposes this fixed, non-user-controlled asset from private storage.
      sqlite3Uri: Uri.parse('/web-app-assets/sqlite'),
      driftWorkerUri: Uri.parse('/web-app-assets/drift-worker'),
    );
    return result.resolvedExecutor;
  }));
}
