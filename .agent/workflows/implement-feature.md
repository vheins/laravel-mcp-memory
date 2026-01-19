---
description: Workflow standar untuk mengimplementasikan kode dari Task, Feature, atau Module.
---

> [!IMPORTANT]
> **Skills Delegation**: Use `feature-implementation` (TDD) and `tech-stacks` (Architecture).

# 1. Identifikasi & Context
- Ambil tugas dari `.agent/tasks/implementation-tasks.md`.
- Baca Feature Docs & API Specs menggunakan range-based reading (`view_file`).

# 2. Iterasi TDD (Per Task)
Ikuti workflow `feature-implementation` skill:
- **Red**: Create/Update test -> Pastikan gagal.
- **Green**: Implementasi (Migration -> Model -> Logic -> API -> UI). Pastikan naming sama persis dengan doc.
- **Verification**: Jalankan test suite -> Passed.

# 3. Finalisasi
- Jalankan linting/formatting.
- Jalankan workflow `/update-task` untuk sinkronisasi status.
- Commit: `feat(<module>): <description> (<TASK-ID>)`.
