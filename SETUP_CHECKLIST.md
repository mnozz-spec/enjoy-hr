# Setup Checklist — Enjoy Croatia Development Environment

Complete these steps in order. Check off as you go. This is a one-time setup.

---

## Phase 1 — SSH access to Hostinger ✅ DONE

- [x] SSH key generated (`~/.ssh/hostinger_enjoyhr`)
- [x] Public key added to Hostinger
- [x] SSH connection tested successfully

**Confirm SSH config entry exists** by running:
```bash
cat ~/.ssh/config
```

You should see an entry for `Host enjoyhr`. If not, add this block:

```
Host enjoyhr
    HostName 92.112.187.42
    Port 65002
    User u320042257
    IdentityFile ~/.ssh/hostinger_enjoyhr
```

Then test: `ssh enjoyhr` — should land you in the shell with no password prompt.

---

## Phase 2 — Staging environment ✅ DONE

- [x] Staging created in Hostinger hPanel
- [x] DNS propagated
- [x] Verified `staging1.enjoy.hr` loads

---

## Phase 3 — Install Local by Flywheel ✅ DONE

- [x] Downloaded and installed Local
- [x] Local site created: `enjoyhr` / `enjoyhr.local` / PHP 8.2
- [x] Site path: `~/Local Sites/enjoyhr/`

---

## Phase 4 — Pull production site to local ✅ DONE

- [x] Production DB and files pulled to local environment
- [x] Local site running at `http://enjoyhr.local`

---

## Phase 5 — Pull production to staging ✅ DONE

- [x] Staging environment running at `https://stagin1.enjoy.hr`

---

## Phase 6 — GitHub repository ✅ DONE

- [x] Private repo created: `mnozz-spec/enjoy-hr`
- [x] GitHub SSH access confirmed
- [x] Initial commit pushed

---

## Phase 7 — Initialize the project locally ✅ DONE

- [x] Project folder at `~/claude-projects/enjoy-hr/`
- [x] Project files (PROJECT_PROMPT.md, CLAUDE.md, etc.) in place
- [x] Git repo initialized, remote added, pushed to GitHub
- [x] `jnews-child/` child theme created in repo
- [x] Symlink in place: `~/Local Sites/enjoyhr/app/public/wp-content/themes/jnews-child` → `~/claude-projects/enjoy-hr/jnews-child`

---

## Phase 8 — First task: site audit ✅ DONE

- [x] Site audit complete — findings in `docs/audit-findings.md`
- [x] JNews OG meta fix developed and deployed to all environments
- [x] First production deployment complete — tagged v1.0.0 (2026-04-26)

---

## Setup complete

All phases done. The development environment is fully operational. See `CLAUDE.md` for current site state and open work items.
