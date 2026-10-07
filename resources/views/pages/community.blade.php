@extends('layouts.public')

@section('title', 'Mkulima Community Hub | WhatsApp Groups, Channels & Farmer Communities Tanzania')
@section('meta_description', 'Connect with fellow farmers across Tanzania. Join official WhatsApp channels, crop-specific WhatsApp groups, Telegram communities, and social channels.')
@section('og_title', 'Mkulima Community Hub | AgriTech Tanzania')
@section('og_description', 'Connect with smallholder farmers, agronomists, and agrodealers across Tanzania via WhatsApp, Telegram, and Mkulima Forum.')

@section('head_extra')
<style>
  /* Tokens, buttons, header and footer come from layouts/public.blade.php. */
  .comm-hero { padding:72px 0 56px; text-align:center; background:#fff; }
  .comm-title { font-size:clamp(30px,4.6vw,48px); font-weight:800; line-height:1.12; letter-spacing:-.025em; color:var(--ink-dark); margin:0 auto 16px; }
  .comm-sub { font-size:17px; line-height:1.65; max-width:40rem; margin:0 auto; }

  .channel-card-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
  @media(max-width:960px){ .channel-card-grid{ grid-template-columns:repeat(2,1fr); } }
  @media(max-width:560px){ .channel-card-grid{ grid-template-columns:1fr; gap:12px; } }
  .comm-status { grid-column:1/-1; text-align:center; padding:40px 16px; font-size:16px; color:var(--ink-muted); background:var(--surface-soft); border:1px solid var(--border-light); border-radius:var(--radius-xl); }

  .comm-card {
    background:#fff; border:1px solid var(--border-light); border-radius:var(--radius-xl);
    padding:24px; display:flex; flex-direction:column; justify-content:space-between;
    transition:border-color .15s ease, box-shadow .15s ease;
  }
  .comm-card:hover { border-color:var(--border-mid); box-shadow:var(--shadow-md); }
  .comm-card-icon { width:48px; height:48px; border-radius:14px; background:var(--leaf-pale); display:flex; align-items:center; justify-content:center; color:var(--forest-mid); margin-bottom:14px; }
  .comm-card-title { font-size:18px; font-weight:700; color:var(--ink-dark); margin-bottom:6px; }
  .comm-card-desc { font-size:15px; color:var(--ink-muted); line-height:1.6; margin-bottom:20px; flex:1; }
  .comm-card .btn { width:100%; }
  .official-tag { display:inline-flex; align-items:center; gap:4px; padding:4px 10px; border-radius:999px; background:var(--leaf-pale); color:var(--forest-dark); font-size:13px; font-weight:700; margin-bottom:12px; }

  @media(max-width:700px){
    .comm-title { font-size:28px; line-height:1.18; }
    .comm-sub { font-size:16px; }
    .comm-card { padding:18px; }
    .comm-card:hover { box-shadow:none; }
  }
</style>
@endsection

@section('content')

{{-- Hero --}}
<div class="comm-hero">
  <div class="wrap fade-up">
    <span class="badge" style="margin-bottom:16px;" data-i18n="c_hero_badge">JAMII YA MKULIMA FORUM</span>
    <h1 class="comm-title" data-i18n="c_hero_title">
      Jiunge na Mtandao wa Wakulima Tanzania
    </h1>
    <p class="comm-sub" data-i18n="c_hero_sub">
      Pata taarifa za masoko, tahadhari za kilimo, na ushauri wa kitaalamu kupitia WhatsApp Channels, vikundi vya WhatsApp, Telegram, na mitandao ya kijamii.
    </p>
  </div>
</div>

{{-- Dynamic Community Directory Grid (B3 & B4) --}}
<section>
  <div class="wrap">
    <div style="text-align:center; margin-bottom:32px;">
      <span class="eyebrow" data-i18n="c_dir_eyebrow">DIREKTA YA JAMII</span>
      <h2 class="section-title" data-i18n="c_dir_title">Njia Rasmi na Vikundi vya Jamii</h2>
    </div>

    <div id="community_grid" class="channel-card-grid">
      <div class="comm-status" data-i18n="c_loading">
        ⏳ Inapakia vikundi na njia za jamii...
      </div>
    </div>
  </div>
</section>

@endsection

@section('page_scripts')
<script nonce="{{ $cspNonce ?? '' }}">
document.addEventListener('DOMContentLoaded', async () => {
  try {
    const res = await fetch('/api/v1/public/community-links', {
      headers: { 'Accept-Language': MK_LANG }
    });
    const json = await res.json();
    const channels = json.data;

    const grid = document.getElementById('community_grid');
    if (!channels || channels.length === 0) {
      grid.innerHTML = '<div class="comm-status">Vikundi vinahuishwa. Rudi hivi karibuni!</div>';
      return;
    }

    const escapeHtml = (value) => String(value ?? '').replace(/[&<>'"]/g, ch => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
    })[ch]);
    const safeExternalUrl = (value) => {
      try {
        const parsed = new URL(String(value), window.location.origin);
        return ['https:', 'http:'].includes(parsed.protocol) ? parsed.href : '#';
      } catch (_) { return '#'; }
    };

    grid.innerHTML = channels.map(c => {
      const targetUrl = safeExternalUrl(c.click_to_chat_url || c.url);
      const isSw = MK_LANG === 'sw';
      const officialBadge = c.is_official 
        ? `<span class="official-tag">✓ ${isSw ? 'Rasmi Mkulima Forum' : 'Official Mkulima Forum'}</span>` 
        : '';

      const descText = typeof c.description === 'object' && c.description !== null
        ? (c.description[MK_LANG] || c.description['sw'] || '')
        : (c.description || (isSw ? 'Jiunge na wakulima wengine kupata taarifa za kilimo.' : 'Join other farmers to get agricultural updates.'));

      const btnText = c.channel_type === 'WHATSAPP_BUSINESS'
        ? (isSw ? '📲 Anza Mazungumzo' : '📲 Start Chat')
        : (isSw ? '🔗 Jiunge Sasa' : '🔗 Join Channel');

      return `
        <div class="comm-card">
          <div>
            ${officialBadge}
            <div class="comm-card-icon"><svg class="ico" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false"><path d="M7.9 20A9 9 0 1 0 4 16.1L2 22Z"/></svg></div>
            <h3 class="comm-card-title">${escapeHtml(c.name)}</h3>
            <p class="comm-card-desc">${escapeHtml(descText)}</p>
          </div>
          <div>
            <a 
              href="${targetUrl}" 
              target="_blank" 
              rel="noopener"
              data-channel-uuid="${escapeHtml(c.uuid)}"
              data-channel-type="${escapeHtml(c.channel_type)}"
              class="btn btn-primary"
            >
              ${btnText}
            </a>
          </div>
        </div>
      `;
    }).join('');

    grid.querySelectorAll('[data-channel-uuid]').forEach(link => {
      link.addEventListener('click', () => trackCommunityClick(
        link.dataset.channelUuid,
        link.dataset.channelType
      ));
    });

  } catch (e) {
    console.error(e);
  }
});

