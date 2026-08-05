// coverage:ignore-file
// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'order.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

T _$identity<T>(T value) => value;

final _privateConstructorUsedError = UnsupportedError(
    'It seems like you constructed your class using `MyClass._()`. This constructor is only meant to be used by freezed and you are not supposed to need it nor use it.\nPlease check the documentation here for more information: https://github.com/rrousselGit/freezed#adding-getters-and-methods-to-our-models');

Order _$OrderFromJson(Map<String, dynamic> json) {
  return _Order.fromJson(json);
}

/// @nodoc
mixin _$Order {
  @JsonKey(fromJson: _idFromJson, toJson: _idToJson)
  String get id => throw _privateConstructorUsedError;
  @JsonKey(name: 'buyer_id', fromJson: _idFromJson, toJson: _idToJson)
  String get buyerId => throw _privateConstructorUsedError;
  @JsonKey(name: 'seller_id', fromJson: _idFromJson, toJson: _idToJson)
  String get sellerId => throw _privateConstructorUsedError;
  List<OrderItem> get items => throw _privateConstructorUsedError;
  @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson)
  double get total => throw _privateConstructorUsedError;
  String get status => throw _privateConstructorUsedError;
  String? get escrowId => throw _privateConstructorUsedError;
  String? get deliveryAddress => throw _privateConstructorUsedError;
  DateTime? get createdAt => throw _privateConstructorUsedError;

  /// Serializes this Order to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of Order
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $OrderCopyWith<Order> get copyWith => throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $OrderCopyWith<$Res> {
  factory $OrderCopyWith(Order value, $Res Function(Order) then) =
      _$OrderCopyWithImpl<$Res, Order>;
  @useResult
  $Res call(
      {@JsonKey(fromJson: _idFromJson, toJson: _idToJson) String id,
      @JsonKey(name: 'buyer_id', fromJson: _idFromJson, toJson: _idToJson)
      String buyerId,
      @JsonKey(name: 'seller_id', fromJson: _idFromJson, toJson: _idToJson)
      String sellerId,
      List<OrderItem> items,
      @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson) double total,
      String status,
      String? escrowId,
      String? deliveryAddress,
      DateTime? createdAt});
}

