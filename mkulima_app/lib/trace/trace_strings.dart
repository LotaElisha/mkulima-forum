/// Mkulima Trace wording. Swahili first, as everywhere in the app; the
/// English is kept beside it and shown as a second line where a buyer or
/// exporter is likely to read the screen.
class TraceStrings {
  TraceStrings._();

  static const brand = 'Mkulima Trace';
  static const tagline = 'Shamba hadi Soko';
  static const taglineEn = 'From farm to market';
  static const serviceDescription =
      'Fuatilia mazao yako kutoka shambani hadi kwa mnunuzi kwa msimbo wa QR.';

  static const notice =
      'Hii ni historia iliyorekodiwa na washiriki. Haithibitishi cheti, '
      'uidhinishaji wala kufuata kanuni za serikali.';
  static const noticeEn =
      'A history recorded by participants. It does not prove certification '
      'or regulatory compliance.';
  static const paymentNote =
      'Imeandikwa na mshiriki. Mkulima Trace haikuhamisha wala kuthibitisha malipo haya.';

  // Sync states. "Pending" never claims the other party has confirmed.
  static const syncPending = 'Inasubiri kutumwa';
  static const syncFailed = 'Imeshindwa kutumwa';
  static const syncSynced = 'Imetumwa';
  static const savedOffline =
      'Imehifadhiwa kwenye simu. Itatumwa ukipata mtandao.';
  static const confirmSavedOffline =
      'Uthibitisho wako umehifadhiwa kwenye simu. Mzigo utahamia kwako '
      'baada ya kutumwa na kupokelewa na mfumo.';

  static const types = <String, (String, String)>{
    'farmer': ('Mkulima', 'Farmer'),
    'collector': ('Ushirika / Mkusanyaji', 'Cooperative / collector'),
    'warehouse': ('Ghala', 'Warehouse operator'),
    'processor': ('Msindikaji', 'Processor'),
    'transporter': ('Msafirishaji', 'Transporter'),
    'buyer': ('Mnunuzi / Msafirishaji nje', 'Approved buyer / exporter'),
  };

  static const typeHelp = <String, String>{
    'farmer': 'Unasajili mashamba na mavuno yako. Unaanza mara moja.',
    'collector':
        'Unapokea mazao kutoka kwa wakulima. Msimamizi atakuthibitisha kwanza.',
    'warehouse': 'Unahifadhi mazao ghalani. Msimamizi atakuthibitisha kwanza.',
    'processor':
        'Unasindika mazao kuwa bidhaa. Msimamizi atakuthibitisha kwanza.',
    'transporter': 'Unabeba mizigo. Msimamizi atakuthibitisha kwanza.',
    'buyer': 'Unanunua mazao. Msimamizi atakuthibitisha kwanza.',
  };

  static const events = <String, (String, String)>{
    'harvest': ('Mavuno', 'Harvest'),
    'handoff': ('Makabidhiano', 'Hand-off'),
    'storage': ('Kuhifadhi', 'Storage'),
    'grading': ('Kupanga daraja', 'Grading'),
    'quality_check': ('Ukaguzi wa ubora', 'Quality check'),
    'processing': ('Usindikaji', 'Processing'),
    'split': ('Kugawanya', 'Split'),
    'merge': ('Kuunganisha', 'Merge'),
    'dispatch': ('Kusafirisha', 'Dispatch'),
    'delivery': ('Kufikisha', 'Delivery'),
    'loss': ('Upotevu', 'Loss'),
    'note': ('Maelezo', 'Note'),
  };

  static const holderEvents = [
    'storage',
    'grading',
    'quality_check',
    'dispatch',
    'delivery',
    'loss',
    'note',
  ];

  static const lotStatus = <String, String>{
    'active': 'Inaendelea',
    'in_transfer': 'Inasubiri uthibitisho wa makabidhiano',
    'split': 'Imegawanywa',
    'merged': 'Imeunganishwa',
    'processed': 'Imesindikwa',
    'delivered': 'Imefikishwa',
    'closed': 'Imefungwa',
  };

  static const confirmation = <String, String>{
    'not_required': '',
    'pending': 'Inasubiri uthibitisho wa upande wa pili',
    'confirmed': 'Imethibitishwa na pande zote',
    'disputed': 'Imepingwa',
  };

  static const units = <String, String>{
    'kg': 'Kilo (kg)',
    'bag_50kg': 'Gunia la kilo 50',
    'bag_90kg': 'Gunia la kilo 90',
    'bag_100kg': 'Gunia la kilo 100',
    'tonne': 'Tani',
  };

  static const paymentMethods = <String, String>{
    'mobile_money': 'Pesa kwa simu',
    'bank': 'Benki',
    'cash': 'Taslimu',
    'other': 'Njia nyingine',
  };

  static String type(String? key) => types[key]?.$1 ?? (key ?? '');
  static String event(String? key) => events[key]?.$1 ?? (key ?? '');
  static String unit(String? key) => units[key] ?? (key ?? '');
}
