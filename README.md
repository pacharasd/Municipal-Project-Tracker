# LLM Wiki: A Compounding Personal Knowledge Base

> **"Obsidian is the IDE; the LLM is the programmer; the wiki is the codebase."**

This repository implements the **LLM Wiki Pattern**: a persistent, compounding, interlinked markdown knowledge base that sits between you and raw source materials. Instead of rediscovering knowledge from scratch on every query like traditional RAG systems, the LLM continuously maintains and enriches a structured personal wiki.

---

## 🏛️ Architecture (The Three Layers)

```text
├── AGENTS.md                  # Layer 3: Master Schema & Operating Conventions
├── GEMINI.md                  # Agent directives (Knowledge Engineer role)
├── README.md                  # This documentation
├── package.json               # CLI operations scripts
│
├── .obsidian/                 # Obsidian Vault Configuration
│   ├── app.json               # Auto-attachments -> raw/assets/, Wikilinks enabled
│   └── graph.json             # Color-coded interactive knowledge graph view
│
├── raw/                       # Layer 1: Immutable Sources (Source of Truth)
│   ├── README.md              # Instructions for raw materials
│   ├── assets/                # Downloaded images, charts, and media attachments
│   └── llm-wiki-concept.md    # Foundational manifesto raw document
│
├── wiki/                      # Layer 2: LLM-Maintained Knowledge Base
│   ├── index.md               # Content catalog, organized by topic
│   ├── log.md                 # Append-only chronological operations log
│   ├── concepts/              # Core domain concepts & methodologies
│   ├── entities/              # Persons, organizations, tools, and platforms
│   ├── sources/               # Extracted summaries and key takeaways
│   ├── syntheses/             # Deep-dive analyses & compound query answers
│   └── templates/             # Standard schemas for new wiki notes
│
└── scripts/                   # Fast Zero-Dependency CLI Toolkit
    ├── lint_wiki.js           # Integrity linter (wikilinks, orphans, frontmatter)
    ├── search_wiki.js         # Weighted search engine with line snippets
    └── wiki.js                # Unified CLI runner (search, lint, status, log)
```

---

## 🚀 How to Use with Obsidian

1. **Open as Vault**:
   - Launch [Obsidian](https://obsidian.md/).
   - Click **"Open folder as vault"** and select this directory (`Municipal_Project_Tracker` or your renamed root folder).
2. **Graph View**:
   - Open **Graph View** (`Ctrl+G`).
   - The knowledge network is already color-coded:
     - 🔵 **Sky Blue**: Concepts (`path:wiki/concepts`)
     - 🟢 **Emerald**: Entities & Tools (`path:wiki/entities`)
     - 🟡 **Amber**: Ingested Sources (`path:wiki/sources`)
     - 🟣 **Magenta**: Syntheses & Analyses (`path:wiki/syntheses`)
3. **Clipped Articles & Images**:
   - Use the **Obsidian Web Clipper** extension to clip articles directly into `raw/`.
   - Downloaded images automatically route to `raw/assets/`.

---

## 🤖 How to Operate the Wiki with Your Agent

### 1. Ingestion (`/ingest <raw/file>`)
Drop any article, note, transcript, or paper into `raw/`, then instruct your agent:
> *"ช่วยประมวลผลเอกสาร raw/my-article.md เข้าสู่วิกิ"* หรือ *"/ingest raw/my-article.md"*

The agent will:
- Read and extract key takeaways, data, and direct quotes.
- Create a structured note in `wiki/sources/`.
- Cross-link and enrich 5–15 related concept and entity pages in `wiki/`.
- Explicitly flag any contradictions with existing knowledge.
- Update `wiki/index.md` and append a timestamped entry to `wiki/log.md`.

### 2. Querying & Compounding Knowledge (`/query <prompt>`)
Ask research or exploratory questions against your knowledge base:
> *"เปรียบเทียบข้อแตกต่างระหว่าง Traditional RAG กับ LLM Wiki ในระยะยาว"* หรือ *"/query <คำถาม>"*

The agent will:
- Search `wiki/index.md` and related notes.
- Synthesize an answer with exact `[[wikilinks]]` citations.
- **File valuable answers back into `wiki/syntheses/`** so your personal knowledge compounds.

### 3. Health Check (`/lint`)
Periodically run or instruct:
> *"/lint"* หรือ *"ตรวจเช็คความสมบูรณ์ของวิกิ"*

The agent verifies link integrity, detects orphan pages, and suggests missing topics.

---

## ⚡ CLI Toolkit Commands

You can run operations directly from the terminal:

```bash
# Check wiki statistics & recent logs
npm run status
# or: node scripts/wiki.js status

# Search wiki pages with weighted relevance scoring
npm run search -- "memex"
# or: node scripts/wiki.js search "memex"

# Run integrity linter (0 broken links, 0 orphans)
npm run lint
# or: node scripts/wiki.js lint
```

---

## 💡 Recommended Obsidian Plugins
- **Dataview**: Queries page frontmatter tags to generate dynamic tables.
- **Marp**: Renders markdown presentation slides directly from notes.
- **Obsidian Web Clipper**: One-click clipping of web articles to markdown.
