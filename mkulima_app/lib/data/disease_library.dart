/// Offline reference: common diseases and pests of Tanzanian crops.
///
/// This is a library to READ, not a classifier. It works with no connection
/// and helps a farmer compare what they see with known symptoms. It never
/// names a disease for a photo: there is no on-device model in this app yet,
/// and picking a disease without one would hand farmers confident, wrong
/// spray advice. Treatments name active ingredients; farmers should confirm
/// a TPHPA-registered product with an extension officer or agronomist.
class DiseaseEntry {
  final String crop;
  final String part;
  final String name;
  final String symptoms;
  final String treatment;
  final String prevention;
  final String advice;

  const DiseaseEntry({
    required this.crop,
    required this.part,
    required this.name,
    required this.symptoms,
    required this.treatment,
    required this.prevention,
    required this.advice,
  });
}

const diseaseLibrary = <DiseaseEntry>[
  DiseaseEntry(
    crop: 'Mahindi',
    part: 'Jani',
    name: 'Kutu ya Majani (Maize Rust)',
    symptoms: 'Madoa madogo ya duara yenye rangi ya kahawia au unga mwekundu wa kutu juu na chini ya majani ya mahindi.',
    treatment: 'Nyunyizia viuatilifu vya ukungu kama Mancozeb au Azoxystrobin mara dalili zinapoanza. Ondoa majani yaliyoathirika vibaya.',
    prevention: 'Panda mbegu za mahindi zilizoidhinishwa zinazostahimili kutu. Zingatia mzunguko wa mazao (crop rotation) na maharage.',
    advice: 'Wasiliana na Afisa Ugani wa kata yako kuchagua dawa iliyosajiliwa na TPHPA kwa bei nafuu.',
  ),
  DiseaseEntry(
    crop: 'Mahindi',
    part: 'Jani',
    name: 'Kinyaushi cha Majani (Northern Leaf Blight)',
    symptoms: 'Madoa marefu yenye umbo la sigara yenye rangi ya kijivu au hudhurungi yanayonyauka majani ya chini kwenda juu.',
    treatment: 'Tumia dawa za kuua ukungu zenye Mancozeb au Pyraclostrobin. Epuka kumwagilia maji juu ya majani wakati wa jioni.',
    prevention: 'Choma au fukia mabaki ya shamba baada ya mavuno. Panda kwa nafasi sahihi (75cm x 30cm) ili kuruhusu mzunguko wa hewa.',
    advice: 'Hakikisha unaweka mbolea ya kupandia (DAP/Minjingu) na kukuzia (UREA/CAN) ili kuongeza kinga ya mmea.',
  ),
  DiseaseEntry(
    crop: 'Mahindi',
    part: 'Shina na Jani',
    name: 'Funza wa Jeshi (Fall Armyworm)',
    symptoms: 'Mashimo kwenye majani ya mmea mchanga, unga wa kinyesi cha funza ndani ya kitovu cha mmea (whorl).',
    treatment: 'Weka viuatilifu kama Emamectin Benzoate au Spinetoram moja kwa moja ndani ya kitovu cha mmea. Unaweza kutumia mchanga safi au jivu kitovuni kama kinga ya asili.',
    prevention: 'Panda mapema mara tu mvua za msimu zinapoanza. Kagua shamba mara 2 kwa wiki wakati mahindi yana urefu wa magoti.',
    advice: 'Nyunyizia dawa asubuhi mapema au jioni wakati funza wanapojitokeza kula.',
  ),
  DiseaseEntry(
    crop: 'Nyanya',
    part: 'Jani na Shina',
    name: 'Ukungu wa Mapema (Early Blight)',
    symptoms: 'Madoa ya duara yenye mistari ya pete (kama shabaha) yenye rangi ya hudhurungi kwenye majani ya chini.',
    treatment: 'Punguza majani ya chini yanayogusa udongo. Nyunyizia Copper Oxychloride au Mancozeb kila baada ya siku 7–10 wakati wa unyevunyevu.',
    prevention: 'Weka matandazo ya nyasi (mulching) kuzuia udongo kutiririkia majani. Zingatia mzunguko wa mazao na mahindi au vitunguu.',
    advice: 'Epuka kuvuta sigara au kushika mimea yenye ugonjwa kisha kushika mimea yenye afya.',
  ),
  DiseaseEntry(
    crop: 'Nyanya',
    part: 'Jani na Tunda',
    name: 'Ukungu Mkubwa (Late Blight)',
    symptoms: 'Madoa makubwa ya kijivu yaliyolowa maji kwenye majani, ukungu mweupe chini ya majani wakati wa baridi/unyevu, na kuoza kwa matunda.',
    treatment: 'Nyunyizia dawa za kimfumo (Systemic Fungicide) kama Metalaxyl + Mancozeb (Ridomil Gold) mara moja.',
    prevention: 'Panda nyanya kwenye matuta yaliyoinuka ili kuruhusu maji kutuama. Tumia mbegu zenye ustahimilivu.',
    advice: 'Ugonjwa huu husambaa kwa haraka sana kwenye baridi na mvua; chukua hatua ndani ya saa 24.',
  ),
  DiseaseEntry(
    crop: 'Maharage',
    part: 'Jani',
    name: 'Kutu ya Maharage (Bean Rust)',
    symptoms: 'Vipele vidogo vyenye unga wa hudhurungi au rangi ya kutu chini ya majani ya maharage na kusababisha majani kukauka.',
    treatment: 'Nyunyizia kiberiti (Sulphur) au dawa ya ukungu ya Triazole / Mancozeb mapema.',
    prevention: 'Panda mbegu zilizothibitishwa na TOSCI. Usipande maharage sehemu moja kwa misimu miwili mfululizo.',
    advice: 'Choma mabaki yote ya msimu uliopita ili kuondoa vijidudu vinavyolala ardhini.',
  ),
  DiseaseEntry(
    crop: 'Muhogo',
    part: 'Jani',
    name: 'Batobato la Muhogo (Cassava Mosaic Disease)',
    symptoms: 'Majani kuwa na madoa ya kijani kibichi na manjano, kupotoka (distortion) na mmea kudumaa.',
    treatment: 'Hakuna tiba ya kemikali kwa virusi. Ng\'oa na choma mara moja miti yote inayoonyesha dalili ili kuzuia kuenea.',
    prevention: 'Panda vipandikizi visivyo na ugonjwa (vya TARI kama TARICASS au Mkuranga). Dhibiti inzi weupe (Whiteflies).',
    advice: 'Pata vipandikizi vilivyoidhinishwa kutoka vituo vya kilimo vya TARI au mashamba ya wakala waliosajiliwa.',
  ),
  DiseaseEntry(
    crop: 'Ndizi',
    part: 'Jani na Shina',
    name: 'Sigatoka Nyeusi (Black Sigatoka)',
    symptoms: 'Mistari myembamba yenye rangi ya hudhurungi au nyeusi inayokauka na kufanya jani lote kuungua.',
    treatment: 'Kata na fukia chini majani yaliyoathirika (de-leafing). Nyunyizia dawa ya ukungu iliyochanganywa na mafuta ya madini.',
    prevention: 'Punguza vichipukizi (desuckering) ili mgomba ubakiwe na miti 3 pekee kwa shimo kuruhusu mwanga na hewa.',
    advice: 'Weka mbolea ya samadi ya kutosha kuupa mgomba nguvu ya kupambana na magonjwa.',
  ),
];
