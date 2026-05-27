# 🚀 Quick Start - CI/CD Pipeline

## ⚡ 5-Minute Setup

### 1. Verify GitHub Secrets (Required)

Go to: **GitHub → Settings → Secrets and variables → Actions**

Add these secrets:
```
DEPLOY_HOST    = your-server-ip-or-domain
DEPLOY_USER    = ssh-username
DEPLOY_KEY     = ssh-private-key-content
```

### 2. Test Locally

```bash
cd src

# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Check code style
vendor/bin/pint --test

# Run static analysis
vendor/bin/phpstan analyse app --level=5
```

### 3. Push to GitHub

```bash
# Create feature branch
git checkout -b feature/my-feature

# Make changes
# ...

# Commit
git add .
git commit -m "Add my feature"

# Push to develop (test only)
git push origin feature/my-feature

# Or push to main (test + deploy)
git push origin main
```

### 4. Monitor Pipeline

Go to: **GitHub → Actions → CI/CD Pipeline**

Watch the workflow:
- ✅ TEST job (5-10 min)
- ✅ LINT job (2-3 min)
- ✅ DEPLOY job (5 min, main only)

---

## 📋 Common Commands

### Run Tests

```bash
cd src

# All tests
php artisan test

# With coverage
php artisan test --coverage

# Specific file
php artisan test tests/Feature/Auth/LoginTest.php

# Specific test
php artisan test --filter testUserCanLogin

# Unit tests only
php artisan test tests/Unit

# Feature tests only
php artisan test tests/Feature

# Parallel
php artisan test --parallel

# Verbose
php artisan test --verbose
```

### Code Quality

```bash
cd src

# Check style
vendor/bin/pint --test

# Fix style
vendor/bin/pint

# Static analysis
vendor/bin/phpstan analyse app --level=5

# All checks
vendor/bin/pint --test && vendor/bin/phpstan analyse app --level=5
```

### Cleanup (Optional)

```bash
cd src

# Remove Playwright
bash cleanup-playwright.sh

# Or manually
npm uninstall @playwright/test
npm ci
```

---

## 🔍 Troubleshooting

### Tests Fail Locally

```bash
cd src

# Check PHP version
php -v

# Check extensions
php -m

# Run with verbose output
php artisan test --verbose

# Check environment
cat .env.testing.example
```

### Workflow Fails on GitHub

1. Go to **GitHub → Actions → CI/CD Pipeline**
2. Click on failed job
3. View logs
4. Check error message
5. Fix locally and push again

### Deployment Fails

1. Check **GitHub Secrets** are configured
2. Verify SSH key permissions: `chmod 600 ~/.ssh/deploy_key`
3. Test SSH connection: `ssh -i deploy_key user@host`
4. Check server logs: `ssh user@host tail -f /var/www/productschool/storage/logs/laravel.log`

---

## 📚 Documentation

- **Testing Guide**: [docs/TESTING.md](./docs/TESTING.md)
- **CI/CD Documentation**: [docs/CI-CD.md](./docs/CI-CD.md)
- **Cleanup Guide**: [CLEANUP_GUIDE.md](./CLEANUP_GUIDE.md)
- **Full Report**: [CI-CD-CLEANUP-REPORT.md](./CI-CD-CLEANUP-REPORT.md)

---

## ✅ Checklist

Before pushing to production:

- [ ] GitHub Secrets configured (DEPLOY_HOST, DEPLOY_USER, DEPLOY_KEY)
- [ ] Tests pass locally: `php artisan test`
- [ ] Code style passes: `vendor/bin/pint --test`
- [ ] Static analysis passes: `vendor/bin/phpstan analyse app --level=5`
- [ ] Feature branch tested on GitHub
- [ ] Pull request reviewed
- [ ] Merged to main branch
- [ ] Deployment successful

---

## 🎯 Pipeline Overview

```
Push to GitHub
    ↓
TEST JOB (30 min)
├─ Setup PHP 8.3
├─ Install dependencies
├─ Run PHPUnit tests
└─ Upload coverage
    ↓
LINT JOB (15 min) [Parallel]
├─ Run Pint (style)
├─ Run PHPStan (analysis)
└─ Run Larastan (Laravel)
    ↓
All pass?
├─ YES → DEPLOY JOB (main only)
│   ├─ SSH to server
│   ├─ Pull code
│   ├─ Install dependencies
│   ├─ Run migrations
│   ├─ Optimize app
│   └─ Restart services
└─ NO → ❌ FAIL
```

---

## 🔐 Security

✅ **What's Secure**:
- No credentials in repository
- Test files excluded from deployment
- SSH key in GitHub Secrets only
- Environment variables in CI/CD only

⚠️ **What to Check**:
- GitHub Secrets are configured
- SSH key has limited permissions
- Server firewall allows SSH
- Deployment user has limited permissions

---

## 📞 Need Help?

1. Check documentation: [docs/CI-CD.md](./docs/CI-CD.md)
2. Review test guide: [docs/TESTING.md](./docs/TESTING.md)
3. Check GitHub Actions logs
4. Review error messages carefully
5. Test locally first

---

**Status**: ✅ Ready for Production

Your CI/CD pipeline is configured and ready to deploy!