/// @nodoc
class _$OrderCopyWithImpl<$Res, $Val extends Order>
    implements $OrderCopyWith<$Res> {
  _$OrderCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of Order
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? buyerId = null,
    Object? sellerId = null,
    Object? items = null,
    Object? total = null,
    Object? status = null,
    Object? escrowId = freezed,
    Object? deliveryAddress = freezed,
    Object? createdAt = freezed,
  }) {
    return _then(_value.copyWith(
      id: null == id
          ? _value.id
          : id // ignore: cast_nullable_to_non_nullable
              as String,
      buyerId: null == buyerId
          ? _value.buyerId
          : buyerId // ignore: cast_nullable_to_non_nullable
              as String,
      sellerId: null == sellerId
          ? _value.sellerId
          : sellerId // ignore: cast_nullable_to_non_nullable
              as String,
      items: null == items
          ? _value.items
          : items // ignore: cast_nullable_to_non_nullable
              as List<OrderItem>,
      total: null == total
          ? _value.total
          : total // ignore: cast_nullable_to_non_nullable
              as double,
      status: null == status
          ? _value.status
          : status // ignore: cast_nullable_to_non_nullable
              as String,
      escrowId: freezed == escrowId
          ? _value.escrowId
          : escrowId // ignore: cast_nullable_to_non_nullable
              as String?,
      deliveryAddress: freezed == deliveryAddress
          ? _value.deliveryAddress
          : deliveryAddress // ignore: cast_nullable_to_non_nullable
              as String?,
      createdAt: freezed == createdAt
          ? _value.createdAt
          : createdAt // ignore: cast_nullable_to_non_nullable
              as DateTime?,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$OrderImplCopyWith<$Res> implements $OrderCopyWith<$Res> {
  factory _$$OrderImplCopyWith(
          _$OrderImpl value, $Res Function(_$OrderImpl) then) =
      __$$OrderImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {@JsonKey(fromJson: _idFromJson, toJson: _idToJson) String id,
      @JsonKey(name: 'buyer_id', fromJson: _idFromJson, toJson: _idToJson)
      String buyerId,
      @JsonKey(name: 'seller_id', fromJson: _idFromJson, toJson: _idToJson)
      String sellerId,
      List<OrderItem> items,
      @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson) double total,
      String status,
      String? escrowId,
      String? deliveryAddress,
      DateTime? createdAt});
}

/// @nodoc
class __$$OrderImplCopyWithImpl<$Res>
    extends _$OrderCopyWithImpl<$Res, _$OrderImpl>
    implements _$$OrderImplCopyWith<$Res> {
  __$$OrderImplCopyWithImpl(
      _$OrderImpl _value, $Res Function(_$OrderImpl) _then)
      : super(_value, _then);

  /// Create a copy of Order
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? id = null,
    Object? buyerId = null,
    Object? sellerId = null,
    Object? items = null,
    Object? total = null,
    Object? status = null,
    Object? escrowId = freezed,
    Object? deliveryAddress = freezed,
    Object? createdAt = freezed,
  }) {
    return _then(_$OrderImpl(
      id: null == id
          ? _value.id
          : id // ignore: cast_nullable_to_non_nullable
              as String,
      buyerId: null == buyerId
          ? _value.buyerId
          : buyerId // ignore: cast_nullable_to_non_nullable
              as String,
      sellerId: null == sellerId
          ? _value.sellerId
          : sellerId // ignore: cast_nullable_to_non_nullable
              as String,
      items: null == items
          ? _value._items
          : items // ignore: cast_nullable_to_non_nullable
              as List<OrderItem>,
      total: null == total
          ? _value.total
          : total // ignore: cast_nullable_to_non_nullable
              as double,
      status: null == status
          ? _value.status
          : status // ignore: cast_nullable_to_non_nullable
              as String,
      escrowId: freezed == escrowId
          ? _value.escrowId
          : escrowId // ignore: cast_nullable_to_non_nullable
              as String?,
      deliveryAddress: freezed == deliveryAddress
          ? _value.deliveryAddress
          : deliveryAddress // ignore: cast_nullable_to_non_nullable
              as String?,
      createdAt: freezed == createdAt
          ? _value.createdAt
          : createdAt // ignore: cast_nullable_to_non_nullable
              as DateTime?,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$OrderImpl implements _Order {
  const _$OrderImpl(
      {@JsonKey(fromJson: _idFromJson, toJson: _idToJson) required this.id,
      @JsonKey(name: 'buyer_id', fromJson: _idFromJson, toJson: _idToJson)
      required this.buyerId,
      @JsonKey(name: 'seller_id', fromJson: _idFromJson, toJson: _idToJson)
      required this.sellerId,
      required final List<OrderItem> items,
      @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson)
      required this.total,
      this.status = 'pending',
      this.escrowId,
      this.deliveryAddress,
      this.createdAt})
      : _items = items;

  factory _$OrderImpl.fromJson(Map<String, dynamic> json) =>
      _$$OrderImplFromJson(json);

  @override
  @JsonKey(fromJson: _idFromJson, toJson: _idToJson)
  final String id;
  @override
  @JsonKey(name: 'buyer_id', fromJson: _idFromJson, toJson: _idToJson)
  final String buyerId;
  @override
  @JsonKey(name: 'seller_id', fromJson: _idFromJson, toJson: _idToJson)
  final String sellerId;
  final List<OrderItem> _items;
  @override
  List<OrderItem> get items {
    if (_items is EqualUnmodifiableListView) return _items;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_items);
  }

  @override
  @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson)
  final double total;
  @override
  @JsonKey()
  final String status;
  @override
  final String? escrowId;
  @override
  final String? deliveryAddress;
  @override
  final DateTime? createdAt;

  @override
  String toString() {
    return 'Order(id: $id, buyerId: $buyerId, sellerId: $sellerId, items: $items, total: $total, status: $status, escrowId: $escrowId, deliveryAddress: $deliveryAddress, createdAt: $createdAt)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$OrderImpl &&
            (identical(other.id, id) || other.id == id) &&
            (identical(other.buyerId, buyerId) || other.buyerId == buyerId) &&
            (identical(other.sellerId, sellerId) ||
                other.sellerId == sellerId) &&
            const DeepCollectionEquality().equals(other._items, _items) &&
            (identical(other.total, total) || other.total == total) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.escrowId, escrowId) ||
                other.escrowId == escrowId) &&
            (identical(other.deliveryAddress, deliveryAddress) ||
                other.deliveryAddress == deliveryAddress) &&
            (identical(other.createdAt, createdAt) ||
                other.createdAt == createdAt));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode => Object.hash(
      runtimeType,
      id,
      buyerId,
      sellerId,
      const DeepCollectionEquality().hash(_items),
      total,
      status,
      escrowId,
      deliveryAddress,
      createdAt);

  /// Create a copy of Order
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$OrderImplCopyWith<_$OrderImpl> get copyWith =>
      __$$OrderImplCopyWithImpl<_$OrderImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$OrderImplToJson(
      this,
    );
  }
}

