@extends('layouts.public')

@section('title', 'Mkulima Community Hub | WhatsApp Groups, Channels & Farmer Communities Tanzania')
@section('meta_description', 'Connect with fellow farmers across Tanzania. Join official WhatsApp channels, crop-specific WhatsApp groups, Telegram communities, and social channels.')
@section('og_title', 'Mkulima Community Hub | AgriTech Tanzania')
@section('og_description', 'Connect with smallholder farmers, agronomists, and agrodealers across Tanzania via WhatsApp, Telegram, and Mkulima Forum.')

@section('head_extra')
<style>
  .comm-hero {
    background: radial-gradient(circle at 70% 30%, #1B4D2E 0%, var(--forest-dark) 70%);
    color:#fff; padding:80px 0; text-align:center;
  }
  .channel-card-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:24px; }
  @media(max-width:960px){ .channel-card-grid{ grid-template-columns:repeat(2,1fr); } }
  @media(max-width:560px){ .channel-card-grid{ grid-template-columns:1fr; } }

  .comm-card {
    background:var(--surface-card); border:1px solid var(--border-light); border-radius:var(--radius-xl);
    padding:28px; transition:all .25s ease; display:flex; flex-direction:column; justify-content:space-between;
  }
  .comm-card:hover { transform:translateY(-4px); box-shadow:var(--shadow-md); border-color:var(--border-mid); }
  .comm-card-icon { font-size:2.4rem; margin-bottom:12px; }
  .comm-card-title { font-size:1.15rem; font-weight:800; color:var(--ink-dark); margin-bottom:6px; }
  .comm-card-desc { font-size:.88rem; color:var(--ink-muted); line-height:1.6; margin-bottom:20px; flex:1; }
  .official-tag { display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:6px; background:#E8F0FE; color:#1A73E8; font-size:.7rem; font-weight:800; text-transform:uppercase; margin-bottom:8px; }
</style>
@endsection

@section('content')

{{-- Hero --}}
<div class="comm-hero">
  <div class="wrap fade-up" style="max-width:700px;">
    <span class="badge dark" style="margin-bottom:16px;" data-i18n="c_hero_badge">JAMII YA MKULIMA FORUM</span>
    <h1 style="font-size:clamp(2.2rem,5vw,3.4rem); font-weight:900; color:#fff; line-height:1.12; margin-bottom:16px;" data-i18n="c_hero_title">
      Jiunge na Mtandao wa Wakulima Tanzania
    </h1>
    <p style="font-size:1.05rem; color:rgba(255,255,255,.9); line-height:1.7;" data-i18n="c_hero_sub">
      Pata taarifa za masoko, tahadhari za kilimo, na ushauri wa kitaalamu kupitia WhatsApp Channels, vikundi vya WhatsApp, Telegram, na mitandao ya kijamii.
    </p>
  </div>
</div>

{{-- Dynamic Community Directory Grid (B3 & B4) --}}
<section>
  <div class="wrap">
    <div style="text-align:center; margin-bottom:40px;">
      <span class="eyebrow" data-i18n="c_dir_eyebrow">DIREKTA YA JAMII</span>
      <h2 class="section-title" data-i18n="c_dir_title">Njia Rasmi na Vikundi vya Jamii</h2>
    </div>

    <div id="community_grid" class="channel-card-grid">
      <div style="grid-column:1/-1; text-align:center; padding:40px;" data-i18n="c_loading">
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
      grid.innerHTML = '<div style="grid-column:1/-1; text-align:center; color:var(--ink-muted);">Vikundi vinahuishwa. Rudi hivi karibuni!</div>';
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
        <div class="comm-card fade-up">
          <div>
            ${officialBadge}
            <div class="comm-card-icon">💬</div>
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
              class="btn btn-primary btn-sm" 
              style="width:100%; justify-content:center;"
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
