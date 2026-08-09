// coverage:ignore-file
// GENERATED CODE - DO NOT MODIFY BY HAND
// ignore_for_file: type=lint
// ignore_for_file: unused_element, deprecated_member_use, deprecated_member_use_from_same_package, use_function_type_syntax_for_parameters, unnecessary_const, avoid_init_to_null, invalid_override_different_default_values_named, prefer_expression_function_bodies, annotate_overrides, invalid_annotation_target, unnecessary_question_mark

part of 'farm.dart';

// **************************************************************************
// FreezedGenerator
// **************************************************************************

T _$identity<T>(T value) => value;

final _privateConstructorUsedError = UnsupportedError(
    'It seems like you constructed your class using `MyClass._()`. This constructor is only meant to be used by freezed and you are not supposed to need it nor use it.\nPlease check the documentation here for more information: https://github.com/rrousselGit/freezed#adding-getters-and-methods-to-our-models');

Farm _$FarmFromJson(Map<String, dynamic> json) {
  return _Farm.fromJson(json);
}

/// @nodoc
mixin _$Farm {
  String get uuid => throw _privateConstructorUsedError;
  String get name => throw _privateConstructorUsedError;
  String get location => throw _privateConstructorUsedError;
  @JsonKey(name: 'size_acres')
  double get sizeAcres => throw _privateConstructorUsedError;
  @JsonKey(name: 'crop_type')
  String get cropType => throw _privateConstructorUsedError;
  @JsonKey(name: 'soil_type')
  String? get soilType => throw _privateConstructorUsedError;
  @JsonKey(name: 'planting_date')
  String? get plantingDate => throw _privateConstructorUsedError;
  @JsonKey(name: 'harvest_expected_date')
  String? get harvestExpectedDate => throw _privateConstructorUsedError;
  String get status => throw _privateConstructorUsedError;
  String? get notes => throw _privateConstructorUsedError;
  List<FarmActivity> get activities => throw _privateConstructorUsedError;

  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;
  @JsonKey(ignore: true)
  $FarmCopyWith<Farm> get copyWith => throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $FarmCopyWith<$Res> {
  factory $FarmCopyWith(Farm value, $Res Function(Farm) then) =
      _$FarmCopyWithImpl<$Res, Farm>;
  @useResult
  $Res call(
      {String uuid,
      String name,
      String location,
      @JsonKey(name: 'size_acres') double sizeAcres,
      @JsonKey(name: 'crop_type') String cropType,
      @JsonKey(name: 'soil_type') String? soilType,
      @JsonKey(name: 'planting_date') String? plantingDate,
      @JsonKey(name: 'harvest_expected_date') String? harvestExpectedDate,
      String status,
      String? notes,
      List<FarmActivity> activities});
}

/// @nodoc
class _$FarmCopyWithImpl<$Res, $Val extends Farm>
    implements $FarmCopyWith<$Res> {
  _$FarmCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? uuid = null,
    Object? name = null,
    Object? location = null,
    Object? sizeAcres = null,
    Object? cropType = null,
    Object? soilType = freezed,
    Object? plantingDate = freezed,
    Object? harvestExpectedDate = freezed,
    Object? status = null,
    Object? notes = freezed,
    Object? activities = null,
  }) {
    return _then(_value.copyWith(
      uuid: null == uuid
          ? _value.uuid
          : uuid // ignore: cast_nullable_to_non_nullable
              as String,
      name: null == name
          ? _value.name
          : name // ignore: cast_nullable_to_non_nullable
              as String,
      location: null == location
          ? _value.location
          : location // ignore: cast_nullable_to_non_nullable
              as String,
      sizeAcres: null == sizeAcres
          ? _value.sizeAcres
          : sizeAcres // ignore: cast_nullable_to_non_nullable
              as double,
      cropType: null == cropType
          ? _value.cropType
          : cropType // ignore: cast_nullable_to_non_nullable
              as String,
      soilType: freezed == soilType
          ? _value.soilType
          : soilType // ignore: cast_nullable_to_non_nullable
              as String?,
      plantingDate: freezed == plantingDate
          ? _value.plantingDate
          : plantingDate // ignore: cast_nullable_to_non_nullable
              as String?,
      harvestExpectedDate: freezed == harvestExpectedDate
          ? _value.harvestExpectedDate
          : harvestExpectedDate // ignore: cast_nullable_to_non_nullable
              as String?,
      status: null == status
          ? _value.status
          : status // ignore: cast_nullable_to_non_nullable
              as String,
      notes: freezed == notes
          ? _value.notes
          : notes // ignore: cast_nullable_to_non_nullable
              as String?,
      activities: null == activities
          ? _value.activities
          : activities // ignore: cast_nullable_to_non_nullable
              as List<FarmActivity>,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$FarmImplCopyWith<$Res> implements $FarmCopyWith<$Res> {
  factory _$$FarmImplCopyWith(
          _$FarmImpl value, $Res Function(_$FarmImpl) then) =
      __$$FarmImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {String uuid,
      String name,
      String location,
      @JsonKey(name: 'size_acres') double sizeAcres,
      @JsonKey(name: 'crop_type') String cropType,
      @JsonKey(name: 'soil_type') String? soilType,
      @JsonKey(name: 'planting_date') String? plantingDate,
      @JsonKey(name: 'harvest_expected_date') String? harvestExpectedDate,
      String status,
      String? notes,
      List<FarmActivity> activities});
}

/// @nodoc
class __$$FarmImplCopyWithImpl<$Res>
    extends _$FarmCopyWithImpl<$Res, _$FarmImpl>
    implements _$$FarmImplCopyWith<$Res> {
  __$$FarmImplCopyWithImpl(_$FarmImpl _value, $Res Function(_$FarmImpl) _then)
      : super(_value, _then);

  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? uuid = null,
    Object? name = null,
    Object? location = null,
    Object? sizeAcres = null,
    Object? cropType = null,
    Object? soilType = freezed,
    Object? plantingDate = freezed,
    Object? harvestExpectedDate = freezed,
    Object? status = null,
    Object? notes = freezed,
    Object? activities = null,
  }) {
    return _then(_$FarmImpl(
      uuid: null == uuid
          ? _value.uuid
          : uuid // ignore: cast_nullable_to_non_nullable
              as String,
      name: null == name
          ? _value.name
          : name // ignore: cast_nullable_to_non_nullable
              as String,
      location: null == location
          ? _value.location
          : location // ignore: cast_nullable_to_non_nullable
              as String,
      sizeAcres: null == sizeAcres
          ? _value.sizeAcres
          : sizeAcres // ignore: cast_nullable_to_non_nullable
              as double,
      cropType: null == cropType
          ? _value.cropType
          : cropType // ignore: cast_nullable_to_non_nullable
              as String,
      soilType: freezed == soilType
          ? _value.soilType
          : soilType // ignore: cast_nullable_to_non_nullable
              as String?,
      plantingDate: freezed == plantingDate
          ? _value.plantingDate
          : plantingDate // ignore: cast_nullable_to_non_nullable
              as String?,
      harvestExpectedDate: freezed == harvestExpectedDate
          ? _value.harvestExpectedDate
          : harvestExpectedDate // ignore: cast_nullable_to_non_nullable
              as String?,
      status: null == status
          ? _value.status
          : status // ignore: cast_nullable_to_non_nullable
              as String,
      notes: freezed == notes
          ? _value.notes
          : notes // ignore: cast_nullable_to_non_nullable
              as String?,
      activities: null == activities
          ? _value._activities
          : activities // ignore: cast_nullable_to_non_nullable
              as List<FarmActivity>,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$FarmImpl implements _Farm {
  const _$FarmImpl(
      {required this.uuid,
      required this.name,
      required this.location,
      @JsonKey(name: 'size_acres') required this.sizeAcres,
      @JsonKey(name: 'crop_type') required this.cropType,
      @JsonKey(name: 'soil_type') this.soilType,
      @JsonKey(name: 'planting_date') this.plantingDate,
      @JsonKey(name: 'harvest_expected_date') this.harvestExpectedDate,
      this.status = 'active',
      this.notes,
      final List<FarmActivity> activities = const []})
      : _activities = activities;

  factory _$FarmImpl.fromJson(Map<String, dynamic> json) =>
      _$$FarmImplFromJson(json);

  @override
  final String uuid;
  @override
  final String name;
  @override
  final String location;
  @override
  @JsonKey(name: 'size_acres')
  final double sizeAcres;
  @override
  @JsonKey(name: 'crop_type')
  final String cropType;
  @override
  @JsonKey(name: 'soil_type')
  final String? soilType;
  @override
  @JsonKey(name: 'planting_date')
  final String? plantingDate;
  @override
  @JsonKey(name: 'harvest_expected_date')
  final String? harvestExpectedDate;
  @override
  @JsonKey()
  final String status;
  @override
  final String? notes;
  final List<FarmActivity> _activities;
  @override
  @JsonKey()
  List<FarmActivity> get activities {
    if (_activities is EqualUnmodifiableListView) return _activities;
    // ignore: implicit_dynamic_type
    return EqualUnmodifiableListView(_activities);
  }

  @override
  String toString() {
    return 'Farm(uuid: $uuid, name: $name, location: $location, sizeAcres: $sizeAcres, cropType: $cropType, soilType: $soilType, plantingDate: $plantingDate, harvestExpectedDate: $harvestExpectedDate, status: $status, notes: $notes, activities: $activities)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$FarmImpl &&
            (identical(other.uuid, uuid) || other.uuid == uuid) &&
            (identical(other.name, name) || other.name == name) &&
            (identical(other.location, location) ||
                other.location == location) &&
            (identical(other.sizeAcres, sizeAcres) ||
                other.sizeAcres == sizeAcres) &&
            (identical(other.cropType, cropType) ||
                other.cropType == cropType) &&
            (identical(other.soilType, soilType) ||
                other.soilType == soilType) &&
            (identical(other.plantingDate, plantingDate) ||
                other.plantingDate == plantingDate) &&
            (identical(other.harvestExpectedDate, harvestExpectedDate) ||
                other.harvestExpectedDate == harvestExpectedDate) &&
            (identical(other.status, status) || other.status == status) &&
            (identical(other.notes, notes) || other.notes == notes) &&
            const DeepCollectionEquality()
                .equals(other._activities, _activities));
  }

  @JsonKey(ignore: true)
  @override
  int get hashCode => Object.hash(
      runtimeType,
      uuid,
      name,
      location,
      sizeAcres,
      cropType,
      soilType,
      plantingDate,
      harvestExpectedDate,
      status,
      notes,
      const DeepCollectionEquality().hash(_activities));

  @JsonKey(ignore: true)
  @override
  @pragma('vm:prefer-inline')
  _$$FarmImplCopyWith<_$FarmImpl> get copyWith =>
      __$$FarmImplCopyWithImpl<_$FarmImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$FarmImplToJson(
      this,
    );
  }
}

abstract class _Farm implements Farm {
  const factory _Farm(
      {required final String uuid,
      required final String name,
      required final String location,
      @JsonKey(name: 'size_acres') required final double sizeAcres,
      @JsonKey(name: 'crop_type') required final String cropType,
      @JsonKey(name: 'soil_type') final String? soilType,
      @JsonKey(name: 'planting_date') final String? plantingDate,
      @JsonKey(name: 'harvest_expected_date') final String? harvestExpectedDate,
      final String status,
      final String? notes,
      final List<FarmActivity> activities}) = _$FarmImpl;

  factory _Farm.fromJson(Map<String, dynamic> json) = _$FarmImpl.fromJson;

  @override
  String get uuid;
  @override
  String get name;
  @override
  String get location;
  @override
  @JsonKey(name: 'size_acres')
  double get sizeAcres;
  @override
  @JsonKey(name: 'crop_type')
  String get cropType;
  @override
  @JsonKey(name: 'soil_type')
  String? get soilType;
  @override
  @JsonKey(name: 'planting_date')
  String? get plantingDate;
  @override
  @JsonKey(name: 'harvest_expected_date')
  String? get harvestExpectedDate;
  @override
  String get status;
  @override
  String? get notes;
  @override
  List<FarmActivity> get activities;
  @override
  @JsonKey(ignore: true)
  _$$FarmImplCopyWith<_$FarmImpl> get copyWith =>
      throw _privateConstructorUsedError;
}

FarmActivity _$FarmActivityFromJson(Map<String, dynamic> json) {
  return _FarmActivity.fromJson(json);
}

/// @nodoc
mixin _$FarmActivity {
  String get uuid => throw _privateConstructorUsedError;
  @JsonKey(name: 'activity_type')
  String get activityType => throw _privateConstructorUsedError;
  @JsonKey(name: 'activity_date')
  String get activityDate => throw _privateConstructorUsedError;
  @JsonKey(name: 'cost_tzs')
  double get costTzs => throw _privateConstructorUsedError;
  String? get notes => throw _privateConstructorUsedError;

  Map<String, dynamic> toJson() => throw _privateConstructorUsedError;
  @JsonKey(ignore: true)
  $FarmActivityCopyWith<FarmActivity> get copyWith =>
      throw _privateConstructorUsedError;
}

/// @nodoc
abstract class $FarmActivityCopyWith<$Res> {
  factory $FarmActivityCopyWith(
          FarmActivity value, $Res Function(FarmActivity) then) =
      _$FarmActivityCopyWithImpl<$Res, FarmActivity>;
  @useResult
  $Res call(
      {String uuid,
      @JsonKey(name: 'activity_type') String activityType,
      @JsonKey(name: 'activity_date') String activityDate,
      @JsonKey(name: 'cost_tzs') double costTzs,
      String? notes});
}

/// @nodoc
class _$FarmActivityCopyWithImpl<$Res, $Val extends FarmActivity>
    implements $FarmActivityCopyWith<$Res> {
  _$FarmActivityCopyWithImpl(this._value, this._then);

  // ignore: unused_field
  final $Val _value;
  // ignore: unused_field
  final $Res Function($Val) _then;

  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? uuid = null,
    Object? activityType = null,
    Object? activityDate = null,
    Object? costTzs = null,
    Object? notes = freezed,
  }) {
    return _then(_value.copyWith(
      uuid: null == uuid
          ? _value.uuid
          : uuid // ignore: cast_nullable_to_non_nullable
              as String,
      activityType: null == activityType
          ? _value.activityType
          : activityType // ignore: cast_nullable_to_non_nullable
              as String,
      activityDate: null == activityDate
          ? _value.activityDate
          : activityDate // ignore: cast_nullable_to_non_nullable
              as String,
      costTzs: null == costTzs
          ? _value.costTzs
          : costTzs // ignore: cast_nullable_to_non_nullable
              as double,
      notes: freezed == notes
          ? _value.notes
          : notes // ignore: cast_nullable_to_non_nullable
              as String?,
    ) as $Val);
  }
}

