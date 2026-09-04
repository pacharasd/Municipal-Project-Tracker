---
title: "LLM Wiki: A Pattern for Building Personal Knowledge Bases Using LLMs"
source_file: "raw/llm-wiki-concept.md"
date_ingested: "2026-09-04"
type: "source"
status: "active"
tags:
  - source
  - pkm
  - knowledge-base
  - llm-architecture
entities:
  - "[[wiki/entities/obsidian|Obsidian]]"
  - "[[wiki/entities/vannevar-bush-memex|Vannevar Bush (Memex)]]"
concepts:
  - "[[wiki/concepts/persistent-knowledge-bases|Persistent Knowledge Bases]]"
  - "[[wiki/concepts/rag-vs-llm-wiki|RAG vs LLM Wiki]]"
---

# LLM Wiki: A Pattern for Building Personal Knowledge Bases Using LLMs

> **Raw Source**: `raw/llm-wiki-concept.md` | **Ingested**: 2026-09-04 | **Type**: Canonical Framework Document

---

## 📌 Executive Summary
This document proposes a paradigm shift in how humans collaborate with Large Language Models (LLMs) to build personal and organizational knowledge bases. Rather than treating LLMs as transient query-time retrieval engines (typical Retrieval-Augmented Generation / RAG), the LLM Wiki pattern employs the model as an active, continuous maintainer of a persistent, compounding markdown wiki. In this model, Obsidian acts as the IDE, the LLM acts as the programmer, and the interlinked markdown repository acts as the codebase.

---

## 🔑 Key Takeaways & Principles
1. **The Compounding Artifact**: Traditional RAG systems rediscover knowledge from scratch on every query without accumulating insight. The LLM Wiki compiles knowledge once, connects cross-references, flags contradictions, and continually enriches existing synthesis.
2. **Division of Labor**: Humans curate sources, guide exploration, and formulate high-level inquiries; the LLM handles all tedious bookkeeping, filing, cross-referencing, and summarization.
3. **The Three Layers**:
   - `raw/`: Immutable source documents (ground truth).
   - `wiki/`: LLM-maintained interlinked knowledge base.
   - Schema (`AGENTS.md` / `GEMINI.md`): Operating instructions that define conventions and workflows.
4. **Three Core Operations**:
   - **Ingest**: Extract, summarize, cross-link 5–15 related pages, update index, append to log.
   - **Query**: Search index first, synthesize answers with citations, and compound valuable findings back into `wiki/syntheses/`.
   - **Lint**: Audit link health, orphan pages, contradictions, and knowledge gaps.

---

## 📊 Core Architectural Comparison
| Parameter | Traditional RAG | LLM Wiki Pattern |
| :--- | :--- | :--- |
| **Storage Structure** | Vector DB / Unlinked Chunks | Interlinked Markdown Files (`wiki/`) |
| **Knowledge Accumulation** | None (ephemeral query synthesis) | Compounding (persistent knowledge graph) |
| **Cross-Referencing** | Discovered per query | Pre-compiled via `[[wikilinks]]` |
| **Contradiction Detection** | Silent overwrite or confusion | Explicitly flagged and tracked |
| **Human Interface** | Chat box | Obsidian Graph View & Markdown IDE |

---

## 🔗 Cross-Referenced Entities & Concepts
- **Core Methodology**: [[wiki/concepts/persistent-knowledge-bases|Persistent Knowledge Bases]]
- **Architectural Analysis**: [[wiki/concepts/rag-vs-llm-wiki|RAG vs LLM Wiki]]
- **Tools & Ecosystem**: [[wiki/entities/obsidian|Obsidian]]
- **Historical Heritage**: [[wiki/entities/vannevar-bush-memex|Vannevar Bush (Memex)]]
- **Synthesis**: [[wiki/syntheses/compounding-knowledge-advantages|Compounding Knowledge Advantages]]

---

## 💬 Notable Direct Quotes
> *"The wiki is a persistent, compounding artifact. The cross-references are already there. The contradictions have already been flagged. The synthesis already reflects everything you've read."*
> — *The Core Idea*

> *"Obsidian is the IDE; the LLM is the programmer; the wiki is the codebase."*
> — *The Core Idea*
