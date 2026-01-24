import { writeFileSync, mkdirSync, existsSync } from 'fs';
import { join, dirname } from 'path';
import { fileURLToPath } from 'url';

const __dirname = dirname(fileURLToPath(import.meta.url));
const coverageDir = join(__dirname, '..', 'coverage');
const outputPath = join(coverageDir, 'index.html');

const html = `<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Couverture de code — PHP Laravel & Vue.js</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    * { box-sizing: border-box; }
    body { font-family: system-ui, -apple-system, sans-serif; margin: 0; padding: 16px; background: #0f172a; color: #e2e8f0; }
    h1 { text-align: center; margin: 0 0 12px; font-size: 1.5rem; font-weight: 600; }
    .nav { display: flex; gap: 10px; justify-content: center; flex-wrap: wrap; margin-bottom: 12px; }
    .nav a { padding: 10px 18px; background: #1e293b; color: #94a3b8; text-decoration: none; border-radius: 8px; font-weight: 500; transition: background .2s, color .2s; }
    .nav a:hover { background: #334155; color: #f1f5f9; }
    .nav a.primary { background: #3b82f6; color: #fff; }
    .nav a.primary:hover { background: #2563eb; }
    .frames { display: flex; flex-direction: column; gap: 12px; }
    .frame-wrap { border-radius: 10px; overflow: hidden; border: 1px solid #334155; background: #fff; }
    .frame-wrap h2 { margin: 0; padding: 10px 14px; font-size: .9rem; background: #1e293b; color: #94a3b8; }
    .frame-wrap iframe { display: block; width: 100%; height: 55vh; min-height: 360px; border: 0; }
    .note { text-align: center; font-size: .85rem; color: #64748b; margin-top: 12px; }
  </style>
</head>
<body>
  <h1>Rapport de couverture — PHP Laravel & Vue.js</h1>
  <nav class="nav">
    <a href="php/index.html" target="_blank" class="primary">Ouvrir PHP (Laravel)</a>
    <a href="vue/index.html" target="_blank" class="primary">Ouvrir Vue.js</a>
  </nav>
  <div class="frames">
    <div class="frame-wrap">
      <h2>PHP (Laravel) — app/</h2>
      <iframe src="php/index.html" title="Couverture PHP Laravel"></iframe>
    </div>
    <div class="frame-wrap">
      <h2>Vue.js — resources/js/</h2>
      <iframe src="vue/index.html" title="Couverture Vue.js"></iframe>
    </div>
  </div>
  <p class="note">Généré par <code>npm run coverage</code> — PHP: coverage/php — Vue: coverage/vue</p>
</body>
</html>
`;

if (!existsSync(coverageDir)) {
  mkdirSync(coverageDir, { recursive: true });
}
writeFileSync(outputPath, html, 'utf8');
console.log('Rapport combiné : file://' + outputPath.replace(/\\/g, '/'));
console.log('Ou ouvre : coverage/index.html');
