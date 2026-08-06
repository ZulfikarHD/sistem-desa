    # Task: Implement [US-7.4] From Scrum Plan "@scrum-planning/Phase 07 - Penerbitan Surat Keterangan.md " , Test, and Document

    ## ⚠️ COMPLIANCE RULES — READ BEFORE STARTING (apply to every step below)

    These rules override your own judgment about what's "sufficient," "reasonable," or "close enough." They exist because a prior run of this prompt skipped a mandatory deliverable (writing a Playwright test) and substituted a different one (PHPUnit coverage) without asking, justifying it after the fact as "comprehensive enough." That is a compliance failure, not a judgment call, and it is exactly what these rules prohibit.

    1. **No silent substitution.** If a step says to produce X (a specific file, test, format, tool), you may not produce Y instead and call the step done — even if Y is objectively good, even if Y covers similar ground, even if producing X is harder. If you believe Y is genuinely better than X, you must say so explicitly, stop, and ask before proceeding. You do not decide this unilaterally.
    2. **No silent scope reduction.** "Full suite" means full suite, not the tests related to this feature. "Every failure" means every failure, not a representative sample. "Create it" means create it, not "note that it's missing." Reinterpreting a mandatory instruction as optional, partial, or best-effort — without flagging that reinterpretation to the user first — is not allowed.
    3. **Environment friction is not a valid reason to skip a deliverable.** "This would require a running dev server + seeded database" describes normal setup work, not a blocker. If you actually cannot complete something (missing tool, no network access, genuinely impossible in this environment), you must:
    - State plainly what's blocking you and why.
    - State what you'd need to unblock it.
    - **Stop and ask** what the user wants you to do — do not pick a substitute and present it as compliant.
    4. **No rationalized downgrades.** Do not use phrases like "instead," "however," or "the existing X provides comparable coverage" to justify not doing what was asked. If you catch yourself writing a sentence like that, that is the signal to stop and flag a deviation instead of proceeding.
    5. **Deviations require explicit flagging, not burial.** If you must deviate from any instruction for a real technical reason, say so in its own clearly labeled line — not folded into a paragraph explaining why it's fine.
    6. **When in doubt, under-claim completion, not over-claim it.** If a step is only partially done, say "partially complete: X done, Y not done, because Z" — never round up to "done."
    7. **Do not manufacture scope boundaries from silence.** An AC that describes one case (e.g. "edit page") and says nothing about an adjacent case (e.g. "create page") is **ambiguous**, not a deliberate exclusion. Treating "not mentioned" as "explicitly out of scope" is a fabricated constraint, not a read of the plan — even though it will *sound* like a plan-derived decision if stated as "the plan scopes this to edit only." Explicit exclusion (the plan says "do not implement on create," or a separate story elsewhere is confirmed to own it) and inferred exclusion (the plan just never brings it up) are different things and must never be reported the same way.
    - If a case is not explicitly addressed by the AC, you must: (a) actively search the rest of the plan/backlog for a story that owns it before assuming it's deferred, and (b) if none is found, surface it as an open scope question to the user — not silently implement a partial version and call it complete, and not silently implement the full version either. State the gap and ask.
    - Never phrase an inferred boundary as if it were a stated requirement (e.g. do not say "out of scope per the AC" when what actually happened is "the AC didn't mention it and I chose not to include it").

    ---

    ## Step 1 — Read the source of truth
    1. Open and fully read the plan file for this feature (path to be provided).
    2. Find the section for **[US-7.4]**. Quote or restate its full scope/requirements in your own words before continuing — including acceptance criteria, edge cases, and any explicitly out-of-scope items mentioned in the plan.
    3. Do not rely on memory or assumptions about what this feature is — read the file directly.
    4. If the plan references other features, files, or docs this feature depends on, open and read those too before continuing.
    5. Explicitly separate two categories when restating scope: (a) **stated** requirements/exclusions — directly supported by AC wording, and (b) **adjacent cases the AC is silent on** (e.g. an AC that mentions "edit page" but not "create page" for the same field). For every item in category (b), apply Compliance Rule 7: search the rest of the plan for ownership, and if none is found, list it explicitly as an **open scope question** in your Step 1 output — do not silently fold it into "out of scope" or silently implement it.

    ## Step 2 — Survey the existing codebase before writing anything
    1. Search the codebase for existing patterns, components, utils, or modules this feature should reuse or follow the conventions of. Do not invent new patterns if an established one already exists.
    2. Identify every file that will need to be created or modified. List them explicitly with a one-line reason for each.
    3. Identify every other feature/module that shares state, data, components, or APIs with what you're about to build. List these now — you will re-check them in Step 6.
    4. Do not write code yet. Show this survey as output first.

    ## Step 3 — Implement
    1. Implement the feature to match the plan from Step 1 exactly. If you must deviate from the plan for a technical reason, state the deviation explicitly and why it's necessary — do not deviate silently.
    2. Handle edge cases proactively: null/undefined inputs, empty states, loading states, error states, race conditions, and permission/auth checks where relevant. Do not implement only the happy path.
    3. Match the codebase's existing conventions (naming, file structure, error handling patterns, styling approach) — do not introduce a new convention without a stated reason.
    4. After implementation, re-read every file you changed or created to confirm the code is actually there as intended — don't just assume the edit worked.

    ## Step 4 — Self bug-check (mandatory, not optional)
    This app has a history of bugs from initial development — do not assume your first pass is correct.
    1. Re-review your own new code specifically for: logic errors, edge cases, null/undefined handling, race conditions, incorrect state updates, off-by-one errors, and broken error handling.
    2. Actively try to break your own implementation mentally before concluding it's solid — list at least the edge cases you specifically checked.
    3. Fix anything you find. Do not move to Step 5 with known unfixed issues unless you explicitly justify deferring them.

    ## Step 5 — Full Playwright E2E regression test (mandatory — NOT optional, NOT substitutable, NOT partial)
    This step exists to confirm the new feature works end-to-end in a real browser and that it did not break anything else. Backend test suites (PHPUnit/PEST, unit tests, etc.) do **not** satisfy this step under any circumstance, regardless of how good their coverage is. If this requires standing up a dev server and seeding a database, that setup work is part of this step, not a reason to skip it.

    1. Check whether a Playwright spec already exists for this feature.
    - **If it exists:** extend/update it to cover the new work.
    - **If it does not exist:** create it. This is a required deliverable of this step, not an optional nice-to-have. You may not substitute another test framework's coverage for this requirement. If you genuinely cannot create it (see Compliance Rule 3), stop and ask — do not silently substitute PHPUnit/PEST or any other suite and describe it as satisfying this step.
    2. The new/updated spec must cover the feature's core flows and acceptance criteria from Step 1, including at least one failure/edge-case scenario — not just the happy path.
    3. Run the **full** Playwright suite for the app — not just the new/modified tests. Cross-feature regressions are the entire point of this step.
    4. Report full, literal results: total tests run, passed, failed, skipped. Do not summarize as "tests mostly pass" — list every failure by name.
    5. For every failure, classify it as: **pre-existing failure unrelated to this work**, **regression caused by your new code**, or **real bug in the new feature**. Fix regressions and real bugs, then re-run the full suite again.
    6. Repeat until the full suite passes cleanly, or every remaining failure is explicitly documented as pre-existing/out-of-scope with justification — not omitted or glossed over.
    7. If at any point in this step you find yourself about to write a sentence that starts with "Instead," or "However, the existing [X] suite provides..." — stop. That sentence is the compliance violation this document exists to prevent. Flag the blocker per Compliance Rule 3 instead.

    ## Step 6 — Cross-feature impact check (mandatory)
    1. Revisit the list of related features/modules from Step 2.
    2. For each one, explicitly check whether the new implementation could have broken it, using both code review and the Step 5 test results as evidence.
    3. Prioritize and clearly flag the **most critical** cross-feature risks at the top of this section — don't bury them.

    ## Step 7 — Update documentation
    1. Run the `/create-documentation` command/workflow.
    2. Follow **every single step of its protocol** — do not shortcut, skip, or paraphrase steps in that protocol.
    3. Confirm explicitly, step by step, that each part of the `/create-documentation` protocol was completed.

    ## Step 8 — Compliance self-audit (mandatory, final step before responding)
    Before writing your final response, go back through Steps 1–7 line by line and confirm, for each numbered sub-item, one of:
    - ✅ Done exactly as specified
    - ⚠️ Deviated — [explicit, specific reason, flagged, not buried]
    - ⛔ Blocked — [what's blocking, what's needed, waiting on user decision]

    Do not submit a final response containing any silent gaps — every sub-item needs one of the three markers above. If any item is ⚠️ or ⛔, that must also appear prominently in the relevant section of the Output, not just in this checklist.

    ---

    ## Output format
    Structure your final response with these exact headers, in this order:
    1. **[US-7.4] Plan Summary** (must include an "Open Scope Questions" subsection per Compliance Rule 7 — write "None found" if genuinely none)
    2. **Codebase Survey** (files touched + related features identified)
    3. **Implementation Summary** (what was built, any deviations from plan + why)
    4. **Self Bug-Check Findings** (edge cases checked, issues found and fixed)
    5. **Playwright Full Test Results** (pass/fail/skip counts + every failure explained; explicit confirmation that no backend suite was substituted for this step)
    6. **Cross-Feature Impact** (most critical first)
    7. **Documentation Update Confirmation**
    8. **Compliance Self-Audit** (per-item ✅/⚠️/⛔ status per Step 8)

    Do not skip any header, even if a section is empty — write "None found" explicitly rather than omitting it. Do not omit the Compliance Self-Audit section under any circumstance — this is the section that would have caught the earlier failure.
