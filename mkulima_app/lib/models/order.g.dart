// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'order.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_$OrderImpl _$$OrderImplFromJson(Map<String, dynamic> json) => _$OrderImpl(
      id: _idFromJson(json['id']),
      buyerId: _idFromJson(json['buyer_id']),
      sellerId: _idFromJson(json['seller_id']),
      items: (json['items'] as List<dynamic>)
          .map((e) => OrderItem.fromJson(e as Map<String, dynamic>))
          .toList(),
      total: _priceFromJson(json['total']),
      status: json['status'] as String? ?? 'pending',
      escrowId: json['escrowId'] as String?,
      deliveryAddress: json['deliveryAddress'] as String?,
      createdAt: json['createdAt'] == null
          ? null
          : DateTime.parse(json['createdAt'] as String),
    );

Map<String, dynamic> _$$OrderImplToJson(_$OrderImpl instance) =>
    <String, dynamic>{
      'id': _idToJson(instance.id),
      'buyer_id': _idToJson(instance.buyerId),
      'seller_id': _idToJson(instance.sellerId),
      'items': instance.items,
      'total': _priceToJson(instance.total),
      'status': instance.status,
      'escrowId': instance.escrowId,
      'deliveryAddress': instance.deliveryAddress,
      'createdAt': instance.createdAt?.toIso8601String(),
    };

_$OrderItemImpl _$$OrderItemImplFromJson(Map<String, dynamic> json) =>
    _$OrderItemImpl(
      productId: _idFromJson(json['product_id']),
      quantity: (json['quantity'] as num).toInt(),
      unitPrice: _priceFromJson(json['unitPrice']),
      subtotal: _priceFromJson(json['subtotal']),
    );

Map<String, dynamic> _$$OrderItemImplToJson(_$OrderItemImpl instance) =>
    <String, dynamic>{
      'product_id': _idToJson(instance.productId),
      'quantity': instance.quantity,
      'unitPrice': _priceToJson(instance.unitPrice),
      'subtotal': _priceToJson(instance.subtotal),
    };
