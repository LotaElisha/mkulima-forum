/// Centralized bilingual strings (Swahili default, English fallback).
/// Screens should use MkStrings instead of hardcoding text so a future
/// flutter_localizations/ARB migration is mechanical.
class MkStrings {
  MkStrings._();

  static const appName = 'MkulimaForum';
  static const tagline = 'Soko la Kilimo kwa Wote';

  // Navigation
  static const navMarket = 'Soko';
  static const navForum = 'Jukwaa';
  static const navServices = 'Huduma';
  static const navProfile = 'Wasifu';
  static const navLogin = 'Ingia';

  // Common actions
  static const retry = 'Jaribu tena';
  static const cancel = 'Ghairi';
  static const save = 'Hifadhi';
  static const search = 'Tafuta';
  static const loading = 'Inapakia...';

  // States
  static const offline = 'Hakuna mtandao — unaona taarifa zilizohifadhiwa';
  static const emptyList = 'Hakuna taarifa kwa sasa';
  static const errorGeneric = 'Hitilafu imetokea. Tafadhali jaribu tena.';
  static const weatherUnavailable =
      'Taarifa za hali ya hewa hazipatikani kwa sasa';
  static const weatherStale = 'Taarifa za zamani — si za sasa hivi';

  // Marketplace
  static const addToCart = 'Weka kikapuni';
  static const checkout = 'Kamilisha ununuzi';
  static const orders = 'Oda zangu';

  // Forum
  static const newThread = 'Anzisha mada';
  static const reply = 'Jibu';
  static const upvote = 'Kubali';
  static const expertBadge = 'Mtaalamu';

  // Scanner
  static const scanPlant = 'Piga picha ya mmea';
  static const scanFailed =
      'Uchambuzi haukufanikiwa kwa sasa. Tafadhali jaribu tena baadaye.';
}
