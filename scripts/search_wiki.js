#!/usr/bin/env node
/**
 * scripts/search_wiki.js
 * Fast local search engine for LLM Wiki markdown files.
 * Performs weighted keyword search across titles, frontmatter tags, and content bodies.
 *
 * Usage:
 *   node scripts/search_wiki.js <search-term>
 *   node scripts/wiki.js search <search-term>
 */

const fs = require('fs');
const path = require('path');

const ROOT_DIR = path.resolve(__dirname, '..');
const WIKI_DIR = path.join(ROOT_DIR, 'wiki');

function getMarkdownFiles(dir) {
  let results = [];
  if (!fs.existsSync(dir)) return results;
  const list = fs.readdirSync(dir);
  for (const item of list) {
    const fullPath = path.join(dir, item);
    const stat = fs.statSync(fullPath);
    if (stat.isDirectory()) {
      results = results.concat(getMarkdownFiles(fullPath));
    } else if (item.endsWith('.md')) {
      results.push(fullPath);
    }
  }
  return results;
}

function parseFrontmatter(content) {
  const match = content.match(/^---\s*\r?\n([\s\S]*?)\r?\n---\s*\r?\n/);
  if (!match) return { frontmatter: {}, body: content };
  const raw = match[1];
  const body = content.slice(match[0].length);
  const data = {};
  for (const line of raw.split('\n')) {
    const kv = line.split(':');
    if (kv.length >= 2) {
      const key = kv[0].trim();
      const val = kv.slice(1).join(':').trim().replace(/^['"]|['"]$/g, '');
      data[key] = val;
    }
  }
  return { frontmatter: data, body };
}

function searchWiki(query) {
  if (!query || query.trim().length === 0) {
    console.log("Usage: node scripts/wiki.js search <query>");
    process.exit(1);
  }

  const terms = query.toLowerCase().trim().split(/\s+/);
  console.log(`\n🔍 Searching LLM Wiki for: "${query}"...`);
  console.log(`Directory: ${WIKI_DIR}\n`);

  const files = getMarkdownFiles(WIKI_DIR);
  const matches = [];

  for (const file of files) {
    const relWiki = path.relative(WIKI_DIR, file).replace(/\\/g, '/');
    const content = fs.readFileSync(file, 'utf8');
    const { frontmatter, body } = parseFrontmatter(content);

    const title = frontmatter.title || path.basename(file, '.md');
    const tags = frontmatter.tags || '';

    // Check if any term matches
    let score = 0;
    const snippets = [];
    const lines = content.split('\n');

    for (const term of terms) {
      if (title.toLowerCase().includes(term)) score += 15;
      if (tags.toLowerCase().includes(term)) score += 8;
      if (relWiki.toLowerCase().includes(term)) score += 5;

      // Scan lines for occurrences and build snippet
      for (let i = 0; i < lines.length; i++) {
        const line = lines[i];
        if (line.toLowerCase().includes(term)) {
          score += 2;
          if (snippets.length < 3) {
            const cleanLine = line.trim().replace(/^#+\s*/, '').slice(0, 140);
            snippets.push(`  [Line ${i + 1}]: ${cleanLine}`);
          }
        }
      }
    }

    if (score > 0) {
      matches.push({
        file: relWiki,
        fullPath: file,
        title,
        score,
        snippets
      });
    }
  }

  // Sort descending by score
  matches.sort((a, b) => b.score - a.score);

  if (matches.length === 0) {
    console.log(`❌ No matching pages found for "${query}".\n`);
    return;
  }

  console.log(`Found ${matches.length} matching page(s):\n`);
  matches.forEach((m, idx) => {
    console.log(`[${idx + 1}] 📄 ${m.title} (Score: ${m.score})`);
    console.log(`    Path: wiki/${m.file}`);
    m.snippets.forEach(s => console.log(`  ${s}`));
    console.log('');
  });
}

// Parse args whether invoked directly or via wiki.js
let rawArgs = process.argv.slice(2);
if (rawArgs.length > 0 && rawArgs[0] === 'search') {
  rawArgs = rawArgs.slice(1);
}
searchWiki(rawArgs.join(' '));

module.exports = { searchWiki };
