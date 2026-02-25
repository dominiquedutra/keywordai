# Lessons Learned

## CRITICAL: Key Leak on Public GitHub Repo

**Date:** 2025 (prior to current work)
**Impact:** Had to delete the entire GitHub repo due to leaked credentials.

### What happened
- Sensitive credentials (API keys, tokens) were committed and pushed to a public GitHub repository.
- Once pushed to a public repo, secrets are exposed even if deleted later (git history, forks, caches).
- The only safe remediation was deleting the entire repo.

### Rules to prevent recurrence
1. **NEVER commit secrets** — API keys, tokens, passwords, `.ini` files with credentials, `.env` files.
2. **Always verify `.gitignore`** before first commit — ensure `config/google_ads_php.ini`, `.env`, and any credential files are listed.
3. **Before any `git add`**, review staged files explicitly — avoid `git add .` or `git add -A` which can sweep in secret files.
4. **If a secret is accidentally committed**, rotate the key IMMEDIATELY — deleting the commit is not enough.
5. **Use `.env.example` and `.ini.example`** with placeholder values, never real credentials.
6. **Before making a repo public**, audit the entire git history for secrets with tools like `git log -p | grep -i "key\|secret\|token\|password"`.
7. **Never hardcode webhook URLs or API endpoints with embedded keys** — use env vars instead.

### Specific leaked secret
- **File:** `app/Services/GoogleChatNotificationService.php:18`
- **What:** Google Chat webhook URL with embedded API key was hardcoded in source code.
- **Fix:** Moved to `GOOGLE_CHAT_WEBHOOK_URL` env var.
- **Rotation:** The exposed API key and webhook token MUST be rotated in Google Cloud Console.
