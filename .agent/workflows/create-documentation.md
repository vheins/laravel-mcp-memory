---
description: create documentation
---

> [!IMPORTANT]
> **Skills Delegation**: Use `expert-documentation` skill for standards (JSON:API, Mermaid, No-Gap).

# 1. Analisis & Riset (Fast)
- **Modul**: Identifikasi Nama & Konteks (Global vs Spesifik).
- **Riset**: Gunakan `grep_search` atau `find_by_name` hanya pada folder relevan.
- **Context Loading**: Gunakan range-based reading (`view_file` with `StartLine`/`EndLine`) untuk file besar.

# 2. Perencanaan
- Susun rencana via scratchpad/memori: Fitur Utama, Endpoints, Testing Scenarios, dan Task Breakdown.

# 3. Execution (Template-Based)
Isi template di `.agent/documents/templates/`. Jangan menulis dari nol.
- **Feature Doc**: User Stories + ERD.
- **API Spec**: JSON:API contract (Request/Response examples).
- **Testing Scenarios**: Positif, Negatif, Security, Monkey.
- **Module Overview**: Index utama + links.

# 4. Finalisasi
- Review link antar dokumen (Cross-linking).
- Beritahu user saat siap direview.