import 'package:freezed_annotation/freezed_annotation.dart';

part 'product.freezed.dart';
part 'product.g.dart';

@freezed
class Product with _$Product {
  const factory Product({
    @JsonKey(fromJson: _idFromJson, toJson: _idToJson) required String id,
    required String name,
    required String description,
    @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson) required double price,
    @JsonKey(name: 'stock_quantity') required int stock,
    @JsonKey(name: 'category_id', fromJson: _idFromJson, toJson: _idToJson) required String categoryId,
    @JsonKey(name: 'user_id', fromJson: _idFromJson, toJson: _idToJson) required String sellerId,
    required String unit,
    List<String>? images,
    @Default(0) int minOrder,
    @Default(true) bool isAvailable,
    DateTime? createdAt,
  }) = _Product;

  factory Product.fromJson(Map<String, dynamic> json) =>
      _$ProductFromJson(json);
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
