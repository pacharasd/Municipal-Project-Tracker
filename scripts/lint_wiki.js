#!/usr/bin/env node
/**
 * scripts/lint_wiki.js
 * LLM Wiki Health & Linter Script
 * Validates:
 * 1. Broken wikilinks ([[Target]] or [[Target|Label]])
 * 2. Orphan pages (pages with 0 inbound links)
 * 3. Unindexed pages (pages missing from wiki/index.md)
 * 4. Basic YAML frontmatter compliance
 * 5. Append-only format in wiki/log.md
 */

const fs = require('fs');
const path = require('path');

const ROOT_DIR = path.resolve(__dirname, '..');
const WIKI_DIR = path.join(ROOT_DIR, 'wiki');
const INDEX_FILE = path.join(WIKI_DIR, 'index.md');
const LOG_FILE = path.join(WIKI_DIR, 'log.md');
const TEMPLATES_DIR = path.join(WIKI_DIR, 'templates');

const WIKILINK_PATTERN = /\[\[([^\]|#]+)(?:#[^\]|]+)?(?:\|[^\]]+)?\]\]/g;
const FRONTMATTER_PATTERN = /^---\s*\r?\n([\s\S]*?)\r?\n---\s*\r?\n/;
const LOG_ENTRY_PATTERN = /^## \[\d{4}-\d{2}-\d{2}\] (?:init|ingest|query|synthesis|lint|refactor) \| .+/;

function getAllFiles(dir, fileList = []) {
  if (!fs.existsSync(dir)) return fileList;
  const files = fs.readdirSync(dir);
  for (const file of files) {
    const filePath = path.join(dir, file);
    const stat = fs.statSync(filePath);
    if (stat.isDirectory()) {
      getAllFiles(filePath, fileList);
    } else if (file.endsWith('.md')) {
      fileList.push(filePath);
    }
  }
  return fileList;
}

function lintWiki() {
  console.log('=== Running LLM Wiki Linter (Node.js) ===');
  console.log(`Wiki Directory: ${WIKI_DIR}`);

  if (!fs.existsSync(WIKI_DIR)) {
    console.error('ERROR: wiki/ directory does not exist.');
    process.exit(1);
  }

  const wikiFiles = getAllFiles(WIKI_DIR);
  console.log(`Total Markdown pages found: ${wikiFiles.length}`);

  const knownTargets = new Set();
  const fileMap = new Map();

  // Add root markdown files (e.g. AGENTS.md, GEMINI.md, README.md)
  const rootFiles = fs.readdirSync(ROOT_DIR).filter(f => f.endsWith('.md'));
  for (const rf of rootFiles) {
    knownTargets.add(rf);
    knownTargets.add(rf.slice(0, -3));
  }

  for (const f of wikiFiles) {
    const relRoot = path.relative(ROOT_DIR, f).replace(/\\/g, '/');
    const relWiki = path.relative(WIKI_DIR, f).replace(/\\/g, '/');
    const stem = path.basename(f, '.md');

    fileMap.set(f, relWiki);

    knownTargets.add(relRoot);
    if (relRoot.endsWith('.md')) knownTargets.add(relRoot.slice(0, -3));
    knownTargets.add(relWiki);
    if (relWiki.endsWith('.md')) knownTargets.add(relWiki.slice(0, -3));
    knownTargets.add(stem);
  }

  const brokenLinks = [];
  const inboundLinks = new Map();
  for (const f of wikiFiles) inboundLinks.set(f, 0);
  const frontmatterIssues = [];

  const indexContent = fs.existsSync(INDEX_FILE) ? fs.readFileSync(INDEX_FILE, 'utf-8') : '';

  for (const filePath of wikiFiles) {
    const isTemplate = filePath.startsWith(TEMPLATES_DIR);
    const content = fs.readFileSync(filePath, 'utf-8');
    const relWiki = path.relative(WIKI_DIR, filePath).replace(/\\/g, '/');

    // Check frontmatter
    if (filePath !== INDEX_FILE && filePath !== LOG_FILE) {
      if (!FRONTMATTER_PATTERN.test(content)) {
        frontmatterIssues.push(`${relWiki}: Missing or malformed YAML frontmatter`);
      }
    }

    // Strip code blocks and inline code before scanning for active wikilinks
    const strippedContent = content
      .replace(/```[\s\S]*?```/g, '')
      .replace(/`[^`\n]+`/g, '');

    // Check wikilinks (templates are allowed to have placeholder links)
    if (!isTemplate) {
      let match;
      const regex = new RegExp(WIKILINK_PATTERN);
      while ((match = regex.exec(strippedContent)) !== null) {
        const cleanTarget = match[1].trim();
        const normalized = cleanTarget.replace(/\\/g, '/');

        const matchesTarget = 
          knownTargets.has(normalized) ||
          knownTargets.has(`${normalized}.md`) ||
          knownTargets.has(`wiki/${normalized}`) ||
          knownTargets.has(`wiki/${normalized}.md`);

        if (matchesTarget) {
          for (const f of wikiFiles) {
            const fRelRoot = path.relative(ROOT_DIR, f).replace(/\\/g, '/');
            const fRelWiki = path.relative(WIKI_DIR, f).replace(/\\/g, '/');
            const fStem = path.basename(f, '.md');
            if ([fRelRoot, fRelRoot.replace(/\.md$/, ''), fRelWiki, fRelWiki.replace(/\.md$/, ''), fStem].includes(normalized)) {
              inboundLinks.set(f, (inboundLinks.get(f) || 0) + 1);
            }
          }
        } else {
          brokenLinks.push({ src: relWiki, target: cleanTarget });
        }
      }
    }
  }

  // Detect orphans (ignore index, log, and templates)
  const orphanPages = [];
  for (const [f, count] of inboundLinks.entries()) {
    const isTemplate = f.startsWith(TEMPLATES_DIR);
    if (f !== INDEX_FILE && f !== LOG_FILE && !isTemplate && count === 0) {
      orphanPages.push(path.relative(WIKI_DIR, f).replace(/\\/g, '/'));
    }
  }

  // Detect unindexed
  const unindexed = [];
  for (const f of wikiFiles) {
    if (f === INDEX_FILE || f === LOG_FILE) continue;
    const stem = path.basename(f, '.md');
    if (!indexContent.includes(stem)) {
      unindexed.push(path.relative(WIKI_DIR, f).replace(/\\/g, '/'));
    }
  }

  // Validate log format
  const logIssues = [];
  if (fs.existsSync(LOG_FILE)) {
    const logLines = fs.readFileSync(LOG_FILE, 'utf-8').split('\n');
    for (let i = 0; i < logLines.length; i++) {
      const line = logLines[i].trim();
      if (line.startsWith('## [')) {
        if (!LOG_ENTRY_PATTERN.test(line)) {
          logIssues.push(`Line ${i + 1}: Malformed log header "${line}". Expected "## [YYYY-MM-DD] <op> | <Title>"`);
        }
      }
    }
  }

  console.log('\n--- Linting Results ---');
  let hasErrors = false;

  if (brokenLinks.length > 0) {
    console.log(`\n[FAIL] Found ${brokenLinks.length} broken wikilink(s):`);
    for (const item of brokenLinks) {
      console.log(`  - In '${item.src}': Link target '[[${item.target}]]' does not exist`);
    }
    hasErrors = true;
  } else {
    console.log('[PASS] All wikilinks resolve successfully.');
  }

  if (frontmatterIssues.length > 0) {
    console.log(`\n[WARN] Frontmatter warnings (${frontmatterIssues.length}):`);
    for (const issue of frontmatterIssues) {
      console.log(`  - ${issue}`);
    }
  } else {
    console.log('[PASS] Frontmatter validation clean.');
  }

  if (orphanPages.length > 0) {
    console.log(`\n[WARN] Found ${orphanPages.length} orphan page(s) with 0 inbound links:`);
    for (const op of orphanPages) {
      console.log(`  - ${op}`);
    }
  } else {
    console.log('[PASS] No orphan pages detected.');
  }

  if (unindexed.length > 0) {
    console.log(`\n[INFO] Pages not directly mentioned in wiki/index.md (${unindexed.length}):`);
    for (const u of unindexed) {
      console.log(`  - ${u}`);
    }
  } else {
    console.log('[PASS] All pages indexed in wiki/index.md.');
  }

  if (logIssues.length > 0) {
    console.log(`\n[WARN] Log header issues (${logIssues.length}):`);
    for (const li of logIssues) {
      console.log(`  - ${li}`);
    }
  } else {
    console.log('[PASS] Operations log format verified.');
  }

  console.log('\n===============================');
  if (hasErrors) {
    console.log('Linter completed with errors.');
    process.exit(1);
  } else {
    console.log('Wiki is healthy and consistent!');
    process.exit(0);
  }
}

lintWiki();

module.exports = { lintWiki };
