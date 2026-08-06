---
name: create-documentation
description: Generate comprehensive project documentation in docs/ folder. Covers dev-docs (architecture diagrams, API specs, feature explanations, decision records) and user-docs (end-user guides). Use when creating documentation, writing feature docs, explaining system design, recording architectural decisions, writing user guides, or when the user mentions docs, documentation, ADR, user guide, or API docs.
---

# Create Documentation

## Overview

Generate two categories of documentation stored in `docs/` with clear folder structure:

```
docs/
├── Readme.md                    # Index of all documentation
├── architecture.md              # Existing - system architecture
├── dev-docs/
│   ├── README.md                # Dev docs index
│   ├── features/
│   │   └── [feature-name].md   # Per-feature technical docs
│   ├── api/
│   │   └── [endpoint-group].md # API endpoint documentation
│   └── decisions/
│       └── [NNN]-[title].md    # Architecture Decision Records
└── user-docs/
    ├── README.md                # User docs index
    └── guides/
        └── [feature-name].md   # Per-feature user guide
```

---

## Dev Docs

### Feature Documentation (`docs/dev-docs/features/[feature-name].md`)

Each feature doc MUST include:

```markdown
# [Feature Name]

## Overview
One-paragraph summary of what this feature does and why it exists.

## Architecture Diagram

\`\`\`mermaid
flowchart TD
    A[User Action] --> B[Controller]
    B --> C[Service/Model]
    C --> D[Database]
\`\`\`

## Data Model

\`\`\`mermaid
erDiagram
    ORDER ||--o{ ORDER_ITEM : contains
    ORDER_ITEM }o--|| MENU_ITEM : references
\`\`\`

## Key Files

| Layer | File | Purpose |
|-------|------|---------|
| Controller | `app/Http/Controllers/XController.php` | Handles requests |
| Model | `app/Models/X.php` | Eloquent model |
| Livewire Component | `app/Livewire/X/DataX.php` | Logic & state |
| Blade View | `resources/views/livewire/x/data-x.blade.php` | Tampilan halaman |

## Flow Explanation

Step-by-step explanation of how data flows through the system for this feature.

1. **User triggers** — describe the UI action
2. **Request handling** — controller + validation
3. **Business logic** — what happens with the data
4. **Response** — what gets returned/rendered

## API Endpoints (if applicable)

| Method | URI | Purpose | Auth |
|--------|-----|---------|------|
| GET | `/orders` | List orders | sanctum |
| POST | `/orders` | Create order | sanctum |

## Decisions & Trade-offs

Document WHY certain approaches were chosen:
- Why X library over Y?
- Why this data structure?
- What constraints influenced the design?

## Related

- Links to related features, ADRs, or external resources
```

### API Documentation (`docs/dev-docs/api/[endpoint-group].md`)

```markdown
# [Endpoint Group] API

## Base URL
`/api/v1/[resource]`

## Authentication
Bearer token via Laravel Sanctum

## Endpoints

### GET /api/v1/[resource]

**Description:** Brief description

**Query Parameters:**

| Param | Type | Required | Default | Description |
|-------|------|----------|---------|-------------|
| page | int | No | 1 | Page number |

**Response 200:**

\`\`\`json
{
  "data": [...],
  "meta": { "current_page": 1, "total": 50 }
}
\`\`\`

**Error Responses:**

| Code | Description |
|------|-------------|
| 401 | Unauthenticated |
| 403 | Unauthorized |
```

### Architecture Decision Records (`docs/dev-docs/decisions/[NNN]-[title].md`)

Number sequentially (001, 002, etc.). Format:

```markdown
# ADR-[NNN]: [Title]

**Date:** YYYY-MM-DD
**Status:** accepted | superseded | deprecated
**Supersedes:** ADR-XXX (if applicable)

## Context
What is the issue? What forces are at play?

## Decision
What is the change we're making?

## Consequences

### Positive
- Benefit 1
- Benefit 2

### Negative
- Trade-off 1
- Trade-off 2

### Neutral
- Side effect that is neither good nor bad
```

---

## User Docs

### User Guide (`docs/user-docs/guides/[feature-name].md`)

Written for end-users (cafe owner, cashier). No technical jargon.

```markdown
# [Feature Name] - Panduan Pengguna

## Apa itu [Feature]?
Penjelasan singkat dalam bahasa yang mudah dipahami.

## Cara Menggunakan

### [Langkah/Aksi 1]

1. Buka halaman **[Nama Halaman]**
2. Klik tombol **[Nama Tombol]**
3. Isi form yang muncul:
   - **[Field 1]**: penjelasan
   - **[Field 2]**: penjelasan
4. Klik **Simpan**

> 💡 **Tips:** Helpful tip for the user.

### [Langkah/Aksi 2]
...

## FAQ

**Q: [Pertanyaan umum]?**
A: [Jawaban singkat dan jelas]

## Troubleshooting

| Masalah | Solusi |
|---------|--------|
| [Problem] | [Solution] |
```

---

## Workflow

When creating documentation:

1. **Identify scope** — Which feature/module to document?
2. **Explore the code** — Read Livewire components, models, routes, Blade views
3. **Create dev-docs** — Technical explanation with diagrams
4. **Create user-docs** — End-user guide in Indonesian
5. **Update indexes** — Add entry to README files
6. **Record decisions** — If architectural choices were made, create ADR

## Diagram Guidelines (Mermaid)

Use appropriate diagram types:

| Purpose | Mermaid Type |
|---------|-------------|
| Request/data flow | `flowchart TD` or `sequenceDiagram` |
| Data relationships | `erDiagram` |
| State transitions | `stateDiagram-v2` |
| User journey | `journey` |
| Component structure | `graph TD` |

## Writing Style

| Audience | Language | Tone |
|----------|----------|------|
| Dev docs | English (technical) | Precise, reference-style |
| User docs | Indonesian (Bahasa) | Friendly, step-by-step |
| ADRs | English | Concise, factual |

## Index Updates

After creating any doc, update the relevant README:

- `docs/Readme.md` — master index
- `docs/dev-docs/README.md` — dev docs index
- `docs/user-docs/README.md` — user docs index
