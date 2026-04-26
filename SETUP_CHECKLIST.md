# Setup Checklist — Enjoy Croatia Development Environment

Complete these steps in order. Check off as you go. This is a one-time setup.

---

## Phase 1 — SSH access to Hostinger ✅ DONE

- [x] SSH key generated (`~/.ssh/hostinger_enjoycroatia`)
- [x] Public key added to Hostinger
- [x] SSH connection tested successfully

**Confirm SSH config entry exists** by running:
```bash
cat ~/.ssh/config
```

You should see an entry for `Host enjoycroatia`. If not, add this block:

```
Host enjoycroatia
    HostName 92.112.187.42
    Port 65002
    User u320042257
    IdentityFile ~/.ssh/hostinger_enjoycroatia
```

Then test: `ssh enjoycroatia` — should land you in the shell with no password prompt.

---

## Phase 2 — Staging environment ✅ DONE

- [x] Staging created in Hostinger hPanel
- [x] DNS propagated
- [x] Verified `staging1.enjoy.hr` loads

---

## Phase 3 — Install Local by Flywheel

- [ ] Download Local from https://localwp.com/ (free)
- [ ] Install on Mac mini
- [ ] Open Local, create a new site:
  - Site name: `enjoyhr`
  - Local domain: `enjoyhr.local`
  - PHP version: **8.2** (match production)
  - Web server: **nginx** (or Apache, your choice — nginx is faster)
  - Database: MariaDB / MySQL (default)

After creation, Local will show you:
- Site path: `~/Local Sites/enjoyhr/` (or wherever you pointed it)
- WP-Admin credentials (note these down)

---

## Phase 4 — Pull production site to local

We'll do this together once Local is installed. Two approaches available:

**Option A: Hostinger backup → local import** (cleaner)
- Generate a full backup from hPanel → download → import to Local

**Option B: All-in-One WP Migration plugin** (easier, has size limits on free version)

**Option C: Manual via WP-CLI + rsync** (cleanest for someone comfortable with terminal — Claude Code will guide)

Decision pending — we'll choose based on site size.

---

## Phase 5 — Pull production to staging

In hPanel staging tools, there should be an option to sync production → staging. This gives us an exact copy of the live site to test against.

---

## Phase 6 — GitHub repository

- [ ] Create new private repo on GitHub: `enjoy-hr` (or your preferred name)
- [ ] Do NOT initialize with README (we have one)
- [ ] Get the SSH clone URL (looks like `git@github.com:YOUR_USERNAME/enjoy-hr.git`)
- [ ] Confirm GitHub SSH access works:
  ```bash
  ssh -T git@github.com
  ```
  Should return: `Hi YOUR_USERNAME! You've successfully authenticated...`
  
  If it fails, generate a separate GitHub SSH key:
  ```bash
  ssh-keygen -t ed25519 -C "marko@loxo.hr" -f ~/.ssh/github_marko
  ```
  Add the `.pub` content to GitHub → Settings → SSH and GPG Keys.

---

## Phase 7 — Initialize the project locally

Once Phases 1-6 are done, Claude Code will help with:

- [ ] Project folder already at `~/claude-projects/enjoy-hr/` ✅
- [ ] Project files (PROJECT_PROMPT.md, CLAUDE.md, etc.) in place ✅
- [ ] `git init`, first commit
- [ ] Add GitHub remote, push
- [ ] Verify or create JNews child theme in `child-theme/`
- [ ] Symlink `child-theme/` into `~/Local Sites/enjoyhr/app/public/wp-content/themes/jnews-child`

The symlink command will be:
```bash
ln -s ~/claude-projects/enjoy-hr/child-theme \
      ~/Local\ Sites/enjoyhr/app/public/wp-content/themes/jnews-child
```

---

## Phase 8 — First task: site audit

Once everything above is done, the first real task begins:
- See `tasks/01-site-audit.md`
- Output goes to `docs/audit-findings.md`
- After audit, we'll plan concrete improvements

---

## What you (Marko) need to do RIGHT NOW

1. ~~Wait for staging propagation~~ ✅ done
2. ~~Confirm staging URL~~ ✅ `staging1.enjoy.hr`
3. Install Local by Flywheel: https://localwp.com/
4. Create the local site with domain `enjoyhr.local`
5. Confirm GitHub username so I can write the right URLs
6. Decide repo name: `enjoy-hr` (matches your folder) or something else?

When ready, ping Claude Code with: *"Setup is ready, let's start Phase 7."*

## What Claude Code will do next

- Walk you through Phase 4 (pulling production to local)
- Set up the Git repo with these project files
- Verify or create the JNews child theme
- Create the symlink between project folder and Local
- Run the first site audit
