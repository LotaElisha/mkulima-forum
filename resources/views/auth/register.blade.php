<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Jisajili — Mkulima Forum</title>
<style>
  :root {
    --green: #2E7D32;
    --green-dark: #1B5E20;
    --green-light: #E8F5E9;
    --amber: #F9A825;
    --amber-dark: #C17900;
    --ink: #1a2b1d;
    --muted: #5c6f60;
    --bg: #F6F8F6;
    --card: rgba(255, 255, 255, 0.95);
    --border: #e3eae4;
    --radius: 16px;
    --shadow: 0 8px 32px 0 rgba(27, 94, 32, 0.08);
  }

  * {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
  }

  body {
    background: radial-gradient(circle at 10% 20%, var(--green-light) 0%, var(--bg) 90%);
    color: var(--ink);
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 20px;
  }

  .container {
    width: 100%;
    max-width: 460px;
    margin: 40px 0;
  }

  .logo-wrap {
    text-align: center;
    margin-bottom: 24px;
  }

  .logo {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-weight: 800;
    font-size: 1.5rem;
    color: var(--green-dark);
    text-decoration: none;
  }

  .logo .leaf {
    width: 38px;
    height: 38px;
    background: var(--green);
    border-radius: 10px;
    display: grid;
    place-items: center;
    color: #fff;
    font-size: 20px;
  }

  .card {
    background: var(--card);
    backdrop-filter: blur(8px);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 32px;
    box-shadow: var(--shadow);
  }

  h1 {
    font-size: 1.4rem;
    font-weight: 800;
    text-align: center;
    margin-bottom: 24px;
    color: var(--green-dark);
  }

  .form-group {
    margin-bottom: 20px;
  }

  label {
    display: block;
    font-size: 0.85rem;
    font-weight: 700;
    color: var(--muted);
    margin-bottom: 6px;
  }

  input, select {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid var(--border);
    border-radius: 12px;
    outline: none;
    font-size: 0.95rem;
    transition: all 0.2s ease;
    background: #fcfdfc;
  }

  input:focus, select:focus {
    border-color: var(--green);
    box-shadow: 0 0 0 3px rgba(46, 125, 50, 0.12);
    background: #fff;
  }

  .input-with-btn {
    display: flex;
    gap: 8px;
  }

  .input-with-btn input {
    flex: 1;
  }

  .btn {
    width: 100%;
    padding: 12px;
    border-radius: 12px;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    border: none;
    transition: all 0.15s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
  }

  .btn-green {
    background: var(--green);
    color: #fff;
  }

  .btn-green:hover {
    background: var(--green-dark);
    transform: translateY(-1px);
  }

  .btn-ghost {
    background: transparent;
    color: var(--green);
    border: 2px solid var(--green);
    width: auto;
    white-space: nowrap;
    padding: 0 16px;
  }

  .btn-ghost:hover {
    background: var(--green-light);
  }

  .btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none !important;
  }

  .alert {
    padding: 12px;
    border-radius: 10px;
    font-size: 0.85rem;
    margin-bottom: 20px;
    display: none;
  }

  .alert-danger {
    background: #ffebee;
    color: #c62828;
    border: 1px solid #ffcdd2;
  }

  .alert-success {
    background: #e8f5e9;
    color: #2e7d32;
    border: 1px solid #c8e6c9;
  }

  .footer-links {
    margin-top: 24px;
    text-align: center;
    font-size: 0.88rem;
    color: var(--muted);
  }

  .footer-links a {
    color: var(--green);
    font-weight: 700;
    text-decoration: none;
  }

  .footer-links a:hover {
    text-decoration: underline;
  }

  /* Spinner */
  .spinner {
    width: 18px;
    height: 18px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-radius: 50%;
    border-top-color: #fff;
    animation: spin 0.8s linear infinite;
  }

  @keyframes spin {
    to { transform: rotate(360deg); }
  }

  /* Hidden Helper */
  .hidden {
    display: none !important;
  }
</style>
</head>
<body>

