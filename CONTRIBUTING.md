# Contributing to KeywordAI

Thank you for your interest in contributing to KeywordAI! This document provides guidelines and instructions for contributing.

## Code of Conduct

- Be respectful and constructive in all interactions
- Focus on what's best for the community and project
- Welcome newcomers and help them get started

## How Can I Contribute?

### Reporting Bugs

Before creating a bug report, please:
1. Check if the issue already exists
2. Use the latest version to verify the bug still exists
3. Collect information about the bug (steps to reproduce, environment, etc.)

When reporting a bug, include:
- Clear title and description
- Steps to reproduce
- Expected vs actual behavior
- Your environment (OS, PHP version, etc.)
- Any relevant logs or error messages

### Suggesting Enhancements

Enhancement suggestions are tracked as GitHub/GitLab issues. When creating one:
- Use a clear, descriptive title
- Provide a detailed description of the proposed feature
- Explain why this enhancement would be useful
- Consider including mockups or examples

### Pull Requests

1. Fork the repository
2. Create a new branch (`git checkout -b feature/amazing-feature`)
3. Make your changes
4. Run tests if available
5. Commit your changes (`git commit -m 'Add amazing feature'`)
6. Push to the branch (`git push origin feature/amazing-feature`)
7. Open a Pull Request

#### Pull Request Guidelines

- Update the README.md if needed
- Follow the existing code style
- Add comments for complex logic
- Ensure your code works with the latest main branch
- Reference any related issues

## Development Setup

### Prerequisites

- PHP 8.2+
- Composer
- Node.js 18+
- SQLite (or MySQL/PostgreSQL)

### Local Installation

```bash
# Clone your fork
git clone https://github.com/yourusername/keywordai.git
cd keywordai

# Install PHP dependencies
composer install

# Install Node dependencies
npm install

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate

# Create SQLite database
touch database/database.sqlite

# Run migrations
php artisan migrate

# Build assets
npm run build

# Start development server
php artisan serve
```

### Running Tests

```bash
# Run PHPUnit tests
php artisan test

# Run linting
./vendor/bin/pint
```

## Project Structure

```
├── app/
│   ├── Console/Commands/    # Artisan commands
│   ├── Http/Controllers/    # Web & API controllers
│   ├── Jobs/                # Background jobs
│   ├── Models/              # Eloquent models
│   └── Services/            # Business logic services
├── config/                  # Configuration files
├── database/
│   ├── migrations/          # Database migrations
│   └── seeders/             # Database seeders
├── resources/
│   ├── views/               # Blade templates
│   ├── css/                 # Stylesheets
│   └── js/                  # JavaScript/TypeScript
├── routes/                  # Route definitions
└── tests/                   # Test files
```

## Coding Standards

- Follow PSR-12 coding standards
- Use meaningful variable and function names
- Write docblocks for classes and methods
- Keep methods small and focused
- Use type hints where possible

### PHP Style Guide

```php
<?php

namespace App\Services;

use App\Models\SearchTerm;
use Illuminate\Support\Collection;

class SearchTermService
{
    /**
     * Process search terms and return filtered collection.
     *
     * @param array $filters
     * @return Collection<SearchTerm>
     */
    public function process(array $filters): Collection
    {
        // Implementation
    }
}
```

### Git Commit Messages

- Use present tense ("Add feature" not "Added feature")
- Use imperative mood ("Move cursor to..." not "Moves cursor to...")
- Limit first line to 72 characters
- Reference issues and pull requests after the first line

Examples:
```
feat: Add AI analysis for search terms

- Integrate Gemini API for term analysis
- Add suggestion endpoint for negative keywords
- Include confidence scoring in responses

Closes #123
```

```
fix: Handle null campaign names in sync job

Previously, the sync would fail when Google returned
null campaign names. Now we default to 'Unknown Campaign'.

Fixes #456
```

## Security

- Never commit API keys, passwords, or tokens
- Use environment variables for sensitive data
- Follow OWASP guidelines for web security
- Report security vulnerabilities privately

## Questions?

Feel free to open an issue for questions or join discussions.

Thank you for contributing! 🎉
