# Enjoy Croatia — Development Project

Custom development for **enjoy.hr**, a Croatian tourism content platform running on JNews + WordPress + Hostinger.

## What's in this repo

- `PROJECT_PROMPT.md` — Master prompt for Claude Code
- `CLAUDE.md` — Code conventions (Claude Code reads this automatically)
- `SETUP_CHECKLIST.md` — Step-by-step initial setup (start here first time)
- `docs/workflow.md` — Daily workflow: local → staging → production
- `docs/deployment.md` — How to deploy changes safely
- `tasks/` — Task briefs for specific pieces of work
- `jnews-child/` — The child theme source code (symlinked into Local's WordPress install)
- `snippets/` — Standalone PHP snippets (for reference)

## Quick start

If this is your first time on this machine: see `SETUP_CHECKLIST.md`.

If everything's already set up:

```bash
# Connect to Hostinger via SSH
ssh enjoyhr

# Or start a Claude Code session in the project root
cd ~/claude-projects/enjoy-hr
claude
```

## Environments

| | URL | Purpose |
|---|---|---|
| Local | http://enjoyhr.local | Development |
| Staging | https://stagin1.enjoy.hr | Pre-production testing *(note: "stagin1" typo in subdomain — leave as-is)* |
| Production | https://enjoy.hr | Live site |

## Golden rules

1. Never edit production directly
2. Never edit the parent JNews theme
3. Always test on staging before production
4. Always commit before deploying
5. When in doubt, ask Claude Code to dry-run / explain first

## Hosting

- **Provider:** Hostinger
- **Plan:** Business
- **SSH alias:** `enjoyhr` (configured in `~/.ssh/config`)
- **WP-CLI:** Available on the server at `/usr/local/bin/wp`

## Contact

Marko Nožica — marko@loxo.hr
