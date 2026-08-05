// GENERATED CODE - DO NOT MODIFY BY HAND

part of 'user.dart';

// **************************************************************************
// JsonSerializableGenerator
// **************************************************************************

_$UserImpl _$$UserImplFromJson(Map<String, dynamic> json) => _$UserImpl(
      uuid: json['uuid'] as String,
      name: json['name'] as String,
      phone: json['phone'] as String,
      email: json['email'] as String?,
      role: json['role'] as String,
      kycStatus: json['kycStatus'] as String? ?? 'pending',
      preferredLanguage: json['preferredLanguage'] as String? ?? 'sw',
      avatar: json['avatar'] as String?,
      countryCode: json['countryCode'] as String?,
      createdAt: json['createdAt'] == null
          ? null
          : DateTime.parse(json['createdAt'] as String),
    );

Map<String, dynamic> _$$UserImplToJson(_$UserImpl instance) =>
    <String, dynamic>{
      'uuid': instance.uuid,
      'name': instance.name,
      'phone': instance.phone,
      'email': instance.email,
      'role': instance.role,
      'kycStatus': instance.kycStatus,
      'preferredLanguage': instance.preferredLanguage,
      'avatar': instance.avatar,
      'countryCode': instance.countryCode,
      'createdAt': instance.createdAt?.toIso8601String(),
    };
