import 'package:flutter_test/flutter_test.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:mkulima_app/providers/cart_provider.dart';
import 'package:mkulima_app/models/product.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  group('CartProvider Unit Tests', () {
    late CartProvider cart;
    const testProduct1 = Product(
      id: 'p1',
      name: 'Mahindi Mbegu',
      description: 'Mbegu bora ya mahindi',
      price: 5000.0,
      stock: 100,
      categoryId: 'cat1',
      sellerId: 'seller1',
      unit: 'kg',
    );

    const testProduct2 = Product(
      id: 'p2',
      name: 'Mbolea DAP',
      description: 'Mbolea ya kupandia',
      price: 75000.0,
      stock: 50,
      categoryId: 'cat2',
      sellerId: 'seller1',
      unit: 'mfuko',
    );

    setUp(() async {
      SharedPreferences.setMockInitialValues({});
      cart = CartProvider();
    });

    test('Initial cart is empty', () {
      expect(cart.items, isEmpty);
      expect(cart.itemCount, 0);
      expect(cart.total, 0.0);
    });

    test('Add item to cart increases itemCount and total', () {
      cart.addToCart(testProduct1, quantity: 2);

      expect(cart.items.length, 1);
      expect(cart.itemCount, 2);
      expect(cart.total, 10000.0);
      expect(cart.isInCart('p1'), isTrue);
      expect(cart.getQuantity('p1'), 2);
    });

    test('Add existing item updates quantity instead of duplicating', () {
      cart.addToCart(testProduct1, quantity: 1);
      cart.addToCart(testProduct1, quantity: 3);

      expect(cart.items.length, 1);
      expect(cart.itemCount, 4);
      expect(cart.total, 20000.0);
    });

    test('Update quantity modifies total and item count', () {
      cart.addToCart(testProduct1, quantity: 1);
      cart.addToCart(testProduct2, quantity: 1);

      expect(cart.total, 80000.0);

      cart.updateQuantity('p1', 5);
      expect(cart.getQuantity('p1'), 5);
      expect(cart.total, 100000.0);
    });

    test('Setting quantity to 0 removes item from cart', () {
      cart.addToCart(testProduct1, quantity: 2);
      cart.updateQuantity('p1', 0);

      expect(cart.isInCart('p1'), isFalse);
      expect(cart.items, isEmpty);
    });

    test('Remove item removes specific product', () {
      cart.addToCart(testProduct1);
      cart.addToCart(testProduct2);
      expect(cart.items.length, 2);

      cart.removeFromCart('p1');
      expect(cart.isInCart('p1'), isFalse);
      expect(cart.isInCart('p2'), isTrue);
      expect(cart.items.length, 1);
    });

    test('Clear cart removes all items', () {
      cart.addToCart(testProduct1);
      cart.addToCart(testProduct2);
      cart.clearCart();

      expect(cart.items, isEmpty);
      expect(cart.itemCount, 0);
      expect(cart.total, 0.0);
    });
  });
}
