import 'package:freezed_annotation/freezed_annotation.dart';

part 'farm.freezed.dart';
part 'farm.g.dart';

@freezed
class Farm with _$Farm {
  const factory Farm({
    required String uuid,
    required String name,
    required String location,
    @JsonKey(name: 'size_acres') required double sizeAcres,
    @JsonKey(name: 'crop_type') required String cropType,
    @JsonKey(name: 'soil_type') String? soilType,
    @JsonKey(name: 'planting_date') String? plantingDate,
    @JsonKey(name: 'harvest_expected_date') String? harvestExpectedDate,
    @Default('active') String status,
    String? notes,
    @Default([]) List<FarmActivity> activities,
  }) = _Farm;

  factory Farm.fromJson(Map<String, dynamic> json) => _$FarmFromJson(json);
}

@freezed
class FarmActivity with _$FarmActivity {
  const factory FarmActivity({
    required String uuid,
    @JsonKey(name: 'activity_type') required String activityType,
    @JsonKey(name: 'activity_date') required String activityDate,
    @JsonKey(name: 'cost_tzs') @Default(0.0) double costTzs,
    String? notes,
  }) = _FarmActivity;

  factory FarmActivity.fromJson(Map<String, dynamic> json) => _$FarmActivityFromJson(json);
}
