@extends('layouts.auth')

@section('title', 'Ingia — MkulimaForum')

@section('content')
  <h1>Karibu tena</h1>
  <p class="lead">Ingia kuendelea na kilimo chako.</p>

  <div id="alert" class="alert" role="alert" aria-live="polite"></div>

  {{-- Method switch. The phone tab is rendered only when OTP is actually
       switched on. It used to be shown unconditionally while auth.otp_enabled
       defaults to false in production, so a farmer could pick "Namba ya Simu",
       enter their number, and get a 503 with no explanation. --}}
  @php($otpEnabled = (bool) app(\App\Services\Spine\ConfigRegistry::class)->get('auth.otp_enabled', ! app()->environment('production')))

  @if($otpEnabled)
    <div class="tabs" role="tablist" aria-label="Njia ya kuingia">
      <button class="tab is-active" id="tab-email" type="button" role="tab"
              aria-selected="true" aria-controls="panel-email">Barua pepe</button>
      <button class="tab" id="tab-phone" type="button" role="tab"
              aria-selected="false" aria-controls="panel-phone">Namba ya simu</button>
    </div>
  @endif

  {{-- ── Email ──────────────────────────────────────────────────────── --}}
  <form id="email-form" role="tabpanel" aria-labelledby="tab-email" novalidate>
    <div class="field">
      <label for="email">Barua pepe</label>
      <div class="control">
        <input id="email" type="email" inputmode="email" autocomplete="email"
               autocapitalize="none" spellcheck="false"
               placeholder="mfano@barua.com" required>
      </div>
      <p class="field-error" id="email-error"></p>
    </div>

    <div class="field">
      <div class="label-row">
        <label for="password">Nenosiri</label>
        <a href="/forgot-password" class="label-link">Umesahau?</a>
      </div>
      <div class="control has-toggle">
        <input id="password" type="password" autocomplete="current-password"
               placeholder="Weka nenosiri lako" required>
        <button class="toggle-visibility" type="button" data-target="password"
                aria-label="Onyesha nenosiri" aria-pressed="false">
          <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2.06 12.35a1 1 0 0 1 0-.7 10.75 10.75 0 0 1 19.87 0 1 1 0 0 1 0 .7 10.75 10.75 0 0 1-19.87 0"/><circle cx="12" cy="12" r="3"/></svg>
        </button>
      </div>
      <p class="field-error" id="password-error"></p>
    </div>

    <button class="btn btn-primary" id="email-submit" type="submit">
      <span class="label">Ingia</span>
    </button>
  </form>

  {{-- ── Phone OTP ──────────────────────────────────────────────────── --}}
  @if($otpEnabled)
    <form id="phone-form" class="hidden" role="tabpanel" aria-labelledby="tab-phone" novalidate>
      <div class="field">
        <label for="phone">Namba ya simu</label>
        <div class="control prefixed">
          <span class="prefix" aria-hidden="true">+255</span>
          <input id="phone" type="tel" inputmode="numeric" autocomplete="tel-national"
                 maxlength="9" placeholder="7XX XXX XXX" required>
        </div>
        <p class="hint">Weka tarakimu 9 baada ya 255, mfano 712345678.</p>
        <p class="field-error" id="phone-error"></p>
      </div>

      <button class="btn btn-quiet" id="otp-request" type="button">
        <span class="label">Tuma msimbo</span>
      </button>

      <div class="field hidden" id="otp-group" style="margin-top:18px">
        <label for="otp_code">Msimbo wa uthibitisho</label>
        <div class="control">
          <input id="otp_code" type="text" inputmode="numeric" autocomplete="one-time-code"
                 maxlength="6" pattern="[0-9]{6}" placeholder="******" class="otp-input" required>
        </div>
        <p class="field-error" id="otp_code-error"></p>
      </div>

      <button class="btn btn-primary hidden" id="phone-submit" type="submit" style="margin-top:6px">
        <span class="label">Thibitisha na uingie</span>
      </button>
    </form>
  @endif

  {{-- ── Social ─────────────────────────────────────────────────────── --}}
  @if(config('services.social.google_client_ids.0') || config('services.social.apple_client_ids.0'))
    <div class="divider"><span>AU</span></div>
    <div class="socials">
      <button class="btn btn-quiet" id="google-social" type="button"
              {{ config('services.social.google_client_ids.0') ? '' : 'disabled' }}>
        <svg width="19" height="19" viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.76h3.57c2.08-1.92 3.28-4.74 3.28-8.09Z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.76c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84A11 11 0 0 0 12 23Z"/><path fill="#FBBC05" d="M5.84 14.11a6.6 6.6 0 0 1 0-4.22V7.05H2.18a11 11 0 0 0 0 9.9l3.66-2.84Z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1a11 11 0 0 0-9.82 6.05l3.66 2.84C6.71 7.29 9.14 5.38 12 5.38Z"/></svg>
        Google
      </button>
      <button class="btn btn-quiet" id="apple-social" type="button"
              {{ config('services.social.apple_client_ids.0') ? '' : 'disabled' }}>
        <svg width="19" height="19" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16.36 12.72c.02 2.6 2.28 3.47 2.3 3.48-.02.06-.36 1.24-1.19 2.46-.72 1.05-1.47 2.1-2.65 2.12-1.16.02-1.53-.69-2.85-.69s-1.73.67-2.83.71c-1.14.04-2-1.13-2.73-2.18-1.48-2.14-2.62-6.06-1.1-8.7A4.24 4.24 0 0 1 8.9 7.75c1.11-.02 2.16.75 2.85.75.68 0 1.96-.93 3.3-.79.56.02 2.14.23 3.15 1.71-.08.05-1.88 1.1-1.86 3.29M14.2 5.38c.61-.74 1.02-1.77.9-2.79-.88.04-1.94.59-2.57 1.32-.56.65-1.05 1.7-.92 2.7.98.08 1.98-.5 2.59-1.23"/></svg>
        Apple
      </button>
    </div>
  @endif
