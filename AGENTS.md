# AGENTS.md - LLM Wiki Operating Guidelines & Master Schema

This repository implements the **LLM Wiki Pattern**: a persistent, compounding, interlinked markdown knowledge base that sits between the user and raw source materials.

In this architecture:
- **Obsidian / Markdown Viewer** is the IDE.
- **The LLM Agent (You)** is the knowledge engineer, programmer, and maintainer.
- **The Wiki (`wiki/`)** is the persistent codebase.
- **The User** is the curator, researcher, and director.

---

## 1. The Three Layers

1. **`raw/` (Raw Sources - Read-Only / Immutable)**:
   - Contains source documents curated by the user (articles, papers, transcripts, notes, data files, clipped web pages).
   - `raw/assets/`: Downloaded images, figures, charts, and media attachments associated with clipped sources.
   - **Rule**: The agent reads from `raw/` as the primary source of truth, but **NEVER edits, alters, or deletes** files in `raw/`.

2. **`wiki/` (The Wiki - Agent-Maintained Knowledge Base)**:
   - Contains interlinked markdown files organized into:
     - `wiki/index.md`: Content catalog categorized by topic/type.
     - `wiki/log.md`: Chronological append-only operations log.
     - `wiki/concepts/`: Core domain concepts, theoretical frameworks, and methodologies.
     - `wiki/entities/`: Key persons, organizations, platforms, tools, and systems.
     - `wiki/sources/`: Structured summaries, key takeaways, and direct citations from raw sources.
     - `wiki/syntheses/`: Compound analyses, comparisons, and answers filed back into the wiki.
     - `wiki/templates/`: Standard schemas for creating new pages.
   - **Rule**: The agent owns this layer entirely — creating pages, cross-referencing with `[[wikilinks]]`, updating existing notes, and flagging contradictions.

3. **`AGENTS.md` / `GEMINI.md` (The Schema)**:
   - Governs the conventions, schemas, and workflows for wiki maintenance.

---

## 2. Core Operations

### A. Ingestion (`/ingest <raw/filename>` or "ประมวลผลเอกสาร")
When the user adds a new document to `raw/` and asks to process it:
1. **Read & Extract**:
   - Read the raw file thoroughly.
   - Extract core arguments, key facts, referenced entities, domain concepts, dates, numbers, and notable direct quotes.
2. **Create Source Summary**:
   - Create `wiki/sources/<slugified-name>.md` following `wiki/templates/template-source.md`.
   - Record YAML frontmatter: `title`, `source_file`, `date_ingested`, `tags`, `entities`, `concepts`.
   - Include Executive Summary, Key Takeaways, Extracted Data, Linked Concepts/Entities, and Direct Quotes.
3. **Cross-Link & Compound Knowledge**:
   - Check existing pages in `wiki/concepts/`, `wiki/entities/`, and `wiki/syntheses/`.
   - Update related existing pages with new findings using `[[Page Name|Display Label]]` wikilinks. A single source should enrich multiple wiki pages where relevant.
   - If a referenced entity or concept is important but doesn't exist yet, create a new page for it using its corresponding template.
   - **Contradiction Flagging**: If the new source contradicts previously ingested information, explicitly record the discrepancy on the relevant wiki pages using an alert block:
     ```markdown
     > [!WARNING] Contradiction noted on YYYY-MM-DD
     > Source [[wiki/sources/new-source|New Source]] claims X, whereas [[wiki/sources/old-source|Old Source]] previously asserted Y.
     ```
4. **Update Catalog (`wiki/index.md`)**:
   - Add the new source and any new concepts/entities to `wiki/index.md` with a one-line summary.
5. **Append to Log (`wiki/log.md`)**:
   - Append a formatted entry:
     ```markdown
     ## [YYYY-MM-DD] ingest | <Title or Source Name>
     - Source: `raw/<filename>`
     - Created: [[wiki/sources/<slugified-name>|Summary]]
     - Updated pages: [[wiki/...]], [[wiki/...]]
     - Key takeaways: <1-2 bullet points>
     ```

### B. Query & Synthesis (`/query <question>` or "สืบค้นข้อมูล")
When the user asks questions against the knowledge base:
1. **Index-First Search**:
   - Read `wiki/index.md` or execute `node scripts/wiki.js search "<term>"` to locate relevant concept, entity, and source pages.
   - Read the target markdown pages in `wiki/`.
2. **Synthesize with Citations**:
   - Provide a comprehensive, structured response citing specific wiki pages (e.g. `[[wiki/concepts/example|ชื่อมโนทัศน์]]`) and raw sources.
3. **Compound Back into Wiki**:
   - If the query produces a novel comparison, strategic analysis, or deep dive that adds lasting value, **file it back into `wiki/syntheses/<slugified-title>.md`** using `wiki/templates/template-synthesis.md`.
   - Update `wiki/index.md` and append a `synthesis` entry to `wiki/log.md`.

### C. Lint & Health Check (`/lint` or "ตรวจสอบความสมบูรณ์")
Periodically audit the health of the wiki:
1. Run `node scripts/wiki.js lint` to check:
   - All `[[wikilinks]]` resolve to existing files.
   - No orphan pages exist in `wiki/` (0 inbound links).
   - All pages are cataloged in `wiki/index.md`.
   - YAML frontmatter is valid.
   - Operations log entries follow standard format.
2. Note and fix any discrepancies.

### D. Status Dashboard (`/status`)
- Run `node scripts/wiki.js status` to inspect total counts, categories, and recent log history.

---

## 3. Formatting & Linking Conventions

### Frontmatter Schema
Every content page in `wiki/` must start with YAML frontmatter:
```yaml
---
title: "Page Title"
type: "concept" # concept | entity | source | synthesis
status: "active" # active | completed | planned | draft
created: "YYYY-MM-DD"
updated: "YYYY-MM-DD"
tags:
  - knowledge-base
  - domain-tag
sources:
  - "[[wiki/sources/example-source|Example Source]]"
---
```

### Wikilinks
- Use Obsidian-standard relative or path wikilinks: `[[Target Page]]` or `[[wiki/subfolder/target-page|Display Label]]`.
- Every major entity and concept mentioned in prose must be hyperlinked so Obsidian's Graph View accurately visualizes the knowledge graph.

### Log Header Format
All log entries in `wiki/log.md` must match:
`## [YYYY-MM-DD] <operation> | <Description>`
Where `<operation>` is one of: `init`, `ingest`, `query`, `synthesis`, `lint`, `refactor`.