<div class="container">
  <div class="logo-wrap">
    <a class="logo" href="/"><span class="leaf">🌱</span> Mkulima Forum</a>
  </div>

  <div class="card">
    <h1>Jisajili kama Mtumiaji Mpya</h1>

    <!-- Alert Message -->
    <div id="alert" class="alert"></div>

    <form id="register-form" onsubmit="handleRegistration(event)">
      <div class="form-group">
        <label for="name">Jina Kamili</label>
        <input type="text" id="name" placeholder="Weka jina lako kamili" required>
      </div>

      <div class="form-group">
        <label for="role">Aina ya Mtumiaji (Role)</label>
        <select id="role" required>
          <option value="farmer">Mkulima (Farmer)</option>
          <option value="buyer">Mnunuzi wa Mazao (Buyer)</option>
          <option value="agrodealer">Muuzaji Pembejeo (Agrodealer)</option>
          <option value="seller">Muuzaji wa Mazao / Bidhaa</option>
          <option value="agronomist">Mtaalamu wa Kilimo (Agronomist)</option>
          <option value="veterinary">Mtaalamu wa Mifugo (Vet)</option>
          <option value="logistics">Msafirishaji / Logistics</option>
        </select>
      </div>

      <div class="form-group">
        <label for="country">Nchi</label>
        <select id="country" required>
          <option value="tz">Tanzania</option>
          <option value="ke">Kenya</option>
          <option value="ug">Uganda</option>
          <option value="rw">Rwanda</option>
        </select>
      </div>

      <div class="form-group">
        <label for="phone">Namba ya Simu (Format: 2557XXXXXXXX)</label>
        <div class="input-with-btn">
          <input type="tel" id="phone" placeholder="255700000000" pattern="^255[0-9]{9}$" required>
          <button type="button" id="otp-request-btn" class="btn btn-ghost" onclick="requestOtpCode()">Tuma Code</button>
        </div>
      </div>

      <div id="otp-input-group" class="form-group hidden">
        <label for="otp_code">Code ya Uhakiki (OTP)</label>
        <input type="text" id="otp_code" placeholder="Weka tarakimu 6 zilizotumwa" minlength="6" maxlength="6">
        <div id="dev-code-alert" class="alert alert-success" style="margin-top: 8px; font-weight: 700;"></div>
      </div>

      <button type="submit" id="submit-btn" class="btn btn-green" disabled>
        <span>Jisajili Sasa</span>
      </button>
    </form>

    <div class="footer-links">
      Tayari una akaunti? <a href="/login">Ingia Hapa</a>
    </div>
  </div>
</div>

<script>
  function showAlert(msg, type = 'danger') {
    const alertBox = document.getElementById('alert');
    alertBox.textContent = msg;
    alertBox.className = `alert alert-${type}`;
    alertBox.style.display = 'block';
  }

  function showLoading(btnId, show = true) {
    const btn = document.getElementById(btnId);
    const span = btn.querySelector('span');
    if (show) {
      btn.disabled = true;
      const loader = document.createElement('div');
      loader.className = 'spinner';
      btn.appendChild(loader);
      if (span) span.style.display = 'none';
    } else {
      btn.disabled = false;
      const loader = btn.querySelector('.spinner');
      if (loader) btn.removeChild(loader);
      if (span) span.style.display = 'inline';
    }
  }

  // Request OTP for registration
  async function requestOtpCode() {
    const phone = document.getElementById('phone').value;
    const requestBtn = document.getElementById('otp-request-btn');
    document.getElementById('alert').style.display = 'none';

    if (!/^255[0-9]{9}$/.test(phone)) {
      showAlert('Weka namba sahihi ya simu kuanza na 255 (mfano: 255700000000)');
      return;
    }

    requestBtn.disabled = true;
    requestBtn.textContent = 'Inatuma...';

    try {
      const res = await fetch('/api/auth/otp/request', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ phone, purpose: 'register' })
      });

      const data = await res.json();

      if (res.ok) {
        showAlert('Code ya uhakiki imetumwa kwa SMS.', 'success');
        document.getElementById('otp-input-group').classList.remove('hidden');
        document.getElementById('submit-btn').disabled = false;
        requestBtn.textContent = 'Tuma Tena';

        // Check if sandbox dev_code was returned (only in local/debug)
        if (data.dev_code) {
          const devAlert = document.getElementById('dev-code-alert');
          devAlert.textContent = `[DEBUG MODE] Code yako ni: ${data.dev_code}`;
          devAlert.style.display = 'block';
        }
      } else {
        showAlert(data.message || 'Imeshindikana kutuma code ya uhakiki.');
        requestBtn.disabled = false;
        requestBtn.textContent = 'Tuma Code';
      }
    } catch (err) {
      showAlert('Hitilafu ya mtandao imetokea. Tafadhali jaribu tena.');
      requestBtn.disabled = false;
      requestBtn.textContent = 'Tuma Code';
    } finally {
      requestBtn.disabled = false;
    }
  }

  // Perform OTP registration verification
  async function handleRegistration(e) {
    e.preventDefault();
    document.getElementById('alert').style.display = 'none';
    showLoading('submit-btn', true);

    const name = document.getElementById('name').value;
    const role = document.getElementById('role').value;
    const country_code = document.getElementById('country').value;
    const phone = document.getElementById('phone').value;
    const code = document.getElementById('otp_code').value;

    try {
      const res = await fetch('/api/auth/otp/verify', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          phone,
          code,
          purpose: 'register',
          name,
          role,
          country_code
        })
      });

      const data = await res.json();

      if (res.ok && data.token) {
        localStorage.setItem('user_token', data.token);
        localStorage.setItem('user_data', JSON.stringify(data.user));
        showAlert('Jisajili kumefanikiwa! Unahamishiwa kwenye Ukurasa Mkuu...', 'success');
        setTimeout(() => { window.location.href = '/'; }, 1500);
      } else {
        showAlert(data.message || 'Code ya uhakiki imekosewa au muda wake umepita.');
      }
    } catch (err) {
      showAlert('Hitilafu ya mtandao imetokea. Tafadhali jaribu tena.');
    } finally {
      showLoading('submit-btn', false);
    }
  }

  // Redirect if already logged in
  if (localStorage.getItem('user_token')) {
    window.location.href = '/';
  }
</script>

</body>
</html>
