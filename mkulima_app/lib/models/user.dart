import 'package:freezed_annotation/freezed_annotation.dart';

part 'user.freezed.dart';
part 'user.g.dart';

@freezed
class User with _$User {
  const factory User({
    required String uuid,
    required String name,
    required String phone,
    String? email,
    required String role,
    @Default('pending') String kycStatus,
    @Default('sw') String preferredLanguage,
    String? avatar,
    String? countryCode,
    DateTime? createdAt,
  }) = _User;

  factory User.fromJson(Map<String, dynamic> json) => _$UserFromJson(json);
}