@endsection

@section('foot')
  Huna akaunti bado? <a href="/register">Jisajili hapa</a>
@endsection

@section('head_extra')
<style>
  /* Screen-specific additions on top of layouts/auth.blade.php. */
  .hidden { display: none !important; }

  .tabs {
    display: flex; gap: 4px; padding: 4px; margin-bottom: 22px;
    background: var(--surface-sunken); border-radius: 12px;
  }
  .tab {
    flex: 1; min-height: 44px; border: 0; border-radius: 9px;
    background: transparent; font-family: inherit; font-size: .9375rem;
    font-weight: 600; color: var(--ink-muted); cursor: pointer;
    transition: background .15s, color .15s;
  }
  .tab.is-active {
    background: var(--surface); color: var(--green-700);
    box-shadow: 0 1px 3px rgba(16,22,19,.10);
  }

  .label-row { display: flex; align-items: baseline; justify-content: space-between; gap: 12px; }
  .label-link { font-size: .875rem; font-weight: 600; }

  /* +255 sits inside the field so the farmer types only the nine digits they
     know. The old input demanded the full "255700000000" and rejected
     everything else with a regex error. */
  .control.prefixed { align-items: stretch; }
  .control.prefixed .prefix {
    display: flex; align-items: center; padding: 0 12px 0 16px;
    background: var(--surface-sunken); color: var(--ink-muted);
    border: 1.5px solid var(--line-strong); border-right: 0;
    border-radius: var(--r-md) 0 0 var(--r-md);
    font-size: 16px; font-weight: 600; white-space: nowrap;
  }
  .control.prefixed input { border-radius: 0 var(--r-md) var(--r-md) 0; }

  .otp-input { letter-spacing: .5em; font-size: 22px !important; text-align: center; font-weight: 700; }

  .divider {
    display: flex; align-items: center; gap: 14px;
    margin: 24px 0 18px; color: var(--ink-muted); font-size: .8125rem; font-weight: 600;
  }
  .divider::before, .divider::after { content: ""; flex: 1; height: 1px; background: var(--line); }

  .socials { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
  .socials .btn:disabled { opacity: .45; cursor: not-allowed; }
</style>

<script nonce="{{ $cspNonce ?? '' }}" defer>
document.addEventListener('DOMContentLoaded', function () {
  var googleClientId = @json(config('services.social.google_client_ids.0'));
  var appleClientId  = @json(config('services.social.apple_client_ids.0'));
  var alertBox = document.getElementById('alert');

  function showAlert(message, kind) {
    alertBox.textContent = message;
    alertBox.className = 'alert show ' + (kind === 'ok' ? 'alert-ok' : 'alert-error');
    alertBox.scrollIntoView({ block: 'nearest' });
  }

  function fieldError(name, message) {
    var node = document.getElementById(name + '-error');
    var input = document.getElementById(name);
    if (!node) return;
    node.textContent = message || '';
    node.classList.toggle('show', Boolean(message));
    if (input) input.setAttribute('aria-invalid', message ? 'true' : 'false');
  }

  function clearErrors() {
    ['email', 'password', 'phone', 'otp_code'].forEach(function (n) { fieldError(n, ''); });
    alertBox.className = 'alert';
  }

  function busy(id, state, idleText) {
    var button = document.getElementById(id);
    if (!button) return;
    button.disabled = state;
    button.querySelector('.label').textContent = state ? 'Subiri…' : idleText;
  }

  // Status banners for arrivals from an emailed link, so the user is told what
  // happened instead of landing on a silent login screen.
  var params = new URLSearchParams(window.location.search);
  var messages = {
    'success': ['Barua pepe yako imethibitishwa. Sasa unaweza kuingia.', 'ok'],
    'already': ['Barua pepe hii tayari ilikuwa imethibitishwa.', 'ok'],
    'email-changed': ['Barua pepe yako mpya imethibitishwa. Tumia hiyo kuingia.', 'ok'],
    'invalid': ['Kiungo cha uthibitisho si sahihi au muda wake umepita. Omba kiungo kipya.', 'error'],
    'taken': ['Barua pepe hiyo tayari inatumiwa na akaunti nyingine.', 'error']
  };
  if (params.has('verified') && messages[params.get('verified')]) {
    showAlert(messages[params.get('verified')][0], messages[params.get('verified')][1]);
  }
  if (params.get('reset') === 'success') {
    showAlert('Nenosiri lako limebadilishwa. Ingia kwa nenosiri jipya.', 'ok');
  }

  // Password visibility.
  document.querySelectorAll('.toggle-visibility').forEach(function (toggle) {
    toggle.addEventListener('click', function () {
      var input = document.getElementById(toggle.dataset.target);
      var reveal = input.type === 'password';
      input.type = reveal ? 'text' : 'password';
      toggle.setAttribute('aria-pressed', String(reveal));
      toggle.setAttribute('aria-label', reveal ? 'Ficha nenosiri' : 'Onyesha nenosiri');
    });
  });

  // Tabs.
  var tabEmail = document.getElementById('tab-email');
  var tabPhone = document.getElementById('tab-phone');
  if (tabEmail && tabPhone) {
    function selectTab(which) {
      var email = which === 'email';
      tabEmail.classList.toggle('is-active', email);
      tabPhone.classList.toggle('is-active', !email);
      tabEmail.setAttribute('aria-selected', String(email));
      tabPhone.setAttribute('aria-selected', String(!email));
      document.getElementById('email-form').classList.toggle('hidden', !email);
      document.getElementById('phone-form').classList.toggle('hidden', email);
      clearErrors();
    }
    tabEmail.addEventListener('click', function () { selectTab('email'); });
    tabPhone.addEventListener('click', function () { selectTab('phone'); });
  }

  async function post(url, body) {
    var response = await fetch(url, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Auth-Client': 'web'
      },
      body: JSON.stringify(body)
    });
    var data = await response.json().catch(function () { return {}; });
    return { ok: response.ok, status: response.status, data: data };
  }

  // Where a signed-in web user actually goes. This used to be '/', the
  // marketing home page, which has no logged-in state — so a successful login
  // looked identical to never having logged in at all.
  function landAfterLogin() {
    window.location.href = @json(\App\Support\AppDownload::hasWebBuild() ? '/app/web/' : '/download');
  }

  document.getElementById('email-form').addEventListener('submit', async function (event) {
    event.preventDefault();
    clearErrors();

    var email = document.getElementById('email').value.trim();
    var password = document.getElementById('password').value;

    if (!email || email.indexOf('@') < 1) {
      fieldError('email', 'Weka barua pepe sahihi.');
      document.getElementById('email').focus();
      return;
    }
    if (!password) {
      fieldError('password', 'Weka nenosiri lako.');
      document.getElementById('password').focus();
      return;
    }

    busy('email-submit', true, 'Ingia');
    try {
      var result = await post('/api/auth/login/email', { email: email, password: password });
      if (result.ok) {
        showAlert('Umefanikiwa kuingia. Unaelekezwa…', 'ok');
        landAfterLogin();
        return;
      }
      if (result.status === 429) {
        showAlert('Umejaribu mara nyingi mfululizo. Subiri dakika moja kisha jaribu tena.');
      } else if (result.status === 401) {
        showAlert('Barua pepe au nenosiri si sahihi.');
      } else {
        showAlert(result.data.message || 'Kuingia kumeshindikana. Jaribu tena.');
      }
    } catch (error) {
      showAlert('Hakuna mtandao. Angalia muunganisho wako kisha jaribu tena.');
    } finally {
      busy('email-submit', false, 'Ingia');
    }
  });

  // ── Phone OTP ──────────────────────────────────────────────────────
  var otpRequest = document.getElementById('otp-request');
  if (otpRequest) {
    function fullPhone() {
      return '255' + document.getElementById('phone').value.replace(/\D/g, '').replace(/^0+/, '');
    }

    otpRequest.addEventListener('click', async function () {
      clearErrors();
      var phone = fullPhone();
      if (!/^255[0-9]{9}$/.test(phone)) {
        fieldError('phone', 'Weka tarakimu 9, mfano 712345678.');
        document.getElementById('phone').focus();
        return;
      }

      busy('otp-request', true, 'Tuma msimbo');
      try {
        var result = await post('/api/auth/otp/request', { phone: phone, purpose: 'login' });
        if (result.ok) {
          showAlert('Msimbo umetumwa kwa SMS.', 'ok');
          document.getElementById('otp-group').classList.remove('hidden');
          document.getElementById('phone-submit').classList.remove('hidden');
          document.getElementById('otp_code').focus();
          otpRequest.querySelector('.label').textContent = 'Tuma tena';
        } else if (result.status === 429) {
          showAlert('Umeomba msimbo mara nyingi. Subiri kidogo kisha jaribu tena.');
        } else if (result.status === 503) {
          showAlert('Kuingia kwa SMS hakupatikani kwa sasa. Tumia barua pepe.');
        } else {
          showAlert(result.data.message || 'Imeshindikana kutuma msimbo.');
        }
      } catch (error) {
        showAlert('Hakuna mtandao. Angalia muunganisho wako kisha jaribu tena.');
      } finally {
        busy('otp-request', false, otpRequest.querySelector('.label').textContent);
      }
    });

    document.getElementById('phone-form').addEventListener('submit', async function (event) {
      event.preventDefault();
      clearErrors();

      var code = document.getElementById('otp_code').value.replace(/\D/g, '');
      if (code.length !== 6) {
        fieldError('otp_code', 'Msimbo una tarakimu 6.');
        return;
      }

      busy('phone-submit', true, 'Thibitisha na uingie');
      try {
        var result = await post('/api/auth/otp/verify', { phone: fullPhone(), code: code, purpose: 'login' });
        if (result.ok) {
          showAlert('Umefanikiwa kuingia. Unaelekezwa…', 'ok');
          landAfterLogin();
          return;
        }
        if (result.status === 404) {
          showAlert('Hakuna akaunti yenye namba hii. Jisajili kwanza.');
        } else {
          showAlert(result.data.message || 'Msimbo si sahihi au muda wake umepita.');
        }
      } catch (error) {
        showAlert('Hakuna mtandao. Angalia muunganisho wako kisha jaribu tena.');
      } finally {
        busy('phone-submit', false, 'Thibitisha na uingie');
      }
    });
  }

  // ── Social ─────────────────────────────────────────────────────────
  async function social(provider, identityToken, name) {
    var result = await post('/api/auth/social', {
      provider: provider, identity_token: identityToken, name: name || null
    });
    if (!result.ok) {
      throw new Error(result.data.message
        || Object.values(result.data.errors || {}).flat()[0]
        || 'Imeshindikana.');
    }
    landAfterLogin();
  }

  var googleButton = document.getElementById('google-social');
  if (googleButton) {
    googleButton.addEventListener('click', function () {
      try {
        google.accounts.id.initialize({
          client_id: googleClientId,
          callback: function (r) { social('google', r.credential).catch(function (e) { showAlert(e.message); }); }
        });
        google.accounts.id.prompt();
      } catch (error) {
        showAlert('Google sign-in haijakamilika kusanidiwa.');
      }
    });
  }

  var appleButton = document.getElementById('apple-social');
  if (appleButton) {
    appleButton.addEventListener('click', async function () {
      try {
        AppleID.auth.init({
          clientId: appleClientId, scope: 'name email',
          redirectURI: location.origin + '/login', usePopup: true
        });
        var r = await AppleID.auth.signIn();
        await social('apple', r.authorization.id_token, r.user && r.user.name && r.user.name.firstName);
      } catch (error) {
        showAlert('Apple sign-in haikukamilika.');
      }
    });
  }
});
</script>

@if(config('services.social.google_client_ids.0'))
  <script nonce="{{ $cspNonce ?? '' }}" src="https://accounts.google.com/gsi/client" async></script>
@endif
@if(config('services.social.apple_client_ids.0'))
  <script nonce="{{ $cspNonce ?? '' }}" src="https://appleid.cdn-apple.com/appleauth/static/jsapi/appleid/1/en_US/appleid.auth.js"></script>
@endif
@endsection
