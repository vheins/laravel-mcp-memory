---
description: generate test scenarios
---

> [!IMPORTANT]
> **Skills Delegation**: Use `qa-design` for scenario generation patterns.

# 1. Context Collection
- Baca Overview, Feature Doc, dan API Spec module target.
- Gunakan range-based reading untuk memahami aturan bisnis kritis.

# 2. Scenarios Generation
Gunakan template `.agent/documents/templates/testing-feature.md` & `testing-overview.md`.
- **Feature Testing**: Positif (Happy Path), Negatif (Validation), Monkey (Chaos), Security (Privilege).
- **Index**: Update `test-<module>.md` dengan link ke file fitur.

# 3. Finalisasi
- Pastikan semua User Story ter-cover.
- Verifikasi semua link markdown berfungsi.