abstract class _Order implements Order {
  const factory _Order(
      {@JsonKey(fromJson: _idFromJson, toJson: _idToJson)
      required final String id,
      @JsonKey(name: 'buyer_id', fromJson: _idFromJson, toJson: _idToJson)
      required final String buyerId,
      @JsonKey(name: 'seller_id', fromJson: _idFromJson, toJson: _idToJson)
      required final String sellerId,
      required final List<OrderItem> items,
      @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson)
      required final double total,
      final String status,
      final String? escrowId,
      final String? deliveryAddress,
      final DateTime? createdAt}) = _$OrderImpl;

  factory _Order.fromJson(Map<String, dynamic> json) = _$OrderImpl.fromJson;

  @override
  @JsonKey(fromJson: _idFromJson, toJson: _idToJson)
  String get id;
  @override
  @JsonKey(name: 'buyer_id', fromJson: _idFromJson, toJson: _idToJson)
  String get buyerId;
  @override
  @JsonKey(name: 'seller_id', fromJson: _idFromJson, toJson: _idToJson)
  String get sellerId;
  @override
  List<OrderItem> get items;
  @override
  @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson)
  double get total;
  @override
  String get status;
  @override
  String? get escrowId;
  @override
  String? get deliveryAddress;
  @override
  DateTime? get createdAt;

  /// Create a copy of Order
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$OrderImplCopyWith<_$OrderImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

OrderItem _$OrderItemFromJson(Map<String, dynamic> json) {
  return _OrderItem.fromJson(json);
}

/// @nodoc
mixin _$OrderItem {
  @JsonKey(name: 'product_id', fromJson: _idFromJson, toJson: _idToJson)
  String get productId => throw _privateConstructorUsedError;
  int get quantity => throw _privateConstructorUsedError;
  @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson)
  double get unitPrice => throw _privateConstructorUsedError;
  @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson)
  double get subtotal => throw _privateConstructorUsedError;

  /// Serializes this OrderItem to a JSON map.
  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;

  /// Create a copy of OrderItem
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  $OrderItemCopyWith<OrderItem> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $OrderItemCopyWith<$Res> {
  factory $OrderItemCopyWith(OrderItem value, $Res Function(OrderItem) then) =
      _$OrderItemCopyWithImpl<$Res, OrderItem>;
  @useResult
  $Res call(
      {@JsonKey(name: 'product_id', fromJson: _idFromJson, toJson: _idToJson)
      String productId,
      int quantity,
      @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson) double unitPrice,
      @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson)
      double subtotal});
}

/// @nodoc
class _$OrderItemCopyWithImpl<$Res, $Val extends OrderItem>
    implements $OrderItemCopyWith<$Res> {
  _$OrderItemCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  /// Create a copy of OrderItem
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? productId = null,
    Object? quantity = null,
    Object? unitPrice = null,
    Object? subtotal = null,
  }) {
    return _then(_value.copyWith(
      productId: null == productId
          ? _value.productId
          : productId // ignore: cast_nullable_to_non_nullable
              as String,
      quantity: null == quantity
          ? _value.quantity
          : quantity // ignore: cast_nullable_to_non_nullable
              as int,
      unitPrice: null == unitPrice
          ? _value.unitPrice
          : unitPrice // ignore: cast_nullable_to_non_nullable
              as double,
      subtotal: null == subtotal
          ? _value.subtotal
          : subtotal // ignore: cast_nullable_to_non_nullable
              as double,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$OrderItemImplCopyWith<$Res>
    implements $OrderItemCopyWith<$Res> {
  factory _$$OrderItemImplCopyWith(
          _$OrderItemImpl value, $Res Function(_$OrderItemImpl) then) =
      __$$OrderItemImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {@JsonKey(name: 'product_id', fromJson: _idFromJson, toJson: _idToJson)
      String productId,
      int quantity,
      @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson) double unitPrice,
      @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson)
      double subtotal});
}

