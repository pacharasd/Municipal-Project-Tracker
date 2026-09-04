# Operations Log (`wiki/log.md`)

Append-only chronological record of all wiki operations: ingests, syntheses, health checks (lints), and structural refactors.

Format for each entry:
`## [YYYY-MM-DD] <operation> | <Title>`
Where `<operation>` is one of `init`, `ingest`, `query`, `synthesis`, `lint`, `refactor`.

---

## [2026-09-04] ingest | LLM Wiki Foundational Manifesto
- Source: `raw/llm-wiki-concept.md`
- Created source summary: [[wiki/sources/llm-wiki-concept|LLM Wiki: A Pattern for Building Personal Knowledge Bases]]
- Created concepts:
  - [[wiki/concepts/persistent-knowledge-bases|Persistent Knowledge Bases]]
  - [[wiki/concepts/rag-vs-llm-wiki|RAG vs LLM Wiki]]
- Created entities:
  - [[wiki/entities/obsidian|Obsidian]]
  - [[wiki/entities/vannevar-bush-memex|Vannevar Bush (Memex)]]
- Created synthesis:
  - [[wiki/syntheses/compounding-knowledge-advantages|Compounding Knowledge Advantages]]
- Updated catalog: [[wiki/index|wiki/index.md]]

## [2026-09-04] init | Dedicated LLM Wiki Knowledge Base Established
- Initialized 3-tier architecture:
  - `raw/`: Read-only source documents and `raw/assets/` for local media.
  - `wiki/`: Persistent markdown knowledge base with `concepts/`, `entities/`, `sources/`, `syntheses/`, and `templates/`.
  - `AGENTS.md` & `GEMINI.md`: Master schema defining Ingest, Query, Lint workflows and Obsidian conventions.
- Configured Obsidian vault settings (`.obsidian/app.json`, `.obsidian/graph.json`) with color-coded nodes and `raw/assets/` attachments.
- Configured CLI toolkit (`scripts/search_wiki.js`, `scripts/lint_wiki.js`, `scripts/wiki.js`) and root `package.json`.
- Established starter templates in `wiki/templates/`.
