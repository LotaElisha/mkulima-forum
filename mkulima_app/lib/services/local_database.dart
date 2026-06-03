import 'package:drift/drift.dart';
import 'package:drift_flutter/drift_flutter.dart';
import '../models/product.dart' as domain;

part 'local_database.g.dart';

class Products extends Table {
  TextColumn get id => text()();
  TextColumn get name => text()();
  TextColumn get description => text()();
  RealColumn get price => real()();
  IntegerColumn get stock => integer()();
  TextColumn get categoryId => text()();
  TextColumn get sellerId => text()();
  TextColumn get unit => text()();
  TextColumn get images => text().nullable()();
  IntegerColumn get minOrder => integer().withDefault(const Constant(0))();
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
  TextColumn get preferredLanguage => text().withDefault(const Constant('sw'))();

  @override
  Set<Column> get primaryKey => {uuid};
}

@DriftDatabase(tables: [Products, Orders, Users])
class LocalDatabase extends _$LocalDatabase {
  LocalDatabase() : super(_openConnection());

  @override
  int get schemaVersion => 1;

  static QueryExecutor _openConnection() {
    return driftDatabase(name: 'mkulima_database');
  }

  // Product operations
  Future<List<Product>> getAllProducts() => select(products).get();

  Future<void> insertProduct(domain.Product product) async {
    await into(products).insertOnConflictUpdate(ProductsCompanion(
      id: Value(product.id),
      name: Value(product.name),
      description: Value(product.description),
      price: Value(product.price),
      stock: Value(product.stock),
      categoryId: Value(product.categoryId),
      sellerId: Value(product.sellerId),
      unit: Value(product.unit),
      images: Value(product.images?.join(','));
      minOrder: Value(product.minOrder),
      isAvailable: Value(product.isAvailable),
      syncedAt: Value(DateTime.now()),
    ));
  }

  Future<void> insertProducts(List<domain.Product> productList) async {
    await batch((batch) {
      batch.insertAllOnConflictUpdate(
        products,
        productList.map((p) => ProductsCompanion(
          id: Value(p.id),
          name: Value(p.name),
          description: Value(p.description),
          price: Value(p.price),
          stock: Value(p.stock),
          categoryId: Value(p.categoryId),
          sellerId: Value(p.sellerId),
          unit: Value(p.unit),
          images: Value(p.images?.join(',')),
          minOrder: Value(p.minOrder),
          isAvailable: Value(p.isAvailable),
          syncedAt: Value(DateTime.now()),
        )).toList(),
      );
    });
  }

  // Order operations
  Future<List<Order>> getPendingOrders() {
    return (select(orders)..where((o) => o.isSynced.equals(false))).get();
  }

  Future<void> insertOrder(domain.Order order) async {
    await into(orders).insert(OrdersCompanion(
      id: Value(order.id),
      buyerId: Value(order.buyerId),
      sellerId: Value(order.sellerId),
      items: Value(order.items.map((i) => '${i.productId}:${i.quantity}:${i.unitPrice}').join('|')),
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
  Future<void> saveUser(domain.User user, String token) async {
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

  Future<domain.User?> getCurrentUser() async {
    final user = await select(users).getSingleOrNull();
    if (user == null) return null;
    return domain.User(
      uuid: user.uuid,
      name: user.name,
      phone: user.phone,
      email: user.email,
      role: user.role,
      preferredLanguage: user.preferredLanguage,
    );
  }

  Future<String?> getToken() async {
    final user = await select(users).getSingleOrNull();
    return user?.token;
  }

  Future<void> clearUser() async {
    await delete(users).go();
  }
}