/// @nodoc
abstract class _$$FarmActivityImplCopyWith<$Res>
    implements $FarmActivityCopyWith<$Res> {
  factory _$$FarmActivityImplCopyWith(
          _$FarmActivityImpl value, $Res Function(_$FarmActivityImpl) then) =
      __$$FarmActivityImplCopyWithImpl<$Res>;
  @override
  @useResult
  $Res call(
      {String uuid,
      @JsonKey(name: 'activity_type') String activityType,
      @JsonKey(name: 'activity_date') String activityDate,
      @JsonKey(name: 'cost_tzs') double costTzs,
      String? notes});
}

/// @nodoc
class __$$FarmActivityImplCopyWithImpl<$Res>
    extends _$FarmActivityCopyWithImpl<$Res, _$FarmActivityImpl>
    implements _$$FarmActivityImplCopyWith<$Res> {
  __$$FarmActivityImplCopyWithImpl(
      _$FarmActivityImpl _value, $Res Function(_$FarmActivityImpl) _then)
      : super(_value, _then);

  @pragma('vm:prefer-inline')
  @override
  $Res call({
    Object? uuid = null,
    Object? activityType = null,
    Object? activityDate = null,
    Object? costTzs = null,
    Object? notes = freezed,
  }) {
    return _then(_$FarmActivityImpl(
      uuid: null == uuid
          ? _value.uuid
          : uuid // ignore: cast_nullable_to_non_nullable
              as String,
      activityType: null == activityType
          ? _value.activityType
          : activityType // ignore: cast_nullable_to_non_nullable
              as String,
      activityDate: null == activityDate
          ? _value.activityDate
          : activityDate // ignore: cast_nullable_to_non_nullable
              as String,
      costTzs: null == costTzs
          ? _value.costTzs
          : costTzs // ignore: cast_nullable_to_non_nullable
              as double,
      notes: freezed == notes
          ? _value.notes
          : notes // ignore: cast_nullable_to_non_nullable
              as String?,
    ));
  }
}

