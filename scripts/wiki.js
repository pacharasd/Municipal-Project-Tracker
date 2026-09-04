#!/usr/bin/env node
/**
 * scripts/wiki.js
 * Unified CLI Dispatcher for LLM Wiki Operations.
 *
 * Commands:
 *   node scripts/wiki.js lint             # Health check wikilinks, frontmatter, orphans
 *   node scripts/wiki.js search <term>    # Search wiki pages with ranked relevance
 *   node scripts/wiki.js status           # View wiki statistics & recent activity log
 *   node scripts/wiki.js log <op> <title> # Append a structured entry to wiki/log.md
 */

const fs = require('fs');
const path = require('path');

const ROOT_DIR = path.resolve(__dirname, '..');
const WIKI_DIR = path.join(ROOT_DIR, 'wiki');
const LOG_FILE = path.join(WIKI_DIR, 'log.md');
const RAW_DIR = path.join(ROOT_DIR, 'raw');

const command = process.argv[2];
const args = process.argv.slice(3);

function printHelp() {
  console.log(`
📚 LLM Wiki - Command Line Toolkit
==================================
Usage:
  node scripts/wiki.js <command> [options]
  npm run <command> [options]

Commands:
  lint              Run integrity linter on all wiki markdown files
  search <query>    Fast full-text & keyword search across wiki pages
  status            Show wiki statistics, category page counts, and recent logs
  log <op> <title>  Append a new operations log entry (op: ingest|query|synthesis|lint|refactor)
  help              Display this help message

Examples:
  node scripts/wiki.js search "knowledge"
  node scripts/wiki.js lint
  node scripts/wiki.js status
  node scripts/wiki.js log ingest "Personal PKM Architecture"
`);
}

function showStatus() {
  console.log('\n📊 === LLM Wiki Status Dashboard ===');
  console.log(`Workspace: ${ROOT_DIR}`);
  console.log(`Wiki Path: ${WIKI_DIR}\n`);

  if (!fs.existsSync(WIKI_DIR)) {
    console.error('ERROR: wiki/ directory does not exist.');
    return;
  }

  const categories = ['concepts', 'entities', 'sources', 'syntheses', 'templates'];
  let totalWikiPages = 0;

  console.log('📂 Page Count by Category:');
  for (const cat of categories) {
    const catDir = path.join(WIKI_DIR, cat);
    let count = 0;
    if (fs.existsSync(catDir)) {
      count = fs.readdirSync(catDir).filter(f => f.endsWith('.md')).length;
    }
    totalWikiPages += count;
    console.log(`  - wiki/${cat.padEnd(12)} : ${count} page(s)`);
  }

  // Count raw files
  let rawCount = 0;
  if (fs.existsSync(RAW_DIR)) {
    rawCount = fs.readdirSync(RAW_DIR).filter(f => f.endsWith('.md') || f.endsWith('.txt') || f.endsWith('.pdf')).length;
  }
  console.log(`  - raw/ (Sources)     : ${rawCount} raw document(s)`);
  console.log(`  - Total Wiki Pages   : ${totalWikiPages + 2} (including index.md & log.md)\n`);

  // Recent 5 log entries
  if (fs.existsSync(LOG_FILE)) {
    const logContent = fs.readFileSync(LOG_FILE, 'utf8');
    const lines = logContent.split('\n');
    const logHeaders = lines.filter(l => l.trim().startsWith('## ['));
    console.log('🕒 Recent Operations Log Entries:');
    const recent = logHeaders.slice(0, 5);
    if (recent.length === 0) {
      console.log('  (No entries recorded yet)');
    } else {
      recent.forEach(entry => console.log(`  ${entry.trim()}`));
    }
  }
  console.log('\n====================================\n');
}

function appendLog(op, title) {
  if (!op || !title) {
    console.log('Usage: node scripts/wiki.js log <operation> <title>');
    console.log('Operations: init | ingest | query | synthesis | lint | refactor');
    return;
  }

  const validOps = ['init', 'ingest', 'query', 'synthesis', 'lint', 'refactor'];
  if (!validOps.includes(op.toLowerCase())) {
    console.warn(`[WARN] "${op}" is not standard. Recommended: ${validOps.join(', ')}`);
  }

  const today = new Date().toISOString().slice(0, 10);
  const header = `## [${today}] ${op.toLowerCase()} | ${title}\n`;
  const entryBody = `- Automated log entry created on ${today}.\n\n`;

  if (!fs.existsSync(LOG_FILE)) {
    fs.writeFileSync(LOG_FILE, `# Operations Log (wiki/log.md)\n\n${header}${entryBody}`, 'utf8');
  } else {
    const content = fs.readFileSync(LOG_FILE, 'utf8');
    const splitPoint = content.indexOf('---\n');
    if (splitPoint !== -1) {
      const before = content.slice(0, splitPoint + 4);
      const after = content.slice(splitPoint + 4);
      fs.writeFileSync(LOG_FILE, `${before}\n${header}${entryBody}${after}`, 'utf8');
    } else {
      fs.appendFileSync(LOG_FILE, `\n${header}${entryBody}`, 'utf8');
    }
  }

  console.log(`✅ Appended log entry to wiki/log.md: "${header.trim()}"`);
}

switch (command) {
  case 'lint':
    require('./lint_wiki.js');
    break;

  case 'search':
    require('./search_wiki.js');
    break;

  case 'status':
    showStatus();
    break;

  case 'log':
    appendLog(args[0], args.slice(1).join(' '));
    break;

  case 'help':
  case '--help':
  case '-h':
  default:
    printHelp();
    break;
}
