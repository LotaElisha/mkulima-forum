// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'product.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_$ProductImpl _$$ProductImplFromJson(Map<String, dynamic> json) =>
    _$ProductImpl(
      id: _idFromJson(json['id']),
      name: json['name'] as String,
      description: json['description'] as String,
      price: _priceFromJson(json['price']),
      stock: (json['stock_quantity'] as num).toInt(),
      categoryId: _idFromJson(json['category_id']),
      sellerId: _idFromJson(json['user_id']),
      unit: json['unit'] as String,
      images:
          (json['images'] as List<dynamic>?)?.map((e) => e as String).toList(),
      minOrder: (json['minOrder'] as num?)?.toInt() ?? 0,
      isAvailable: json['isAvailable'] as bool? ?? true,
      createdAt: json['createdAt'] == null
          ? null
          : DateTime.parse(json['createdAt'] as String),
    );

Map<String, dynamic> _$$ProductImplToJson(_$ProductImpl instance) =>
    <String, dynamic>{
      'id': _idToJson(instance.id),
      'name': instance.name,
      'description': instance.description,
      'price': _priceToJson(instance.price),
      'stock_quantity': instance.stock,
      'category_id': _idToJson(instance.categoryId),
      'user_id': _idToJson(instance.sellerId),
      'unit': instance.unit,
      'images': instance.images,
      'minOrder': instance.minOrder,
      'isAvailable': instance.isAvailable,
      'createdAt': instance.createdAt?.toIso8601String(),
    };
