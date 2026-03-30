#!/usr/bin/env node
/**
 * Memory bank — API Laravel Woodkraft.
 * Usage : depuis woodkraft-backend/ — node .cursor/memory-bank/update-memory-bank.mjs
 */
import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const ROOT = path.resolve(__dirname, '../..')
const OUT = path.join(__dirname, 'PROJECT.md')

const MANUAL_START = '<!-- MEMORY_BANK_MANUAL_START -->'
const MANUAL_END = '<!-- MEMORY_BANK_MANUAL_END -->'

function readJson(p) {
  try {
    return JSON.parse(fs.readFileSync(p, 'utf8'))
  } catch {
    return null
  }
}

function countByExt(dir, ext, acc = { n: 0 }) {
  if (!fs.existsSync(dir)) return acc.n
  const entries = fs.readdirSync(dir, { withFileTypes: true })
  for (const e of entries) {
    const full = path.join(dir, e.name)
    if (e.isDirectory()) {
      if (e.name === 'node_modules' || e.name === 'vendor' || e.name === 'dist') continue
      countByExt(full, ext, acc)
    } else if (e.isFile() && e.name.endsWith(ext)) acc.n++
  }
  return acc.n
}

function phpVersionHint(composerLockPath) {
  if (!fs.existsSync(composerLockPath)) return null
  try {
    const lock = JSON.parse(fs.readFileSync(composerLockPath, 'utf8'))
    return lock.platform?.php ?? null
  } catch {
    return null
  }
}

function extractApiRouteLines(apiPhpPath) {
  if (!fs.existsSync(apiPhpPath)) return []
  const src = fs.readFileSync(apiPhpPath, 'utf8')
  return src
    .split('\n')
    .map((l) => l.trim())
    .filter((l) => l.startsWith('Route::') && !l.startsWith('//'))
}

function buildDoc() {
  const composer = readJson(path.join(ROOT, 'composer.json'))
  const npmPkg = readJson(path.join(ROOT, 'package.json'))
  const phpLock = phpVersionHint(path.join(ROOT, 'composer.lock'))

  const phpApp = countByExt(path.join(ROOT, 'app'), '.php')
  const migrations = countByExt(path.join(ROOT, 'database/migrations'), '.php')
  const controllers = countByExt(path.join(ROOT, 'app/Http/Controllers'), '.php')

  const req = composer?.require ?? {}
  const beKeys = [
    'php',
    'laravel/framework',
    'intervention/image-laravel',
    'darkaonline/l5-swagger',
  ]
  const stackLines = beKeys
    .filter((k) => req[k])
    .map((k) => `- ${k.replace(/^.*\//, '')} ${req[k]}`)

  const tailwind = npmPkg?.devDependencies?.tailwindcss
  const vite = npmPkg?.devDependencies?.vite

  const routeSnippets = extractApiRouteLines(path.join(ROOT, 'routes/api.php'))
  const generatedAt = new Date().toISOString()

  const stackBlock = [
    phpLock ? `- **PHP (composer.lock)** : ${phpLock}` : null,
    stackLines.length ? stackLines.join('\n') : '- _(composer.json introuvable)_',
    tailwind ? `- tailwindcss (npm) ${tailwind}` : null,
    vite ? `- vite (npm) ${vite}` : null,
  ]
    .filter(Boolean)
    .join('\n')

  const routesBlock = routeSnippets.length
    ? routeSnippets.map((l) => `- \`${l}\``).join('\n')
    : '- _(aucune ligne Route:: trouvée)_'

  const lines = [
    '# Memory bank — Woodkraft (API Laravel)',
    '',
    '> Section auto entre `MEMORY_BANK_AUTO_START` et `MEMORY_BANK_AUTO_END`. Zone manuelle préservée.',
    '',
    MANUAL_START,
    '',
    '_Décisions API, auth, règles métier, URLs de déploiement, contrats avec le front, etc._',
    '',
    MANUAL_END,
    '',
    '<!-- MEMORY_BANK_AUTO_START -->',
    '## Métadonnées',
    '',
    `- **Dernière génération** : ${generatedAt}`,
    `- **Racine app** : \`${ROOT}\` (\`woodkraft-backend\`)`,
    '',
    '## Stack (extraits)',
    '',
    stackBlock,
    '',
    '## Préfixe API',
    '',
    '- Fichier principal : `routes/api.php` — groupe `prefix(\'v1\')` → URLs de type `/api/v1/...`',
    '',
    '## Inventaire',
    '',
    `- Fichiers PHP dans \`app/\` : **${phpApp}**`,
    `- Contrôleurs (\`app/Http/Controllers\`) : **${controllers}**`,
    `- Migrations : **${migrations}**`,
    '',
    '## Lignes de routes `routes/api.php` (aperçu)',
    '',
    routesBlock,
    '',
    '<!-- MEMORY_BANK_AUTO_END -->',
    '',
  ]

  return lines.join('\n')
}

function mergeManual(existing, autoDoc) {
  const hasManual =
    existing.includes(MANUAL_START) && existing.includes(MANUAL_END)
  if (!hasManual) return autoDoc

  const manualBlock = existing.slice(
    existing.indexOf(MANUAL_START),
    existing.indexOf(MANUAL_END) + MANUAL_END.length
  )
  return autoDoc.replace(
    new RegExp(
      `${escapeRegex(MANUAL_START)}[\\s\\S]*?${escapeRegex(MANUAL_END)}`,
      'm'
    ),
    manualBlock
  )
}

function escapeRegex(s) {
  return s.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
}

const auto = buildDoc()
let finalDoc = auto
if (fs.existsSync(OUT)) {
  finalDoc = mergeManual(fs.readFileSync(OUT, 'utf8'), auto)
}
fs.writeFileSync(OUT, finalDoc, 'utf8')
console.log(`Memory bank (backend) mise à jour : ${OUT}`)
