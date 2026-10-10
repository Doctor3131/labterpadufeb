# Domain Docs

How engineering skills should consume this repo's domain documentation.

## Before exploring

Read the root `GLOSSARY.md`, if present, and relevant ADRs in `docs/adr/`. If these files don't exist, proceed without flagging their absence; domain-modeling creates them when terms or decisions are resolved.

## Layout

This repo uses a single-context layout:

- `GLOSSARY.md` at the root
- ADRs in `docs/adr/`

## Vocabulary and decisions

Use glossary terms when naming domain concepts in issues, proposals, hypotheses, or tests. If a concept is missing, reconsider whether it belongs to the project vocabulary or note the gap for domain-modeling.

If your output conflicts with an ADR, surface the conflict explicitly rather than silently overriding it.
