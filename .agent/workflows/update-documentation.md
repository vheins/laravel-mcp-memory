---
description: Memastikan sinkronisasi total dokumentasi (Overlap Check).
---

> [!IMPORTANT]
> **Skills Delegation**: Use `expert-documentation` for consistency check.

# 1. Trigger Identification
Tentukan pemicu: Perubahan Fitur (Bisnis), API (Teknis), atau Testing (Skenario).

# 2. Sync Execution (Order: Module -> API -> Testing -> Overview)
- **Check All**: Gunakan prinsip "Change One, Check All".
- **Action**: Update file terkait di `.agent/documents/`. Gunakan `replace_file_content` untuk update parsial agar hemat token.

# 3. The Gap Check
Pastikan 3 "Ya":
1. User Story ter-cover di API?
2. Tiap API punya minimal 1 Positif & 1 Negatif test?
3. Overview sudah memuat semua fitur terbaru?

# 4. Commit
`git add .agent/documents/` -> `docs: sync documentation for <module-name>`