/// @nodoc
@JsonSerializable()
class _$FarmActivityImpl implements _FarmActivity {
  const _$FarmActivityImpl(
      {required this.uuid,
      @JsonKey(name: 'activity_type') required this.activityType,
      @JsonKey(name: 'activity_date') required this.activityDate,
      @JsonKey(name: 'cost_tzs') this.costTzs = 0.0,
      this.notes});

  factory _$FarmActivityImpl.fromJson(Map<String, dynamic> json) =>
      _$$FarmActivityImplFromJson(json);

  @override
  final String uuid;
  @override
  @JsonKey(name: 'activity_type')
  final String activityType;
  @override
  @JsonKey(name: 'activity_date')
  final String activityDate;
  @override
  @JsonKey(name: 'cost_tzs')
  final double costTzs;
  @override
  final String? notes;

  @override
  String toString() {
    return 'FarmActivity(uuid: $uuid, activityType: $activityType, activityDate: $activityDate, costTzs: $costTzs, notes: $notes)';
  }

  @override
  bool operator ==(Object other) {
    return identical(this, other) ||
        (other.runtimeType == runtimeType &&
            other is _$FarmActivityImpl &&
            (identical(other.uuid, uuid) || other.uuid == uuid) &&
            (identical(other.activityType, activityType) ||
                other.activityType == activityType) &&
            (identical(other.activityDate, activityDate) ||
                other.activityDate == activityDate) &&
            (identical(other.costTzs, costTzs) || other.costTzs == costTzs) &&
            (identical(other.notes, notes) || other.notes == notes));
  }

