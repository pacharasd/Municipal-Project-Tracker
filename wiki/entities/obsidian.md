---
title: "Obsidian"
type: "entity"
status: "active"
created: "2026-09-04"
updated: "2026-09-04"
tags:
  - entity
  - tool
  - markdown-ide
sources:
  - "[[wiki/sources/llm-wiki-concept|LLM Wiki Concept]]"
---

# Obsidian

> **Type**: Markdown IDE / PKM Platform | **Status**: Active | **Last Updated**: 2026-09-04

---

## 🏢 Profile & Role in LLM Wiki
In the LLM Wiki architecture, **Obsidian serves as the IDE** while the LLM acts as the programmer and the wiki acts as the codebase. Because Obsidian operates purely on a local folder of plaintext markdown files, the user and the agent can read and write simultaneously without file locking or database overhead.

---

## 🔑 Key Features for LLM Wiki Workflows
1. **Interactive Graph View**: Visualizes the entire knowledge network in real time, exposing cluster hubs, bridge notes, and isolated orphan pages.
2. **Standard Wikilinks (`[[Page Name]]`)**: Enables bi-directional linking and seamless traversal between entities, concepts, and sources.
3. **Obsidian Web Clipper**: Browser extension allowing instant clipping of web articles into markdown in `raw/`.
4. **Local Attachments**: Configured via `.obsidian/app.json` to store images and figures in `raw/assets/`.
5. **Community Plugins**:
   - **Dataview**: Generates dynamic tables and queries based on YAML frontmatter.
   - **Marp**: Converts markdown notes directly into presentation slide decks.

---

## 🔗 Connected Concepts & Sources
- **Framework**: [[wiki/concepts/persistent-knowledge-bases|Persistent Knowledge Bases]]
- **Architectural Comparison**: [[wiki/concepts/rag-vs-llm-wiki|RAG vs LLM Wiki]]
- **Canonical Source**: [[wiki/sources/llm-wiki-concept|LLM Wiki Concept]]
