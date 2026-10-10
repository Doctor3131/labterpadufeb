# Issue tracker: GitHub

Issues and specs for this repo live as GitHub issues. Use the `gh` CLI for all operations.

## Conventions

- **Create an issue**: `gh issue create --title "..." --body "..."`. Use a heredoc for multi-line bodies.
- **Read an issue**: `gh issue view <number> --json number,title,body,labels,comments`.
- **List issues**: `gh issue list --state open --json number,title,body,labels,comments --jq '[.[] | {number, title, body, labels: [.labels[].name], comments: [.comments[].body]}]'` with appropriate `--label` and `--state` filters.
- **Make an issue a sub-issue of a parent**: use `gh issue create --parent <parent>` or `gh issue edit <parent> --add-sub-issue <child>`. Older `gh` versions can use the GitHub API; otherwise put `Part of #<parent>` in the child body.
- **Comment on an issue**: `gh issue comment <number> --body "..."`
- **Apply / remove labels**: `gh issue edit <number> --add-label "..."` / `--remove-label "..."`
- **Close**: `gh issue close <number> --comment "..."`

Infer the repo from `git remote -v`; `gh` does this automatically inside the clone.

## Pull requests as a triage surface

**PRs as a request surface: no.** Set to `yes` if this repo treats external PRs as feature requests.

When enabled, use `gh pr` equivalents to read, comment on, label, and close PRs. GitHub shares one number space across issues and PRs, so resolve a bare `#42` with `gh pr view 42`, then fall back to `gh issue view 42`.

## When a skill says "publish to the issue tracker"

Create a GitHub issue.

## When a skill says "fetch the relevant ticket"

Read it using the command above.

## Wayfinding operations

The map is a single issue labelled `wayfinder:map`; child tickets are linked as GitHub sub-issues where available. Otherwise use a task list in the map body and `Part of #<map>` in each child. Use `wayfinder:<type>` labels (`research`/`prototype`/`grilling`/`task`).

Blocking uses GitHub native issue dependencies when available. Add an edge with `gh api --method POST repos/<owner>/<repo>/issues/<child>/dependencies/blocked_by -F issue_id=<blocker-db-id>`; the ID is the blocker's numeric database ID, not issue number or node ID. Otherwise record `Blocked by: #<n>` in the child body. A ticket is unblocked when all blockers are closed.

Claim a ticket with `gh issue edit <n> --add-assignee @me`. Resolve by commenting with the answer, closing the issue, then adding a context pointer to the map's Decisions-so-far.
