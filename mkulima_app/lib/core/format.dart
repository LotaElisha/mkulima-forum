import 'package:intl/intl.dart';

final NumberFormat _thousands = NumberFormat.decimalPattern('en');

/// Shillings as farmers read them: "TSh 18,000". One spelling everywhere —
/// screens used to mix "TSh" and "TZS" and print 18000 without separators.
String mkMoney(num? amount) => 'TSh ${_thousands.format((amount ?? 0).round())}';

/// A bare amount with separators, for places that add their own unit.
String mkAmount(num? amount) => _thousands.format((amount ?? 0).round());