/// @nodoc
class __$$OrderItemImplCopyWithImpl<$Res>
    extends _$OrderItemCopyWithImpl<$Res, _$OrderItemImpl>
    implements _$$OrderItemImplCopyWith<$Res> {
  __$$OrderItemImplCopyWithImpl(
      _$OrderItemImpl _value, $Res Function(_$OrderItemImpl) _then)
      : super(_value, _then);

  /// Create a copy of OrderItem
  /// with the given fields replaced by the non-null parameter values.
  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? productId = null,
    Object? quantity = null,
    Object? unitPrice = null,
    Object? subtotal = null,
  }) {
    return _then(_$OrderItemImpl(
      productId: null == productId
          ? _value.productId
          : productId // ignore: cast_nullable_to_non_nullable
              as String,
      quantity: null == quantity
          ? _value.quantity
          : quantity // ignore: cast_nullable_to_non_nullable
              as int,
      unitPrice: null == unitPrice
          ? _value.unitPrice
          : unitPrice // ignore: cast_nullable_to_non_nullable
              as double,
      subtotal: null == subtotal
          ? _value.subtotal
          : subtotal // ignore: cast_nullable_to_non_nullable
              as double,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$OrderItemImpl implements _OrderItem {
  const _$OrderItemImpl(
      {@JsonKey(name: 'product_id', fromJson: _idFromJson, toJson: _idToJson)
      required this.productId,
      required this.quantity,
      @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson)
      required this.unitPrice,
      @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson)
      required this.subtotal});

  factory _$OrderItemImpl.fromJson(Map<String, dynamic> json) =>
      _$$OrderItemImplFromJson(json);

  @override
  @JsonKey(name: 'product_id', fromJson: _idFromJson, toJson: _idToJson)
  final String productId;
  @override
  final int quantity;
  @override
  @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson)
  final double unitPrice;
  @override
  @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson)
  final double subtotal;

  @override
  String toString() {
    return 'OrderItem(productId: $productId, quantity: $quantity, unitPrice: $unitPrice, subtotal: $subtotal)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$OrderItemImpl &&
            (identical(other.productId, productId) ||
                other.productId == productId) &&
            (identical(other.quantity, quantity) ||
                other.quantity == quantity) &&
            (identical(other.unitPrice, unitPrice) ||
                other.unitPrice == unitPrice) &&
            (identical(other.subtotal, subtotal) ||
                other.subtotal == subtotal));
  }

  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  int get hashCode =>
      Object.hash(runtimeType, productId, quantity, unitPrice, subtotal);

  /// Create a copy of OrderItem
  /// with the given fields replaced by the non-null parameter values.
  @JsonKey(includeFromJson: false, includeToJson: false)
  @override
  @pragma('vm:prefer-inline')
  _$$OrderItemImplCopyWith<_$OrderItemImpl> get copyWith =>
      __$$OrderItemImplCopyWithImpl<_$OrderItemImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$OrderItemImplToJson(
      this,
    );
  }
}

abstract class _OrderItem implements OrderItem {
  const factory _OrderItem(
      {@JsonKey(name: 'product_id', fromJson: _idFromJson, toJson: _idToJson)
      required final String productId,
      required final int quantity,
      @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson)
      required final double unitPrice,
      @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson)
      required final double subtotal}) = _$OrderItemImpl;

  factory _OrderItem.fromJson(Map<String, dynamic> json) =
      _$OrderItemImpl.fromJson;

  @override
  @JsonKey(name: 'product_id', fromJson: _idFromJson, toJson: _idToJson)
  String get productId;
  @override
  int get quantity;
  @override
  @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson)
  double get unitPrice;
  @override
  @JsonKey(fromJson: _priceFromJson, toJson: _priceToJson)
  double get subtotal;

  /// Create a copy of OrderItem
  /// with the given fields replaced by the non-null parameter values.
  @override
  @JsonKey(includeFromJson: false, includeToJson: false)
  _$$OrderItemImplCopyWith<_$OrderItemImpl> get copyWith =>
      throw _privateConstructorUsedError;
}
