// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'farm.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_$FarmImpl _$$FarmImplFromJson(Map<String, dynamic> json) => _$FarmImpl(
      uuid: json['uuid'] as String,
      name: json['name'] as String,
      location: json['location'] as String,
      sizeAcres: (json['size_acres'] as num).toDouble(),
      cropType: json['crop_type'] as String,
      soilType: json['soil_type'] as String?,
      plantingDate: json['planting_date'] as String?,
      harvestExpectedDate: json['harvest_expected_date'] as String?,
      status: json['status'] as String? ?? 'active',
      notes: json['notes'] as String?,
      activities: (json['activities'] as List<dynamic>?)
              ?.map((e) => FarmActivity.fromJson(e as Map<String, dynamic>))
              .toList() ??
          const [],
    );

Map<String, dynamic> _$$FarmImplToJson(_$FarmImpl instance) =>
    <String, dynamic>{
      'uuid': instance.uuid,
      'name': instance.name,
      'location': instance.location,
      'size_acres': instance.sizeAcres,
      'crop_type': instance.cropType,
      'soil_type': instance.soilType,
      'planting_date': instance.plantingDate,
      'harvest_expected_date': instance.harvestExpectedDate,
      'status': instance.status,
      'notes': instance.notes,
      'activities': instance.activities,
    };

_$FarmActivityImpl _$$FarmActivityImplFromJson(Map<String, dynamic> json) =>
    _$FarmActivityImpl(
      uuid: json['uuid'] as String,
      activityType: json['activity_type'] as String,
      activityDate: json['activity_date'] as String,
      costTzs: (json['cost_tzs'] as num?)?.toDouble() ?? 0.0,
      notes: json['notes'] as String?,
    );

Map<String, dynamic> _$$FarmActivityImplToJson(_$FarmActivityImpl instance) =>
    <String, dynamic>{
      'uuid': instance.uuid,
      'activity_type': instance.activityType,
      'activity_date': instance.activityDate,
      'cost_tzs': instance.costTzs,
      'notes': instance.notes,
    };
