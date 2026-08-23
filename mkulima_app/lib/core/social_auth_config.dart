import 'package:flutter/foundation.dart';

/// Which social sign-in providers this build is actually configured for.
///
/// Both providers were shipping as visible buttons that threw the moment they
/// were tapped:
///
///   GoogleSignInException(clientConfigurationError,
///     serverClientId must be provided on Android)
///   Exception: APPLE_SERVICE_ID is not configured.
///
/// Neither is a code defect - the credentials were simply never passed to the
/// build. But a button that cannot work should not be on the screen, so the
/// app now asks this class before drawing one. The values come from
/// --dart-define at compile time, which means they are baked into the binary
/// and there is no runtime call to make this decision wrong.
class SocialAuthConfig {
  /// OAuth **web** client id from Google Cloud, not the Android client id.
  ///
  /// Android sign-in needs the web client id in `serverClientId` - the Android
  /// client is matched by package name and SHA-1 fingerprint instead and is
  /// never named in code. Passing the Android id here is the single most
  /// common way this breaks.
  static const String googleServerClientId =
      String.fromEnvironment('GOOGLE_SERVER_CLIENT_ID');

  /// Apple **Services ID** (e.g. app.mkulimaforum.signin), not the bundle id.
  /// Only needed for the Android/web flow; native iOS uses the bundle id.
  static const String appleServiceId =
      String.fromEnvironment('APPLE_SERVICE_ID');

  static bool get isGoogleConfigured => googleServerClientId.isNotEmpty;

  /// Apple sign-in on a real Apple device needs no extra configuration; on
  /// Android and web it runs through Apple's web flow, which does.
  static bool get isAppleConfigured {
    if (kIsWeb) return appleServiceId.isNotEmpty;
    if (defaultTargetPlatform == TargetPlatform.iOS ||
        defaultTargetPlatform == TargetPlatform.macOS) {
      return true;
    }
    return appleServiceId.isNotEmpty;
  }

  /// Apple's guidelines only require the button where Apple ships it.
  static bool get shouldOfferApple =>
      isAppleConfigured &&
      (kIsWeb ||
          defaultTargetPlatform == TargetPlatform.iOS ||
          defaultTargetPlatform == TargetPlatform.macOS ||
          appleServiceId.isNotEmpty);

  static bool get hasAnyProvider => isGoogleConfigured || shouldOfferApple;

  /// Printed once at startup in debug builds so a misconfigured build is
  /// obvious before anyone taps anything.
  static void debugReport() {
    if (!kDebugMode) return;
    debugPrint(
      'SocialAuthConfig: google=${isGoogleConfigured ? "configured" : "MISSING GOOGLE_SERVER_CLIENT_ID"}, '
      'apple=${isAppleConfigured ? "configured" : "MISSING APPLE_SERVICE_ID"}',
    );
  }
}
