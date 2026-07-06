import 'package:freezed_annotation/freezed_annotation.dart';

part 'order.freezed.dart';
part 'order.g.dart';

@freezed
class Order with _$Order {
  const factory Order({
    @JsonKey(fromJson: _idFromJson, toJson: _idToJson) required String id,
    @JsonKey(name: 'buyer_id', fromJson: _idFromJson, toJson: _idToJson) required String buyerId,
    @JsonKey(name: 'seller_id', fromJson: _idFromJson, toJson: _idToJson) required String sellerId,
    required List<OrderItem> items,
    @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson) required double total,
    @Default('pending') String status,
    String? escrowId,
    String? deliveryAddress,
    DateTime? createdAt,
  }) = _Order;

  factory Order.fromJson(Map<String, dynamic> json) => _$OrderFromJson(json);
}

@freezed
class OrderItem with _$OrderItem {
  const factory OrderItem({
    @JsonKey(name: 'product_id', fromJson: _idFromJson, toJson: _idToJson) required String productId,
    required int quantity,
    @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson) required double unitPrice,
    @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson) required double subtotal,
  }) = _OrderItem;

  factory OrderItem.fromJson(Map<String, dynamic> json) =>
      _$OrderItemFromJson(json);
}

// Handle int or String IDs from API
String _idFromJson(dynamic id) => id?.toString() ?? '';
dynamic _idToJson(String id) => id;

// Handle string or double prices from API
double _priceFromJson(dynamic price) {
  if (price == null) return 0.0;
  if (price is double) return price;
  if (price is int) return price.toDouble();
  if (price is String) return double.tryParse(price) ?? 0.0;
  return 0.0;
}
dynamic _priceToJson(double price) => price;
