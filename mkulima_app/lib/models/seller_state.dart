import 'package:flutter/foundation.dart';

/// Where an account stands on selling.
///
/// Kept separate from [User] on purpose. `User` is a freezed class, and adding
/// a field there means regenerating `user.freezed.dart` and `user.g.dart`;
/// this value arrives on the same payload and is read by exactly one part of
/// the UI, so a plain class keeps the change contained.
///
/// It exists because the profile screen used to decide whether to show the
/// Seller Dashboard from `user.role == 'farmer' || user.role == 'agrodealer'`
/// - which showed it to every farmer, and the API answered 403. The client no
/// longer guesses: the server sends the state and the client draws it.
@immutable
class SellerState {
  /// One of: none, pending, rejected, approved.
  final String state;

  /// True when seller endpoints will accept this account.
  final bool canSell;

  /// True when an application may be submitted now.
  final bool canApply;

  final String? businessName;
  final String? rejectionReason;
  final DateTime? submittedAt;

  const SellerState({
    required this.state,
    required this.canSell,
    required this.canApply,
    this.businessName,
    this.rejectionReason,
    this.submittedAt,
  });

  /// The safe assumption for an account we have not asked about yet: show no
  /// business tools, and offer the application.
  static const SellerState unknown =
      SellerState(state: 'none', canSell: false, canApply: true);

  bool get isApproved => canSell;
  bool get isPending => state == 'pending';
  bool get isRejected => state == 'rejected';
  bool get hasNeverApplied => state == 'none';

  factory SellerState.fromJson(Map<String, dynamic>? json) {
    if (json == null) return unknown;

    final application = json['application'];
    final app = application is Map ? Map<String, dynamic>.from(application) : null;

    return SellerState(
      state: json['state'] is String ? json['state'] as String : 'none',
      canSell: json['can_sell'] == true,
      canApply: json['can_apply'] != false,
      businessName: app?['business_name'] as String?,
      rejectionReason: app?['rejection_reason'] as String?,
      submittedAt: app?['submitted_at'] is String
          ? DateTime.tryParse(app!['submitted_at'] as String)?.toLocal()
          : null,
    );
  }

  /// Pulls the seller block out of any envelope the API might use: the login
  /// and /me responses nest it under `user`, the seller endpoints return it
  /// at the top level under `seller`.
  factory SellerState.fromAnywhere(dynamic body) {
    if (body is! Map) return unknown;

    if (body['seller'] is Map) {
      return SellerState.fromJson(Map<String, dynamic>.from(body['seller']));
    }
    if (body['user'] is Map) {
      final user = Map<String, dynamic>.from(body['user']);
      if (user['seller'] is Map) {
        return SellerState.fromJson(Map<String, dynamic>.from(user['seller']));
      }
    }
    return unknown;
  }
}
