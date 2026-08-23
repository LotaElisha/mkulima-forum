import 'package:flutter_test/flutter_test.dart';
import 'package:mkulima_app/models/seller_state.dart';

/// The profile screen used to decide whether to show the Seller Dashboard from
/// `role == 'farmer' || role == 'agrodealer'`, which is true for every farmer,
/// and the API answered 403. These pin the replacement: the server states the
/// answer and the client reads it.
void main() {
  group('SellerState', () {
    test('reads the block nested under user, as login and /me send it', () {
      final state = SellerState.fromAnywhere({
        'user': {
          'role': 'farmer',
          'seller': {'state': 'none', 'can_sell': false, 'can_apply': true},
        },
      });

      expect(state.canSell, isFalse);
      expect(state.hasNeverApplied, isTrue);
      expect(state.canApply, isTrue);
    });

    test('reads the top-level block, as the seller endpoints send it', () {
      final state = SellerState.fromAnywhere({
        'seller': {'state': 'approved', 'can_sell': true, 'can_apply': false},
      });

      expect(state.isApproved, isTrue);
    });

    test('carries the rejection reason so the app can explain itself', () {
      final state = SellerState.fromAnywhere({
        'seller': {
          'state': 'rejected',
          'can_sell': false,
          'can_apply': true,
          'application': {
            'business_name': 'Njombe Agrovet',
            'rejection_reason': 'Leseni haikuambatanishwa.',
            'submitted_at': '2026-08-23T07:48:56+00:00',
          },
        },
      });

      expect(state.isRejected, isTrue);
      expect(state.rejectionReason, 'Leseni haikuambatanishwa.');
      expect(state.businessName, 'Njombe Agrovet');
      expect(state.submittedAt, isNotNull);
    });

    test('an absent or malformed block never grants selling rights', () {
      // The failure mode that matters: if parsing goes wrong we must fall back
      // to "cannot sell", never to "can".
      for (final body in [null, 'nonsense', 42, <String, dynamic>{}, {'seller': null}]) {
        expect(SellerState.fromAnywhere(body).canSell, isFalse, reason: '$body');
      }
      expect(SellerState.unknown.canSell, isFalse);
    });
  });
}
