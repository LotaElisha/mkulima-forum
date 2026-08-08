<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MkulimaForum — Shiriki. Jifunze. Endelea.</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800;900&family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background: linear-gradient(135deg, #0C3619 0%, #165A2A 100%);
            color: white;
            min-height: 100vh;
        }
        .container { max-width: 1140px; margin: 0 auto; padding: 30px 20px; }
        header { text-align: center; padding: 40px 20px 20px; }
        header img { height: 70px; width: auto; margin: 0 auto 15px; }
        .motto { color: #FFBA36; font-weight: 800; letter-spacing: 0.1em; text-transform: uppercase; font-size: 0.9em; }

        .cta-buttons { text-align: center; padding: 25px 0; }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #F5A623, #FFBA36);
            color: #0C3619;
            padding: 14px 34px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1rem;
            margin: 8px;
            transition: all 0.3s;
            box-shadow: 0 4px 15px rgba(245,166,35,0.3);
        }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(245,166,35,0.5); }
        .btn-secondary { background: transparent; border: 2px solid rgba(255,255,255,0.4); color: white; box-shadow: none; }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            padding: 30px 0;
        }
        .feature-card {
            background: rgba(255,255,255,0.08);
            backdrop-filter: blur(12px);
            border-radius: 20px;
            padding: 28px;
            border: 1px solid rgba(255,255,255,0.15);
            transition: transform 0.3s;
        }
        .feature-card:hover { transform: translateY(-4px); background: rgba(255,255,255,0.12); }
        .feature-card h3 { font-family: 'Outfit', sans-serif; font-size: 1.35em; margin-bottom: 10px; color: #9FE870; }
        .feature-card p { opacity: 0.88; line-height: 1.6; font-size: 0.95em; }

        .api-section {
            background: rgba(0,0,0,0.35);
            border-radius: 24px;
            padding: 36px;
            margin: 30px 0;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .api-section h2 { font-family: 'Outfit', sans-serif; text-align: center; margin-bottom: 24px; font-size: 1.8em; }
        .endpoint {
            background: rgba(0,0,0,0.4);
            border-radius: 12px;
            padding: 14px 20px;
            margin: 10px 0;
            font-family: ui-monospace, monospace;
            font-size: 0.9em;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .method { background: #4C9B27; padding: 3px 10px; border-radius: 6px; font-size: 0.8em; font-weight: bold; }

        footer { text-align: center; padding: 40px 0 20px; opacity: 0.75; font-size: 0.85em; }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <img src="/images/brand-banner.png" alt="MkulimaForum">
            <div class="motto">Shiriki &bull; Jifunze &bull; Endelea</div>
        </header>

        <div class="cta-buttons">
            <a href="/" class="btn">Fungua Tovuti Kuu</a>
            <a href="/app/mkulima-forum.apk" class="btn btn-secondary">Pakua Android APK</a>
            <a href="/admin/login" class="btn btn-secondary">Admin Portal</a>
        </div>

        <div class="features">
            <div class="feature-card">
                <h3>🌿 AI Plant Scanner</h3>
                <p>Gemini 3 Flash inatambua magonjwa ya mimea na kutoa tiba sahihi kwa Kiswahili.</p>
            </div>
            <div class="feature-card">
                <h3>💬 Mkulima Bot</h3>
                <p>Ushauri wa kitaalamu wa kilimo kwa njia ya mazungumzo kupitia AI.</p>
            </div>
            <div class="feature-card">
                <h3>🛒 Soko la Kilimo</h3>
                <p>Nunua na uza mbegu, mbolea, na mazao kwa ulinzi wa malipo ya Escrow.</p>
            </div>
        </div>

        <div class="api-section">
            <h2>API Core Endpoints</h2>
            <div class="endpoint">
                <span><span class="method">GET</span> /api/health</span>
                <span>Server Health &amp; Version Check</span>
            </div>
            <div class="endpoint">
                <span><span class="method">POST</span> /api/scanner/scan</span>
                <span>AI Plant Disease Scanning</span>
            </div>
            <div class="endpoint">
                <span><span class="method">POST</span> /api/agronomist/ask</span>
                <span>Virtual Agronomist Q&amp;A</span>
            </div>
        </div>

        <footer>
            MkulimaForum &copy; {{ date('Y') }} | Digital Platform for East African Farmers
        </footer>
    </div>
</body>
</html>
