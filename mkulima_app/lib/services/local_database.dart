import 'package:drift/drift.dart';
import 'dart:io';
import 'package:drift/native.dart';
import 'package:path_provider/path_provider.dart';
import 'package:path/path.dart' as p;

part 'local_database.g.dart';

class Products extends Table {
  TextColumn get id => text()();
  TextColumn get name => text()();
  TextColumn get description => text()();
  RealColumn get price => real()();
  IntColumn get stock => integer()();
  TextColumn get categoryId => text()();
  TextColumn get sellerId => text()();
  TextColumn get unit => text()();
  TextColumn get images => text().nullable()();
  IntColumn get minOrder => integer().withDefault(const Constant(0))();
  BoolColumn get isAvailable => boolean().withDefault(const Constant(true))();
  DateTimeColumn get createdAt => dateTime().nullable()();
  DateTimeColumn get syncedAt => dateTime().nullable()();

  @override
  Set<Column> get primaryKey => {id};
}

class Orders extends Table {
  TextColumn get id => text()();
  TextColumn get buyerId => text()();
  TextColumn get sellerId => text()();
  TextColumn get items => text()();
  RealColumn get total => real()();
  TextColumn get status => text().withDefault(const Constant('pending'))();
  TextColumn get escrowId => text().nullable()();
  TextColumn get deliveryAddress => text().nullable()();
  DateTimeColumn get createdAt => dateTime().nullable()();
  BoolColumn get isSynced => boolean().withDefault(const Constant(false))();

  @override
  Set<Column> get primaryKey => {id};
}

class Users extends Table {
  TextColumn get uuid => text()();
  TextColumn get name => text()();
  TextColumn get phone => text()();
  TextColumn get email => text().nullable()();
  TextColumn get role => text()();
  TextColumn get token => text().nullable()();
  TextColumn get preferredLanguage =>
      text().withDefault(const Constant('sw'))();

  @override
  Set<Column> get primaryKey => {uuid};
}

@DriftDatabase(tables: [Products, Orders, Users])
class LocalDatabase extends _$LocalDatabase {
  LocalDatabase() : super(_openConnection());

  @override
  int get schemaVersion => 1;

  static QueryExecutor _openConnection() {
    return LazyDatabase(() async {
      final dbFolder = await getApplicationDocumentsDirectory();
      final file = File(p.join(dbFolder.path, 'mkulima_database.db'));
      return NativeDatabase.createInBackground(file);
    });
  }

  // Product operations
  Future<List<Product>> getAllProducts() => select(products).get();

  Future<void> insertProduct(dynamic product) async {
    await into(products).insertOnConflictUpdate(ProductsCompanion(
      id: Value(product.id),
      name: Value(product.name),
      description: Value(product.description),
      price: Value(product.price),
      stock: Value(product.stock),
      categoryId: Value(product.categoryId),
      sellerId: Value(product.sellerId),
      unit: Value(product.unit),
      images: Value(product.images?.join(',')),
      minOrder: Value(product.minOrder ?? 0),
      isAvailable: Value(product.isAvailable ?? true),
      syncedAt: Value(DateTime.now()),
    ));
  }

  Future<void> insertProducts(List<dynamic> productList) async {
    for (final p in productList) {
      await insertProduct(p);
    }
  }

  // Order operations
  Future<List<Order>> getPendingOrders() {
    return (select(orders)..where((o) => o.isSynced.equals(false))).get();
  }

  Future<void> insertOrder(dynamic order) async {
    await into(orders).insert(OrdersCompanion(
      id: Value(order.id),
      buyerId: Value(order.buyerId),
      sellerId: Value(order.sellerId),
      items: Value('items'),
      total: Value(order.total),
      status: Value(order.status),
      escrowId: Value(order.escrowId),
      deliveryAddress: Value(order.deliveryAddress),
      isSynced: const Value(false),
    ));
  }

  Future<void> markOrderSynced(String orderId) async {
    await (update(orders)..where((o) => o.id.equals(orderId)))
        .write(const OrdersCompanion(isSynced: Value(true)));
  }

  // User operations
  Future<void> saveUser(dynamic user, String token) async {
    await into(users).insertOnConflictUpdate(UsersCompanion(
      uuid: Value(user.uuid),
      name: Value(user.name),
      phone: Value(user.phone),
      email: Value(user.email),
      role: Value(user.role),
      token: Value(token),
      preferredLanguage: Value(user.preferredLanguage),
    ));
  }

  Future<dynamic> getCurrentUser() async {
    final user = await select(users).getSingleOrNull();
    if (user == null) return null;
    return user;
  }

  Future<String?> getToken() async {
    final user = await select(users).getSingleOrNull();
    return user?.token;
  }

  Future<void> clearUser() async {
    await delete(users).go();
  }
}
