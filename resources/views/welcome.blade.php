<!DOCTYPE html>
<html lang="sw">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>MkulimaForum - Jukwaa la Wakulima wa Afrika Mashariki</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a5f2a 0%, #0d3320 100%);
            color: white;
            min-height: 100vh;
        }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header {
            text-align: center;
            padding: 60px 20px 40px;
        }
        header h1 {
            font-size: 3em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
        }
        header p {
            font-size: 1.3em;
            opacity: 0.9;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            padding: 40px 20px;
        }
        .feature-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border-radius: 16px;
            padding: 30px;
            border: 1px solid rgba(255,255,255,0.2);
            transition: transform 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            background: rgba(255,255,255,0.15);
        }
        .feature-card h3 {
            font-size: 1.5em;
            margin-bottom: 10px;
            color: #7fff7f;
        }
        .feature-card p {
            opacity: 0.85;
            line-height: 1.6;
        }
        .api-section {
            background: rgba(0,0,0,0.3);
            border-radius: 16px;
            padding: 40px;
            margin: 40px 20px;
        }
        .api-section h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2em;
        }
        .endpoint {
            background: rgba(0,0,0,0.4);
            border-radius: 8px;
            padding: 15px 20px;
            margin: 10px 0;
            font-family: 'Courier New', monospace;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .method {
            background: #4CAF50;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: bold;
        }
        .status {
            text-align: center;
            padding: 40px;
            background: rgba(255,255,255,0.05);
            border-radius: 16px;
            margin: 20px;
        }
        .status-badge {
            display: inline-block;
            background: #4CAF50;
            padding: 10px 30px;
            border-radius: 30px;
            font-weight: bold;
            margin-top: 15px;
        }
        footer {
            text-align: center;
            padding: 40px;
            opacity: 0.7;
            font-size: 0.9em;
        }
        .cta-buttons {
            text-align: center;
            padding: 30px;
        }
        .btn {
            display: inline-block;
            background: #FFD700;
            color: #0d3320;
            padding: 15px 40px;
            border-radius: 30px;
            text-decoration: none;
            font-weight: bold;
            font-size: 1.1em;
            margin: 10px;
            transition: all 0.3s;
        }
        .btn:hover {
            transform: scale(1.05);
            box-shadow: 0 5px 20px rgba(255,215,0,0.4);
        }
        .btn-secondary {
            background: transparent;
            border: 2px solid white;
            color: white;
        }
        @media (max-width: 768px) {
            header h1 { font-size: 2em; }
            header p { font-size: 1em; }
            .endpoint { flex-direction: column; text-align: center; gap: 10px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>MkulimaForum</h1>
            <p>Jukwaa la Kidigitali la Wakulima wa Afrika Mashariki</p>
            <p style="font-size: 0.9em; margin-top: 10px; opacity: 0.7;">Digital Super-App for East African Farmers</p>
        </header>

        <div class="cta-buttons">
            <a href="/api/health" class="btn">Check API Status</a>
            <a href="https://github.com/lotaanywaki/mkulima-forum" class="btn btn-secondary">GitHub Repo</a>
        </div>

        <div class="features">
            <div class="feature-card">
                <h3>Soko la Kilimo</h3>
                <p>Nunua na uza mbolea, mbegu, dawa za wadudu, na vifaa vya kilimo. Malipo salama kupesa M-Pesa na Tigo Pesa.</p>
            </div>
            <div class="feature-card">
                <h3>Kagua Mimea</h3>
                <p>Piga picha mimea iliyoathirika na upewe utambuzi wa magonjwa na tiba kwa kutumia AI.</p>
            </div>
            <div class="feature-card">
                <h3>Jukwaa la Majadiliano</h3>
                <p>Shiriki uzoefu, uliza maswali, na jifunze kutoka kwa wakulima wengine kupitia jukwaa la kidigitali.</p>
            </div>
            <div class="feature-card">
                <h3>Mtaalamu wa AI</h3>
                <p>Pata ushauri wa kitaalamu wa kilimo kutoka kwa mtaalamu wa AI anayezungumza Kiswahili.</p>
            </div>
            <div class="feature-card">
                <h3>Masoko ya Huduma</h3>
                <p>Tafuta wataalamu wa kilimo, wakulima wa mifugo, na watoa huduma wengine karibu nawe.</p>
            </div>
            <div class="feature-card">
                <h3>Notisi za Papo Hapo</h3>
                <p>Pata arifa za bei, hali ya hewa, na taarifa muhimu kwa wakati halisi.</p>
            </div>
        </div>

        <div class="api-section">
            <h2>API Endpoints</h2>
            <div class="endpoint">
                <span><span class="method">GET</span> /api/health</span>
                <span>Check server status</span>
            </div>
            <div class="endpoint">
                <span><span class="method">POST</span> /api/auth/otp/request</span>
                <span>Request OTP code</span>
            </div>
            <div class="endpoint">
                <span><span class="method">POST</span> /api/auth/otp/verify</span>
                <span>Verify OTP & get token</span>
            </div>
            <div class="endpoint">
                <span><span class="method">GET</span> /api/marketplace/categories</span>
                <span>List product categories</span>
            </div>
            <div class="endpoint">
                <span><span class="method">GET</span> /api/marketplace/products</span>
                <span>List all products</span>
            </div>
            <div class="endpoint">
                <span><span class="method">POST</span> /api/scanner/scan</span>
                <span>Upload image for AI diagnosis</span>
            </div>
            <div class="endpoint">
                <span><span class="method">POST</span> /api/agronomist/ask</span>
                <span>Ask AI agronomist</span>
            </div>
            <div class="endpoint">
                <span><span class="method">POST</span> /api/payments/initiate</span>
                <span>Initiate M-Pesa/Tigo payment</span>
            </div>
        </div>

        <div class="status">
            <h2>Server Status</h2>
            <div class="status-badge">ONLINE</div>
            <p style="margin-top: 15px; opacity: 0.8;">
                Version: 1.0.0 | Environment: Production<br>
                Database: PostgreSQL + pgvector | Cache: Redis<br>
                Auth: Laravel Sanctum | Payments: M-Pesa + Tigo Pesa
            </p>
        </div>
    </div>

    <footer>
        <p>MkulimaForum &copy; 2025 | Built for East African Farmers</p>
        <p style="margin-top: 10px; font-size: 0.8em;">Powered by Laravel 11, PostgreSQL, Redis, Flutter & AI</p>
    </footer>
</body>
</html>