  @JsonKey(ignore: true)
  @override
  int get hashCode => Object.hash(
      runtimeType, uuid, activityType, activityDate, costTzs, notes);

  @JsonKey(ignore: true)
  @override
  @pragma('vm:prefer-inline')
  _$$FarmActivityImplCopyWith<_$FarmActivityImpl> get copyWith =>
      __$$FarmActivityImplCopyWithImpl<_$FarmActivityImpl>(this, _$identity);

  @override
  Map<String, dynamic> toJson() {
    return _$$FarmActivityImplToJson(
      this,
    );
  }
}

abstract class _FarmActivity implements FarmActivity {
  const factory _FarmActivity(
      {required final String uuid,
      @JsonKey(name: 'activity_type') required final String activityType,
      @JsonKey(name: 'activity_date') required final String activityDate,
      @JsonKey(name: 'cost_tzs') final double costTzs,
      final String? notes}) = _$FarmActivityImpl;

  factory _FarmActivity.fromJson(Map<String, dynamic> json) =
      _$FarmActivityImpl.fromJson;

  @override
  String get uuid;
  @override
  @JsonKey(name: 'activity_type')
  String get activityType;
  @override
  @JsonKey(name: 'activity_date')
  String get activityDate;
  @override
  @JsonKey(name: 'cost_tzs')
  double get costTzs;
  @override
  String? get notes;
  @override
  @JsonKey(ignore: true)
  _$$FarmActivityImplCopyWith<_$FarmActivityImpl> get copyWith =>
      throw _privateConstructorUsedError;
}
