# Raw Sources Directory (`raw/`)

This directory is **Layer 1** of the LLM Wiki architecture: the human-curated collection of source documents.

---

## 🔒 Operating Rules for LLM Agents:
1. **Read-Only**: The agent reads from files in `raw/` to extract knowledge, cross-reference concepts, and verify facts. It **NEVER modifies, edits, or deletes** any raw source file.
2. **Source of Truth**: When facts or claims conflict across the knowledge base, raw sources serve as the immutable ground truth.

---

## 📁 Directory Structure:
- `raw/`: Place articles, PDF summaries, transcripts, clipped web pages, research papers, and notes here (`.md`, `.txt`).
- `raw/assets/`: Store downloaded images, diagrams, screenshots, and media attachments associated with clipped sources.

---

## 📥 How to Ingest a Source:
Drop a document into `raw/` and instruct your agent:
> *"/ingest raw/<filename>"* หรือ *"ประมวลผลเอกสาร raw/<filename> เข้าสู่วิกิ"*