async function trackCommunityClick(uuid, type) {
  try {
    await fetch('/api/v1/community/click', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        channel_uuid: uuid,
        event: type === 'WHATSAPP_BUSINESS' ? 'whatsapp_contact_clicked' : 'join_link_clicked'
      })
    });
  } catch(e){}
}

mkPageTranslations = {
  sw: {
    c_hero_badge: 'JAMII YA MKULIMA FORUM', c_hero_title: 'Jiunge na Mtandao wa Wakulima Tanzania',
    c_hero_sub: 'Pata taarifa za masoko, tahadhari za kilimo, na ushauri wa kitaalamu kupitia WhatsApp Channels, vikundi vya WhatsApp, Telegram, na mitandao ya kijamii.',
    c_dir_eyebrow: 'DIREKTA YA JAMII', c_dir_title: 'Njia Rasmi na Vikundi vya Jamii',
    c_loading: '⏳ Inapakia vikundi na njia za jamii...',
  },
  en: {
    c_hero_badge: 'MKULIMA COMMUNITY HUB', c_hero_title: 'Join the Farmer Network Across Tanzania',
    c_hero_sub: 'Access market prices, agricultural advisories, and expert guidance via WhatsApp Channels, WhatsApp Groups, Telegram, and social media.',
    c_dir_eyebrow: 'COMMUNITY DIRECTORY', c_dir_title: 'Official Channels & Farmer Communities',
    c_loading: '⏳ Loading community channels and groups...',
  }
};
</script>
@endsection
